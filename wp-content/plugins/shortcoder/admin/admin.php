<?php

if( ! defined( 'ABSPATH' ) ) exit;

class SC_Admin{

    public static function init(){
        
        add_action( 'init', array( __class__, 'register_post_type' ), 0 );

        add_action( 'init', array( __class__, 'register_taxonomy' ), 0 );

        add_action( 'admin_enqueue_scripts', array( __class__, 'enqueue_scripts' ) );

        add_action( 'admin_footer', array( __class__, 'changelog' ) );

        add_action( 'admin_footer', array( __class__, 'import_export' ) );

        add_action( 'wp_ajax_sc_admin_ajax', array( __class__, 'admin_ajax' ) );

    }

    public static function register_post_type(){

        $labels = array(
            'name'                  => _x( 'Shortcoder', 'Post Type General Name', 'sc' ),
            'singular_name'         => _x( 'Shortcode', 'Post Type Singular Name', 'sc' ),
            'menu_name'             => __( 'Shortcoder', 'sc' ),
            'name_admin_bar'        => __( 'Shortcode', 'sc' ),
            'archives'              => __( 'Shortcode Archives', 'sc' ),
            'attributes'            => __( 'Shortcode Attributes', 'sc' ),
            'parent_item_colon'     => __( 'Parent Shortcode:', 'sc' ),
            'all_items'             => __( 'All Shortcodes', 'sc' ),
            'add_new_item'          => __( 'Create shortcode', 'sc' ),
            'add_new'               => __( 'Create shortcode', 'sc' ),
            'new_item'              => __( 'New Shortcode', 'sc' ),
            'edit_item'             => __( 'Edit Shortcode', 'sc' ),
            'update_item'           => __( 'Update Shortcode', 'sc' ),
            'view_item'             => __( 'View Shortcode', 'sc' ),
            'view_items'            => __( 'View Shortcodes', 'sc' ),
            'search_items'          => __( 'Search Shortcode', 'sc' ),
            'not_found'             => __( 'Not found', 'sc' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'sc' ),
            'featured_image'        => __( 'Featured Image', 'sc' ),
            'set_featured_image'    => __( 'Set featured image', 'sc' ),
            'remove_featured_image' => __( 'Remove featured image', 'sc' ),
            'use_featured_image'    => __( 'Use as featured image', 'sc' ),
            'insert_into_item'      => __( 'Insert into shortcode', 'sc' ),
            'uploaded_to_this_item' => __( 'Uploaded to this shortcode', 'sc' ),
            'items_list'            => __( 'Shortcodes list', 'sc' ),
            'items_list_navigation' => __( 'Shortcodes list navigation', 'sc' ),
            'filter_items_list'     => __( 'Filter shortcodes list', 'sc' ),
        );

        $args = array(
            'label'                 => __( 'Shortcode', 'sc' ),
            'labels'                => $labels,
            'supports'              => false,
            'taxonomies'            => array( 'sc_tag' ),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 25,
            'menu_icon'             => '',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'show_in_rest'          => false,
            'map_meta_cap'          => true,
            'capability_type'       => 'shortcoder',
        );

        register_post_type( SC_POST_TYPE, $args );

    }

    public static function register_taxonomy(){

        $labels = array(
            'name'                       => _x( 'Tags', 'Taxonomy General Name', 'sc' ),
            'singular_name'              => _x( 'Tag', 'Taxonomy Singular Name', 'sc' ),
            'menu_name'                  => __( 'Tags', 'sc' ),
            'all_items'                  => __( 'All Tags', 'sc' ),
            'parent_item'                => __( 'Parent Tag', 'sc' ),
            'parent_item_colon'          => __( 'Parent Tag:', 'sc' ),
            'new_item_name'              => __( 'New Tag Name', 'sc' ),
            'add_new_item'               => __( 'Add New Tag', 'sc' ),
            'edit_item'                  => __( 'Edit Tag', 'sc' ),
            'update_item'                => __( 'Update Tag', 'sc' ),
            'view_item'                  => __( 'View Tag', 'sc' ),
            'separate_items_with_commas' => __( 'Separate tags with commas', 'sc' ),
            'add_or_remove_items'        => __( 'Add or remove tags', 'sc' ),
            'choose_from_most_used'      => __( 'Choose from the most used', 'sc' ),
            'popular_items'              => __( 'Popular Tags', 'sc' ),
            'search_items'               => __( 'Search Tags', 'sc' ),
            'not_found'                  => __( 'Not Found', 'sc' ),
            'no_terms'                   => __( 'No tags', 'sc' ),
            'items_list'                 => __( 'Tags list', 'sc' ),
            'items_list_navigation'      => __( 'Tags list navigation', 'sc' ),
        );
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => false,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => false,
            'show_tagcloud'              => false,
            'show_in_rest'               => false,
        );

