<?php

/**
 * Calls the class on the post edit screen.
 */
function callMetaBoxGallery() {
	new Meta_Box_Gallery();
}

if ( is_admin() ) {
	// add_action( 'load-post.php', 'callMetaBoxGallery' );
	// add_action( 'load-post-new.php', 'callMetaBoxGallery' );
}

/** 
 * The Class.
 */
class Meta_Box_Gallery {

	public $gallery_image_size;
	public $post_type_arr;
	public $post_id;
	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct($gallery_image_size = 'full', $post_type_arr = array('post', 'page'), $post_id = false) {

		$this->gallery_image_size = $gallery_image_size;
		$this->post_type_arr = $post_type_arr;
		$this->post_id = $post_id;
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save' ) );
	}

	/**
	 * Adds the meta box container.
	 */
	public function add_meta_box( $post_type ) {

		global $post;
		if($this->post_id != false && $this->post_id != $post->ID)
		{
			return;
		}

		if ( in_array( $post_type, $this->post_type_arr  )) {
			add_meta_box(
				GALLERY_META_BOX // id of metabox
				,__( 'Dairu Galleries' )
				,array( $this, 'render_meta_box_content' )
				,$post_type
				,'side'
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

		// Check if our nonce is set.
		if ( ! isset( $_POST['dariu_gallery_nonce'] ) )
			return $post_id;

		$nonce = $_POST['dariu_gallery_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'dariu_gallery' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
				//     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// Sanitize the user input.
		$mydata = sanitize_text_field( $_POST['dariu_galleries'] );

		// Update the meta field.
		update_post_meta( $post_id, 'postmeta_dariu_galleries', $mydata );
	}


	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 */
	public function render_meta_box_content( $post ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'dariu_gallery', 'dariu_gallery_nonce' );

		// Get WordPress' media upload URL
		$upload_link = esc_url( get_upload_iframe_src( 'image', $post->ID ) );

		// See if there's a media id already saved as post meta
		$prodGalleries = get_post_meta( $post->ID, 'postmeta_dariu_galleries', true );
		// {'gallery':[{'id':'0', 'title':'title', url:'url'}]}
		if($prodGalleries == NULL)
		{
			// $prodGalleries = '{"gallery":[{"id":"0", "title":"title", "url":"url"}]}';
			$prodGalleries = '{"gallery":[]}';
		}
		$prodGalleriesArr = explode(',', $prodGalleries);

		?>
		<div id="dariu-galleries-container">
			<!-- Your image container, which can be manipulated with js -->
			<input type="hidden" id="gallery_size" value="<?php echo $this->gallery_image_size; ?>"/>
			<input type="hidden" id="gallery_data_server" value="<?php echo esc_attr($prodGalleries); ?>"/>

			<div class="dariu-galleries-container">
				<!-- ko foreach: galleryArray -->
			        <div class="dariu-galleries-list">
						<div class="dariu-gallery-item">
							<img src="" alt="" data-bind="attr:{ src: url }"/>
						</div>
						<a class="dariu-gallery-btn-del" data-bind="event:{click:$parent.removeEntry}">
							<?php _e('Remove') ?>
						</a>
					</div>
			    <!-- /ko -->
				<!--
				<?php
					for( $i=0; $i<sizeof($prodGalleriesArr); $i++ ):
						// Get the image src
						$your_img_src = wp_get_attachment_image_src( $prodGalleriesArr[$i], 'thumbnail' );
				?>
					<?php if ( is_array( $your_img_src ) ) : ?>
						
						<div class="dariu-galleries-list">
							<div class="dariu-gallery-item">
								<img src="<?php echo $your_img_src[0] ?>" alt="" />
							</div>
							<a class="dariu-gallery-btn-del" href="#" attachmentid="<?php echo $prodGalleriesArr[$i];?>">
								<?php _e('Remove') ?>
							</a>
						</div>
					<?php endif; ?>
				<?php endfor; ?>
				-->
			</div>
			<div class="clear"></div>

			<!-- Your add & remove image links -->
			<p class="hide-if-no-js">
				<a class="dariu-gallery-upload-link" href="<?php echo $upload_link ?>" data-bind="event:{click:addMediaImage}">
					<?php _e('Add gallery') ?>
				</a>
			</p>

			<!-- A hidden input to set and post the chosen image id -->
			<input class="dariu-galleries" name="dariu_galleries" id="dariu_galleries" data-bind="attr:{value:galleryData}" type="hidden" value="<?php echo esc_attr( $prodGalleries ); ?>" />
		</div>
		<?php
	}

}

function getDariuGallery($id,$size = 'full')
{
	$galleriesStr = get_post_meta($id, 'postmeta_dariu_galleries', true);
	$galleriesArr = explode(',', $galleriesStr);
	$galleryList = array();
	for( $i=0; $i<sizeof($galleriesArr); $i++ )
	{
		$mediaId = trim($galleriesArr[$i]);
		$img = wp_get_attachment_image( (int)$mediaId, HOME_GALLERY_SIZE );
		array_push($galleryList, $img);
	}
	return $galleryList;
}