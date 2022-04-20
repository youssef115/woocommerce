<?php

/**
 * Class for interact with DB or data resource and state.
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */
final class Themify_Builder_Model {

    /**
     * Feature Image Size
     * @var array
     */
    public static $featured_image_size = array();

    /**
     * Image Width
     * @var array
     */
    public static $image_width = array();

    /**
     * Image Height
     * @var array
     */
    public static $image_height = array();

    /**
     * External Link
     * @var array
     */
    public static $external_link = array();

    /**
     * Lightbox Link
     * @var array
     */
    public static $lightbox_link = array();
    public static $modules = array();
    public static $layouts_version_name = 'tbuilder_layouts_version';
    private static $transient_name = 'tb_edit_';

    /**
     * Active custom post types registered by Builder.
     *
     * @var array
     */
    public static $builder_cpt = array();

    /**
     * Directory Registry
     */
    private static $directory_registry = array();

    private function __construct() {
	
    }

    public static function register_module($module_class) {
	$instance = new $module_class();
	self::$modules[$instance->slug] = self::is_front_builder_activate() || is_admin() ? self::add_favorite_option($instance) : $instance;
    }

    /**
     * Add favorite option to module instance
     * @return object
     */
    private static function add_favorite_option(Themify_Builder_Component_Module $instance) {
	static $fv = NULL;
	if ($fv === NULL) {
	    $fv = get_user_option('themify_module_favorite', get_current_user_id());
	    $fv = !empty($fv) ? (array) json_decode($fv) : array();
	}
	$instance->favorite = !empty($fv[$instance->slug]);
	return $instance;
    }

    /**
     * Check whether builder is active or not
     * @return bool
     */
    public static function builder_check() {
	static $is = NULL;
	if ($is === null) {
	    $is = apply_filters('themify_enable_builder', themify_builder_get('setting-page_builder_is_active', 'builder_is_active'));
	    $is = !( 'disable' === $is );
	}
	return $is;
    }

    /**
     * Check whether module is active
     * @param $name
     * @return boolean
     */
    public static function check_module_active($name) {
	return isset(self::$modules[$name]);
    }

    /**
     * Check is frontend editor page
     */
    public static function is_frontend_editor_page() {
	static $is = NULL;
	if ($is === null) {
	    $is = is_user_logged_in() && current_user_can('edit_pages', self::get_id());
	    $is = apply_filters('themify_builder_is_frontend_editor', $is);
	}
	return $is;
    }

    /**
     * Check if builder frontend edit being invoked
     */
    public static function is_front_builder_activate() {
	static $is = NULL;
	if ($is === null) {
	    $is = isset($_GET['tb-preview']) && self::is_frontend_editor_page();
	}
	return $is;
    }

    /**
     * Load general metabox fields
     */
    public static function load_general_metabox() {
	// Featured Image Size
	self::$featured_image_size = apply_filters('themify_builder_metabox_featured_image_size', array(
	    'name' => 'feature_size',
	    'title' => __('Image Size', 'themify'),
	    'description' => sprintf(__('Image sizes can be set at <a href="%s">Media Settings</a>', 'themify'), admin_url('options-media.php')),
	    'type' => 'featimgdropdown'
	));
	// Image Width
	self::$image_width = apply_filters('themify_builder_metabox_image_width', array(
	    'name' => 'image_width',
	    'title' => __('Image Width', 'themify'),
	    'description' => '',
	    'type' => 'textbox',
	    'meta' => array('size' => 'small')
	));
	// Image Height
	self::$image_height = apply_filters('themify_builder_metabox_image_height', array(
	    'name' => 'image_height',
	    'title' => __('Image Height', 'themify'),
	    'description' => '',
	    'type' => 'textbox',
	    'meta' => array('size' => 'small'),
	    'class' => self::is_img_php_disabled() ? 'builder_show_if_enabled_img_php' : '',
	));
	// External Link
	self::$external_link = apply_filters('themify_builder_metabox_external_link', array(
	    'name' => 'external_link',
	    'title' => __('External Link', 'themify'),
	    'description' => __('Link Featured Image and Post Title to external URL', 'themify'),
	    'type' => 'textbox',
	    'meta' => array()
	));
	// Lightbox Link
	self::$lightbox_link = apply_filters('themify_builder_metabox_lightbox_link', array(
	    'name' => 'lightbox_link',
	    'title' => __('Lightbox Link', 'themify'),
	    'description' => __('Link Featured Image to lightbox image, video or external iframe', 'themify'),
	    'type' => 'textbox',
	    'meta' => array()
	));
    }

    /**
     * Get module name by slug
     * @param string $slug
     * @return string
     */
    public static function get_module_name($slug) {
	return isset(self::$modules[$slug]) ? self::$modules[$slug]->name : $slug;
    }

    /**
     * Set Pre-built Layout version
     */
    public static function set_current_layouts_version($version) {
	return set_transient(self::$layouts_version_name, $version);
    }

    /**
     * Get current Pre-built Layout version
     */
    public static function get_current_layouts_version() {
	$current_layouts_version = get_transient(self::$layouts_version_name);
	if (false === $current_layouts_version) {
	    self::set_current_layouts_version('0');
	    $current_layouts_version = '0';
	}
	return $current_layouts_version;
    }

    /**
     * Check whether layout is pre-built layout or custom
     */
    public static function is_prebuilt_layout($id) {
	$protected = get_post_meta($id, '_themify_builder_prebuilt_layout', true);
	return isset($protected) && 'yes' === $protected;
    }

    /**
     * Return frame layout
     */
    public static function get_frame_layout() {
	$path = THEMIFY_BUILDER_URI . '/img/row-frame/';
	return array(
	    array('value' => 'none', 'label' => __('None', 'themify'), 'img' => $path . 'none.png'),
	    array('value' => 'slant1', 'label' => __('Slant 1', 'themify'), 'img' => $path . 'slant1.svg'),
	    array('value' => 'slant2', 'label' => __('Slant 2', 'themify'), 'img' => $path . 'slant2.svg'),
	    array('value' => 'arrow1', 'label' => __('Arrow 1', 'themify'), 'img' => $path . 'arrow1.svg'),
	    array('value' => 'arrow2', 'label' => __('Arrow 2', 'themify'), 'img' => $path . 'arrow2.svg'),
	    array('value' => 'arrow3', 'label' => __('Arrow 3', 'themify'), 'img' => $path . 'arrow3.svg'),
	    array('value' => 'arrow4', 'label' => __('Arrow 4', 'themify'), 'img' => $path . 'arrow4.svg'),
	    array('value' => 'arrow5', 'label' => __('Arrow 5', 'themify'), 'img' => $path . 'arrow5.svg'),
	    array('value' => 'arrow6', 'label' => __('Arrow 6', 'themify'), 'img' => $path . 'arrow6.svg'),
	    array('value' => 'cloud1', 'label' => __('Cloud 1', 'themify'), 'img' => $path . 'cloud1.svg'),
	    array('value' => 'cloud2', 'label' => __('Cloud 2', 'themify'), 'img' => $path . 'cloud2.svg'),
	    array('value' => 'curve1', 'label' => __('Curve 1', 'themify'), 'img' => $path . 'curve1.svg'),
	    array('value' => 'curve2', 'label' => __('Curve 2', 'themify'), 'img' => $path . 'curve2.svg'),
	    array('value' => 'mountain1', 'label' => __('Mountain 1', 'themify'), 'img' => $path . 'mountain1.svg'),
	    array('value' => 'mountain2', 'label' => __('Mountain 2', 'themify'), 'img' => $path . 'mountain2.svg'),
	    array('value' => 'mountain3', 'label' => __('Mountain 3', 'themify'), 'img' => $path . 'mountain3.svg'),
	    array('value' => 'wave1', 'label' => __('Wave 1', 'themify'), 'img' => $path . 'wave1.svg'),
	    array('value' => 'wave2', 'label' => __('Wave 2', 'themify'), 'img' => $path . 'wave2.svg'),
	    array('value' => 'wave3', 'label' => __('Wave 3', 'themify'), 'img' => $path . 'wave3.svg'),
	    array('value' => 'wave4', 'label' => __('Wave 4', 'themify'), 'img' => $path . 'wave4.svg'),
	    array('value' => 'ink-splash1', 'label' => __('Ink Splash 1', 'themify'), 'img' => $path . 'ink-splash1.svg'),
	    array('value' => 'ink-splash2', 'label' => __('Ink Splash 2', 'themify'), 'img' => $path . 'ink-splash2.svg'),
	    array('value' => 'zig-zag', 'label' => __('Zig Zag', 'themify'), 'img' => $path . 'zig-zag.svg'),
	    array('value' => 'grass', 'label' => __('Grass', 'themify'), 'img' => $path . 'grass.svg'),
	    array('value' => 'melting', 'label' => __('Melting', 'themify'), 'img' => $path . 'melting.svg'),
	    array('value' => 'lace', 'label' => __('Lace', 'themify'), 'img' => $path . 'lace.svg'),
	);
    }

