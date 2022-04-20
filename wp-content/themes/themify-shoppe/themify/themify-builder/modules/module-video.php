<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Module Name: Video
 * Description: Display Video content
 */

class TB_Video_Module extends Themify_Builder_Component_Module {

    function __construct() {
	self::$texts['title_video'] = __('Video Title', 'themify');
	self::$texts['caption_video'] = __('Video Caption', 'themify');
	parent::__construct(array(
	    'name' => __('Video', 'themify'),
	    'slug' => 'video'
	));
    }

    public function get_title($module) {
	return isset($module['mod_settings']['title_video']) ? esc_html($module['mod_settings']['title_video']) : '';
    }

    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title_video',
		'type' => 'title'
	    ),
	    array(
		'id' => 'style_video',
		'type' => 'layout',
		'label' => __('Video Layout', 'themify'),
		'mode' => 'sprite',
		'options' => array(
		    array('img' => 'video_top', 'value' => 'video-top', 'label' => __('Video Top', 'themify')),
		    array('img' => 'video_left', 'value' => 'video-left', 'label' => __('Video Left', 'themify')),
		    array('img' => 'video_right', 'value' => 'video-right', 'label' => __('Video Right', 'themify')),
		    array('img' => 'video_overlay', 'value' => 'video-overlay', 'label' => __('Video Overlay', 'themify'))
		)
	    ),
	    array(
		'id' => 'url_video',
		'type' => 'url',
		'label' => __('Video URL', 'themify'),
		'class' => 'fullwidth',
		'help' =>__('YouTube, Vimeo, etc. video <a href="https://themify.me/docs/video-embeds" target="_blank">embed link</a>', 'themify')
	    ),
	    array(
		'id' => 'autoplay_video',
		'type' => 'toggle_switch',
		'label' => __('Autoplay', 'themify'),
			'options' => 'simple',
			'binding' => array(
				'checked'     => array( 'show' => array( 'autoplay_text' ) ),
				'not_checked' => array( 'hide' => array( 'autoplay_text' ) ),
			)
		),
		array(
			'id' => 'autoplay_text',
			'type' => 'message',
			'label' => '',
			'comment' => __( 'Note: most browsers require video to be muted for autoplay', 'themify' )
		),
		array(
			'id' => 'mute_video',
			'type' => 'toggle_switch',
			'label' => __('Mute', 'themify'),
		'options' => 'simple'
	    ),
	    array(
		'id' => 'o_i_c',
		'label'=>__( 'Overlay Image', 'themify' ),
		'type' => 'toggle_switch',
		'options' => array(
		    'on' => array('name'=>'1','value' =>'en'),
		    'off' => array('name'=>'', 'value' =>'dis'),
		),
		'binding' => array(
			'checked' => array(
				'show' => array('o_i','o_m')
			),
			'not_checked' => array(
				'hide' => array('o_i','o_m')
			)
		)
	    ),
	    array(
		'id' => 'o_i',
		'type' => 'image',
					'label' =>'',
		'class' => 'xlarge',
	    ),
	    array(
					'id' => 'o_m',
		'type' => 'multi',
		'label' => '',
		'options' => array(
		    array(
			'id' => 'o_w',
			'label' => 'w',
			'type' => 'number'
		    ),
		    array(
			'id' => 'o_h',
			'label' => 'ht',
			'type' => 'number'
		    )
		)
	    ),
	    array(
		'id' => 'width_video',
		'type' => 'number',
		'label' => __('Video Width', 'themify'),
		'help' => __('Enter fixed witdth (eg. 200px) or relative (eg. 100%). Video height is auto adjusted.', 'themify'),
		'break' => true,
		'unit' => array(
		    'id' => 'unit_video',
		    'options' => array(
			'px'=>'px',
			'%'=>'%'
		    )
		)
	    ),
	    array(
		'id' => 'title_video',
		'type' => 'text',
		'label' => self::$texts['title_video'],
		'class' => 'xlarge',
		'control' => array(
		    'selector' => '.video-title'
		)
	    ),
	    array(
		'id' => 'title_link_video',
		'type' => 'url',
		'label' => __('Video Title Link', 'themify'),
		'class' => 'xlarge'
	    ),
	    array(
		'id' => 'caption_video',
		'type' => 'textarea',
		'label' => self::$texts['caption_video'],
		'control' => array(
		    'selector' => '.video-caption'
		)
	    ),
	    array(
		'id' => 'css_video',
		'type' => 'custom_css'
	    ),
	    array('type' => 'custom_css_id')
	);
    }

    public function get_default_settings() {
	return array(
			'url_video' => 'https://www.youtube.com/watch?v=FPPce2D8pYI',
			'mute_video' => 'no'
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
			    self::get_color('', 'background_color', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('', 'bg_c', 'bg_c', 'background-color', 'h')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(array('', '.module .video-title', '.module .video-title a')),
			    self::get_color_type(array('.module .video-title','.module .video-title a', ' .tb_text_wrap')),
			    self::get_font_size(),
			    self::get_line_height(),
			    self::get_letter_spacing(),
			    self::get_text_align(),
			    self::get_text_transform(),
			    self::get_font_style(),
			    self::get_text_decoration('', 'text_decoration_regular'),
			    self::get_text_shadow(array(' .video-caption', '.module .video-title', ' .video-title a')),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array('', '.module .video-title', '.module .video-title a'), 'f_f', 'h'),
			    self::get_color_type(array('.module:hover .video-title','.module:hover .video-title a', ':hover .tb_text_wrap'),'f_c_t_h','f_c_h', 'f_g_c_h'),
			    self::get_font_size(':hover', 'f_s', '', 'h'),
			    self::get_line_height(':hover', 'l_h', 'h'),
			    self::get_letter_spacing(':hover', 'l_s', 'h'),
			    self::get_text_align(':hover', 't_a', 'h'),
			    self::get_text_transform(':hover', 't_t', 'h'),
			    self::get_font_style(':hover', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration(':hover', 't_d_r', 'h'),
			    self::get_text_shadow(array(':hover .video-caption', '.module:hover .video-title', '.module:hover .video-title a'),'t_sh','h'),
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array(' a','.module .video-title a'), 'link_color'),
			    self::get_text_decoration(' a')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array(' a','.module .video-title a'), 'link_color', null, null, 'hover'),
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

	$video_title = array(
	    self::get_seperator('f'),
	    self::get_tab(array(
		'n' => array(
		    'options' => array(
			self::get_font_family(array('.module .video-title', '.module .video-title a'), 'font_family_title'),
			self::get_color(array('.module .video-title', '.module .video-title a'), 'font_color_title'),
			self::get_font_size('.module .video-title', 'font_size_title'),
			self::get_line_height('.module .video-title', 'line_height_title'),
			self::get_letter_spacing('.module .video-title', 'letter_spacing_title'),
			self::get_text_transform('.module .video-title', 'text_transform_title'),
			self::get_font_style('.module .video-title', 'font_title', 'font_title_bold'),
			self::get_text_shadow(array('.module .video-title', '.module .video-title a'), 't_sh_t'),
		    )
		),
		'h' => array(
		    'options' => array(
			self::get_font_family(array('.module .video-title', '.module .video-title a'), 'f_f_t', 'h'),
			self::get_color(array('.module .video-title', '.module .video-title a'), 'f_c_t',null,null,'h'),
			self::get_font_size('.module .video-title', 'f_s_t', '', 'h'),
			self::get_line_height('.module .video-title', 'l_h_t', 'h'),
			self::get_letter_spacing('.module .video-title', 'l_s_t', 'h'),
			self::get_text_transform('.module .video-title', 't_t_t', 'h'),
			self::get_font_style('.module .video-title', 'f_t', 'f_t_b', 'h'),
			self::get_text_shadow(array('.module .video-title', '.module .video-title a'), 't_sh_t','h'),
		    )
		)
	    ))
	);

	$video_caption = array(
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .video-caption', 'font_family_caption'),
			    self::get_color('.module .tb_text_wrap', 'font_color_caption'),
			    self::get_font_size(' .video-caption', 'font_size_caption'),
			    self::get_line_height(' .video-caption', 'line_height_caption'),
			    self::get_text_shadow('.module .video-caption', 't_sh_c'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(' .video-caption', 'f_f_c', 'h'),
			    self::get_color('.module .tb_text_wrap', 'f_c_c', null, null, 'h'),
			    self::get_font_size(' .video-caption', 'f_s_c', '', 'h'),
			    self::get_line_height(' .video-caption', 'l_h_c', 'h'),
			    self::get_text_shadow('.module .video-caption', 't_sh_c','h'),
			)
		    )
		))
	    )),
	    // Background
	    self::get_expand(__('Caption Overlay', 'themify'), array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color('.video-overlay .video-content', 'background_color_video_caption', __('Overlay', 'themify'), 'background-color'),
			    self::get_color(array('.module.video-overlay .video-title', '.module.video-overlay .tb_text_wrap'), 'f_c_h_v', __('Overlay Font Color', 'themify'))
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color('.video-overlay:hover .video-content', 'b_c_v_c_h', __('Overlay', 'themify'), 'background-color'),
			    self::get_color(array('.module.video-overlay:hover .video-title', '.module.video-overlay:hover .tb_text_wrap'), 'f_c_h_v_caption', __('Overlay Font Color', 'themify'))
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
		't' => array(
		    'label' => __('Video Title', 'themify'),
		    'options' => $video_title
		),
		'c' => array(
		    'label' => __('Video Caption', 'themify'),
		    'options' => $video_caption
		)
	    )
	);
    }

    public static function autoplay_callback($match) {
	return str_replace($match[1], add_query_arg('autoplay', 1, $match[1]), $match[0]);
    }

	public static function muted_callback( $match ) {
		return str_replace( $match[1], add_query_arg( 'mute', 1, $match[1] ), $match[0] );
	}

	public static function modify_youtube_embed_url( $html, $url, $args ) {
		$parse_url = parse_url( $url );
		if ( !empty( $parse_url['query'] ) || !empty( $parse_url['fragment'] ) ) {
			$parse_url['host'] = str_replace( 'www.', '', $parse_url['host'] );
			$query = !empty( $parse_url['query'] ) ? $parse_url['query'] : false;
			$query .= !empty( $parse_url['fragment'] ) ? $parse_url['fragment'] : '';
			if ( trim( $parse_url['path'], '/' ) !== 'playlist' && ( $parse_url['host'] === 'youtu.be' || $parse_url['host'] === 'youtube.com' ) ) {
				$query = preg_replace( '@v=([^"&]*)@', '', $query );
				$query = str_replace( '&038;', '&', $query );
				$query = empty($query) ? '?' : '&'.$query;
				return $query ? preg_replace( '@embed/([^"&]*)@', 'embed/$1' . $query, $html ) : $html;
			} elseif ( $parse_url['host'] === 'vimeo.com' ) {
				$query = str_replace( '&038;', '&', $query );
				return $query ? preg_replace( '@video/([^"&]*)@', 'video/$1?' . $query, $html ) : $html;
			}
		}
	return $html;
    }

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module('TB_Video_Module');
