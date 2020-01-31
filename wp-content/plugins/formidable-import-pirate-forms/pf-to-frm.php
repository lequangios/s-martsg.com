<?php
/*
Plugin Name: Pirate Forms to Formidable Importer
Description: Import forms from Pirate Forms to Formidable Forms
Version: 1.0
Plugin URI: https://formidableforms.com/
Author: Strategy11
*/

/*
- if PF is installed, and Formidable is not, alter the PF migrate page to include Formidable
- The PF migrate page links to the Formidable import/export page
*/
class FrmPirateImporter {

	public $frm_active;

	public $pf_active;

	public $slug = 'pirate-forms';

	public $name = 'Pirate Forms';

	public $tracking = 'frm_forms_imported';

	/**
	 * Define required properties.
	 */
	public function __construct() {
		if ( ! is_admin() ) {
			return;
		}

		$this->pf_active = is_callable( 'PirateForms_Util::get_form_options' );
		if ( ! $this->pf_active ) {
			// if PF is not installed, do nothing
			return;
		}

		$this->frm_active = is_callable( 'FrmAppHelper::get_menu_name' );
		
		$this->maybe_add_to_import_page();
	}

	private function maybe_add_to_import_page() {
		add_action( 'pirate-forms_page_pirateforms-admin-migration', array( $this, 'link_from_pf' ), 30 );
		if ( ! $this->frm_active ) {
			return;
		}

		$menu_name = sanitize_title( FrmAppHelper::get_menu_name() );
		add_action( $menu_name . '_page_formidable-import', array( $this, 'import_page' ), 1 );
		add_action( 'wp_ajax_frm_import_' . $this->slug, array( $this, 'import_forms' ) );
	}

	/**
	 * Add a link on the Pirate Forms migration page
	 */
	public function link_from_pf() {
		?>
		<div class="wrap">
			<div id="pf-migration">
				<div class="pf-migration-header">
					<h2>Looking for more advanced forms?</h2>
					<p>If your forms need to do more heavy lifting, migrate from Pirate Forms to Formidable Forms.</p>
					<?php if ( $this->frm_active ) { ?>
						<a class="button button-secondary button-hero" href="<?php echo esc_url( admin_url( 'admin.php?page=formidable-import' ) ); ?>">Start Migration to Formidable</a>
					<?php } else { ?>
						<a class="button button-secondary button-hero" href="<?php echo esc_url( admin_url( 'plugin-install.php?s=formidableforms&tab=search&type=author' ) ); ?>">Install Formidable</a>
						
					<?php } ?>
				</div>
			</div>
		</div>
		<?php
	}

	public function import_page() {
		add_action( 'admin_footer', array( $this, 'load_js' ) );
		?>
		<div class="wrap">
			<div class="welcome-panel" id="welcome-panel">
				<h2><?php echo esc_html( $this->name ); ?> Importer</h2>
				<div class="welcome-panel-content" style="text-align:center;margin-bottom:10px;">
					<p class="about-description">
						Import your Pirate forms and settings automatically. <br/>
						Select the forms to import.
					</p>
					<form id="frm_form_pf_importer" method="post" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
						<?php wp_nonce_field( 'nonce', 'frm_ajax' ); ?>
						<input type="hidden" name="slug" value="<?php echo esc_attr( $this->slug ); ?>" />
						<input type="hidden" name="action" value="frm_import_<?php echo esc_attr( $this->slug ); ?>" />
						<div style="margin:10px auto;max-width:400px;text-align:left;">
							<?php foreach ( $this->get_forms() as $form_id => $name ) { ?>
								<p>
									<label>
										<input type="checkbox" name="form_id[]" value="<?php echo esc_attr( $form_id ); ?>" checked="checked" />
										<?php echo esc_html( $name ); ?>
										<?php if ( $new_form_id = $this->is_imported( $form_id ) ) { ?>
											(<a href="<?php echo esc_url( admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . $new_form_id ) ); ?>">previously imported</a>)
										<?php } ?>
									</label>
								</p>
							<?php } ?>
						</div>
						<button type="submit" class="button button-primary button-hero">Start Import</button>
					</form>
					<div id="frm-importer-process" class="frm_hidden">

						<p class="process-count">
							<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
							Importing <span class="form-current">1</span> of <span class="form-total">0</span> forms from <?php echo esc_html( $this->name ); ?>.
						</p>

						<p class="process-completed" class="frm_hidden">
							The import process has finished! We have successfully imported <span class="forms-completed"></span> forms. You can review the results below.
						</p>

						<div class="status"></div>

					</div>
				</div>
			</div>
		</div>
		<?php
	}

