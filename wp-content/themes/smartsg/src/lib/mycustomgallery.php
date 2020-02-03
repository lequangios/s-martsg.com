<?php 
/**
   * Create Meta Box for Post and Page
   * 
   * @package    none
   * @subpackage none
   * @author     Quang Le <levietquangt2@gmail.com>
   */
class My_Custom_Gallery
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
       * @param string 		$metabox_type  			type of meta box : text, textarea, wysiwyg, img, multi_img
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
}