<?php
/**
 * This file contain abstraction class to create module object.
 *
 * Themify_Builder_Component_Module class should be used as main class and
 * create any child extend class for module.
 * 
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 */

/**
 * The abstract class for Module.
 *
 * Abstraction class to initialize module object, don't initialize
 * this class directly but please create child class of it.
 *
 *
 * @package    Themify_Builder
 * @subpackage Themify_Builder/classes
 * @author     Themify
 */
class Themify_Builder_Component_Module extends Themify_Builder_Component_Base {

    /**
     * Module Name.
     * 
     * @access public
     * @var string $name
     */
    public $name;

    /**
     * Module Slug.
     * 
     * @access public
     * @var string $slug
     */
    public $slug;

	/**
	 * Module Category.
	 *
	 * @access public
	 * @var string $category
	 */
	public $category;

    /**
     * Custom Post Type arguments.
     * 
     * @access public
     * @var array $cpt_args
     */
    public $cpt_args = array();

    /**
     * Metabox options.
     * 
     * @access public
     * @var array $meta_box
     */
    protected $meta_box = array();
    
    
    private static $assets = array();
    

    /**
     * Constructor.
     * 
     * @access public
     * @param array $params 
     */
    public function __construct($params) {
        $this->name = $params['name'];
        $this->slug = $params['slug'];
        $this->category = !empty($params['category']) ? $params['category'] : array('general');
    }

    /**
     * Get module options.
     * 
     * @access public
     */
    public function get_options() {
        return array();
    }

    /**
     * Get module styling options.
     * 
     * @access public
     */
    public function get_styling() {
        return array();
    }

    /**
     * Render a module, as a plain text
     *
     * @return string
     */
    public function get_plain_text($module) {
        $options = $this->get_options();
        if (empty($options))
            return '';
        $out = array();

        foreach ($options as $field) {
            // sanitization, check for existence of needed keys
            if (!isset($field['type'],$field['id'],$module[$field['id']])) {
                continue;
            }
            // text, textarea, and wp_editor field types
            if (in_array($field['type'], array('text', 'textarea', 'wp_editor'), true)) {
                $out[] = $module[$field['id']];
            }
            // builder field type
            elseif ($field['type'] === 'builder' && is_array($module[$field['id']])) {
                // gather text field types included in the "builder" field type
                $text_fields = array();
                foreach ($field['options'] as $row_field) {
                    if (isset($row_field['type']) && in_array($row_field['type'], array('text', 'textarea', 'wp_editor'), true)) {
                        $text_fields[] = $row_field['id'];
                    }
                }
                foreach ($module[$field['id']] as $row) {
                    // separate fields from the row that have text fields
                    $texts = array_intersect_key($row, array_flip($text_fields));
                    // add them to the output
                    $out = array_merge(array_values($texts), $out);
                }
            }
        }

        return implode(' ', $out);
    }

    /**
     * Load builder modules
     */
    public static function load_modules($mod_name=false) {
        // load modules
        if($mod_name){
            static $active_modules=null;
            if($active_modules===null){
                $active_modules = Themify_Builder_Model::get_modules('active');
            }
            if(!isset($active_modules[$mod_name])){
                return false;
            }
            $items = array($active_modules[$mod_name]);
        }
        else{
            $items = Themify_Builder_Model::get_modules('active');
        }   
        foreach ($items as $m) {
            require_once( $m['dirname'] . '/' . $m['basename'] );
        }
    }

    public function get_assets() {
        
    }
    
    public static function get_modules_assets() {
        return self::$assets;
    }
    
    public static function add_modules_assets($k,$item) {
        self::$assets[$k] = $item;
    }

    public function render($slug, $mod_id, $builder_id, $settings,$element_id=false) {
        $template = $slug==='highlight' || $slug==='testimonial' || $slug==='post' || $slug==='portfolio'? 'blog' : $slug;
        
        $vars = array(
            'module_ID' => $mod_id,
            'mod_name' => $slug,
            'builder_id' => $builder_id,
            'mod_settings' => $settings,
	    'element_id'=>$element_id
        );
	$vars = apply_filters( 'themify_builder_module_render_vars', $vars );
        return self::retrieve_template('template-' . $template . '.php', $vars, '', '', false);
    }

