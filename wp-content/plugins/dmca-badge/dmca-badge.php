<?php
/*
Plugin Name: DMCA Website Protection Badge
Plugin URI: https://www.dmca.com/WordPress/default.aspx?r=wpd1
Description: Protect your content with a DMCA.com Website Protection Badge. Our badges deter content theft, provide tracking of unauthorized usage (with account), and make takedowns easier and more effective. Visit the plugin site to learn more about DMCA Website Protection Badges, or to register.
Version:           1.9
Author:            DMCA.com
Author URI:        https://wordpress.org/plugins/dmca-badge/
Plugin URI:        https://www.dmca.com/WordPress/default.aspx?r=wpd
License: GPLv2
 */

require( dirname( __FILE__ ) . '/libraries/imperative/imperative.php' );

require_library( 'restian', '0.4.1',  __FILE__, 'libraries/restian/restian.php' );
require_library( 'sidecar', '0.5.1', __FILE__, 'libraries/sidecar/sidecar.php' );
require_library( 'dmca-api-client', '0.1.0', __FILE__, 'libraries/dmca-api-client/dmca-api-client.php' );

register_plugin_loader( __FILE__ );

define('DMCA_PLUGIN_URL', plugins_url('/', __FILE__) );


function dmca_custom_scripts_addition(){

    $screen = get_current_screen();

    if( $screen->id != 'settings_page_dmca-badge-settings' ) return;

    ?>

    <script>window.intercomSettings = { app_id: "ypgdx31r", messengerLocation: "wp-plugin" };</script>
    <script>(function(){var w=window;var ic=w.Intercom;if(typeof ic==="function"){ic('reattach_activator');ic('update',intercomSettings);}else{var d=document;var i=function(){i.c(arguments)};i.q=[];i.c=function(args){i.q.push(args)};w.Intercom=i;function l(){var s=d.createElement('script');s.type='text/javascript';s.async=true;s.src='https://widget.intercom.io/widget/ypgdx31r';var x=d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s,x);}if(w.attachEvent){w.attachEvent('onload',l);}else{w.addEventListener('load',l,false);}}})()</script>
    
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-16080641-10"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'UA-16080641-10');
    </script>
    <?php 
}
add_action( 'admin_footer', 'dmca_custom_scripts_addition' );