    /**
     * Return animation presets
     */
    public static function get_preset_animation() {
	return array(
	    __('Attention Seekers', 'themify') => array(
		'bounce' => __('bounce', 'themify'),
		'flash' => __('flash', 'themify'),
		'pulse' => __('pulse', 'themify'),
		'rubberBand' => __('rubberBand', 'themify'),
		'shake' => __('shake', 'themify'),
		'swing' => __('swing', 'themify'),
		'tada' => __('tada', 'themify'),
		'wobble' => __('wobble', 'themify'),
		'jello' => __('jello', 'themify')
	    ),
	    __('Bouncing Entrances', 'themify') => array(
		'bounceIn' => __('bounceIn', 'themify'),
		'bounceInDown' => __('bounceInDown', 'themify'),
		'bounceInLeft' => __('bounceInLeft', 'themify'),
		'bounceInRight' => __('bounceInRight', 'themify'),
		'bounceInUp' => __('bounceInUp', 'themify')
	    ),
	    __('Bouncing Exits', 'themify') => array(
		'bounceOut' => __('bounceOut', 'themify'),
		'bounceOutDown' => __('bounceOutDown', 'themify'),
		'bounceOutLeft' => __('bounceOutLeft', 'themify'),
		'bounceOutRight' => __('bounceOutRight', 'themify'),
		'bounceOutUp' => __('bounceOutUp', 'themify')
	    ),
	    __('Fading Entrances', 'themify') => array(
		'fadeIn' => __('fadeIn', 'themify'),
		'fadeInDown' => __('fadeInDown', 'themify'),
		'fadeInDownBig' => __('fadeInDownBig', 'themify'),
		'fadeInLeft' => __('fadeInLeft', 'themify'),
		'fadeInLeftBig' => __('fadeInLeftBig', 'themify'),
		'fadeInRight' => __('fadeInRight', 'themify'),
		'fadeInRightBig' => __('fadeInRightBig', 'themify'),
		'fadeInUp' => __('fadeInUp', 'themify'),
		'fadeInUpBig' => __('fadeInUpBig', 'themify')
	    ),
	    __('Fading Exits', 'themify') => array(
		'fadeOut' => __('fadeOut', 'themify'),
		'fadeOutDown' => __('fadeOutDown', 'themify'),
		'fadeOutDownBig' => __('fadeOutDownBig', 'themify'),
		'fadeOutLeft' => __('fadeOutLeft', 'themify'),
		'fadeOutLeftBig' => __('fadeOutLeftBig', 'themify'),
		'fadeOutRight' => __('fadeOutRight', 'themify'),
		'fadeOutRightBig' => __('fadeOutRightBig', 'themify'),
		'fadeOutUp' => __('fadeOutUp', 'themify'),
		'fadeOutUpBig' => __('fadeOutUpBig', 'themify')
	    ),
	    __('Flippers', 'themify') => array(
		'flip' => __('flip', 'themify'),
		'flipInX' => __('flipInX', 'themify'),
		'flipInY' => __('flipInY', 'themify'),
		'flipOutX' => __('flipOutX', 'themify'),
		'flipOutY' => __('flipOutY', 'themify')
	    ),
	    __('Lightspeed', 'themify') => array(
		'lightSpeedIn' => __('lightSpeedIn', 'themify'),
		'lightSpeedOut' => __('lightSpeedOut', 'themify')
	    ),
	    __('Rotating Entrances', 'themify') => array(
		'rotateIn' => __('rotateIn', 'themify'),
		'rotateInDownLeft' => __('rotateInDownLeft', 'themify'),
		'rotateInDownRight' => __('rotateInDownRight', 'themify'),
		'rotateInUpLeft' => __('rotateInUpLeft', 'themify'),
		'rotateInUpRight' => __('rotateInUpRight', 'themify')
	    ),
	    __('Rotating Exits', 'themify') => array(
		'rotateOut' => __('rotateOut', 'themify'),
		'rotateOutDownLeft' => __('rotateOutDownLeft', 'themify'),
		'rotateOutDownRight' => __('rotateOutDownRight', 'themify'),
		'rotateOutUpLeft' => __('rotateOutUpLeft', 'themify'),
		'rotateOutUpRight' => __('rotateOutUpRight', 'themify')
	    ),
	    __('Specials', 'themify') => array(
		'hinge' => __('hinge', 'themify'),
		'rollIn' => __('rollIn', 'themify'),
		'rollOut' => __('rollOut', 'themify')
	    ),
	    __('Zoom Entrances', 'themify') => array(
		'zoomIn' => __('zoomIn', 'themify'),
		'zoomInDown' => __('zoomInDown', 'themify'),
		'zoomInLeft' => __('zoomInLeft', 'themify'),
		'zoomInRight' => __('zoomInRight', 'themify'),
		'zoomInUp' => __('zoomInUp', 'themify')
	    ),
	    __('Zoom Exits', 'themify') => array(
		'zoomOut' => __('zoomOut', 'themify'),
		'zoomOutDown' => __('zoomOutDown', 'themify'),
		'zoomOutLeft' => __('zoomOutLeft', 'themify'),
		'zoomOutRight' => __('zoomOutRight', 'themify'),
		'zoomOutUp' => __('zoomOutUp', 'themify')
	    ),
	    __('Slide Entrance', 'themify') => array(
		'slideInDown' => __('slideInDown', 'themify'),
		'slideInLeft' => __('slideInLeft', 'themify'),
		'slideInRight' => __('slideInRight', 'themify'),
		'slideInUp' => __('slideInUp', 'themify')
	    ),
	    __('Slide Exit', 'themify') => array(
		'slideOutDown' => __('slideOutDown', 'themify'),
		'slideOutLeft' => __('slideOutLeft', 'themify'),
		'slideOutRight' => __('slideOutRight', 'themify'),
		'slideOutUp' => __('slideOutUp', 'themify')
	    )
	);
    }

    /**
     * Get Post Types which ready for an operation
     * @return array
     */
    public static function get_post_types() {

	// If it's not a product search, proceed: retrieve the post types.
	$types = get_post_types(array('exclude_from_search' => false));
	if (themify_is_themify_theme()) {
	    // Exclude pages /////////////////
	    $exclude_pages = themify_builder_get('setting-search_settings_exclude');
	    if (!empty($exclude_pages)) {
		unset($types['page']);
	    }
		// Exclude posts /////////////////
		$exclude_posts = themify_builder_get('setting-search_exclude_post');
		if (!empty($exclude_posts)) {
			unset($types['post']);
		}
	    // Exclude custom post types /////
	    $exclude_types = apply_filters('themify_types_excluded_in_search', get_post_types(array(
		'_builtin' => false,
		'public' => true,
		'exclude_from_search' => false
	    )));

	    foreach (array_keys($exclude_types) as $type) {
		$check = themify_builder_get('setting-search_exclude_' . $type);
		if (!empty($check)) {
		    unset($types[$type]);
		}
	    }
	}
	// Exclude Layout and Layout Part custom post types /////
	unset($types['section'], $types['tbuilder_layout'], $types['tbuilder_layout_part']);

	return $types;
    }

    /**
     * Check whether builder animation is active
     * @return boolean
     */
    public static function is_animation_active() {
	static $is = NULL;
	if ($is === null) {
	    // check if mobile exclude disabled OR disabled all transition
	    $val = themify_builder_get('setting-page_builder_animation_appearance', 'builder_animation_appearance');
	    $disable_all = $val === 'all';
	    $disable_mobile = $disable_all===true || $val === 'mobile';
	    $is = self::is_front_builder_activate() || !(  $disable_all===true || ( $disable_mobile===true && themify_is_touch() ) );
	}
	return $is;
    }

    /**
     * Check whether builder parallax is active
     * @return boolean
     */
    public static function is_parallax_active() {
	static $is = NULL;
	if ($is === null) {
	    // check if mobile exclude disabled OR disabled all transition
	    $val = themify_builder_get('setting-page_builder_animation_parallax_bg', 'builder_animation_parallax_bg');
	    $disable_all = $val === 'all';
	    $disable_mobile = $disable_all===true || $val === 'mobile';
	    $is = !($disable_all===true  || ( $disable_mobile===true && themify_is_touch() ));
	}
	return $is;
    }

    /**
     * Check whether builder scroll effect is active
     * @return boolean
     */
	public static function is_scroll_effect_active() {
		static $is = NULL;
		if ( $is === null ) {
			// check if mobile exclude disabled OR disabled all transition
			$val = themify_builder_get( 'setting-page_builder_animation_scroll_effect', 'builder_animation_scroll_effect' );
			$disable_all = $val === 'all';
			$disable_mobile = $disable_all===true || $val === 'mobile';
			$is = !($disable_all===true || ( $disable_mobile===true && themify_is_touch() ));
		}
		return $is;
	}

    /**
     * Check whether builder sticky scroll is active
     * @return boolean
     */
    public static function is_sticky_scroll_active() {
	static $is = NULL;
	if ($is === null) {
	    // check if mobile exclude disabled OR disabled all transition
	    $val = themify_builder_get('setting-page_builder_animation_sticky_scroll', 'builder_animation_sticky_scroll');
	    $disable_all = $val === 'all';
	    $disable_mobile = $disable_all===true || $val === 'mobile';
	    $is = !($disable_all===true || ( $disable_mobile===true && themify_is_touch() ));
	}
	return apply_filters( 'tb_sticky_scroll_active', $is );
    }