	public function load_js() {
		wp_enqueue_script( 'pf_frm', plugins_url( '', __FILE__ ) . '/import.js', array( 'jquery' ), 1 );

		$strings = array(
			'nonce'        => wp_create_nonce( 'frm_ajax' ),
			'ajax_url'     => admin_url( 'admin-ajax.php', is_ssl() ? 'admin' : 'http' ),
		);
		wp_localize_script( 'pf_frm', 'pffrm', $strings );
	}

	/**
	 * Import all forms using ajax
	 */
	public function import_forms() {

		check_ajax_referer( 'frm_ajax', 'nonce' );
		FrmAppHelper::permission_check( 'frm_edit_forms' );

		$forms = FrmAppHelper::get_simple_request(
			array(
				'param'    => 'form_id',
				'type'     => 'post',
				'sanitize' => 'absint',
			)
		);

		if ( is_array( $forms ) ) {
			$imported = array();
			foreach ( (array) $forms as $form_id ) {
				$imported[] = $this->import_form( $form_id );
			}
		} else {
			$imported = $this->import_form( $forms );
		}

		wp_send_json_success( $imported );
	}

	/**
	 * Import a single form
	 */
	public function import_form( $pf_form_id ) {

		$pf_form           = $this->get_form( $pf_form_id );
		$pf_fields_custom  = PirateForms_Util::get_post_meta( $pf_form_id, 'custom' );
		if ( ! empty( $pf_fields_custom[0] ) ) {
			$pf_fields_custom = $pf_fields_custom[0];
		} else {
			$pf_fields_custom = array();
		}

		$pf_fields_default = array(
			'name',
			'email',
			'subject',
			'message',
			'attachment',
			'checkbox',
			'recaptcha',
		);

		$upgrade_omit = array();
		$fields       = array();

		// Prepare all DEFAULT fields.
		foreach ( $pf_fields_default as $field ) {
			// Ignore fields that are not displayed or not added at all.
			if ( empty( $pf_form[ 'pirateformsopt_' . $field . '_field' ] ) ) {
				continue;
			}

			$label = ! empty( $pf_form[ 'pirateformsopt_label_' . $field ] ) ? $pf_form[ 'pirateformsopt_label_' . $field ] : ucwords( $field );

			$new_field = array(
				'type'     => $this->convert_field_type( $field ),
				'name'     => $label,
				'required' => 'req' === $pf_form[ 'pirateformsopt_' . $field . '_field' ] ? '1' : '',
				'label'    => 'inside',
				'original' => $field,
			);

			if ( isset( $pf_form[ 'pirateformsopt_label_err_' . $field ] ) ) {
				$new_field['blank'] = $pf_form[ 'pirateformsopt_label_err_' . $field ];
			} elseif ( isset( $pf_form[ 'pirateformsopt_label_err_no_' . $field ] ) ) {
				$new_field['blank'] = $pf_form[ 'pirateformsopt_label_err_no_' . $field ];
			}

			// If it is Lite and it's a field type not included, make a note then continue to the next field.
			if ( $this->skip_pro_field( $new_field['type'] ) ) {
				$upgrade_omit[] = $label;
				continue;
			}

			switch ( $field ) {
				case 'name':
					$new_field['classes'] = 'frm_first frm_half';
					break;
				case 'email':
					$new_field['classes'] = 'frm_half';
					break;
				case 'message':
					$new_field['blank'] = $pf_form[ 'pirateformsopt_label_err_no_content'];
					break;
				case 'recaptcha':
					$new_field['label'] = 'none';
					$new_field['required'] = 0;
					break;
				case 'checkbox':
					$new_field['label'] = 'none';
					$new_field['options'] = array( $label );
					break;
				case 'attachment':
				case 'file':
					$new_field['label'] = 'none';
					$new_field['attach'] = 1;
					break;
			}

			$fields[] = $new_field;
		}

		// Prepare all CUSTOM fields.
		foreach ( $pf_fields_custom as $id => $field ) {
			// Ignore fields that are not displayed.
			if ( empty( $field['display'] ) ) {
				continue;
			}

			$label = sanitize_text_field( $field['label'] );
			$new_field = array(
				'type'     => $this->convert_field_type( $field['type'] ),
				'name'     => $label,
				'required' => 'req' === $field['display'] ? '1' : '', // Possible values in PF: 'yes', 'req'.,
				'label'    => 'inside',
				'original' => $field['type'],
			);

			// If it is Lite and it's a field type not included, make a note then continue to the next field.
			if ( $this->skip_pro_field( $new_field['type'] ) ) {
				$upgrade_omit[] = $label;
				continue;
			}

			switch ( $field['type'] ) {
				case 'checkbox':
					$new_field['label'] = 'none';
					$new_field['options'] = array( $label );
					break;

				case 'select':
				case 'multiselect':
					$new_field['label'] = 'none';

					$options = array();
					foreach ( explode( PHP_EOL, $field['options'] ) as $option ) {
						$options[] = $option;
					}

					$new_field['options'] = $options;
					if ( $field['type'] === 'multiselect' ) {
						$new_field['multiple'] = 1;
					}
					break;

				case 'label':
					$new_field['name'] = 'HTML';
					$new_field['description'] = $field['label'];
					break;

				case 'file':
					$new_field['label'] = 'none';
					$new_field['attach'] = 1;

					break;
			}

			$fields[] = $new_field;
		}

		$pf_form_name = $this->get_form_name( $pf_form_id );

		// Make sure we have imported some fields.
		if ( empty( $fields ) ) {
			return array(
				'error' => true,
				'name'  => $pf_form_name,
				'msg'   => 'No form fields found.',
			);
		}

		// Create a form array, that holds all the data.
		$form = array(
			'import_form_id' => $pf_form_id,
			'fields'         => $fields,
			'name'           => $pf_form_name,
			'description'    => '',
			'options'        => array(
				'submit_value'    => stripslashes( $pf_form['pirateformsopt_label_submit_btn'] ),
				'success_action'  => empty( $pf_form['pirateformsopt_thank_you_url'] ) ? 'message' : 'page',
				'success_page_id' => (int) $pf_form['pirateformsopt_thank_you_url'],
				'show_form'       => 0,
				'akismet'         => 'yes' === $pf_form['pirateformsopt_akismet'] ? 1 : 0,
				'no_save'         => 'yes' === $pf_form['pirateformsopt_store'] ? 0 : 1,
			),
			'actions'        => array(
				array(
					'type'     => 'email',
					'email_message' => $pf_form['pirateformsopt_email_content'],
					'email_to' => $pf_form['pirateformsopt_email_recipients'],
					'from'     => $pf_form['pirateformsopt_email'],
				),
			),
		);

		if ( ! empty( $pf_form['pirateformsopt_label_submit'] ) ) {
			$form['options']['success_msg'] = $pf_form['pirateformsopt_label_submit'];
		}

		if ( ! empty( $pf_form['pirateformsopt_confirm_email'] ) ) {
			$form['actions'][] = array(
				'type'          => 'email',
				'email_message' => $pf_form['pirateformsopt_confirm_email'],
				'email_subject' => stripslashes( $pf_form['pirateformsopt_label_submit_btn'] ),
				'email_to'      => '[email]',
				'from'          => $pf_form['pirateformsopt_email'],
				'plain_text'    => 1,
			);
		}
		$response = $this->add_form( $form, $upgrade_omit );

		$this->set_ip_saving( $pf_form );
		$this->save_recaptcha_keys( $pf_form );

		return $response;
	}