    /**
     * Initialize Custom Post Type.
     * 
     * @access public
     * @param array $args 
     */
    public function initialize_cpt($args) {
	$this->cpt_args = $args;
	$this->load_cpt();
	if(Themify_Builder::$frontedit_active===false){
	    add_filter( 'post_updated_messages', array($this, 'cpt_updated_messages') );
	}
    }

    /**
     * Load Custom Post Type.
     * 
     * @access public
     */
    public function load_cpt() {
        global $ThemifyBuilder;

        if (post_type_exists($this->slug)) {
            // check taxonomy register
            if (!taxonomy_exists($this->slug . '-category')) {
                $this->register_taxonomy();
            }
        } else {
            $this->register_cpt();
            $this->register_taxonomy();
            add_filter('themify_do_metaboxes', array($this, 'cpt_meta_boxes'));

            // push to themify builder class
            $ThemifyBuilder->push_post_types($this->slug);
        }
    }

    /**
     * Customize post type updated messages.
     * 
     * @access public
     * @param $messages
     * @return mixed
     */
    public function cpt_updated_messages($messages) {
        global $post, $post_ID;
        $view = get_permalink($post_ID);

        $messages[$this->slug] = array(
            0 => '',
            1 => sprintf(__('%s updated. <a href="%s">View %s</a>.', 'themify'), $this->name, esc_url($view), $this->name),
            2 => __('Custom field updated.', 'themify'),
            3 => __('Custom field deleted.', 'themify'),
            4 => sprintf(__('%s updated.', 'themify'), $this->name),
            5 => isset($_GET['revision']) ? sprintf(__('%s restored to revision from %s', 'themify'), $this->name, wp_post_revision_title((int) $_GET['revision'], false)) : false,
            6 => sprintf(__('%s published.', 'themify'), $this->name),
            7 => sprintf(__('%s saved.', 'themify'), $this->name),
            8 => sprintf(__('%s submitted.', 'themify'), $this->name),
            9 => sprintf(__('%s scheduled for: <strong>%s</strong>.', 'themify'), $this->name, date_i18n(__('M j, Y @ G:i', 'themify'), strtotime($post->post_date))),
            10 => sprintf(__('%s draft updated.', 'themify'), $this->name)
        );
        return $messages;
    }

    /**
     * Register Post type.
     * 
     * @access public
     * @return void
     */
    public function register_cpt() {
        $cpt = $this->cpt_args;
        $options = array(
            'labels' => array(
                'name' => $cpt['plural'],
                'singular_name' => $cpt['singular'],
                'add_new' => __('Add New', 'themify'),
                'add_new_item' => sprintf(__('Add New %s', 'themify'), $cpt['singular']),
                'edit_item' => sprintf(__('Edit %s', 'themify'), $cpt['singular']),
                'new_item' => sprintf(__('New %s', 'themify'), $cpt['singular']),
                'view_item' => sprintf(__('View %s', 'themify'), $cpt['singular']),
                'search_items' => sprintf(__('Search %s', 'themify'), $cpt['plural']),
                'not_found' => sprintf(__('No %s found', 'themify'), $cpt['plural']),
                'not_found_in_trash' => sprintf(__('No %s found in Trash', 'themify'), $cpt['plural']),
                'menu_name' => $cpt['plural']
            ),
            'supports' => isset($cpt['supports']) ? $cpt['supports'] : array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt'),
            //'menu_position' => $position++,
            'hierarchical' => false,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => false,
            'publicly_queryable' => true,
            'rewrite' => array('slug' => isset($cpt['rewrite']) ? $cpt['rewrite'] : strtolower($cpt['singular'])),
            'query_var' => true,
            'can_export' => true,
            'capability_type' => 'post',
            'menu_icon' => isset($cpt['menu_icon']) ? $cpt['menu_icon'] : ''
        );

        register_post_type($this->slug, $options);
    }