    /**
     * Get Grid Settings
     * @return array
     */
    public static function get_grid_settings($setting = 'grid') {

	switch ($setting) {
	    case 'grid':
		$value = array(
		    // Grid FullWidth
		    array('img' => '1_col', 'data' => array('-full')),
		    // Grid 2
		    array('img' => '2_col', 'data' => array('4-2', '4-2')),
		    // Grid 3
		    array('img' => '3_col', 'data' => array('3-1', '3-1', '3-1')),
		    // Grid 4
		    array('img' => '4_col', 'data' => array('4-1', '4-1', '4-1', '4-1')),
		    // Grid 5
		    array('img' => '5_col', 'data' => array('5-1', '5-1', '5-1', '5-1', '5-1')),
		    // Grid 6
		    array('img' => '6_col', 'data' => array('6-1', '6-1', '6-1', '6-1', '6-1', '6-1')),
		    array('img' => '1_4_3_4', 'data' => array('4-1', '4-3')),
		    array('img' => '1_4_1_4_2_4', 'data' => array('4-1', '4-1', '4-2')),
		    array('img' => '1_4_2_4_1_4', 'data' => array('4-1', '4-2', '4-1'), 'exclude' => true),
		    array('img' => '2_4_1_4_1_4', 'data' => array('4-2', '4-1', '4-1')),
		    array('img' => '3_4_1_4', 'data' => array('4-3', '4-1')),
		    array('img' => '2_3_1_3', 'data' => array('3-2', '3-1')),
		    array('img' => '1_3_2_3', 'data' => array('3-1', '3-2'))
		);
		break;
	    case 'column_dir':
		$value = array(
		    array('img' => 'column_ltr', 'value' => 'ltr', 'name' => __('Left-to-Right', 'themify')),
		    array('img' => 'column_rtl', 'value' => 'rtl', 'name' => __('Right-to-Left', 'themify'))
		);
		break;
	    case 'column_alignment':
	    case 'column_alignment_class':
		$columnAlignment = array(
		    array('img' => 'alignment_top', 'value' => 'col_align_top', 'name' => __('Align Top', 'themify')),
		    array('img' => 'alignment_middle', 'value' => 'col_align_middle', 'name' => __('Align Middle', 'themify')),
		    array('img' => 'alignment_bottom', 'value' => 'col_align_bottom', 'name' => __('Align Bottom', 'themify'))
		);
		if ('column_alignment' === $setting) {
		    $value = $columnAlignment;
		} else {
		    $value = array();
		    foreach ($columnAlignment as $ca) {
			$value[] = $ca['value'];
		    }
		    $value = implode(' ', $value);
		}
		break;
	    case 'height':
		$value = array(
		    array('img' => 'column_stretch', 'value' => '', 'name' => __('Stretch', 'themify')),
		    array('img' => 'column_auto', 'value' => 1, 'name' => __('Auto height', 'themify'))
		);
		break;
	    default :
		$gutters = array(
		    array('name' => __('Normal Gutter', 'themify'), 'value' => 'gutter-default'),
		    array('name' => __('Narrow Gutter', 'themify'), 'value' => 'gutter-narrow'),
		    array('name' => __('No Gutter', 'themify'), 'value' => 'gutter-none'),
		);
		if ($setting === 'gutter_class') {
		    $value = array();
		    foreach ($gutters as $g) {
			$value[] = $g['value'];
		    }
		    $value = implode(' ', $value);
		} else {
		    $value = $gutters;
		}
		break;
	}
	return $value;
    }

    /**
     * Returns list of colors and thumbnails
     *
     * @return array
     */
    public static function get_colors() {
	return apply_filters('themify_builder_module_color_presets', array(
	    array('img' => 'default', 'value' => 'default', 'label' => __('default', 'themify')),
	    array('img' => 'black', 'value' => 'black', 'label' => __('black', 'themify')),
	    array('img' => 'grey', 'value' => 'gray', 'label' => __('gray', 'themify')),
	    array('img' => 'blue', 'value' => 'blue', 'label' => __('blue', 'themify')),
	    array('img' => 'light-blue', 'value' => 'light-blue', 'label' => __('light-blue', 'themify')),
	    array('img' => 'green', 'value' => 'green', 'label' => __('green', 'themify')),
	    array('img' => 'light-green', 'value' => 'light-green', 'label' => __('light-green', 'themify')),
	    array('img' => 'purple', 'value' => 'purple', 'label' => __('purple', 'themify')),
	    array('img' => 'light-purple', 'value' => 'light-purple', 'label' => __('light-purple', 'themify')),
	    array('img' => 'brown', 'value' => 'brown', 'label' => __('brown', 'themify')),
	    array('img' => 'orange', 'value' => 'orange', 'label' => __('orange', 'themify')),
	    array('img' => 'yellow', 'value' => 'yellow', 'label' => __('yellow', 'themify')),
	    array('img' => 'red', 'value' => 'red', 'label' => __('red', 'themify')),
	    array('img' => 'pink', 'value' => 'pink', 'label' => __('pink', 'themify'))
	));
    }

    /**
     * Returns list of appearance
     *
     * @return array
     */
    public static function get_appearance() {
	return array(
	    array('name' => 'rounded', 'value' => __('Rounded', 'themify')),
	    array('name' => 'gradient', 'value' => __('Gradient', 'themify')),
	    array('name' => 'glossy', 'value' => __('Glossy', 'themify')),
	    array('name' => 'embossed', 'value' => __('Embossed', 'themify')),
	    array('name' => 'shadow', 'value' => __('Shadow', 'themify'))
	);
    }

    /**
     * Returns list of border styles
     *
     * @return array
     */
    public static function get_border_styles() {
	return array(
	    'solid' => __('Solid', 'themify'),
	    'dashed' => __('Dashed', 'themify'),
	    'dotted' => __('Dotted', 'themify'),
	    'double' => __('Double', 'themify'),
	    'none' => __('None', 'themify')
	);
    }
    
     /**
     * Returns list of border styles
     *
     * @return array
     */
    public static function get_border_radius_styles() {
	return array(
		array( 'id' => 'top', 'label' => __( 'Top Left', 'themify' ) ),
		array( 'id' => 'bottom', 'label' => __( 'Bottom Right', 'themify' ) ),
		array( 'id' => 'right', 'label' => __( 'Top right', 'themify' ) ),
		array( 'id' => 'left', 'label' => __( 'Bottom Left', 'themify' ) ),
	);
    }

    /**
     * Returns list of text_aligment
     *
     * @return array
     */
    public static function get_text_aligment() {
	return array(
	    array('value' => 'left', 'name' => __('Left', 'themify'), 'icon' => '<span class="ti-align-left"></span>'),
	    array('value' => 'center', 'name' => __('Center', 'themify'), 'icon' => '<span class="ti-align-center"></span>'),
	    array('value' => 'right', 'name' => __('Right', 'themify'), 'icon' => '<span class="ti-align-right"></span>'),
	    array('value' => 'justify', 'name' => __('Justify', 'themify'), 'icon' => '<span class="ti-align-justify"></span>')
	);
    }

    /**
     * Returns list of background repeat values
     *
     * @return array
     */
    public static function get_repeat() {
	return array(
	    'repeat' => __('Repeat All', 'themify'),
	    'repeat-x' => __('Repeat Horizontally', 'themify'),
	    'repeat-y' => __('Repeat Vertically', 'themify'),
	    'no-repeat' => __('Do not repeat', 'themify'),
	    'fullcover' => __('Fullcover', 'themify')
	);
    }

    /**
     * Returns list of background position values
     *
     * @return array
     */
    public static function get_position() {
	return array(
	    'left-top' => __('Left Top', 'themify'),
	    'left-center' => __('Left Center', 'themify'),
	    'left-bottom' => __('Left Bottom', 'themify'),
	    'right-top' => __('Right top', 'themify'),
	    'right-center' => __('Right Center', 'themify'),
	    'right-bottom' => __('Right Bottom', 'themify'),
	    'center-top' => __('Center Top', 'themify'),
	    'center-center' => __('Center Center', 'themify'),
	    'center-bottom' => __('Center Bottom', 'themify')
	);
    }

    /**
     * Returns list of text_decoration
     *
     * @return array
     */
    public static function get_text_decoration() {
	return array(
	    array('value' => 'underline', 'name' => __('Underline', 'themify'), 'label_class' => 'tb_text_underline', 'icon' => 'U'),
	    array('value' => 'overline', 'name' => __('Overline', 'themify'), 'label_class' => 'tb_text_overline', 'icon' => 'O'),
	    array('value' => 'line-through', 'name' => __('Line through', 'themify'), 'label_class' => 'tb_text_through', 'icon' => 'S'),
	    array('value' => 'none', 'name' => __('None', 'themify'), 'label_class' => 'tb_text_none', 'icon' => '-')
	);
    }

    /**
     * Returns list of font style option
     *
     * @return array
     */
    public static function get_font_style() {
	return array(
	    array('value' => 'italic', 'name' => __('Italic', 'themify'), 'icon' => '<span class="tb_font_italic">I</span>'),
	    array('value' => 'normal', 'name' => __('Normal', 'themify'), 'icon' => 'N')
	);
    }

    /**
     * Returns list of font weight option
     *
     * @return array
     */
    public static function get_font_weight() {
	return array(
	    array('value' => 'bold', 'name' => __('Bold', 'themify'), 'icon' => '<span class="tb_font_bold">B</span>'),
	);
    }

    /**
     * Returns list of text transform options
     *
     * @return array
     */
    public static function get_text_transform() {
	return array(
	    array('value' => 'uppercase', 'name' => __('Uppercase', 'themify'), 'icon' => 'AB'),
	    array('value' => 'lowercase', 'name' => __('Lowercase', 'themify'), 'icon' => 'ab'),
	    array('value' => 'capitalize', 'name' => __('Capitalize', 'themify'), 'icon' => 'Ab'),
	    array('value' => 'none', 'name' => __('None', 'themify'), 'icon' => '–')
	);
    }

    /**
     * Returns list of blend mode options
     *
     * @return array
     */
    public static function get_blend_mode() {
	return array(
	    'normal' => __('Normal', 'themify'),
	    'multiply' => __('Multiply', 'themify'),
	    'screen' => __('Screen', 'themify'),
	    'overlay' => __('Overlay', 'themify'),
	    'darken' => __('Darken', 'themify'),
	    'lighten' => __('Lighten', 'themify'),
	    'color-dodge' => __('Color Dodge', 'themify'),
	    'color-burn' => __('Color Burn', 'themify'),
	    'difference' => __('Difference', 'themify'),
	    'exclusion' => __('Exclusion', 'themify'),
	    'hue' => __('Hue', 'themify'),
	    'saturation' => __('Saturation', 'themify'),
	    'color' => __('Color', 'themify'),
	    'luminosity' => __('Luminosity', 'themify')
	);
    }

