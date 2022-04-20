<?php
if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

/**
 * Module Name: Social Share
 */
class TB_Social_Share_Module extends Themify_Builder_Component_Module
{

	function __construct() {
		parent::__construct( array(
			'name' => __( 'Social Share', 'themify' ),
			'slug' => 'social-share'
		) );
	}

	public function get_options() {

		return array(
			array(
				'id' => 'mod_title',
				'type' => 'title'
			),
			array(
				'id' => 'networks',
				'type' => 'checkbox',
				'label' => __( 'Networks', 'themify' ),
				'options' => array(
					array( 'name' => 'fb', 'value' => __( 'Facebook', 'themify' ) ),
					array( 'name' => 'tw', 'value' => __( 'Twitter', 'themify' ) ),
					array( 'name' => 'lk', 'value' => __( 'LinkedIn', 'themify' ) ),
					array( 'name' => 'pi', 'value' => __( 'Pinterest', 'themify' ) ),
					array( 'name' => 'em', 'value' => __( 'Email', 'themify' ) )
				)
			),
			array(
				'id' => 'size',
				'label' => __( 'Size', 'themify' ),
				'type' => 'layout',
				'mode' => 'sprite',
				'options' => array(
					array( 'img' => 'normall_button', 'value' => 'normal', 'label' => __( 'Normal', 'themify' ) ),
					array( 'img' => 'small_button', 'value' => 'small', 'label' => __( 'Small', 'themify' ) ),
					array( 'img' => 'large_button', 'value' => 'large', 'label' => __( 'Large', 'themify' ) ),
					array( 'img' => 'xlarge_button', 'value' => 'xlarge', 'label' => __( 'xLarge', 'themify' ) ),
				)
			),
			array(
				'id' => 'shape',
				'label' => __( 'Icon Shape', 'themify' ),
				'type' => 'layout',
				'mode' => 'sprite',
				'options' => array(
					array( 'img' => 'circle_button', 'value' => 'circle', 'label' => __( 'Circle', 'themify' ) ),
					array( 'img' => 'rounded_button', 'value' => 'rounded', 'label' => __( 'Rounded', 'themify' ) ),
					array( 'img' => 'squared_button', 'value' => 'squared', 'label' => __( 'Squared', 'themify' ) ),
					array( 'img' => 'none', 'value' => 'none', 'label' => __( 'None', 'themify' ) )
				)
			),
			array(
				'id' => 'arrangement',
				'label' => __( 'Arrangement ', 'themify' ),
				'type' => 'layout',
				'mode' => 'sprite',
				'options' => array(
					array( 'img' => 'horizontal_button', 'value' => 'h', 'label' => __( 'Horizontal', 'themify' ) ),
					array( 'img' => 'vertical_button', 'value' => 'v', 'label' => __( 'Vertical', 'themify' ) ),
				)
			),
			array(
				'id' => 'title',
				'type' => 'toggle_switch',
				'label' => __( 'Title', 'themify' ),
			),
			array(
				'id' => 'css',
				'type' => 'custom_css'
			),
			array( 'type' => 'custom_css_id' )
		);
	}

	public function get_default_settings() {
		return array(
			'mod_title_social_share' => '',
			'networks' => 'fb|tw|pi|em',
			'size' => 'normal',
			'shape' => 'none',
			'arrangement' => 'h',
			'title' => 'yes',
		);
	}

