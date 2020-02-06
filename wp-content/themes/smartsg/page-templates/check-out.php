<?php
/**
 * Template Name: Check Out Page
 *
 * @package WordPress
 * @subpackage s-martsg
 * @since s-martsg 1.0
 */

get_header(); ?>

<div id="primary" class="featured-content content-area">
    <main id="main" class="site-main">
        <article  <?php post_class('posts-entry fbox'); ?>>
            <header class="entry-header">
                <?php
                if ( is_singular() ) :
                    the_title( '<h1 class="entry-title">', '</h1>' );
                else :
                    the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
                endif;

                if ( 'page' === get_post_type() ) : ?>
                <div class="entry-meta">
                    <div class="blog-data-wrapper">
                        <div class="post-data-divider"></div>
                        <div class="post-data-positioning">
                            <div class="post-data-text">
                                <?php minimalistblogger_posted_on(); ?>
                            </div>
                        </div>
                    </div>
                </div><!-- .entry-meta -->
                <?php
                endif; ?>
            </header><!-- .entry-header -->
            <div class="entry-content">
                <p><strong><?php _e('Bạn đang ở đâu ?'); ?></strong></p>
                <figure class="wp-block-image">
                    <a href="">
                        <img src="<?php echo get_template_directory();?>/js/vt_hochiminh.png" alt="" class="wp-image-782">
                    </a>
                </figure>
            </div>
        </article>
        
    </main><!-- #main -->
</div><!-- #primary -->

<?php 
get_sidebar();
get_footer(); ?>