    /**
     * Check whether image script is use or not
     *
     * @since 2.4.2 Check if it's a Themify theme or not. If it's not, it's Builder standalone plugin.
     *
     * @return boolean
     */
    public static function is_img_php_disabled() {
	static $is = NULL;
	if ($is === null) {
	    $is = themify_builder_get('setting-img_settings_use', 'image_setting-img_settings_use') ? true : false;
	}
	return $is;
    }

    public static function is_fullwidth_layout_supported() {
	static $is = null;
	if ($is === null) {
	    $is = apply_filters('themify_builder_fullwidth_layout_support', false) ? true : false;
	}
	return $is;
    }

    /**
     * Get alt text defined in WP Media attachment by a given URL
     *
     * @since 2.2.5
     *
     * @param string $image_url
     *
     * @return string
     */
    public static function get_alt_by_url($image_url) {
	$upload_dir = wp_upload_dir();
	$attachment_id = themify_get_attachment_id_from_url($image_url, $upload_dir['baseurl']);
	if ($attachment_id) {
	    if ($alt = get_post_meta($attachment_id, '_wp_attachment_image_alt', true)) {
		return $alt;
	    }
	}
	return '';
    }

    /**
     * Get all modules settings for used in localize script.
     *
     * @access public
     * @return array
     */
    public static function get_modules_localize_settings() {
	$return = array();

	foreach (self::$modules as $module) {
	    $default = $module->get_default_settings();
	    $return[$module->slug]['name'] = $module->name;
	    $return[$module->slug]['category'] = $module->category;
	    if ($module->favorite) {
		$return[$module->slug]['favorite'] = 1;
	    }
	    if ($default) {
		$return[$module->slug]['defaults'] = $default;
	    }
	    $type = $module->get_visual_type();
	    if ($type) {
		$return[$module->slug]['type'] = $type;
	    }
	}
	uasort($return, array(__CLASS__, 'sortBy'));
	return $return;
    }

    private static function sortBy($a, $b) {
	return strnatcasecmp($a['name'], $b['name']);
    }

    public static function remove_empty_module_categories($categories) {
	$current_categories = array();
	foreach (self::$modules as $module) {
	    foreach($module->category as $c){
		if(!isset($current_categories[$c])){
		    $current_categories[$c]=true;
		}
	    }
	}
	foreach ($categories as $key => $category) {
	    if(!isset($current_categories[$key])){
		unset($categories[$key]);
	    }
	}
	return $categories;
    }

    public static function hasAccess() {
	static $is = null;

	if ($is === null) {
	    $is = Themify_Access_Role::check_access_backend();
	}

	return $is;
    }

    public static function localize_js( $object_name, $l10n ) {
		foreach ( (array) $l10n as $key => $value ) {
			if ( is_scalar( $value ) ) {
				$l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
			}
		}
		$l10n = apply_filters( "tb_localize_js_{$object_name}", $l10n );

		return $l10n ? "var $object_name = " . wp_json_encode( $l10n ) . ';' : '';
    }

    public static function format_text($content) {
		global $wp_embed,$ThemifyBuilder;
		
		$isLoop=$ThemifyBuilder->in_the_loop===true;
		$ThemifyBuilder->in_the_loop = true;
		$content = wptexturize($content);

		$pattern = '|<p>\s*(https?://[^\s"]+)\s*</p>|im'; // pattern to check embed url
		$to = '<p>' . PHP_EOL . '$1' . PHP_EOL . '</p>'; // add line break
		$content = $wp_embed->run_shortcode($content);
		$content = do_shortcode(shortcode_unautop($content));
		$content = preg_replace($pattern, $to, $content);
		$content = $wp_embed->autoembed($content);
		$ThemifyBuilder->in_the_loop = $isLoop;
		$content = convert_smilies( $content );
		$content = self::generate_read_more( $content );

		return $content;
    }

	/*
     * Generate read more link for text module
     *
     * @param string $content
     * @return string generated load more link in the text.
	 */
    public static function generate_read_more($content) {
		if ( preg_match('/(<|&lt;)!--more(.*?)?--(>|&gt;)/', $content, $matches ) ) {
			$text = trim( $matches[2] );
			if ( ! empty( $text ) ) {
				$read_more_text = $text;
			} else {
				$read_more_text = apply_filters( 'themify_builder_more_text', __( 'More ', 'themify' ) );
			}
			$content = str_replace( $matches[0], '<div class="more-text" style="display: none">', $content );
			$content .= '</div><a href="#" class="tb-text-more-link module-text-more tb-more-tag">' . $read_more_text . '</a>';
		}
		return $content;
    }

    /**
     * backward combality 13.10.19 v4.6.9
     *
     * @return bool
     */
    public static function is_themify_theme() {
	return themify_is_themify_theme();
    }

    /**
     * Get module php files data
     * @param string $select
     * @return array
     */
    public static function get_modules($select = 'all') {
	$modules = array();
	$directories = self::get_directory_path('modules');
	if ('active' === $select) {
	    if (themify_is_themify_theme()) {
		$data = themify_get_data();
		$pre = 'setting-page_builder_exc_';
	    } else {
		$pre = 'builder_exclude_module_';
		$data = self::get_builder_settings();
	    }
	}
	foreach ($directories as $dir) {
	    if (file_exists($dir)) {
		$d = dir($dir);
		while (( false !== ( $entry = $d->read() ))) {
		    if ($entry !== '.' && $entry !== '..' && $entry !== '.svn') {
			$path = $d->path . $entry;
			if (!is_dir($path)) { /* clean-up, make sure no directories is included in the list */
			    $path_info = pathinfo($path);
			    if (strpos($path_info['filename'], 'module-') === 0) {
				$id = str_replace('module-', '', $path_info['filename']);
				if ($select === 'active' && !empty($data[$pre . $id])) {
				    continue;
				}
				$modules[$id] = array(
				    'dirname' => $path_info['dirname'],
				    'basename' => $path_info['basename']
				);
				if($select==='all'){
				    $modules[$id]['name']=current(get_file_data($path, array('Module Name')));
				}
			    }
			}
		    }
		}
	    }
	}
	return $modules;
    }

    /**
     * Check whether theme loop template exist
     * @param string $template_name
     * @param string $template_path
     * @return boolean
     */
    public static function is_loop_template_exist($template_name, $template_path) {
	return !locate_template(array(trailingslashit($template_path) . $template_name)) ? false : true;
    }

    /**
     * Register default directories used to load modules and their templates
     */
    public static function setup_default_directories() {
	$theme_dir = get_template_directory();
	self::register_directory('templates', THEMIFY_BUILDER_TEMPLATES_DIR, 1);
	self::register_directory('templates', $theme_dir . '/themify-builder/', 15);
	if (is_child_theme()) {
	    self::register_directory('templates', get_stylesheet_directory() . '/themify-builder/', 20);
	}
	self::register_directory('modules', THEMIFY_BUILDER_MODULES_DIR, 1);
	self::register_directory('modules', $theme_dir . '/themify-builder-modules/', 15);
    }

    public static function register_directory($context, $path, $priority = 10) {
	self::$directory_registry[$context][$priority][] = trailingslashit($path);
    }

    public static function get_directory_path($context) {
	static $dir = array();
	if (!isset($dir[$context])) {
	    krsort(self::$directory_registry[$context]);
	    $dir[$context] = call_user_func_array('array_merge', self::$directory_registry[$context]);
	    unset(self::$directory_registry[$context]);
	}
	return $dir[$context];
    }

    public static function remove_cache($post_id, $tag = false, array $args = array()) {
	TFCache::remove_cache($tag, $post_id, $args);
    }

    public static function is_cpt_active($post_type) {
	return apply_filters("builder_is_{$post_type}_active", in_array($post_type, self::$builder_cpt, true));
    }

    public static function builder_cpt_check() {

	foreach (array('slider', 'highlight', 'testimonial') as $post_type) {
	    if (post_type_exists($post_type)) {
		self::$builder_cpt[] = $post_type;
	    } else {
		$posts = get_posts(array(
		    'post_type' => $post_type,
		    'posts_per_page' => 1,
		    'post_status' => 'any',
		    'no_found_rows' => true,
		    'ignore_sticky_posts'=>true,
		    'update_post_term_cache' => false,
		    'update_post_meta_cache' => false,
		    'cache_results' => false,
		    'orderby' => 'none'
		));
		if (!empty($posts)) {
		    self::$builder_cpt[] = $post_type;
		}
	    }
	}
    }

    /**
     * Get a list of post types that can be accessed publicly
     *
     * does not include attachments, Builder layouts and layout parts,
     * and also custom post types in Builder that have their own module.
     *
     * @return array of key => label pairs
     */
    public static function get_public_post_types($exclude = true) {

	$post_types = get_post_types(array('public' => true, 'publicly_queryable' => 'true'), 'objects');
	$excluded_types = array('attachment', 'tbuilder_layout', 'tbuilder_layout_part', 'section', 'tglobal_style','tb_cf');
	if ($exclude) {
	    $excluded_types = array_merge(self::$builder_cpt, $excluded_types);
	}
	foreach ($post_types as $key => $value) {
	    if (!in_array($key, $excluded_types, true)) {
		$result[$key] = $value->labels->singular_name;
	    }
	}
	return apply_filters('builder_get_public_post_types', $result);
    }

