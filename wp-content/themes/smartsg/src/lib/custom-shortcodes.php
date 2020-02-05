<?php 
function mbGoogleMap_shortcode($args, $content) {
    $a = shortcode_atts( array(
		'style' => 'something',
        'content' => 'something else',
        'backgourd_img' => '',
        'backgourd_color' => ''
    ), $atts );

    return '<iframe width="600" height="450" frameborder="0" style="border:0" src="https://www.google.com/maps/embed/v1/place?q=place_id:ChIJ0T2NLikpdTERKxE8d61aX_E&key=AIzaSyAZYK7ntrrtCscRGBUebIGEjVeh9s1qOEU" allowfullscreen></iframe>';
}
add_shortcode( 'mbGoogleMap', 'mbGoogleMap_shortcode' );