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

function wpb_load_widget() {
    register_widget( 'wpb_widget' );
}
add_action( 'widgets_init', 'wpb_load_widget' );



// Creating the widget 
class wpb_widget extends WP_Widget {
 
    function __construct() {
        parent::__construct(
            
        // Base ID of your widget
        'wpb_widget', 
            
        // Widget name will appear in UI
        __('Giỏ Hàng', 'wpb_widget_domain'), 
            
        // Widget description
        array( 'description' => __( 'Thêm shortcut đặt hàng nhanh cho sản phẩm', 'wpb_widget_domain' ), ) 
        );
    }
        
    // Creating widget front-end
        
    public function widget( $args, $instance ) {
        $title = apply_filters( 'widget_title', $instance['title'] );
        
        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if ( ! empty( $title ) )
        echo $args['before_title'] . $title . $args['after_title'];
        $post_meta = json_decode($instance['post_value']);
        $wp_image = $instance['wp_image'];
        $id_name = round(microtime(true));
        // This is where you run the code and display the output
        ?>
        <a href="javascript:onlick_<?php echo $id_name; ?>()">
            <img width="1280" height="960" src="<?php echo $wp_image; ?>" class="image attachment-full size-full" alt="" style="max-width: 100%; height: auto;" title="<?php echo $title ?>">
        </a>
        <script type="text/javascript">
            
            function onlick_<?php echo $id_name; ?>() {
                if (typeof(Storage) !== "undefined") {
                    // Store
                    sessionStorage.setItem("product_name", "<?php echo $post_meta->{'pTitle'}  ?>");
                    sessionStorage.setItem("product_link", "<?php echo esc_url( $post_meta->{'pLink'} ); ?>");
                    sessionStorage.setItem("product_meta", '<?php echo addslashes($post_meta->{'pMeta'}) ?>');
                    sessionStorage.setItem("product_qrcode", '<?php echo addslashes($post_meta->{'pMetaQR'}) ?>');
                    sessionStorage.setItem("product_order", "0");
                } else {
                    
                }
                document.location = '/dat-hang';
            }
        </script>
        <?php
        
        echo $args['after_widget'];
    }
                
    // Widget Backend 
    public function form( $instance ) {
        $title = ! empty( $instance['title'] ) ? $instance['title'] : 'ABC 123';
        $wp_image = ! empty( $instance['wp_image'] ) ? $instance['wp_image'] : '';
        $post_value = ! empty( $instance['post_value'] ) ? $instance['post_value'] : '';

        global $post;
        $args = array( 'posts_per_page' => 30 );
        $lastposts = get_posts( $args );
        // Widget admin form
        ?>
        <p>
            <p>
                <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
                <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
            </p>
            <p>
                <select class="widefat" id="<?php echo $this->get_field_id( 'post_value' ); ?>" name="<?php echo $this->get_field_name( 'post_value' ); ?>">
                <?php 
                foreach ($lastposts as $post) {
                    $pMeta = get_post_meta($post->ID, 'my_price_content', true ); 
                    $pMetaQR = get_post_meta($post->ID, 'product_qr_code', true ); 
                    $pTitle = $post->post_title;
                    $pLink = get_permalink($post->ID);
                    $data = array();
                    $data["pMeta"] = $pMeta;
                    $data["pMetaQR"] = $pMetaQR;
                    $data["pTitle"] = $pTitle;
                    $data["pLink"] = $pLink;

                    ?>
                    <option value='<?php echo json_encode($data); ?>'><?php echo $post->post_title ?></option>
                    <?php
                }
                ?>
                </select>
            </p>
            <p>
                <label for="<?php echo $this->get_field_id( 'wp_image' ); ?>"><?php _e( 'Image:' ); ?></label> 
                <img src="<?php echo esc_attr( $wp_image ); ?>" width="150" height="150" style="display:block"/>
                <input type="hidden" value="<?php echo esc_attr( $wp_image ); ?>" class="widefat regular-text process_custom_images" id="<?php echo $this->get_field_id( 'wp_image' ); ?>" name="<?php echo $this->get_field_name( 'wp_image' ); ?>">
                <button class="set_custom_images_widefat button">Set Image</button>
            </p>
            <p>
            </p>
        </p>
        <script type="text/javascript">
            jQuery(document).ready(function() {
                var $ = jQuery;
                if ($('.set_custom_images_widefat').length > 0) {
                    if ( typeof wp !== 'undefined' && wp.media && wp.media.editor) {
                        $('.set_custom_images_widefat').on('click', function(e) {
                            e.preventDefault();
                            var button = $(this);
                            var id = button.prev();
                            var img = id.prev();
                            wp.media.editor.send.attachment = function(props, attachment) {
                                id.val(attachment.url);
                                img.attr('src', attachment.url);
                            };
                            wp.media.editor.open(button);
                            return false;
                        });
                    }
                }
            });
        </script>
        <?php 
    }
            
    // Updating widget replacing old instances with new
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['wp_image'] = ( ! empty( $new_instance['wp_image'] ) ) ? strip_tags( $new_instance['wp_image'] ) : '';
        $instance['post_value'] = ( ! empty( $new_instance['post_value'] ) ) ? ( $new_instance['post_value'] ) : '';
        return $instance;
    }
} // Class wpb_widget ends here