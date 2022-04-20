<?php
/*
Plugin Name:     Builder WooCommerce
Plugin URI:      https://themify.me/addons/woocommerce
Version:         1.4.6
Author:          Themify
Author URI:  	 https://themify.me
Description:     Show WooCommerce products anywhere with the Builder. It requires to use with the latest version of any Themify theme or the Themify Builder plugin.
Text Domain:     builder-wc
Domain Path:     /languages
WC tested up to: current
*/

defined( 'ABSPATH' ) or die( '-1' );

class Builder_Woocommerce {

	public $url;
	private $dir;
	public $version;

	/**
	 * Creates or returns an instance of this class. 
	 *
	 * @return	A single instance of this class.
	 */
	public static function get_instance() {
            static $instance = null;
            if($instance===null){
                $instance = new self;
            }
            return $instance;
	}

	private function __construct() {
		$this->constants();
		add_action( 'plugins_loaded', array( $this, 'setup' ), 1 );
		add_action( 'plugins_loaded', array( $this, 'i18n' ), 5 );
		if(is_admin()){
		    add_filter( 'plugin_row_meta', array( $this, 'themify_plugin_meta'), 10, 2 );
		    add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'action_links') );
		    add_action( 'themify_builder_admin_enqueue', array( $this, 'admin_enqueue' ), 15 );
		    add_action( 'wp_ajax_builder_wc_get_terms', array( $this, 'get_terms' ), 15 );
		}else {
		    add_action('themify_builder_frontend_enqueue', array($this, 'admin_enqueue'), 15);
		}
	}

	public function constants() {
		$data = get_file_data( __FILE__, array( 'Version' ) );
		$this->version = $data[0];
		$this->url = trailingslashit( plugin_dir_url( __FILE__ ) );
		$this->dir = trailingslashit( plugin_dir_path( __FILE__ ) );
	}

	public function setup() {
		if( ! class_exists( 'WooCommerce' ) ) {
			return;
		}
		add_action( 'themify_builder_setup_modules', array( $this, 'register_module' ) );
	}

	public function themify_plugin_meta( $links, $file ) {
		if ( plugin_basename( __FILE__ ) === $file ) {
			$row_meta = array(
			  'changelogs'    => '<a href="' . esc_url( 'https://themify.me/changelogs/' ) . basename( dirname( $file ) ) .'.txt" target="_blank" aria-label="' . esc_attr__( 'Plugin Changelogs', 'themify' ) . '">' . esc_html__( 'View Changelogs', 'themify' ) . '</a>'
			);
	 
			return array_merge( $links, $row_meta );
		}
		return (array) $links;
	}
	public function action_links( $links ) {
		if ( is_plugin_active( 'themify-updater/themify-updater.php' ) ) {
			$tlinks = array(
			 '<a href="' . admin_url( 'index.php?page=themify-license' ) . '">'.__('Themify License', 'themify') .'</a>',
			 );
		} else {
			$tlinks = array(
			 '<a href="' . esc_url('https://themify.me/docs/themify-updater-documentation') . '">'. __('Themify Updater', 'themify') .'</a>',
			 );
		}
		return array_merge( $links, $tlinks );
	}
	public function i18n() {
		load_plugin_textdomain( 'builder-wc', false, '/languages' );
	}


	public function register_module() {
		Themify_Builder_Model::register_directory( 'templates', $this->dir . 'templates' );
		Themify_Builder_Model::register_directory( 'modules', $this->dir . 'modules' );
               
	}

	public function admin_enqueue(){
	    wp_enqueue_script( 'themify-builder-wc-admin', themify_enque($this->url . 'assets/admin.js'), array('themify-builder-app-js'), $this->version, true );
	    wp_localize_script( 'themify-builder-wc-admin', 'builderWc', array(
		'all'=> __( 'All Categories', 'builder-wc' ),
		'top_level'=>__( 'Only Top Level', 'builder-wc' ),
		'top_cat'=>__( 'Only Top Level Categories', 'builder-wc' ),
		'cat'=>__( 'Category', 'builder-wc' )
	    ));

	}
	
	public function get_terms(){
	    check_ajax_referer('tb_load_nonce', 'nonce');
	    wp_dropdown_categories( array(
		'taxonomy' => 'product_cat',
		'show_option_all' => false,
		'hide_empty' => 1,
		'selected' =>'',
		'value_field' => 'slug'
	    ) );
	}

	/*
 	* Always display rating in archive product page
 	*/
	public static function product_get_rating_html( $rating_html, $rating, $count ) {
		if('0' === $rating){
			/* translators: %s: rating */
			$label = __( 'Rated 0 out of 5', 'themify' );
			$rating_html  = '<div class="star-rating" role="img" aria-label="' . $label . '">' . wc_get_star_rating_html( $rating, $count ) . '</div>';
		}
		return $rating_html;
	}
}
Builder_Woocommerce::get_instance();
