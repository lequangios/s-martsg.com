<?php
/**
 * Template Name: Order Page
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
                    the_title( '<h1 id="product-name" class="entry-title">', '</h1>' );
                else :
                    the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
                endif;

                if ( 'page' === get_post_type() ) : ?>
                <div class="entry-meta">
                    <div class="blog-data-wrapper">
                        <div class="post-data-divider"></div>
                        <div class="post-data-positioning">
                            <div class="post-data-text">
                            <div class="frm_message"><p>Cảm ơn quý khách hàng đã đặt mua chúng tôi sẽ liên hệ ngay đến quý khách hàng !</p></div>
                            </div>
                        </div>
                    </div>
                </div><!-- .entry-meta -->
                <?php
                endif; ?>
            </header><!-- .entry-header -->
            <div class="entry-content">
                <p></p>
                <figure class="wp-block-table">
                    <table class="" style="margin-top: 20px;">
                        <style>table, th, td { border: 1px solid black;}</style>
                        <tbody>
                            <tr><td>Tên Khách Hàng</td><td>Lê Văn A</td></tr>
                            <tr><td>Địa Chỉ</td><td>123, Võ Văn Kiệt, Phường 1, Quận 5, TP Hồ Chi Minh</td></tr>
                            <tr><td>Số Điện Thoại</td><td>090226549</td></tr>
                            <tr><td>Email</td><td></td></tr>
                            <tr><td>Hình Thức Thanh Toán</td><td>CHUYỂN KHOẢN QUA TÀI KHOẢN NGÂN HÀNG</td></tr>
                            <tr><td>Tên Sản Phẩm</td><td><a href="https://s-martsg.com/">Tôm cua tùm lum</a></td></tr>
                            <tr><td>Loại</td><td>Hảo hạng</td></tr>
                            <tr><td>Số lượng</td><td>1000 tấn</td></tr>
                        </tbody>
                    </table>
                </figure>
            </div>
        </article>
    </main><!-- #main -->
</div><!-- #primary -->

<?php 
get_sidebar();
get_footer(); ?>