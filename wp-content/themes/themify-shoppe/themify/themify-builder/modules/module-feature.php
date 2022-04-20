<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Module Name: Feature
 * Description: Display Feature content
 */

class TB_Feature_Module extends Themify_Builder_Component_Module {

    function __construct() {
        self::$texts['title_feature'] = __('Feature Title', 'themify');
        self::$texts['content_feature'] = __('Feature content', 'themify');
        parent::__construct(array(
            'name' => __('Feature', 'themify'),
            'slug' => 'feature'
        ));
    }

    public function get_options() {
        return array(
            array(
                'id' => 'mod_title_feature',
                'type' => 'title'
            ),
            array(
                'id' => 'title_feature',
                'type' => 'text',
                'label' => self::$texts['title_feature'],
                'class' => 'large',
                'control' => array(
                    'selector' => '.module-feature-title a'
                )
            ),
            array(
                'id' => 'content_feature',
                'type' => 'wp_editor',
                'control' => array(
                    'selector' => '.tb_text_wrap'
                )
            ),
            array(
                'id' => 'layout_feature',
                'type' => 'layout',
                'label' => __('Layout', 'themify'),
                'mode' => 'sprite',
                'options' => array(
                    array('img' => 'icon_left', 'value' => 'icon-left', 'label' => __('Icon Left', 'themify')),
                    array('img' => 'icon_right', 'value' => 'icon-right', 'label' => __('Icon Right', 'themify')),
                    array('img' => 'icon_top', 'value' => 'icon-top', 'label' => __('Icon Top', 'themify'))
                )
            ),
            array(
                'type' => 'multi',
                'label' => __('Circle', 'themify'),
		'wrap_class'=>'multi_circle_feature',
                'options' => array(
                    array(
                        'id' => 'circle_percentage_feature',
                        'type' => 'range',
                        'label' => __('Percentage', 'themify'),
                        'class' => 'xsmall',
                        'units' => array(
                            '%' => array(
                                'min' => 0,
                                'max' => 100
                            )
                        )
                    ),
                    array(
                        'id' => 'circle_stroke_feature',
                        'type' => 'range',
                        'label' => __('Stroke', 'themify'),
                        'class' => 'xsmall',
                        'units' => array(
                            'px' => array(
                                'min' => 0,
                                'max' => 100
                            )
                        )
                    ),
                    array(
                        'id' => 'circle_color_feature',
                        'type' => 'color',
                        'label' => 'c'
                    ),
                    array(
                        'id' => 'circle_size_feature',
                        'type' => 'select',
                        'label' => __('Size', 'themify'),
                        'options' => array(
                            'small' => __('Small', 'themify'),
                            'medium' => __('Medium', 'themify'),
                            'large' => __('Large', 'themify'),
                            'custom' => __('Custom', 'themify')
                        ),
                        'binding' => array(
                            'small' => array('hide' => array('custom_circle_size_feature')),
                            'medium' => array('hide' => array('custom_circle_size_feature')),
                            'large' => array('hide' => array('custom_circle_size_feature')),
                            'custom' => array('show' => array('custom_circle_size_feature')),
                        )
                    ),
                    array(
                        'id' => 'custom_circle_size_feature',
                        'type' => 'text',
                        'label' => __('Circle size(px)', 'themify')
                    ),
                )
            ),
            array(
                'id' => 'icon_type_feature',
                'type' => 'radio',
                'label' => __('Icon Type', 'themify'),
                'options' => array(
		    array('value'=>'icon','name'=>__('Icon', 'themify')),
		    array('value'=>'image_icon','name'=>__('Image', 'themify'))
                ),
                'option_js' => true
            ),
            array(
                'id' => 'image_feature',
                'type' => 'image',
                'label' => __('Image URL', 'themify'),
                'wrap_class' => 'tb_group_element_image_icon'
            ),
            array(
                'type' => 'multi',
                'label' => '',
                'options' => array(
                    array(
                        'id' => 'icon_feature',
                        'type' => 'icon',
                        'label' => __('Icon', 'themify'),
                        'wrap_class' => 'tb_group_element_icon'
                    ),
                    array(
                        'id' => 'icon_color_feature',
                        'type' => 'color',
                        'label' => 'c',
                        'class' => 'medium',
                        'wrap_class' => 'tb_group_element_icon'
                    ),
                    array(
                        'id' => 'icon_bg_feature',
                        'type' => 'color',
                        'label' => 'bg',
                        'class' => 'medium',
                        'wrap_class' => 'tb_group_element_icon'
                    ),
                )
            ),
            array(
                'id' => 'link_feature',
                'type' => 'url',
                'label' => __('Link', 'themify'),
                'class' => 'fullwidth',
                'binding' => array(
                    'empty' => array(
                        'hide' => array('link_options', 'lightbox_size','feature_download_link')
                    ),
                    'not_empty' => array(
                        'show' => array('link_options', 'lightbox_size','feature_download_link')
                    )
                )
            ),
            array(
                'id' => 'feature_download_link',
                'type' => 'checkbox',
                'label' => __('Download-able Link', 'themify'),
                'options' => array(
                    array('name' => 'yes', 'value' => __('Download link as file', 'themify'))
                )
            ),
            array(
                'id' => 'link_options',
                'type' => 'radio',
                'label' => 'o_l',
                'link_type' => true,
		'wrap_class' => 'tb_compact_radios',
                'option_js' => true
            ),
            array(
                'type' => 'multi',
                'label' => __('Lightbox Dimension', 'themify'),
                'options' => array(
                    array(
                        'id' => 'lightbox_width',
                        'label' => 'w', 
                        'type' => 'range',
                        'control'=>false,
                        'units' => array(
                            'px' => array(
                                'min' => 0,
                                'max' => 500
                            ),
                            'em' => array(
                                'min' => -10,
                                'max' => 10
                            ),
                            '%' => array(
                                'min' => 0,
                                'max' => 100
                            )
                        )
                    ),
                    array(
                        'id' => 'lightbox_height',
                        'label' => 'ht',
                        'type' => 'range',
                        'control'=>false,
                        'units' => array(
                            'px' => array(
                                'min' => 0,
                                'max' => 500
                            ),
                            'em' => array(
                                'min' => -10,
                                'max' => 10
                            ),
                            '%' => array(
                                'min' => 0,
                                'max' => 100
                            )
                        )
                    )
                ),
                'wrap_class' => 'tb_group_element_lightbox lightbox_size'
            ),
            array(
                'id' => 'overlap_image_feature',
                'type' => 'image',
                'label' => __('Overlap Image', 'themify'),
                'binding' => array(
                    'empty' => array('hide' => array('overlap_image_width','overlap_image_height')),
                    'not_empty' => array('show' => array('overlap_image_width','overlap_image_height'))
                )
            ),
            array(
                'type' => 'multi',
                'label' => '',
                'options' => array(
                    array(
                        'id' => 'overlap_image_width',
                        'type' => 'text',
                        'label' => 'w'
                    ),
                    array(
                        'id' => 'overlap_image_height',
                        'type' => 'text',
                        'label' => 'ht'
                    )
                ),
            ),
            array(
                'id' => 'css_feature',
                'type' => 'custom_css'
            ),
            array('type'=>'custom_css_id')
        );
    }