	private function convert_field_type( $type ) {
		$field_types = array(
			'name'        => 'text',
			'subject'     => 'text',
			'message'     => 'textarea',
			'recaptcha'   => 'captcha',
			'attachment'  => 'file',
			'tel'         => 'phone',
			'multiselect' => 'select',
			'label'       => 'html',
		);
		if ( isset( $field_types[ $type ] ) ) {
			$type = $field_types[ $type ];
		}
		return $type;
	}

	private function skip_pro_field( $type ) {
		$fields_pro_omit = array( 'file' ); // Strict PRO fields with no Lite alternatives.
		return ( ! FrmAppHelper::pro_is_installed() && in_array( $type, $fields_pro_omit, true ) );
	}

	private function set_ip_saving( $pf_form ) {
		// Do not save user IP address and UA.
		if ( empty( $pf_form['pirateformsopt_store_ip'] ) || 'yes' !== $pf_form['pirateformsopt_store_ip'] ) {
			$frm_settings = FrmAppHelper::get_settings();
			$frm_settings->no_ips = 1;
			$frm_settings->store();
		}
	}

	private function save_recaptcha_keys( $pf_form ) {
		$has_keys = ! empty( $pf_form['pirateformsopt_recaptcha_sitekey'] ) && ! empty( $pf_form['pirateformsopt_recaptcha_secretkey'] );
		$has_recaptcha = ! empty( $pf_form['pirateformsopt_recaptcha_field'] ) && 'yes' === $pf_form['pirateformsopt_recaptcha_field'];

		if ( $has_recaptcha && $has_keys ) {
			$frm_settings = FrmAppHelper::get_settings();

			// Try to abstract keys from PF.
			if ( empty( $frm_settings->pubkey ) || empty( $frm_settings->privkey ) ) {
				$frm_settings->pubkey  = $pf_form['pirateformsopt_recaptcha_sitekey'];
				$frm_settings->privkey = $pf_form['pirateformsopt_recaptcha_secretkey'];
				$frm_settings->store();
			}
		}
	}

