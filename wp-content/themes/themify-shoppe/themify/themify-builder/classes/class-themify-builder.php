<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly

if (!class_exists('Themify_Builder')) :

    /**
     * Main Themify Builder class
     *
     * @package default
     */
    class Themify_Builder {

	private static $async = array('themify-combobox', 'themify-builder-google-webfont', 'themify-colorpicker', 'jquery-knob');

	/**
	 * @var array
	 */
	public $registered_post_types = array('post', 'page');

	/**
	 * Define builder grid active or not
	 * @var bool
	 */
	public static $frontedit_active = false;
	
	/**
	 * Define builder grid active id
	 * @var int
	 */
	public static $builder_active_id = null;


	/**
	 * Get status of builder content whether inside builder content or not
	 */
	public $in_the_loop = false;

	/**
	 * A list of posts which have been rendered by Builder
	 */
	private $post_ids = array();

	private $restriction_id = null;
	
	public static $builder_is_saving=false;
	
	/**
	 * Themify Builder Constructor
	 */
	public function __construct() {
	    
	}

	/**
	 * Class Init
	 */
	public function init() {
	    // Include required files
	    $this->includes_always();
	    Themify_Builder_Model::setup_default_directories();
	    Themify_Builder_Components_Manager::init();
	    $is_active = Themify_Builder_Model::is_front_builder_activate();
	    if ($is_active===false) {
		add_filter('themify_builder_module_content', array('Themify_Builder_Model', 'format_text'));
		add_filter('themify_builder_module_content', 'wp_make_content_images_responsive');
		add_filter('themify_image_make_responsive_image', 'wp_make_content_images_responsive');
	    }
	    elseif(isset($_GET['tb-id'])){
		self::$builder_active_id = (int)$_GET['tb-id'];
	    }
	    // Actions
	    $this->setup();
	    if (Themify_Builder_Model::is_frontend_editor_page()) {
		
		$this->includes_editable();
		if ($is_active===true) {
		    // load module panel frontend
		    add_action('wp_footer', array($this, 'load_javascript_template_front'), 10);
		    add_filter('show_admin_bar', '__return_false');// Custom CSS
		    add_action('wp_head', array($this, 'display_custom_css'));
		} else {
		    // Ajax Actions
			if ( defined( 'DOING_AJAX' ) ) {
				add_action( 'wp_ajax_tb_load_module_partial', array( $this, 'load_module_partial_ajaxify' ), 10 );
				add_action( 'wp_ajax_tb_render_element', array( $this, 'render_element_ajaxify' ), 10 );
				add_action( 'wp_ajax_tb_load_shortcode_preview', array( $this, 'shortcode_preview' ), 10 );
				add_action( 'wp_ajax_tb_get_post_types', array( $this, 'themify_get_post_types' ), 10 );
				add_action( 'wp_ajax_tb_get_menu', array( $this, 'tb_get_menu' ) );
				add_action( 'wp_ajax_themify_get_tax', array( $this, 'themify_get_tax' ), 10 );
				add_action( 'wp_ajax_themify_builder_get_tax_data', array( $this, 'themify_builder_get_tax_data' ), 10 );
				add_action( 'wp_ajax_tb_render_element_shortcode', array( $this, 'render_element_shortcode_ajaxify' ), 10 );
				// Builder Save Data
				add_action( 'wp_ajax_tb_save_data', array( $this, 'save_data_builder' ), 10 );
				add_action( 'wp_ajax_tb_save_css', array( $this, 'save_builder_css' ), 10 );
				add_action( 'wp_ajax_themify_builder_plupload_action', array( $this, 'builder_plupload' ), 10 );
				// AJAX Action Save Module Favorite Data
				add_action( 'wp_ajax_tb_module_favorite', array( $this, 'save_module_favorite_data' ) );
				//AJAX Action Get Visual Templates
				add_action( 'wp_ajax_tb_load_visual_templates', array( $this, 'load_visual_templates' ) );
				//AJAX Action Get Form Templates
				add_action( 'wp_ajax_tb_load_form_templates', array( $this, 'load_form_templates' ) );
				//AJAX Action update ticks and TakeOver
				add_action( 'wp_ajax_tb_update_tick', array( $this, 'updateTick' ) );
				add_action( 'wp_ajax_tb_help', array( $this, 'help' ) );
				// Regenerate CSS Files
				add_action( 'wp_ajax_themify_regenerate_css_files_ajax', array( $this, 'themify_regenerate_css_files_ajax' ) );
				// Replace URL
				add_action( 'wp_ajax_themify_get_builder_posts', array( $this, 'themify_get_builder_posts' ) );
				add_action( 'wp_ajax_themify_find_and_replace_ajax', array( $this, 'themify_find_and_replace_ajax' ) );
				// Maintenance hooks
				add_action( 'wp_ajax_tb_load_maintenance_pages', array( $this, 'load_maintenance_pages' ) );
				add_action( 'wp_ajax_tb_get_autocomplete', array( $this, 'tb_get_autocomplete' ) );
				add_action( 'wp_ajax_tb_get_select_dataset', array( $this, 'tb_get_select_dataset' ) );
				// populate autocomplete field type when 'dataset' => 'custom_fields'
				add_filter( 'tb_autocomplete_dataset_custom_fields', array( $this, 'autocomplete_dataset_custom_fields' ), 10, 3 );

			} else {
			// Builder write panel
			if (is_admin()===true) {
			    // Filtered post types
			    add_filter('themify_post_types', array($this, 'extend_post_types'));
			    Themify_Builder_Model::load_general_metabox(); // setup metabox fields
			    add_filter('themify_do_metaboxes', array($this, 'builder_write_panels'), 11);
			    add_action('themify_builder_metabox', array($this, 'add_builder_metabox'), 10);
			    add_action('admin_enqueue_scripts', array($this, 'check_admin_interface'), 10);
			    // Switch to frontend
			    add_action('save_post', array($this, 'switch_frontend'), 999, 1);
			    // Disable WP Editor
			    add_filter('edit_form_after_title', array($this, 'themify_disable_wp_editor'), 99);
			} else {
			    add_action('admin_bar_menu', array($this, 'builder_admin_bar_menu'), 100);
			    
			}
		    }
		    if($is_active===true || is_admin()!==true){
			add_action('wp_footer', array($this, 'async_footer'));
		    }
		    // Import Export
		    new Themify_Builder_Import_Export($this);
		}
		
		// Library Module, Rows and Layout Parts
		new Themify_Builder_Library_Items();
		
		// Themify Builder Revisions
		new Themify_Builder_Revisions();
		
		// Fix security restrictions
		add_filter('user_can_richedit', '__return_true');
	    } else {
		add_filter('post_class', array('Themify_Builder_Component_Base', 'filter_post_class'));
		// Activate Maintenance mode if set
		$this->load_maintenance_mode();
	    }

		// initialize Gutenberg compatibility
		if (Themify_Builder_Model::is_gutenberg_active()) {
		    Themify_Builder_Gutenberg::get_instance();
		}

	    // Script Loader
	    add_action('wp_enqueue_scripts', array($this, 'register_js_css'), 9);

	    // Hook to frontend
	    add_filter('the_content', array($this, 'builder_clear_static_content'), 1);
	    add_filter('the_content', array($this, 'builder_show_on_front'), 11);
	    add_filter('body_class', array($this, 'body_class'), 10);
	    // Add extra protocols like skype: to WordPress allowed protocols.
	    if (!has_filter('kses_allowed_protocols', 'themify_allow_extra_protocols') && function_exists('themify_allow_extra_protocols')) {
		add_filter('kses_allowed_protocols', 'themify_allow_extra_protocols');
	    }
	    // Plugin compatibility
		new Themify_Builder_Plugin_Compat();
		if ( Themify_Builder_Model::is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			Themify_Builder_WPML::get_instance();
		}

	    // if (!is_admin() || defined('DOING_AJAX')) {
	    Themify_Builder_Stylesheet::init();
	    add_filter('themify_main_script_vars', array($this, 'add_minify_vars'));

	    // Visibility controls
	    new Themify_Builder_Visibility_Controls();
	    new Themify_Builder_Layouts();
	    new Themify_Global_Styles();
	    new Themify_Custom_Fonts();

	    // Login module action for failed login
	    add_action( 'wp_login_failed', array( $this, 'wp_login_failed' ) );
	}
	/**
	 * Return Builder data for a post
	 *
	 * @since 1.4.2
	 * @return array
	 */
	public function get_builder_data($post_id) {
	    global $ThemifyBuilder_Data_Manager;
	    $builder_data = $ThemifyBuilder_Data_Manager->get_data($post_id);
	    if (!is_array($builder_data)) {
		$builder_data = array();
	    }
	    return apply_filters('themify_builder_data', $builder_data, $post_id);
	}

	/**
	 * Return all modules for a post as a two-dimensional array
	 *
	 * @since 1.4.2
	 * @return array
	 */
	public function get_flat_modules_list($post_id = null, $builder_data = null, $only_check = false) {
	    if ($builder_data === null) {
		$builder_data = $this->get_builder_data($post_id);
	    }
	    if ($only_check !== false) {
		return strpos(json_encode($builder_data), 'mod_settings') !== false;
	    }
	    $_modules = array();
	    // loop through modules in Builder
	    if (is_array($builder_data)) {
		foreach ($builder_data as $row) {
		    if (!empty($row['cols'])) {
			foreach ($row['cols'] as $col) {
			    if (!empty($col['modules'])) {
				foreach ($col['modules'] as $mod) {
				    if (isset($mod['mod_name'])) {
					$_modules[] = $mod;
				    }
				    // Check for Sub-rows
				    if (!empty($mod['cols'])) {
					foreach ($mod['cols'] as $sub_col) {
					    if (!empty($sub_col['modules'])) {
						foreach ($sub_col['modules'] as $sub_module) {
						    $_modules[] = $sub_module;
						}
					    }
					}
				    }
				}
			    }
			}
		    }
		}
	    }

	    return $_modules;
	}

	/**
	 * Return first not empty text module
	 *
	 * @since 1.4.2
	 * @return string
	 */
	public function get_first_text($post_id = null, $builder_data = null) {
	    if ($builder_data === null) {
		$builder_data = $this->get_builder_data($post_id);
	    }
	    // loop through modules in Builder
	    if (is_array($builder_data)) {
		foreach ($builder_data as $row) {
		    if (!empty($row['cols'])) {
			foreach ($row['cols'] as $col) {
			    if (!empty($col['modules'])) {
				foreach ($col['modules'] as $mod) {
				    if (isset($mod['mod_name']) && $mod['mod_name'] === 'text' && !empty($mod['mod_settings']['content_text'])) {
					return $mod['mod_settings']['content_text'];
				    }
				    // Check for Sub-rows
				    if (!empty($mod['cols'])) {
					foreach ($mod['cols'] as $sub_col) {
					    if (!empty($sub_col['modules'])) {
						foreach ($sub_col['modules'] as $sub_module) {
						    if (isset($sub_module['mod_name']) && $sub_module['mod_name'] === 'text' && !empty($sub_module['mod_settings']['content_text'])) {
							return $sub_module['mod_settings']['content_text'];
						    }
						}
					    }
					}
				    }
				}
			    }
			}
		    }
		}
	    }

	    return '';
	}

	/**
	 * Load JS and CSs for async loader.
	 *
	 * @since 2.1.9
	 */
	public function async_footer() {
	    wp_deregister_script('wp-embed');
	    $icons = Themify_Icon_Picker::get_instance();
	    wp_enqueue_style('themify-builder-loader', themify_enque(THEMIFY_BUILDER_URI . '/css/themify.builder.loader.css'), null, THEMIFY_VERSION);
	    wp_enqueue_script('themify-builder-loader', themify_enque(THEMIFY_BUILDER_URI . '/js/themify.builder.loader.js'), array('jquery'), THEMIFY_VERSION, true);
	    wp_localize_script('themify-builder-loader', 'tbLoaderVars', array(
		'styles' => apply_filters('themify_styles_top_frame', array(
		    themify_enque(THEMIFY_BUILDER_URI . '/css/combine.css'),
		    themify_enque(THEMIFY_BUILDER_URI . '/css/toolbar.css'),
		    is_rtl() ? themify_enque(THEMIFY_BUILDER_URI . '/css/toolbar-rtl.css') : '',
		    themify_enque(THEMIFY_URI . '/themify-icons/themify-icons.css'),
		    THEMIFY_BUILDER_URI . '/css/animate.min.css',
		    themify_enque(THEMIFY_BUILDER_URI . '/css/themify.combobox.css'),
		    themify_enque($icons->url . 'assets/styles.css'),
		    themify_enque(THEMIFY_METABOX_URI . 'css/jquery.minicolors.css'),
		    )
		),
		'turnOnBuilder' => __('Turn On Builder', 'themify'),
		'editTemplate'=>__('Edit Template','themify'),
		'isTouch' => themify_is_touch() ? true : false,
		'isGlobalStylePost' => Themify_Global_Styles::$isGlobalEditPage
	    ));
	    if (current_user_can('upload_files')) {
		wp_enqueue_media();
	    }
	    echo '<div style="display:none;">';
	    wp_editor(' ', 'tb_lb_hidden_editor');
	    echo '</div>';
	}

	/**
	 * Init function
	 */
	public function setup() {
	    do_action('themify_builder_setup_modules', $this);
	    /* git #1862 */
	    Themify_Builder_Model::builder_cpt_check();
	    if (!self::$frontedit_active && ( Themify_Builder_Model::is_front_builder_activate() || is_admin() )) {
		$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : FALSE;
		$is_action = isset($_REQUEST['action']) || !Themify_Builder_Model::is_front_builder_activate();
		if (!$is_action || !in_array($action, array('tb_update_tick', 'tb_load_module_partial', 'tb_render_element', 'tb_load_shortcode_preview', 'themify_builder_plupload_action', 'tb_module_favorite', 'themify_regenerate_css_files_ajax',  'render_element_shortcode_ajaxify', 'themify_get_tax', 'themify_get_post_types', 'tb_get_menu', 'themify_builder_get_tax_data', 'tb_help', 'themify_builder_plupload_action'), true)) {
		    Themify_Builder_Component_Module::load_modules();
		}
	    }
	    Themify_Builder_Model::isWpEditorDisable();
	}
	
	private function includes_always() {
	    if (Themify_Builder_Model::is_gutenberg_active()) {
		include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-gutenberg.php';
	    }
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-builder-data-manager.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-stylesheet.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-plugin-compat.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-components-manager.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-widgets.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-visibility-controls.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-wpml.php';
	    include THEMIFY_BUILDER_INCLUDES_DIR . '/deprecated.php';
	}

	private function includes_editable() {
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-revisions.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-library-item.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-builder-duplicate-page.php';
	    include THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-import-export.php';
	}

	/**
	 * List of post types that support the editor
	 *
	 * @since 2.4.8
	 */
	public function builder_post_types_support() {
	    $public_post_types = get_post_types(array(
		'public' => true,
		'_builtin' => false,
		'show_ui' => true,
	    ));
	    $post_types = array_merge($public_post_types, array('post', 'page'));
	    foreach ($post_types as $key => $type) {
		if (!post_type_supports($type, 'editor')) {
		    unset($post_types[$key]);
		}
	    }

	    return apply_filters('themify_builder_post_types_support', $post_types);
	}

	/**
	 * Builder write panels
	 *
	 * @param $meta_boxes
	 *
	 * @return array
	 */
	public function builder_write_panels($meta_boxes) {
	    if (Themify_Builder_Model::is_gutenberg_editor())
		return $meta_boxes;

	    // Page builder Options
	    $page_builder_options = apply_filters('themify_builder_write_panels_options', array(
		array(
		    'name' => 'page_builder',
		    'title' => __('Themify Builder', 'themify'),
		    'description' => '',
		    'type' => 'page_builder'
		),
		array(
		    'name' => 'builder_switch_frontend',
		    'title' => false,
		    'type' => 'textbox',
		    'value' => 0,
		    'meta' => array('size' => 'small')
		)
	    ));

	    $types = $this->builder_post_types_support();
	    $all_meta_boxes = array();
	    foreach ($types as $type) {
		$all_meta_boxes[] = apply_filters('themify_builder_write_panels_meta_boxes', array(
		    'name' => __('Themify Builder', 'themify'),
		    'id' => 'page-builder',
		    'options' => $page_builder_options,
		    'pages' => $type
		));
	    }

	    return array_merge($meta_boxes, $all_meta_boxes);
	}

	/**
	 * Add builder metabox
	 */
	public function add_builder_metabox() {
	    include THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-meta.php';
	}

	/**
	 * Load interface js and css
	 *
	 * @since 2.1.9
	 */
	private function load_frontend_interface() {

	    // load only when builder is turn on
	    wp_enqueue_style('themify-builder-combine', themify_enque(THEMIFY_BUILDER_URI . '/css/combine.css'), false, THEMIFY_VERSION);
	    wp_enqueue_style('themify-builder-admin-ui', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui.css'), false, THEMIFY_VERSION);
	    if (is_rtl()) {
		wp_enqueue_style('themify-builder-admin-ui-rtl', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui-rtl.css'), array('themify-builder-admin-ui'), THEMIFY_VERSION);
	    }

	    if (class_exists('Jetpack_VideoPress')) {
		// Load this so submit_button() is available in VideoPress' print_media_templates().
		require_once ABSPATH . 'wp-admin/includes/template.php';
	    }
	    $enqueue_scripts = array(
		'wp-backbone',
		'jquery-ui-draggable',
		'shortcode',
		'word-count',
		'themify-colorpicker',
		'themify-builder-google-webfont',
		'themify-combobox',
		'themify-builder-common-js',
		'themify-builder-app-js'
	    );

	    require_once ABSPATH . WPINC . '/media-template.php';
	    ob_start();
	    do_action('themify_builder_frontend_data');
	    wp_print_media_templates();
	    $top_iframe_data = ob_get_contents();
	    ob_end_clean();

	    foreach ($enqueue_scripts as $script) {
		switch ($script) {
		    case 'themify-combobox':
			wp_enqueue_script($script, THEMIFY_BUILDER_URI . '/js/themify.combobox.min.js', null, THEMIFY_VERSION, true);
			break;
		    case 'themify-colorpicker':
			wp_enqueue_script($script, THEMIFY_METABOX_URI . 'js/jquery.minicolors.min.js', null, THEMIFY_VERSION, true); // grab from themify framework
				wp_localize_script( $script, 'themifyCM', Themify_Metabox::themify_localize_cm_data() );

			break;

		    case 'themify-builder-google-webfont':
			wp_enqueue_script($script, themify_https_esc('http://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js'), null,'1.6.26', true);
			break;

		    case 'themify-builder-common-js':
			// front ui js
			wp_enqueue_script('themify-builder-simple-bar-js', THEMIFY_BUILDER_URI . '/js/simplebar.min.js', null,THEMIFY_VERSION, true);
			wp_enqueue_script($script, themify_enque(THEMIFY_BUILDER_URI . '/js/themify.builder.common.js'), null, THEMIFY_VERSION, true);
			// Icon picker
			Themify_Icon_Picker::get_instance()->enqueue();
			break;
		    case 'themify-builder-app-js':
			// is mobile version
			if (themify_is_touch()) {
			    wp_enqueue_script('themify-builder-mobile-ui-js', THEMIFY_BUILDER_URI . '/js/jquery.ui.touch-punch.min.js', array('jquery-ui-mouse'), THEMIFY_VERSION, true);
			}
			wp_enqueue_script('themify-builder-js', themify_enque(THEMIFY_BUILDER_URI . '/js/themify.builder.script.js'), null, THEMIFY_VERSION, true);
			wp_enqueue_script('tb_builder_js_style', themify_enque(THEMIFY_URI . '/js/generate-style.js'), null, THEMIFY_VERSION, true);
			wp_enqueue_script($script, themify_enque(THEMIFY_BUILDER_URI . '/js/themify-builder-app.js'), array('tb_builder_js_style'), THEMIFY_VERSION, true);
			wp_enqueue_script('themify-builder-draggable', themify_enque(THEMIFY_BUILDER_URI . '/js/themify-builder-drag.js'), array($script), THEMIFY_VERSION, true);
			global $shortcode_tags, $wp_styles;
			wp_localize_script($script, 'themifyBuilder', apply_filters('themify_builder_ajax_front_vars', array(
			    'ajaxurl' => admin_url('admin-ajax.php'),
			    'includes_url' => includes_url(),
			    'meta_url' => THEMIFY_METABOX_URI,
			    'isTouch' => themify_is_touch() ? 'true' : 'false',
			    'tb_load_nonce' => wp_create_nonce('tb_load_nonce'),
			    'isThemifyTheme' => themify_is_themify_theme() ? 'true' : 'false',
			    'disableShortcuts' => themify_builder_get('setting-page_builder_disable_shortcuts', 'builder_disable_shortcuts'),
			    'available_shortcodes' => array_keys($shortcode_tags),
			    'widget_css' => array(home_url($wp_styles->registered['widgets']->src), home_url($wp_styles->registered['customize-widgets']->src)),
			    'modules' => Themify_Builder_Model::get_modules_localize_settings(),
			    'i18n' => self::get_i18n(),
			    'paths' => self::get_paths(),
			    'custom_css'=>get_post_meta( self::$builder_active_id, 'tbp_custom_css', true ),
			    'data' => $top_iframe_data,
			    'debug' => defined('THEMIFY_DEBUG') && THEMIFY_DEBUG,
			    'import_nonce' => wp_create_nonce('themify_builder_import_filethemify-builder-plupload'),
			    // Breakpoints
			    'breakpoints' => themify_get_breakpoints(),
			    'cf_api_url' => Themify_Custom_Fonts::$api_url,
			    'google'=>themify_get_google_web_fonts_list(),
			    'cf'=>Themify_Custom_Fonts::get_list()
			)));
			wp_enqueue_script('jquery-knob', THEMIFY_BUILDER_URI . '/js/jquery.knob.min.js', null, THEMIFY_VERSION, true);
			wp_enqueue_script('themifyGradient', themify_enque(THEMIFY_BUILDER_URI . '/js/themifyGradient.js'), array('themify-colorpicker'), THEMIFY_VERSION, true);
			wp_enqueue_script('themify-builder-inline-editing', themify_enque(THEMIFY_BUILDER_URI . '/js/themify-builder-inline-editing.js'), array('themify-builder-app-js'), THEMIFY_VERSION, true);
			wp_enqueue_script('themify-builder-front-ui-js', themify_enque(THEMIFY_BUILDER_URI . '/js/themify-builder-visual.js'), array('themify-builder-inline-editing'), THEMIFY_VERSION, true);
			wp_localize_script('themify-builder-front-ui-js', 'themify_builder_plupload_init', Themify_Builder_Model::get_builder_plupload_init());
			
			wp_localize_script('themify-builder-front-ui-js', 'builderdata_' . self::$builder_active_id, array(
			    'data' => $this->get_builder_data(self::$builder_active_id)
			));
			break;
		    default:
			wp_enqueue_script($script);
			break;
		}
	    }
	    do_action('themify_builder_frontend_enqueue');
	}

	/**
	 * Returns an array containing paths to different assets loade by Builder editor
	 *
	 * @return array
	 */
	private static function get_paths() {
		return array(
			// Pre-designed layouts
			'layouts_index' => 'https://themify.me/themify-layouts/index.json',
			// URL to file containing Builder data for layout {SLUG}
			'layout_template' => 'https://themify.me/themify-layouts/{SLUG}.txt',
			// Pre-designed rows
			'rows_index' => 'https://themify.me/public-api/predesigned-rows/index.json',
			// row template
			'row_template' => 'https://themify.me/public-api/predesigned-rows/{SLUG}.txt',
		);
	}

	public static function get_i18n() {
	    global $wp_registered_sidebars;
	    $sidebars = array();
	    $sidebars[''] = '';
	    foreach ($wp_registered_sidebars as $v) {
		$sidebars[$v['id']] = $v['name'];
	    }
	    $y = __('Yes', 'themify');
	    $n = __('No', 'themify');
	    $options = array(
		'confirmRestoreRev' => __('Save the current state as a revision before replacing?', 'themify'),
		'dialog_import_page_post' => __('Would you like to replace or append the layout?', 'themify'),
		'confirm_on_duplicate_page' => __('Save before duplicating this page?', 'themify'),
		'moduleDeleteConfirm' => __('Press OK to remove this module', 'themify'),
		'rowDeleteConfirm' => __('Press OK to remove this row', 'themify'),
		'subrowDeleteConfirm' => __('Press OK to remove this sub row', 'themify'),
		'importFileConfirm' => __('Import will override all current Builder data. Press OK to continue', 'themify'),
		'confirm_template_selected' => __('Would you like to replace or append the layout?', 'themify'),
		'enterRevComment' => __('Add optional revision comment:', 'themify'),
		'confirmDeleteRev' => __('Are you sure want to delete this revision', 'themify'),
		'layoutEditConfirm' => __('Your changes will be lost. Are you sure?', 'themify'),
		'switchToFrontendLabel' => __('Themify Builder', 'themify'),
		'text_alert_wrong_paste' => __('Error: Paste valid data only (paste row data to row, sub-row data to sub-row, module data to module).', 'themify'),
		'text_import_layout_button' => __('Import Layout', 'themify'),
		'rowLibraryDeleteConfirm' => __('Are you sure to delete this saved row?', 'themify'),
		'multiSelected'=>__('Multiple Selected', 'themify'),
		'moduleLibraryDeleteConfirm' => __('Are you sure to delete this saved module?', 'themify'),
		'partLibraryDeleteConfirm' => __('Are you sure to delete this? It can not be undone. Once the Layout Part is deleted, all pages with this Layout Part will be gone.', 'themify'),
		'incorrectImageURL' => __('Incorrect image URL. Please enter a valid image URL.', 'themify'),
		'enterGlobalStyleName' => __('Please enter Global Style name.', 'themify'),
		'gs' => __('Global Styles', 'themify'),
		'has_gs' => __('This module is using a Global Style. Adding styling to this module will override the Global Style. Click here to add styling.', 'themify'),
		'preview' => __('Preview', 'themify'),
		'add_module' => __('Add module', 'themify'),
		'not_empty' => __('Please enter required field.', 'themify'),
		'addSavedGS' => __('Would you like to apply the saved Global Style?', 'themify'),
		'no_op_module'=>__('There is no option for this module. Click "Styling" tab to customize it.', 'themify'),
		's_v' => __('Sticky Visibility', 'themify'),
		'h_a' => __('Hide All', 'themify'),
		'de' => __('Desktop', 'themify'),
		'mo' => __('Mobile', 'themify'),
		'ta' => __('Tablet', 'themify'),
		'ta_l' => __('Tablet Landscape', 'themify'),
		'label' => array(
		    'rmeta'=>__('Are you sure want to delete this metabox', 'themify'),
		    'smodule' => 'Save Module',
		    'srow' => 'Save Row',
		    'g_s' => __('Global Style', 'themify'),
		    'query_by' => __('Query by', 'themify'),
		    'query_id' => __('Post Type', 'themify'),
		    'query_term_id' => __('Categories', 'themify'),
		    'query_tax_id' => __('Taxonomy', 'themify'),
		    'query_tag_id' => __('Tags', 'themify'),
		    'query_desc' => __('Enter multiple category IDs (eg. 2,5,8) or slug (eg. news,blog,featured) or exclude category IDs (eg. -2,-5,-8).', 'themify'),
		    'slug_desc' => __('Insert post slug(s) - separate multiple slugs with commas.', 'themify'),
		    'errorId' => __('This ID has been used on the page, please enter another one.', 'themify'),
		    'slug_label' => __('Post Slugs', 'themify'),
			'all_posts' => __( 'All Posts', 'themify' ),
			'sticky_first' => __( 'Show Sticky Posts First', 'themify' ),
		    'slayout_part' => __('Save as Layout Part', 'themify'),
		    'nlayout' => __('New Layout Part', 'themify'),
		    'mlayout' => __('Manage Layout Part', 'themify'),
		    'import_label' => __('%s data', 'themify'),
		    'import_data' => __('Paste %s data here', 'themify'),
		    'import_tab' => __('Import %s', 'themify'),
		    'export_tab' => __('Export %s', 'themify'),
		    'export_data' => __('You can copy & paste this data to another Builder site', 'themify'),
		    'revision' => __('Revisions', 'themify'),
		    'solid' => __('Solid', 'themify'),
		    'upload' => __('Upload', 'themify'),
		    'upload_image' => __('Upload an Image', 'themify'),
		    'insert_image' => __('Insert file URL', 'themify'),
		    'browse_image' => __('Browse Library', 'themify'),
		    'add_media' => __('Add Media', 'themify'),
		    'add_gallery' => __('Insert Gallery', 'themify'),
		    'image_preview' => __('Image Preview', 'themify'),
		    'css' => __('CSS', 'themify'),
		    'cus_css_m' => __('The custom CSS entered here will add to this page only.', 'themify'),
		    'save_as_layout' => __('Save as Layout', 'themify'),
		    'replace_builder' => __('Replace Layout', 'themify'),
		    'append_builder' => __('Append To Layout', 'themify'),
		    'title' => __('Title', 'themify'),
		    'or' => __('or', 'themify'),
		    'slug' => __('Slug', 'themify'),
		    'google_fonts' => __('Google Fonts', 'themify'),
		    'cf_fonts' => __('Custom Fonts', 'themify'),
		    'safe_fonts' => __('Web Safe Fonts', 'themify'),
		    'font_preview' => __('Font Preview', 'themify'),
		    'visual' => __('Visual', 'themify'),
		    'text' => __('Text', 'themify'),
		    'new_row' => __('Add new', 'themify'),
		    'table_landscape' => __('Tablet Landscape', 'themify'),
		    'duplicate' => __('Duplicate', 'themify'),
		    'delete' => __('Delete', 'themify'),
		    'insert_icon' => __('Insert Icon', 'themify'),
		    'linear' => __('Linear', 'themify'),
		    'radial' => __('Radial', 'themify'),
		    'rotation' => __('Rotation', 'themify'),
		    'image' => __('Image', 'themify'),
		    'gradient' => __('Gradient', 'themify'),
		    'clear_gradient' => __('Clear Gradient', 'themify'),
		    'circle_radial' => __('Circle Radial', 'themify'),
		    'all' => __('All', 'themify'),
		    'top' => __('Top', 'themify'),
		    'top_left' => __('Top-Left', 'themify'),
		    'left' => __('Left', 'themify'),
		    'bottom' => __('Bottom', 'themify'),
		    'right' => __('Right', 'themify'),
		    'styling' => __('Styling', 'themify'),
		    'visibility' => __('Visibility', 'themify'),
		    'animation' => __('Animation', 'themify'),
		    'custom_css' => __('CSS Class', 'themify'),
		    'id_name' => __('ID Name', 'themify'),
		    'id_help' => __('ID name is used to identify element in un-stick option for "Sticky Scrolling" feature.', 'themify'),
		    'transparent' => __('Transparent', 'themify'),
		    'id_desc' => __('ID name should be unique (it is used to identify the element for Sticky Scrolling).', 'themify'),
		    'custom_css_help' => __('Add custom CSS class(es) for custom styling (<a href="https://themify.me/docs/builder#additional-css-class" target="_blank">learn more</a>).', 'themify'),
		    'reset_style' => __('Reset Styling', 'themify'),
		    'reset_effect' => __('Reset All Effects', 'themify'),
		    'ctr_save' => __('Ctrl + S', 'themify'),
		    'save' => __('Save', 'themify'),
		    'done' => __('Done', 'themify'),
		    's_s' => __('Save Style', 'themify'),
		    'clear_date' => __('Clear Date', 'themify'),
		    'select_menu' => __('Select a Menu...', 'themify'),
		    'menu_help' => sprintf(__('Add more <a href="%s" target="_blank">%s</a>', 'themify'), admin_url('nav-menus.php'), __('menu', 'themify')),
		    'search_widget' => __('Search widgets…', 'themify'),
		    'widget_validate' => __('Please select the Widget', 'themify'),
		    'p' => __('Padding', 'themify'),
		    'm' => __('Margin', 'themify'),
		    'b' => __('Border', 'themify'),
		    'f' => __('Font', 'themify'),
		    'f_l' => __('Filters', 'themify'),
		    'f_f' => __('Font Family', 'themify'),
		    'f_w' => __('Font Weight', 'themify'),
		    'f_st' => __('Font Style', 'themify'),
		    'f_s' => __('Font Size', 'themify'),
		    'l_h' => __('Line Height', 'themify'),
		    'l_s' => __('Letter Spacing', 'themify'),
		    't_a' => __('Text Align', 'themify'),
		    't_t' => __('Text Transform', 'themify'),
		    't_d' => __('Text Decoration', 'themify'),
		    'bg' => __('Background', 'themify'),
		    'bg_c' => __('Background Color', 'themify'),
		    'b_i' => __('Background Image', 'themify'),
		    'b_r' => __('Background Repeat', 'themify'),
		    'b_p' => __('Background Position', 'themify'),
		    'r' => __('Repeat', 'themify'),
		    'f_c' => __('Font Color', 'themify'),
		    'l' => __('Link', 'themify'),
		    'o_l'=>__('Open Link In','themify'),
		    'lg'=>__('Lightbox','themify'),
		    'cl'=>__('Custom Link','themify'),
		    'c' => __('Color', 'themify'),
		    'c_t' => __('Color Type', 'themify'),
		    'col' => __('Multi-columns', 'themify'),
		    'g' => __('General', 'themify'),
		    'm_t' => __('Module Title', 'themify'),
		    'head' => __('Heading', 'themify'),
		    'n' => __('Normal', 'themify'),
		    'h' => __('Hover', 'themify'),
		    'w' => __('Width', 'themify'),
		    'ht' => __('Height', 'themify'),
		    'a_ht' => __('Auto Height', 'themify'),
		    'm_ht' => __('Min Height', 'themify'),
		    'mx_ht' => __('Max Height', 'themify'),
		    'c_c' => __('Column Count', 'themify'),
		    'c_g' => __('Column Gap', 'themify'),
		    'c_d' => __('Column Divider', 'themify'),
		    'req' => __('Required', 'themify'),
		    'b_m' => __('Blend Mode', 'themify'),
		    'r_c' => __('Corners', 'themify'),
		    'bo_r' => __('Corners', 'themify'),
		    'sh' => __('Shadow', 'themify'),
		    'b_s' => __('Box Shadow', 'themify'),
		    'h_o' => __('Horizontal Offset', 'themify'),
		    'v_o' => __('Vertical Offset', 'themify'),
		    'bl' => __('Blur', 'themify'),
		    'spr' => __('Spread', 'themify'),
		    'in_sh' => __('Inset Shadow', 'themify'),
		    't_sh' => __('Text Shadow', 'themify'),
		    'h_sh' => __('Horizontal Shadow', 'themify'),
		    'v_sh' => __('Vertical Shadow', 'themify'),
		    's_e_f' => __('Scroll Effects', 'themify'),
		    's_e_s' => __('Sticky', 'themify'),
		    's_e_m' => __('Motion', 'themify'),
		    'y' => $y,
		    'no' => $n,
		    'en'=> __('Enable', 'themify'),
		    'dis'=> __('Disable', 'themify'),
		    'hi'=>__('Hide', 'themify'),
		    's'=>__('Show', 'themify'),
		    'cus'=>__('Custom', 'themify'),
		    'F_j_Y' => __('August 18, 2019 (F j, Y)', 'themify'),
		    'Y_m_d' => __('2019-08-18 (Y-m-d)', 'themify'),
		    'm_d_Y' => __('08/18/2019 (m/d/Y)', 'themify'),
		    'd_m_Y' => __('18/08/2019 (d/m/Y)', 'themify'),
		    'def' => __('Default', 'themify'),
		    'cus_f' => __('Custom Format', 'themify'),
		    'cus_fd_h' => __('Enter date format in these letters: l D d j S F m M n Y y', 'themify'),
		    'cus_ft_h' =>  __('Enter time format in these letters: g G H i a A', 'themify'),
		    'icon' => __('Icon', 'themify'),
		    'b_t' => __('Before Text', 'themify'),
		    'a_t' => __('After Text', 'themify'),
		    'd_f' => __('Date Format', 'themify'),
		    't_f'=>__('Time Format', 'themify'),
		    'g_i_a'=> __('6:28 pm (g:i a)', 'themify'),
		    'g_i_A'=> __('6:28 PM (g:i A)', 'themify'),
		    'H_i'=>__('18:28 (H:i)', 'themify'),
		    'no_c'=>__('No Comments', 'themify'),
		    'one_c'=>__('One Comment', 'themify'),
		    'comments'=> __('Comments', 'themify'),
		    'a_p'=>__('Author Picture', 'themify'),
		    'p_s'=>__('Picture Size', 'themify'),
		    'sep'=>__('Separator', 'themify'),
			'layout_replace' => __( 'Replace Layout', 'themify' ),
			'layout_append' => __( 'Append To Layout', 'themify' ),
			'layout_error' => __( 'There was an error in loading layout, please try again later, or you can download this file: ({FILE}) and then import manually (https://themify.me/docs/builder#import-export).', 'themify' ),
			'save' => __( 'Save', 'themify' ),
			'save_no' => __( 'Don\'t Save', 'themify' ),
			'rows_fetch_error' => __( 'Failed to load Pre-Designed Rows from server.', 'themify' ),
			'row_fetch_error' => __( 'Failed to fetch row template.', 'themify' ),
			'hue' => __( 'Hue', 'themify' ),
			'sat' => __( 'Saturation', 'themify' ),
			'bri' => __( 'Brightness', 'themify' ),
			'con' => __( 'Contrast', 'themify' ),
			'inv' => __( 'Invert', 'themify' ),
			'se' => __( 'Sepia', 'themify' ),
			'op' => __( 'Opacity', 'themify' ),
			'zi' => __( 'Z-Index', 'themify' ),
			'a_wd' => __('Auto Width', 'themify'),
			'mi_wd' => __('Min Width', 'themify'),
			'ma_wd' => __('Max Width', 'themify'),
			'po' => __('Position', 'themify'),
			'auto' => __('Auto', 'themify'),
			'abs' => __('Absolute', 'themify'),
			'fi' => __('Fixed', 'themify'),
			're' => __('Relative', 'themify'),
			'st' => __('Static', 'themify'),
			'disp' => __('Display', 'themify'),
		),
		'options' => array(
		    'border_radius'=>Themify_Builder_Model::get_border_radius_styles(),
		    'border' => Themify_Builder_Model::get_border_styles(),
		    'appearance' => Themify_Builder_Model::get_appearance(),
		    'aligment' => Themify_Builder_Model::get_text_aligment(),
		    'repeat' => Themify_Builder_Model::get_repeat(),
		    'position' => Themify_Builder_Model::get_position(),
		    'text_decoration' => Themify_Builder_Model::get_text_decoration(),
		    'font_style' => Themify_Builder_Model::get_font_style(),
		    'font_weight' => Themify_Builder_Model::get_font_weight(),
		    'text_transform' => Themify_Builder_Model::get_text_transform(),
		    'frame' => Themify_Builder_Model::get_frame_layout(),
		    'preset_animation' => Themify_Builder_model::get_preset_animation(),
		    'animation' => Themify_Builder_model::get_animation(),
		    'visibility' => Themify_Builder_model::get_visibility(),
		    'color' => Themify_Builder_model::get_colors(),
		    'blend' => Themify_Builder_model::get_blend_mode(),
			'fonts' => array(
				'safe' => themify_get_web_safe_font_list(),
				'google' => themify_get_google_web_fonts_list(),
				'cf' => Themify_Custom_Fonts::get_list()
            ),
		    'slider_options'=>Themify_Builder_model::get_slider_options(),
		    'sidebars' => $sidebars,
		    'img_appearance'=>array(
			array('name' => 'rounded', 'value' => __('Rounded', 'themify')),
			array('name' => 'drop-shadow', 'value' => __('Drop Shadow', 'themify')),
			array('name' => 'bordered', 'value' => __('Bordered', 'themify')),
			array('name' => 'circle', 'value' => __('Circle', 'themify'), 'help' => __('Circle style works better for square image ratio.', 'themify'))
		    ),
		    'link_to'=>array(
			array( 'name' => __( 'Permalink', 'themify' ), 'value' => 'permalink' ),
			array( 'name' => __( 'Media File', 'themify' ), 'value' => 'media' ),
			array( 'name' => __( 'Custom', 'themify' ), 'value' => 'custom' ),
			array( 'name' => __( 'None', 'themify' ), 'value' => 'none' )
		    ),
		    'link_type' => array(
			array('value' => 'regular', 'name' => __('Same window', 'themify')),
			array('value' => 'lightbox', 'name' => __('Lightbox', 'themify')),
			array('value' => 'newtab', 'name' => __('New tab', 'themify'))
		    ),
		    'echoose' => array(
			'' => '',
			'yes' => $y,
			'no' => $n
		    ),
		    'choose' => array(
			'yes' => $y,
			'no' => $n
		    ),
		    'rchoose' => array(
			'no' => $n,
			'yes' => $y
		    ),
		    'order' => array(
			'desc' => __('Descending', 'themify'),
			'asc' => __('Ascending', 'themify')
		    ),
		    'orderBy' => array(
			'date' => __('Date', 'themify'),
			'ID' => __('Id', 'themify'),
			'author' => __('Author', 'themify'),
			'title' => __('Title', 'themify'),
			'name' => __('Name', 'themify'),
			'modified' => __('Modified', 'themify'),
			'rand' => __('Random', 'themify'),
			'comment_count' => __('Comment Count', 'themify'),
			'meta_value' => __('Custom Field String', 'themify'),
			'meta_value_num' => __('Custom Field Numeric', 'themify')
		    ),
            'display'=> array(
				'' => '',
				'block' => __('Block', 'themify'),
				'inline-block' => __('Inline', 'themify'),
			    'none' => __('None', 'themify')
			),
            'va_display'=> array(
				'' => '',
				'top' => __('Top', 'themify'),
				'middle' => __('Middle', 'themify'),
			    'bottom' => __('Bottom', 'themify')
			)
		)
	    );
	    unset($sidebars);
	    $image_size = themify_get_image_sizes_list(false);
	    if (!empty($image_size)) {
			$options['options']['image_size'] = array_merge(
				array( '' => '' ),
				$image_size
			);
		}
	    return $options;
	}

	/**
	 * Load admin js and css
	 * @param $hook
	 */
	public function check_admin_interface($hook) {
	    if (in_array($hook, array('post-new.php', 'post.php'), true) && in_array(get_post_type(), themify_post_types(), true) && Themify_Builder_Model::hasAccess()) {
		add_action('admin_footer', array($this, 'load_javascript_template_admin'), 10);
		add_filter('admin_body_class', array($this, 'admin_body_class'), 10, 1);
		add_filter('mce_css', array($this, 'builder_static_badge_css'));
	    }
	}

	private function load_admin_interface() {
	    wp_enqueue_script('themify-static-badge', themify_enque(THEMIFY_BUILDER_URI . '/js/themify-builder-static-badge.js'), array('mce-view'), true, THEMIFY_VERSION);
	    wp_enqueue_style('themify-builder-loader', themify_enque(THEMIFY_BUILDER_URI . '/css/themify.builder.loader.css'), null, THEMIFY_VERSION);
	    wp_enqueue_style('themify-builder-combine', themify_enque(THEMIFY_BUILDER_URI . '/css/combine.css'), null, THEMIFY_VERSION);
	    wp_enqueue_style('themify-builder-toolbar', themify_enque(THEMIFY_BUILDER_URI . '/css/toolbar.css'), null, THEMIFY_VERSION);
	    
	    wp_enqueue_style('themify-builder-admin-ui', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui.css'), null, THEMIFY_VERSION);
	    wp_enqueue_style('themify-builder-style', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-style.css'), null, THEMIFY_VERSION);
	    if (is_rtl()) {
		wp_enqueue_style('themify-builder-toolbar-rtl', themify_enque(THEMIFY_BUILDER_URI . '/css/toolbar-rtl.css'), null, THEMIFY_VERSION);
		wp_enqueue_style('themify-builder-admin-ui-rtl', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui-rtl.css'), array('themify-builder-admin-ui'), THEMIFY_VERSION);
	    }
	    // Enqueue builder admin scripts
	    $enqueue_scripts = array(
		'main',
		'jquery-ui-core',
		'jquery-ui-draggable',
		'jquery-ui-selectmenu',
		'jquery-ui-sortable',
		'themify-builder-google-webfont',
		'themify-combobox',
		'themify-builder-common-js',
		'themify-builder-app-js',
		'themify-builder-backend-js'
	    );
	    $id=get_the_ID();
	    foreach ($enqueue_scripts as $script) {
		switch ($script) {
		    case 'main':
			wp_enqueue_script('themify-main-script', themify_enque(THEMIFY_URI . '/js/main.js'), null, THEMIFY_VERSION, true);
			break;
		    case 'themify-combobox':
			wp_enqueue_style($script . '-css', themify_enque(THEMIFY_BUILDER_URI . '/css/themify.combobox.css'), null, THEMIFY_VERSION);
			wp_enqueue_script($script, THEMIFY_BUILDER_URI . '/js/themify.combobox.min.js', null, THEMIFY_VERSION, true);
			break;
		    case 'themify-builder-google-webfont':
			wp_enqueue_script($script, themify_https_esc('http://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js'), null, '1.6.26', true);
			break;
		    case 'themify-builder-common-js':
			wp_enqueue_script('themify-builder-simple-bar-js', THEMIFY_BUILDER_URI . '/js/simplebar.min.js', null,THEMIFY_VERSION, true);
			wp_enqueue_script('themify-builder-common-js', themify_enque(THEMIFY_BUILDER_URI . '/js/themify.builder.common.js'), null, THEMIFY_VERSION, true);
			break;

		    case 'themify-builder-app-js':

			wp_enqueue_script('tb_builder_js_style', themify_enque(THEMIFY_URI . '/js/generate-style.js'), null, THEMIFY_VERSION, true);
			wp_enqueue_script($script, themify_enque(THEMIFY_BUILDER_URI . '/js/themify-builder-app.js'), array('themify-builder-common-js'), THEMIFY_VERSION, true);
			global $wp_styles;
			wp_localize_script($script, 'themifyBuilder', apply_filters('themify_builder_ajax_admin_vars', array(
			    'ajaxurl' => admin_url('admin-ajax.php'),
			    'includes_url' => includes_url(),
			    'meta_url' => THEMIFY_METABOX_URI,
			    'tb_load_nonce' => wp_create_nonce('tb_load_nonce'),
			    'post_ID' => $id,
			    'isTouch' => themify_is_touch() ? 'true' : 'false',
			    'isThemifyTheme' => themify_is_themify_theme() ? 'true' : 'false',
			    'disableShortcuts' => themify_builder_get('setting-page_builder_disable_shortcuts', 'builder_disable_shortcuts'),
			    'widget_css' => array(home_url($wp_styles->registered['widgets']->src), home_url($wp_styles->registered['customize-widgets']->src)),
			    // Output builder data to use by Backbone Models
			    'builder_data' => $this->get_builder_data($id),
			    'modules' => Themify_Builder_Model::get_modules_localize_settings(),
			    'i18n' => self::get_i18n(),
			    'paths' => self::get_paths(),
			    'builder_url' => THEMIFY_BUILDER_URI,
			    'ticks' => self::get_tick_options(),
			    'is_gutenberg_editor' => Themify_Builder_Model::is_gutenberg_editor(),
			    'debug' => defined('THEMIFY_DEBUG') && THEMIFY_DEBUG,
			    'import_nonce' => wp_create_nonce('themify_builder_import_filethemify-builder-plupload'),
			    'custom_css'=>get_post_meta( $id, 'tbp_custom_css', true ),
			    // Breakpoints
			    'breakpoints' => themify_get_breakpoints(),
				'cf_api_url' => Themify_Custom_Fonts::$api_url
			)));
			break;
		    case 'themify-builder-backend-js':
			wp_enqueue_script('jquery-knob', THEMIFY_BUILDER_URI . '/js/jquery.knob.min.js', null, THEMIFY_VERSION, true);
			wp_enqueue_script('themifyGradient', themify_enque(THEMIFY_BUILDER_URI . '/js/themifyGradient.js'), array('themify-colorpicker'), THEMIFY_VERSION, true);
			wp_register_script('themify-builder-backend-js', themify_enque(THEMIFY_BUILDER_URI . '/js/themify-builder-backend.js'), array('themify-builder-app-js'), THEMIFY_VERSION, true);
			wp_enqueue_script('themify-builder-backend-js');
			wp_localize_script($script, 'themify_builder_plupload_init', Themify_Builder_Model::get_builder_plupload_init());
			wp_localize_script($script, 'tbLocalScript', array('version' => THEMIFY_VERSION));
			break;

		    default:
			wp_enqueue_script($script);
			break;
		}

	    }
	    
	    do_action('themify_builder_admin_enqueue');
	}

	/**
	 * Register styles and scripts necessary for Builder template output.
	 * These are enqueued when user initializes Builder or from a template output.
	 *
	 * Registered style handlers:
	 *
	 * Registered script handlers:
	 * themify-builder-module-plugins-js
	 * themify-builder-script-js
	 *
	 * @since 2.1.9
	 */
	public function register_js_css() {
	    wp_enqueue_style('builder-styles', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-style.css'), null, THEMIFY_VERSION);
	    if ( is_rtl() ) {
		    wp_enqueue_style( 'builder-styles-rtl', themify_enque( THEMIFY_BUILDER_URI . '/css/themify-builder-style-rtl.css' ), null, THEMIFY_VERSION );
	    }
	    add_filter('style_loader_tag', array($this, 'builder_stylesheet_style_tag'), 15, 4);
	    add_action('wp_footer', array($this, 'footer_js'));
	}

	public function footer_js() {
	    if(Themify_Builder_Model::is_front_builder_activate()){
		foreach (Themify_Builder_Model::$modules as $m) {
		    $assets = $m->get_assets();
		    if (!empty($assets)) {
			Themify_Builder_Component_Module::add_modules_assets($m->slug, $assets);
		    }
		}
	    }
	    wp_localize_script('themify-main-script', 'tbLocalScript', apply_filters('themify_builder_script_vars', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'isAnimationActive' => Themify_Builder_Model::is_animation_active(),
		'isParallaxActive' => Themify_Builder_Model::is_parallax_active(),
		'isScrollEffectActive' => Themify_Builder_Model::is_scroll_effect_active(),
		'isStickyScrollActive' => Themify_Builder_Model::is_sticky_scroll_active(),
		'animationInviewSelectors' => array('.module.wow', '.module_row.wow', '.builder-posts-wrap > .post.wow'),
		'backgroundSlider' => array(
		    'autoplay' => 5000,
		),
		'animationOffset' => 100,
		'videoPoster' => THEMIFY_BUILDER_URI . '/img/blank.png',
		'backgroundVideoLoop' => 'yes',
		'builder_url' => THEMIFY_BUILDER_URI,
		'framework_url' => THEMIFY_URI,
		'version' => THEMIFY_VERSION,
		'fullwidth_support' => Themify_Builder_Model::is_fullwidth_layout_supported(),
		'fullwidth_container' => 'body',
		'loadScrollHighlight' => true,
		'addons' => Themify_Builder_Component_Module::get_modules_assets(),
		'breakpoints' => themify_get_breakpoints(),
		'ticks' => self::get_tick_options(),
		'cf_api_url' => Themify_Custom_Fonts::$api_url,
		'emailSub' => __('Check this out!','themify')
	    )));
	    //Inject variable values in gallery script
	    wp_localize_script('themify-main-script', 'themifyScript', array(
		'lightbox' => themify_lightbox_vars_init(),
		'lightboxContext' => apply_filters('themify_lightbox_context', 'body')
		    )
	    );
	    //Inject variable values in Scroll-Highlight script
	    wp_localize_script('themify-main-script', 'tbScrollHighlight', apply_filters('themify_builder_scroll_highlight_vars', array(
		'fixedHeaderSelector' => '',
		'speed' => 900,
		'navigation' => '#main-nav, .module-menu .ui.nav',
		'scrollOffset' => 0
	    )));
	}

	/**
	 * Prevent builder-style.css stylesheet from loading in the page, the stylesheet is loaded in themify.builder.script.js
	 *
	 * @return html
	 */
	public function builder_stylesheet_style_tag($tag, $handle, $href, $media) {
	    if ( 'builder-styles' === $handle) {
		remove_filter('style_loader_tag', array($this, 'builder_stylesheet_style_tag'), 15, 4);
		$tag= '<meta name="builder-styles-css" data-href="' . $href . '" content="builder-styles-css" id="builder-styles-css" />' . "\n";
	    }
	    return $tag;
	}

	public function defer_js($tag, $handle, $src) {
	    if (in_array($handle, self::$async, true)) {
		return str_replace(' src', ' async="true" src', $tag);
	    }
	    return $tag;
	}

	public function shortcode_preview() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
	    if (!empty($_POST['shortcode'])) {
		$images = Themify_Builder_Model::get_images_from_gallery_shortcode(sanitize_text_field($_POST['shortcode']));
		$result = array();
		if (!empty($images)) {
		    foreach ($images as $image) {
			$img_data = wp_get_attachment_image_src($image->ID, 'thumbnail');
			$result[] = $img_data[0];
		    }
		}
		echo json_encode($result);
	    }
	    wp_die();
	}

	public function themify_get_post_types() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
	    if (isset($_POST['type'])) {
		$result = array();
			if(!empty($_POST['all']) && 'true' === $_POST['all']){
				$result['any'] = array('name' => __('All','themify'),'options'=>'');
			}
		$post_types = false;
		if ($_POST['type'] === 'post_types') {
		    $taxes = Themify_Builder_Model::get_public_taxonomies();
		    $exclude = !empty($_POST['exclude']) && 'false' === $_POST['exclude'] ? false : true;
		    $post_types = Themify_Builder_Model::get_public_post_types($exclude);
		    foreach ($post_types as $k => $v) {
			$result[$k] = array('name' => $v);
			$post_type_tax = get_object_taxonomies($k);
			foreach ($post_type_tax as $t) {
			    if (isset($taxes[$t])) {
				if (!isset($result[$k]['options'])) {
				    $result[$k]['options'] = array();
				}
				$result[$k]['options'][$t] = array('name' => $taxes[$t]);
			    }
			}
		    }
		} else if ($_POST['type'] === 'terms' && !empty($_POST['v'])) {
		    $tax = get_taxonomy($_POST['v']);
		    if (!empty($tax)) {
			$terms_by_tax = get_terms(array(
			    'hide_empty' => true,
			    'number' => 100,
			    'no_found_rows' => true,
			    'orderby' => 'name',
			    'order' => 'ASC',
			    'taxonomy' => $tax->name
			));
			$result['0'] = $tax->labels->all_items;
			unset($tax);
			foreach ($terms_by_tax as $v) {
			    $result[$v->slug] = $v->name;
			}
		    }
		}
		echo json_encode(apply_filters('themify_builder_query_post', $result, $_POST['type'], $post_types));
	    }
	    die;
	}

	public function tb_get_menu() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
	    $menu = get_terms('nav_menu', array('hide_empty' => false));
	    array_unshift($menu, array('slug' => '', 'name' => ''));
	    die(json_encode($menu));
	}

	public function themify_get_tax() {
	    if (!empty($_GET['tax']) && !empty($_GET['term'])) {
		$terms_by_tax = get_terms(sanitize_key($_GET['tax']), array('hide_empty' => true, 'name__like' => sanitize_text_field($_GET['term'])));
		$items = array();
		if (!empty($terms_by_tax)) {
		    foreach ($terms_by_tax as $t) {
			$items[] = array('value' => $t->slug, 'label' => $t->name);
		    }
		}
		echo wp_json_encode($items);
	    }
	    wp_die();
	}

	public function themify_builder_get_tax_data() {
	    if (!empty($_POST['data'])) {
		$respose = array();
		foreach ($_POST['data'] as $k => $v) {
		    $tax = key($v);
		    $slug = $v[$tax];
		    $terms_by_slug = get_term_by('slug', $slug, $tax);
		    $respose[] = array('tax' => $tax, 'val' => $terms_by_slug->name);
		}
		echo wp_json_encode($respose);
	    }
	    wp_die();
	}

	/**
	 * Load module partial when update live content
	 */
	public function load_module_partial_ajaxify() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
	    $cid = $_POST['tb_cid'];
	    $identifier = array($cid);
	    self::$frontedit_active = true;
	    if (!empty($_POST['tb_post_id'])) {
		    Themify_Builder_Component_Base::$post_id =self::$builder_active_id= $_POST['tb_post_id'];
	    }
	    $new_modules = array(
		'mod_name' => $_POST['tb_module_slug'],
		'mod_settings' => json_decode(stripslashes($_POST['tb_module_data']), true)
	    );
	     if(isset($_POST['element_id'])){
		$new_modules['element_id'] = $_POST['element_id'];
	    }
	    Themify_Builder_Component_Module::template($new_modules, $cid, true, $identifier);

	    wp_die();
	}

	public function render_element_ajaxify() {
	    check_ajax_referer( 'tb_load_nonce', 'tb_load_nonce' );

		$response = array();
		$batch = json_decode( stripslashes( $_POST['batch'] ), true );

		if ( !empty( $_POST['tb_post_id'] ) ) {
			Themify_Builder_Component_Base::$post_id=self::$builder_active_id = $_POST['tb_post_id'];
		}
		if ( !empty( $_POST['tmpGS'] ) ) {
		    Themify_Global_Styles::$used_styles[self::$builder_active_id]=Themify_Global_Styles::addGS(self::$builder_active_id ,json_decode(stripslashes($_POST['tmpGS']),true));
		}
		if ( !empty( $batch ) ) {
			$used_gs = array();
			foreach ( $batch as $b ) {
				$type = $b['data']['elType'];
				self::$frontedit_active = true;
				switch ( $type ) {
					case 'module':
						if(isset($_POST['element_id'])){
						    $b['data']['element_id'] = $_POST['element_id'];
						}
						$markup = Themify_Builder_Component_Module::template( $b['data'], self::$builder_active_id, false, array( $b['jobID'] ) );
						break;

					case 'subrow':
						unset( $b['data']['cols'] );
						$b['data']['row_order'] = $b['jobID'];
						$markup = Themify_Builder_Component_SubRow::template( $b['jobID'], $b['jobID'], $b['jobID'], $b['data'], self::$builder_active_id );
						break;

					case 'column':
						unset( $b['data']['modules'] );
						$b['data']['column_order'] = $b['jobID'];
						$markup = Themify_Builder_Component_Column::template( $b['jobID'], array( 'row_order' => $b['jobID'] ), $b['jobID'], $b['data'], self::$builder_active_id );
						break;

					case 'row':
						unset( $b['data']['cols'] );
						$b['data']['row_order'] = $b['jobID'];
						$markup = Themify_Builder_Component_Row::template( $b['jobID'], $b['data'],self::$builder_active_id);
						break;
				}
				$response[ $b['jobID'] ] = $markup;
				if(!empty($b['data']['attached_gs'])){
				    $used_gs = array_merge($used_gs,$b['data']['attached_gs']);
                }
			}
		}
		if ( !empty( $used_gs ) ) {
			$used_gs = array_unique($used_gs);
		    // Return used gs if used
			$args = array(
			    'exclude' => empty($_POST['loadedGS']) ? array() : $_POST['loadedGS'],
			    'include' => $used_gs,
			    'limit' => -1,
			    'data' => true
			);
			$usedGS = Themify_Global_Styles::get_global_styles($args);
			if(!empty($usedGS)){
			    $response['gs'] = $usedGS;
			}
		}
	    die(json_encode($response));
	}

	public function render_element_shortcode_ajaxify() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');

	    $shortcodes = $styles = array();
	    $shortcode_data = json_decode(stripslashes_deep($_POST['shortcode_data']), true);

	    if (is_array($shortcode_data)) {
		foreach ($shortcode_data as $shortcode) {
		    $shortcodes[] = array('key' => $shortcode, 'html' => Themify_Builder_Model::format_text($shortcode));
		}
	    }

	    global $wp_styles;
	    if (isset($wp_styles) && !empty($shortcodes)) {
		ob_start();
		$tmp = $wp_styles->do_items();
		ob_end_clean();
		foreach ($tmp as $handler) {
		    if (isset($wp_styles->registered[$handler])) {
			$styles[] = array(
			    's' => $wp_styles->registered[$handler]->src,
			    'v' => $wp_styles->registered[$handler]->ver,
			    'm' => isset($wp_styles->registered[$handler]->args) ? $wp_styles->registered[$handler]->args : 'all'
			);
		    }
		}
		unset($tmp);
	    }

	    wp_send_json_success(array(
		'shortcodes' => $shortcodes,
		'styles' => $styles
	    ));
	}

	/**
	 * Save builder main data
	 */
	public function save_data_builder() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
	    // Information about writing process.
	    $results = array();
	    $data = json_decode(stripslashes_deep($_POST['data']), true);
	    if(empty($data)){
			$data=array();
	    }
	    if (is_array($data)) {
			self::$builder_is_saving = true;
			$post_id = (int) $_POST['id'];
			global $ThemifyBuilder_Data_Manager;
			$results = $ThemifyBuilder_Data_Manager->save_data($data, $post_id, 'main', $_POST['sourceEditor']);
			if ( isset( $_POST['custom_css'] ) ) {
				if(!empty($_POST['custom_css'])){
					update_post_meta( $post_id, 'tbp_custom_css', $_POST['custom_css'] );
					$results['custom_css'] = $_POST['custom_css'];
				}
				else{
					delete_post_meta( $post_id, 'tbp_custom_css');
				}
			}
			$data= null;
			$results['builder_data'] = json_decode($results['builder_data'], true);
			self::$builder_is_saving = null;
	    }
	    wp_send_json_success($results);
	}

	public function save_builder_css() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
	    $data = json_decode(stripslashes_deep($_POST['css']), true);
	    if (is_array($data)) {
			$custom_css = isset( $_POST['custom_css']) ? stripcslashes( $_POST['custom_css'] ) : '';
			echo json_encode( Themify_Builder_Stylesheet::write_stylesheet( (int) $_POST['id'], $data, $custom_css ) );
	    }
			die;
	    }

	/**
	 * Clear out static Builder data if it exists inside the_content
	 * Leaves out the bare HTML comment placeholder as Builder will later
	 * uses it to determine where its output should go (self::builder_show_on_front)
	 *
	 * @return string
	 */
	public function builder_clear_static_content($content) {
	    global $ThemifyBuilder_Data_Manager, $wp_current_filter;
	    // Skip for excerpt display
	    return in_array('get_the_excerpt', $wp_current_filter, true) || self::$builder_is_saving===true? $content : $ThemifyBuilder_Data_Manager->update_static_content_string('<!--themify_builder_static--><!--/themify_builder_static-->', $content);
	}

	/**
	 * Hook to content filter to show builder output
	 * @param $content
	 * @return string
	 */
	public function builder_show_on_front($content) {
	    global $post;
	    $post_id = get_the_id();
	    $is_gs_admin_page = isset($_GET['page']) && 'themify-global-styles' === $_GET['page']  && is_admin();
        // Exclude builder output in admin post list mode excerpt, Dont show builder on product single description
	    if (
            ! $is_gs_admin_page
			&& ( ! is_object( $post )
			|| ( is_admin() && !defined('DOING_AJAX') )
			|| (!Themify_Builder_Model::is_front_builder_activate() && false === apply_filters( 'themify_builder_display', true, $post_id ) )
			|| post_password_required()
		) ) {
			return $content;
	    }

		/* disable Builder display on WC pages. Those are handled in Themify_Builder_Plugin_Compat */
		if ( themify_is_woocommerce_active() ) {
			if (
				( is_shop() && wc_get_page_id( 'shop' ) === $post_id )
				|| ( is_singular( 'product' ) && 'product' === get_post_type() )
			) {
				return $content;
			}
		}

	    //the_excerpt
	    global $wp_current_filter;
	    if (in_array('get_the_excerpt', $wp_current_filter, true)) {
		return $content?$content:$this->get_first_text($post_id);
	    }
	    // Infinite-loop prevention
	    if (empty($this->post_ids)) {
			$this->post_ids[] = $post_id;
	    } elseif (in_array($post_id, $this->post_ids, true)) {
		// we have already rendered this, go back.
		return $content;
	    }

	    /* With the exception of single post pages, when Read More Tag is present do not show builder content. */
	    if ( strpos( $post->post_content, '<!--more-->' )!==false && ! is_single( $post->ID ) && !is_page( $post->ID ) ) {
		    return $content;
	    }

	    $content = $this->get_builder_output( $post_id, $content );

	    $this->post_ids[] = $post_id;

	    $this->post_ids = array_unique($this->post_ids);
	    if (array_shift($this->post_ids) === $post_id) {
		// the loop is finished, reset the ID list
		$this->post_ids = array();
	    }

	    return $content;
	}

	/**
	 * Renders Builder data for a given $post_id
	 *
	 * If $content is sent, the function will attempt to find the proper place
	 * where Builder content should be injected to. Otherwise, raw output is returned.
	 *
	 * @return string
	 * @since 4.6.2
	 */
	function get_builder_output( $post_id, $content = '' ) {

		/* in frontend editor, render only a container and set the frontend_builder_ids[] property */
	    if ($post_id == self::$builder_active_id && Themify_Builder_Model::is_front_builder_activate()) {
		    Themify_Builder_Stylesheet::enqueue_stylesheet(false,$post_id);
			$builder_output = sprintf('<div id="themify_builder_content-%1$d" data-postid="%1$d" class="themify_builder_content themify_builder_content-%1$d themify_builder"></div>', $post_id);
			$builder_output .= $this->get_builder_stylesheet('', $post_id);
		} else {
		$builder_data = $this->get_builder_data($post_id);
		if ( ! is_array( $builder_data ) ) {
			$builder_data = array();
		}
			$template_args = array();
		// Check For page break module
		$page_breaks = $this->count_page_break_modules($post_id);
		if ($page_breaks > 0) {
			$pb_result = $this->load_current_inner_page_content($builder_data, $page_breaks);
			$builder_data = $pb_result['builder_data'];
			$template_args['pb_pagination'] = $pb_result['pagination'];
			$pb_result = null;
		}
		Themify_Builder_Component_Base::$post_id = $post_id;
			$template_args['builder_output'] = $builder_data;
			$template_args['builder_id'] = $post_id;
		$template = $this->in_the_loop===true ? 'builder-layout-part-output.php' : 'builder-output.php';
			$builder_output = Themify_Builder_Component_Base::retrieve_template($template, $template_args, '', '', false);
		if (strpos($builder_output, 'module_row') !== false) {
			do_action('themify_builder_before_template_content_render');
		}

		Themify_Builder_Stylesheet::enqueue_stylesheet(false,$post_id);
		$builder_output = $this->get_builder_stylesheet($builder_output, $post_id) . $builder_output;
		}

		/* if $content parameter is empty, simply return the builder output, no need to replace anything */
		if ( empty( $content ) ) {
		return $builder_output;
	}

		/* find where Builder output should be injected to inside $content */
	    // Start builder block replacement
	    global $ThemifyBuilder_Data_Manager;
	    if (Themify_Builder_Model::is_gutenberg_active() && $ThemifyBuilder_Data_Manager->has_builder_block($content)) {
			$content = $ThemifyBuilder_Data_Manager->update_static_content_string('', $content); // remove static content tag
			$content = $ThemifyBuilder_Data_Manager->replace_builder_block_tag('<!--themify_builder_static--><!--/themify_builder_static-->', $content);
	    }
	    if ($ThemifyBuilder_Data_Manager->has_static_content($content)) {
			$content = $ThemifyBuilder_Data_Manager->update_static_content_string($builder_output, $content);
	    } else {
			$display_position = apply_filters('themify_builder_display_position', 'below', $post_id);
			if ('above' === $display_position) {
				$content = $builder_output . $content;
			} else {
				$content .= $builder_output;
			}
	    }
	    // Make sure static content is excluded, otherwise hide it with css
	    $content = $ThemifyBuilder_Data_Manager->wrap_static_content_if_fail($content);
			return $content;
	    }

	/**
	 * Returns <link> tag for Builder stylesheet or enqueue it properly, if necessary.
	 *
	 * @return string
	 */
	public function get_builder_stylesheet($builder_output, $post_id = false) {
	    /* in RSS feeds and REST API endpoints, do not output the scripts */
	    if (is_feed() || ( defined('REST_REQUEST') && REST_REQUEST )) {
		return;
	    }
	    if (isset($_REQUEST['themify_builder_infinite_scroll']) && $_REQUEST['themify_builder_infinite_scroll'] === 'yes' && !Themify_Builder_Model::is_front_builder_activate()) {
		$data = Themify_Builder_Stylesheet::enqueue_stylesheet(true, $post_id);
		if (!isset($data['handler'])) {
		    return '';
		}
		return '<div data-url="' . esc_attr($data['url']) . '" class="tb_style_generated"></div>';
	    }
	    static $builder_loaded = null;
	    if ($builder_loaded === null && ( Themify_Builder_Model::is_front_builder_activate() || strpos($builder_output, 'module_row') !== false )) { // check if builder has any content
		if (!isset($this->stylesheet_redo)) { // < ignore this flag, it's only for compatibility with certain plugins.
		    $builder_loaded = true;
		    wp_dequeue_style('builder-styles');
		}
		$output='';
		if(!TFCache::is_ajax()){
			// "data-no-minify" attribute excludes the stylesheet from WP Rocket's minify feature
		    $output='<link rel="preload" href="'.themify_enque( THEMIFY_BUILDER_URI . '/css/themify-builder-style.css' ).'?ver='.THEMIFY_VERSION.'" as="style" data-no-minify="1" />';
		}
		$output.='<script type="text/javascript">
				if(document.getElementById( "themify-builder-style" )===null ){
					var el =  document.getElementById( "builder-styles-css" );
					if(el!==null){
					var link = document.createElement("link");
					link.id="themify-builder-style";
					link.rel="stylesheet";
					link.type="text/css";
					link.href=el.getAttribute("data-href");
					el.parentNode.replaceChild( link, el);
					}
				}
			</script>';
		

		return $output;
	    }
	    return '';
	}

	/**
	 * Loads JS templates for front-end editor.
	 */
	public function load_javascript_template_front() {
	    add_filter('script_loader_tag', array($this, 'defer_js'), 11, 3);
	    $this->load_frontend_interface();
	    include( THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/themify-builder-js-tmpl-common.php' );
	    include( THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/themify-builder-js-tmpl-front.php' );
	    include( THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-module-panel.php' );
	}

	/**
	 * Loads JS templates for WordPress admin dashboard editor.
	 */
	public function load_javascript_template_admin() {
	    add_filter('script_loader_tag', array($this, 'defer_js'), 11, 3);
	    $this->load_admin_interface();
	    $this->print_static_content_badge_templates();
	    include( THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/themify-builder-js-tmpl-common.php' );
	    include( THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/themify-builder-js-tmpl-admin.php' );
	}

	/**
	 * Plupload ajax action
	 */
	public function builder_plupload() {
	    // check ajax nonce
	    check_ajax_referer('tb_load_nonce');
	    if (!current_user_can('upload_files')) {
		die;
	    }

	    $imgid = $_POST['imgid'];
	    /** If post ID is set, uploaded image will be attached to it. @var String */
	    $postid = $_POST['topost'];

	    /** Handle file upload storing file|url|type. @var Array */
	    $file = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'themify_builder_plupload_action'));

	    //let's see if it's an image, a zip file or something else
	    $ext = explode('/', $file['type']);

	    // Import routines
	    if ('zip' === $ext[1] || 'rar' === $ext[1] || 'plain' === $ext[1]) {

		$url = wp_nonce_url('admin.php?page=themify');

		if (false === ( $creds = request_filesystem_credentials($url) )) {
		    return true;
		}
		if (!WP_Filesystem($creds)) {
		    request_filesystem_credentials($url, '', true);
		    return true;
		}

		global $wp_filesystem;
		$is_txt = $path=$gs_path = false;
		if ('zip' === $ext[1] || 'rar' === $ext[1]) {
		    $destination = wp_upload_dir();
		    $destination_path = $destination['path'];
		    unzip_file($file['file'], $destination_path);
		    if ($wp_filesystem->exists($destination_path . '/builder_data_export.txt')) {
			$path = $destination_path . '/builder_data_export.txt';
			$is_txt = true;
		    }
			$gs_path = $destination_path . '/builder_gs_data_export.txt';
		    $gs_path = $wp_filesystem->exists($gs_path) ? $gs_path : false;
			}
			elseif ($wp_filesystem->exists($file['file'])) {
		    $path = $file['file'];
		}

		if ($path) {
		    $data = $wp_filesystem->get_contents($path);
		    $data = is_serialized($data) ? maybe_unserialize($data) : json_decode($data,true);
			if(!empty($data['custom_css'])){
				update_post_meta( $postid , 'tbp_custom_css', $data['custom_css'] );
				$file['custom_css'] = $data['custom_css'];
				unset($data['custom_css']);
			}
		    // Check if it is GS data or not
            $gsData = reset($data);
            if(is_array($gsData) && !empty($gsData['title']) && !empty($gsData['class']) && !empty($gsData['type'])){
				$args = array(
					'style-name' => $gsData['title'],
					'style-type' => $gsData['type'],
					'styles' => $gsData['data'],
					'slug' => $gsData['class'],
					'id' => $postid
				);
				$file = array_merge($file, Themify_Global_Styles::add_new($args,'import'));
            }
				else{
		    // Import attached Global Styles
            if($gs_path){
		$gs_data = $wp_filesystem->get_contents($gs_path);
		$gs_data = is_serialized($gs_data) ? maybe_unserialize($gs_data) : json_decode($gs_data);
		$used_gs = Themify_Global_Styles::builder_import($gs_data);
		$wp_filesystem->delete($gs_path);
            }
					// set data here
					$file = array_merge($file, $GLOBALS['ThemifyBuilder_Data_Manager']->save_data(Themify_Builder_Model::removeElementIds((array)$data), $postid, 'import'));
				}
            if ($is_txt) {
			$wp_filesystem->delete($path);
		    }
		    $wp_filesystem->delete($file['file']);
			} 
			else{
		    _e('Data could not be loaded', 'themify');
		}
	    } 
		else {
		// Insert into Media Library
		// Set up options array to add this file as an attachment
		$attachment = array(
		    'post_mime_type' => sanitize_mime_type($file['type']),
		    'post_title' => str_replace('-', ' ', sanitize_file_name(pathinfo($file['file'], PATHINFO_FILENAME))),
		    'post_status' => 'inherit'
		);

		if ($postid) {
		    $attach_id = wp_insert_attachment($attachment, $file['file'], $postid);
		}
		// Common attachment procedures
		require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
		$attach_data = wp_generate_attachment_metadata($attach_id, $file['file']);
		wp_update_attachment_metadata($attach_id, $attach_data);

		if ($postid) {
		    $large = wp_get_attachment_image_src($attach_id, 'large');
		    $thumb = wp_get_attachment_image_src($attach_id, 'thumbnail');

		    //Return URL for the image field in meta box
		    $file['large_url'] = $large[0];
		    $file['thumb'] = $thumb[0];
		    $file['id'] = $attach_id;
		}
	    }
		if($gs_path){
		    $file['used_gs'] = $used_gs;
		}
	    $file['type'] = $ext[1];
	    // send the uploaded file url in response
	    echo json_encode($file);
	    exit;
	}

	/**
	 * Display Toggle themify builder
	 * wp admin bar
	 */
	public function builder_admin_bar_menu($wp_admin_bar) {
	    if(!is_admin_bar_showing()){
		return;
	    }
	    $isAvailable=true;
	    $post_id=false;
	    if (themify_is_woocommerce_active() && is_shop()) {
		$post_id = Themify_Builder_Model::get_ID();
	    } else {
		$isAvailable=Themify_Builder_Model::is_frontend_editor_page() && !is_archive() && !is_home() && !is_search()&&!is_404();
		if($isAvailable===true){
		    $p = get_queried_object(); //get_the_ID can back wrong post id
		    $post_id = isset($p->ID) ? $p->ID : false;
		    unset($p);
		}
	    }
	    if ($isAvailable===true && (!$post_id || !current_user_can('edit_post', $post_id))) {
		return;
	    }
	    $isAvailable = apply_filters('themify_builder_admin_bar_is_available', $isAvailable);
		$title = '<span data-id="' . $post_id . '" class="tb_front_icon"></span>';
		$title.='<span class="tb_tooltip">'.__('Builder is not available on this page','themify').'</span>'.esc_html__('Turn On Builder', 'themify');
	    $args = array(
		array(
		    'id' => 'themify_builder',
			'title' => $title,
		    'href' => '#',
			'meta' => array('class' => 'toggle_tb_builder'.($isAvailable===true?'':' tb_disabled_turn_on'))
		)
	    );

	    $args = apply_filters('themify_builder_admin_bar_menu', $args,$isAvailable);
	    foreach ($args as $arg) {
		$wp_admin_bar->add_node($arg);
	    }
	}

	/**
	 * Switch to frontend
	 * @param int $post_id
	 */
	public function switch_frontend($post_id) {
	    //verify post is not a revision
	    if (isset($_POST['builder_switch_frontend_noncename']) && $_POST['builder_switch_frontend_noncename'] === 'ok' && self::$builder_is_saving!==true && !wp_is_post_revision($post_id)) {
		// redirect to frontend
		$_POST['builder_switch_frontend'] = 0;
		$_POST['builder_switch_frontend_noncename'] = 0;
		$post_url = get_permalink($post_id);
		wp_redirect(themify_https_esc($post_url) . '#builder_active');
		exit;
	    }
	}

	/**
	 * Disable WP Editor
	 */
	public function themify_disable_wp_editor() {
	    if (Themify_Builder_Model::isWpEditorDisable() && themify_builder_get('setting-page_builder_is_active') !== 'disable') {
		$module_list = $this->get_flat_modules_list(get_the_ID(), null, true);

		echo '<div class="themify-wp-editor-holder' . (!empty($module_list) ? ' themify-active-holder' : '' ) . '">
                                <a href="' . get_permalink() . '#builder_active">' . esc_html__('Edit With Themify Builder', 'themify') . '</a>
                        </div>';
		unset($module_list);
	    }
	}

	/**
	 * Add Builder body class
	 * @param $classes
	 * @return mixed|void
	 */
	public function body_class($classes) {
	    if (themify_is_touch()) {
		$classes[] = 'istouch';
		// return the $classes array
		$ios = $this->check_for_old_ios();
		if ($ios) {
		    $classes[] = $ios;
		}
	    }
	    if (Themify_Builder_Model::is_frontend_editor_page()) {
		$post_id = Themify_Builder_Model::get_ID();
		$uid = Themify_Builder_Model::get_edit_transient($post_id);

		if ($uid > 0 && $uid != get_current_user_id()) {
		    $classes[] = 'tb_restriction';
		    $this->restriction_id = $uid;
		    $this->restriction_data();
		} elseif (Themify_Builder_Model::is_front_builder_activate()) {
		    $classes[] = 'themify_builder_active builder-breakpoint-desktop';
		    if (empty($_SERVER['HTTP_PURPOSE']) || $_SERVER['HTTP_PURPOSE'] !== 'prefetch') {
			Themify_Builder_Model::set_edit_transient($post_id, get_current_user_id());
		    }
		}
		if(Themify_Global_Styles::$isGlobalEditPage===true){
		    $classes[]='gs_post';
		}
	    }
	    if (Themify_Builder_Model::is_parallax_active()) {
		$classes[] = 'builder-parallax-scrolling-active';
	    }
	    return apply_filters('themify_builder_body_class', $classes);
	}

	public function admin_body_class($classes) {
	    $classes .= ' builder-breakpoint-desktop tb_panel_closed';
	    $post_id = Themify_Builder_Model::get_ID();
	    $uid = Themify_Builder_Model::get_edit_transient($post_id);
	    $current = get_current_user_id();
	    if ($uid > 0 && $uid != $current) {
		$classes .= ' tb_restriction';
		$this->restriction_id = $uid;
		$this->restriction_data();
	    } elseif (!$uid) {
		Themify_Builder_Model::set_edit_transient($post_id, $current);
	    }
	    return $classes;
	}

	private function restriction_data() {
	    if (is_admin()) {
		add_action('admin_footer', array($this, 'load_restriction'));
	    } else {
		add_action('wp_footer', array($this, 'load_restriction'));
	    }
	}

	public function load_restriction($takeover = false,$id=null) {
	    $data = get_userdata($this->restriction_id);
	    include( THEMIFY_BUILDER_INCLUDES_DIR . '/tpl/themify-builder-js-tmpl-locked.php' );
	}

	/**
	 * Includes this custom post to array of cpts managed by Themify
	 * @param Array
	 * @return Array
	 */
	public function extend_post_types($types) {
	    static $post_types = null;
	    if ($post_types === null) {
		$post_types = array_unique(array_merge(
				$this->registered_post_types, array_values(get_post_types(array(
		    'public' => true,
		    '_builtin' => false,
		    'show_ui' => true,
				)))
		));
	    }
	    return array_unique(array_merge($types, $post_types));
	}

	/**
	 * Push the registered post types to object class
	 * @param $type
	 */
	public function push_post_types($type) {
	    $this->registered_post_types[] = $type;
	}

	/**
	 * Reset builder query
	 * @param $action
	 */
	public function reset_builder_query($action = 'reset') {
	    if ('reset' === $action) {
		remove_filter('the_content', array($this, 'builder_show_on_front'), 11);
	    } elseif ('restore' === $action) {
		add_filter('the_content', array($this, 'builder_show_on_front'), 11);
	    }
	}

	/**
	 * Get google fonts
	 */
	public function get_custom_google_fonts() {
	    global $themify;
	    $fonts = array();
	    if (!empty($themify->builder_google_fonts)) {
		$themify->builder_google_fonts = substr($themify->builder_google_fonts, 0, -1);
		$fonts = explode('|', $themify->builder_google_fonts);
	    }
	    return $fonts;
	}

	public function check_for_old_ios() {
	    if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod') !== false) {
		preg_match('/OS\s(\d+)_\d/', $_SERVER['HTTP_USER_AGENT'], $version);
		if (isset($version[1]) && $version[1] < 8) {
		    return 'ios7';
		}
	    }

	    return false;
	}

	public function add_minify_vars($vars) {
	    $vars['minify']['js']['themify.builder.script'] = themify_enque(THEMIFY_BUILDER_URI . '/js/themify.builder.script.js', true);
	    $vars['minify']['js']['themify.scroll-highlight'] = themify_enque(THEMIFY_BUILDER_URI . '/js/themify.scroll-highlight.js', true);
	    $vars['minify']['js']['themify-youtube-bg'] = themify_enque(THEMIFY_BUILDER_URI . '/js/themify-youtube-bg.js', true);
	    $vars['minify']['js']['themify-ticks'] = themify_enque(THEMIFY_BUILDER_URI . '/js/themify-ticks.js', true);
	    $vars['minify']['css']['themify-builder-style'] = themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-style.css', true);
	    $vars['scrollTo'] = themify_builder_get( 'setting-scrollto_offset','builder_scrollTo' );
	    return $vars;
	}

	public function static_badge_hide_content() {
	    global $post;
	    $post->post_content = preg_replace('/<!--themify_builder_static-->([\s\S]*?)<!--\/themify_builder_static-->/', '<!--themify_builder_static-->&nbsp;<!--/themify_builder_static-->', $post->post_content);
	}

	/**
	 * Static badge js template
	 */
	public function print_static_content_badge_templates() {
	    ?>
	    <script type="text/html" id="tmpl-tb-static-badge">
	        <div class="tb_static_badge_box">
		    <?php if (!Themify_Builder_Model::is_gutenberg_editor()): ?>
			<h4><?php esc_html_e('Themify Builder Placeholder', 'themify'); ?></h4>
			<p><?php esc_html_e('This badge represents where the Builder content will append on the frontend. You can move this placeholder anywhere within the editor or add content before or after.', 'themify'); ?></p>
			<p><?php echo sprintf('%s <a href="#" class="tb_mce_view_frontend_btn">%s</a> | <a href="#" class="tb_mce_view_backend_btn">%s</a>', esc_html__('Edit Builder:', 'themify'), esc_html__('Frontend', 'themify'), esc_html__('Backend', 'themify')); ?></p>
		    <?php endif; ?>
	        </div>
	    </script>
	    <?php if (Themify_Builder_Model::is_gutenberg_editor()): ?>
		<div style="display: none;"><?php wp_editor(' ', 'tb_lb_hidden_editor'); ?></div>
	    <?php endif; ?>
	    <?php
	}

	/**
	 * Register css in tinymce editor.
	 * @param string $mce_css
	 * @return string
	 */
	public function builder_static_badge_css($mce_css) {
	    $mce_css .= ', ' . themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-static-badge.css');
	    return $mce_css;
	}

	/**
	 * Save Module Favorite Data
	 *
	 * @return void
	 */
	public function save_module_favorite_data() {
	    $module = isset($_POST['module_name']) ? $_POST['module_name'] : null;
	    $module_state = isset($_POST['module_state']) ? $_POST['module_state'] : 0;

	    if ($module) {
		$user_favorite_modules = get_user_option('themify_module_favorite', get_current_user_id());
		$user_favorite_modules = !empty($user_favorite_modules) ? array_merge((array) json_decode($user_favorite_modules), array($module => $module_state)) : array($module => $module_state);
		update_user_option(get_current_user_id()
			, 'themify_module_favorite'
			, json_encode($user_favorite_modules));
	    }

	    die();
	}

	public function load_visual_templates() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
	    $response = array();
	    foreach (Themify_Builder_Model::$modules as $module) {
		$template = $module->print_template();
		if ($template) {
		    $response[$module->slug] = preg_replace('!\s+!', ' ', $template);
		}
	    }
	    echo json_encode($response);
	    wp_die();
	}

	public static function getComponentJson($onlyStyle = false,$offset=-1,$limit=1000) {
	    $return = array();
	    $i=0;
	    $data = Themify_Builder_Components_Manager::get_component_types();
	    
	 
	    foreach ($data as $k => $c) {
		if($i>=$offset && $i<$limit){
		    $return[$k] = $c->get_form_settings($onlyStyle);
		}
		++$i;
	    }
	    $data = null;
	   
	    foreach (Themify_Builder_Model::$modules as $k => $c) {
		if($i>=$offset && $i<$limit){
		    $return[$k] = $c->get_form_settings($onlyStyle);
		}
		++$i;
	    }
	    return $return;
	}

	public function load_form_templates() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
	    $limit=35;
	    $offset = ($_POST['page']-1)*$limit;
	    $data = array('v'=>self::getComponentJson(false,$offset,$_POST['page']*$limit),'d'=>Themify_Builder_Component_Base::$draggableModules,'l'=>$limit);
	    if(empty($data['v'])){
		$data=array('v'=>'ok');
	    }
	    echo json_encode($data);
	    wp_die();
	}

	private static function get_tick_options() {
	    return array(
		'tick' => Themify_Builder_Model::get_transient_time(),
		'ajaxurl' => admin_url('admin-ajax.php'),
		'postID' => Themify_Builder_Model::get_ID()
	    );
	}

	public function updateTick() {
	    if (!empty($_POST['postID'])) {
		$id = (int) $_POST['postID'];
		$uid = Themify_Builder_Model::get_edit_transient($id);
		$current = get_current_user_id();
		if (!$uid || $uid == $current || !empty($_POST['take'])) {
		    Themify_Builder_Model::set_edit_transient($id, $current);
		    echo '1';
		} elseif ($uid && $uid != $current && empty($_POST['take'])) {

		    $this->restriction_id = $uid;
		    $this->load_restriction(true,$id);
		}
	    }
	    wp_die();
	}

	public function help() {
	    check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
	    include THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-help-video.php';
	    die;
	}

	/**
	 * Load content of only current inner page
	 * @param $builder_data
	 * @param $page_breaks count of page break modules
	 * @return array
	 */
	public function load_current_inner_page_content($builder_data, $page_breaks) {
	    global $page, $multipage, $numpages;
		$temp_global = array('page'=>$page,'multipage'=>$multipage,'numpages'=>$numpages);
	    $page = get_query_var( 'paged' );
	    if(empty($page)){
		    $page=get_query_var( 'page' );
		    if(empty($page)){
			    $page=1;
		    }
	    }
	    $page=(int)$page;
	    $temp_data = array();
	    $page_num = 1;
	    foreach ($builder_data as $row) {
		if (isset($row['styling']['custom_css_row']) && strpos($row['styling']['custom_css_row'], 'tb-page-break') !== false) {
		    ++$page_num;
		    continue;
		}
		$temp_data[$page_num][] = $row;
	    }
	    // Adjust the globals
	    $page = $page > ( $page_breaks + 1 ) || $page < 1 ? 1 : $page;
	    $numpages = $page_breaks + 1;
	    $multipage = 1;
		$return['pagination'] = wp_link_pages(array('before' => '<p class="post-pagination"><strong>'.__('Pages:','themify').'</strong> ', 'after' => '</p>', 'next_or_number' => 'number','echo'=>0));
		$return['builder_data'] = isset($temp_data[$page]) ? $temp_data[$page] : $builder_data;
		// revert the globals
		$multipage = $temp_global['multipage'];
		$numpages = $temp_global['numpages'];
		$page = $temp_global['page'];
		return $return;
	}

	/**
	 * Check builder content for page break module
	 * @param $post_id
	 * @return int count of page break modules
	 */
	private function count_page_break_modules($post_id) {
	    global $ThemifyBuilder_Data_Manager;
	    $meta_key = $ThemifyBuilder_Data_Manager->meta_key;
	    $data = get_post_meta($post_id, $meta_key, true);
	    $result = preg_match_all('/"mod_name":"page-break"/', $data, $modules);
	    return $result;
	}

	/**
	 * Regenerate CSS files Ajax
	 *
	 */
	public function themify_regenerate_css_files_ajax() {
	    check_ajax_referer('ajax-nonce', 'nonce');
	    die(self::regenerate_css_files());
	}

		/**
		 * Find for old URLs in generated CSS files and regenerates them base on new URLs
		 *
		 */
    public static function regenerate_css_files() {
			if ( current_user_can( 'manage_options' ) ) {
	    $upload_dir = wp_upload_dir();
	    $wp_filesystem = Themify_Filesystem::get_instance();
				$path = '/' . trim( $upload_dir['basedir'], '/' ) . '/themify-css';
				if ( $wp_filesystem->execute->exists( $path ) ) {
					if ( !$wp_filesystem->execute->rmdir( $path, true ) ) {
						return __( 'Something goes wrong. Please check the if the upload folder is writtable.' );
		}
	    }
				return 'finished';
	}
	    return false;
    }

    public function display_custom_css() {
        $custom_css = get_post_meta( self::$builder_active_id , 'tbp_custom_css', true );
        if ( !empty($custom_css) ){
            echo PHP_EOL , '<!-- Custom Style -->' , PHP_EOL,
                '<style type="text/css" id="tb_custom_css_tmp">', PHP_EOL,
                $custom_css , PHP_EOL,
                '</style>' , PHP_EOL , '<!-- / end custom style -->' ,PHP_EOL;
        }
    }

	/**
	 * Serach in all modules fields and return text inputs
	 * @param $options array of module fields
	 * @return array
	 */
    function find_text_inputs_in_module( $options ) {
        $inputs = array();
        $allowed_types = array( 'text', 'textarea', 'wp_editor', 'custom_css', 'image', 'url' );
        foreach ( $options as $option ) {
            if ( 'builder' === $option && !empty( $options['options'] ) ) {
                $inputs = array_merge( $inputs, $this->find_text_inputs_in_module( $options['options'] ) );
            } elseif ( isset( $option['options'] ) ) {
                $inputs = array_merge( $inputs, $this->find_text_inputs_in_module( $option['options'] ) );
            } elseif ( isset( $option['tabs'] ) ) {
                $inputs = array_merge( $inputs, $this->find_text_inputs_in_module( $option['tabs'] ) );
            } elseif ( isset( $option['fields'] ) ) {
                $inputs = array_merge( $inputs, $this->find_text_inputs_in_module( $option['fields'] ) );
            } elseif ( isset( $option['type'] ) && in_array( $option['type'], $allowed_types ) ) {
                $inputs[] = $option['id'];
            }
        }
        return $inputs;
    }

	/**
	 * Define search area for find and replace tool and return required options to search
	 * @param $modules array of modules names
	 * @return array
	 */
	public function find_and_replace_search_area($modules) {
	    $modules = array_unique($modules);
	    $module_scopes = $themify_modules = array();
	    $themify_modules = Themify_Builder_Model::$modules;
	    foreach ($modules as $key => $module) {
		if (isset($themify_modules[$module])) {
		    $module = $themify_modules[$module];
		    $options = $module->get_options();
		    $module_scopes[$module->slug] = $this->find_text_inputs_in_module($options);
		} else {
		    unset($modules[$key]);
		}
	    }
	    $options = array('background_image', 'custom_css_id',);
	    foreach ($modules as $module) {
		$options[] = 'mod_title_' . $module;
		$options = array_merge($options, $module_scopes[$module]);
	    }
	    $options = array_unique($options);
	    return $options;
	}

	/**
	 * Get all posts from all post type that has builder data as post meta
	 * @param $paged array of modules names
	 * @return array posts id
	 * @since 4.1.2
	 */
	function themify_get_builder_posts($paged = 1) {
	    $result = array();
	    global $ThemifyBuilder_Data_Manager;
	    $meta_key = $ThemifyBuilder_Data_Manager->meta_key;
	    $args = array(
		'post_type' => 'any',
		'posts_per_page' => '7',
		'update_post_term_cache' => false,
		'ignore_sticky_posts'=>true,
		'update_post_meta_cache' => false,
		'cache_results' => false,
		'paged' => $paged,
		'meta_query' => array(
		    array(
			'key' => $meta_key,
			'value' => '',
			'compare' => '!=',
		    ),
		),
	    );
	    $query = new WP_Query($args);
	    $result['groups'] = $query->max_num_pages;
	    if ($query->have_posts()) {
		while ($query->have_posts()) {
		    $query->the_post();
		    $result['posts'][] = get_the_ID();
		}
		/* Restore original Post Data */
		wp_reset_postdata();
	    }
	    if (isset($_POST['from_ajax'])) {
		echo json_encode($result);
		wp_die();
	    } else {
		return $result['posts'];
	    }
	}

	/**
	 * Search for a string in all Builder data and replace with given string
	 */
		public function themify_find_and_replace_ajax() {
			check_ajax_referer( 'ajax-nonce', 'nonce' );
			if ( empty( $_POST['group_number'] ) ) {
				echo 'Invalid data';
				die();
			}
			$posts_id = $this->themify_get_builder_posts( $_POST['group_number'] );
			global $ThemifyBuilder_Data_Manager;
			$meta_key = $ThemifyBuilder_Data_Manager->meta_key;
			$replacementsCount = 0;
			foreach ( $posts_id as $post_id ) {
			    $data = get_post_meta( $post_id, $meta_key, true );
				preg_match_all( '/"mod_name":"(.*?)"/', $data, $modules );
				$options = $this->find_and_replace_search_area( $modules[1] );
				// Replace original urls with new url without slashes
				$original_string = addcslashes( $_POST['original_string'], '"/' );
				$new_data = $data;
				foreach ( $options as $option ) {
					$new_data = preg_replace_callback(
						'/"' . $option . '":"(((?!"[},]).)*' . $original_string . '((?!"[},]).)*)/', function ( $matches ) use ( $option, $original_string ) {
						global $replacementsCount;
						$return = preg_replace( "/($original_string)/U", $_POST['replace_string'], $matches[1], -1, $count );
						$return = '"' . $option . '":"' . $return;
						$replacementsCount += $count;
						return $return;
					}, $new_data
					);
				}
				// Replace original urls with new url with this pattern (http://test.com\/wp-content\/uploads\/2018\/03\/test.jpg) ot this pattern (http://example.com/)
				$pos1 = strpos( $_POST['original_string'], '/wp-content' );
				$pos2 = strpos( $_POST['replace_string'], '/wp-content' );
				if ( false !== $pos1 || false !== $pos2 ) {
					if ( false !== $pos1 ) {
						$part1 = addcslashes( substr( $_POST['original_string'], 0, $pos1 ), '/' );
						$part2 = addcslashes( addcslashes( addcslashes( substr( $_POST['original_string'], $pos1 ), '/' ), '/' ), '/' );
						$original_string = $part1 . $part2;
					}
					if ( false !== $pos2 ) {
						$part1 = addcslashes( substr( $_POST['replace_string'], 0, $pos2 ), '/' );
						$part2 = addcslashes( addcslashes( addcslashes( substr( $_POST['replace_string'], $pos1 ), '/' ), '/' ), '/' );
						$replace_string = $part1 . $part2;
					}
					foreach ( $options as $option ) {
						$new_data = preg_replace_callback(
							'/"' . $option . '":"(((?!"[},]).)*' . $original_string . '((?!"[},]).)*)/', function ( $matches ) use ( $option, $original_string, $replace_string ) {
							global $replacementsCount;
							$return = preg_replace( "/($original_string)/U", $replace_string, $matches[1], -1, $count );
							$return = '"' . $option . '":"' . $return;
							$replacementsCount += $count;
							return $return;
						}, $new_data
						);
					}
				} else if ( '/' === substr( $_POST['original_string'], -1, 1 ) || '/' === substr( $_POST['replace_string'], -1, 1 ) ) {
					$original_string = ( '/' === substr( $_POST['original_string'], -1, 1 ) ) ? addcslashes( substr( $_POST['original_string'], 0, -1 ), '/' ) . '\\\\\/' : addcslashes( $_POST['original_string'], '/' );
					$replace_string = ( '/' === substr( $_POST['replace_string'], -1, 1 ) ) ? addcslashes( substr( $_POST['replace_string'], 0, -1 ), '/' ) . '\\\\\/' : addcslashes( $_POST['replace_string'], '/' );
					foreach ( $options as $option ) {
						$new_data = preg_replace_callback(
							'/"' . $option . '":"(((?!"[},]).)*' . $original_string . '((?!"[},]).)*)/', function ( $matches ) use ( $option, $original_string, $replace_string ) {
							global $replacementsCount;
							$return = preg_replace( "/($original_string)/U", $replace_string, $matches[1], -1, $count );
							$return = '"' . $option . '":"' . $return;
							$replacementsCount += $count;
							return $return;
						}, $new_data
						);
					}
				}
				// Replace original urls with new url with slashes
				if ( false !== strpos( $_POST['original_string'], '/' ) || false !== strpos( $_POST['replace_string'], '/' ) ) {
					$original_string = addcslashes( $_POST['original_string'], '/' );
					$original_url_with_slashes = addcslashes( addcslashes( $original_string, '"/' ), '"/' );
					$replace_url_with_slashes = addcslashes( $_POST['replace_string'], '"/' );
					foreach ( $options as $option ) {
						$new_data = preg_replace_callback(
							'/"' . $option . '":"(((?!"[},]).)*' . $original_url_with_slashes . '((?!"[},]).)*)/', function ( $matches ) use ( $option, $original_url_with_slashes, $replace_url_with_slashes ) {
							global $replacementsCount;
							$return = preg_replace( "/($original_url_with_slashes)/U", $replace_url_with_slashes, $matches[1], -1, $count );
							$return = '"' . $option . '":"' . $return;
							$replacementsCount += $count;
							return $return;
						}, $new_data
						);
					}
				}
				if ( $data !== $new_data ) {
					$data = json_decode( $new_data, true );
					if ( is_array( $data ) ) {
						$ThemifyBuilder_Data_Manager->save_data( $data, $post_id );
					}
				}
			}
			global $replacementsCount;
			set_transient( 'themify_find_and_replace_in_progress_' . $_POST['group_number'], $replacementsCount, 1000 );
			if ( 1 === $_POST['group_number'] ) {
				set_transient( 'themify_find_and_replace_in_progress', true, 1000 );
			}
			$finished = true;
			$totalReplacements = 0;
			for ( $i = 1; $i <= $_POST['total_groups']; ++$i ) {
				if ( $i == $_POST['group_number'] ) {
					$totalReplacements += $replacementsCount;
					continue;
				}
				$status = get_transient( 'themify_find_and_replace_in_progress_' . $i );
				if ( false === $status ) {
					$finished = false;
					break;
				} else {
					$totalReplacements += $status;
				}
			}
			if ( $finished ) {
				for ( $i = 1; $i <= $_POST['total_groups']; ++$i ) {
					delete_transient( 'themify_find_and_replace_in_progress_' . $i );
				}
				delete_transient( 'themify_find_and_replace_in_progress' );
				echo 'finished-' . $totalReplacements;
			} else {
				echo $_POST['group_number'] . ' from ' . $_POST['total_groups'];
			}
			die();
		}

	/**
	 * Actions to perform when login via Login module fails
	 *
	 * @since 4.5.4
	 */
	function wp_login_failed( $username ) {
		if ( ! isset( $_SERVER['HTTP_REFERER'] ) ) {
			return;
		}
		$referrer = $_SERVER['HTTP_REFERER'];  // where did the post submission come from?
		// if there's a valid referrer, and it's not the default log-in screen
		if ( isset( $_POST['tb_login'], $_POST['tb_redirect_fail'] )  && (int) $_POST['tb_login'] === 1 && ! empty( $referrer ) && ! strstr( $referrer, 'wp-login' ) && ! strstr( $referrer, 'wp-admin' ) ) {
				wp_redirect( $_POST['tb_redirect_fail'] );
				exit;
			}
		}

		/**
		 * Load pages for maintenance page dropdown
		 */
		public function load_maintenance_pages() {
			check_ajax_referer( 'ajax-nonce', 'nonce' );
			$pages = get_pages();
			$selected = themify_builder_get( 'setting-page_builder_maintenance_page', 'tools_maintenance_page' );
			$output = sprintf( '<option value=""%s></option>',selected('',$selected));
			foreach ( $pages as $page ) {
				$output .= sprintf( '<option value="%s"%s>%s</option>',
					$page->post_name,
					selected($page->post_name,$selected),
					$page->post_title
				);
			}
			echo $output;
			die();
		}

		/**
		 * Load Maintenance mode if is set
		 */
		public function load_maintenance_mode() {
			if( 'wp-login.php' !== $GLOBALS['pagenow'] && !is_user_logged_in()){
			$pre = 'setting-page_builder_';
			$is_enabled = (bool) themify_builder_get($pre . 'maintenance_mode', 'tools_maintenance_mode') && (bool) themify_builder_get($pre . 'maintenance_page', 'tools_maintenance_page');
				if ( ! $is_enabled ) {
				return;
			}
			// Priority = 11 that is *after* WP default filter `redirect_canonical` in order to avoid redirection loop.
			add_action( 'template_redirect', array( $this, 'maintenance_redirect' ), 11 );
		}
		}
		/**
		 * redirect to Maintenance mode
		 */
		public function maintenance_redirect() {
			$selected_value = themify_builder_get( 'setting-page_builder_maintenance_page', 'tools_maintenance_page' );
			$selected_page = empty( $selected_value ) ? '' : get_page_by_path( $selected_value, OBJECT, 'page' );
			if( !empty($selected_page) && !is_page($selected_value) ){
				exit( wp_redirect( get_page_link($selected_page->ID) ));
			}
		}

	/**
	 * Handles Ajax request to get results for "autocomplete" fields in Builder
	 *
	 * Calls "tb_autocomplete_dataset_{$dataset}" filter which should return an assoc-array
	 *
	 * @since 4.6.5
	 */
	public function tb_get_autocomplete() {
		if ( ! isset($_POST['value'] ) || empty( $_POST['dataset'] ) ) {
			wp_send_json_error();
		}
		$pid = (int) $_POST['pid'];
		$value = sanitize_text_field( $_POST['value'] );
		$dataset = sanitize_text_field( $_POST['dataset'] );

		$result = apply_filters( "tb_autocomplete_dataset_{$dataset}", array(), $value, $pid );
		wp_send_json_success( $result );
	}

	/**
	 * Handles Ajax request to get dynamic values to fill "select" field type
	 *
	 * Calls "tb_select_dataset_{$dataset}" filter
	 *
	 * @since 4.6.5
	 */
	public function tb_get_select_dataset() {
		if ( empty( $_POST['dataset'] ) ) {
			wp_send_json_error();
		}
		$pid = (int) $_POST['pid'];
		$dataset = sanitize_text_field( $_POST['dataset'] );

		/**
		 * The return value should be in the format of:
		 *
		 *     array(
		 *         'options' => array(
		 *             {value} => {label},
		 *         )
		 *     )
		 * 
		 * Or for select fields with multiple groups:
		 *
		 *     array(
		 *         'optgroup' => true,
		 *         'options' => array(
		 *             {group_key} => array(
		 *                 'label' => {group_label},
		 *                 'options' => array(
		 *                     {option_value} => {option_label},
		 *                 )
		 *             ),
		 *         )
		 *     )
		 * 
		 */
		$result = apply_filters( "tb_select_dataset_{$dataset}", array(), $pid );
		wp_send_json_success( $result );
	}

	/**
	 * Hooked to 'tb_autocomplete_dataset_custom_fields'
	 *
	 * @return array
	 */
	function autocomplete_dataset_custom_fields( $result, $value, $pid ) {
		return $this->search_custom_fields( $value );
	}

	/**
	 * Returns a list of custom field keys matching $name
	 *
	 * @return array
	 */
	function search_custom_fields( $name ) {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key FROM {$wpdb->prefix}postmeta WHERE meta_key like %s LIMIT 50",
				$name . '%'
			),
		OBJECT );
		return wp_list_pluck( $results, 'meta_key', 'meta_key' );
	}
	
	//temp code for compatibility  builder new version with old version of addons to avoid the fatal error, can be removed after updating(2017.07.20)
	public function register_directory() {//deprecated need for Flow
	    
	}
	
	public function is_themify_theme() {//deprecated need for Flow
	    
	}
	
	public function retrieve_template(){//deprecated need for Flow
	   
	}
}
endif;
