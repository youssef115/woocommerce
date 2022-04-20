<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Module Name: Alert
 * Description: Display Alert content
 */

class TB_alert_Module extends Themify_Builder_Component_Module {

    /**
     * Declared in components/base.php
     * Redeclared here to prevent errors in case of downgrade
     */
    protected static $texts = array();

    function __construct() {
	self::$texts['heading_alert'] = __('Alert Heading', 'themify');
	self::$texts['text_alert'] = __('Alert Text', 'themify');
	self::$texts['action_btn_text_alert'] = __('Action Button', 'themify');
	parent::__construct(array(
	    'name' => __('Alert', 'themify'),
	    'slug' => 'alert'
	));
    }

    public function get_plain_text($module) {
	$text = isset($module['heading_alert']) ? $module['heading_alert'] : '';
	if (isset($module['text_alert'])) {
	    $text .= $module['text_alert'];
	}
	return $text;
    }

    public function get_options() {
	return array(
	    array(
		'id' => 'mod_title_alert',
		'type' => 'title'
	    ),
	    array(
		'id' => 'layout_alert',
		'type' => 'layout',
		'mode' => 'sprite',
		'label' => __('Alert Style', 'themify'),
		'options' => array(
		    array('img' => 'callout_button_right', 'value' => 'button-right', 'label' => __('Button Right', 'themify')),
		    array('img' => 'callout_button_left', 'value' => 'button-left', 'label' => __('Button Left', 'themify')),
		    array('img' => 'callout_button_bottom', 'value' => 'button-bottom', 'label' => __('Button Bottom', 'themify')),
		    array('img' => 'callout_button_bottom_center', 'value' => 'button-bottom-center', 'label' => __('Button Bottom Center', 'themify'))
		)
	    ),
	    array(
		'id' => 'heading_alert',
		'type' => 'text',
		'label' => self::$texts ['heading_alert'],
		'class' => 'xlarge',
		'control' => array(
		    'selector' => '.alert-heading'
		)
	    ),
	    array(
		'id' => 'text_alert',
		'type' => 'textarea',
		'label' => self::$texts['text_alert'],
		'control' => array(
		    'selector' => '.alert-content .tb_text_wrap'
		)
	    ),
	    array(
		'id' => 'color_alert',
		'type' => 'layout',
		'mode' => 'sprite',
		'class' => 'tb_colors',
		'label' => __('Alert Color', 'themify'),
		'color' => true,
		'transparent'=>true
	    ),
	    array(
		'id' => 'appearance_alert',
		'type' => 'checkbox',
		'label' => __('Alert Appearance', 'themify'),
		'appearance' => true
	    ),
		self::get_seperator(__('Action Button', 'themify')),
	    array(
		'id' => 'action_btn_text_alert',
		'type' => 'text',
		'label' => self::$texts ['action_btn_text_alert'],
		'class' => 'medium',
		'control' => array(
		    'selector' => '.tb_alert_text'
		)
	    ),
	    array(
		'id' => 'alert_button_action',
		'type' => 'select',
		'label' => __('Click Action', 'themify'),
		'options' => array(
		    'close' => __('Close alert box', 'themify'),
		    'message' => __('Display a message', 'themify'),
		    'url' => __('Go to URL', 'themify'),
		),
		'binding' => array(
		    'close' => array('hide' => array('alert_message_text', 'action_btn_link_alert', 'open_link_new_tab_alert')),
		    'message' => array('show' => array('alert_message_text'), 'hide' => array('action_btn_link_alert', 'open_link_new_tab_alert')),
		    'url' => array('show' => array('action_btn_link_alert', 'open_link_new_tab_alert'), 'hide' => array('alert_message_text'))
		)
	    ),
	    array(
		'id' => 'alert_message_text',
		'type' => 'textarea',
		'label' => __('Message text', 'themify')
	    ),
	    array(
		'id' => 'action_btn_link_alert',
		'type' => 'url',
		'label' => __('Action Button Link', 'themify'),
		'class' => 'xlarge'
	    ),
	    array(
		'id' => 'open_link_new_tab_alert',
		'type' => 'radio',
		'label' => __('Open Link', 'themify'),
		'options' => array(
		    array('value' => 'no', 'name' => __('Same Window', 'themify')),
		    array('value' => 'yes', 'name' => __('New Window', 'themify'))
		)
	    ),
	    array(
		'id' => 'action_btn_color_alert',
		'type' => 'layout',
		'class' => 'tb_colors',
		'mode' => 'sprite',
		'label' => __('Button Color', 'themify'),
		'color' => true,
		'transparent'=>true
	    ),
	    array(
		'id' => 'action_btn_appearance_alert',
		'type' => 'checkbox',
		'label' => __('Button Appearance', 'themify'),
		'appearance' => true
	    ),
	    array(
		'type' => 'separator'
	    ),
	    array(
		'id' => 'alert_no_date_limit',
		'type' => 'toggle_switch',
		'label' => __('Schedule Alert', 'themify'),
		'options' => array(
		    'on' => array('name'=>'alert_schedule','value' =>'en'),
		    'off' => array('name'=>'', 'value' =>'dis'),
		),
		'binding' => array(
		    'checked' => array(
			'show' => array('alert_start_at', 'alert_end_at')
		    ),
		    'not_checked' => array(
			'hide' => array('alert_start_at', 'alert_end_at')
		    )
		)
	    ),
	    array(
		'id' => 'alert_start_at',
		'type' => 'date',
		'label' => __('Start at', 'themify')
	    ),
	    array(
		'id' => 'alert_end_at',
		'type' => 'date',
		'label' => __('End at', 'themify')
	    ),
	    array(
		'id' => 'alert_show_to',
		'type' => 'select',
		'label' => __('Guest/Logged Users', 'themify'),
		'options' => array(
		    '' => __('Show to both users and guest visitors', 'themify'),
		    'guest' => __('Show only to guest visitors', 'themify'),
		    'user' => __('Show only to logged-in users', 'themify')
		)
	    ),
	    array(
            'id' => 'alert_limit_count',
            'type' => 'number',
            'label' => __('Limit Display', 'themify'),
            'help' => __('Enter the number of times that this alert should show.', 'themify'),
	    ),
	    array(
		'id' => 'alert_auto_close',
		'label' => __('Auto Close', 'themify'),
		'type' => 'toggle_switch',
		'options' => array(
		    'on' => array('name'=>'alert_close_auto','value' =>__( 'Enable', 'themify' )),
		    'off' => array('name'=>'', 'value' =>__( 'Disable', 'themify' ))
		),
		'binding' => array(
		    'checked' => array(
			'show' => array('alert_auto_close_delay')
		    ),
		    'not_checked' => array(
			'hide' => array('alert_auto_close_delay')
		    )
		)
	    ),
	    array(
		'id' => 'alert_auto_close_delay',
		'type' => 'number',
		'label' => __('Close Alert After', 'themify'),
		'after' => __(' Seconds', 'themify')
	    ),
	    array(
		'id' => 'css_alert',
		'type' => 'custom_css'
	    ),
	    array('type' => 'custom_css_id')
	);
    }