	/**
	 * Get ALL THE FORMS.
	 * We need only ID's and names here.
	 *
	 * @return array
	 */
	public function get_forms() {

		// Union those arrays, as array_merge() does keys reindexing.
		$forms = $this->get_default_forms() + $this->get_pro_forms();

		// Sort by IDs ASC.
		ksort( $forms );

		return $forms;
	}

	/**
	 * Pirate Forms has a default form, which doesn't have an ID.
	 *
	 * @return array
	 */
	private function get_default_forms() {

		$form = PirateForms_Util::get_form_options();

		// Just make sure that it's there and not broken.
		if ( empty( $form ) ) {
			return array();
		}

		$form_name = $this->get_form_name( 0 );
		return array( 0 => $form_name );
	}

	/**
	 * Copy-paste from Pro plugin code, it doesn't have API to get this data easily.
	 *
	 * @return array
	 */
	private function get_pro_forms() {

		$forms = array();
		$query = new WP_Query(
			array(
				'post_type'              => 'pf_form',
				'post_status'            => 'publish',
				'posts_per_page'         => - 1,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$forms[ get_the_ID() ] = get_the_title();
			}
		}

		return $forms;
	}

	private function get_form_name( $form_id ) {
		if ( empty( $form_id ) ) {
			$form_name = 'Default Form';
		} else {
			$form_name = get_post_field( 'post_title', $form_id );
			$form_name = apply_filters( 'the_title', $form_name, $form_id ); 
		}
		return $form_name;
	}

	/**
	 * Get a single form options.
	 *
	 * @param int $id Form ID.
	 *
	 * @return array
	 */
	private function get_form( $id ) {
		return PirateForms_Util::get_form_options( (int) $id );
	}

