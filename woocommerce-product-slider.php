<?php
/*
Plugin Name: WooCommerce Product Slider
Plugin URI: https://yourwebsite.com/
Description: A plugin to add a customizable WooCommerce product slider using Swiper.js.
Version: 1.0
Author: Your Name
Author URI: https://yourwebsite.com/
License: GPL2
*/

// Enqueue Swiper.js and custom styles
function enqueue_swiper_assets() {
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js', [], null, true);
    wp_enqueue_script('custom-swiper-init', plugin_dir_url(__FILE__) . 'assets/js/swiper-init.js', ['swiper-js'], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_swiper_assets');

// Create the product slider shortcode
function custom_woocommerce_slider_default_card($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts([
        'category' => '',
        'orderby' => 'name',
        'order' => 'ASC',
    ], $atts);

    // Query WooCommerce products
    $args = [
        'post_type' => 'product',
        'posts_per_page' => -1,
        'orderby' => $atts['orderby'],
        'order' => $atts['order'],
        'tax_query' => [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $atts['category'],
            ],
        ],
    ];
    $products = new WP_Query($args);

    // Generate Swiper HTML
    ob_start();
    if ($products->have_posts()) {
        ?>
        <div class="swiper-container">
            <div class="swiper-wrapper">
                <?php while ($products->have_posts()) : $products->the_post(); ?>
                    <div class="swiper-slide">
                        <?php wc_get_template_part('content', 'product'); ?>
                    </div>
                <?php endwhile; ?>
            </div>             
            <!-- Navigation buttons -->
            <div class="swiper-button-next"></div>
            <div class="swiper-button-prev"></div>
            <!-- Pagination -->
            <div class="swiper-pagination"></div>
        </div> 

        <?php
        wp_reset_postdata();
    } else {
        echo '<div class="woocommerce-info">Coming Soon</div>';
    }
    return ob_get_clean();
}
add_shortcode('product_slider_default', 'custom_woocommerce_slider_default_card');

// Add Swiper.js initialization script
function create_swiper_init_script() {
    $script = "
    document.addEventListener('DOMContentLoaded', function () {    
        const swiper = new Swiper('.swiper-container', {
            slidesPerView: 4,
            spaceBetween: 20,
            loop: false,
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            breakpoints: {
                100: {
                    slidesPerView: 1,
                },
                768: {
                    slidesPerView: 3,
                },
                1024: {
                    slidesPerView: 4,
                },
            },
        });
    });
    ";
    file_put_contents(plugin_dir_path(__FILE__) . 'assets/js/swiper-init.js', $script);
}
register_activation_hook(__FILE__, 'create_swiper_init_script');