    public function get_default_settings() {
	return array(
	    'heading_alert' => esc_html__('Alert Heading', 'themify'),
	    'text_alert' => esc_html__('Alert Text', 'themify'),
	    'action_btn_text_alert' => esc_html__('Action button', 'themify'),
	    'action_btn_link_alert' => 'https://themify.me/',
	    'action_btn_color_alert' => 'blue',
	    'alert_auto_close_delay' => 5,
	    'color_alert' => 'tb_default_color'
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
			    self::get_font_family(array(' .alert-content')),
			    self::get_color_type(array(' .tb_text_wrap',' .alert-heading')),
			    self::get_font_size(' .alert-content'),
			    self::get_line_height(' .alert-content'),
			    self::get_letter_spacing(' .alert-content'),
			    self::get_text_align(' .alert-content'),
			    self::get_text_transform(' .alert-content'),
			    self::get_font_style(' .alert-content'),
			    self::get_text_decoration(array(' .alert-content',' a'), 'text_decoration_regular'),
			    self::get_text_shadow(' .alert-content'),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(array(' .alert-content:hover'), 'f_f', null, ''),
			    self::get_color_type(array(':hover .tb_text_wrap',':hover .alert-heading'), 'f_c_t_h', 'f_c_h', 'f_g_c_h'),
			    self::get_font_size(' .alert-content', 'f_s', '', 'h'),
			    self::get_line_height(' .alert-content', 'l_h', 'h'),
			    self::get_letter_spacing(' .alert-content', 'l_s', 'h'),
			    self::get_text_align(' .alert-content', 't_a', 'h'),
			    self::get_text_transform(' .alert-content', 't_t', 'h'),
			    self::get_font_style(' .alert-content', 'f_st', 'f_w', 'h'),
			    self::get_text_decoration(array(' .alert-content',' a'), 't_d_r', 'h'),
			    self::get_text_shadow(' .alert-content', 't_sh', 'h'),
			)
		    )
		))
	    )),
	    // Link
	    self::get_expand('l', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(array('.module .tb_text_wrap a','.module .tb_alert_text'), 'link_color'),
			    self::get_text_decoration(array('.module .alert-content',' a')),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(array('.module .tb_text_wrap a','.module .tb_alert_text'), 'link_color', null,null, 'hover'),
			    self::get_text_decoration(array('.module .alert-content',' a'), 't_d', 'h'),
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(),
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding('', 'p', 'h'),
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(),
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

	$alert_title = array(
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family('.module .alert-heading', 'f_f_a_t'),
			    self::get_color('.module .alert-heading', 'f_c_a_t'),
			    self::get_font_size('.module .alert-heading', 'f_s_a_t'),
			    self::get_line_height('.module .alert-heading', 'l_h_a_t'),
			    self::get_letter_spacing('.module .alert-heading', 'l_s_a_t'),
			    self::get_text_transform('.module .alert-heading', 't_t_a_t'),
			    self::get_font_style('.module .alert-heading', 'f_st_a_t', 'f_s_a_b'),
			    self::get_text_shadow('.module .alert-heading', 't_sh_a_t')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family('.module .alert-heading', 'f_f_a_t', 'h'),
			    self::get_color('.module .alert-heading', 'f_c_a_t', null, null, 'h'),
			    self::get_font_size('.module .alert-heading', 'f_s_a_t', '', 'h'),
			    self::get_line_height('.module .alert-heading', 'l_h_a_t', 'h'),
			    self::get_letter_spacing('.module .alert-heading', 'l_s_a_t', 'h'),
			    self::get_text_transform('.module .alert-heading', 't_t_a_t', 'h'),
			    self::get_font_style('.module .alert-heading', 'f_st_a_t', 'f_s_a_b', 'h'),
			    self::get_text_shadow('.module .alert-heading', 't_sh_a_t', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin('.module .alert-heading', 'm_a_t')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin('.module .alert-heading', 'm_a_t', 'h')
			)
		    )
		))
	    ))
	);

	$alert_button = array(
	    // Background
	    self::get_expand('bg', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_color(' .alert-button a', 'background_color_button', 'bg_c', 'background-color')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_color(' .alert-button a', 'background_color_button', 'bg_c', 'background-color','hover')
			)
		    )
		))
	    )),
	    // Font
	    self::get_expand('f', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_font_family(' .alert-button a', 'font_family_button'),
			    self::get_color('.module a .tb_alert_text', 'font_color_button'),
			    self::get_font_size(' .alert-button a', 'font_size_button'),
			    self::get_line_height(' .alert-button a', 'line_height_button'),
			    self::get_text_shadow(' .alert-button a', 't_sh_a_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_font_family(':hover .alert-button a', 'f_f_b_h', ''),
			    self::get_color('.module a .tb_alert_text', 'font_color_button',null, null, 'hover'),
			    self::get_font_size(':hover .alert-button a', 'f_s_b_h', '', ''),
			    self::get_line_height(':hover .alert-button a', 'l_h_b_h', ''),
			    self::get_text_shadow(':hover .alert-button a', 't_sh_a_b_h', '')
			)
		    )
		))
	    )),
	    // Padding
	    self::get_expand('p', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_padding(' .alert-button a', 'p_a_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_padding(' .alert-button a', 'p_a_b', 'h')
			)
		    )
		))
	    )),
	    // Margin
	    self::get_expand('m', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_margin(' .alert-button a', 'm_a_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_margin(' .alert-button a', 'm_a_b', 'h')
			)
		    )
		))
	    )),
	    // Border
	    self::get_expand('b', array(
		self::get_tab(array(
		    'n' => array(
			'options' => array(
			    self::get_border(' .alert-button a', 'b_a_b')
			)
		    ),
		    'h' => array(
			'options' => array(
			    self::get_border(' .alert-button a', 'b_a_b', 'h')
			)
		    )
		))
	    )),
		// Rounded Corners
		self::get_expand('r_c', array(
				self::get_tab(array(
					'n' => array(
						'options' => array(
							self::get_border_radius(' .alert-button a', 'a_b_r_c')
						)
					),
					'h' => array(
						'options' => array(
							self::get_border_radius(' .alert-button a', 'a_b_r_c', 'h')
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
							self::get_box_shadow(' .alert-button a', 'a_b_sh')
						)
					),
					'h' => array(
						'options' => array(
							self::get_box_shadow(' .alert-button a', 'a_b_sh', 'h')
						)
					)
				))
			)
		)
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
		    'label' => __('Alert Title', 'themify'),
		    'options' => $alert_title
		),
		'b' => array(
		    'label' => __('Alert Button', 'themify'),
		    'options' => $alert_button
		)
	    )
	);
    }

    protected function _visual_template() {
	$module_args = $this->get_module_args();
	?>
    <# data.color_alert = undefined === data.color_alert || 'default' === data.color_alert ? 'tb_default_color' : data.color_alert; #>
    <# data.action_btn_color_alert = undefined === data.action_btn_color_alert || 'default' === data.action_btn_color_alert ? 'tb_default_color' : data.action_btn_color_alert; #>
	<div class="module module-<?php echo $this->slug; ?> ui {{ data.layout_alert }} {{ data.color_alert }} {{ data.css_alert }} {{ data.background_repeat }} <# data.appearance_alert ? print( data.appearance_alert.split('|').join(' ') ) : ''; #>">
	    <# if ( data.mod_title_alert ) { #>
	    <?php echo $module_args['before_title']; ?>{{{ data.mod_title_alert }}}<?php echo $module_args['after_title']; ?>
	    <# } #>
	    <div class="alert-inner">
		<div class="alert-content">
		    <h3 class="alert-heading" contenteditable="false" data-name="heading_alert">{{{ data.heading_alert }}}</h3>
		    <div class="tb_text_wrap" contenteditable="false" data-name="text_alert">{{{ data.text_alert }}}</div>
		</div>
		<# if ( data.action_btn_text_alert ) { #>
		<div class="alert-button">
		    <a href="{{ data.action_btn_link_alert }}" class="ui builder_button {{ data.action_btn_color_alert }} <# data.action_btn_appearance_alert  ? print( data.action_btn_appearance_alert.split('|').join(' ') ) : ''; #>">
			<span class="tb_alert_text" contenteditable="false" data-name="action_btn_text_alert">{{{ data.action_btn_text_alert }}}</span>
		    </a>
		</div>
		<# } #>
	    </div>
	    <div class="alert-close ti-close"></div>
	</div>
	<?php
    }

}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module('TB_alert_Module');
