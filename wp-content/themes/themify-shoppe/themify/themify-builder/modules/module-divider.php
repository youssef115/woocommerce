<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Divider
 * Description: Display Divider
 */
class TB_Divider_Module extends Themify_Builder_Component_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Divider', 'themify'),
			'slug' => 'divider'
		));
	}

	public function get_options() {
		return array(
			array(
				'id' => 'mod_title_divider',
				'type' => 'title'
			),
			array(
				'id' => 'style_divider',
				'type' => 'layout',
                                'mode'=>'sprite',
				'label' => __('Divider Style', 'themify'),
				'options' => array(
					array('img' => 'solid', 'value' => 'solid', 'label' => __('Solid', 'themify')),
					array('img' => 'dotted', 'value' => 'dotted', 'label' => __('Dotted', 'themify')),
					array('img' => 'dashed', 'value' => 'dashed', 'label' => __('Dashed', 'themify')),
					array('img' => 'double', 'value' => 'double', 'label' => __('Double', 'themify'))
				)
			),
			array(
				'id' => 'stroke_w_divider',
				'type' => 'range',
				'label' => __('Thickness', 'themify'),
				'class' => 'xsmall',
				'units' => array(
					'px' => array(
						'min' => 0,
						'max' => 500
					)
				)
			),
			array(
				'id' => 'color_divider',
				'type' => 'color',
				'label' => __('Color', 'themify')
			),
			array(
				'id' => 'top_margin_divider',
				'type' => 'range',
				'label' => __('Top Margin', 'themify'),
				'class' => 'xsmall',
				'units' => array(
					'px' => array(
						'min' => -500,
						'max' => 500
					)
				)
			),
			array(
				'id' => 'bottom_margin_divider',
				'type' => 'range',
				'label' => __('Bottom Margin', 'themify'),
				'class' => 'xsmall',
				'units' => array(
					'px' => array(
						'min' => -500,
						'max' => 500
					)
				)
			),
			array(
				'id' => 'divider_type',
				'type' => 'radio',
				'label' => __('Divider Width', 'themify'),
				'options' => array(
				    array('value'=>'fullwidth','name'=>__('Fullwidth', 'themify')),
				    array('value'=>'custom','name'=>__('Custom', 'themify'))
				),
				'option_js' => true
			),
			array(
				'id' => 'divider_width',
				'type' => 'range',
				'label' => 'w',
				'class' => 'xsmall',
				'wrap_class' => 'tb_group_element_custom',
				'units' => array(
					'px' => array(
						'min' => 0,
						'max' => 500
					)
				)
			),
			array(
				'id' => 'divider_align',
				'type' => 'icon_radio',
				'label' => __('Alignment', 'themify'),
				'options' => array(
					array('value' => 'left', 'name' => __('Left', 'themify'),'icon'=> '<span class="ti-align-left"></span>'),
					array('value' => 'center', 'name' => __('Center', 'themify'),'icon'=> '<span class="ti-align-center"></span>'),
					array('value' => 'right', 'name' => __('Right', 'themify'),'icon'=> '<span class="ti-align-right"></span>')
				),
				'wrap_class' => 'tb_group_element_custom'
			),
			array(
				'id' => 'css_divider',
				'type' => 'custom_css'
			),
                        array('type'=>'custom_css_id')
		);
	}

	public function get_styling() {
		return array(
			'type' => 'tabs',
			'options' => array(
			'm_t' => array(
				'options' => $this->module_title_custom_style()
			)
			)
		);
	}

	public function get_default_settings() {
		return array(
			'stroke_w_divider' => 1,
			'color_divider' => '000000',
			'divider_width' => 150
		);
	}
	

	protected function _visual_template() { 
		$module_args = self::get_module_args(); ?>
		<# 
		var style = '',
			align = 'custom' === data.divider_type && data.divider_align ? 'divider-' + data.divider_align : '';
		if ( data.stroke_w_divider ) style += 'border-width:'+ data.stroke_w_divider +'px; ';
		if ( data.color_divider ) style += 'border-color:' + data.color_divider + '; ';
		if ( data.top_margin_divider ) style += 'margin-top:' + data.top_margin_divider + 'px; ';
		if ( data.bottom_margin_divider ) style += 'margin-bottom:'+ data.bottom_margin_divider +'px; ';
		if ( 'custom' === data.divider_type && data.divider_width > 0 ) style += 'width:'+ data.divider_width +'px; ';
		if (!data.style_divider ) data.style_divider = 'solid';
                if (!data.divider_type ) data.divider_type = 'fullwidth';
		#>
		<div class="module module-<?php echo $this->slug ; ?> divider-{{ data.divider_type }} {{ data.style_divider }} {{ align }} {{ data.css_divider }}" style="{{ style }}">
            <# if ( data.mod_title_divider ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_divider }}}<?php echo $module_args['after_title']; ?>
			<# } #>
		</div>
	<?php
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Divider_Module' );