	public function get_styling() {
		$general = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_image()
						)
					),
					'h' => array(
						'options' => array(
							self::get_image( '', 'b_i', 'bg_c', 'b_r', 'b_p', 'h' )
						)
					)
				) )
			) ),
			// Font
			self::get_expand( 'f', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_family(),
							self::get_color_type( ' a' ),
							self::get_font_size(),
							self::get_line_height(),
							self::get_letter_spacing(),
							self::get_text_align(),
							self::get_text_transform(),
							self::get_font_style(),
							self::get_text_decoration(),
							self::get_text_shadow(),
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_family( '', 'f_f', 'h' ),
							self::get_color_type( ':hover a', 'h'),
							self::get_font_size( '', 'f_s_h', '', 'h' ),
							self::get_line_height( '', 'l_h_h', 'h' ),
							self::get_letter_spacing( '', 'l_s', 'h' ),
							self::get_text_align( '', 't_a', 'h' ),
							self::get_text_transform( '', 't_t', 'h' ),
							self::get_font_style( '', 'f_st', 'f_w', 'h' ),
							self::get_text_decoration( '', 't_d_r', 'h' ),
							self::get_text_shadow( '', 't_sh', 'h' ),
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding()
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( '', 'p', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin()
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( '', 'm', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border()
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( '', 'b', 'h' )
						)
					)
				) )
			) ),
			// Filter
			self::get_expand( 'f_l',
				array(
					self::get_tab( array(
						'n' => array(
							'options' => self::get_blend()

						),
						'h' => array(
							'options' => self::get_blend( '', '', 'h' )
						)
					) )
				)
			),
			// Width
			self::get_expand( 'w', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_width( '', 'w' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_width( '', 'w', 'h' )
						)
					)
				) )
			) ),
			// Height & Min Height
			self::get_expand( 'ht', array(
					self::get_height(),
					self::get_min_height(),
                    self::get_max_height()
				)
			),
			// Rounded Corners
			self::get_expand( 'r_c', array(
					self::get_tab( array(
						'n' => array(
							'options' => array(
								self::get_border_radius()
							)
						),
						'h' => array(
							'options' => array(
								self::get_border_radius( '', 'r_c', 'h' )
							)
						)
					) )
				)
			),
			// Shadow
			self::get_expand( 'sh', array(
					self::get_tab( array(
						'n' => array(
							'options' => array(
								self::get_box_shadow()
							)
						),
						'h' => array(
							'options' => array(
								self::get_box_shadow( '', 'sh', 'h' )
							)
						)
					) )
				)
			),
			// Position
			self::get_expand( 'po', array( self::get_css_position() ) ),
			// Display
			self::get_expand('disp', self::get_display())
		);

		$icon = array(
			// Background
			self::get_expand( 'bg', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_image( ' a', 'b_i', 'in_b_c', 'b_r', 'b_p' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_image( ' a:hover', 'b_i_h', 'in_h_b_c', 'b_r_h', 'b_p_h' )
						)
					)
				) )
			) ),
			// Color
			self::get_expand( 'c', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_color( ' a', 'f_c_i' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_color( ' a', 'f_c_i', null, null, 'h' )
						)
					)
				) )
			) ),
			// Font Size
			self::get_expand( 'Size', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_font_size( ' a', 'f_s_i' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_font_size( ' a', 'f_s_i', '', 'h' )
						)
					)
				) )
			) ),
			// Border
			self::get_expand( 'b', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border( ' a', 'br_i' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border( ' a', 'br_i', 'h' )
						)
					)
				) )
			) ),
			// Padding
			self::get_expand( 'p', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_padding( ' a', 'p_i' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_padding( ' a', 'p_i', 'h' )
						)
					)
				) )
			) ),
			// Margin
			self::get_expand( 'm', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_margin( ' a', 'm_i' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_margin( ' a', 'm_i', 'h' )
						)
					)
				) )
			) ),
			// Rounded Corners
			self::get_expand( 'r_c', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_border_radius( ' a', 'r_c_i' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius( ' a', 'r_c_i', 'h' )
						)
					)
				) )
			) ),
			// Shadow
			self::get_expand( 'sh', array(
				self::get_tab( array(
					'n' => array(
						'options' => array(
							self::get_box_shadow( ' a', 'b_sh_i' )
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow( ' a', 'b_sh_i', 'h' )
						)
					)
				) )
			) )
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
				'icon' => array(
					'label' => __( 'Icon', 'themify' ),
					'options' => $icon
				)
			)
		);
	}

	protected function _visual_template() {
		$module_args = self::get_module_args();
		?>
        <# var nets = undefined !== data.networks && '' !== data.networks  ? data.networks.split('|') : []; 
        if(data.networks.length>0){
        var info = {
        fb:{icon:'ti-facebook',title:'<?php _e( 'Facebook', 'themify' ); ?>',type:'facebook'},
        tw:{icon:'ti-twitter-alt',title:'<?php _e( 'Twitter', 'themify' ); ?>',type:'twitter'},
        lk:{icon:'ti-linkedin',title:'<?php _e( 'Linkedin', 'themify' ); ?>',type:'linkedin'},
        pi:{icon:'ti-pinterest',title:'<?php _e( 'Pinterest', 'themify' ); ?>',type:'pinterest'},
        em:{icon:'ti-email',title:'<?php _e( 'Email', 'themify' ); ?>',type:'email'}
        };
        } #>
        <div class="module module-<?php echo $this->slug; ?> {{ data.css_social_share }} tb_ss_style_{{ data.style }} tb_ss_arrangement_{{ data.arrangement }} tb_ss_shape_{{ data.shape }} tb_ss_size_{{ data.size }}">
			<# if ( data.mod_title ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title }}}<?php echo $module_args['after_title']; ?>
			<# } #>
			<div class="module-social-share-wrapper">
            <# if( data.mod_title_social_share ) { #>
			<?php echo $module_args['before_title']; ?>
            {{{ data.mod_title_social_share }}}
			<?php echo $module_args['after_title']; ?>
            <# } 
             for(var i = 0,len=nets.length; i < len; ++i){ #>
			 <div class="ss_anchor_wrap">
            <a href="#" data-type="{{ info[nets[i]].type }}">
                <i class="tb_social_share_icon {{ info[nets[i]].icon }}"></i>
                <# if('no' === data.title){ #>
                <span class="tb_social_share_title">{{{ info[nets[i]].title }}}</span>
                <# } #>
            </a>
			 </div>
            <# } #>
			</div>
        </div>
		<?php
	}

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Social_Share_Module' );
