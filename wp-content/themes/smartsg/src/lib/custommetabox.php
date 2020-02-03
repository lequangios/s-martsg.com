<?php 
include 'phpqrcode/qrlib.php';

/**
   * Create Meta Box for Post and Page
   * 
   * @package    none
   * @subpackage none
   * @author     Quang Le <levietquangt2@gmail.com>
   */
class My_Custom_Meta_Box
{
	public $post_type_array;
	public $metabox_div_id;
	public $metabox_label_name;
	public $metabox_name;
	public $metabox_type = 'text'; 
	public $position = 'side';
	public $post_id = false;

	/**
       * 
       * Hook into the appropriate actions when the class is constructed.
       *
       * @param array() 	$post_type_array  		array of post type will add meta box
       * @param string  	$metabox_div_id  		id of div container of meta box
       * @param string 		$metabox_label_name  	label name
       * @param string 		$metabox_name  			name of meta box
       * @param string 		$metabox_type  			type of meta box : text, textarea, wysiwyg, img, multi_img, qr_code
       * @param string 		$position  				position of meta box : normal, side, advanced
       * @param integer 	$post_id  				id of post need to add meta box, default is false, will add to all post
       * @return object
       */
	public function __construct($post_type_array = array(), $metabox_div_id, $metabox_label_name, $metabox_name, $metabox_type = 'text', $position = 'side', $post_id = false) 
	{
		$this->post_type_array = $post_type_array;
		$this->metabox_div_id = $metabox_div_id;
		$this->metabox_label_name = $metabox_label_name;
		$this->metabox_name = $metabox_name;
		$this->metabox_type = $metabox_type;
		$this->position = $position;
		$this->post_id = $post_id;

		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
		
	}
	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box($post_type) {
		// Add for one page
		global $post;
		if($this->post_id != false && $this->post_id != $post->ID)
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
				,$this->position
				,'low'
			);
		}
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {
	
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Add for one page
		global $post;
		if($post  && $this->post_id != false && $this->post_id != $post->ID)
		{
			return;
		}

		// Check if our nonce is set.
		$nonce_name = $this->metabox_name.'_nonce';

		if ( ! isset( $_POST[$nonce_name] ) )
			return $post_id;

		$nonce = $_POST[$nonce_name];

		// Verify that the nonce is valid.
		// if ( ! wp_verify_nonce( $nonce, 'dariu_metabox' ) )
		// 	return $post->ID;

		// If this is an autosave, our form has not been submitted,
				//     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post->ID ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post->ID ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input
		$mydata = sanitize_text_field( $_POST[$this->metabox_name] );
		

		if($this->metabox_type == "text")
		{
			// Update the meta field.
			update_post_meta( $post->ID, $this->metabox_name, $mydata );
		}
		else if($this->metabox_type == "textarea")
		{
			// Update the meta field.
			update_post_meta( $post->ID, 
							  $this->metabox_name, 
							  implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $_POST[$this->metabox_name] ) ) ) );
		}
		else if($this->metabox_type == "wyswyg")
		{
			// Update the meta field.
			update_post_meta( $post->ID, $this->metabox_name, $mydata );
		}
		else if($this->metabox_type == "qr_code")
		{
			$url = $post->guid;
			$slug = $post->post_name;
			$path = wp_get_upload_dir();
			$dir = $path['basedir']."/QRCode/";
			$dir_url = $path['baseurl'].'/QRCode/';
			if($slug != '') {
				$img_url = $dir.$slug.".png";
				QRcode::png($url, $img_url, 'L', 15, 2);
				$mydata = $dir_url.$slug.'.png';
				// Update the meta field.
				update_post_meta( $post->ID, $this->metabox_name, $mydata);
				//header("Refresh:0");
			}
		}
		else 
		{
			// Update the meta field.
			update_post_meta( $post->ID, $this->metabox_name, $mydata );
		}
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content($post) {

		global $post;
		if($this->post_id != false && $this->post_id != $post->ID)
		{
			return;
		}
	
		if($this->metabox_type == "text")
		{
			$this->render_text_meta_box_content();
		}
		else if($this->metabox_type == "textarea")
		{
			$this->render_textarea_meta_box_content();
		}
		else if($this->metabox_type == "wyswyg")
		{
			$this->render_wyswyg_meta_box_content();
		}
		else if ($this->metabox_type == "qr_code") {
			$this->render_qrcode_metabox_content();
		}
		else 
		{
			$this->render_text_meta_box_content();
		}
	}

	/**
	 * Render QRCode Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */

	public function render_qrcode_metabox_content()
	{
		global $post;
		$values = get_post_custom( $post->ID );
		$value = isset( $values[$this->metabox_name] ) ? esc_attr( $values[$this->metabox_name][0] ) : '';
		//var_dump($post);
		// Add an nonce field so we can check for it later.
		wp_nonce_field( $this->metabox_label_name, $this->metabox_name.'_nonce' );
		if($value == ''){
			?>
			<a style="margin: 20px auto; display: block; width: 495px; height: 495px;" href="javascript:location.reload();">
				<?php echo esc_html_e('Tạo Mã QRCode cho sản phẩm') ?>
			</a>
			<?php
		} else {
			?>
			<a style="margin: 20px auto; display: block; width: 495px; height: 495px;" href="<?php echo $value; ?>">
				<img class="prod-qrcode" name="<?php echo $post->post_name; ?>" src="<?php echo $value; ?>"/>
			</a>
			<?php
		}
	}

	/**
	 * Render textarea Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_text_meta_box_content()
	{
		global $post;

		$values = get_post_custom( $post->ID );
		$value = isset( $values[$this->metabox_name] ) ? esc_attr( $values[$this->metabox_name][0] ) : '';

		// Add an nonce field so we can check for it later.
		wp_nonce_field( $this->metabox_label_name, $this->metabox_name.'_nonce' );
		?>
		<label><?php echo $this->metabox_label_name; ?></label><input type="text" placeholder="<?php echo $this->metabox_label_name; ?>" name="<?php echo $this->metabox_name; ?>" id="<?php echo $this->metabox_name; ?>" value="<?php echo $value; ?>" style="max-width:300px;width:100%;">
		<?php
	}

	/**
	 * Render textarea Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_textarea_meta_box_content()
	{
		global $post;

		$values = get_post_custom( $post->ID );
		$value = isset( $values[$this->metabox_name] ) ? esc_html( $values[$this->metabox_name][0] ) : '';

		// Add an nonce field so we can check for it later.
		wp_nonce_field( $this->metabox_label_name, $this->metabox_name.'_nonce' );

		?>
		<textarea cols="8" style="max-width:100%; width:100%;" placeholder="<?php echo $this->metabox_label_name; ?>" name="<?php echo $this->metabox_name; ?>" id="<?php echo $this->metabox_name; ?>" value="<?php echo $value; ?>"><?php echo nl2br($value); ?></textarea>
		<?php
	}

	/**
	 * Render wyswyg Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_wyswyg_meta_box_content()
	{
		global $post;

		$editor_id = $this->metabox_div_id.'_editor';
		$values = get_post_custom( $post->ID );
		$value = isset( $values[$this->metabox_name] ) ? esc_html( $values[$this->metabox_name][0] ) : '';

		// Add an nonce field so we can check for it later.
		wp_nonce_field( $this->metabox_label_name, $this->metabox_name.'_nonce' );

		?>
		<style type='text/css'>
            #<?php echo $this->metabox_div_id; ?> #edButtonHTML, #<?php echo $this->metabox_div_id; ?> #edButtonPreview {background-color: #F1F1F1; border-color: #DFDFDF #DFDFDF #CCC; color: #999;}
            #<?php echo $editor_id; ?>{width:100%;}
            #<?php echo $this->metabox_div_id; ?> #editorcontainer{background:#fff !important;}
            #<?php echo $this->metabox_div_id; ?> #<?php echo $editor_id; ?>_fullscreen{display:none;}
        </style>
    
        <script type='text/javascript'>
            jQuery(function($){
                $('#<?php echo $this->metabox_div_id; ?> #editor-toolbar > a').click(function(){
                        $('#<?php echo $this->metabox_div_id; ?> #editor-toolbar > a').removeClass('active');
                        $(this).addClass('active');
                });
                
                if($('#<?php echo $this->metabox_div_id; ?> #edButtonPreview').hasClass('active')){
                        $('#<?php echo $this->metabox_div_id; ?> #ed_toolbar').hide();
                }
                
                $('#$<?php echo $this->metabox_div_id; ?> #edButtonPreview').click(function(){
                        $('#<?php echo $this->metabox_div_id; ?> #ed_toolbar').hide();
                });
                
                $('#<?php echo $this->metabox_div_id; ?> #edButtonHTML').click(function(){
                        $('#<?php echo $this->metabox_div_id; ?> #ed_toolbar').show();
                });
				//Tell the uploader to insert content into the correct WYSIWYG editor
				$('#media-buttons a').bind('click', function(){
					var customEditor = $(this).parents('#<?php echo $this->metabox_div_id; ?>');
					if(customEditor.length > 0){
						edCanvas = document.getElementById('<?php echo $editor_id; ?>');
					}
					else{
						edCanvas = document.getElementById('content');
					}
				});
            });
        </script>
		<?php

		//Create The Editor
        $content = get_post_meta($post->ID, WYSIWYG_META_KEY, true);
        the_editor($content, $editor_id);
        ?>
        <div style='clear:both; display:block;'></div>
        <?php
	}

	/**
	 * Render Image Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_img_meta_box_content()
	{
		global $post;

		$editor_id = $this->metabox_div_id.'_editor';
		$values = get_post_custom( $post->ID );
		$value = isset( $values[$this->metabox_name] ) ? esc_attr( $values[$this->metabox_name][0] ) : '';

		// Add an nonce field so we can check for it later.
		wp_nonce_field( $this->metabox_label_name, $this->metabox_name.'_nonce' );

		?>
		<style type='text/css'>
            #<?php echo $this->metabox_div_id; ?> #edButtonHTML, #<?php echo $this->metabox_div_id; ?> #edButtonPreview {background-color: #F1F1F1; border-color: #DFDFDF #DFDFDF #CCC; color: #999;}
            #<?php echo $editor_id; ?>{width:100%;}
            #<?php echo $this->metabox_div_id; ?> #editorcontainer{background:#fff !important;}
            #<?php echo $this->metabox_div_id; ?> #<?php echo $editor_id; ?>_fullscreen{display:none;}
        </style>
    
        <script type='text/javascript'>
                jQuery(function($){
                        $('#<?php echo $this->metabox_div_id; ?> #editor-toolbar > a').click(function(){
                                $('#<?php echo $this->metabox_div_id; ?> #editor-toolbar > a').removeClass('active');
                                $(this).addClass('active');
                        });
                        
                        if($('#<?php echo $this->metabox_div_id; ?> #edButtonPreview').hasClass('active')){
                                $('#<?php echo $this->metabox_div_id; ?> #ed_toolbar').hide();
                        }
                        
                        $('#$<?php echo $this->metabox_div_id; ?> #edButtonPreview').click(function(){
                                $('#<?php echo $this->metabox_div_id; ?> #ed_toolbar').hide();
                        });
                        
                        $('#<?php echo $this->metabox_div_id; ?> #edButtonHTML').click(function(){
                                $('#<?php echo $this->metabox_div_id; ?> #ed_toolbar').show();
                        });
						//Tell the uploader to insert content into the correct WYSIWYG editor
						$('#media-buttons a').bind('click', function(){
							var customEditor = $(this).parents('#<?php echo $this->metabox_div_id; ?>');
							if(customEditor.length > 0){
								edCanvas = document.getElementById('<?php echo $editor_id; ?>');
							}
							else{
								edCanvas = document.getElementById('content');
							}
						});
                });
        </script>
		<?php

		//Create The Editor
        $content = get_post_meta($post->ID, WYSIWYG_META_KEY, true);
        the_editor($content, $editor_id);
        ?>
        <div style='clear:both; display:block;'></div>
        <?php
	}
}