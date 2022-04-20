<?php
	if (!defined('ABSPATH'))
		exit; // Exit if accessed directly
	/**
	 * Module Name: Map
	 * Description: Display Map
	 */

	class TB_Map_Module extends Themify_Builder_Component_Module {

		function __construct() {
			parent::__construct(array(
				'name' => __('Map', 'themify'),
				'slug' => 'map'
			));
			add_filter( 'themify_builder_ajax_front_vars', array($this, 'check_map_api'));
			add_filter( 'themify_builder_ajax_admin_vars', array($this, 'check_map_api'));
		}

		/**
		 * Handles Ajax request to check map api
		 *
		 * @since 4.5.0
		 */
		function check_map_api($values) {
			$googleAPI = themify_builder_get( "setting-google_map_key" );
		    $values['google_api'] =  empty($googleAPI)?false:true;
			if(!$values['google_api']) {
			    $values['google_api_err'] = __('Please enter the required Google Maps API key.','themify');
			}
		    $bingAPI = themify_builder_get( "setting-bing_map_key" );
			$values['bing_api'] =  empty($bingAPI)?false:true;
			if(!$values['bing_api']) {
				$values['bing_api_err'] = __('Please enter the required Bing Maps API key.','themify');
			}
			return $values;
		}

		public function get_options() {
			$range = range(1,20);
			$map_key_setting =themify_is_themify_theme() ? admin_url('admin.php?page=themify#setting-google_map') : admin_url('admin.php?page=themify-builder&tab=builder_settings') ;
			return array(
				array(
					'id' => 'mod_title_map',
					'type' => 'title'
				),
				array(
					'id' => 'map_provider',
					'type' => 'radio',
					'label' => __('Map Provider', 'themify'),
					'options' => array(
						array('value' => 'google', 'name' => __('Google', 'themify')),
						array('value' => 'bing', 'name' => __('Bing', 'themify'))
					),
					'option_js' => true
				),
				array(
					'id' => 'map_display_type',
					'type' => 'radio',
					'label' => __('Type', 'themify'),
					'options' => array(
						array('value' => 'dynamic', 'name' => __('Dynamic', 'themify')),
						array('value' => 'static', 'name' => __('Static image', 'themify'))
					),
					'option_js' => true,
					'wrap_class' => 'tb_group_element_google'
				),
				array(
					'id' => 'address_map',
					'type' => 'address',
					'label' => __('Address', 'themify')
				),
				array(
					'id' => 'google_map_api_key',
					'type' => 'check_map_api',
					'map' => 'google',
					'wrap_class' => 'pushed tb_field_error_msg tb_group_element_google'
				),
				array(
					'id' => 'bing_map_api_key',
					'type' => 'check_map_api',
					'map' => 'bing',
					'wrap_class' => 'pushed tb_field_error_msg tb_group_element_bing'
				),
				array(
					'id' => 'latlong_map',
					'type' => 'text',
					'class' => 'large',
					'label' => __('Lat/Long', 'themify'),
					'help' => __('To use Lat/Long (eg. 43.6453137,-79.3918391) instead of address, leave address field empty.', 'themify')
				),
				array(
					'id' => 'bing_type_map',
					'type' => 'select',
					'label' => __('Type', 'themify'),
					'options' => array(
						'aerial' => __('Aerial', 'themify'),
						'road' => __('Road', 'themify'),
						'canvasDark' => __('Canvas Dark', 'themify'),
						'canvasLight' => __('Canvas Light', 'themify'),
						'grayscale' => __('Gray Scale', 'themify'),
					),
					'wrap_class' => 'tb_group_element_bing'
				),
				array(
					'id' => 'zoom_map',
					'type' => 'select',
					'label' => __('Zoom', 'themify'),
					'options' => array_combine($range,$range)
				),
				array(
					'id' => 'w_map',
					'type' => 'range',
					'class' => 'xsmall',
					'label' => 'w',
					'wrap_class' => 'tb_group_element_dynamic',
					'units' => array(
						'px' => array(
							'min' => 0,
							'max' => 2000
						),
						'%' => array(
							'min' => 0,
							'max' => 100
						)
					)
				),
				array(
					'id' => 'w_map_static',
					'type' => 'number',
					'label' => 'w',
					'after' => 'px',
					'wrap_class' => 'tb_group_element_static'
				),
				array(
					'id' => 'h_map',
					'type' => 'range',
					'label' => 'ht',
					'class' => 'xsmall',
					'units' => array(
						'px' => array(
							'min' => 0,
							'max' => 2000
						),
						'%' => array(
							'min' => 0,
							'max' => 100
						)
					)
				),
				array(
					'type' => 'multi',
					'label' => __('Border', 'themify'),
					'options' => array(
						array(
							'id' => 'b_style_map',
							'type' => 'select',
							'border' => true
						),
						array(
							'id' => 'b_color_map',
							'type' => 'color',
							'class' => 'large'
						),
						array(
							'id' => 'b_width_map',
							'type' => 'range',
							'class' => 'small',
							'after' => 'px'
						),
					)
				),
				array(
					'id' => 'type_map',
					'type' => 'select',
					'label' => __('Type', 'themify'),
					'options' => array(
						'ROADMAP' => __('Road Map', 'themify'),
						'SATELLITE' => __('Satellite', 'themify'),
						'HYBRID' => __('Hybrid', 'themify'),
						'TERRAIN' => __('Terrain', 'themify')
					),
					'wrap_class' => 'tb_group_element_google'
				),
				array(
					'id' => 'map_control',
					'type' => 'toggle_switch',
					'label' => __('Map Controls', 'themify'),
					'options' => 'simple',
					'wrap_class' => 'tb_group_element_dynamic'
				),
				array(
					'id' => 'draggable_map',
					'type' => 'toggle_switch',
					'label' => __('Draggable', 'themify'),
					'options' => array(
						'on' => array('name'=>'enable', 'value' =>'en'),
						'off' => array('name'=>'disable', 'value' =>'dis')
					),
					'wrap_class' => 'tb_group_element_dynamic'
				),
				array(
					'id' => 'scrollwheel_map',
					'type' => 'toggle_switch',
					'label' => __('Scrollwheel', 'themify'),
					'options' => array(
						'on' => array('name'=>'enable', 'value' =>'en'),
						'off' => array('name'=>'disable', 'value' =>'dis')
					),
					'wrap_class' => 'tb_group_element_dynamic'
				),
				array(
					'id' => 'draggable_disable_mobile_map',
					'type' => 'toggle_switch',
					'label' => __('Mobile Draggable', 'themify'),
					'options' => array(
						'on' => array('name'=>'no', 'value' =>'en'),
						'off' => array('name'=>'yes', 'value' =>'dis')
					),

					'wrap_class' => 'tb_group_element_dynamic'
				),
				array(
					'id' => 'info_window_map',
					'type' => 'textarea',
					'label' => __('Info Window', 'themify'),
					'help' => __('Additional info that will be shown when clicking on map marker', 'themify'),
					'wrap_class' => 'tb_group_element_dynamic'
				),
				array(
					'id' => 'css_map',
					'type' => 'custom_css'
				),
				array('type' => 'custom_css_id')
			);
		}

		public function get_default_settings() {
			return array(
				'address_map' => 'Toronto',
				'b_style_map' => 'solid',
				'map_control'=>'yes',
				'draggable_map'=>'enable',
				'w_map' => '100',
				'w_map_unit' => '%',
				'h_map_unit' => 'px',
				'zoom_map'=>'8',
				'w_map_static'=>'500',
				'h_map'=>'300'
			);
		}

		public function get_styling() {
			$general = array(
				// Background
				self::get_expand('bg', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_color('', 'background_color', 'bg_c', 'background-color'),
							)
						),
						'h' => array(
							'options' => array(
								self::get_color('', 'bg_c', 'bg_c', 'background-color', 'h'),
							)
						)
					))
				)),
				// Padding
				self::get_expand('p', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_padding()
							)
						),
						'h' => array(
							'options' => array(
								self::get_padding('', 'p', 'h')
							)
						)
					))
				)),
				// Margin
				self::get_expand('m', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_margin()
							)
						),
						'h' => array(
							'options' => array(
								self::get_margin('', 'm', 'h')
							)
						)
					))
				)),
				// Border
				self::get_expand('b', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_border()
							)
						),
						'h' => array(
							'options' => array(
								self::get_border('', 'b', 'h')
							)
						)
					))
				)),
				// Filter
				self::get_expand('f_l',
					array(
						self::get_tab(array(
							'n' => array(
								'options' => self::get_blend()

							),
							'h' => array(
								'options' => self::get_blend('', '', 'h')
							)
						))
					)
				),
				// Height & Min Height
				self::get_expand('ht', array(
						self::get_height(),
						self::get_min_height(),
						self::get_max_height()
					)
				),
				// Rounded Corners
				self::get_expand('r_c', array(
						self::get_tab(array(
							'n' => array(
								'options' => array(
									self::get_border_radius()
								)
							),
							'h' => array(
								'options' => array(
									self::get_border_radius('', 'r_c', 'h')
								)
							)
						))
					)
				),
				// Shadow
				self::get_expand('sh', array(
						self::get_tab(array(
							'n' => array(
								'options' => array(
									self::get_box_shadow()
								)
							),
							'h' => array(
								'options' => array(
									self::get_box_shadow('', 'sh', 'h')
								)
							)
						))
					)
				),
			);

			return array(
				'type' => 'tabs',
				'options' => array(
					'g' => array(
						'options' => $general
					),
					'm_t' => array(
						'options' => $this->module_title_custom_style()
					)
				)
			);
		}

		protected function _visual_template() {
			$module_args = self::get_module_args();
			$default_addres = sprintf('<b>%s</b><br/><p>#s#</p>', __('Address', 'themify'));
			?>
            <# 
            var w_unit = data.w_map_unit===undefined?'px':false,
            h_unit = data.w_map_unit===undefined?'px':false;
            _.defaults(data, {
		    'w_map_unit':'%',
		    'type_map':'ROADMAP',
		    'scrollwheel_map':'disable',
		    'draggable_map':'enable',
		    'map_control':'yes',
		    'draggable_disable_mobile_map':'yes',
		    'map_provider':'google'
            });
	    if(w_unit==='px' && data.unit_w === '%'){
		    w_unit='%';
	    }
            var address = data.address_map?data.address_map.trim().replace(/\s\s+/g, ' '):'',
            info = !data.info_window_map?'<?php echo $default_addres ?>'.replace('#s#',address):data.info_window_map;
            style = '';
            if(data.b_width_map){
            style+= 'border: '+data.b_style_map+' '+data.b_width_map+'px';
            if (data.b_color_map) {
            style+=' '+tb_app.Utils.toRGBA(data.b_color_map);
            }
            style+= ';';
            }
            #>

            <div class="module module-<?php echo $this->slug; ?> {{ data.css_map }}">
                <# if( data.mod_title_map ) { #>
				<?php echo $module_args['before_title']; ?>
                {{{ data.mod_title_map }}}
				<?php echo $module_args['after_title']; ?>
                <# }

                if( data.map_provider === 'google' && data.map_display_type==='static' ) {
                var args = 'key='+'<?php echo Themify_Builder_Model::getMapKey() ?>';
                if(address){
                args+='&center='+address;
                }
                else if(data.latlong_map){
                args+='&center='+data.latlong_map;
                }
                data.w_map_static=data.w_map_static+'';
                data.h_map=data.h_map+'';
                args+='&zoom='+data.zoom_map;
                args+='&maptype='+data.type_map.toLowerCase();
                args+='&size='+data.w_map_static.replace(/[^0-9]/,'')+'x'+data.h_map.replace(/[^0-9]/,'');
                #>
                <img style="{{ style }}" src="https://maps.googleapis.com/maps/api/staticmap?{{ args }}" />
                <#
                }
                else if( address || data.latlong_map ) {
                if(!w_unit){
					w_unit = data.w_map_unit;
                }
                if(!h_unit){
					h_unit = data.h_map_unit;
                }
                style+= 'width:'+data.w_map + w_unit+';';
                style+= 'height:'+data.h_map + h_unit+';';
                reverse = !address && data.latlong_map;
                address = address? address: data.latlong_map,
                scroll = data.scrollwheel_map === 'enable',
                drag = data.draggable_map === 'enable',
                control = data.map_control === 'no' ? 1 : 0,

                type= data.map_provider === 'google'?data.type_map:data.bing_type_map;
                if (drag && data.draggable_disable_mobile_map==='yes' && ThemifyBuilderModuleJs._isMobile()) {
                drag = false;
                }
                #>
                <div data-address="{{ address }}" data-control="{{control}}" data-zoom="{{ data.zoom_map }}" data-type="{{ type }}" data-scroll="{{ data.scroll }}" data-drag="{{ data.drag }}" class="<# print(data.map_provider === 'google'?'themify_map':'themify_bing_map')#> map-container"  style="{{ style }}"  data-info-window="{{ info }}" data-reverse-geocoding="{{ reverse }}"></div>
                <# } #>

            </div>
			<?php
		}


		/**
		 * Render plain content
		 */
		public function get_plain_content($module) {
			$mod_settings = wp_parse_args($module['mod_settings'], array(
				'mod_title_map' => '',
				'zoom_map' => 15
			));
			if (!empty($mod_settings['address_map'])) {
				$mod_settings['address_map'] = preg_replace('/\s+/', ' ', trim($mod_settings['address_map']));
			}
			$text = sprintf('<h3>%s</h3>', $mod_settings['mod_title_map']);
			$text .= sprintf(
				'<iframe frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="https://maps.google.com/maps?q=%s&amp;t=m&amp;z=%d&amp;output=embed&amp;iwloc=near"></iframe>', urlencode($mod_settings['address_map']), absint($mod_settings['zoom_map'])
			);
			return $text;
		}

	}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
	Themify_Builder_Model::register_module('TB_Map_Module');
