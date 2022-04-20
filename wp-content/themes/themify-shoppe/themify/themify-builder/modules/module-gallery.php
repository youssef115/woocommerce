<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Module Name: Gallery
 * Description: Display WP Gallery Images
 */

class TB_Gallery_Module extends Themify_Builder_Component_Module {

    function __construct() {
        parent::__construct(array(
            'name' => __('Gallery', 'themify'),
            'slug' => 'gallery'
        ));
    }

    public function get_options() {
        $is_img_enabled = Themify_Builder_Model::is_img_php_disabled();
        return array(
            array(
                'id' => 'mod_title_gallery',
                'type' => 'title'
            ),
            array(
                'id' => 'layout_gallery',
                'type' => 'radio',
                'label' => __('Gallery Layout', 'themify'),
                'options' => array(
		    array('value'=>'grid','name'=>__('Grid', 'themify')),
		    array('value'=>'showcase','name'=>__('Showcase', 'themify')),
		    array('value'=>'lightboxed','name'=>__('Lightboxed', 'themify')),
		    array('value'=>'slider','name'=>__('Slider', 'themify'))
                ),
		'wrap_class' => 'tb_compact_radios',
                'option_js' => true
            ),
            array(
                'id' => 'layout_masonry',
                'type' => 'toggle_switch',
                'label' => __('Masonry', 'themify'),
                'options' => array(
                    'on'=>array('name' => 'masonry', 'value' =>'en'),
		    'off'=>array('name' => '', 'value' => 'dis')
                ),
                'wrap_class' => 'tb_group_element_grid'
            ),
            array(
                'id' => 'thumbnail_gallery',
                'type' => 'image',
                'label' => __('Thumbnail', 'themify'),
                'class' => 'large',
                'wrap_class' => 'tb_group_element_lightboxed'
            ),
            array(
                'id' => 'shortcode_gallery',
                'type' => 'gallery',
                'label' => __('Gallery Shortcode', 'themify')
            ),
            array(
                'id' => 'gallery_pagination',
                'label' => __('Pagination', 'themify'),
		'type' => 'toggle_switch',
		'options' => array(
		    'on'=>array('name'=>'pagination', 'value' =>'en'),
		    'off'=>array('name'=>'', 'value' =>'dis')
		),
		'binding' => array(
			'checked' => array(
				'show' => array('gallery_per_page')
			),
			'not_checked' => array(
				'hide' => array('gallery_per_page')
			)
		),
		'wrap_class' => 'tb_group_element_grid'
            ),
            array(
                'id' => 'gallery_per_page',
                'type' => 'number',
                'label' => __('Images Per Page', 'themify'),
                'wrap_class' => 'tb_group_element_grid tb-checkbox_element_pagination'
            ),
			array(
				'id' => 'slider_thumbs',
				'type' => 'toggle_switch',
				'label' => __('Show thumbnails', 'themify'),
				'default'=>'on',
				'options' => array(
					'on'=>array('name' => '', 'value' =>'s'),
					'off'=>array('name' => 'yes', 'value' => 'hi')
				),
				'wrap_class' => 'tb_group_element_slider'
			),
            array(
                'id' => 'gallery_image_title',
                'label' => __('Image Title', 'themify'),
		'type' => 'toggle_switch',
		'options' => array(
		    'on'=>array('name'=>'yes', 'value' =>'s'),
		    'off'=>array('name'=>'', 'value' =>'hi')
		)
            ),
            array(
                'id' => 'gallery_exclude_caption',
                'label' => __('Image Caption', 'themify'),
		'type' => 'toggle_switch',
		'default'=>'on',
		'options' => array(
		    'on' => array('name'=>'', 'value' =>'s'),
		    'off' => array('name'=>'yes', 'value' =>'hi')
		)
            ),
            array(
                'id' => 's_image_w_gallery',
                'type' => 'number',
                'label' => __('Image Width', 'themify'),
                'hide' => $is_img_enabled,
                'after' => 'px',
                'wrap_class' => 'tb_group_element_showcase tb_group_element_slider'
            ),
            array(
                'id' => 's_image_h_gallery',
                'type' => 'number',
                'label' => __('Image Height', 'themify'),
                'hide' => $is_img_enabled,
                'after' => 'px',
                'wrap_class' => 'tb_group_element_showcase tb_group_element_slider'
            ),
            array(
                'id' => 's_image_size_gallery',
                'type' => 'select',
                'label' => __('Main Image Size', 'themify'),
                'hide' => !$is_img_enabled,
                'image_size' => true
            ),
            array(
                'id' => 'thumb_w_gallery',
                'type' => 'number',
                'label' => __('Thumbnail Width', 'themify'),
                'hide' => $is_img_enabled,
                'after' => 'px'
            ),
            array(
                'id' => 'thumb_h_gallery',
                'type' => 'number',
                'label' => __('Thumbnail Height', 'themify'),
                'hide' => $is_img_enabled,
                'after' => 'px'
            ),
            array(
                'id' => 'image_size_gallery',
                'type' => 'select',
                'label' => __('Image Size', 'themify'),
                'hide' => !$is_img_enabled,
                'image_size' => true
            ),
            array(
                'id' => 'gallery_columns',
                'type' => 'select',
                'label' => __('Columns', 'themify'),
                'options' => array_combine( range( 1, 9 ), range( 1, 9 ) ),
                'wrap_class' => 'tb_group_element_grid'
            ),
            array(
                'type' => 'slider',
                'label' => __('Slider Options', 'themify'),
                'slider_options' => true,
                'wrap_class' => 'tb_group_element_slider'
            ),
            array(
                'id' => 'link_opt',
                'type' => 'select',
                'label' => __('Link to', 'themify'),
                'options' => array(
                    'post' => __('Attachment Page', 'themify'),
                    'file' => __('Media File', 'themify'),
                    'none' => __('None', 'themify')
                ),
                'wrap_class' => 'tb_group_element_grid tb_group_element_slider',
                'binding' => array(
                    'file' => array('show' => array('link_image_size','lightbox_title')),
                    'post' => array('hide' => array('link_image_size','lightbox_title')),
                    'none' => array('hide' => array('link_image_size','lightbox_title'))
                )
            ),
            array(
                'id' => 'link_image_size',
                'type' => 'select',
                'label' => __('Link to Image Size', 'themify'),
                'image_size' => true,
                'wrap_class' => 'tb_group_element_grid tb_group_element_slider'
            ),
			array(
				'id' => 'lightbox_title',
				'label' => __('Lightbox Image Title', 'themify'),
				'type' => 'toggle_switch',
				'default'=>'on',
				'options' => array(
					'on' => array('name'=>'', 'value' =>'s'),
					'off' => array('name'=>'no', 'value' =>'hi')
				)
			),
            array(
                'id' => 'appearance_gallery',
                'type' => 'checkbox',
                'label' => __('Image Appearance', 'themify'),
		'img_appearance'=>true
            ),
            array(
                'id' => 'css_gallery',
                'type' => 'custom_css'
            ),
            array('type'=>'custom_css_id')
        );
    }

