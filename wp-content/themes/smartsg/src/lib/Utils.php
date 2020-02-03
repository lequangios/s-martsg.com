<?php

/* Tool For Menu */
function get_my_menu_id($menu_name)
{
	$menu_obj = wp_get_nav_menu_object($menu_name);
	if($menu_obj) return $menu_obj->term_id;
	else return -1;
}

function get_my_menu_item_id($menu_name, $item_name)
{
	$item = false;
	$array_menu = wp_get_nav_menu_items($menu_name);
	if(!is_array($array_menu)) return;
	foreach ($array_menu as $m)
	{
		if ($m->title == $item_name) 
		{
			$item = $m->ID;
			break;
		}
	}
	return $item;
}

function add_new_my_menu_item($menu_id, $title, $url, $parent_id, $type='page')
{

	$arrs = array('menu-item-title' 		=>  __($title),
		        	  'menu-item-parent-id' => $parent_id,
		              'menu-item-url' 		=> $url, 
		              'menu-item-type' 		=> $type,
		              'menu-item-status' 	=> 'publish');

	$id = wp_update_nav_menu_item($menu_id, 0, $arrs);
	return $id;
}

function update_my_menu_item($menu_name, $title, $url, $parent_id = false, $type='page')
{
	$menu_id = get_my_menu_id($menu_name);

	$item_id = get_my_menu_item_id($menu_name, $title);

	if($item_id != false)
	{
		if($parent_id == false)
		{
			$arrs = array('menu-item-title' 	=>  __($title),
						  'menu-item-type' 		=> $type,
		              	  'menu-item-url' 		=> $url);

			wp_update_nav_menu_item($menu_id, $item_id, $arrs);
		}
		else
		{
			$arrs = array('menu-item-title' 	=>  __($title),
					  'menu-item-parent-id' => $parent_id,
					  'menu-item-type' 		=> $type,
		              'menu-item-url' 		=> $url);

			wp_update_nav_menu_item($menu_id, $item_id, $arrs);
		}
		
	}
}

/* Tool for Page */
function add_New_Custom_Page($title, $slug, $template, $addToMenu, $menu_name, $parent_name)
{
	$menu_id = get_my_menu_id($menu_name);
	$parent_id = get_my_menu_item_id($menu_name, $parent_name);

	$page = get_page_by_path($slug, OBJECT, "page");
	if($page == NULL)
	{
		$thepage = array(
	        'post_title' => $title,
	        'post_status' => 'publish',
	        'post_name'	=> $slug,
	        'post_type' => 'page',
	        'post_author' => 1,
	        'post_category' => array(7,8)
	    );

	    $page_id=wp_insert_post($thepage);
		update_post_meta( $page_id, '_wp_page_template', $template);

		if($addToMenu == true)
		{
			$url = get_post_permalink($page_id);
			add_new_my_menu_item($menu_id, $title, $url, $parent_id);
		}
		add_option( $slug , $page_id, '', 'yes' );
		return $page_id;
	}
	else
	{
		$url = get_post_permalink($page->ID);
		update_my_menu_item($menu_name, $title, $url, $parent_id);
		update_option( $slug , $page->ID, '', 'yes' );
		return $page->ID; 
	}

}

/* Tool for Submit Contact */
// add_action( 'wp_ajax_nopriv_add_new_contact', 'add_new_contact' );
// add_action( 'wp_ajax_add_new_contact', 'add_new_contact' );
// function add_new_contact(){
// 	// Check Data
// 	if(( isset( $_POST['user_email'] ) )&&(esc_attr( $_POST['user_email'] ) != ''))
// 	{
// 		$email = esc_attr( $_POST['user_email'] );
// 		$isEmail = false;

// 		if (!$isEmail) {
// 			// Create Contact
// 		    $contacter = array(
// 		        'post_title' => 'contact ' + time(),
// 		        'post_status' => 'publish',
// 		        'post_type' => 'contact',
// 		        'post_author' => 1,
// 		        'post_category' => array(7,8)
// 		    );

// 		    $contacter_id=wp_insert_post($contacter);

// 		    if($contacter_id != 0){
// 		    	if( isset( $_POST['user_email'] ) )
// 			            update_post_meta( $contacter_id , 'user-email', esc_attr( $_POST['user_email'] ) );

// 			    if( isset( $_POST['user-name'] ) )
// 			            update_post_meta( $contacter_id , 'user-name', esc_attr( $_POST['user-name'] ) );

// 			    if( isset( $_POST['user-message'] ) )
// 			            update_post_meta( $contacter_id , 'user-message', esc_attr( $_POST['user-message'] ) );
			        
// 			    echo DARIU_EMAIL_HAD_SUBMIT;
// 		    }
// 		    else echo DARIU_EMAIL_INVALID;
// 		}
// 	}
// 	else
// 	{
// 		echo DARIU_EMAIL_INVALID;
// 	}
// 	die();
// }

// For body class
add_filter('body_class', 'my_custom_body_class');

function my_custom_body_class($classes) 
{
	$classes[] = 'dariu_website';
    return $classes;
}