    /**
     * Get a list of taxonomies that can be accessed publicly
     *
     * does not include post formats, section categories (used by some themes),
     * and also custom post types in Builder that have their own module.
     *
     * @return array of key => label pairs
     */
    public static function get_public_taxonomies($exclude = true) {
	$taxonomies = get_taxonomies(array('public' => true), 'objects');
	$excludes = array('post_format', 'section-category');
	if ($exclude) { // exclude taxonomies from Builder CPTs
	    foreach (self::$builder_cpt as $value) {
		$excludes[] = "{$value}-category";
	    }
	}
	foreach ($taxonomies as $key => $value) {
	    if (!in_array($key, $excludes, true)) {
		$result[$key] = $value->labels->name;
	    }
	}

	return apply_filters('builder_get_public_taxonomies', $result);
    }

    /**
     * Get images from gallery shortcode
     * @return object
     */
    public static function get_images_from_gallery_shortcode($shortcode) {
	preg_match('/\[gallery.*ids=.(.*).\]/', $shortcode, $ids);
	if (isset($ids[1])) {
	    $ids = trim($ids[1], '\\');
	    $ids = trim($ids, '"');
	    $image_ids = explode(',', $ids);
	    $orderby = self::get_gallery_param_option($shortcode, 'orderby');
	    $orderby = $orderby != '' ? $orderby : 'post__in';
	    $order = self::get_gallery_param_option($shortcode, 'order');
	    $order = $order != '' ? $order : 'ASC';

	    // Check if post has more than one image in gallery
	    return get_posts(array(
		'post__in' => $image_ids,
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'numberposts' => -1,
		'no_found_rows' => true,
		'cache_results' => false,
		'update_post_term_cache' => false,
		'update_post_meta_cache' => false,
		'orderby' => $orderby,
		'order' => $order
	    ));
	}
	return array();
    }

    /**
     * Get gallery shortcode options
     * @param $shortcode
     * @param $param
     */
    public static function get_gallery_param_option($shortcode, $param = 'link') {
	$pattern = '/\[gallery .*?(?=' . $param . ')' . $param . '=.([^\']+)./si';
	preg_match($pattern, $shortcode, $out);

	$out = isset($out[1]) ? explode('"', $out[1]) : array('');
	return $out[0];
    }

    public static function parse_slug_to_ids($slug_string, $post_type = 'post') {
	$slug_arr = explode(',', $slug_string);
	$return = array();
	if (!empty($slug_arr)) {
	    foreach ($slug_arr as $slug) {
		$return[] = self::get_id_by_slug(trim($slug), $post_type);
	    }
	}
	return $return;
    }

    public static function get_id_by_slug($slug, $post_type = 'post') {
	$args = array(
	    'name' => $slug,
	    'post_type' => $post_type,
	    'post_status' => 'publish',
	    'numberposts' => 1,
	    'no_found_rows' => true,
	    'cache_results' => false,
	    'ignore_sticky_posts'=>true,
	    'update_post_term_cache' => false,
	    'update_post_meta_cache' => false,
	    'orderby' => 'none'
	);
	$my_posts = get_posts($args);
	return $my_posts ? $my_posts[0]->ID : null;
    }

    public static function getMapKey() {
	return themify_builder_get('setting-google_map_key', 'builder_settings_google_map_key');
    }

    /**
     * Get initialization parameters for plupload. Filtered through themify_builder_plupload_init_vars.
     * @return mixed|void
     * @since 1.4.2
     */
    public static function get_builder_plupload_init() {
	return apply_filters('themify_builder_plupload_init_vars', array(
	    'runtimes' => 'html5',
	    'browse_button' => 'tb_plupload_browse_button', // adjusted by uploader
	    'container' => 'tb_plupload_upload_ui', // adjusted by uploader
	    'drop_element' => 'tb_plupload_upload_ui', // adjusted by uploader
	    'file_data_name' => 'async-upload', // adjusted by uploader
	    'multiple_queues' => true,
	    'max_file_size' => wp_max_upload_size() . 'b',
	    'url' => admin_url('admin-ajax.php'),
	    'filters' => array(array(
		    'title' => __('Allowed Files', 'themify'),
		    'extensions' => 'jpg,jpeg,gif,png,zip,txt'
		)),
	    'multipart' => true,
	    'urlstream_upload' => true,
	    'multi_selection' => false, // added by uploader
	    // additional post data to send to our ajax hook
	    'multipart_params' => array(
		'_ajax_nonce' => '', // added by uploader
		'action' => 'themify_builder_plupload_action', // the ajax action name
		'imgid' => 0 // added by uploader
	    )
	));
    }

    /**
     * Get Builder Settings
     */
    public static function get_builder_settings() {
	static $data = null;
	if ($data === null) {
	    $data = get_option('themify_builder_setting');
	    if (is_array($data) && !empty($data)) {
		foreach ($data as $name => $value) {
		    $data[$name] = stripslashes($value);
		}
	    } else {
		$data = array();
	    }
	}
	return $data;
    }

    /**
     * Get ID
     */
    public static function get_ID() {
	if (themify_is_woocommerce_active() && is_shop()) {
	    $page_id = version_compare(WOOCOMMERCE_VERSION, '3.0.0', '>=') ? wc_get_page_id('shop') : woocommerce_get_page_id('shop');
	} else {
	    $page_id = get_the_id();
	}

	return $page_id;
    }

    /**
     * Get Grid menu list
     */
    public static function grid($handle = 'row') {
	$grid_lists = self::get_grid_settings();
	$gutters = self::get_grid_settings('gutter');
	$column_alignment = self::get_grid_settings('column_alignment');
	$column_direction = self::get_grid_settings('column_dir');
	$auto_height = self::get_grid_settings('height');
	$breakpoints = array('desktop' => '') + themify_get_breakpoints();
	?>
	<div class="tb_grid_menu">
	    <ul class="grid_tabs">
		<?php foreach ($breakpoints as $b => $v): ?>
	    	<li title="<?php echo $b === 'tablet_landscape' ? __('Tablet Landscape', 'themify') : ucfirst($b); ?>"
	    	    data-id="<?php echo $b ?>"
	    	    class="tab_<?php echo $b ?><?php if ($b === 'tablet_landscape'): ?> ti-tablet<?php endif; ?> ti-<?php echo $b ?>"></li>
		    <?php endforeach; ?>
	    </ul>
	    <div class="tb_grid_tab tb_grid_desktop">
		<ul class="tb_grid_list clearfix">
		    <?php foreach ($grid_lists as &$li): ?>
			<?php $li['col'] = count($li['data']); ?>
	    	    <li class="grid-layout-<?php echo esc_attr(implode('-', $li['data'])); ?>"
	    		data-col="<?php echo $li['col']; ?>"
	    		data-grid="<?php echo esc_attr(json_encode($li['data'])); ?>"><span
	    		    class="tb_grids tb_<?php echo $li['img'] ?>"></span></li>
			<?php endforeach; ?>
		</ul>
		<div class="tb_component_wrap_left">
		    <ul class="tb_column_alignment tb_actions">
			<?php foreach ($column_alignment as $v): ?>
	    		<li<?php if ($v['value'] === 'col_align_top') echo ' class="selected"' ?>
	    		    data-alignment="<?php echo $v['value'] ?>">
	    		    <span class="tb_<?php echo $v['img'] ?>"></span>
	    		    <div class="themify_tooltip"><?php echo $v['name']; ?></div>
	    		</li>
			<?php endforeach; ?>
		    </ul>
		    <ul class="tb_column_direction tb_actions">
			<?php foreach ($column_direction as $v): ?>
	    		<li<?php if ($v['value'] === 'ltr') echo ' class="selected"' ?>
	    		    data-dir="<?php echo $v['value']; ?>">
	    		    <span class="tb_<?php echo $v['img'] ?>"></span>
	    		    <div class="themify_tooltip"><?php echo $v['name']; ?></div>
	    		</li>
			<?php endforeach; ?>
		    </ul>
		</div>
		<div class="tb_component_wrap_right">
		    <ul class="tb_column_gutter tb_actions">
			<?php foreach ($gutters as $v): ?>
	    		<li<?php if ($v['value'] === 'gutter-default') echo ' class="selected"' ?>
	    		    data-value="<?php echo $v['value']; ?>">
	    		    <span class="tb_<?php echo $v['value'] ?>"></span>
	    		    <div class="themify_tooltip"><?php echo $v['name']; ?></div>
	    		</li>
			<?php endforeach; ?>
		    </ul>
		    <ul class="tb_column_height tb_actions">
			<?php foreach ($auto_height as $v): ?>
	    		<li<?php if ($v['value'] === '') echo ' class="selected"' ?>
	    		    data-value="<?php echo $v['value']; ?>">
	    		    <span class="tb_<?php echo $v['img'] ?>"></span>
	    		    <div class="themify_tooltip"><?php echo $v['name']; ?></div>
	    		</li>
			<?php endforeach; ?>
		    </ul>
		</div>
	    </div>
	    <div class="tb_grid_tab tb_grid_reposnive clearfix tb_grid_tablet_landscape">
		<ul class="tb_grid_list" data-type="tablet_landscape">
		    <li class="tb1 tablet_landscape-auto selected" data-grid='["-auto"]'><span
			    class="tb_grids tb_auto"></span></li>
			<?php foreach ($grid_lists as $k => &$li): ?>
			    <?php
			    if (!isset($li['exclude'])) {
				$li['data'] = array_values(array_unique($li['data']));
			    }
			    ?>
	    	    <li class="tb<?php echo $li['col']; ?> grid-layout-<?php echo esc_attr(implode('-', $li['data'])); ?>"
	    		data-col="<?php echo $li['col']; ?>"
	    		data-grid="<?php echo esc_attr(json_encode($li['data'])); ?>"><span
	    		    class="tb_grids tb_<?php echo $li['img'] ?>"></span></li>
			    <?php
			endforeach;
			unset($li);
			?>
		</ul>
		<ul class="tb_column_direction clearfix tb_actions">
		    <?php foreach ($column_direction as $v): ?>
	    	    <li<?php if ($v['value'] === 'ltr') echo ' class="selected"' ?>
	    		data-dir="<?php echo $v['value']; ?>">
	    		<span class="tb_<?php echo $v['img'] ?>"></span>
	    		<div class="themify_tooltip"><?php echo $v['name']; ?></div>
	    	    </li>
		    <?php endforeach; ?>
		</ul>
	    </div>
	    <div class="tb_grid_tab tb_grid_reposnive tb_grid_tablet">
		<ul class="tb_grid_list" data-type="tablet">
		    <li class="tb1 tablet-auto selected" data-grid='["-auto"]'><span class="tb_grids tb_auto"></span>
		    </li>
		    <?php foreach ($grid_lists as $k => $li1): ?>
	    	    <li class="tb<?php echo $li1['col']; ?> grid-layout-<?php echo esc_attr(implode('-', $li1['data'])); ?>"
	    		data-col="<?php echo $li1['col']; ?>"
	    		data-grid="<?php echo esc_attr(json_encode($li1['data'])); ?>"><span
	    		    class="tb_grids tb_<?php echo $li1['img'] ?>"></span></li>
			<?php endforeach; ?>
		</ul>
		<ul class="tb_column_direction clearfix tb_actions">
		    <?php foreach ($column_direction as $v): ?>
	    	    <li<?php if ($v['value'] === 'ltr') echo ' class="selected"' ?>
	    		data-dir="<?php echo $v['value']; ?>">
	    		<span class="tb_<?php echo $v['img'] ?>"></span>
	    		<div class="themify_tooltip"><?php echo $v['name']; ?></div>
	    	    </li>
		    <?php endforeach; ?>
		</ul>
	    </div>
	    <div class="tb_grid_tab tb_grid_reposnive tb_grid_mobile">
		<ul class="tb_grid_list" data-type="mobile">
		    <li class="tb1 mobile-auto selected" data-grid='["-auto"]'><span class="tb_grids tb_auto"></span>
		    </li>
		    <?php foreach ($grid_lists as $li1): ?>
	    	    <li class="tb<?php echo $li1['col']; ?> grid-layout-<?php echo esc_attr(implode('-', $li1['data'])); ?>"
	    		data-col="<?php echo $li1['col']; ?>"
	    		data-grid="<?php echo esc_attr(json_encode($li1['data'])); ?>"><span
	    		    class="tb_grids tb_<?php echo $li1['img'] ?>"></span></li>
			<?php endforeach; ?>
		</ul>
		<ul class="tb_column_direction clearfix tb_actions">
		    <?php foreach ($column_direction as $v): ?>
	    	    <li<?php if ($v['value'] === 'ltr') echo ' class="selected"' ?>
	    		data-dir="<?php echo $v['value']; ?>">
	    		<span class="tb_<?php echo $v['img'] ?>"></span>
	    		<div class="themify_tooltip"><?php echo $v['name']; ?></div>
	    	    </li>
		    <?php endforeach; ?>
		</ul>
	    </div>
	</div>
	<?php
    }

