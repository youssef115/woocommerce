<?php
/**
 * Shopdock Hook Actions
 *
 * Get ride woocommerce action hook and plugin hook
 *
 * @author 		Themify
 * @category 	Actions
 * @package 	Shopdock
 */

/**
 * Shopdock Dock Bar
 **/
add_action( 'wp_footer', 'shopdock_dock_bar', 10 );
add_action( 'wp_ajax_shopdock_dock_bar', 'shopdock_wp_ajax_dock_bar' );
add_action( 'wp_ajax_nopriv_shopdock_dock_bar', 'shopdock_wp_ajax_dock_bar' );

/* Products Loop */
/* remove default hook from woocommerce */
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 ); // product thumbnail

/**
 * Add action shopdock loop add to cart
 **/
add_action( 'woocommerce_before_shop_loop_item_title', 'shopdock_template_loop_product_thumbnail', 10 ); // product thumbnail

/**
 * Post Classes
 */
add_filter( 'post_class', 'shopdock_post_class' );

/**
 * single product on lightbox action
 **/
add_action( 'shopdock_single_product_image_ajax', 'woocommerce_show_product_sale_flash', 20 );
add_action( 'shopdock_single_product_image_ajax', 'woocommerce_show_product_images', 20 );
add_action( 'shopdock_single_product_ajax_content', 'woocommerce_template_single_add_to_cart', 10 );
add_action( 'shopdock_single_product_price', 'woocommerce_template_single_price', 10 );

/**
 * Shopdock hook style and js
 **/
add_action( 'wp_print_styles', 'shopdock_enqueue_styles' );
add_action( 'wp_enqueue_scripts', 'shopdock_enqueue_scripts', 20 );
add_action( 'wp_print_styles', 'shopdock_skins_style' );