    public function get_default_settings() {
        return array(
            'title_feature' => self::$texts['title_feature'],
            'content_feature' => self::$texts['content_feature'],
            'circle_percentage_feature' => '100',
            'circle_stroke_feature' => '3',
            'icon_feature' => 'fa-home',
            'layout_feature' => 'icon-top',
            'circle_size_feature' => 'small',
	    'icon_type_feature'=>'icon',
            'circle_color_feature' => '#de5d5d'
        );
    }

    public function get_styling() {
        $general = array(
            // Background
            self::get_expand('bg', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_image()
                    )
                ),
                'h' => array(
                    'options' => array(
                         self::get_image('', 'b_i','bg_c','b_r','b_p', 'h')
                    )
                )
            ))
		)),
            // Font
            self::get_expand('f', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_font_family(array(' .tb_text_wrap',' h3.module-feature-title')),
                        self::get_color_type(array(' .tb_text_wrap', ' h3.module-feature-title')),
                        self::get_font_size(),
                        self::get_line_height(),
                        self::get_letter_spacing(),
                        self::get_text_align(array('',' .module-feature-content')),
                        self::get_text_transform(),
                        self::get_font_style(),
                        self::get_text_decoration('', 'text_decoration_regular'),
			self::get_text_shadow(array(' .tb_text_wrap',' .module-feature-title')),
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_font_family(array(':hover .tb_text_wrap',':hover h3.module-feature-title'), 'f_f_h'),
                        self::get_color_type(array(':hover .tb_text_wrap', ':hover h3.module-feature-title'),'', 'f_c_t_h', 'f_c_h', 'f_g_c_h'),
                        self::get_font_size('', 'f_s', '', 'h'),
                        self::get_line_height('', 'l_h', 'h'),
                        self::get_letter_spacing('', 'l_s', 'h'),
                        self::get_text_align(array('',' .module-feature-content'), 't_a', 'h'),
                        self::get_text_transform('', 't_t', 'h'),
                        self::get_font_style('', 'f_st', 'f_w', 'h'),
                        self::get_text_decoration('', 't_d_r', 'h'),
			self::get_text_shadow(array(' .tb_text_wrap',' .module-feature-title'),'t_sh','h'),
                    )
                )
            ))
		)),
            // Link
            self::get_expand('l', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_color('.module a', 'link_color'),
                        self::get_text_decoration(' a')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_color('.module a:hover', 'link_color_hover'),
                        self::get_text_decoration(' a', 't_d', 'h')
                    )
                )
            ))
		)),
            // Padding
            self::get_expand('p', array(
            self::get_tab( array(
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

        $feature_title = array(
            // Font
            self::get_seperator('f'),
            self::get_tab(array(
                'n' => array(        
                    'options' => array(
                        self::get_font_family(array('.module .module-feature-title', '.module .module-feature-title a'), 'font_family_title'),
                        self::get_color(array('.module h3.module-feature-title', '.module h3.module-feature-title a'), 'font_color_title'),
                        self::get_font_size('.module .module-feature-title', 'font_size_title'),
                        self::get_line_height('.module .module-feature-title', 'line_height_title'),
                        self::get_letter_spacing('.module .module-feature-title', 'l_s_t'),
                        self::get_text_transform('.module .module-feature-title', 't_t_t'),
                        self::get_font_style('.module .module-feature-title', 'f_s_t', 'f_t_b'),
                        self::get_text_shadow('.module .module-feature-title', 't_sh_t')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_font_family(array('.module .module-feature-title', '.module .module-feature-title a'), 'f_f_t', 'h'),
						self::get_color(array('.module h3.module-feature-title', '.module h3.module-feature-title a'), 'font_color_title',  null, null, 'hover'),
                        self::get_font_size('.module .module-feature-title', 'f_s_t', '', 'h'),
                        self::get_line_height('.module .module-feature-title', 'l_h_t', 'h'),
                        self::get_letter_spacing('.module .module-feature-title', 'l_s_t', 'h'),
                        self::get_text_transform('.module .module-feature-title', 't_t_t', 'h'),
                        self::get_font_style('.module .module-feature-title', 'f_st_t', 'f_t_b', 'h'),
						self::get_text_shadow('.module .module-feature-title', 't_sh_t','h')
                    )
                )
            ))
        );

        $feature_content = array(
            // Font
            self::get_expand('f', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_font_family(array('.module .tb_text_wrap', ' .module-feature-content a'), 'font_family_content'),
                        self::get_color(array('.module .tb_text_wrap','.module .module-feature-content a','.module .module-feature-title'), 'f_c_c'),
                        self::get_font_size(array('.module .tb_text_wrap', ' .module-feature-content a'), 'f_s_c'),
                        self::get_line_height(array('.module .tb_text_wrap', ' .module-feature-content a'), 'l_h_c'),
                        self::get_letter_spacing(array('.module .tb_text_wrap', ' .module-feature-content a'), 'l_s_c'),
                        self::get_text_shadow(array('.module .tb_text_wrap', ' .module-feature-content a'), 't_sh_c')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_font_family(array('.module .tb_text_wrap', ' .module-feature-content a'), 'f_f_c', 'h'),
                        self::get_color(array('.module .tb_text_wrap','.module .module-feature-content a','.module .module-feature-title'), 'f_c_c', null, null, 'h'),
                        self::get_font_size(array('.module .tb_text_wrap', ' .module-feature-content a'), 'f_s_c', '', 'h'),
                        self::get_line_height(array('.module .tb_text_wrap', ' .module-feature-content a'), 'l_h_c', 'h'),
                        self::get_letter_spacing(array('.module .tb_text_wrap', ' .module-feature-content a'), 'l_s_c', 'h'),
			self::get_text_shadow(array('.module .tb_text_wrap', ' .module-feature-content a'), 't_sh_c','h')
                    )
                )
            ))
		)),
            // Padding
            self::get_expand('p', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_padding(' .module-feature-content', 'c_p')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_padding(' .module-feature-content', 'c_p', 'h')
                    )
                )
            ))
		)),
            // Margin
            self::get_expand('m', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_margin(' .module-feature-content', 'c_m')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_margin(' .module-feature-content', 'c_m', 'h')
                    )
                )
            ))
		)),
            // Border
            self::get_expand('b', array(
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_border(' .module-feature-content', 'c_b')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_border(' .module-feature-content', 'c_b', 'h')
                    )
                )
            ))
		))
        );

        $featured_icon = array(
            // Font
            self::get_seperator('f'),
            self::get_tab(array(
                'n' => array(
                    'options' => array(
                        self::get_font_size(' .module-feature-icon', 'f_s_i')
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_font_size(' .module-feature-icon', 'f_s_i', '', 'h')
                    )
                )
            ))
        );

        return array(
            'type' => 'tabs',
            'options' => array(
                'g' => array(
                    'options' => $general
                ),
                'm_t' => array(
                    'options' => $this->module_title_custom_style()
                ),
                't' => array(
                    'label' => __('Feature Title', 'themify'),
                    'options' => $feature_title
                ),
                'f' => array(
                    'label' => __('Feature Icon', 'themify'),
                    'options' => $featured_icon
                ),
                'c' => array(
                    'label' => __('Feature Content', 'themify'),
                    'options' => $feature_content
                )
            )
        );
    }

    protected function _visual_template() {
        $module_args = self::get_module_args();
        $chart_vars = apply_filters('themify_chart_init_vars', array(
            'trackColor' => 'rgba(0,0,0,.1)',
            'size' => 150
        ));
        ?>
        <#
        var chart_vars = JSON.parse('<?php echo json_encode($chart_vars) ?>');
        _.defaults(data, {
	    icon_feature:'',
	    icon_bg_feature:''
        });
        if (!data.layout_feature) {
	    data.layout_feature = 'icon-top';
        }
        var chart_class = '';
        if (data.circle_size_feature === 'large') {
	    chart_vars.size = 200;
        } else if (data.circle_size_feature === 'medium') {
	    chart_vars.size = 150;
        } else if (data.circle_size_feature === 'small') {
	    chart_vars.size = 100;
        }else if (data.circle_size_feature === 'custom'){
	    chart_vars.size = data.custom_circle_size_feature;
        }
        data.circle_percentage_feature = data.circle_percentage_feature?data.circle_percentage_feature.replace('%',''):'';
        if(!data.circle_percentage_feature){
	    chart_class='no-chart';
	    data.circle_percentage_feature = 0;
	    chart_vars.trackColor = 'rgba(0,0,0,0)';
        }
        else{
	    if(data.circle_percentage_feature>100){
		data.circle_percentage_feature = '100';
	    }
	    chart_class = 'with-chart';
	}
        if(data.overlap_image_feature){
	    chart_class+=' with-overlay-image';
        }
        var module_id = '.tb_element_cid_'+data.cid,
        style = '',
        insetColor = data.icon_bg_feature!==''?tb_app.Utils.toRGBA(data.icon_bg_feature):'';
        if(data.circle_stroke_feature){
	    var circleBackground = chart_vars.trackColor,
	    circleColor = tb_app.Utils.toRGBA(data.circle_color_feature),
	    insetSize =  parseInt(data.circle_stroke_feature),
	    style=module_id+' .module-feature-chart-html5{box-shadow: inset 0 0 0 '+insetSize+'px '+circleBackground+';}';
	    style+=module_id+' .chart-loaded.chart-html5-fill{box-shadow: inset 0 0 0 '+insetSize+'px '+circleColor+';}';
        }
	    if(insetColor!==''){
		style+=module_id+' .chart-html5-inset{background-color:'+insetColor+';}';
	    }
        #>
        <div class="module module-<?php echo $this->slug; ?> {{chart_class}} layout-{{ data.layout_feature }} size-{{data.circle_size_feature}} {{ data.css_feature }}">
            <style type="text/css">
                {{style}}
            </style> 
            <# if( data.mod_title_feature ) { #>
            <?php echo $module_args['before_title']; ?>
            {{{ data.mod_title_feature }}}
            <?php echo $module_args['after_title']; ?>
            <# } #>

            <div class="module-feature-image">
                <# if(data.overlap_image_feature){
                var style = 'width:' + ( data.overlap_image_width ? data.overlap_image_width + 'px;' : 'auto;' );
                style += 'height:' + ( data.overlap_image_height ? data.overlap_image_height + 'px;' : 'auto;' );
                #>
                <img src="{{data.overlap_image_feature}}" style="{{style}}"/>
                <#}
                if(data.link_feature){ #>
                <a href="{{ data.link_feature }}">
                    <#}#>
                    <div class="module-feature-chart-html5"
                         <# if(data.circle_percentage_feature){ #>
                         data-progress="0"
                         data-progress-end="{{data.circle_percentage_feature}}"
                         data-size="{{chart_vars.size}}"
                         <#}#>>
                         <div class="chart-html5-circle">
                            <# if(data.circle_percentage_feature){ #>
                            <div class="chart-html5-mask chart-html5-full">
                                <div class="chart-html5-fill"></div>
                            </div>
                            <div class="chart-html5-mask chart-html5-half">
                                <div class="chart-html5-fill"></div>
                            </div>
                            <#}#>
                            <div class="chart-html5-inset<# if(data.icon_type_feature==='icon' && data.icon_feature!==''){ #> chart-html5-inset-icon<# } #>">
                                <# if (data.icon_type_feature && data.icon_type_feature.indexOf('image')!==-1){ #>
				    <# if(data.image_feature){ #>
					<img src="{{data.image_feature}}" />
				    <# } #>
                                <# }
                                else{ 
                                if ('' !== insetColor){ #><div class="module-feature-background" style="background:{{insetColor}}"></div><# }
                                if ('' !== data.icon_feature){ #><i class="module-feature-icon {{tb_app.Utils.getIcon(data.icon_feature)}}"<# if(data.icon_color_feature!==''){ #> style="color:<# print(tb_app.Utils.toRGBA(data.icon_color_feature)) #>"<# } #>></i><# } 
                                } #>
                            </div>
                        </div>
                    </div>
                    <# if(data.link_feature){ #>
                </a>
                <# } #>
            </div>
            <div class="module-feature-content">
                <# if(data.title_feature!==''){ #>
                <h3 class="module-feature-title"<# if(!data.link_feature){#> data-name="title_feature" contenteditable="false"<#}#>>
                    <# if(data.link_feature){ #>
                    <a href="{{data.link_feature}}" contenteditable="false" data-name="title_feature" href="{{data.link_feature}}">
                        <#}#>
                        {{{data.title_feature}}}
                        <# if(data.link_feature){ #>
                    </a>
                    <#}#>
                </h3>
                <# } #>
		<div contenteditable="false" data-name="content_feature" class="tb_text_wrap tb_editor_enable">
		    {{{ data.content_feature }}}
		</div>
            </div>
        </div>
        <?php
    }

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module('TB_Feature_Module');
