<?php
/**
 * Shopdock Functions
 *
 * General Shopdock functions
 *
 * @author 		Jameskoster
 * @category 	Actions
 * @package 	Shopdock
 */

/**
 * Add the skin and button position classes to the post class
 * @return  array $classes the post classes
 */
function shopdock_post_class( $classes ) {
	$add_item_class = get_option( 'woocommerce_shopdock_position' );
	$skin			= get_option( 'woocommerce_shopdock_skin' );

	$post_type 		= get_post_type( get_the_ID() );

	if ( 'product' == $post_type ) {
		$classes[] = $add_item_class;
		$classes[] = $skin;
	}

	return $classes;
}