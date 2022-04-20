<?php
/**
 * Builder Plugin Compatibility Code
 *
 * Themify_Builder_Plugin_Compat class provide code hack for some
 * plugins that need to be compatible.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * The Builder Plugin Compatibility class.
 *
 * This class contain hook, filters, and method to hack plugins.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
class Themify_Builder_Plugin_Compat {

	/**
	 * Constructor.
	 *
	 * @access public
	 */
	public function __construct() {

		// WooCommerce
		if (themify_is_woocommerce_active() ) {
			$description_hook = 'long' === themify_get( 'setting-product_description_type', 'long' ) ? 'the_content' : 'woocommerce_short_description';
			add_filter( $description_hook, array( $this, 'single_product_builder_content') );
			if('woocommerce_short_description'===$description_hook){
				add_action( 'woocommerce_variable_add_to_cart', array( $this, 'remove_builder_content_variation' ) );
			}
			add_action( 'woocommerce_archive_description', array( $this, 'wc_builder_shop_page' ), 11 );
			add_action( 'woocommerce_before_template_part', array( $this, 'before_woocommerce_templates' ) );
			add_action( 'woocommerce_after_template_part', array( $this, 'after_woocommerce_templates' ) );
			add_filter( 'woocommerce_product_tabs', array( $this, 'woocommerce_product_tabs' ) );
			// Single Variations Plugin compatibility
			if(!is_admin() && class_exists('Iconic_WSSV_Query')) {
				add_filter( 'pre_get_posts', array( $this, 'add_variations_to_product_query' ), 50, 1 );
			}

			/**
			 * disable Builder on Shop page
			 * self::wc_builder_shop_page() handles this
			 */
			add_action( 'woocommerce_before_main_content', array( $this, 'woocommerce_before_main_content' ) );
			add_action( 'woocommerce_after_main_content', array( $this, 'woocommerce_after_main_content' ) );
		}

		// WPML compatibility
		if ( Themify_Builder_Model::is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			add_action( 'wp_ajax_themify_builder_icl_copy_from_original', array( $this, 'icl_copy_from_original' ) );
		}

		// Rank Math SEO compatibility
		if ( is_admin() && Themify_Builder_Model::is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			add_filter( 'themify_builder_ajax_admin_vars', array( __CLASS__, 'tb_rank_math_content_filter' ) );
			add_action( 'wp_ajax_tb_rank_math_content_ajax', array( __CLASS__, 'tb_rank_math_content_ajax' ) );
		}

		// Paid Membership Pro
		if( defined( 'PMPRO_VERSION' ) ) {
			add_filter( 'themify_builder_display', array( $this, 'pmpro_themify_builder_display' ), 10, 2 );
		}

		// Members
		if( class_exists( 'Members_Load' ) ) {
			add_filter( 'themify_builder_display', array( $this, 'members_themify_builder_display' ), 10, 2 );
		}

		// WooCommerce Membership
		if( function_exists( 'wc_memberships' ) ) {
			add_filter( 'themify_builder_display', array( $this, 'wc_memberships_themify_builder_display' ), 10, 2 );
		}

		// Duplicate Post plugin
		if ( Themify_Builder_Model::is_plugin_active( 'duplicate-post/duplicate-post.php' ) ) {
			add_filter( 'option_duplicate_post_blacklist', array( $this, 'dp_meta_backlist'), 10, 2 );
			add_action('dp_duplicate_post', array( $this, 'dp_duplicate_builder_data'), 10, 2);
			add_action('dp_duplicate_page', array( $this, 'dp_duplicate_builder_data'), 10, 2);
		}

		// BWP Minify Plugin
		// Only apply the filter when WP Multisite with subdirectory install.
		if (defined( 'SUBDOMAIN_INSTALL' ) && !SUBDOMAIN_INSTALL && Themify_Builder_Model::is_plugin_active('bwp-minify/bwp-minify.php') ) {
			add_filter( 'bwp_minify_get_src', array( $this, 'bwp_minify_get_src' ) );
		}

		// Envira Gallery
		add_filter('themify_builder_post_types_support',array($this,'themify_builder_post_types_support'),12,1);
		add_filter('themify_post_types',array($this,'themify_builder_post_types_support'),12,1);

		// WP Super Cache
		add_action( 'themify_builder_save_data', array( $this, 'wp_super_cache_purge' ), 10, 2 );

		// Thrive Builder and Thrive Leads
		add_filter( 'themify_builder_is_frontend_editor', array( $this, 'thrive_compat' ) );

		// WP Gallery Custom Links
		if( Themify_Builder_Model::is_plugin_active( 'wp-gallery-custom-links/wp-gallery-custom-links.php' ) ) {
			add_filter( 'themify_builder_image_link_before', array( $this, 'wp_gallery_custom_links' ), 10, 3 );
		}

		// WordPress Related Posts
		if( Themify_Builder_Model::is_plugin_active( 'wordpress-23-related-posts-plugin/wp_related_posts.php' ) ) {
			add_action( 'init', array( $this, 'wp_related_posts' ) );
		}

		// The Events Calendar
		if ( defined( 'TRIBE_EVENTS_FILE' ) ) {
			add_action( 'template_redirect', array( $this, 'the_events_calendar_fix' ) );
		}

		/**
		 * Smart Cookie Kit
		 * @link https://wordpress.org/plugins/smart-cookie-kit/
		 */
		if(Themify_Builder_Model::is_frontend_editor_page()){
		    if (Themify_Builder_Model::is_front_builder_activate()&& class_exists( 'NMOD_SmartCookieKit_Frontend' ) ) {
				    remove_action( 'wp_enqueue_scripts', array( NMOD_SmartCookieKit_Frontend::init(), 'buffer_set' ), 0 );
				    remove_action( 'wp_print_footer_scripts', array( NMOD_SmartCookieKit_Frontend::init(), 'buffer_unset' ), 10 );

				    remove_action( 'wp_enqueue_scripts', array( NMOD_SmartCookieKit_Frontend::init(), 'enqueue_scripts' ), 1 );
				    remove_action( 'wp_print_footer_scripts', array( NMOD_SmartCookieKit_Frontend::init(), 'run_fontend_kit' ), 99999 );

		    }
		    if ( defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
			add_filter('autoptimize_filter_js_noptimize','__return_true');
			add_filter('autoptimize_filter_css_noptimize','__return_true');
			add_filter('autoptimize_filter_js_noptimize','__return_true');
		    }
		}

		/**
		 * Popup Maker plugin
		 * @link https://wordpress.org/plugins/popup-maker/
		 *
		 * Sets a flag on $ThemifyBuilder so that Builder stylesheet is added to the page multiple times.
		 * Necessary due to how the plugin messes up WP behavior.
		 */
		if ( function_exists( 'pum_autoloader' ) ) {
			global $ThemifyBuilder;
			$ThemifyBuilder->stylesheet_redo = true;
		}

		/**
		 * Media Library Folders Pro For WordPress
		 * @link https://maxgalleria.com/downloads/media-library-plus-pro/
		 */
		if ( class_exists( 'MaxGalleriaMediaLibPro' ) ) {
			add_filter( 'themify_styles_top_frame', array( $this, 'MaxGalleriaMediaLibPro_themify_styles_top_frame' ) );
		}

		if ( Themify_Builder_Model::is_plugin_active( 'sg-cachepress/sg-cachepress.php' ) ) {
			add_filter( 'sgo_css_combine_exclude', array( $this, 'sg_css_combine_exclude' ),99 );
		}

		if ( class_exists( 'RankMath' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'RankMath_compatibility' ), 10 );
		}
	}

	function woocommerce_before_main_content() {
		if ( is_shop() ) {
			$GLOBALS['ThemifyBuilder']->reset_builder_query( 'reset' );
		}
	}

	function woocommerce_after_main_content() {
		if ( is_shop() ) {
			$GLOBALS['ThemifyBuilder']->reset_builder_query( 'restore' );
		}
	}

	/**
	 * WordPress Related Posts plugin compatibility
	 * @link https://wordpress.org/plugins/wordpress-23-related-posts-plugin/
	 * Display related posts after the Builder content
	 */
	function wp_related_posts() {
		remove_filter( 'the_content', 'wp_rp_add_related_posts_hook', 10 );
		add_filter( 'the_content', 'wp_rp_add_related_posts_hook', 12 );
	}

	/**
	 * Compatibility with WP Gallery Custom Links plugin
	 * @link https://wordpress.org/plugins/wp-gallery-custom-links
	 * Apply Link and Target fields to gallery images in Grid layout
	 *
	 * @return string
	 */
	function wp_gallery_custom_links( $link_before, $image, $settings ) {
		$attachment_meta = get_post_meta( $image->ID, '_gallery_link_url', true );
		if( $attachment_meta ) {
			$link_before = preg_replace( '/href="(.*)"/', 'href="' . $attachment_meta . '"', $link_before );
		}
		$attachment_meta = get_post_meta( $image->ID, '_gallery_link_target', true );
		if( $attachment_meta ) {
			$link_before = str_replace( '>', ' target="' . $attachment_meta . '">', $link_before );
		}

		return $link_before;
	}

	/**
	 * Paid Membership Pro
	 * Show Builder contents only if user has access
	 *
	 * @access public
	 * @return bool
	 */
	public function pmpro_themify_builder_display( $display, $post_id ) {
		$hasaccess = pmpro_has_membership_access( NULL, NULL, true );
		if( is_array( $hasaccess ) ) {
			//returned an array to give us the membership level values
			$post_membership_levels_ids = $hasaccess[1];
			$post_membership_levels_names = $hasaccess[2];
			$hasaccess = $hasaccess[0];
		}
		return ! $hasaccess?false:$display;
	}

	/**
	 * Members compatibility
	 * Show Builder contents only if user has access
	 *
	 * @access public
	 * @return bool
	 */
	public function members_themify_builder_display( $display, $post_id ) {
		return !members_can_current_user_view_post( $post_id )?false:$display;
	}

	/**
	 * WooCommerce Membership compatibility
	 * Show Builder contents only if user has access
	 *
	 * @access public
	 * @return bool
	 */
	public function wc_memberships_themify_builder_display( $display, $post_id ) {
				return wc_memberships_is_post_content_restricted() && (! current_user_can( 'wc_memberships_view_restricted_post_content', $post_id ) || ! current_user_can( 'wc_memberships_view_delayed_post_content', $post_id ) )?false:true;
	}

	/**
	 * Load Builder content from original page when "Copy content" feature in WPML is used
	 *
	 * @access public
	 * @since 1.4.3
	 */
	public function icl_copy_from_original() {

		if( isset( $_POST['source_page_id'],$_POST['source_page_lang'] )) {
					global $ThemifyBuilder, $wpdb;
					$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid='%d' AND language_code='%s' LIMIT 1", $_POST[ 'source_page_id' ], $_POST[ 'source_page_lang' ] ) );
					$post    =!empty($post_id)?get_post( $post_id ):null;
					if ( ! empty( $post ) ) {
							$builder_data = $ThemifyBuilder->get_builder_data( $post->ID );
							include THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-meta.php';
					} else {
							echo '-1';
					}
		}
		die;
	}

	private static function show_builder_content($id,$content=''){
		global $ThemifyBuilder;
		$content = $ThemifyBuilder->get_builder_output( $id, $content );
		return $content;
	}

	/**
	 * Render builder content for Single products
	 *
	 * @access public
	 * @return string
	 */
	public function single_product_builder_content( $content ) {
		global $post;
		if ( is_product() && $post->post_type === 'product' ) {
			$content = self::show_builder_content( $post->ID, $content );
		}

		return $content;
	}

	/**
	 * Remove builder content filter from variation short description
	 */
	public function remove_builder_content_variation() {
		global $post;
		if ( is_product() && $post->post_type === 'product' ) {
			remove_filter( 'woocommerce_short_description', array( $this, 'single_product_builder_content') );
		}
	}

	/**
	 * Show builder on Shop page.
	 *
	 * @access public
	 */
	public function wc_builder_shop_page() {
		if ( is_shop() ) {
			echo self::show_builder_content(Themify_Builder_Model::get_ID());
		}
	}

	/**
	 * Avoid render buider content in WooCommerce content
	 */

	public function before_woocommerce_templates() {
		if( Themify_Builder_Model::is_front_builder_activate() ) {
			global $ThemifyBuilder;
			remove_filter( 'the_content', array( $ThemifyBuilder, 'builder_show_on_front'), 11 );
		}
	}

	public function after_woocommerce_templates() {
		if( Themify_Builder_Model::is_front_builder_activate() ) {
			global $ThemifyBuilder;
			add_filter( 'the_content', array( $ThemifyBuilder, 'builder_show_on_front' ), 11 );
		}
	}

	/**
	 * Ensure "Description" product tab is visible on frontend even if there are no description,
	 * so that Builder frontend editor can be used.
	 *
	 * Hooked to "woocommerce_product_tabs"
	 *
	 * @return array
	 */
	function woocommerce_product_tabs( $tabs ) {
		if ( is_singular( 'product' ) && ! isset( $tabs['description'] ) && Themify_Builder_Model::is_frontend_editor_page() ) {
			$tabs['description'] = array(
				'title' => __( 'Description', 'themify' ),
				'priority' => 10,
				'callback' => 'woocommerce_product_description_tab',
			);
		}

		return $tabs;
	}

	/**
	 * Load Admin Scripts.
	 *
	 * @access public
	 * @param string $hook
	 */
	public function RankMath_compatibility( $hook ) {
		if (in_array($hook, array('post-new.php', 'post.php'),true) && Themify_Builder_Model::hasAccess() && in_array(get_post_type(), themify_post_types(),true)) {
			wp_enqueue_script( 'themify-builder-plugin-compat', themify_enque(THEMIFY_BUILDER_URI .'/js/themify.builder.plugin.compat.js'), array('jquery', 'wp-hooks', 'rank-math-analyzer'), THEMIFY_VERSION, true );
		}
	}

	/**
	 * Echo builder on description tab
	 *
	 * @access public
	 * @return void
	 */
	public function echo_builder_on_description_tabs() {
		global $post;
		echo apply_filters( 'the_content', $post->post_content );
	}


	/**
	 * Get all builder text content from module which contain text
	 *
	 * @access public
	 * @param array $data
	 * @return string
	 */
	public function _get_all_builder_text_content( $data ) {
		global $ThemifyBuilder;

		$data = $ThemifyBuilder->get_flat_modules_list( null, $data );
		$text = '';
		if( is_array( $data ) ) {
			foreach( $data as $module ) {
				if( isset( $module['mod_name'] ,Themify_Builder_Model::$modules[ $module['mod_name'] ] ) ) {
					$text .= ' ' . Themify_Builder_Model::$modules[ $module['mod_name'] ]->get_plain_text( $module['mod_settings'] );
				}
			}
		}
		unset($data);
		return strip_tags( strip_shortcodes( $text ) );
	}

	/**
	 * Backlist builder meta_key from duplicate post settings custom fields
	 *
	 * @access public
	 * @param string $value
	 * @param string $option
	 * @return string
	 */
	public function dp_meta_backlist( $value, $option ) {
		$list_arr = explode(',', $value );
		$list_arr[] = '_themify_builder_settings_json';
		$value = implode( ',', $list_arr );
		return $value;
	}

	/**
	 * Action to duplicate builder data.
	 *
	 * @access public
	 * @param int $new_id
	 * @param object $post
	 */
	public function dp_duplicate_builder_data( $new_id, $post ) {
		global $ThemifyBuilder, $ThemifyBuilder_Data_Manager;
		$builder_data = $ThemifyBuilder->get_builder_data( $post->ID ); // get builder data from original post
		$ThemifyBuilder_Data_Manager->save_data( $builder_data, $new_id ); // save the data for the new post
	}

	/**
	 * Filter builder post types compatibility
	 *
	 * @access public
	 * @param int $new_id
	 * @param object $post
	 */
	public function themify_builder_post_types_support($post_types){
		$post_types = array_unique($post_types);
		$exclude = array_search('envira', $post_types,true);
		if($exclude!==false){
			unset($post_types[$exclude]);
		}
		$exclude = array_search('envira_album', $post_types,true);
		if($exclude!==false){
			unset($post_types[$exclude]);
		}
		return $post_types;
	}

	/**
	 * Modify the src for builder stylesheet.
	 *
	 * @access public
	 * @param string $string
	 * @return string
	 */
	public function bwp_minify_get_src( $string ) {
		$split_string = explode( ',', $string );
		$found_src = array();
		foreach( $split_string as $src ) {
			if ( preg_match( '/^files\/themify-css/', $src ) ) {
							$found_src[] = $src;
			}
		}
		if ( !empty( $found_src )) {
			$upload_dir = wp_upload_dir();
			$base_path = substr( $upload_dir['basedir'], strpos( $upload_dir['basedir'], 'wp-content' ) );
			foreach ( $found_src as $replace_src ) {
				$key = array_search( $replace_src, $split_string );
				if ( $key !== false ) {
					$split_string[ $key ] = trailingslashit( $base_path ) . str_replace( 'files/themify-css', 'themify-css', $split_string[ $key ] );
				}
			}
			$string = implode( ',', $split_string );
		}
		return $string;
	}

	/**
	 * Clear WP Super Cache plugin cache for a post when Builder data is saved
	 *
	 * @access public
	 * @since 2.5.8
	 */
	public function wp_super_cache_purge( $builder_data, $post_id ) {
		if( function_exists( 'wp_cache_post_change' ) ) {
			wp_cache_post_change( $post_id );
		}
	}

	/**
	 * Check whether Yoast SEO (free/premium) plugin activated
	 *
	 * @access public
	 * @return boolean
	 */
	public function is_yoast_seo_active() {
		return Themify_Builder_Model::is_plugin_active( 'wordpress-seo/wp-seo.php' ) || Themify_Builder_Model::is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' );
	}

	/**
	 * Compatibility with Thrive Builder and Thrive Leads plugins
	 * Disables Builder's frontend editor when Thrive editor is active
	 *
	 * @return bool
	 */
	function thrive_compat( $enabled ) {
		return isset( $_GET['tve'] ) && $_GET['tve'] === 'true' && function_exists( 'tve_editor_content' )?false:$enabled;
	}

	/**
	 * Fix duplicate content in The Events Calendar plugin
	 *
	 * @link https://wordpress.org/plugins/the-events-calendar/
	 */
	function the_events_calendar_fix() {
		if ( is_singular( 'tribe_events' ) ) {
			add_filter( 'tribe_events_after_html', array( $this, 'tribe_events_after_html' ) );
		}
	}

	/**
	 * Disable Builder frontend output after "tribe_events_after_html" filter
	 *
	 * @return string
	 */
	function tribe_events_after_html( $after ) {
		global $ThemifyBuilder;
		remove_filter( 'the_content', array( $ThemifyBuilder, 'builder_show_on_front' ), 11 );
		return $after;
	}

	/*
	 * Localize builder output for Rank Math Plugin integration
	 * */
	public static function tb_rank_math_content_filter($vars){
		$vars['builder_output'] = self::show_builder_content(Themify_Builder_Model::get_ID());
		return $vars;
	}

	/*
	 * Send back builder output base on current builder data for Rank Meta Plugin integration
	 * */
	public static function tb_rank_math_content_ajax(){
		Themify_Builder_Component_Base::retrieve_template('builder-output.php', array('builder_output' => $_POST['data'], 'builder_id' => $_POST['id']), '', '', true);
		wp_die();
	}

	/**
	 * Fix frontend media picker styles missing with the Media Library Folders Pro plugin
	 *
	 * @return array
	 */
	function MaxGalleriaMediaLibPro_themify_styles_top_frame( $styles ) {
		$styles[] = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/js/jstree/themes/default/style.min.css';
		$styles[] = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/mlfp-media.css';
		$styles[] = MAXGALLERIA_MEDIA_LIBRARY_PLUGIN_URL . '/maxgalleria-media-library.css';

		return $styles;
	}

	function add_variations_to_product_query($q){
		if ('product' !== $q->get('post_type') && !$q->is_search ) {
			return $q;
		}

		// Add product variations to the query
		$post_type   = (array) $q->get( 'post_type' );
		$post_type[] = 'product_variation';
		if ( ! in_array( 'product', $post_type ) ) {
			$post_type[] = 'product';
		}
		$q->set( 'post_type', array_filter( $post_type ) );

		// Don't get variations with unpublished parents
		$unpublished_variable_product_ids = Iconic_WSSV_Query::get_unpublished_variable_product_ids();
		if ( ! empty( $unpublished_variable_product_ids ) ) {
			$post_parent__not_in = (array) $q->get( 'post_parent__not_in' );
			$q->set( 'post_parent__not_in', array_merge( $post_parent__not_in, $unpublished_variable_product_ids ) );
		}

		// Don't get variations with missing parents :(
		$variation_ids_with_missing_parent = Iconic_WSSV_Query::get_variation_ids_with_missing_parent();
		if ( ! empty( $variation_ids_with_missing_parent ) ) {
			$post__not_in = (array) $q->get( 'post__not_in' );
			$q->set( 'post__not_in', array_merge( $post__not_in, $variation_ids_with_missing_parent ) );
		}

		if ( version_compare( WC_VERSION, '3.0.0', '<' ) ) {
			// update the meta query to include our variations
			$meta_query = (array) $q->get( 'meta_query' );
			$meta_query = Iconic_WSSV_Query::update_meta_query( $meta_query );
			$q->set( 'meta_query', $meta_query );
		} else {
			// update the tax query to include our variations
			$tax_query = (array) $q->get( 'tax_query' );
			$tax_query = Iconic_WSSV_Query::update_tax_query( $tax_query );
			$q->set( 'tax_query', $tax_query );
		}

		return $q;
	}

	/**
	 * Compatibility with SG optimizer
	 */
	function sg_css_combine_exclude( $exclude_list ) {
		// Add the style handle to exclude list.
		$exclude_list[] = 'wp-mediaelement';
		$exclude_list[] = 'media-views';
		$exclude_list[] = 'buttons';
		$exclude_list[] = 'dashicons';
		return $exclude_list;
	}
}
