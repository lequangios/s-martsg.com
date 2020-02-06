<?php 
/**
   * Create Meta Box for Post and Page
   * 
   * @package    none
   * @subpackage none
   * @author     Quang Le <levietquangt2@gmail.com>
   */
class My_Custom_Group_Meta_Box
{
	public $post_type_array;
	public $metabox_div_id;
	public $metabox_label_name;
	public $metabox_name;
	public $post_id = false;
	public $data_model;
	public $metabox_group_value_id;
	public $metabox_group_wrapper_id;

	/**
       * 
       * Hook into the appropriate actions when the class is constructed.
       *
       * @param array() 	$post_type_array  		array of post type will add meta box
       * @param string  	$metabox_div_id  		id of div container of meta box
       * @param string 		$metabox_label_name  	label name
       * @param string 		$metabox_name  			name of meta box
       * @param string 		$metabox_type  			type of meta box : text, textarea, wysiwyg, img, multi_img
       * @param string 		$position  				position of meta box : normal, side, advanced
       * @param integer 	$post_id  				id of post need to add meta box, default is false, will add to all post
       * @return object
       */
	public function __construct($post_type_array = array(), $metabox_div_id, $metabox_label_name, $metabox_name, $data_model = 'modelData', $post_id = false, $break = false) 
	{
		$this->post_type_array = $post_type_array;
		$this->metabox_div_id = $metabox_div_id;
		$this->metabox_label_name = $metabox_label_name;
		$this->metabox_name = $metabox_name;
		$this->post_id = $post_id;
		$this->data_model = $data_model;
		$this->metabox_group_value_id = $data_model.'_value';
		$this->metabox_group_wrapper_id = $data_model.'_wrapper_id';

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box($post_type) {
		// Add for one page
		global $post;
		if($post && $this->post_id != false && $this->post_id != $post->ID)
		{
			return;
		}

		if ( in_array( $post_type, $this->post_type_array )) 
		{
			add_meta_box(
				$this->metabox_div_id
				,__($this->metabox_label_name)
				,array( $this, 'render_meta_box_content' )
				,$post_type
				,'normal'
				,'low'
			);
		}

		
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save($post_id) {
		
		global $post;
		if($post && $this->post_id != false && $this->post_id != $post->ID)
		{
			return;
		}

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		$nonce_name = $this->metabox_name.'_nonce';

		if ( ! isset( $_POST[$nonce_name] ) )
			return $post_id;

		// $nonce = $_POST[$nonce_name];

		// // Verify that the nonce is valid.
		// if ( ! wp_verify_nonce( $nonce, 'dariu_metabox' ) )
		// 	return $post_id;

		// If this is an autosave, our form has not been submitted,
		//     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post->ID ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post->ID) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		$mydata = sanitize_text_field( $_POST[$this->metabox_name] );

		// Update the meta field.
		update_post_meta( $post->ID, $this->metabox_name, $mydata );

		// logMyMessage($mydata);
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {

		if($this->post_id != false && $this->post_id != $post->ID)
		{
			return;
		}
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( $this->metabox_label_name, $this->metabox_name.'_nonce' );

		$upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );

		// $values = get_post_custom( $post->ID );
		$values = get_post_meta( $post->ID, $this->metabox_name, true );

		if($values==NULL) $values = '{"data":[]}';
		// $value = isset( $values[$this->metabox_name] ) ? esc_attr( $values[$this->metabox_name][0] ) : '{"data":[]}';
		?>
		<div id="<?php echo $this->metabox_group_wrapper_id; ?>" class="custom-meta-box-wrapper">
			<input type="hidden" id="<?php echo $this->metabox_group_value_id ; ?>" value = "<?php echo esc_html($values); ?>"/>
			<div class="group-meta" data-bind="foreach:{ data: <?php echo $this->data_model; ?>  }">
				<div  data-bind="attr:{id:'item_'+id()}" class="group-meta-content">
					<p class="row"><label>Title</label><input type="text" name="title" placeholder="Title" data-bind="textInput:title"/></p>
					<p style="clear:both"></p>
					<p class="row">
						<label>Decription</label>
						<div class="col" style="float: left; max-width:500px;width:100%;">
							<p class="menu-p">[&nbsp;<span class="intLink" onclick="insertMetachars('&lt;b&gt;','&lt;\/b&gt;', this);"><strong>Bold</strong></span> | <span class="intLink" onclick="insertMetachars('&lt;em&gt;','&lt;\/em&gt;', this);"><em>Italic</em></span> | <span class="intLink" onclick="var newURL=prompt('Enter the full URL for the link');if(newURL){insertMetachars('&lt;a href=\u0022'+newURL+'\u0022&gt;','&lt;\/a&gt;');}else{document.myForm.myTxtArea.focus();}">URL</span>&nbsp;]</p>

							<textarea cols="8" rows="10" style="max-width:500px;width:100%;" name="description" placeholder="Decription"  data-bind="textInput:description, attr:{value:description}" value=""></textarea>
						</div>
					</p>
					<p style="clear:both"></p>
					<p class="row"><label>Url</label><input type="text" name="url" placeholder="url" data-bind="textInput:url"/></p>
					<p style="clear:both"></p>
					<p>
						<label>Icon</label>
						<p class="col">
							<div class="review no-icon">
								<img class="iconUrl" src="" data-bind="attr:{src: iconUrl}">
								<a class="close" data-bind="event:{click:$parent.removeMediaImage}"></a>
								<input type="text" class="iconId hidden" name="iconId" value="" data-bind="attr:{value:iconId}" />
							</div>
							<a class="add-new-icon" data='group_0' data-bind="event:{click:$parent.addMediaImage}" href="<?php echo $upload_link ?>">Add Icon</a>
						</p>
					</p>
					<input type="text" class="dataJson hidden" name="dataJson" value="" data-bind="attr:{value:dataJson}" />
					<p style="clear:both"></p>
					<a class="remove-entry button-primary" data-bind="click: $parent.removeEntry" >Remove Entry</a>
					<div class="line"></div>
				</div>
			</div>
			<a class="add-entry button-primary" data-bind="click: addEntry">Add Entry</a>	
			<p style="clear:both"></p>
			<input type='text' class="hidden" name='<?php echo $this->metabox_name ?>' id='<?php echo $this->metabox_name ?>' data-bind="attr:{value:homeMetaData}" value='<?php echo $value; ?>'/>
		</div>
		<?php
	}
}