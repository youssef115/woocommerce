<?php
/*
Plugin Name: 	WooCommerce Shopdock
Plugin URI: 	https://themify.me/woocommerce-shopdock
Version:		1.1.6
Author:     	Themify
Author URI: 	https://themify.me
Description: 	Add an Ajax shop dock to any theme powered with WooCommerce. Users can add or remove item to the cart with a single click. The cart total and quantity are updated instantly. The layout is mobile ready (responsive).
Text Domain:	wc_shopdock
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Localisation
 **/

load_plugin_textdomain( 'wc_shopdock', false, dirname(plugin_basename(__FILE__)) . '/languages/' );

function wc_shopdock_is_woocommerce_active() {
	$active_plugins = (array) get_option( 'active_plugins', array() );
	if ( is_multisite() )
		$active_plugins = array_merge( $active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
	return in_array( 'woocommerce/woocommerce.php', $active_plugins ) || array_key_exists( 'woocommerce/woocommerce.php', $active_plugins );
}

if ( wc_shopdock_is_woocommerce_active() ) {

	add_action( 'plugins_loaded', 'woocommerce_shopdock_init' );
	//add_action( 'init', 'wc_shopdock_updater_setup' );

	function woocommerce_shopdock_init() {
		// Setup Global Variables
		if ( ! defined( 'SHOPDOCK_NAME' ) )
		    define( 'SHOPDOCK_NAME', trim( dirname( plugin_basename( __FILE__ ) ), '/' ) );
		if ( ! defined( 'SHOPDOCK_DIR' ) )
		    define( 'SHOPDOCK_DIR', WP_PLUGIN_DIR . '/' . SHOPDOCK_NAME );
		if ( ! defined( 'SHOPDOCK_URL' ) )
		    define( 'SHOPDOCK_URL', WP_PLUGIN_URL . '/' . SHOPDOCK_NAME );
		if ( ! defined( 'SHOPDOCK_TEMPLATE_URL' ) )
		    define( 'SHOPDOCK_TEMPLATE_URL', 'shopdock/' );

		add_action( 'woocommerce_shopdock_skin', 'default' );
		add_action( 'woocommerce_shopdock_position', 'top-left' );

		/**
		 * Include functions and scripts
		 **/
		include_once SHOPDOCK_DIR . '/' . 'includes' . '/' . 'shopdock-init.php';
		include_once SHOPDOCK_DIR . '/' . 'includes' . '/' . 'shopdock-admin.php';
		include_once SHOPDOCK_DIR . '/' . 'includes' . '/' . 'shopdock-templates.php';
		include_once SHOPDOCK_DIR . '/' . 'includes' . '/' . 'shopdock-scripts.php';
		include_once SHOPDOCK_DIR . '/' . 'includes' . '/' . 'shopdock-hooks.php';
		include_once SHOPDOCK_DIR . '/' . 'includes' . '/' . 'shopdock-functions.php';
	}
}

function wc_shopdock_updater_setup() {
	require_once( SHOPDOCK_DIR . '/upgrader/woocommerce-upgrader-updater.php' );
	$data = get_file_data( __FILE__, array( 'Version' ) );
	$version = $data[0];
	new WC_Shopdock_Updater( trim( dirname( plugin_basename( __FILE__) ), '/' ), $version, trim( plugin_basename( __FILE__), '/' ) );
}