    public static function get_transient_time() {
	return apply_filters('themify_builder_ticks', MINUTE_IN_SECONDS / 2);
    }

    public static function set_edit_transient($post_id, $value) {

	return set_transient(self::$transient_name . $post_id, $value, self::get_transient_time());
    }

    public static function get_edit_transient($post_id) {
	return get_transient(self::$transient_name . $post_id);
    }

    public static function remove_edit_transient($post_id) {
	return delete_transient(self::$transient_name . $post_id);
    }

    public static function get_icon($icon) {
	if (strpos($icon, 'fa-') === 0) {
	    $icon = 'fa ' . $icon;
	}
	return $icon;
    }

    /**
     * Check if gutenberg active
     * @return boolean
     */
    public static function is_gutenberg_active() {
	static $is = null;
	if ($is === null) {
	    global $wp_version;
	    $is = self::is_plugin_active('gutenberg/gutenberg.php') || version_compare($wp_version, '5.0', '>=');
	    if ($is === true && version_compare($wp_version, '5.0', '>=')) {
		$is = !self::is_plugin_active('disable-gutenberg/disable-gutenberg.php') && !self::is_plugin_active('classic-editor/classic-editor.php');
	    }
	}
	return $is;
    }

    public static function isWpEditorDisable() {
	static $is = null;
	if ($is === null) {
	    $is = themify_builder_get('setting-page_builder_disable_wp_editor', 'builder_disable_wp_editor');
	}
	return $is;
    }

    /**
     * Plugin Active checking
     *
     * @access public
     * @param string $plugin
     * @return bool
     */
    public static function is_plugin_active($plugin) {
	static $plugins = null;
	static $active_plugins = array();
	if ($plugins === null) {
	    $plugins = is_multisite() ? get_site_option('active_sitewide_plugins') : false;
	    $active_plugins = (array) apply_filters('active_plugins', get_option('active_plugins'));
	}
	return ( $plugins !== false && isset($plugins[$plugin]) ) || in_array($plugin, $active_plugins, true);
    }

    /**
     * Check if we are gutenberg editor
     * @return boolean
     */
    public static function is_gutenberg_editor() {
	static $is = null;
	if ($is === null) {
	    /*
	     * There have been reports of specialized loading scenarios where `get_current_screen`
	     * does not exist. In these cases, it is safe to say we are not loading Gutenberg.
	     */
	    global $post;
	    $is = !(!is_admin() || isset($_GET['classic-editor']) || !function_exists('get_current_screen') || get_current_screen()->base !== 'post' || !self::is_gutenberg_active() || !self::gutenberg_can_edit_post($post) );
	}
	return $is;
    }

    private static function gutenberg_can_edit_post($post) {
	$post = get_post($post);
	$can_edit = !(!$post || 'trash' === $post->post_status || !self::gutenberg_can_edit_post_type($post->post_type) || !current_user_can('edit_post', $post->ID) || ( absint(get_option('page_for_posts')) === $post->ID && empty($post->post_content) ) );

	/**
	 * Filter to allow plugins to enable/disable Gutenberg for particular post.
	 *
	 * @since 3.5
	 *
	 * @param bool $can_edit Whether the post can be edited or not.
	 * @param WP_Post $post The post being checked.
	 */
	return apply_filters('gutenberg_can_edit_post', $can_edit, $post);
    }

    private static function gutenberg_can_edit_post_type($post_type) {
	$can_edit = !(!post_type_exists($post_type) || !post_type_supports($post_type, 'editor') );
	if ($can_edit === true) {
	    $post_type_object = get_post_type_object($post_type);
	    $can_edit = !( $post_type_object && !$post_type_object->show_in_rest );
	}
	/**
	 * Filter to allow plugins to enable/disable Gutenberg for particular post types.
	 *
	 * @since 1.5.2
	 *
	 * @param bool $can_edit Whether the post type can be edited or not.
	 * @param string $post_type The post type being checked.
	 */
	return apply_filters('gutenberg_can_edit_post_type', $can_edit, $post_type);
    }

    /**
     * Get frame type
     * @return string|boolean
     */
    public static function get_frame($settings, $side) {
	if (isset($settings["{$side}-frame_type"]) && $settings["{$side}-frame_type"] === $side . '-presets' && !empty($settings["{$side}-frame_layout"])) {
	    return $settings["{$side}-frame_layout"]!=='none'?'presets':false;
	} elseif (isset($settings["{$side}-frame_type"]) && $settings["{$side}-frame_type"] === $side . '-custom' && !empty($settings["{$side}-frame_custom"])) {
	    return 'custom';
	} else {
	    return false;
	}
    }