        register_taxonomy( 'sc_tag', array( SC_POST_TYPE ), $args );
        
    }

    public static function is_sc_admin_page(){

        $screen = get_current_screen();

        if( $screen && $screen->post_type == SC_POST_TYPE ){
            return true;
        }else{
            return false;
        }

    }

    public static function inline_js_variables(){

        return array(
            'sc_version' => SC_VERSION,
            'ajax_url' => get_admin_url() . 'admin-ajax.php',
            'screen' => get_current_screen(),
            'text_editor_switch_notice' => __( 'Switching editor will refresh the page. Please save your changes before refreshing. Do you want to refresh the page now ?', 'sc' )
        );

    }

    public static function enqueue_scripts( $hook ){

        wp_enqueue_style( 'sc-icon-css', SC_ADMIN_URL . 'css/menu-icon.css', array(), SC_VERSION );

        if( !self::is_sc_admin_page() ){
            return false;
        }

        wp_enqueue_style( 'sc-admin-css', SC_ADMIN_URL . 'css/style.css', array(), SC_VERSION );

        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'sc-admin-js', SC_ADMIN_URL . 'js/script.js', array( 'jquery' ), SC_VERSION );

        wp_localize_script( 'sc-admin-js', 'SC_VARS', self::inline_js_variables() );

    }

    public static function admin_ajax(){

        $g = self::clean_get();
        $do = $g[ 'do' ];

        if( $do == 'close_changelog' ){
            update_option( 'shortcoder_last_changelog', SC_VERSION );
            echo 'done';
        }

        die( 0 );

    }

    public static function changelog(){

        if( !self::is_sc_admin_page() ){
            return false;
        }

        $last_changelog = get_option( 'shortcoder_last_changelog' );

        if( $last_changelog && version_compare( $last_changelog, SC_VERSION, '>=' ) ){
            return false;
        }

        $response = wp_remote_get( 'https://raw.githubusercontent.com/vaakash/vaakash.github.io/master/misc/shortcoder/changelogs/' . SC_VERSION . '.html' );
        $changelog = false;

        if( !is_wp_error( $response ) && $response[ 'response' ][ 'code' ] == 200 ){
            $changelog = wp_remote_retrieve_body( $response );
        }

        if( !$changelog ){
            update_option( 'shortcoder_last_changelog', SC_VERSION );
            return false;
        }

        echo '<div class="sc_changelog"><main>
        <article>' . $changelog . '</article>
        <footer><button href="#" class="button button-primary dismiss_btn">' . __( 'Continue using Shortcoder', 'sc' ) . '</a></footer>
        </main></div>';

    }

    public static function import_export(){

        if( !self::is_sc_admin_page() ){
            return false;
        }

        $screen = get_current_screen();
        if( $screen->base != 'edit' ){
            return false;
        }

        echo '<div id="ie_content" class="hidden"><div>
<div id="contextual-help-back"></div>
<div id="contextual-help-columns">
    <div class="contextual-help-tabs">
        <ul>
            <li class="active"><a href="#export-tab" aria-controls="export-tab">Export</a></li>
            <li><a href="#import-tab" aria-controls="import-tab">Import</a></li>
            <li><a href="#import-others-tab" aria-controls="import-others-tab">Import from other sources</a></li>
        </ul>
    </div>
    <div class="contextual-help-sidebar"><p><a href="https://www.aakashweb.com/docs/shortcoder-doc/" target="_blank">Documentation</a></p></div>
    <div class="contextual-help-tabs-wrap">
        <div id="export-tab" class="help-tab-content active">
        <h3>' . __( 'Export', 'sc' ) . '</h3><p>' . __( 'WordPress has a native exporter tool which can be used to export shortcoder data. Navigate to <code>Tools -> Export</code> and select "Shortcoder" as the content to export.', 'sc' ) . '</p>
        <a href="' . admin_url( 'export.php' ) . '" class="button button-primary">' . __( 'Go to export page', 'sc' ) . '</a>
        </div>
        <div id="import-tab" class="help-tab-content">
        <h3>' . __( 'Import', 'sc' ) . '</h3><p>' . __( 'The XML file downloaded through the native export process can be imported via WordPress\'s own import tool. Navigate to <code>Tools -> Import</code>, install the importer plugin if not installed and run the importer under WordPress section.', 'sc' ) . '</p>
        <a href="' . admin_url( 'import.php' ) . '" class="button button-primary">' . __( 'Go to import page', 'sc' ) . '</a>
        </div>
        <div id="import-others-tab" class="help-tab-content">
        <h3>' . __( 'Import from other sources', 'sc' ) . '</h3><p>' . __( 'To import from other sources like CSV, excel please read the below linked documentation.', 'sc' ) . '</p>
        <a href="https://www.aakashweb.com/docs/shortcoder-doc/import-export/" target="_blank" class="button button-primary">' . __( 'Open documentation', 'sc' ) . '</a>
        </div>
    </div>
</div>
        </div></div>';

    }

    public static function clean_get(){
        
        foreach( $_GET as $k => $v ){
            $_GET[$k] = sanitize_text_field( $v );
        }

        return $_GET;
    }

}

SC_Admin::init();

?>