    public function get_default_settings() {
        return array(
	    'auto_scroll_opt_slider'=>'4',
            'gallery_columns' => '4',
	    'visible_opt_slider'=>'4',
	    'show_arrow_slider'=>'yes',
	    'wrap_slider'=>'yes',
	    'pause_on_hover_slider'=>'resume',
            'layout_gallery' => 'grid',
	    'link_image_size'=>'full',
	    'link_opt'=>'file',
            'thumb_w_gallery' => 300,
            'thumb_h_gallery' => 200
        );
    }

    public function get_visual_type() {
        return 'ajax';
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
                        self::get_font_family(),
                        self::get_color_type(array(' .themify_image_title',' .themify_image_caption',' .gallery-showcase-title-text')),
                        self::get_font_size(),
                        self::get_line_height(' .gallery-caption'),
                        self::get_letter_spacing(),
                        self::get_text_align(' .gallery-caption'),
                        self::get_text_transform(),
                        self::get_font_style(),
                        self::get_text_decoration(' .gallery-caption', 'text_decoration_regular'),
			self::get_text_shadow(),
                    )
                ),
                'h' => array(
                    'options' => array(
                        self::get_font_family('', 'f_f', 'h'),
                        self::get_color_type(array(':hover .themify_image_title',':hover .themify_image_caption',':hover .gallery-showcase-title-text'),'','f_c_t_h', 'f_c_h', 'f_g_c_h'),
                        self::get_font_size('', 'f_s', '', 'h'),
                        self::get_line_height(' .gallery-caption', 'l_h', 'h'),
                        self::get_letter_spacing('', 'l_s', 'h'),
                        self::get_text_align(' .gallery-caption', 't_a', 'h'),
                        self::get_text_transform('', 't_t', 'h'),
                        self::get_font_style('', 'f_st', 'f_w', 'h'),
                        self::get_text_decoration(' .gallery-caption', 't_d_r', 'h'),
			self::get_text_shadow('','t_sh','h'),
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
			self::get_color(' a', 'link_color',null, null, 'hover'),
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
		
        $image = array(
			// Background
			self::get_expand('bg', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_color('.module-gallery .gallery-icon img', 'g_i_bg_c', 'bg_c', 'background-color')
					)
					),
					'h' => array(
					'options' => array(
						self::get_color('.module-gallery .gallery-icon img', 'g_i_bg_c', 'bg_c', 'background-color', 'h')
					)
					)
				))
			)),
			// Padding
			self::get_expand('p', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_padding('.module-gallery .gallery-icon img', 'g_i_p')
					)
					),
					'h' => array(
					'options' => array(
						self::get_padding('.module-gallery .gallery-icon img', 'g_i_p', 'h')
					)
					)
				))
			)),
			// Margin
			self::get_expand('m', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_margin('.module-gallery .gallery-icon img', 'g_i_m')
					)
					),
					'h' => array(
					'options' => array(
						self::get_margin('.module-gallery .gallery-icon img', 'g_i_m', 'h')
					)
					)
				))
			)),
			// Border
			self::get_expand('b', array(
				self::get_tab(array(
					'n' => array(
					'options' => array(
						self::get_border('.module-gallery .gallery-icon img', 'g_i_b')
					)
					),
					'h' => array(
					'options' => array(
						self::get_border('.module-gallery .gallery-icon img', 'g_i_b', 'h')
					)
					)
				))
			)),
			// Rounded Corners
			self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius('.module-gallery .gallery-icon img', 'g_i_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius('.module-gallery .gallery-icon img', 'g_i_r_c', 'h')
						)
					)
				))
			)),
			// Shadow
			self::get_expand('sh', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_box_shadow('.module-gallery .gallery-icon img', 'g_i_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow('.module-gallery .gallery-icon img', 'g_i_b_sh', 'h')
						)
					)
				))
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
                'i' => array(
					'label' => __('Gallery Image', 'themify'),
                    'options' => $image
                ),
            )
        );
    }

    /**
     * Render plain content for static content.
     * 
     * @param array $module 
     * @return string
     */
    public function get_plain_content($module) {
        $mod_settings = wp_parse_args($module['mod_settings'], array(
            'mod_title_gallery' => '',
            'shortcode_gallery' => ''
        ));
        $text = '' !== $mod_settings['mod_title_gallery'] ? sprintf('<h3>%s</h3>', $mod_settings['mod_title_gallery']) : '';
        $text .= $mod_settings['shortcode_gallery'];
        return $text;
    }

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module('TB_Gallery_Module');