    public static function get_animation() {
	return apply_filters('themify_builder_animation_settings_fields', array(
	    //Animation
	    array(
		'type' => 'separator',
		'label' => __('Animation', 'themify')
	    ),
	    array(
		'type' => 'multi',
		'label' => __('Entrance Animation', 'themify'),
		'options' => array(
		    array(
			'id' => 'animation_effect',
			'type' => 'animation_select'
		    ),
		    array(
			'id' => 'animation_effect_delay',
			'type' => 'number',
			'after' => __('Delay', 'themify'),
			'step'=>0.1
		    ),
		    array(
			'id' => 'animation_effect_repeat',
			'type' => 'number',
			'after' => __('Repeat', 'themify')
		    )
		)
	    ),
	    array(
		'type' => 'animation_select',
		'label' => __('Hover Animation', 'themify'),
		'id' => 'hover_animation_effect'
	    ),
	    //Float Scrolling
	    array(
		'type' => 'separator',
		'label' => __('Effects', 'themify')
	    ),
	    array(
		'type' => 'tabs',
		'isRadio'=>true,
		'id' => 'animation_effect_tab',
		'options' => array(
			's_e_m' => array(
				'options' => array(
					array(
						'id'      => 'motion_effects',
						'type'    => 'accordion',
						'options' => array(
							'v'     => array(
								'label'   => __( 'Vertical Scroll', 'themify' ),
								'options' => array(
									array(
										'id'      => 'v_dir',
										'type'    => 'select',
										'label'   => __( 'Direction', 'themify' ),
										'options' => array(
											'' => '',
											'up'   => __( 'Up', 'themify' ),
											'down' => __( 'Down', 'themify' )
										)
									),
									array(
										'id'    => 'v_speed',
										'type'  => 'slider_range',
										'label' => __( 'Speed', 'themify' ),
										'options' => array(
											'min'  => '1',
											'max'  => '10',
											'unit' => '',
                                            'range' => false,
                                            'default' => '5'
										)
									),
									array(
										'id'      => 'v_vp',
										'type'    => 'slider_range',
										'label'   => __( 'Viewport', 'themify' )
									),
								)
							),
							'h'   => array(
								'label'   => __( 'Horizontal Scroll', 'themify' ),
								'options' => array(
									array(
										'id'      => 'h_dir',
										'type'    => 'select',
										'label'   => __( 'Direction', 'themify' ),
										'options' => array(
											'' => '',
											'toleft'  => __( 'To Left', 'themify' ),
											'toright' => __( 'To Right', 'themify' )
										)
									),
									array(
										'id'    => 'h_speed',
										'type'  => 'slider_range',
										'label' => __( 'Speed', 'themify' ),
			'options' => array(
											'min'  => '1',
											'max'  => '10',
											'unit' => '',
											'range' => false,
                                            'default' => '5'
										)
									),
									array(
										'id'      => 'h_vp',
										'type'    => 'slider_range',
										'label'   => __( 'Viewport', 'themify' )
									),
								)
							),
							't' => array(
								'label'   => __( 'Transparency', 'themify' ),
								'options' => array(
									array(
										'id'      => 't_dir',
										'type'    => 'select',
										'label'   => __( 'Direction', 'themify' ),
										'options' => array(
											'' => '',
											'fadein'    => __( 'Fade In', 'themify' ),
											'fadeout'   => __( 'Fade Out', 'themify' ),
											'fadeoutin' => __( 'Fade Out In', 'themify' ),
											'fadeinout' => __( 'Fade In Out', 'themify' )
										)
									),
									array(
										'id'      => 't_vp',
										'type'    => 'slider_range',
										'label'   => __( 'Viewport', 'themify' )
									),
								)
							),
							'b'         => array(
								'label'   => __( 'Blur', 'themify' ),
								'options' => array(
									array(
										'id'      => 'b_dir',
										'type'    => 'select',
										'label'   => __( 'Direction', 'themify' ),
										'options' => array(
											'' => '',
											'fadein'  => __( 'Fade In', 'themify' ),
											'fadeout' => __( 'Fade Out', 'themify' )
										)
									),
									array(
										'id'    => 'b_level',
										'type'  => 'slider_range',
										'label' => __( 'Level', 'themify' ),
										'options' => array(
											'min'  => '1',
											'max'  => '10',
											'unit' => '',
											'range' => false,
											'default' => '5'
										)
									),
									array(
										'id'      => 'b_vp',
										'type'    => 'slider_range',
										'label'   => __( 'Viewport', 'themify' )
									),
								)
							),
							'r'       => array(
								'label'   => __( 'Rotate', 'themify' ),
								'options' => array(
			    array(
										'id'      => 'r_dir',
				'type' => 'select',
										'label'   => __( 'Direction', 'themify' ),
				'options' => array(
											'' => '',
											'toleft'  => __( 'To Left', 'themify' ),
											'toright' => __( 'To Right', 'themify' )
				    )
				),
									array(
										'id'    => 'r_num',
										'type'  => 'range',
										'label' => __( 'Number of Spins', 'themify' ),
										'units' => array(
											'' => array(
												'min' => '0.05',
												'max' => '100',
                                                'increment' => '0.1'
											)
										)
			    ),
			    array(
										'id'    => 'r_origin',
										'type'  => 'position_box',
										'label' => __( 'Transform Origin', 'themify' )
									),
									array(
										'id'      => 'r_vp',
										'type'    => 'slider_range',
										'label'   => __( 'Viewport', 'themify' )
									),
								)
							),
							's'        => array(
								'label'   => __( 'Scale', 'themify' ),
				'options' => array(
			    array(
										'id'      => 's_dir',
										'type'    => 'select',
										'label'   => __( 'Direction', 'themify' ),
				'options' => array(
											'' => '',
											'up'   => __( 'Scale Up', 'themify' ),
											'down' => __( 'Scale Down', 'themify' )
										)
									),
									array(
										'id'    => 's_ratio',
										'type'  => 'range',
										'label' => __( 'Scale Ratio', 'themify' ),
										'units' => array(
											'' => array(
												'min' => '1',
												'max' => '30',
												'increment' => '0.1'
											)
										)
									),
									array(
										'id'    => 's_origin',
										'type'  => 'position_box',
										'label' => __( 'Transform Origin', 'themify' )
									),
									array(
										'id'      => 's_vp',
										'type'    => 'slider_range',
										'label'   => __( 'Viewport', 'themify' )
				),
								)
							),
						)
			    ),
			)
		    ),
		    's_e_s' => array(
			'options' => array(
			    array(
				'type' => 'multi',
				'wrap_class' => 'stick_middle_wrapper',
				'options' => array(
				    array(
					'id' => 'stick_at_check',
					'type' => 'checkbox',
					'options' => array(
					    array('name' => 'stick_at_check', 'value' => __('Stick at', 'themify'))
					),
					'binding' => array(
					    'not_checked' => array(
						'hide' => array('unstick_when_wrapper')
					    ),
					    'checked' => array(
						'show' => array('unstick_when_wrapper')
					    )
					)
				    ),
				    array(
					'id' => 'stick_at_position',
					'type' => 'select',
					'options' => array(
					    'top' => __('Top Position', 'themify'),
					    'bottom' => __('Bottom Position', 'themify')
					)
				    ),
				    array(
					'id' => 'stick_at_pos_val',
					'type' => 'range',
					'units' => array(
					    'px' => array(
						'min' => 0,
						'max' => 1000000
					    ),
					    '%' => array(
						'min' => 0,
						'max' => 100
					    )
					)
				    )
				)
			    ),
			    array(
				'type' => 'multi',
				'wrap_class' => 'stick_middle_wrapper unstick_when_wrapper',
				'options' => array(
				    array(
					'id' => 'unstick_when_check',
					'type' => 'checkbox',
					'options' => array(
					    array('name' => 'unstick_when_check', 'value' => __('Un-stick when', 'themify'))
					)
				    ),
				    array(
					'id' => 'unstick_when_element',
					'type' => 'select',
					'options' => array(
					    'builder_end' => __('Builder Content End', 'themify'),
					    'row' => __('Row', 'themify'),
					    'module' => __('Module', 'themify')
					),
					'binding' => array(
					    'builder_end' => array(
						'hide' => array('unstick_wrapper_extend_option', 'unstick_when_el_row_select', 'unstick_when_el_module_select')
					    ),
					    'row' => array(
						'show' => array('unstick_wrapper_extend_option', 'unstick_when_el_row_select'),
						'hide' => array('unstick_when_el_module_select')
					    ),
					    'module' => array(
						'show' => array('unstick_wrapper_extend_option', 'unstick_when_el_module_select'),
						'hide' => array('unstick_when_el_row_select')
					    ),
					    'select' => array(
						'value' => 'builder_end',
						'hide' => array('unstick_wrapper_extend_option', 'unstick_when_el_row_select', 'unstick_when_el_module_select')
					    ),
					)
				    ),
				    array(
					'id' => 'unstick_when_el_row_id',
					'type' => 'sticky',
					'wrap_class' => 'unstick_when_el_row_select',
					'key' => 'row'
				    ),
				    array(
					'id' => 'unstick_when_el_mod_id',
					'type' => 'sticky',
					'wrap_class' => 'unstick_when_el_module_select',
					'key' => 'module'
				    ),
				    array(
					'id' => 'unstick_when_condition',
					'type' => 'select',
					'wrap_class' => 'unstick_wrapper_extend_option',
					'options' => array(
					    'hits' => __('Hits', 'themify'),
					    'passes' => __('Passes', 'themify')
					)
				    ),
				    array(
					'id' => 'unstick_when_pos',
					'type' => 'select',
					'wrap_class' => 'unstick_wrapper_extend_option',
					'options' => array(
					    'this' => __('This element', 'themify'),
					    'top' => __('Top Position', 'themify'),
					    'bottom' => __('Bottom Position', 'themify')
					),
					'binding' => array(
					    'this' => array(
						'hide' => array('unstick_wrapper_position_unit')
					    ),
					    'top' => array(
						'show' => array('unstick_wrapper_position_unit')
					    ),
					    'bottom' => array(
						'show' => array('unstick_wrapper_position_unit')
					    )
					)
				    ),
				    array(
					'id' => 'unstick_when_pos_val',
					'type' => 'range',
					'wrap_class' => 'unstick_wrapper_position_unit',
					'units' => array(
					    'px' => array(
						'min' => 0,
						'max' => 100000
					    ),
					    '%' => array(
						'min' => 0,
						'max' => 100
					    )
					)
				    )
				)
			    ),
			)
	    ),
	    )
	    ),
	));
    }

