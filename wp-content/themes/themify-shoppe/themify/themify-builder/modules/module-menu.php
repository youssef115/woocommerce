<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Module Name: Menu
 * Description: Display Custom Menu
 */

class TB_Menu_Module extends Themify_Builder_Component_Module {

    function __construct() {
	parent::__construct(array(
	    'name' => __('Menu', 'themify'),
	    'slug' => 'menu',
	    'category' => array('general','site')
	));
    }

    public function get_title($module) {
	return isset($module['mod_settings']['custom_menu']) ? $module['mod_settings']['custom_menu'] : '';
    }

    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title_menu',
		'type' => 'title'
	    ),
	    array(
		'id' => 'layout_menu',
		'type' => 'layout',
		'label' => __('Menu Layout', 'themify'),
		'mode' => 'sprite',
		'options' => array(
		    array('img' => 'menu_bar', 'value' => 'menu-bar', 'label' => __('Menu Bar', 'themify')),
		    array('img' => 'menu_fullbar', 'value' => 'fullwidth', 'label' => __('Menu Fullbar', 'themify')),
		    array('img' => 'menu_vertical', 'value' => 'vertical', 'label' => __('Menu Vertical', 'themify'))
		)
	    ),
			array(
				'id' => 'alignment',
				'label' => __( 'Alignment', 'themify' ),
				'type' => 'icon_radio',
				'options' => array(
					array( 'value' => 'left', 'name' => __( 'Left', 'themify' ), 'icon' => '<span class="ti-align-left"></span>' ),
					array( 'value' => 'center', 'name' => __( 'Center', 'themify' ), 'icon' => '<span class="ti-align-center"></span>' ),
					array( 'value' => 'right', 'name' => __( 'Right', 'themify' ), 'icon' => '<span class="ti-align-right"></span>' )
				),
			),
	    array(
		'id' => 'custom_menu',
		'type' => 'select_menu',
		'label' => __('Custom Menu', 'themify')
	    ),
	    array(
		'id' => 'allow_menu_fallback',
		'label' => '',
		'type' => 'checkbox',
		'options' => array(
		    array('name' => 'allow_fallback', 'value' => __('If no menu found, list all pages', 'themify'))
		)
	    ),
	    array(
		'id' => 'allow_menu_breakpoint',
		'label' =>  __('Mobile Menu', 'themify'),
		'type' => 'toggle_switch',
		'options' => array(
		    'on' => array('name'=>'allow_menu','value' =>'en'),
		    'off' => array('name'=>'', 'value' =>'dis')
		),
		'binding' => array(
			'checked' => array(
				'show' => array('menu_breakpoint', 'menu_slide_direction', 'mobile_menu_style')
			),
			'not_checked' => array(
				'hide' => array('menu_breakpoint', 'menu_slide_direction', 'mobile_menu_style')
			)
		)
	    ),
	    array(
			'id' => 'menu_breakpoint',
			'label' => '',
			'type' => 'number',
			'after' => __('Breakpoint (px)', 'themify'),
			'binding' => array(
				'empty' => array(
				'hide' => array('menu_slide_direction')
				),
				'not_empty' => array(
				'show' => array('menu_slide_direction')
				)
			),
			'wrap_class' => 'tb-checkbox_element_allow_menu'
	    ),
	    array(
				'id'         => 'mobile_menu_style',
			'label' => '',
			'type' => 'select',
				'after'      => __( 'Mobile Menu Style', 'themify' ),
			'options' => array(
					'slide'    => __( 'Slide', 'themify' ),
					'overlay'  => __( 'Overlay', 'themify' ),
					'dropdown' => __( 'Dropdown', 'themify' )
			),
			'wrap_class' => 'tb-checkbox_element_allow_menu'
	    ),
	    array(
				'id'         => 'menu_slide_direction',
			'label' => '',
			'type' => 'select',
				'after'      => __( 'Mobile slide direction', 'themify' ),
			'options' => array(
					'right' => __( 'Right', 'themify' ),
					'left'  => __( 'Left', 'themify' )
			),
			'wrap_class' => 'tb-checkbox_element_allow_menu'
	    ),
	    array(
		'id' => 'color_menu',
		'type' => 'layout',
		'label' => __('Color', 'themify'),
		'class' => 'tb_colors',
		'mode' => 'sprite',
		'color' => true,
		'transparent'=>true
	    ),
	    array(
		'id' => 'according_style_menu',
		'type' => 'checkbox',
		'label' => __('Appearance', 'themify'),
		'appearance' => true
	    ),
	    array(
		'id' => 'css_menu',
		'type' => 'custom_css'
	    ),
	    array('type' => 'custom_css_id')
	);
    }

	public function get_default_settings() {
		return array(
			'custom_menu' => 'default',
			'mobile_menu_style' => 'slide',
			'color_menu' => 'tb_default_color'
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
			    self::get_color(' .nav', 'background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .nav', 'bg_c', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .nav li'),
			    self::get_color(' .nav li a', 'font_color'),
			    self::get_font_size(' .nav li'),
			    self::get_line_height(' .nav li'),
			    self::get_letter_spacing(' .nav li'),
			    self::get_text_align(' .nav'),
			    self::get_text_transform(' .nav li'),
			    self::get_font_style(' .nav li'),
			    self::get_text_shadow(' .nav li'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .nav li', 'f_f', 'h'),
			    self::get_color(' .nav li a', 'f_c',  null, null, 'h'),
			    self::get_font_size(' .nav li', 'f_s', '', 'h'),
			    self::get_line_height(' .nav li', 'l_h', 'h'),
			    self::get_letter_spacing(' .nav li', 'l_s', 'h'),
			    self::get_text_align(' .nav', 't_a', 'h'),
			    self::get_text_transform(' .nav li', 't_t', 'h'),
			    self::get_font_style(' .nav li:hover', 'f_st_h', 'f_w_h', 'h'),
			    self::get_text_shadow(' .nav li','t_sh','h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding('')
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
			    self::get_border('')
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
	    // Width
	    self::get_expand('w', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_width('', 'w')
					)
				),
				'h' => array(
					'options' => array(
						self::get_width('', 'w', 'h')
					)
				)
			))
	    )),
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
		// Position
		self::get_expand('po', array( self::get_css_position())),
		// Display
		self::get_expand('disp', self::get_display())
	);

	$menu_links = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .nav > li > a', 'link_background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .nav > li > a', 'link_background_color', 'bg_c', 'background-color','hover')
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('.module .nav > li > a', 'link_color'),
			    self::get_text_decoration('.module .nav > li > a')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('.module .nav > li > a', 'link_color',null, null, 'hover'),
			    self::get_text_decoration('.module .nav > li > a', 't_d', 'h')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .nav > li > a', 'p_m_l')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .nav > li > a', 'p_m_l', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .nav > li > a', 'm_m_l')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .nav > li > a', 'm_m_l', 'h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .nav > li > a', 'b_m_l')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .nav > li > a', 'b_m_l', 'h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_border_radius(' .nav > li > a', 'r_c_m_l')
					)
				),
				'h' => array(
					'options' => array(
						self::get_border_radius(' .nav > li > a', 'r_c_m_l', 'h')
					)
				)
			))
		)),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' .nav > li > a', 'sh_m_l')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' .nav > li > a', 'sh_m_l', 'h')
					)
				)
			))
		)),
	);

	$current_menu_links = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' li.current_page_item > a,  li.current-menu-item > a', 'current-links_background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' li.current_page_item > a:hover,  li.current-menu-item > a:hover', 'current-links_hover_background_color', 'bg_c', 'background-color')
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' li.current_page_item > a,  li.current-menu-item > a', 'current-links_color'),
			    self::get_text_decoration(' li.current_page_item a,  li.current-menu-item a', 'current-links_text_decoration')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' li.current_page_item > a:hover,  li.current-menu-item > a:hover', 'current-links_color_hover'),
			    self::get_text_decoration(' li.current_page_item a,  li.current-menu-item a', 'c-l_t_d','h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' li.current_page_item a,  li.current-menu-item a', 'b_m_c_l')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' li.current_page_item a,  li.current-menu-item a', 'b_m_c_l', 'h')
				)
				)
			))
	    )),
		// Shadow
		self::get_expand('sh', array(
			self::get_tab(array(
				'n' => array(
					'options' => array(
						self::get_box_shadow(' li.current_page_item > a,  li.current-menu-item > a', 'sh_m_c_l')
					)
				),
				'h' => array(
					'options' => array(
						self::get_box_shadow(' li.current_page_item > a,  li.current-menu-item > a', 'sh_m_c_l', 'h')
					)
				)
			))
		)),
	);

	$menu_dropdown_links = array(
		// Container Background
	    self::get_expand('Container', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' li > ul', 'd_l_ctn_b_c', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' li > ul:hover', 'd_l_ctn_b_c_h', __('Background Hover', 'themify'), 'background-color')
				)
				)
			))
	    )),
		// Background
	    self::get_expand('bg', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' li > ul a', 'dropdown_links_background_color', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' li > ul a:hover', 'dropdown_links_hover_background_color', __('Background Hover', 'themify'), 'background-color')
				)
				)
			))
	    )),
	    // Font
	    self::get_expand('f', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_font_family(' .nav li > ul a', 'font_family_menu_dropdown_links'),
					self::get_color(' .nav li > ul a', 'dropdown_links_color'),
					self::get_font_size(' .nav li > ul a', 'font_size_menu_dropdown_links'),
					self::get_line_height(' .nav li > ul a', 'l_h_m_d_l'),
					self::get_letter_spacing(' .nav li > ul a', 'l_s_m_d_l'),
					self::get_text_align(' .nav li > ul a', 't_a_m_d_l'),
					self::get_text_transform(' .nav li > ul a', 't_t_m_d_l'),
					self::get_font_style(' .nav li > ul a', 'f_d_l', 'f_d_b'),
					self::get_text_decoration(' .nav li > ul a', 't_d_m_d_l'),
					self::get_text_shadow(' .nav li > ul a', 't_sh_l'),
				)
				),
				'h' => array(
				'options' => array(
					self::get_font_family(' .nav li > ul a', 'f_f_m_d_l', 'h'),
					self::get_color(' .nav li > ul a:hover', 'dropdown_links_hover_color', __('Color Hover', 'themify')),
					self::get_font_size(' .nav li > ul a', 'f_s_m_d_l', '', null,null, 'h'),
					self::get_line_height(' .nav li > ul a', 'l_h_m_d_l', 'h'),
					self::get_letter_spacing(' .nav li > ul a', 'l_s_m_d_l', 'h'),
					self::get_text_align(' .nav li > ul a', 't_a_m_d_l', 'h'),
					self::get_text_transform(' .nav li > ul a', 't_t_m_d_l', 'h'),
					self::get_font_style(' .nav li > ul a', 'f_d_l', 'f_d_b', 'h'),
					self::get_text_decoration(' .nav li > ul a', 't_d_m_d_l', 'h'),
					self::get_text_shadow(' .nav li > ul a', 't_sh_l', 'h'),
				)
				)
			))
	    )),
		// Padding
		self::get_expand('p', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_padding(' li > ul a', 'd_l_p')
				)
				),
				'h' => array(
				'options' => array(
					self::get_padding(' li > ul a', 'd_l_p_h', 'h')
				)
				)
			))
		)),
	    // Margin
	    self::get_expand('m', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_margin(' li > ul a', 'd_l_m')
				)
				),
				'h' => array(
				'options' => array(
					self::get_margin(' li > ul a', 'd_l_m_h', 'h')
				)
				)
			))
	    )),
	    // Border
	    self::get_expand('b', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_border(' li > ul a', 'd_l_b')
				)
				),
				'h' => array(
				'options' => array(
					self::get_border(' li > ul a', 'd_l_b_h', 'h')
				)
				)
			))
	    ))
	);

	$menu_mobile = array(
	    // Background
	    self::get_expand('Panel', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color('.mobile-menu-module', 'mobile_menu_background_color', 'bg_c', 'background-color'),
					self::get_padding('.mobile-menu-module', 'p_m_m_ct'),
					self::get_border('.mobile-menu-module', 'b_m_m_ct'),
					self::get_box_shadow('.mobile-menu-module', 'sh_m_m_ct'),
					self::get_width('.mobile-menu-module', 'wh_m_m_ct')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color('.mobile-menu-module', 'm_m_b_c', 'bg_c', 'background-color', null, 'h'),
					self::get_padding('.mobile-menu-module', 'p_m_m_ct', 'h'),
					self::get_border('.mobile-menu-module', 'b_m_m_ct', 'h'),
					self::get_box_shadow('.mobile-menu-module', 'sh_m_m_ct', 'h'),
					self::get_width('.mobile-menu-module', 'wh_m_m_ct', 'h')
				)
				)
			))
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family('.mobile-menu-module li a', 'f_f_m_m'),
			    self::get_color('.mobile-menu-module li a', 'm_c_m_m'),
			    self::get_font_size('.mobile-menu-module li a', 'f_s_m_m'),
			    self::get_line_height('.mobile-menu-module li a', 'l_h_m_m'),
			    self::get_letter_spacing('.mobile-menu-module li a', 'l_s_m_m'),
			    self::get_text_align('.mobile-menu-module li a', 't_a_m_m'),
			    self::get_text_transform('.mobile-menu-module li a', 't_t_m_m'),
			    self::get_font_style('.mobile-menu-module li a', 'f_sy_m_m', 'f_b_m_m'),
			    self::get_text_decoration('.mobile-menu-module li a', 't_d_m_m'),
				self::get_text_shadow('.mobile-menu-module li a', 't_sh_m'),
			    self::get_color('.mobile-menu-module li a', 'b_c_m_m', 'bg_c', 'background-color'),
				self::get_padding('.mobile-menu-module li a', 'p_m_m'),
				self::get_margin('.mobile-menu-module li a', 'm_m_m'),
				self::get_border('.mobile-menu-module li a', 'b_m_m')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('.mobile-menu-module li a', 'f_f_m_m', 'h'),
			    self::get_color('.mobile-menu-module li a', 'm_c_h_m_m',null, null, 'h'),
			    self::get_font_size('.mobile-menu-module li a', 'f_s_m_m', 'h'),
			    self::get_line_height('.mobile-menu-module li a', 'l_h_m_m', 'h'),
			    self::get_letter_spacing('.mobile-menu-module li a', 'l_s_m_m', 'h'),
			    self::get_text_align('.mobile-menu-module li a', 't_a_m_m', 'h'),
			    self::get_text_transform('.mobile-menu-module li a', 't_t_m_m', 'h'),
			    self::get_font_style('.mobile-menu-module li a', 'f_sy_m_m', 'f_b_m_m', 'h'),
			    self::get_text_decoration('.mobile-menu-module li a', 't_d_m_m', 'h'),
				self::get_text_shadow('.mobile-menu-module li a', 't_sh_m','h'),
				self::get_color('.mobile-menu-module li a:hover', 'b_c_m_m_h', 'bg_c', 'background-color', null, 'h'),
				self::get_padding('.mobile-menu-module li a', 'p_m_m', 'h'),
				self::get_margin('.mobile-menu-module li a', 'm_m_m', 'h'),
				self::get_border('.mobile-menu-module li a', 'b_m_m', 'h')
			)
		    )
		))
	    )),
	    // Overlay
	    self::get_expand('Overlay', array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .body-overlay', 'b_c_m_m_o', 'bg_c', 'background-color')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .body-overlay:hover', 'b_c_m_m_o', 'bg_c', 'background-color', null, 'h')
				)
				)
			))
	    )),
	    // Burger Icon
	    self::get_expand(__('Burger Icon', 'themify'), array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color(' .menu-module-burger', 'b_c_m_m_i', 'bg_c', 'background-color'),
					self::get_color(' .menu-module-burger', 'c_m_m_i'),
					self::get_padding(' .menu-module-burger', 'p_m_m_i'),
					self::get_margin(' .menu-module-burger', 'm_m_m_i'),
					self::get_border(' .menu-module-burger', 'b_m_m_i'),
					self::get_width(array(' .menu-module-burger', ' .menu-module-burger-inner'), 'w_m_m_i'),
					self::get_height(' .menu-module-burger-inner', 'h_m_m_i')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color(' .menu-module-burger:hover', 'b_c_m_m_i_h', 'bg_c', 'background-color', null, 'h'),
					self::get_color(' .menu-module-burger', 'c_m_m_i_h',null, null, 'h'),
					self::get_padding(' .menu-module-burger', 'p_m_m_i', 'h'),
					self::get_margin(' .menu-module-burger', 'm_m_m_i', 'h'),
					self::get_border(' .menu-module-burger', 'b_m_m_i', 'h'),
					self::get_width(array(' .menu-module-burger:hover', ' .menu-module-burger:hover .menu-module-burger-inner'), 'w_m_m_i_h', null,null, ''),
					self::get_height(' .menu-module-burger-inner', 'h_m_m_i', 'h')
				)
				)
			)),
		)),
	    // Close Button
	    self::get_expand(__('Close Button', 'themify'), array(
			self::get_tab(array(
				'n' => array(
				'options' => array(
					self::get_color('.mobile-menu-module .menu-close', 'b_c_m_m_cb', 'bg_c', 'background-color'),
					self::get_color('.mobile-menu-module .menu-close', 'c_m_m_cb'),
					self::get_padding('.mobile-menu-module .menu-close', 'p_m_m_cb'),
					self::get_margin('.mobile-menu-module .menu-close', 'm_m_m_cb'),
					self::get_border('.mobile-menu-module .menu-close', 'b_m_m_cb'),
					self::get_width(array('.mobile-menu-module .menu-close'), 'w_m_m_cb'),
					self::get_height('.mobile-menu-module .menu-close', 'h_m_m_cb'),
					self::get_border_radius('.mobile-menu-module .menu-close', 'r_c_m_m_cb'),
					self::get_box_shadow('.mobile-menu-module .menu-close', 'sh_m_m_cb')
				)
				),
				'h' => array(
				'options' => array(
					self::get_color('.mobile-menu-module .menu-close:hover', 'b_c_m_m_cb_h', 'bg_c', 'background-color', null, 'h'),
					self::get_color('.mobile-menu-module .menu-close', 'c_m_m_cb_h',null, null, 'h'),
					self::get_padding('.mobile-menu-module .menu-close', 'p_m_m_cb', 'h'),
					self::get_margin('.mobile-menu-module .menu-close', 'm_m_m_cb', 'h'),
					self::get_border('.mobile-menu-module .menu-close', 'b_m_m_cb', 'h'),
					self::get_width(array('.mobile-menu-module .menu-close'), 'w_m_m_cb_h', null,null, ''),
					self::get_height('.mobile-menu-module .menu-close', 'h_m_m_cb', 'h'),
					self::get_border_radius('.mobile-menu-module .menu-close', 'r_c_m_m_cb', 'h'),
					self::get_box_shadow('.mobile-menu-module .menu-close', 'sh_m_m_cb', 'h')
				)
				)
			)),
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
		'l' => array(
		    'label' => __('Menu Links', 'themify'),
		    'options' => $menu_links
		),
		'c' => array(
		    'label' => __('Current Links', 'themify'),
		    'options' => $current_menu_links
		),
		'dl' => array(
		    'label' => __('Dropdown Links', 'themify'),
		    'options' => $menu_dropdown_links
		),
		'm' => array(
		    'label' => __('Mobile Menu', 'themify'),
		    'options' => $menu_mobile
		)
	    )
	);
    }

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module('TB_Menu_Module');
