<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Module Name: Box
 * Description: Display box content
 */

class TB_Box_Module extends Themify_Builder_Component_Module {

    function __construct() {
        self::$texts['content_box'] = __('Box content', 'themify');
        parent::__construct(array(
            'name' => __('Box', 'themify'),
            'slug' => 'box'
        ));
    }

    public function get_options() {
        return array(
            array(
                'id' => 'mod_title_box',
                'type' => 'title'
            ),
            array(
                'id' => 'content_box',
                'type' => 'wp_editor',
		'control' => array(
		    'selector' => '.tb_text_wrap'
		)
            ),
            array(
                'id' => 'color_box',
                'type' => 'layout',
                'mode' => 'sprite',
                'class' => 'tb_colors',
                'label' => __('Color', 'themify'),
                'color' => true
            ),
            array(
                'id' => 'appearance_box',
                'type' => 'checkbox',
                'label' => __('Appearance', 'themify'),
                'appearance' => true
            ),
            array(
                'id' => 'add_css_box',
                'type' => 'custom_css'
            ),
            array('type'=>'custom_css_id')
        );
    }

    public function get_default_settings() {
        return array(
            'content_box' => '<p>'.self::$texts['content_box'].'</p>'
        );
    }

    public function get_styling() {
        $general = array(
            //bacground
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_image(' .ui')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_image(' .ui', 'b_i','bg_c','b_r','b_p', 'h')
			)
		    )
		))
	    )),
            // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .tb_text_wrap'),
			    self::get_color_type(' .tb_text_wrap'),
			    self::get_font_size(),
			    self::get_line_height(),
			    self::get_letter_spacing(),
			    self::get_text_align(),
			    self::get_text_transform(),
			    self::get_font_style(),
			    self::get_text_decoration(' .tb_text_wrap', 'text_decoration_regular'),
			    self::get_text_shadow(' .tb_text_wrap'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(':hover .tb_text_wrap', 'f_f_h'),
			    self::get_color_type(':hover .tb_text_wrap','','f_c_t_h','f_c_h', 'f_g_c_h'),
			    self::get_font_size('', 'f_s', '', 'h'),
			    self::get_line_height('', 'l_h', 'h'),
			    self::get_letter_spacing('', 'l_s', 'h'),
			    self::get_text_align('', 't_a', 'h'),
			    self::get_text_transform('', 't_t', 'h'),
			    self::get_font_style('', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration(' .tb_text_wrap', 't_d_r', 'h'),
			    self::get_text_shadow(' .tb_text_wrap', 't_sh', 'h')
			)
		    )
		))
	    )),
            // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .ui a', 'link_color'),
			    self::get_text_decoration(' a')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .ui a', 'link_color',null, null, 'hover'),
			    self::get_text_decoration(' a', 't_d', 'h')
			)
		    )
		))
	    )),
            // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .ui')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .ui', 'p', 'h')
			)
		    )
		))
	    )),
            // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin('')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin('', 'm', 'h'),
			)
		    )
		))
	    )),
            // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .ui')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .ui', 'b', 'h')
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
						self::get_height(' .module-box-content'),
						self::get_min_height(' .module-box-content'),
						self::get_max_height(' .module-box-content')
					)
				),
			// Rounded Corners
			self::get_expand('r_c', array(
					self::get_tab(array(
						'n' => array(
							'options' => array(
								self::get_border_radius(' .module-box-content')
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius(' .module-box-content', 'r_c', 'h')
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
								self::get_box_shadow(' .module-box-content')
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow(' .module-box-content', 'sh', 'h')
							)
						)
					))
				)
			),
        );

        $heading = array();
        for ($i = 1; $i <= 6; ++$i) {
            $h = 'h' . $i;
	    $selector = $h;
	    if($i === 3){
		$selector.=':not(.module-title)';
	    }
            $heading = array_merge($heading, array(
		self::get_expand(sprintf(__('Heading %s Font', 'themify'), $i), array(
                self::get_tab(array(
                    'n' => array(
                        'options' => array(
                            self::get_font_family('.module .tb_text_wrap ' . $selector, 'font_family_' . $h),
                            self::get_color_type('.module .tb_text_wrap ' .$selector,'','font_color_type_' . $h, 'font_color_' . $h, 'font_gradient_color_' . $h),
                            self::get_font_size('.module ' . $h, 'font_size_' . $h),
                            self::get_line_height('.module ' . $h, 'line_height_' . $h),
                            self::get_letter_spacing('.module ' . $h, 'letter_spacing_' . $h),
                            self::get_text_transform('.module ' . $h, 'text_transform_' . $h),
                            self::get_font_style('.module ' . $h, 'font_style_' . $h, 'font_weight_' . $h),
                            self::get_text_shadow('.module ' . $h, 't_sh_' . $h),
                            // Heading  Margin
                            self::get_heading_margin_multi_field('.module ', $h, 'top'),
                            self::get_heading_margin_multi_field('.module ', $h, 'bottom')
                        )
                    ),
                    'h' => array(
                        'options' => array(
                            self::get_font_family('.module:hover .tb_text_wrap ' . $selector, 'f_f_' . $h.'_h'),
			                self::get_color_type('.module:hover .tb_text_wrap ' .$selector,'','f_c_t_' . $h.'_h',   'f_c_' . $h.'_h', 'f_g_c_' . $h.'_h'),
                            self::get_font_size('.module:hover ' . $h, 'f_s_' . $h.'_h'),
                            self::get_line_height('.module:hover ' . $h, 'l_h_' . $h.'_h'),
                            self::get_letter_spacing('.module:hover ' . $h, 'l_s_' . $h.'_h'),
                            self::get_text_transform('.module:hover ' . $h, 't_t_' . $h.'_h'),
                            self::get_font_style('.module:hover ' . $h, 'f_st_' . $h.'_h', 'f_w_' . $h.'_h'),
                            self::get_text_shadow('.module:hover ' . $h, 't_sh_' . $h.'_h','h'),
                            // Heading  Margin
                            self::get_heading_margin_multi_field('.module', $h, 'top','h'),
                            self::get_heading_margin_multi_field('.module', $h, 'bottom','h')
                        )
                    )
		    ))
		))
		)
            );
        }

        return array(
            'type' => 'tabs',
            'options' => array(
                'g' => array(
                    'options' => $general
                ),
                'head' => array(
                    'options' => $heading
                )
            )
        );
    }

    protected function _visual_template() {
        $module_args = self::get_module_args();
        ?>
        <div class="module module-<?php echo $this->slug; ?>">
            <# if ( data.mod_title_box ) { #>
            <?php echo $module_args['before_title']; ?>{{{ data.mod_title_box }}}<?php echo $module_args['after_title']; ?>
            <# } #>
            <div class="ui module-<?php echo $this->slug; ?>-content <# data.color_box && data.color_box!=='default' ? print( data.color_box ):print('tb_default_color'); #> {{ data.add_css_box }} <# data.appearance_box ? print( data.appearance_box.split('|').join(' ') ) : ''; #>">
                <div contenteditable="false" data-name="content_box" class="tb_editor_enable tb_text_wrap">{{{ data.content_box }}}</div>
            </div>
        </div>
        <?php
    }

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module('TB_Box_Module');