    /**
     * Register Taxonomy.
     * 
     * @access public
     * @return void
     */
    public function register_taxonomy() {
        $cpt = $this->cpt_args;
        $options = array(
            'labels' => array(
                'name' => sprintf(__('%s Categories', 'themify'), $cpt['singular']),
                'singular_name' => sprintf(__('%s Category', 'themify'), $cpt['singular']),
                'search_items' => sprintf(__('Search %s Categories', 'themify'), $cpt['singular']),
                'popular_items' => sprintf(__('Popular %s Categories', 'themify'), $cpt['singular']),
                'all_items' => sprintf(__('All Categories', 'themify'), $cpt['singular']),
                'parent_item' => sprintf(__('Parent %s Category', 'themify'), $cpt['singular']),
                'parent_item_colon' => sprintf(__('Parent %s Category:', 'themify'), $cpt['singular']),
                'edit_item' => sprintf(__('Edit %s Category', 'themify'), $cpt['singular']),
                'update_item' => sprintf(__('Update %s Category', 'themify'), $cpt['singular']),
                'add_new_item' => sprintf(__('Add New %s Category', 'themify'), $cpt['singular']),
                'new_item_name' => sprintf(__('New %s Category', 'themify'), $cpt['singular']),
                'separate_items_with_commas' => sprintf(__('Separate %s Category with commas', 'themify'), $cpt['singular']),
                'add_or_remove_items' => sprintf(__('Add or remove %s Category', 'themify'), $cpt['singular']),
                'choose_from_most_used' => sprintf(__('Choose from the most used %s Category', 'themify'), $cpt['singular']),
                'menu_name' => sprintf(__('%s Category', 'themify'), $cpt['singular']),
            ),
            'public' => true,
            'show_in_nav_menus' => false,
            'show_ui' => true,
            'show_admin_column' => true,
            'show_tagcloud' => true,
            'hierarchical' => true,
            'rewrite' => true,
            'query_var' => true
        );

        register_taxonomy($this->slug . '-category', array($this->slug), $options);

        // admin column custom taxonomy
        add_filter('manage_taxonomies_for_' . $this->slug . '_columns', array($this, 'category_columns'));
    }

    /**
     * Category Columns.
     * 
     * @access public
     * @param array $taxonomies 
     * @return array
     */
    public function category_columns($taxonomies) {
        $taxonomies[] = $this->slug . '-category';
        return $taxonomies;
    }

	/**
	 * Return metabox options for this module
	 *
	 * @return array
	 */
	public function get_metabox() {
		return array();
	}

    /**
     * If there's not an options tab in Themify Custom Panel meta box already defined for this post type, like "Portfolio Options", add one.
     *
     * @since 2.3.8
     *
     * @param array $meta_boxes
     *
     * @return array
     */
    public function cpt_meta_boxes($meta_boxes = array()) {
		Themify_Builder_Model::load_general_metabox(); // setup common fields

        $meta_box_id = $this->slug . '-options';
        if ( ! in_array( $meta_box_id, wp_list_pluck( $meta_boxes, 'id' ), true ) ) {
            $options = $this->get_metabox();
			if ( ! empty( $options ) ) {
				$meta_boxes = array_merge( $meta_boxes, array(
					array(
						'name' => sprintf( __( '%s Options', 'themify' ), $this->cpt_args['singular'] ),
						'id' => $meta_box_id,
						'options' => $options,
						'pages' => $this->slug
					)
				));
			}
        }
        return $meta_boxes;
    }

    /**
     * Get Module Title.
     * 
     * @access public
     * @param object $module 
     */
    public function get_title($module) {
        return '';
    }

    public function get_name() {
        return $this->slug;
    }

    public function print_template($echo=false) {
        ob_start();
        $template_file = self::locate_template( 'template-' . $this->slug . '-visual.php' );
        if( file_exists( $template_file ) ) {
            include $template_file;
        } else {
            $this->_visual_template();
        }
        $content_template = ob_get_clean();
        if (empty($content_template)) {
            return false;
        }
        $output = '<script type="text/template" id="tmpl-builder-'.$this->slug.'-content">'.$content_template.'</script>';
        if(!$echo){
            return $output;
        }
        echo $output;
    }

    protected function _visual_template() {
        
    }

    public function get_default_settings() {
        return false;
    }

    public function get_visual_type() {
        return 'live';
    }

    protected static function get_module_args() {
        static $args = null;
        if($args===null){
            $args = array();
            $args['before_title'] = '<h3 class="module-title">';
            $args['after_title'] = '</h3>';
            $args = apply_filters('themify_builder_module_args', $args);
        }
        return $args;
    }