	/**
	 * Add the new form to the database and return AJAX data.
	 *
	 * @since 1.4.2
	 *
	 * @param array $form Form to import.
	 * @param array $upgrade_omit No field alternative in WPForms.
	 */
	private function add_form( $form, $upgrade_omit = array() ) {

		// Create empty form so we have an ID to work with.
		$form_id = FrmForm::create(
			array(
				'name'        => $form['name'],
				'description' => $form['description'],
				'options'     => $form['options'],
			)
		);

		if ( empty( $form_id ) ) {
			return array(
				'error' => true,
				'name'  => sanitize_text_field( $form['settings']['form_title'] ),
				'msg'   => esc_html__( 'There was an error while creating a new form.', 'wpforms' ),
			);
		}

		foreach ( $form['fields'] as $key => $field ) {
			$new_field = FrmFieldsHelper::setup_new_vars( $field['type'], $form_id );
			$new_field = array_merge( $new_field, $field );
			$new_field['field_options'] = array_merge( $new_field['field_options'], $field );
			$form['fields'][ $key ]['id'] = FrmField::create( $new_field );
		}

		// create emails
		foreach ( $form['actions'] as $action ) {
			$action_control = FrmFormActionsController::get_form_actions( $action['type'] );
			unset( $action['type'] );
			$new_action = $action_control->prepare_new( $form_id );
			foreach ( $action as $key => $value ) {
				$new_action->post_content[ $key ] = $this->replace_smart_tags( $value, $form['fields'] );
			}

			$action_control->save_settings( $new_action );
		}

		$this->track_import( $form['import_form_id'], $form_id );

		// Build and send final AJAX response!
		return array(
			'name'          => $form['name'],
			'id'            => $form_id,
			'link'          => esc_url_raw( admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . $form_id ) ),
			'upgrade_omit'  => $upgrade_omit,
		);
	}

	/**
	 * Replace 3rd-party form provider tags/shortcodes with our own Smart Tags.
	 * See: PirateForms_Util::get_magic_tags() for all PF tags.
	 *
	 * @param string $string String to process the smart tag in.
	 * @param array  $fields List of fields for the form.
	 *
	 * @return string
	 */
	private function replace_smart_tags( $string, $fields ) {
		foreach ( $fields as $field ) {
			$frm_tag = '[' . $field['id'] . ']';
			if ( 'email' === $field['type'] ) {
				$string = str_replace( '{email}', $frm_tag, $string );
				$string = str_replace( '[email]', $frm_tag, $string );
			} elseif ( 'file' === $field['type'] ) {
				$string = str_replace( '{attachments}', $frm_tag, $string );
			}

			$new_tag = str_replace( array( ' ', '.' ), '_', stripslashes( sanitize_text_field( $field['name'] ) ) );
			$new_tag = '{' . strtolower( $new_tag ) . '}';
			$string = str_replace( $new_tag, $frm_tag, $string );	

			if ( isset( $field['original'] ) ) {
				// covers name, checkbox, message
				$new_tag = '{' . strtolower( $field['original'] ) . '}';
				$string = str_replace( $new_tag, $frm_tag, $string );
			}
		}

		$string = str_replace( '{ip}', '[ip]', $string );
		$string = str_replace( '{permalink}', '', $string );

		return $string;
	}

	/**
	 * After a form has been successfully imported we track it, so that in the
	 * future we can alert users if they try to import a form that has already
	 * been imported.
	 *
	 * @param int $source_id Imported plugin form ID
	 * @param int $new_form_id Formidable form ID
	 */
	private function track_import( $source_id, $new_form_id ) {

		$imported = $this->get_tracked_import();

		$imported[ $this->slug ][ $new_form_id ] = $source_id;

		update_option( $this->tracking, $imported, false );
	}

	/**
	 * @return array
	 */
	private function get_tracked_import() {
		return get_option( $this->tracking, array() );
	}

	/**
	 * @param int $source_id Imported plugin form ID
	 *
	 * @return int the ID of the created form or 0
	 */
	private function is_imported( $source_id ) {
		$imported = $this->get_tracked_import();
		$new_form_id = 0;
		if ( isset( $imported[ $this->slug ] ) && in_array( $source_id, $imported[ $this->slug ] ) ) {
			$new_form_id = array_search( $source_id, array_reverse( $imported[ $this->slug ], true ) );
		}
		return $new_form_id;
	}
}

function load_frm_pirate_importer() {
	new FrmPirateImporter();
}
add_action( 'admin_init', 'load_frm_pirate_importer' );
