<?php 

class My_Custom_Post_Type
{
	public $id = '';
	public $cat = '';
	public $name = '';
	public $names = '';
	public $icon  = '';
	public $isTag = false;
	public $labelCat = NULL;
	public $arrCat = NULL;
	public $labelPost = NULL;
	public $arrPost = NULL;

	/**
	 * Hook into the appropriate actions when the class is constructed.
	 */
	public function __construct($id, $cat, $name, $names, $isTag, $icon) 
	{
		$this->id = $id;
		$this->cat = $cat;
		$this->name = $name;
		$this->names = $names;
		$this->isTag = $isTag;
		$this->icon = $icon;

		$this->configCustomPostTypedData();
	}

	/**
	 * Config Data for Custom post type.
	 */
	public function configCustomPostTypedData()
	{
		if($this->cat != false)
		{
			// Add new taxonomy, make it hierarchical (like categories)
			$this->labelCat = array(
				'name'              => _x( $this->names.' Categories', 'taxonomy plural name' ),
				'singular_name'     => _x( $this->name.' Category', 'taxonomy singular name' ),
				'search_items'      => __( 'Search '.$this->name.' Category' ),
				'all_items'         => __( $this->names.' Categories' ),
				'parent_item'       => __( 'Parent' ),
				'parent_item_colon' => __( 'Parent:' ),
				'edit_item'         => __( 'Edit '.$this->name.' Category' ),
				'update_item'       => __( 'Update '.$this->name.' Category' ),
				'add_new_item'      => __( 'Add '.$this->name.' Category' ),
				'new_item_name'     => __( $this->name.' Category Name' ),
				'menu_name'         => __( $this->names.' Categories' ),
			);

			$this->arrCat = array(
				'hierarchical'      => true,
				'labels'            => $this->labelCat,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'has_archive' 		=> $this->id,
				'rewrite'           => array( 'slug' => $this->id.'/'.$this->cat, 'with_front' => false ),
				'supports'          => array( 'author', 'thumbnail' )
			);

			// create taxonomies for the post type
			register_taxonomy($this->cat, array( $this->id ), $this->arrCat );
		}

		if($this->icon == '' || $this->icon == false)
		{
			$this->icon = get_template_directory_uri().'/assets/img/icons/Library.png';
		}
		else
		{
			$this->icon = get_template_directory_uri().'/assets/img/icons/'.$this->icon;
		}

		if($this->isTag == true)
		{
			$this->labelPost = array(
			    'name' => _x($this->names, 'post type general name'),
			    'singular_name' => _x($this->name, 'post type singular name'),
			    'add_new' => _x('Add New', $this->name),
			    'add_new_item' => __("Add New ".$this->name),
			    'view_item' => __("View ".$this->name),
			    'search_items' => __("Search ".$this->names),
			    'not_found' =>  __('No '.$this->names.' found'),
			    'not_found_in_trash' => __('No '.$this->names.' found in Trash'),
			    'parent_item_colon' => ''
			);
			$this->arrPost = array(
			    'labels' => $this->labelPost,
			    'public' => true,
			    'publicly_queryable' => true,
			    'show_ui' => true,
			    'query_var' => true,
			    'rewrite' => true,
			    'capability_type' => 'post',
			    'hierarchical' => false,
			    'menu_position' => null,
			    'menu_icon' => $this->icon,
				'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt'),
			    'taxonomies' => array('post_tag')
			);
		}
		else
		{
			$this->labelPost = array(
			    'name' => _x($this->names, 'post type general name'),
			    'singular_name' => _x($this->name, 'post type singular name'),
			    'add_new' => _x('Add New', $this->name),
			    'add_new_item' => __("Add New ".$this->name),
			    'view_item' => __("View ".$this->name),
			    'search_items' => __("Search ".$this->names),
			    'not_found' =>  __('No '.$this->names.' found'),
			    'not_found_in_trash' => __('No '.$this->names.' found in Trash'),
			    'parent_item_colon' => ''
			);
			$this->arrPost = array(
			    'labels' => $this->labelPost,
			    'public' => true,
			    'publicly_queryable' => true,
			    'show_ui' => true,
			    'query_var' => true,
			    'hierarchical' => false,
			    'menu_icon' => $this->icon,
				'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt')
			);
		}

		register_post_type($this->id,$this->arrPost);
		
	}
}

function get_My_Permalink_Custom_Post_ByCat($cat_slug, $post_type)
{
	return esc_url(get_term_link($cat_slug,$post_type));
}

function get_My_Custom_Post_Type_Categories($cat_slug)
{	
	$arr = array();
	$terms = get_terms($cat_slug);
	foreach($terms as $term){
		array_push($arr,array(
			'ID'	=> $term->term_id,
			'name' 	=> $term->name,
			'slug' 	=> $term->slug,
			'taxonomy'=>$term->taxonomy
		));
		
	}
	return $arr;
}

function get_My_Custom_Post_Type($posts_per_page, $post_type, $cat_slug)
{
	global $wp_query;
	
	$posts_per_page==NULL?$posts_per_page=9999:$posts_per_page;
	
	$paged = $wp_query->query_vars['paged'];
	$loop = new WP_Query( array( 'post_type' => $post_type,
								 'category_name'=> $cat_slug,
								 'posts_per_page' => $posts_per_page, 
								 'paged' => $paged) ); 
	
	wp_reset_query();

	return $loop;
}

function add_New_My_Custom_Post_Type_Post($title, $slug, $content, $post_type, $addToMenu, $parent_name, $type="custom")
{
	$menu_id = get_my_menu_id(MENU_NAME);
	$parent_id = get_my_menu_item_id(MENU_NAME, $parent_name);
	$term_id = get_my_menu_item_id(MENU_NAME, $title);

	$new_post_id = -1;
	$post = get_page_by_path($slug, OBJECT, $post_type );
	if($post == NULL)
	{
		$thepost = array(
	        'post_title' 	=> $title,
	        'post_status' 	=> 'publish',
	        'post_name'		=> $slug,
	        'post_type' 	=> $post_type,
	        'post_content' 	=> $content,
	        'post_author' 	=> 1,
	        'post_category' => array(7,8)
	    );

	    $new_post_id=wp_insert_post($thepost);

	    // Ad it to menu at parent name position
	    if($addToMenu == true && $term_id == false)
		{
			
			$url = get_post_permalink($new_post_id);
			add_new_my_menu_item($menu_id, $title, $url, $parent_id, $type);
		}
	}
	else
	{
		if($addToMenu == true && $term_id == false)
		{
			$url = get_post_permalink($post->ID);
			$title = $post->post_title;
			add_new_my_menu_item($menu_id, $title, $url, $parent_id, $type);
		}
	}

	return $new_post_id;
}