	public function get_form_settings( $onlyStyle = false ) {
		$styling = $this->get_styling();
		// Add Z-index to all modules
		$styling = $this->add_zindex_filed($styling);
		if ( $onlyStyle === true ) {
			return $styling;
		}
		$module_form_settings = array(
			'setting' => array(
				'name' => ucfirst( $this->name ),
				'options' => apply_filters( 'themify_builder_module_settings_fields', $this->get_options(), $this )
			)
		);
		if ( !empty( $styling ) ) {
			$module_form_settings['styling'] = array( 'options' => $styling );
		}
		if ( $this->get_animation() === true ) {
			$module_form_settings['animation'] = true;
		}
		$module_form_settings['visibility'] = true;
		return apply_filters( 'themify_builder_module_lightbox_form_settings', $module_form_settings, $this );
	}


    /**
     * Get template for module
     * @param $mod
     * @param bool $echo
     * @param bool $wrap
     * @param array $identifier
     * @return bool|string
     */
    public static function template($mod, $builder_id = 0, $echo = true, $identifier = array()) {
        if (Themify_Builder::$frontedit_active===false) {
            /* allow addons to control the display of the modules */
            $display = apply_filters('themify_builder_module_display', true, $mod, $builder_id, $identifier);
            if (false === $display || ( isset( $mod['mod_settings']['visibility_all'] ) && $mod['mod_settings']['visibility_all'] === 'hide_all' ) ) {
                return false;
            }
        }	
        if(!isset( $mod['mod_name'])){
            return false;
        }
        $output = '';
	$slug = $mod['mod_name'];
	static $isLoaded = array();
		
        if( !isset($isLoaded[$slug])){//load only the modules in the page
	    if(!isset(Themify_Builder_Model::$modules[$slug])){
		self::load_modules($slug);
	    }
            // check whether module active or not
            if (!Themify_Builder_Model::check_module_active($slug)) {
                return false;
            }
	    $isLoaded[$slug]= true;
	    if(Themify_Builder::$frontedit_active===false && empty($mod['mod_settings']['_render_plain_content'])){
                $assets = Themify_Builder_Model::$modules[ $slug ]->get_assets();
                if (!isset($assets['async'])  && isset($assets['css']) && !empty($assets)) {
					$id=$slug.'-css';
					if((isset($_REQUEST['themify_builder_infinite_scroll']) && $_REQUEST['themify_builder_infinite_scroll']==='yes')|| TFCache::is_ajax()){
						$external = '';
						if(isset($assets['external'])){
							$external='data-external="'.esc_attr(json_encode(array($assets['external']))).'" ';
							unset($assets['external']);
						}
						$output='<div '.$external.'data-data="'.esc_attr(json_encode(array($slug=>$assets))).'" class="tb_async_module"></div>';
						if($echo===true){
						    echo $output;
						    $output='';
						}
					}
					elseif($echo===true){
						$assets['css']=(array) $assets['css'];
						foreach ($assets['css'] as $s) {
							$ver = isset($assets['ver']) ?  $assets['ver'] : THEMIFY_VERSION;
							$href = $s.'?ver='.$ver;
							// addon's stylesheet loads after themify-builder-styles.css, but before theme's stylesheet.
							echo '<script type="text/javascript">
									var id ="'.$id.'";
									if(document.getElementById(id)===null){
									    var link = document.createElement("link");
									    link.rel="stylesheet";
									    link.type="text/css";
									    link.id=id;
									    link.href="'.$href.'";
									    document.body.appendChild(link);
									}
							</script>';	
						}
					}
			    unset($assets['css']);
                }
                if(isset($assets['js']) || isset($assets['css'])){
                    self::$assets[$slug] = $assets;
                }
            }
        }
        $mod['mod_settings'] = isset($mod['mod_settings']) ? $mod['mod_settings'] : array();
	
	$mod_id =$slug . '-' . $builder_id . '-' . implode('-', $identifier);
        $mod['mod_settings']+=self::get_module_args();
		if($echo!==true){
        $output.= PHP_EOL; // add line break
        ob_start();
		}
        do_action('themify_builder_background_styling',$builder_id,$mod,$mod_id,'module');
		if($echo!==true){
        $output .= ob_get_clean();
        // add line break
        $output .= PHP_EOL;
        }
        // render the module
        $output .= Themify_Builder_Model::$modules[ $slug ]->render($slug, $mod_id, $builder_id, $mod['mod_settings'],isset($mod['element_id'])?$mod['element_id']:false);
	if ($echo===true) {
		echo $output;
	}
        else{
        $output .= PHP_EOL;
            return $output;
        }
    }

