<?php

class Themify_Builder_Component_Base {

    /**
     * The original post id
     */
    public static $post_id = false;

    /**
     * The layout_part_id
     */
    public static $layout_part_id = false;

    /**
     * Array of classnames to add to post objects
     */
    private static $_post_classes = array();

    public static $draggableModules = array();

    /**
     * The names of settings for tooltip.
     * 
     * @access public
     */
    protected static $texts = array();

    public function __construct() {
	
    }

    public function get_type() {
	return 'component';
    }

    protected function get_animation() {
	return true;
    }

    public function get_name() {
	
    }

    public final function get_class_name() {
	return get_class($this);
    }

    public static function get_tab(array $options) {
	return array(
	    'type' => 'tabs',
	    'options' => $options
	);
    }

    public function get_styling() {
	$type = $this->get_name();
	$margin_fields = array(
	    'margin' => self::get_margin()
	);
	$margin_hover_fields = array(
	    'margin' => self::get_margin('', 'm', 'h')
	);
	$inner = __('Inner Container', 'themify');
	if ($type === 'row' || $type === 'column') {
		$margin_fields['margin_top']=self::get_margin_top_bottom_opposity('','margin-top','margin-bottom');
		$margin_hover_fields['margin_top']=self::get_margin_top_bottom_opposity('','m_t','m_b','h');
	    
	    if($type === 'row'){
		$overlay = __('Row Overlay', 'themify');
		$inner_selector = 'row_inner';
	    }
	    else{
		$overlay = __('Column Overlay', 'themify');
		$inner_selector = 'tb-column-inner';
	    }
	    unset($margin_fields['margin'], $margin_hover_fields['margin']);
	} else {
	    $overlay = __('Subrow Overlay', 'themify');
	    $inner_selector = 'subrow_inner';
	}

	$inner_selector = array('>div.' . $inner_selector);

	if ($type === 'column') {
	    $inner_selector[] = '>.tb_holder';
	}

	// Image size
	$inner_selector_hover = array();
	foreach ($inner_selector as $item) {
	    $inner_selector_hover[] = $item . ':hover';
	}
	$options = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    array(
				'id' => 'background_type',
				'label' => __('Background Type', 'themify'),
				'type' => 'radio',
				'options' => array(
				    array('value' => 'image', 'name' => __('Image', 'themify')),
				    array('value' => 'gradient', 'name' => __('Gradient', 'themify')),
				    array('value' => 'video', 'name' => __('Video', 'themify'), 'class' => 'responsive_disable'),
				    array('value' => 'slider', 'name' => __('Slider', 'themify'), 'class' => 'responsive_disable')
				),
				'is_background' => true,
				'wrap_class' => 'tb_compact_radios',
				'option_js' => true
			    ),
			    array(
				'type'=>'group',
				'wrap_class' => 'tb_group_element_slider',
				'options'=>array(
				    // Background Slider
				    array(
					'id' => 'background_slider',
					'type' => 'gallery',
					'label' => __('Background Slider', 'themify'),
					'is_responsive' => false
				    ),
				    // Background Slider Image Size
				    array(
					'id' => 'background_slider_size',
					'label' => '',
					'after' => __('Image Size', 'themify'),
					'type' => 'select',
					'image_size' => true,
					'default' => 'large',
					'is_responsive' => false
				    ),
				    // Background Slider Mode
				    array(
					'id' => 'background_slider_mode',
					'label' => '',
					'after' => __('Background Slider Mode', 'themify'),
					'type' => 'select',
					'options' => array(
					    'fullcover' => __('Fullcover', 'themify'),
					    'best-fit' => __('Best Fit', 'themify'),
					    'kenburns-effect' => __('Ken Burns Effect', 'themify')
					),
					'is_responsive' => false
				    ),
				    array(
					'id' => 'background_slider_speed',
					'label' => '',
					'after' => __('Slider Speed', 'themify'),
					'type' => 'select',
					'default' => '2000',
					'options' => array(
					    '3500' => __('Slow', 'themify'),
					    '2000' => __('Normal', 'themify'),
					    '500' => __('Fast', 'themify')
					),
					'is_responsive' => false
				    )
				)
			    ),
			    // Video Background
			    array(
				'id' => 'background_video',
				'type' => 'video',
				'label' => __('Background Video', 'themify'),
				'help' => __('Video format: mp4, YouTube, and Vimeo. Note: video background does not play on some mobile devices, background image will be used as fallback. Audio should be disabled to have auto play.', 'themify'),
				'class' => 'xlarge',
				'is_responsive' => false,
				'wrap_class' => 'tb_group_element_video'
			    ),
			    array(
				'id' => 'background_video_options',
				'type' => 'checkbox',
				'label' => '',
				'options' => array(
				    array('name' => 'unloop', 'value' => __('Disable looping', 'themify')),
										array('name' => 'mute', 'value' => __('Disable audio', 'themify'),'help'=>__('Audio must be disabled in order to auto play in most browsers.','themify')),
										array('name' => 'playonmobile', 'value' => __('Mobile support', 'themify'),'help'=>__('Video must be mp4 format (YouTube or Vimeo video is not supported).','themify'))
				),
				'default' => 'mute',
				'is_responsive' => false,
				'wrap_class' => 'tb_group_element_video'
			    ),
			    // Background Image
			    array(
				'id' => 'background_image',
				'type' => 'image',
				'label' => 'b_i',
				'class' => 'xlarge',
				'wrap_class' => 'tb_group_element_image tb_group_element_video',
				'prop' => 'background-image',
				'selector' => '',
				'binding' => array(
				    'empty' => array(
					'hide' => array('tb_image_options')
				    ),
				    'not_empty' => array(
					'show' => array('tb_image_options')
				    )
				)
			    ),
			    array(
				'id' => 'background_gradient',
				'type' => 'gradient',
				'label' => '',
				'class' => 'xlarge',
				'wrap_class' => 'tb_group_element_gradient',
				'prop' => 'background-image',
				'selector' => ''
			    ),
			    array(
				    'type'=>'group',
				    'wrap_class' => 'tb_group_element_image tb_image_options',
				    'options'=>array(
					// Background repeat
					array(
					    'id' => 'background_repeat',
					    'label' => '',
					    'type' => 'select',
					    'after' => __('Background Mode', 'themify'),
					    'prop' => 'background-mode',
					    'origId'=>'background_image',
					    'selector' => '',
					    'options' => array(
						'repeat' => __('Repeat All', 'themify'),
						'repeat-x' => __('Repeat Horizontally', 'themify'),
						'repeat-y' => __('Repeat Vertically', 'themify'),
						'repeat-none' => __('Do not repeat', 'themify'),
						'fullcover' => __('Fullcover', 'themify'),
						'best-fit-image' => __('Best Fit', 'themify'),
						'builder-parallax-scrolling' => __('Parallax Scrolling', 'themify'),
						'builder-zoom-scrolling' => __('Zoom Scrolling', 'themify'),
						'builder-zooming' => __('Zooming', 'themify')
					    ),

					    'binding' => array(
						'repeat-none' => array(
						    'show' => array('background_zoom','background_position')
						),
						'builder-parallax-scrolling' => array(
						    'hide' => array('background_attachment','background_position','background_zoom'),
						    'responsive' => array(
							'disabled' => array('background_repeat')
						    )
						),
						'builder-zoom-scrolling' => array(
						    'hide' => array('background_attachment','background_zoom'),
						    'show' => array('background_position'),
						    'responsive' => array(
							'disabled' => array('background_repeat')
						    )
						),
						'builder-zooming' => array(
						    'hide' => array('background_attachment','background_zoom'),
						    'show' => array('background_position'),
						    'responsive' => array(
							'disabled' => array('background_repeat')
						    )
						),
						'select' => array(
						    'value' => 'repeat-none',
						    'hide' => array('background_zoom'),
						    'show' => array('background_attachment','background_position')
						),
						'responsive' => array(
						    'disabled' => array('builder-parallax-scrolling', 'builder-zoom-scrolling', 'builder-zooming')
						)
					    )
					),
					// Background attachment
					array(
					    'id' => 'background_attachment',
					    'label' => '',
					    'type' => 'select',
					    'origId'=>'background_image',
					    'after' => __('Background Attachment', 'themify'),
					    'options' => array(
						'scroll' => __('Scroll', 'themify'),
						'fixed' => __('Fixed', 'themify')
					    ),
					    'prop' => 'background-attachment',
					    'selector' => ''
					),
					// Background Zoom
					array(
					    'id' => 'background_zoom',
					    'label' => '',
					    'origId'=>'background_image',
					    'type' => 'checkbox',
					    'options' => array(
						array('value' => __('Zoom on hover', 'themify'), 'name' => 'zoom')
					    ),
					    'is_responsive' => false
					),
					// Background position
					array(
					    'id' => 'background_position',
					    'label' => '',
					    'origId'=>'background_image',
					    'type' => 'position_box',
					    'position' => true,
					    'prop' => 'background-position',
					    'selector' => ''
					),
				    )
    				),
			    
			    // Background Color
			    array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => 'bg_c',
				'wrap_class' => 'tb_group_element_image tb_group_element_slider tb_group_element_video',
				'prop' => 'background-color',
				'selector' => ''
			    ),
			)
		    ),
		    'h' => array(
			'options' => array(
			    array(
				'id' => 'b_t_h',
				'label' => __('Background Type', 'themify'),
				'type' => 'radio',
				'options' => array(
				    array('value' => 'image', 'name' => __('Image', 'themify')),
				    array('value' => 'gradient', 'name' => __('Gradient', 'themify'))
				/*  array('value' => 'video', 'name' => __('Video', 'themify')'class' => 'responsive_disable'), */
				/* array('value' => 'slider', 'name' => __('Slider', 'themify'), 'class' => 'responsive_disable') */
				),
				'is_background' => true,
				'wrap_class' => 'tb_compact_radios',
				'option_js' => true
			    ),
			    // Background Image
			    array(
				'id' => 'bg_i_h',
				'type' => 'image',
				'label' => 'b_i',
				'class' => 'xlarge',
				'wrap_class' => 'tb_group_element_image',
				'prop' => 'background-image',
				'selector' => ':hover',
				'binding' => array(
				    'empty' => array(
					'hide' => array('tb_image_options')
				    ),
				    'not_empty' => array(
					'show' => array('tb_image_options')
				    )
				)
			    ),
			    array(
				'id' => 'b_g_h',
				'type' => 'gradient',
				'label' => '',
				'class' => 'xlarge',
				'wrap_class' => 'tb_group_element_gradient',
				'prop' => 'background-image',
				'selector' => ':hover'
			    ),
			    array(
				'type'=>'group',
				'wrap_class' => 'tb_group_element_image tb_image_options',
				'options'=>array(
					// Background repeat
					array(
					    'id' => 'b_r_h',
					    'label' => '',
					    'type' => 'select',
					    'origId'=>'bg_i_h',
					    'after' => __('Background Mode', 'themify'),
					    'prop' => 'background-mode',
					    'selector' => ':hover',
					    'options' => array(
						'repeat' => __('Repeat All', 'themify'),
						'repeat-x' => __('Repeat Horizontally', 'themify'),
						'repeat-y' => __('Repeat Vertically', 'themify'),
						'repeat-none' => __('Do not repeat', 'themify'),
						'fullcover' => __('Fullcover', 'themify'),
						'best-fit-image' => __('Best Fit', 'themify')
					    )

					),
					// Background attachment
					array(
					    'id' => 'b_a_h',
					    'label' => '',
					    'origId'=>'bg_i_h',
					    'type' => 'select',
					    'after' => __('Background Attachment', 'themify'),
					    'options' => array(
						'scroll' => __('Scroll', 'themify'),
						'fixed' => __('Fixed', 'themify')
					    ),
					    'prop' => 'background-attachment',
					    'selector' => ':hover'
					),
					// Background position
					array(
					    'id' => 'b_p_h',
					    'label' => '',
					    'origId'=>'bg_i_h',
					    'type' => 'position_box',
					    'position' => true,
					    'prop' => 'background-position',
					    'selector' => ':hover'
					)
				    )
				),
				// Background Color
				array(
				    'id' => 'b_c_h',
				    'type' => 'color',
				    'label' => 'bg_c',
				    'wrap_class' => 'tb_group_element_image',
				    'prop' => 'background-color',
				    'selector' => ':hover'
				)
			)
		    )
		))
	    )),
	    // Overlay Color
	    self::get_expand($overlay, array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    array(
				'id' => 'cover_color-type',
				'label' => __('Overlay', 'themify'),
				'type' => 'radio',
				'options' => array(
				    array('value' => 'color', 'name' => __('Color', 'themify')),
				    array('value' => 'cover_gradient', 'name' => __('Gradient', 'themify'))
				),
				'option_js' => true,
				'is_overlay' => true
			    ),
			    array(
				'id' => 'cover_color',
				'type' => 'color',
				'label' => '',
				'wrap_class' => 'tb_group_element_color',
				'is_overlay'=>true,
				'prop' => 'background-color',
				'selector' => array('>.builder_row_cover::before', '>.ms-tableCell>.builder_row_cover::before')
			    ),
			    array(
				'id' => 'cover_gradient',
				'type' => 'gradient',
				'label' => '',
				'wrap_class' => 'tb_group_element_cover_gradient',
				'is_overlay'=>true,
				'prop' => 'background-image',
				'selector' => array('>.builder_row_cover::before', '>.ms-tableCell>.builder_row_cover::before')
			    )
			)
		    ),
		    'h' => array(
			'options' => array(
			    array(
				'id' => 'cover_color_hover-type',
				'label' => __('Overlay', 'themify'),
				'type' => 'radio',
				'options' => array(
				    array('value' => 'hover_color', 'name' => __('Color', 'themify')),
				    array('value' => 'hover_gradient', 'name' => __('Gradient', 'themify'))
				),
				'option_js' => true,
				'is_overlay' => true
			    ),
			    array(
				'id' => 'cover_color_hover',
				'type' => 'color',
				'label' => '',
				'wrap_class' => 'tb_group_element_hover_color',
				'is_overlay'=>true,
				'prop' => 'background-color',
				'selector' => array(':hover>.builder_row_cover::before', '>.ms-tableCell:hover>.builder_row_cover::before')
			    ),
			    array(
				'id' => 'cover_gradient_hover',
				'type' => 'gradient',
				'label' => '',
				'wrap_class' => 'tb_group_element_hover_gradient',
				'is_overlay'=>true,
				'prop' => 'background-image',
				'selector' => array('>.builder_row_cover::after', '>.ms-tableCell>.builder_row_cover::after')
			    ),
			)
		    )
		))
	    )),
	    // Inner Container
	    self::get_expand($inner, array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    // Background Image
			    array(
				'id' => 'background_image_inner',
				'type' => 'image',
				'label' => 'b_i',
				'class' => 'xlarge',
				'prop' => 'background-image',
				'selector' => $inner_selector,
				'binding' => array(
				    'empty' => array(
					'hide' => array('tb_image_inner_options')
				    ),
				    'not_empty' => array(
					'show' => array('tb_image_inner_options')
				    )
				)
			    ),
			    // Background repeat
			    array(
				'id' => 'background_repeat_inner',
				'label' => '',
				'type' => 'select',
				'origId'=>'background_image_inner',
				'after' => __('Background Mode', 'themify'),
				'prop' => 'background-mode',
				'selector' => $inner_selector,
				'options' => array(
				    'repeat' => __('Repeat All', 'themify'),
				    'repeat-x' => __('Repeat Horizontally', 'themify'),
				    'repeat-y' => __('Repeat Vertically', 'themify'),
				    'repeat-none' => __('Do not repeat', 'themify'),
				    'fullcover' => __('Fullcover', 'themify'),
				    'best-fit-image' => __('Best Fit', 'themify'),
				),
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
			    ),
			    // Background attachment
			    array(
				'id' => 'background_attachment_inner',
				'label' => '',
				'type' => 'select',
				'origId'=>'background_image_inner',
				'after' => __('Background Attachment', 'themify'),
				'options' => array(
				    'scroll' => __('Scroll', 'themify'),
				    'fixed' => __('Fixed', 'themify')
				),
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
				'prop' => 'background-attachment',
				'selector' => $inner_selector
			    ),
			    // Background position
			    array(
				'id' => 'background_position_inner',
				'label' => '',
				'type' => 'position_box',
				'origId'=>'background_image_inner',
				'position' => true,
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
				'prop' => 'background-position',
				'selector' => $inner_selector
			    ),
			    // Background Color
			    array(
				'id' => 'background_color_inner',
				'type' => 'color',
				'label' => 'bg_c',
				'wrap_class' => 'tb_group_element_image',
				'prop' => 'background-color',
				'selector' => $inner_selector
			    ),
			    self::get_padding($inner_selector, 'padding_inner'),
			    self::get_border($inner_selector, 'border_inner')
			)
		    ),
		    'h' => array(
			'options' => array(
			    // Background Image
			    array(
				'id' => 'b_i_i_h',
				'type' => 'image',
				'label' => 'b_i',
				'class' => 'xlarge',
				'prop' => 'background-image',
				'selector' => $inner_selector_hover,
				'binding' => array(
				    'empty' => array(
					'hide' => array('tb_image_inner_options')
				    ),
				    'not_empty' => array(
					'show' => array('tb_image_inner_options')
				    )
				)
			    ),
			    // Background repeat
			    array(
				'id' => 'b_r_i_h',
				'label' => '',
				'origId'=>'b_i_i_h',
				'type' => 'select',
				'after' => __('Background Mode', 'themify'),
				'prop' => 'background-mode',
				'selector' => $inner_selector_hover,
				'options' => array(
				    'repeat' => __('Repeat All', 'themify'),
				    'repeat-x' => __('Repeat Horizontally', 'themify'),
				    'repeat-y' => __('Repeat Vertically', 'themify'),
				    'repeat-none' => __('Do not repeat', 'themify'),
				    'fullcover' => __('Fullcover', 'themify'),
				    'best-fit-image' => __('Best Fit', 'themify'),
				),
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
			    ),
			    // Background attachment
			    array(
				'id' => 'b_a_i_h',
				'label' => '',
				'origId'=>'b_i_i_h',
				'type' => 'select',
				'after' => __('Background Attachment', 'themify'),
				'options' => array(
				    'scroll' => __('Scroll', 'themify'),
				    'fixed' => __('Fixed', 'themify')
				),
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
				'prop' => 'background-attachment',
				'selector' => $inner_selector_hover
			    ),
			    // Background position
			    array(
				'id' => 'b_p_i_h',
				'label' => '',
				'origId'=>'b_i_i_h',
				'type' => 'position_box',
				'position' => true,
				'wrap_class' => 'tb_group_element_image tb_image_inner_options',
				'prop' => 'background-position',
				'selector' => $inner_selector_hover
			    ),
			    // Background Color
			    array(
				'id' => 'b_c_i_h',
				'type' => 'color',
				'label' => 'bg_c',
				'wrap_class' => 'tb_group_element_image',
				'prop' => 'background-color',
				'selector' => $inner_selector_hover
			    ),
			    self::get_padding($inner_selector, 'p_i', 'h'),
			    self::get_border($inner_selector, 'b_i', 'h')
			)
		    )
		))
	    )),
	    //frame
	    self::get_expand(__('Frame', 'themify'), array(
			self::get_frame_tabs()
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(array('',' p', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6')),
			    self::get_color(array('',' p', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6'), 'font_color'),
			    self::get_font_size(),
			    self::get_line_height(),
			    self::get_letter_spacing(),
			    self::get_text_align(),
			    self::get_text_transform(),
			    self::get_font_style(),
			    self::get_text_decoration('', 'text_decoration_regular'),
			    self::get_text_shadow(array('',' p', ' h1', ' h2', ' h3:not(.module-title)', ' h4', ' h5', ' h6')),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array(':hover',':hover p', ':hover h1', ':hover h2', ':hover h3:not(.module-title)', ':hover h4', ':hover h5', ':hover h6'), 'f_f_h'),
			    self::get_color(array(':hover',':hover p', ':hover h1', ':hover h2', ':hover h3:not(.module-title)', ':hover h4', ':hover h5', ':hover h6'), 'f_c_h'),
			    self::get_font_size('', 'f_s', '', 'h'),
			    self::get_line_height('', 'l_h', 'h'),
			    self::get_letter_spacing('', 'l_s', 'h'),
			    self::get_text_align('', 't_a', 'h'),
			    self::get_text_transform('', 't_t', 'h'),
			    self::get_font_style('', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration('', 't_d_r', 'h'),
			    self::get_text_shadow(array(':hover',':hover p', ':hover h1', ':hover h2', ':hover h3:not(.module-title)', ':hover h4', ':hover h5', ':hover h6'), 't_sh','h'),
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' a', 'link_color'),
			    self::get_text_decoration(' a')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' a', 'l_c', null, null, 'h'),
			    self::get_text_decoration(' a', 't_d', 'h')
			)
		    )
		))
		    )
	    ),
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
		    )
	    )
	);
	// Margin
    $options[] = self::get_expand('m', array(
        self::get_tab(array(
            'n' => array(
            'options' => $margin_fields
            ),
            'h' => array(
            'options' => $margin_hover_fields
            )
        ))
    ));
	// Border
	$options[] = self::get_expand('b', array(
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
	));
        // Filter
		$options[] = self::get_expand('f_l',
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
			);

		// Height & Min Height
		if ($type === 'row' || $type === 'column' || $type === 'subrow'){
			$options[] = self::get_expand('ht', array(
					self::get_height(),
					self::get_min_height(),
                    self::get_max_height()
				)
			);
		}
		// Rounded Corners
		$options[] = self::get_expand('r_c', array(
		        self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(array('', '>.builder_row_cover::before', '>.ms-tableCell>.builder_row_cover::before'))
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(array(':hover', '>.builder_row_cover:hover::before', '>.ms-tableCell>.builder_row_cover:hover::before'), 'r_c')
						)
					)
				))
			)
		);

		// Shadow
		$options[] = self::get_expand('sh', array(
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
		);

		// Position
		if ($type === 'row' || $type === 'subrow'|| $type === 'column'){
			$options[] = self::get_expand('po', array(
				self::get_css_position()
			));
		}

		// Add z-index filed to Rows and Columns
		$options[] = self::get_expand('zi',
			array(
				self::get_zindex()
			)
		);
	if ($type !== 'row') {
            $options[] = array(
                'id' => 'custom_css_' . $type,
                'type' => 'custom_css'
            );
        }
	return apply_filters('themify_builder_' . $type . '_fields_styling', $options);
    }

    protected static function get_directions_data(array $row, $count) {
	$directions = array('desktop', 'tablet', 'tablet_landscape', 'mobile');
	$row_attributes = array();
	foreach ($directions as $dir) {
	    if (!empty($row[$dir . '_dir']) && $row[$dir . '_dir'] !== 'ltr') {
		$row_attributes['data-' . $dir . '_dir'] = $row[$dir . '_dir'];
	    }
	}
	$col_mobile = !empty($row['col_mobile']) && $row['col_mobile'] !== 'mobile-auto' ? $row['col_mobile'] : false;
	$col_tablet = !empty($row['col_tablet']) && $row['col_tablet'] !== 'tablet-auto' ? $row['col_tablet'] : false;
	$col_tablet_landscape = !empty($row['col_tablet_landscape']) && $row['col_tablet_landscape'] !== 'tablet_landscape-auto' ? $row['col_tablet_landscape'] : false;
	if ($col_mobile !== false || $col_tablet !== false || $col_tablet_landscape !== false) {
	    $row_attributes['data-basecol'] = $count;
	    if ($col_tablet !== false) {
		$row_attributes['data-col_tablet'] = $col_tablet;
	    }
	    if ($col_tablet_landscape !== false) {
		$row_attributes['data-col_tablet_landscape'] = $col_tablet_landscape;
	    }
	    if ($col_mobile !== false) {
		$row_attributes['data-col_mobile'] = $col_mobile;
	    }
	}
	return $row_attributes;
    }

    protected static function get_order($count) {
	switch ($count) {

	    case 6:
		$order_classes = array('first', 'second', 'third', 'fourth', 'fifth', 'last');
		break;

	    case 5:
		$order_classes = array('first', 'second', 'third', 'fourth', 'last');
		break;

	    case 4:
		$order_classes = array('first', 'second', 'third', 'last');
		break;

	    case 3:
		$order_classes = array('first', 'middle', 'last');
		break;

	    case 2:
		$order_classes = array('first', 'last');
		break;

	    default:
		$order_classes = array('first');
		break;
	}
	return $order_classes;
    }

    public static function get_frame_tabs($selector = '', $id = 'frame_tabs') {
;

	return self::get_tab(
			array(
			    'top' => array(
				'label' => 'top',
				'options' => self::get_frame_props($selector),
			    ),
			    'bottom' => array(
				'label' => 'bottom',
				'options' => self::get_frame_props($selector, 'bottom')
			    ),
			    'left' => array(
				'label' => 'left',
				'options' => self::get_frame_props($selector, 'left')
			    ),
			    'right' => array(
				'label' => 'right',
				'options' => self::get_frame_props($selector, 'right')
			    )
			)
	);
    }

    private static function get_frame_props($selector = '', $id = 'top') {


	return array(
	    array(
		'id' => $id . '-frame_type',
		'type' => 'radio',
		'options' => array(
		    /**
		     * @note the value in this option is prefixed with $id, this is to ensure option_js works properly
		     */
		    array('value' => $id . '-presets', 'name' => __('Presets', 'themify')),
		    array('value' => $id . '-custom', 'name' => __('Custom', 'themify')),
		),
		'prop' => 'frame-custom',
		'wrap_class' => 'tb_frame',
		/**
		 * the second selector is for themes with Builder Section Scrolling feature
		 * @ref #7241
		 */
		'selector' => array($selector . '>.tb_row_frame_' . $id, $selector . ' > .fp-tableCell > .tb_row_frame_' . $id),
		'option_js' => true
	    ),
	    array(
		'id' => $id . '-frame_layout',
		'type' => 'frame',
		'prop' => 'frame',
		'wrap_class' => 'frame_tabs tb_group_element_' . $id . '-presets',
		'selector' => array($selector . '>.tb_row_frame_' . $id, $selector . ' > .fp-tableCell > .tb_row_frame_' . $id)
	    ),
	    array('id' => $id . '-frame_custom',
		'type' => 'image',
		'class' => 'tb_frame xlarge',
		'wrap_class' => 'tb_group_element_' . $id . '-custom'
	    ),
	    array('id' => $id . '-frame_color',
		'type' => 'color',
		'class' => 'tb_frame small',
		'wrap_class' => 'tb_group_element_' . $id . '-presets'
	    ),
	    array(
		'type' => 'multi',
		'options' => array(
		    array('id' => $id . '-frame_width',
			'type' => 'range',
			'class' => 'tb_frame xsmall',
			'label' => 'w',
			'select_class' => 'tb_frame_unit',
			'units' => array(
			    '%' => array(
				'min' => 0,
				'max' => 1000
			    ),
			    'px' => array(
				'min' => 0,
				'max' => 10000
			    ),
			    'em' => array(
				'min' => 0,
				'max' => 30
			    )
			)
		    ),
		    array('id' => $id . '-frame_height',
			'type' => 'range',
			'label' => '',
			'class' => 'tb_frame xsmall',
			'label' => 'ht',
			'select_class' => 'tb_frame_unit',
			'units' => array(
			    '%' => array(
				'min' => 0,
				'max' => 1000
			    ),
			    'px' => array(
				'min' => 0,
				'max' => 10000
			    ),
			    'em' => array(
				'min' => 0,
				'max' => 30
			    )
			)
		    ),
		    array(
			'id' => $id . '-frame_repeat',
			'type' => 'range',
			'label' => 'r',
			'class' => 'tb_frame'
		    )
		)
	    ),
	    array(
		'id' => $id . '-frame_location',
		'type' => 'select',
		'is_responsive' => false,
		'class' => 'tb_frame',
		'options' => array(
		    'in_bellow' => __('Display below content', 'themify'),
		    'in_front' => __('Display above content', 'themify')
		)
	    ),
	);
    }

    /**
     * Return the correct animation css class name
     * @param string $effect 
     * @return string
     */
    public static function parse_animation_effect($effect, $mod_settings = null) {

	$class = '';
	if (Themify_Builder_Model::is_animation_active()) {
	    if (!empty($mod_settings['hover_animation_effect'])) {
		$class = ' hover-wow hover-animation-' . $mod_settings['hover_animation_effect'];
	    }
	    if ($effect === '') {
		return $class;
	    }
	    $class .= '' !== $effect && !in_array($effect, array('fade-in', 'fly-in', 'slide-up'), true) ? ' wow ' . $effect : $effect;
	    if (!empty($mod_settings['animation_effect_delay'])) {
		$class .= ' animation_effect_delay_' . $mod_settings['animation_effect_delay'];
	    }
	    if (!empty($mod_settings['animation_effect_repeat'])) {
		$class .= ' animation_effect_repeat_' . $mod_settings['animation_effect_repeat'];
	    }
	}
	return $class;
    }

    /**
     * Retrieve builder templates
     * @param $template_name
     * @param array $args
     * @param string $template_path
     * @param string $default_path
     * @param bool $echo
     * @return string
     */
    public static function retrieve_template($template_name, $args = array(), $template_path = '', $default_path = '', $echo = true) {

	ob_start();
	self::get_template($template_name, $args, $template_path = '', $default_path = '');
	if ($echo) {
	    echo ob_get_clean();
	} else {
	    return ob_get_clean();
	}
    }

    /**
     * Get template builder
     * @param $template_name
     * @param array $args
     * @param string $template_path
     * @param string $default_path
     */
    public static function get_template($template_name, $args = array(), $template_path = '', $default_path = '') {
	static $paths = array();
	$key = $template_name . $template_path . $default_path;
	if (!isset($paths[$key])) {
	    $paths[$key] = self::locate_template($template_name, $template_path, $default_path);
	}
	if (isset($paths[$key])) {
	    global $ThemifyBuilder;
	    //backward compatibility 02.26.19
	    if (!empty($args)) {
		extract($args);
	    }
	    include($paths[$key]);
	}
    }

    /**
     * Locate a template and return the path for inclusion.
     *
     * This is the load order:
     *
     * 		yourtheme		/	$template_path	/	$template_name
     * 		$default_path	/	$template_name
     */
    public static function locate_template($template_name, $template_path = '', $default_path = '') {
	$template = '';
	$templates = Themify_Builder_Model::get_directory_path('templates');
	foreach ($templates as $dir) {
	    if (is_file($dir . $template_name)) {
		$template = $dir . $template_name;
		break;
	    }
	}
	// Get default template
	if (!$template) {
	    $template = $default_path . $template_name;
	}
	// Return what we found
	return apply_filters('themify_builder_locate_template', $template, $template_name, $template_path);
    }

    /**
     * Get checkbox data
     * @param $setting
     * @return string
     */
    public static function get_checkbox_data($setting) {
	return implode(' ', explode('|', $setting));
    }

    /**
     * Return only value setting
     * @param $string
     * @return string
     */
    public static function get_param_value($string) {
	$val = explode('|', $string);
	return $val[0];
    }

    /**
     * Helper to get element attributes return as string.
     *
     * @access public
     * @param array $props
     * @return string
     */
    public static function get_element_attributes($props) {
	$out = '';
	foreach ($props as $atts => $val) {
	    $out .= ' ' . $atts . '="' . esc_attr($val) . '"';
	}
	return $out;
    }

    /**
     * Filter post_class to add the classnames to posts
     *
     * @return array
     */
    public static function filter_post_class($classes) {
	return !empty(self::$_post_classes) ? array_merge($classes, self::$_post_classes) : $classes;
    }

    /**
     * Add classes to post_class
     * @param string|array $classes
     */
    public static function add_post_class($classes) {
	foreach ((array) $classes as $class) {
	    self::$_post_classes[$class] = $class;
	}
    }

    /**
     * Remove sepecified classnames from post_class
     * @param string|array $classes
     */
    public static function remove_post_class($classes) {
	foreach ((array) $classes as $class) {
	    unset(self::$_post_classes[$class]);
	}
    }

    /**
     * Get query page
     */
    public static function get_paged_query() {
	global $wp;
	$page = 1;
	$qpaged = get_query_var('paged');
	if (!empty($qpaged)) {
	    $page = $qpaged;
	} else {
	    $qpaged = wp_parse_args($wp->matched_query);
	    if (isset($qpaged['paged']) && $qpaged['paged'] > 0) {
		$page = $qpaged['paged'];
	    }
	}
	return $page;
    }

    /**
     * Returns page navigation
     * @param string Markup to show before pagination links
     * @param string Markup to show after pagination links
     * @param object WordPress query object to use
     * @param original_offset number of posts configured to skip over
     * @return string
     */
    public static function get_pagenav($before = '', $after = '', $query = false, $original_offset = 0) {
	global $wp_query;

	if (false == $query) {
	    $query = $wp_query;
	}

	$paged = (int) self::get_paged_query();
	$numposts = $query->found_posts;

	// $query->found_posts does not take offset into account, we need to manually adjust that
	if ((int) $original_offset) {
	    $numposts = $numposts - (int) $original_offset;
	}

	$max_page = ceil($numposts / $query->query_vars['posts_per_page']);
	$out = '';

	if (empty($paged)) {
	    $paged = 1;
	}
	$pages_to_show = apply_filters('themify_filter_pages_to_show', 5);
	$pages_to_show_minus_1 = $pages_to_show - 1;
	$half_page_start = floor($pages_to_show_minus_1 / 2);
	$half_page_end = ceil($pages_to_show_minus_1 / 2);
	$start_page = $paged - $half_page_start;
	if ($start_page <= 0) {
	    $start_page = 1;
	}
	$end_page = $paged + $half_page_end;
	if (($end_page - $start_page) != $pages_to_show_minus_1) {
	    $end_page = $start_page + $pages_to_show_minus_1;
	}
	if ($end_page > $max_page) {
	    $start_page = $max_page - $pages_to_show_minus_1;
	    $end_page = $max_page;
	}
	if ($start_page <= 0) {
	    $start_page = 1;
	}

	if ($max_page > 1) {
	    $out .= $before . '<div class="pagenav clearfix">';
	    if ($start_page >= 2 && $pages_to_show < $max_page) {
		$first_page_text = "&laquo;";
		$out .= '<a href="' . esc_url(get_pagenum_link()) . '" title="' . esc_attr($first_page_text) . '" class="number">' . $first_page_text . '</a>';
	    }
	    if ($pages_to_show < $max_page)
		$out .= get_previous_posts_link('&lt;');
	    for ($i = $start_page; $i <= $end_page; $i++) {
		if ($i == $paged) {
		    $out .= ' <span class="number current">' . $i . '</span> ';
		} else {
		    $out .= ' <a href="' . esc_url(get_pagenum_link($i)) . '" class="number">' . $i . '</a> ';
		}
	    }
	    if ($pages_to_show < $max_page)
		$out .= get_next_posts_link('&gt;');
	    if ($end_page < $max_page) {
		$last_page_text = "&raquo;";
		$out .= '<a href="' . esc_url(get_pagenum_link($max_page)) . '" title="' . esc_attr($last_page_text) . '" class="number">' . $last_page_text . '</a>';
	    }
	    $out .= '</div>' . $after;
	}
	return $out;
    }

    public static function get_seperator($label = false) {
	$opt = array(
	    'type' => 'separator'
	);
	if ($label !== false) {
	    $opt['label'] = $label;
	}
	return $opt;
    }

    public static function get_expand($label, array $options) {
	return array(
	    'type' => 'expand',
	    'label' => $label,
	    'options' => $options
	);
    }

    protected static function get_font_family($selector = '', $id = 'font_family', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'font_select',
	    'label' => 'f_f',
	    'prop' => 'font-family',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_element_font_weight($selector = '', $id = 'element_font_weight', $state = '') {//backward compatibility
    }

    protected static function get_font_size($selector = '', $id = 'font_size', $label = '', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	if ($label === '') {
	    $label = 'f_s';
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => $label,
	    'selector' => $selector,
	    'prop' => 'font-size',
	    'units' => array(
		'px' => array(
		    'min' => 6,
		    'max' => 900
		),
		'em' => array(
		    'min' => 0.5,
		    'max' => 50
		),
		'%' => array(
		    'min' => 70,
		    'max' => 4000
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_line_height($selector = '', $id = 'line_height', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => 'l_h',
	    'selector' => $selector,
	    'prop' => 'line-height',
	    'units' => array(
		'px' => array(
		    'min' => -400,
		    'max' => 400
		),
		'em' => array(
		    'min' => -20,
		    'max' => 20
		),
		'%' => array(
		    'min' => 100,
		    'max' => 4000
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_letter_spacing($selector = '', $id = 'letter_spacing', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => 'l_s',
	    'selector' => $selector,
	    'prop' => 'letter-spacing',
	    'units' => array(
		'px' => array(
		    'min' => -50,
		    'max' => 200
		),
		'em' => array(
		    'min' => -3,
		    'max' => 20
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_text_align($selector = '', $id = 'text_align', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 't_a',
	    'type' => 'icon_radio',
	    'aligment' => true,
	    'prop' => 'text-align',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_text_transform($selector = '', $id = 'text_transform', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 't_t',
	    'type' => 'icon_radio',
	    'text_transform' => true,
	    'prop' => 'text-transform',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_text_decoration($selector = '', $id = 'text_decoration', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'icon_radio',
	    'label' => 't_d',
	    'text_decoration' => true,
	    'prop' => 'text-decoration',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_font_style($selector = '', $id = 'font_style', $id2 = 'font_weight', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	    $id2 .= '_' . $state;
	}
	$res = array(
	    'type' => 'multi',
	    'wrap_class' => 'tb_multi_fonts',
	    'label' => 'f_st',
	    'options' => array(
		array(
		    'id' => $id . '_regular',
		    'type' => 'icon_radio',
		    'font_style' => true,
		    'prop' => 'font-style',
		    'selector' => $selector
		),
		array(
		    'id' => $id2,
		    'type' => 'icon_radio',
		    'font_weight' => true,
		    'prop' => 'font-weight',
		    'selector' => $selector
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    foreach ($res['options'] as $k => $v) {
		$res['options'][$k]['ishover'] = true;
	    }
	}
	return $res;
    }

    protected static function get_color($selector = '', $id = '', $label = null, $prop = 'color',  $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	if ($prop === null) {
	    $prop = 'color';
	}
	if ($label === null) {
	    $label = 'c';
	}
	$color = array(
	    'id' => $id,
	    'type' => 'color',
	    'prop' => $prop,
	    'selector' => $selector
	);
	if ($label) {
	    $color['label'] = $label;
	}
	if ($state === 'h' || $state === 'hover') {
	    $color['ishover'] = true;
	}
	return $color;
    }

    protected static function get_image($selector = '', $id = 'background_image', $colorId = 'background_color',$repeatId='background_repeat',$posId='background_position', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	    if($colorId!==''){
		$colorId.= '_' . $state;
	    }
	    if($repeatId!==''){
		$repeatId.= '_' . $state;
	    }
	     if($posId!==''){
		$posId.= '_' . $state;
	    }
	}
	$res = array(
	    'id' => $id,
	    'type' => 'imageGradient',
	    'label' => 'bg',
	    'prop' => 'background-image',
	    'selector' => $selector,
	    'option_js' => true,
	    'origId'=>$id,
	    'colorId' => $colorId,
	    'repeatId'=>$repeatId,
	    'posId'=>$posId,
	    'binding' => array(
		'empty' => array(
		    'hide' => array('tb_image_options')
		),
		'not_empty' => array(
		    'show' => array('tb_image_options')
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

	// CSS Filters
	protected static function get_blend( $selector = '', $id = 'bl_m', $state = '', $filters_id = 'css_f' ) {
		if ( $state !== '' ) {
			$id .= '_' . $state;
			$filters_id .= '_' . $state;
		}
		$res = array();
		$res[] = array( 'id' => $id,
			'label' => 'b_m',
			'type' => 'select',
			'prop' => 'mix-blend-mode',
			'is_responsive' => false,
			'selector' => $selector,
			'blend' => true
		);
		$res[] = array(
			'id' => $filters_id,
			'type' => 'filters',
			'is_responsive' => false,
			'prop' => 'filter',
			'selector' => $selector
		);
		if ( $state === 'h' || $state === 'hover' ) {
			$res[0]['ishover'] = true;
			$res[1]['ishover'] = true;
		}
		return $res;

	}

    protected static function get_repeat($selector = '', $id = 'background_repeat', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 'b_r',
	    'type' => 'select',
	    'repeat' => true,
	    'prop' => 'background-mode',
	    'selector' => $selector,
	    'wrap_class' => 'tb_group_element_image tb_image_options'
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_position($selector = '', $id = 'background_position', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'label' => 'b_p',
	    'type' => 'position_box',
	    'position' => true,
	    'prop' => 'background-position',
	    'selector' => $selector,
	    'wrap_class' => 'tb_group_element_image tb_image_options'
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_padding($selector = '', $id = 'padding', $state = '',$isDragable=null) {
	if($id===''){
	    $id = 'padding';
	}
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'padding',
	    'label' => 'p',
	    'prop' => 'padding',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	elseif(($isDragable===true && ($selector!=='' || $id!=='padding')) ||($isDragable===null && $selector==='' && $id!=='padding')){
	  
	    self::setDraggables(get_called_class(), $res['type'], $res['id'], $res['selector']);
	}
	return $res;
    }

    protected static function get_margin($selector = '', $id = 'margin', $state = '',$isDragable=null) {
	if($id===''){
	    $id = 'margin';
	}
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'margin',
	    'label' => 'm',
	    'prop' => 'margin',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	elseif(($isDragable===true && ($selector!=='' || $id!=='margin')) ||($isDragable===null && $selector==='' && $id!=='margin')){
	    self::setDraggables(get_called_class(), $res['type'], $res['id'], $res['selector']);
	}
	return $res;
    }

    protected static function get_margin_top($selector = '', $id = 'margin-top', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => 'm',
	    'prop' => 'margin-top',
	    'selector' => $selector,
	    'description' => '<span class="tb_range_after">'.__('Top', 'themify').'<span>',
	    'units' => array(
		'px' => array(
		    'min' => -1000,
		    'max' => 1000
		),
		'em' => array(
		    'min' => -20,
		    'max' => 20
		),
		'%' => array(
		    'min' => -100,
		    'max' => 100
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_margin_bottom($selector = '', $id = 'margin-bottom', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'range',
	    'label' => '',
	    'prop' => 'margin-bottom',
	    'selector' => $selector,
	    'description' =>'<span class="tb_range_after">'.__('Bottom', 'themify').'<span>',
	    'units' => array(
		'px' => array(
		    'min' => -1000,
		    'max' => 1000
		),
		'em' => array(
		    'min' => -20,
		    'max' => 20
		),
		'%' => array(
		    'min' => -100,
		    'max' => 100
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    
    protected static function get_margin_top_bottom_opposity($selector = '', $topId = 'margin-top',$bottomId='margin-bottom', $state = ''){
	if ($state !== '') {
	    $topId .= '_' . $state;
	    $bottomId .= '_' . $state;
	}
	$res = array(
	    'topId' => $topId,
	    'bottomId'=>$bottomId,
	    'type' => 'margin_opposity',
	    'label' => '',
	    'prop'=>'',
	    'selector' => $selector,
	    'units' => array(
		'px' => array(
		    'min' => -1000,
		    'max' => 1000
		),
		'em' => array(
		    'min' => -20,
		    'max' => 20
		),
		'%' => array(
		    'min' => -100,
		    'max' => 100
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_border($selector = '', $id = 'border', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id,
	    'type' => 'border',
	    'label' => 'b',
	    'prop' => 'border',
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_width($selector = '', $id = 'width', $state = '') {

	if ($state !== '') {
	    $id .= '_' . $state;
	}

	$res = array(
	    'id' => $id,
	    'type' => 'width',
	    'prop' => 'width',
	    'label' => '',
	    'selector' => $selector,
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_multi_columns_count($selector = '', $id = 'column', $state = '') {
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	$res = array(
	    'id' => $id . '_count',
	    'type' => 'multiColumns',
	    'label' => 'c_c',
	    'prop' => 'column-count',
	    'binding' => array(
		'empty' => array(
		    'hide' => array('tb_multi_columns_wrap')
		),
		'not_empty' => array(
		    'show' => array('tb_multi_columns_wrap')
		)
	    ),
	    'selector' => $selector
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_multi_columns_gap($selector = '', $id = 'column', $state = '') {//backward compatibility
    }

    protected static function get_multi_columns_divider($selector = '', $id = 'column', $state = '') {//backward compatibility
    }

    protected static function get_heading_margin_multi_field($selector = '', $h_level = 'h1', $margin_side = 'top', $state = '', $id = '') {
	$id = $id === '' ? $h_level : $id;
	if ($h_level === '') {
	    $h_level.=' ';
	}
	$id = $id . '_margin_' . $margin_side;
	if ($state !== '') {
	    $id .= '_' . $state;
	}
	if($selector!=='' && is_array($selector)){
	    foreach ($selector as $key=>$val){
	        $selector[$key] = $val . ' ' . $h_level;
	    }
	}else{
		$selector .= ' ' . $h_level;
	}
	$res = array(
	    'label' => ('top' === $margin_side ? 'm' : ''),
	    'id' => $id,
	    'type' => 'range',
	    'prop' => 'margin-' . $margin_side,
	    'selector' => $selector,
	    'description' =>'<span class="tb_range_after">'. sprintf(__('%s', 'themify'), $margin_side).'<span>',
	    'units' => array(
		'px' => array(
		    'min' => -1000,
		    'max' => 1000
		),
		'em' => array(
		    'min' => -20,
		    'max' => 20
		),
		'%' => array(
		    'min' => 0,
		    'max' => 100,
		    'increment' => 1
		)
	    )
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }

    protected static function get_color_type($selector='',$state = '',$id='',$solid_id='', $gradient_id='') {
	if ($state !== '') {
	    if($id===''){
		$id= 'f_c_t';
	    }
	    if($solid_id===''){
		$solid_id= 'f_c';
	    }
	    if($gradient_id===''){
		$gradient_id= 'f_g_c';
	    }
	    $id .= '_' . $state;
	    $solid_id .= '_' . $state;
	    $gradient_id .= '_' . $state;
	}
	else{
	    if($id===''){
		$id= 'font_color_type';
	    }
	    if($solid_id===''){
		$solid_id= 'font_color';
	    }
	    if($gradient_id===''){
		$gradient_id= 'font_gradient_color';
	    }
	}
	
	$res = array(
	    'id' => $id,
	    'type' => 'fontColor',
	    'selector' => $selector,
	    'prop'=>'radio',
	    'label'=>'f_c',
	    's' => $solid_id,
	    'g' => $gradient_id
	    
	);
	if ($state === 'h' || $state === 'hover') {
	    $res['ishover'] = true;
	}
	return $res;
    }
    
    

    /**
     * Computes and returns data for Builder row or column video background.
     *
     * @since 2.3.3
     *
     * @param array $styling The row's or column's styling array.
     *
     * @return bool|string Return video data if row/col has a background video, else return false.
     */
	protected static function get_video_background( $styling ) {
		if ( !( isset( $styling['background_type'] ) && 'video' === $styling['background_type'] && !empty( $styling['background_video'] ) ) ) {
			return false;
		}
		$video_data = 'data-fullwidthvideo="' . esc_url( themify_https_esc( $styling['background_video'] ) ) . '"';

		// Will only be written if they exist, for backwards compatibility with global JS variable tbLocalScript.backgroundVideoLoop

		if ( isset( $styling['background_video_options'] ) ) {
			if ( is_array( $styling['background_video_options'] ) ) {
				$video_data .= in_array( 'mute', $styling['background_video_options'], true ) ? '' : ' data-mutevideo="unmute"';
				$video_data .= in_array( 'unloop', $styling['background_video_options'], true ) ? ' data-unloopvideo="unloop"' : '';
				$video_data .= in_array( 'playonmobile', $styling['background_video_options'], true ) ? ' data-playonmobile="play"' : '';
			} else {
				$video_data .= ( false !== stripos( $styling['background_video_options'], 'mute' ) ) ? '' : ' data-mutevideo="unmute"';
				$video_data .= ( false !== stripos( $styling['background_video_options'], 'unloop' ) ) ? ' data-unloopvideo="unloop"' : '';
				$video_data .= ( false !== stripos( $styling['background_video_options'], 'playonmobile' ) ) ? ' data-playonmobile="play"' : '';
			}
		}
		return apply_filters( 'themify_builder_row_video_background', $video_data, $styling );
	}

    /**
     * Sticky Element props attributes
     * @param array $props 
     * @param array $fields_args 
     * @param string $mod_name 
     * @param string $module_ID 
     * @return array
     */
    public static function sticky_element_props($props, $fields_args) {

	if (isset($fields_args['stick_at_check']) && '' !== $fields_args['stick_at_check']) {
	    $settings = array();

	    if (!isset($fields_args['stick_at_pos_val']))
		$fields_args['stick_at_pos_val'] = '';

	    if (!isset($fields_args['stick_at_pos_val_unit']))
		$fields_args['stick_at_pos_val_unit'] = 'px';

	    if (!isset($fields_args['unstick_when_pos_val']))
		$fields_args['unstick_when_pos_val'] = '';

	    if (!isset($fields_args['unstick_when_pos_val_unit']))
		$fields_args['unstick_when_pos_val_unit'] = 'px';
	    
	    if(!isset($fields_args['unstick_when_element'])){
		$fields_args['unstick_when_element'] = 'builder_end';
	    }
	    if(!isset($fields_args['unstick_when_el_row_id'])){
		$fields_args['unstick_when_el_row_id'] = 'row';
	    }
	    if(!isset($fields_args['unstick_when_condition'])){
		$fields_args['unstick_when_condition'] = 'hits';
	    }
	    if(!isset($fields_args['unstick_when_pos'])){
		$fields_args['unstick_when_pos'] = 'this';
	    }
	    $settings['stick'] = array(
		'position' => isset($fields_args['stick_at_position'])?$fields_args['stick_at_position']:'',
		'value' => $fields_args['stick_at_pos_val'],
		'val_unit' => $fields_args['stick_at_pos_val_unit']
	    );

	    if (isset($fields_args['unstick_when_check']) && '' !== $fields_args['unstick_when_check']) {
			$settings['unstick'] = array(
				'el_type' =>$fields_args['unstick_when_element'],
				'el_row_target' => $fields_args['unstick_when_el_row_id'],
				'el_mod_target' =>isset( $fields_args['unstick_when_el_mod_id'])? $fields_args['unstick_when_el_mod_id']:'',
				'rule' => $fields_args['unstick_when_condition'],
				'current' => $fields_args['unstick_when_pos'],
				'value' => $fields_args['unstick_when_pos_val'],
				'val_unit' => $fields_args['unstick_when_pos_val_unit']
			);
	    }
	    $props['data-sticky-active'] = wp_json_encode($settings);
	    
	    
	}
	//Add custom attributes html5 data to module container div to show parallax options.
	elseif (Themify_Builder::$frontedit_active===false && ( !empty( $fields_args['motion_effects']  ) || !empty( $fields_args['custom_parallax_scroll_speed'] ) ) && Themify_Builder_Model::is_parallax_active()) {
		$props['data-lax'] = 'true';
		$props['data-lax-anchor'] = 'self';
		// Check settings from Floating tab to apply them to Lax library
		if ( !empty( $fields_args['custom_parallax_scroll_speed'] ) ) {
	    $props['data-parallax-element-speed'] = $fields_args['custom_parallax_scroll_speed'];

		$speed = self::map_animation_speed($fields_args['custom_parallax_scroll_speed']);

	    if (!empty($fields_args['custom_parallax_scroll_reverse']) && $fields_args['custom_parallax_scroll_reverse'] !== '|') {
				$props['data-lax-translate-y'] = 'vh 1,0 ' . $speed;
			} else {
				$props['data-lax-translate-y'] = 'vh 1,0 -' . $speed;
	    }
	    if (!empty($fields_args['custom_parallax_scroll_fade']) && $fields_args['custom_parallax_scroll_fade'] !== '|') {
				$props['data-lax-opacity'] = 'vh 1,0 0';
	    }
	}
		if ( ! isset( $fields_args['motion_effects']['t'] ) ) {
		$props['data-lax-optimize'] = 'true';
		}
        // Add motion effects from Motion tab
        // Vertical
	if ( isset( $fields_args['motion_effects']['v'],$fields_args['motion_effects']['v']['val']['v_dir'] )
            && $fields_args['motion_effects']['v']['val']['v_dir'] !== '' ) {
		    $v_speed = 600;
			if ( isset( $fields_args['motion_effects']['v']['val']['v_speed'] ) ) {
				$v_speed = self::map_animation_speed($fields_args['motion_effects']['v']['val']['v_speed']);
			}
		    $viewport = isset($fields_args['motion_effects']['v']['val']['v_vp']) ? explode(',', $fields_args['motion_effects']['v']['val']['v_vp']) : array(0,100);
			$bottom = 1 - ((int) $viewport[0] / 100);
			$top = 1 - ((int) $viewport[1] / 100);
			$props['data-lax-translate-y'] = $fields_args['motion_effects']['v']['val']['v_dir'] === 'up' ? '(vh*' . $bottom . ') 0,(vh*' . $top . ') -' . $v_speed : '(vh*' . $bottom . ') 0,(vh*' . $top . ') ' . $v_speed;
			if ( isset( $fields_args['motion_effects']['v']['val']['v_speed'] ) && $fields_args['motion_effects']['v']['val']['v_speed'] !== '0' ) {
				$props['data-lax-v-speed'] = $fields_args['motion_effects']['v']['val']['v_speed'];
			}
		}
		// Horizontal
		if ( isset( $fields_args['motion_effects']['h'],$fields_args['motion_effects']['h']['val']['h_dir']  )
		            && $fields_args['motion_effects']['h']['val']['h_dir'] !== '' ) {
		    $h_speed = 600;
			if ( isset( $fields_args['motion_effects']['h']['val']['h_speed'] ) ) {
				$h_speed = self::map_animation_speed($fields_args['motion_effects']['h']['val']['h_speed']);
			}
			$viewport = isset($fields_args['motion_effects']['h']['val']['h_vp']) ? explode(',', $fields_args['motion_effects']['h']['val']['h_vp']) : array(0,100);
			$bottom = 1 - ((int) $viewport[0] / 100);
			$top = 1 - ((int) $viewport[1] / 100);
			$props['data-lax-translate-x'] = $fields_args['motion_effects']['h']['val']['h_dir'] === 'toleft' ? '(vh*' . $bottom . ') 0,(vh*' . $top . ') -' . $h_speed : '(vh*' . $bottom . ') 0,(vh*' . $top . ') ' . $h_speed;
			if ( isset( $fields_args['motion_effects']['h']['val']['h_speed'] ) && $fields_args['motion_effects']['h']['val']['h_speed'] !== '0' ) {
				$props['data-lax-h-speed'] = $fields_args['motion_effects']['h']['val']['h_speed'];
			}
		}
		// Opacity
		if ( isset( $fields_args['motion_effects']['t'],$fields_args['motion_effects']['t']['val']['t_dir'] )
		     && $fields_args['motion_effects']['t']['val']['t_dir'] !== '' ) {
			$viewport = isset($fields_args['motion_effects']['t']['val']['t_vp']) ? explode(',', $fields_args['motion_effects']['t']['val']['t_vp']) : array(0,100);
			$bottom = 1 - ((int) $viewport[0] / 100);
			$top = 1 - ((int) $viewport[1] / 100);
			$center = ( $bottom - (($bottom - $top) / 2) );
			if ( $fields_args['motion_effects']['t']['val']['t_dir'] === 'fadein' ) {
				$props['data-lax-opacity'] = '(vh*' . $bottom . ') 0,(vh*' . $top . ') 1';
			} else if ( $fields_args['motion_effects']['t']['val']['t_dir'] === 'fadeout' ) {
				$props['data-lax-opacity'] = '(vh*' . $bottom . ') 1,(vh*' . $top . ') 0';
			} else if ( $fields_args['motion_effects']['t']['val']['t_dir'] === 'fadeoutin' ) {
				$props['data-lax-opacity'] = '(vh*' . $bottom . ') 1,(vh*' . $center . ') 0,(vh*' . $top . ') 1';
			} else if ( $fields_args['motion_effects']['t']['val']['t_dir'] === 'fadeinout' ) {
				$props['data-lax-opacity'] = '(vh*' . $bottom . ') 0,(vh*' . $center . ') 1,(vh*' . $top . ') 0';
			}
        } elseif ( ! isset($fields_args['animation_effect_delay'])) {
			$props['data-lax-no-op'] = 'true';
			}
		// Blur
		if ( isset( $fields_args['motion_effects']['b'],$fields_args['motion_effects']['b']['val']['b_dir'] )
		     && $fields_args['motion_effects']['b']['val']['b_dir'] !== '' ) {
		    $b_level = 10;
			if ( isset( $fields_args['motion_effects']['b']['val']['b_level'] ) ) {
				$b_level = self::map_animation_speed($fields_args['motion_effects']['b']['val']['b_level'], 'blur');
			}
			if ( $fields_args['motion_effects']['b']['val']['b_dir'] === 'fadein' ) {
				$props['data-lax-b'] = $b_level;
			}
			$viewport = isset($fields_args['motion_effects']['b']['val']['b_vp']) ? explode(',', $fields_args['motion_effects']['b']['val']['b_vp']) : array(0,100);
			$bottom = 1 - ((int) $viewport[0] / 100);
			$top = 1 - ((int) $viewport[1] / 100);
			$props['data-lax-blur'] = $fields_args['motion_effects']['b']['val']['b_dir'] === 'fadein' ? '(vh*' . $bottom . ') ' . $b_level . ',(vh*' . $top . ') 0' : '(vh*' . $bottom . ') 0,(vh*' . $top . ') ' . $b_level;
			if ( isset( $fields_args['motion_effects']['b']['val']['b_level'] ) ) {
				$props['data-lax-b-speed'] = $fields_args['motion_effects']['b']['val']['b_level'];
			}
			}
		// Rotate
		if ( isset( $fields_args['motion_effects']['r'],$fields_args['motion_effects']['r']['val']['r_dir'] )
		     && $fields_args['motion_effects']['r']['val']['r_dir'] !== '' ) {
			$viewport = isset($fields_args['motion_effects']['r']['val']['r_vp']) ? explode(',', $fields_args['motion_effects']['r']['val']['r_vp']) : array(0,100);
			$rotates = isset($fields_args['motion_effects']['r']['val']['r_num']) ? (float) $fields_args['motion_effects']['r']['val']['r_num'] * 360 : 360;
			$bottom = 1 - ((int) $viewport[0] / 100);
			$top = 1 - ((int) $viewport[1] / 100);
			$props['data-lax-rotate'] = $fields_args['motion_effects']['r']['val']['r_dir'] === 'toleft' ? '(vh*' . $bottom . ') 0,(vh*' . $top . ') -' . $rotates : '(vh*' . $bottom . ') 0,(vh*' . $top . ') ' . $rotates;
			if ( isset( $fields_args['motion_effects']['r']['val']['r_origin'] ) ) {
				$props['data-box-position'] = self::map_transform_origin($fields_args['motion_effects']['r']['val']['r_origin']);
			}
		}
        // Scale
		if ( isset( $fields_args['motion_effects']['s'],$fields_args['motion_effects']['s']['val']['s_dir']  )
		     && $fields_args['motion_effects']['s']['val']['s_dir'] !== '' ) {
			$viewport = isset($fields_args['motion_effects']['s']['val']['s_vp']) ? explode(',', $fields_args['motion_effects']['s']['val']['s_vp']) : array(0,100);
			$ratio = isset($fields_args['motion_effects']['s']['val']['s_ratio']) ? (float) $fields_args['motion_effects']['s']['val']['s_ratio'] : 3;
			$bottom = 1 - ((int) $viewport[0] / 100);
			$top = 1 - ((int) $viewport[1] / 100);
			$props['data-lax-scale'] = $fields_args['motion_effects']['s']['val']['s_dir'] === 'up' ? '(vh*' . $bottom . ') 1,(vh*' . $top . ') ' . $ratio : '(vh*' . $bottom . ') 1,(vh*' . $top . ') ' . number_format(1/$ratio, 3);
			if ( isset( $fields_args['motion_effects']['s']['val']['s_origin'] ) ) {
				$props['data-box-position'] = self::map_transform_origin($fields_args['motion_effects']['s']['val']['s_origin']);
			}
	    }
	}
	if (isset($fields_args['custom_parallax_scroll_zindex']) && $fields_args['custom_parallax_scroll_zindex'] !== '') {
	    $zIndex = 'z-index:' . (int) $fields_args['custom_parallax_scroll_zindex'] . ';';
	    if (isset($props['style'])) {
		$props['style'].=$zIndex;
	    } else {
		$props['style'] = $zIndex;
	    }
	}
	if (isset($fields_args['custom_css_id'])){
	    $props['id'] = $fields_args['custom_css_id'];
	}
	return $props;
    }
    
	/**
     * Map animation speed parameter and returns new speed
     *
	 * @param string $val Initial speed value
	 * @param string $attr attribute name
	 *
	 * @return float|int Returns speed of element based on initial value
	 */
    private static function map_animation_speed( $val, $attr = 'no' ) {
	    switch ( $val ) {
		    case 10:
                $speed = ($attr === 'blur') ? 20 : 670;
			    break;
    
		    case 9:
                $speed = ($attr === 'blur') ? 18 : 600;
			    break;
    
		    case 8:
                $speed = ($attr === 'blur') ? 16 : 530;
			    break;
    
		    case 7:
                $speed = ($attr === 'blur') ? 14 : 460;
			    break;

		    case 6:
                $speed = ($attr === 'blur') ? 12 : 390;
			    break;

		    case 4:
                $speed = ($attr === 'blur') ? 8 : 250;
			    break;

		    case 3:
                $speed = ($attr === 'blur') ? 6 : 200;
			    break;

		    case 2:
                $speed = ($attr === 'blur') ? 4 : 140;
			    break;

		    case 1:
			    $speed = ($attr === 'blur') ? 2 : 70;
			    break;

		    default:
                $speed = ($attr === 'blur') ? 10 : 320;
	    }

	    return $speed;
    }

    /**
	 * Map initial origin value and returns transform origin property
	 *
	 * @param string $props Initial origin value
	 *
	 * @return string Returns transform origin value of element based on initial value
	 */
	private static function map_transform_origin( $props ) {
		switch ( $props ) {
			case '0,0':
				$output = 'top left';
				break;

			case '50,0':
				$output = 'top center';
				break;

			case '100,0':
				$output = 'top right';
				break;

			case '0,50':
				$output = 'left center';
				break;

			case '50,50':
				$output = 'center center';
				break;

			case '100,50':
				$output = 'right center';
				break;

			case '0,100':
				$output = 'bottom left';
				break;

			case '50,100':
				$output = 'bottom center';
				break;

			case '100,100':
				$output = 'bottom right';
				break;

			default:
			    $perc = explode(',', $props);
				$output = $perc[0] . '% ' . $perc[1] . '%';
		}

		return $output;
	}

    
    /**
     * Computes and returns the HTML a color overlay.
     *
     * @since 2.3.3
     *
     * @param array $styling The row's or column's styling array.
     *
     * @return bool Returns false if $styling doesn't have a color overlay. Otherwise outputs the HTML;
     */
	private static function do_color_overlay($styling) {

		$type = !isset( $styling['cover_color-type'] ) || $styling['cover_color-type'] === 'color' ? 'color' : 'gradient';
		$is_empty = $type === 'color' ? empty( $styling['cover_color'] ) : empty( $styling['cover_gradient-gradient'] );

		if ( $is_empty === true ) {
			$hover_type = !isset( $styling['cover_color_hover-type'] ) || $styling['cover_color_hover-type'] === 'hover_color' ? 'color' : 'gradient';
			$is_empty_hover = $hover_type === 'color' ? empty( $styling['cover_color_hover'] ) : empty( $styling['cover_gradient_hover-gradient'] );
		}
		if ( $is_empty === false || $is_empty_hover === false) {
			echo '<div class="builder_row_cover"></div>';
			return true;
		}
		return false;
	}


	protected static function show_frame( $styles, $printed=array()) {
		$breakpoints = array( 'desktop' => '' ) + themify_get_breakpoints();
		$sides=array( 'top', 'bottom', 'left', 'right' );
		foreach ( $sides as $side ) {
		    if(!isset($printed[$side])){
				foreach ( $breakpoints as $bp => $v ) {
					$settings = 'desktop' === $bp ? $styles : ( !empty( $styles[ 'breakpoint_' . $bp ] ) ? $styles[ 'breakpoint_' . $bp ] : array() );
					if ( !empty( $settings ) && Themify_Builder_Model::get_frame( $settings, $side ) ) {
						$printed[$side] = true;
						?>
						<div class="tb_row_frame tb_row_frame_<?php echo $side; ?> <?php if ( isset( $settings["{$side}-frame_location"] ) && $settings["{$side}-frame_location"] === 'in_front' ) {
							echo $settings["{$side}-frame_location"];
						} ?>"></div>
						<?php
						break;
					}
				}
		   }
		}
		return $printed;
	}
    /**
     * Computes and returns the HTML for a background slider.
     *
     * @since 2.3.3
     *
     * @param array  $row_or_col   Row or column definition.
     * @param string $order        Order of row/column (e.g. 0 or 0-1-0-1 for sub columns)
     * @param string $type Accepts 'row', 'col', 'sub-col'
     *
     * @return bool Returns false if $row_or_col doesn't have a bg slider. Otherwise outputs the HTML for the slider.
     */
    public static function do_slider_background($row_or_col, $type = 'row') {
	if (!isset($row_or_col['styling']['background_type']) || 'slider' !== $row_or_col['styling']['background_type'] || empty($row_or_col['styling']['background_slider'])) {
	    return false;
	}
	$images = Themify_Builder_Model::get_images_from_gallery_shortcode($row_or_col['styling']['background_slider']);
	if (!empty($images)) :
	    
	    $size = isset($row_or_col['styling']['background_slider_size']) ? $row_or_col['styling']['background_slider_size'] : false;
	    if (!$size) {
		$size = Themify_Builder_Model::get_gallery_param_option($row_or_col['styling']['background_slider'], 'size');
		if (!$size)
		    $size = 'large';
	    }
	    $bgmode = !empty($row_or_col['styling']['background_slider_mode']) ? $row_or_col['styling']['background_slider_mode'] : 'fullcover';
	    $slider_speed = !empty($row_or_col['styling']['background_slider_speed']) ?$row_or_col['styling']['background_slider_speed'] : '2000';
	    ?>
	    <div class="<?php echo $type; ?>-slider tb_slider" data-bgmode="<?php echo $bgmode; ?>" data-sliderspeed="<?php echo $slider_speed ?>">
	        <ul class="row-slider-slides clearfix">
		    <?php
		    foreach($images as $i=>$img) {
			$img_data = wp_get_attachment_image_src($img->ID, $size);
			?>
			<li data-bg="<?php echo esc_url(themify_https_esc($img_data[0])); ?>" class="normal">
			    <a class="row-slider-dot" data-index="<?php echo $i; ?>"></a>
			</li>
			<?php
		    }
		    ?>
	        </ul>
	        <div class="row-slider-nav">
	    	<a class="row-slider-arrow row-slider-prev">&lsaquo;</a>
	    	<a class="row-slider-arrow row-slider-next">&rsaquo;</a>
	        </div>
	    </div>
	    <!-- /.row-bgs -->
	    <?php
		return true;
	endif; // images
	return false;
    }

    public static function background_styling($row, $type,$builder_id) {
	// Background cover color
		if (!empty($row['styling'])) {
			$hasOverlay=false;
			if(!self::do_color_overlay($row['styling'])){
				$breakpoints = themify_get_breakpoints();
				foreach ($breakpoints as $bp => $v) {
				    if (!empty($row['styling']['breakpoint_' . $bp]) && self::do_color_overlay($row['styling']['breakpoint_' . $bp])) {
					$hasOverlay=true;
					break;
				    }
				}	
			}
			else{
			    $hasOverlay=true;
			}
			// Background Slider
			self::do_slider_background($row, $type);	
			$frames=array();
			$framesCount=0;
			if(!empty($row['styling']['global_styles'])){	
				$used_gs=Themify_Global_Styles::get_used_gs($builder_id);
				if(!empty($used_gs)){
					$global_styles = explode(' ',$row['styling']['global_styles']);
					if($hasOverlay===false){
						$breakpoints =array('desktop'=>'')+ themify_get_breakpoints();
					}
					foreach ($global_styles as $cl){
						if(isset($used_gs[$cl])){
							if($hasOverlay===false){
								foreach ($breakpoints as $bp => $v) {
								
									if(($bp==='desktop' && self::do_color_overlay($used_gs[$cl])) || ($bp!=='desktop' && !empty($used_gs[$cl]['breakpoint_'.$bp]) && self::do_color_overlay($used_gs[$cl]['breakpoint_'.$bp]))){
										$hasOverlay=true;
										break;
									}
								}	
							}
							if($framesCount!==4){
								$frames=self::show_frame($used_gs[$cl],$frames);
								$framesCount=count($frames);
							}
						}
						if($hasOverlay===true && $framesCount===4){
							break;
						}
					}
				}
			}
			if($framesCount!==4){
				self::show_frame($row['styling'],$frames);
			}
		}
    }
    
	// Get Height Options plus Auto Height
    protected static function get_height($selector = '', $id = 'ht') {
		return array(
			'id' => $id,
			'label' => 'ht',
			'type' => 'height',
			'selector' => $selector
		);
	}

	// Get Min Height Option
	protected static function get_min_height($selector = '', $id = 'mi_h') {
		return array(
			'id' => $id,
			'type' => 'range',
			'label' => 'm_ht',
			'prop' => 'min-height',
			'selector' => $selector,
			'units' => array(
				'px' => array(
					'min' => 0,
					'max' => 2000
				),
				'vh' => array(
					'min' => 0,
					'max' => 100
				),
				'%' => array(
					'min' => 0,
					'max' => 100
				),
				'em' => array(
					'min' => 0,
					'max' => 200
				)
			)
		);
	}

	// Get Max Height Option
	protected static function get_max_height($selector = '', $id = 'mx_h') {
		return array(
			'id' => $id,
			'type' => 'range',
			'label' => 'mx_ht',
			'prop' => 'max-height',
			'selector' => $selector,
			'units' => array(
				'px' => array(
					'min' => 0,
					'max' => 2000
				),
				'vh' => array(
					'min' => 0,
					'max' => 100
				),
				'%' => array(
					'min' => 0,
					'max' => 100
				),
				'em' => array(
					'min' => 0,
					'max' => 200
				)
			)
		);
	}

	// Get Rounded Corners
	protected static function get_border_radius($selector = '', $id = 'b_ra', $state = '') {
		if ($state !== '') {
			$id .= '_' . $state;
		}
		$res = array(
			'id' => $id,
			'type' => 'border_radius',
			'label' => 'bo_r',
			'wrap_class' => 'border-radius-options',
			'prop' => 'border-radius',
			'selector' => $selector,
			'border_radius'=>true
		);
		if ($state === 'h' || $state === 'hover') {
			$res['ishover'] = true;
		}
		return $res;
	}

	// Get Box Shadow
	protected static function get_box_shadow($selector = '', $id = 'b_sh', $state = '') {
		if ($state !== '') {
			$id .= '_' . $state;
		}
		$res = array(
			'id' => $id,
			'type' => 'box_shadow',
			'label' => 'b_s',
			'prop' => 'box-shadow',
			'selector' => $selector
		);
		if ($state === 'h' || $state === 'hover') {
			$res['ishover'] = true;
		}
		return $res;
	}

	// Get Text Shadow
	protected static function get_text_shadow($selector = '', $id = 'text-shadow', $state = '') {
		if ($state !== '') {
			$id .= '_' . $state;
		}
		$res = array(
			'id' => $id,
			'type' => 'text_shadow',
			'label' => 't_sh',
			'prop' => 'text-shadow',
			'selector' => $selector
		);
		if ($state === 'h' || $state === 'hover') {
			$res['ishover'] = true;
		}
		return $res;
	}
	
	private static function setDraggables($cl,$type,$id,$selector){
	    $slug = '';
	    $isModule = strpos($cl,'Themify_Builder_Component_')!==0;
	    $slug = $isModule===true?str_replace(array('TB_','_Module'), array('',''), $cl):str_replace('Themify_Builder_Component_', '', $cl);
	    $slug = strtolower($slug);
	    if(!isset(self::$draggableModules[$slug])){
		if($isModule===true && !isset(Themify_Builder_Model::$modules[$slug])){
		    foreach(Themify_Builder_Model::$modules as $k=>$m){	
			if($cl===get_class($m)){
			    $slug=$k;
			    break;
			}
		    }
		}
		if(!isset(self::$draggableModules[$slug])){
		    self::$draggableModules[$slug] = array();
		}
	    }
	    if(!isset(self::$draggableModules[$slug][$type])){
		self::$draggableModules[$slug][$type] = array();
	    }
	    self::$draggableModules[$slug][$type][$id] = $selector;
	}

	// Get z-index
	protected static function get_zindex($selector = '', $id = 'zi') {
		return array(
			'id' => $id,
			'label' => 'zi',
			'prop' => 'z-index',
			'selector' => $selector,
			'type' => 'number',
            'help' => __('Module with greater stack order is always in front of an module with a lower stack order', 'themify')
		);
	}

	protected static function get_min_width($selector = '', $id = 'mi_w') {
		return array(
			'id' => $id,
			'type' => 'range',
			'label' => 'mi_wd',
			'prop' => 'min-width',
			'selector' => $selector,
			'units' => array(
				'px' => array(
					'min' => 0,
					'max' => 2000
				),
				'%' => array(
					'min' => 0,
					'max' => 100
				),
				'em' => array(
					'min' => 0,
					'max' => 200
				)
			)
		);
}

	protected static function get_max_width($selector = '', $id = 'ma_w') {
		return array(
			'id' => $id,
			'type' => 'range',
			'label' => 'ma_wd',
			'prop' => 'max-width',
			'selector' => $selector,
			'units' => array(
				'px' => array(
					'min' => 0,
					'max' => 2000
				),
				'%' => array(
					'min' => 0,
					'max' => 100
				),
				'em' => array(
					'min' => 0,
					'max' => 200
				)
			)
		);
	}

	// Get CSS Position
	protected static function get_css_position($selector = '', $id = 'po', $state = '') {
		if ($state !== '') {
			$id .= '_' . $state;
}
		$res = array(
			'id' => $id,
			'type' => 'position',
			'label' => 'po',
			'prop' => 'position',
			'selector' => $selector
		);
		if ($state === 'h' || $state === 'hover') {
			$res['ishover'] = true;
		}
		return $res;
	}

	// CSS Display
	protected static function get_display( $selector = '', $id = 'disp' ) {
		$va_id = $id . '_va';
		$res = array();
		$res[] = array( 'id' => $id,
			'label' => 'disp',
			'type' => 'select',
			'prop' => 'display',
			'selector' => $selector,
			'binding' => array(
				'empty' => array('hide' => array($va_id)),
				'block' => array('hide' => array($va_id)),
				'none' => array('hide' => array($va_id)),
				'inline-block' => array('show' => array($va_id))
			),
			'display'=>true
		);
		$res[] = array( 'id' => $va_id,
			'label' => __('Vertical Align','themify'),
			'type' => 'select',
			'prop' => 'vertical-align',
			'selector' => $selector,
			'origID' => $id,
			'va_display' => true
		);
		return $res;
	}
}
