<?php

class Themify_Builder_Stylesheet {

    public static $generateStyles = array();
    private static  $ids = array();
    private static $activeBuilderHandler=null;
    private static $isLoaded = array();
    public static $isLoadedFonts=array();
    /**
     * Constructor
     * 
     * @access public
     * @param object Themify_Builder $builder 
     */
    public static function init() {
        if (defined('DOING_AJAX')) {
            add_action('wp_ajax_tb_slider_live_styling', array(__CLASS__, 'slider_live_styling'), 10);
	    add_action('wp_ajax_nopriv_tb_generate_on_fly', array(__CLASS__, 'save_builder_css'), 10);
	    add_action('wp_ajax_tb_generate_on_fly', array(__CLASS__, 'save_builder_css'), 10);
        } else if(!is_admin()){
	    if(!Themify_Builder_Model::is_front_builder_activate()){
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_stylesheet'), 14);
	    }
	    else{
		add_filter('style_loader_tag', array(__CLASS__, 'set_active_builder_style_position'), 15, 4);
	    }
        }
    }
    


    /**
     * Checks if the builder stylesheet exists and enqueues it.
     * 
     * @since 2.2.5
     * 
     * @return bool True if enqueue was successful, false otherwise.
     */
    public static function enqueue_stylesheet( $return = false, $post_id = null ) {
		$isActive=Themify_Builder_Model::is_front_builder_activate();
		if(($isActive===false && isset(self::$isLoaded[$post_id])) || (defined( 'REST_REQUEST' ) && REST_REQUEST )){
			return false;
		}
		$stylesheet_path = self::get_stylesheet( 'bydir', $post_id );
		if(!empty($stylesheet_path['id'])){
			if (!self::is_readable_and_not_empty( $stylesheet_path['url'] ) ) {
				$filesystem = Themify_Filesystem::get_instance();
				if(!$filesystem->execute->is_file(self::getTmpPath($stylesheet_path['url']))){
					$id = $stylesheet_path['id'];
					if(!isset(self::$generateStyles[$id])){
						self::$generateStyles[$id] = true;
						$post_data = $GLOBALS['ThemifyBuilder_Data_Manager']->get_data($id);
						if($post_data){
							static $strucutre =null;
							if($strucutre===null){
								$strucutre = true;
								Themify_Builder_Component_Module::load_modules();
								wp_enqueue_script('tb_builder_js_style',themify_enque(THEMIFY_URI.'/js/generate-style.js'),null,THEMIFY_VERSION,true);

								add_filter( 'themify_google_fonts_full_list', '__return_true' );
								wp_localize_script('tb_builder_js_style', 'ThemifyBuilderStyle', array(
										'styles'=>Themify_Builder::getComponentJson(true),
										'points'=>themify_get_breakpoints(),
										'nonce'=>wp_create_nonce('tb_load_nonce'),
										'ajaxurl'=>admin_url('admin-ajax.php'),
										'google'=>themify_get_google_web_fonts_list(),
										'cf'=>Themify_Custom_Fonts::get_list()
								));
								remove_filter( 'themify_google_fonts_full_list', '__return_true' );
							}
							$post_data = self::reSaveData($post_data);
							$GLOBALS['ThemifyBuilder_Data_Manager']->save_data($post_data, $id);
							wp_localize_script('tb_builder_js_style', 'themify_builder_data_'.$id, $post_data);
							$gs = Themify_Global_Styles::used_global_styles($id);
							if(!empty($gs)){
								wp_localize_script('tb_builder_js_style', 'themify_builder_gs_'.$id, $gs);
							}
							$gs=$post_data=null;
						}
					}
				}
			}
			else{
				setlocale( LC_CTYPE, get_locale() . '.UTF-8' );
				$handler = pathinfo( $stylesheet_path['url'] );
				$handlerId=$handler['filename'];
				$version = filemtime( $stylesheet_path['url'] );
				$url = self::get_stylesheet( 'byurl', $stylesheet_path['id'] );
				$url = themify_https_esc( $url['url'] );
				self::$isLoaded[$stylesheet_path['id']]=true;
				if( $return ) {
					return array(
					'handler' => $handlerId,
					'url' => $url . '?ver=' . $version
					);
				} else {
					global $ThemifyBuilder; 
					$isLoop = $ThemifyBuilder->in_the_loop===true && Themify_Enqueue_Assets::$isInTop===true && Themify_Enqueue_Assets::$isInline===false;
					
					$isHeader=$isLoop===false  && Themify_Enqueue_Assets::$isHeader===true && Themify_Enqueue_Assets::$isInline===false;
					if($isHeader===true && $isActive===false){
					    $isLoop=true;
					}
					$fonts = self::getFonts( $stylesheet_path['id'] );
					$link='';
					if ( !empty( $fonts ) ) {
						$href = themify_get_google_fonts_url( $fonts );
						$fonts = null;
						if ( $href !== false ) {
						    if($isLoop===true){
							$link= '<link class="themify-builder-generated-css" id="' . $handlerId . '-fonts" rel="stylesheet" href="' . $href . '" type="text/css" />';
						    }
						    else{
							if($isHeader===true && $isActive===false){
							    $link='<link rel="preload" href="'.$href.'" as="style"/>';
							}
							themify_enque_style( $handlerId.'-fonts', $href, null,null);
						    }
						}
					}

					// custom fonts
					$custom_fonts = Themify_Custom_Fonts::load_fonts( $stylesheet_path['id'] );
					if ( ! empty( $custom_fonts ) ) {
						echo '<style>' . $custom_fonts . '</style>' . PHP_EOL;
					}

					if($isLoop===true){
						if($isActive===true && $stylesheet_path['id']==Themify_Builder::$builder_active_id){
							$link.=self::get_active_style_tag($handlerId);
							remove_filter('style_loader_tag', array(__CLASS__, 'set_active_builder_style_position'), 15, 4);
						}
						else{
							$link.= '<link class="themify-builder-generated-css" id="' . $handlerId . '" rel="stylesheet" href="' . $url . '?ver=' . $version . '" type="text/css" />';
						}
					    
					}
					else{
						
						if($isActive===true && $stylesheet_path['id']==Themify_Builder::$builder_active_id){
							self::$activeBuilderHandler=$handlerId;
						}
						elseif($isHeader===true&& $isActive===false){
							$link.= '<link rel="preload" href="'.$url.'?ver='.$version.'" as="style"/>';
					    }
					    themify_enque_style( $handlerId, $url, null, $version);
					}
					echo $link;
				}
				return true;
			}
		}
		return false;
	}
	