    /**
     * Returns module title custom style
     * @param string $slug 
     * @return array
     */
    protected function module_title_custom_style() {
        return array(
            // Background
            self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('.module .module-title', 'background_color_module_title', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('.module .module-title', 'bg_c_m_t','bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
            // Font
            self::get_expand('f', array(
		self::get_tab(array(
		    'n' =>array(
			'options' => array(
			    self::get_font_family('.module .module-title', 'font_family_module_title'),
			    self::get_color('.module .module-title', 'font_color_module_title'),
			    self::get_font_size('.module .module-title', 'font_size_module_title'),
			    self::get_line_height('.module .module-title', 'line_height_module_title'),
			    self::get_text_align('.module .module-title', 'text_align_module_title'),
			    self::get_text_shadow('.module .module-title', 't_sh_m_t'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('.module .module-title', 'f_f_m_t', 'h'),
			    self::get_color('.module .module-title', 'f_c_m_t', null, null, 'h'),
			    self::get_font_size('.module .module-title', 'f_s_m_t', '', 'h'),
			    self::get_line_height('.module .module-title', 'l_h_m_t', 'h'),                        
			    self::get_text_align('.module .module-title', 't_a_m_t', 'h'),
			    self::get_text_shadow('.module .module-title', 't_sh_m_t', 'h'),
			)
		    )
		))
	    ))
        );
    }

    /**
     * Get plain content of the module output.
     * 
     * @param array $module 
     * @return string
     */
    public function get_plain_content( $module ) {
		if(isset($module['mod_settings'])){
			$module['mod_settings']['_render_plain_content'] = true;
			// Remove format text filter including do_shortcode
			if (!Themify_Builder_Model::is_front_builder_activate()) {
			remove_filter('themify_builder_module_content', array('Themify_Builder_Model', 'format_text'));
			}
		}
		else{
			 $module['mod_settings']=array('_render_plain_content'=>true);
		}
        return self::template( $module, 0, false );
    }
    /**
    * Title tag settings for Post module
    *
    * @return array
    */
    public static function post_title_tag($args) {
	    $args['tag'] = 'h2';
	    return $args;
    }

	/**
	 * Add z-index option to styling tab
	 *
	 * @return array
	 */
	public function add_zindex_filed($styling) {
		$field = self::get_expand('zi',
			array(
				self::get_zindex('','custom_parallax_scroll_zindex')
			)
		);
		if ( isset( $styling['type'] ) && 'tabs' === $styling['type'] ) {
			$styling['options'][ key( $styling['options'] ) ]['options'][] = $field;
		} else {
			$styling[] = $field;
		}
		return $styling;
	}
}

if (!function_exists('themify_builder_testimonial_author_name')) :

    function themify_builder_testimonial_author_name($post, $show_author) {
	$out = '';
	if ('yes' === $show_author) {
	    if ($author = get_post_meta($post->ID, '_testimonial_name', true))
		$out = '<span class="dash"></span><cite class="testimonial-name">' . $author . '</cite> <br/>';

	    if ($position = get_post_meta($post->ID, '_testimonial_position', true))
		$out .= '<em class="testimonial-title">' . $position;

	    if ($link = get_post_meta($post->ID, '_testimonial_link', true)) {
		if ($position) {
		    $out .= ', ';
		} else {
		    $out .= '<em class="testimonial-title">';
		}
		$out .= '<a href="' . esc_url($link) . '">';
	    }

	    if ($company = get_post_meta($post->ID, '_testimonial_company', true))
		$out .= $company;
	    else
		$out .= $link;

	    if ($link)
		$out .= '</a>';

	    $out .= '</em>';
	}
	return $out;
    }

endif;
