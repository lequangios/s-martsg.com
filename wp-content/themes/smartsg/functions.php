<?php 
date_default_timezone_set('Asia/Ho_Chi_Minh');
add_filter('show_admin_bar', '__return_false');

define('THEME_VERSION',                 "1.0"                           );
define('THEME_PATH',                    'https://s-martsg.com/wp-content/themes/smartsg'        );
define('THEME_URL',                     esc_url( home_url( '/' ) )      );
define('THEME_URI',                     'https://s-martsg.com/wp-content/themes/smartsg'    );
define('THEME_ASSETS',                  THEME_URI.'/assets'             );
define('THEME_PATH_PLUGIN',             THEME_PATH.'/plugin'            );
define('THEME_URL_PLUGIN',              THEME_URI .'/plugin'            );
define('BASE_UPLOAD',                   '/wp-content/uploads'           );
define('OPTIONS_FRAMEWORK_DIRECTORY',   THEME_PATH_PLUGIN . '/options/' );

include "src/lib/custommetabox.php";
include "src/lib/customgroupmetabox.php";
include "src/lib/custom-shortcodes.php";

add_action( 'wp_enqueue_scripts', 'gold_essentials_enqueue_styles' );
function gold_essentials_enqueue_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' ); 
} 


function gold_essentials_google_fonts() {
	wp_enqueue_style( 'gold-essentials-google-fonts', '//fonts.googleapis.com/css?family=Noto+Serif:400,700|Open+Sans:400,600,700&display=swap', false ); 
}
add_action( 'wp_enqueue_scripts', 'gold_essentials_google_fonts' );


function gold_essentials_customize_register( $wp_customize ) {
	$wp_customize->add_section( 'sidebar_settings', array(
		'title'      => __('Sidebar Settings','gold-essentials'),
		'priority'   => 1,
		'capability' => 'edit_theme_options',
		) );

	$wp_customize->add_setting( 'sidebar_headline_color', array(
		'default'           => '#000',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
		) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sidebar_headline_color', array(
		'label'       => __( 'Headline Color', 'gold-essentials' ),
		'section'     => 'sidebar_settings',
		'priority'   => 1,
		'settings'    => 'sidebar_headline_color',
		) ) );

	$wp_customize->add_setting( 'sidebar_link_color', array(
		'default'           => '#000',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
		) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sidebar_link_color', array(
		'label'       => __( 'Link Color', 'gold-essentials' ),
		'section'     => 'sidebar_settings',
		'priority'   => 1,
		'settings'    => 'sidebar_link_color',
		) ) );

	$wp_customize->add_setting( 'sidebar_text_color', array(
		'default'           => '#333',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
		) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sidebar_text_color', array(
		'label'       => __( 'Text Color', 'gold-essentials' ),
		'section'     => 'sidebar_settings',
		'priority'   => 1,
		'settings'    => 'sidebar_text_color',
		) ) );

	$wp_customize->add_setting( 'sidebar_border_color', array(
		'default'           => '#c69c6d',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'postMessage',
		) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sidebar_border_color', array(
		'label'       => __( 'Border Color', 'gold-essentials' ),
		'section'     => 'sidebar_settings',
		'priority'   => 1,
		'settings'    => 'sidebar_border_color',
		) ) );


}
add_action( 'customize_register', 'gold_essentials_customize_register' );
if(! function_exists('gold_essentials_customizer_css_final_output' ) ):
	function gold_essentials_customizer_css_final_output(){
		?>

		<style type="text/css">
			#smobile-menu.show .main-navigation ul ul.children.active, #smobile-menu.show .main-navigation ul ul.sub-menu.active, #smobile-menu.show .main-navigation ul li, .smenu-hide.toggle-mobile-menu.menu-toggle, #smobile-menu.show .main-navigation ul li, .primary-menu ul li ul.children li, .primary-menu ul li ul.sub-menu li, .primary-menu .pmenu, .super-menu { border-color: <?php echo esc_attr(get_theme_mod( 'navigation_border_color')); ?>; border-bottom-color: <?php echo esc_attr(get_theme_mod( 'navigation_border_color')); ?>; }
			#secondary .widget h3, #secondary .widget h3 a, #secondary .widget h4, #secondary .widget h1, #secondary .widget h2, #secondary .widget h5, #secondary .widget h6 { color: <?php echo esc_attr(get_theme_mod( 'sidebar_headline_color')); ?>; }
			#secondary .widget a, #secondary a, #secondary .widget li a , #secondary span.sub-arrow{ color: <?php echo esc_attr(get_theme_mod( 'sidebar_link_color')); ?>; }
			#secondary, #secondary .widget, #secondary .widget p, #secondary .widget li, .widget time.rpwe-time.published { color: <?php echo esc_attr(get_theme_mod( 'sidebar_text_color')); ?>; }
			#secondary .swidgets-wrap, #secondary .widget ul li, .featured-sidebar .search-field { border-color: <?php echo esc_attr(get_theme_mod( 'sidebar_border_color')); ?>; }
			.site-info, .footer-column-three input.search-submit, .footer-column-three p, .footer-column-three li, .footer-column-three td, .footer-column-three th, .footer-column-three caption { color: <?php echo esc_attr(get_theme_mod( 'footer_text_color')); ?>; }

			body, 
			.site, 
			.swidgets-wrap h3, 
			.post-data-text { background: <?php echo esc_attr(get_theme_mod( 'website_background_color')); ?>; }
			.site-title a, 
			.site-description { color: <?php echo esc_attr(get_theme_mod( 'header_logo_color')); ?>; }
			.sheader { background: <?php echo esc_attr(get_theme_mod( 'header_background_color')); ?> }
		</style>
		<?php }
		add_action( 'wp_head', 'gold_essentials_customizer_css_final_output' );
		endif;


if ( ! function_exists( 'smartsg_setup' ) ) :
	function smartsg_setup() {
		$qrcode = new My_Custom_Meta_Box(array('post'), 'product_qr_code', 'Mã QRCode của sản phẩm', 'product_qr_code', 'qr_code', 'normal');
		new My_Custom_Group_Meta_Box(array('post'), 'my-price-meta-group', 'Loại & Giá Thành', 'my_price_content', 'homePageMetaGroup');
	}
	add_action( 'after_setup_theme', 'smartsg_setup' );
endif;

if ( ! function_exists( 'smarsg_scripts' ) ) :
	function smarsg_scripts()
	{
		$dept = array();
		wp_enqueue_style( 'myStyle'              , THEME_ASSETS . '/css/custom.css', $dept, THEME_VERSION );

		wp_enqueue_script('myDataModel'	    	 , THEME_ASSETS . '/js/DataModel.js',$dept,THEME_VERSION,false);
		wp_enqueue_script('myKnockoutJS'	     , THEME_ASSETS . '/js/knockout-3.5.1.js',$dept,'3.5.1',false);
	}
endif;
add_action('admin_enqueue_scripts', 'smarsg_scripts');