	public static function set_active_builder_style_position($tag, $handle, $href, $media) {
		if($handle===self::$activeBuilderHandler){
			$tag = self::get_active_style_tag($handle);
			remove_filter('style_loader_tag', array(__CLASS__, 'set_active_builder_style_position'), 15, 4);
		}
		return $tag;
	}
	
	private static function get_active_style_tag($handle){
		return '<meta id="tb_active_style_'.Themify_Builder::$builder_active_id.'" name="'.$handle.'-css" content="'.$handle.'-css"/>';
	}
	
	/**
	* If post doesn't have generated css file,generate an unique Id for each component in the builder
	* 
	* @return array
	*/
	private  static function reSaveData($data){
	    foreach($data as &$r){
		if(isset($r['cid'])){
		    unset($r['cid']);
		}
		if(!isset($r['element_id'])){
		    $r['element_id'] = Themify_Builder_Model::generateID();
		}
		unset($r['row_order']);
		$r['element_id'] = self::checkUniqId($r['element_id']);
		if (!empty($r['cols'])) {
		    foreach($r['cols'] as &$c){
			if(!isset($c['element_id'])){
			    $c['element_id'] = Themify_Builder_Model::generateID();
			}
			unset($c['column_order']);
			$c['element_id'] = self::checkUniqId($c['element_id']);
			if(isset($c['cid'])){
			    unset($c['cid']);
			}
			if (!empty($c['modules'])) {
			    foreach($c['modules'] as &$m){
					if ( ! is_array( $m ) ) {
						continue;
					}
				if(!isset($m['element_id'])){
				    $m['element_id'] = Themify_Builder_Model::generateID();
				}
				unset($m['row_order']);
				$m['element_id'] = self::checkUniqId($m['element_id']);
				if(isset($m['mod_settings']['cid'])){
				    unset($m['mod_settings']['cid']);
				}
				if (!empty($m['cols'])) {
				    foreach ($m['cols'] as &$sub_col) {
					if(!isset($sub_col['element_id'])){
					    $sub_col['element_id'] = Themify_Builder_Model::generateID();
					}
					unset($sub_col['column_order']);
					$sub_col['element_id'] = self::checkUniqId($sub_col['element_id']);
					if(isset($sub_col['cid'])){
					    unset($sub_col['cid']);
					}
					if (!empty($sub_col['modules'])) {
					    foreach ($sub_col['modules'] as &$sub_m) {
						if(!isset($sub_m['element_id'])){
						    $sub_m['element_id'] = Themify_Builder_Model::generateID();
						}
						$sub_m['element_id'] = self::checkUniqId($sub_m['element_id']);
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
	    self::$ids = array();
	    return $data;
	}
	
	/**
	* Generate css file on the fly, if builder doesn't have style created an empty file,
	* which will help to detect there is no need to create css file.
	* @return void
	*/
	public static function save_builder_css(){
		check_ajax_referer('tb_load_nonce', 'tb_load_nonce');
		if(!empty($_POST['id'])){
			$filesystem = Themify_Filesystem::get_instance();
			$id = (int)$_POST['id'];
			$stylesheet_path = self::get_stylesheet( 'bydir',$id);
			if (!$filesystem->execute->is_file($stylesheet_path['url']) ) {
				$data = !empty($_POST['css'])?json_decode(stripslashes_deep($_POST['css']), true):array();
				if(!is_array($data)){
					$data = array();
				}
				if(!empty($data)){
					$res = self::write_stylesheet($id, $data);
				}
				if(empty($data) || empty($res['css_file'])){
					$filesystem->execute->put_contents(self::getTmpPath($stylesheet_path['url']), 'done', FS_CHMOD_FILE);
				}
			}
		}
	    die;
	}
	/*Return tmp path of original file*/
	public static function getTmpPath($path){
	    return str_replace('.css','-tmp.css',$path);
	}
	
	private static function checkUniqId($id){
	    while(isset(self::$ids[$id])){
		$id = Themify_Builder_Model::generateID();
	    }
	    self::$ids[$id] = true;
	    return $id;
	}

    /**
     * Write stylesheet file.
     * 
     * @since 2.2.5
     * 
     * @return array
     */
    public static function write_stylesheet($style_id,$data,$custom_css = '') {
		// Information about how writing went.
		$css='';
		$results = array();
		if(!empty($data)){
			$breakpoints = themify_get_breakpoints();
			$fonts = array( 'fonts' => array(), 'cf_fonts' => array() );
			$breakpoints = array('desktop'=>'')+$breakpoints;
			if(!empty($data['gs'])){
				$css .= '/* Builder GLOBAL CSS START */' . PHP_EOL;
				foreach($breakpoints as $b=>$bpoint){
					if(!empty($data['gs'][$b]) ){
						$styles='';
						foreach($data['gs'][$b] as  $selector=>$arr){
							$styles.=$selector.'{'.implode(' ',$arr).'}'.PHP_EOL;
						}
						if($b!=='desktop'){
							$max = is_array($bpoint)?$bpoint[1]:$bpoint;
							$styles=PHP_EOL.sprintf('@media screen and (max-width: %spx) {', $max).PHP_EOL.$styles.'}';
						}
						$css.=$styles;
					}
				}
				unset($data['gs']);
				$css.='/* Builder GLOBAL CSS END */'.PHP_EOL;
			}
			foreach($breakpoints as $b=>$bpoint){
				if(!empty($data[$b]) ){
					$styles='';
					foreach($data[$b] as  $selector=>$arr){
						$styles.=$selector.'{'.implode(' ',$arr).'}'.PHP_EOL;
					}
					if($b!=='desktop'){
						$max = is_array($bpoint)?$bpoint[1]:$bpoint;
						$styles=PHP_EOL.sprintf('@media screen and (max-width: %spx) {', $max).PHP_EOL.$styles.'}';
					}
					$css.=$styles;
					unset($data[$b]);
				}
			}
			foreach ( array_keys( $fonts ) as $ftype ) {
				if ( !empty( $data[ $ftype ] ) ) {
					foreach ( $data[ $ftype ] as $f => $w ) {
						$v = 'fonts' === $ftype ? str_replace( ' ', '+', $f ) : $f;
						if ( !empty( $w ) ) {
							$v .= ':' . implode( ',', $w );
						}
						$fonts[ $ftype ][] = $v;
					}
				}
			}
			$data=null;
		}
		$css_file = self::get_stylesheet('bydir', $style_id);
		$css_file = $css_file['url'];
		$tmp_path = self::getTmpPath($css_file);
		$filesystem = Themify_Filesystem::get_instance();
        if ($filesystem->execute->is_file($css_file)) {
            $filesystem->execute->delete($css_file);
        }
		if ($filesystem->execute->is_file($tmp_path)) {
            $filesystem->execute->delete($tmp_path);
        }
		if(!empty($custom_css)){
			$css .= '/* Builder Custom CSS START */' . PHP_EOL . $custom_css . PHP_EOL . '/* Builder Custom CSS END */';
		}
		if (!empty($css)) {
			$css = apply_filters( 'themify_builder_stylesheet_css', $css, $style_id, $css_file );
			$css = '/* Generated from '.get_post_type($style_id).': '.get_post_field( 'post_name', $style_id )." */\r\n".$css;
            $write = $filesystem->execute->put_contents($css_file, $css, FS_CHMOD_FILE);
            if($write) {
				// Add information about writing.
				$tmp = self::get_stylesheet( 'byurl', $style_id );
				$results['css_file'] = $tmp['url'];
				$results['write'] = $write;
				if(!empty($fonts)){
					// Save Fonts
					foreach ( array_keys( $fonts ) as $ftype ) {
						if ( !empty( $fonts[ $ftype ] ) ) {
							$fonts[ $ftype ] = implode( '|', $fonts[ $ftype ] );
							$option_key = 'fonts' === $ftype ? 'themify_builder_google_fonts' : 'themify_builder_cf_fonts';
							$builder_fonts = get_option( $option_key );
							if ( !is_array( $builder_fonts ) ) {
								$builder_fonts = array();
							}
							if ( isset( $builder_fonts[ $style_id ] ) ) {
								$builder_fonts[ $style_id ] = $fonts[ $ftype ];
								$entry_fonts = $builder_fonts;
							} else {
								$entry_fonts = array( $style_id => $fonts[ $ftype ] ) + $builder_fonts;
							}
							update_option( $option_key, $entry_fonts );
						}
					}
				}
			}else{
				$results['write'] = esc_html__('Styles can`t be written.Please check permission of uploading folder', 'themify');
			}
		} else {
			// Add information about writing.
			$results['write'] = esc_html__('Nothing written. Empty CSS.', 'themify');
		}
		return $results;
    }

    /**
     * Return the URL or the directory path for a template, template part or content builder styling stylesheet.
     * 
     * @since 2.2.5
     *
     * @param string $mode Whether to return the directory or the URL. Can be 'bydir' or 'byurl' correspondingly. 
     * @param int $single ID of layout, layour part or entry that we're working with.
     *
     * @return string
     */
    public static function get_stylesheet($mode = 'bydir', $single = null) {
        static $before = null;
        if ($before === null) {
            $upload_dir = wp_upload_dir();
            $before = array(
                'bydir' => $upload_dir['basedir'],
                'byurl' => $upload_dir['baseurl'],
            );
        }
      
        if ($single===null) {
            $single = Themify_Builder_Model::get_ID();
        }
        $single = is_int($single) ? get_post($single) : get_page_by_path($single, OBJECT, Themify_Builder_Layouts::$layout_part_slug);
        if (!is_object($single)) {
            return array('id'=>$single,'url'=>'');
        }
        $single = $single->ID;
        $path = "$before[$mode]/themify-css";
        if ('bydir' === $mode) {
            $filesystem = Themify_Filesystem::get_instance();
            if ( ! $filesystem->execute->is_dir($path) ) {
                wp_mkdir_p( $path);
            }
        }

        /**
         * Filters the return URL or directory path including the file name.
         *
         * @param string $stylesheet Path or URL for the global styling stylesheet.
         * @param string $mode What was being retrieved, 'bydir' or 'byurl'.
         * @param int $single ID of the template, template part or content builder that we're fetching.
         *
         */
        return array('id'=>$single,'url'=>apply_filters('themify_builder_get_stylesheet',  "$path/themify-builder-$single-generated.css", $mode, $single));
    }



    /**
     * Enqueues Google Fonts
     * 
     * @since 2.2.6
     */
    public static function getFonts($post_id=null) {
		if ( defined( 'THEMIFY_GOOGLE_FONTS' ) && THEMIFY_GOOGLE_FONTS == false ) {
			return array();
		}

        $entry_google_fonts = get_option('themify_builder_google_fonts');
	$google_fonts=array();
        if (!empty($entry_google_fonts) && is_array($entry_google_fonts)) {
            $entry_id = $post_id?$post_id:Themify_Builder_Model::get_ID();
            if (isset($entry_google_fonts[$entry_id])) {
                $fonts = explode('|', $entry_google_fonts[$entry_id]);
                foreach ($fonts as $font) {
		    if(!empty($font) && !in_array($font,self::$isLoadedFonts,true)){
			$google_fonts[] = $font;
			self::$isLoadedFonts[]=$font;
		    }
                }
            }
        }
        return $google_fonts;
    }


    /**
     * Checks whether a file exists, can be loaded and is not empty.
     * 
     * @since 2.2.5
     * 
     * @param string $file_path Path in server to the file to check.
     * 
     * @return bool
     */
    private static function is_readable_and_not_empty($file_path = '') {
        return empty($file_path)?false:is_readable($file_path) && 0 !== filesize($file_path);
    }


    public static function slider_live_styling() {
        check_ajax_referer('tb_load_nonce', 'nonce');
        $bg_slider_data = $_POST['tb_background_slider_data'];
        $row_or_col = array(
            'styling' => array(
                'background_slider' => urldecode($bg_slider_data['shortcode']),
                'background_type' => 'slider',
                'background_slider_mode' => $bg_slider_data['mode'],
                'background_slider_speed' => $bg_slider_data['speed'],
                'background_slider_size' => $bg_slider_data['size'],
            )
        );
        Themify_Builder_Component_Base::do_slider_background($row_or_col, $bg_slider_data['type']);
        wp_die();
    }
        
    /**
     * Converts color in hexadecimal format to RGB format.
     *
     * @since 1.9.6
     *
     * @param string $hex Color in hexadecimal format.
     * @return string Color in RGB components separated by comma.
     */
    private static function hex2rgb($hex) {
        $hex = str_replace('#', '', $hex);

        if (strlen($hex) === 3) {
            $r = substr($hex, 0, 1);
            $g = substr($hex, 1, 1);
            $b = substr($hex, 2, 1);
            $r = hexdec($r . $r);
            $g = hexdec($g . $g);
            $b = hexdec($b. $b);
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return implode(',', array($r, $g, $b));
    }

    /**
     * Get RGBA color format from hex color
     *
     * @return string
     */
    public static function get_rgba_color($color) {
        if (strpos($color, 'rgba') !== false) {
            return $color;
        }
        $color = explode('_', $color);
        $opacity = isset($color[1]) && $color[1] !== '' ? $color[1] : '1';
        return $opacity >= 0 && $opacity !== '1' && $opacity !== '1.00' && $opacity !== '0.99' ? 'rgba(' . self::hex2rgb($color[0]) . ', ' . $opacity . ')' : ($color[0] !== '' ? ('#' . str_replace('#', '', $color[0])) : false);
    }
    
}