    /**
     * Append visibility controls to row/modules.
     * @access    public
     * @return    array
     */
    public static function get_visibility() {
	$options = array(
	    'on' => array('name' => '', 'value' => 's'),
	    'off' => array('name' => 'hide', 'value' => 'hi')
	);
	return array(
	    array(
		'type' => 'separator',
		'label' => 'visibility',
	    ),
	    array(
		'id' => 'visibility_desktop',
		'label' => __('Desktop', 'themify'),
		'type' => 'toggle_switch',
		'default' => 'on',
		'options' => $options,
		'wrap_class' => 'tb_module_visibility_control'
	    ),
	    array(
		'id' => 'visibility_tablet',
		'label' => __('Tablet', 'themify'),
		'type' => 'toggle_switch',
		'default' => 'on',
		'options' => $options,
		'wrap_class' => 'tb_module_visibility_control'
	    ),
	    array(
		'id' => 'visibility_tablet_landscape',
		'label' => __('Tablet Landscape', 'themify'),
		'type' => 'toggle_switch',
		'default' => 'on',
		'options' => $options,
		'wrap_class' => 'tb_module_visibility_control'
	    ),
	    array(
		'id' => 'visibility_mobile',
		'label' => __('Mobile', 'themify'),
		'type' => 'toggle_switch',
		'default' => 'on',
		'options' => $options,
		'wrap_class' => 'tb_module_visibility_control'
	    ),
	    array(
		'id' => 'sticky_visibility',
		'label' => __('Sticky Visibility', 'themify'),
		'type' => 'toggle_switch',
		'options' => array(
		    'on' => array('name' => 'hide')
		),
        'wrap_class' => 'tb_module_visibility_control',
		'help' => __('Hide this when parent row\'s sticky scrolling is active', 'themify'),
	    ),
	    array(
		'id' => 'visibility_all',
		'label' => __('Hide All', 'themify'),
		'type' => 'toggle_switch',
		'options' => array(
		    'on' => array('name' => 'hide_all')
		),
		'binding' => array(
		    'not_checked' => array(
			'show' => array('tb_module_visibility_control')
		    ),
		    'checked' => array(
			'hide' => array('tb_module_visibility_control')
		    )
		),
		'help' => __('Hide this in all devices', 'themify')
	    )
	);
    }

    public static function generateID() {
	$hash = '';
	$alpha_numeric = 'abcdefghijklmnopqrstuvwxyz0123456789';
	for ($i = 0; $i < 4; ++$i) {
	    $hash .= '' . $alpha_numeric[rand(0, 35)];
	}
	$m = microtime();
	$len = strlen($m);
	if ($len > 10) {
	    $len = floor($len / 2);
	}
	--$len;
	for ($i = 0; $i < 3; ++$i) {
	    $h = $m[rand(2, $len)];
	    if ($h === '') {
		$h = $m[rand(2, ( $len - 1))];
	    }
	    $hash .= $h;
	}
	return $hash;
    }

    public static function get_slider_options() {

	return array(array(
		'id' => 'visible_opt_slider',
		'type' => 'select',
		'options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7),
		'after' => __('Visible Slides', 'themify')
	    ),
		array(
			'id' => 'tab_visible_opt_slider',
			'type' => 'select',
			'options' => array('', 1 => 1, 2 => 2, 3 => 3, 4 => 4),
			'after' => __('Tablet Visible Slides', 'themify')
		),
        array(
		'id' => 'mob_visible_opt_slider',
		'type' => 'select',
		'options' => array('', 1 => 1, 2 => 2, 3 => 3, 4 => 4),
		'after' => __('Mobile Visible Slides', 'themify')
	    ),
	    array(
		'id' => 'auto_scroll_opt_slider',
		'type' => 'select',
		'options' => array(
		    'off' => __('Off', 'themify'),
		    1 => __('1 sec', 'themify'),
		    2 => __('2 sec', 'themify'),
		    3 => __('3 sec', 'themify'),
		    4 => __('4 sec', 'themify'),
		    5 => __('5 sec', 'themify'),
		    6 => __('6 sec', 'themify'),
		    7 => __('7 sec', 'themify'),
		    8 => __('8 sec', 'themify'),
		    9 => __('9 sec', 'themify'),
		    10 => __('10 sec', 'themify'),
		    15 => __('15 sec', 'themify'),
		    20 => __('20 sec', 'themify')
		),
			'binding' => array(
				'off' => array(
					'hide' => array('pause_on_hover_slider','play_pause_control')
				),
				'select' => array(
					'value' => range(1,20),
					'show' => array('pause_on_hover_slider','play_pause_control')
				)
			),
		'after' => __('Auto Scroll', 'themify')
	    ),
		array(
			'id' => 'scroll_opt_slider',
			'type' => 'select',
			'options' => array(1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7),
			'after' => __('Scroll', 'themify')
		),
	    array(
		'id' => 'speed_opt_slider',
		'type' => 'select',
		'options' => array(
		    'normal' => __('Normal', 'themify'),
		    'fast' => __('Fast', 'themify'),
		    'slow' => __('Slow', 'themify')
		),
		'after' => __('Speed', 'themify')
	    ),
	    array(
		'id' => 'effect_slider',
		'type' => 'select',
		'options' => array(
		    'scroll' => __('Slide', 'themify'),
		    'fade' => __('Fade', 'themify'),
		    'crossfade' => __('Cross Fade', 'themify'),
		    'cover' => __('Cover', 'themify'),
		    'cover-fade' => __('Cover Fade', 'themify'),
		    'uncover' => __('Uncover', 'themify'),
		    'uncover-fade' => __('Uncover Fade', 'themify'),
		    'continuously' => __('Continuously', 'themify')
		),
		'after' => __('Effect', 'themify')
	    ),
	    array(
		'id' => 'pause_on_hover_slider',
		'type' => 'toggle_switch',
		'options' => array(
		    'on' => array('name' => 'resume', 'value' => 'y'),
		    'off' => array('name' => 'false', 'value' => 'no'),
		),
		'after' => __('Pause On Hover', 'themify'),
		'default' => 'on',
	    ),
		array(
			'id' => 'play_pause_control',
			'type' => 'toggle_switch',
			'options' => 'simple',
			'after' => __('Play/Pause Button', 'themify'),
			'default' => 'off',
		),
	    array(
		'id' => 'wrap_slider',
		'type' => 'toggle_switch',
		'options' => 'simple',
		'after' => __('Wrap', 'themify'),
		'default' => 'on',
	    ),
	    array(
		'id' => 'show_nav_slider',
		'type' => 'toggle_switch',
		'options' => 'simple',
		'after' => __('Pagination', 'themify'),
		'default' => 'on',
	    ),
	    array(
		'id' => 'show_arrow_slider',
		'type' => 'toggle_switch',
		'after' => __('Slider Arrows', 'themify'),
		'options' => 'simple',
		'binding' => array(
		    'no' => array(
			'hide' => array('show_arrow_buttons_vertical')
		    ),
		    'select' => array(
			'value' => 'no',
			'show' => array('show_arrow_buttons_vertical')
		    )
		),
		'default' => 'on',
	    ),
	    array(
		'id' => 'show_arrow_buttons_vertical',
		'type' => 'checkbox',
		'options' => array(
		    array('name' => 'vertical', 'value' => __('Display arrows middle', 'themify'))
		)
	    ),
	    array(
		'id' => 'left_margin_slider',
		'type' => 'number',
		'after' => __('Left margin (px)', 'themify')
	    ),
	    array(
		'id' => 'right_margin_slider',
		'type' => 'number',
		'after' => __('Right margin (px)', 'themify')
	    ),
	    array(
		'id' => 'height_slider',
		'type' => 'select',
		'options' => array(
		    'variable' => __('Variable', 'themify'),
		    'auto' => __('Auto', 'themify')
		),
		'after' => __('Height', 'themify'),
		'help' => __('"Auto" measures the highest slide and all other slides will be set to that size. "Variable" makes every slide has it\'s own height.', 'themify')
	    )
	);
    }
    
    
    public static function removeElementIds(array $data){
	foreach($data as &$r){
	    unset($r['cid'],$r['element_id']);

	    if (!empty($r['cols'])) {

		foreach($r['cols'] as &$c){

		    unset($c['cid'],$c['element_id']);

		    if (!empty($c['modules'])) {

			foreach($c['modules'] as &$m){

			    unset($m['element_id']);
			    if(isset($m['mod_settings']['cid'])){
				unset($m['mod_settings']['cid']);
			    }
			    if (!empty($m['cols'])) {

				foreach ($m['cols'] as &$sub_col) {

				    unset($sub_col['cid'],$sub_col['element_id']);

				    if (!empty($sub_col['modules'])) {

					foreach ($sub_col['modules'] as &$sub_m) {

					    unset($sub_m['element_id']);
					    if(isset($sub_m['mod_settings']['cid'])){
						unset($sub_m['mod_settings']['cid']);
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
	return $data;
    }
	
    public static function parseTerms($terms,$taxonomy){
	// deal with how category fields are saved
	$new_terms = $new_exclude_terms = array();
	$terms = preg_replace('/\|[multiple|single]*$/', '', $terms);

	$temp_terms = explode( ',', $terms );
	foreach( $temp_terms as $t ) {
		$t=trim($t);
		$isNumeric=is_numeric( $t );
		if($isNumeric===false) {
		    if($term = term_exists( preg_replace( '/^-/', '', $t ), $taxonomy ) ){
			$t = ( $t[0] === '-' ? -1 : 1 ) * is_array( $term ) ? $term['term_id'] : $term;
			$isNumeric=true;
		    }
		    else{
			continue;
		    }
		}
		if($isNumeric===true) {
		    if(0 <= $t){
			$new_terms[] =  $t;
		    }
		    else{
			$new_exclude_terms[] =  (-1)*$t;
		    }
		}
	}
	return array('in'=>$new_terms,'ex'=>$new_exclude_terms);
    }
    
    public static function parseTermsQuery(&$args,$terms,$taxonomy){
	$tmp = self::parseTerms($terms,$taxonomy );
	$new_terms = $tmp['in'];
	$new_exclude_terms = $tmp['ex'];
	$tmp=$terms=null;
	if (!empty($new_terms) && !in_array('0', $new_terms)) {
	    $args['tax_query'] = array(
		array(
		    'taxonomy' => $taxonomy,
		    'field' => 'id',
		    'terms' => $new_terms
		)
	    );
	}
	if (!empty($new_exclude_terms)) {
	    $args['tax_query'][] = array(
		'taxonomy' => $taxonomy,
		'field' => 'id',
		'terms' => $new_exclude_terms,
		'operator' => 'NOT IN'
	    );
	}
    }
}
