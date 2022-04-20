<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Alert
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):

    $fields_default = array(
	'mod_title_alert' => '',
	'appearance_alert' => '',
	'layout_alert' => '',
	'color_alert' => 'tb_default_color',
	'heading_alert' => '',
	'text_alert' => '',
	'alert_button_action' => 'close',
	'alert_message_text' => '',
	'action_btn_link_alert' => '#',
	'open_link_new_tab_alert' => '',
	'action_btn_text_alert' => false,
	'action_btn_color_alert' => 'tb_default_color',
	'action_btn_appearance_alert' => '',
	'alert_no_date_limit' => '',
	'alert_start_at' => '',
	'alert_end_at' => '',
	'alert_show_to' => '',
	'alert_limit_count' => '',
	'alert_auto_close' => '',
	'alert_auto_close_delay' => '',
	'css_alert' => '',
	'background_repeat' => '',
	'animation_effect' => ''
    );

    if (isset($args['mod_settings']['appearance_alert'])) {
	$args['mod_settings']['appearance_alert'] = self::get_checkbox_data($args['mod_settings']['appearance_alert']);
    }
    if (isset($args['mod_settings']['action_btn_appearance_alert'])) {
	$args['mod_settings']['action_btn_appearance_alert'] = self::get_checkbox_data($args['mod_settings']['action_btn_appearance_alert']);
    }
    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    $mod_name=$args['mod_name'];
    $builder_id = $args['builder_id'];
    $element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
    $container_class =apply_filters('themify_builder_module_classes', array(
	'module ui',
	'module-' . $mod_name, 
	$element_id, 
	$fields_args['layout_alert'], 
	$fields_args['color_alert'], 
	$fields_args['css_alert'],
	$fields_args['appearance_alert'], 
	$fields_args['background_repeat'],
	self::parse_animation_effect($fields_args['animation_effect'], $fields_args)
		    ), $mod_name, $element_id, $fields_args);
    
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
    $container_props = apply_filters('themify_builder_module_container_props', array(
	'class' =>  implode(' ', $container_class),
	'data-auto-close' => !empty($fields_args['alert_auto_close']) && !empty($fields_args['alert_auto_close_delay']) ? $fields_args['alert_auto_close_delay'] : '',
	'data-module-id' => $element_id,
	'data-alert-limit' => $fields_args['alert_limit_count'],
	    ), $fields_args, $mod_name, $element_id);
    
    // Button action
    $url = $fields_args['alert_button_action'] === 'url' ? esc_url($fields_args['action_btn_link_alert']) : '#';
    $button_close_class = $fields_args['alert_button_action'] !== 'url' ? 'alert-close' : '';
    $button_attr = $fields_args['alert_button_action'] === 'url' && 'yes' === $fields_args['open_link_new_tab_alert'] ? ' rel="noopener" target="_blank"' : '';

    if ($fields_args['alert_button_action'] === 'message' && !empty($fields_args['alert_message_text'])) {
	$button_attr = ' data-alert-message="' . esc_attr($fields_args['alert_message_text']) . '"';
    }

    $ui_class = implode(' ', array('ui', 'builder_button', $fields_args['action_btn_color_alert'], $fields_args['action_btn_appearance_alert'], $button_close_class));

    // Alert visibility
    $is_alert_visible = true;
    if (Themify_Builder::$frontedit_active===false) {
	if (!empty($fields_args['alert_no_date_limit']) && (!empty($fields_args['alert_start_at']) || !empty($fields_args['alert_end_at']) )) {
	    $now = time();
	    if (!empty($fields_args['alert_start_at'])) {
		$is_alert_visible = ( strtotime($fields_args['alert_start_at']) - $now ) < 0;
	    }
	    if ($is_alert_visible===true && !empty($fields_args['alert_end_at'])) {
		$is_alert_visible = ( $now - strtotime($fields_args['alert_end_at']) ) < 0;
	    }
	}
	
	if ($is_alert_visible===true && !empty($fields_args['alert_show_to'])) {
	    if ($fields_args['alert_show_to'] === 'guest') {
		$is_alert_visible = !is_user_logged_in();
	    } elseif ($fields_args['alert_show_to'] === 'user') {
		$is_alert_visible = is_user_logged_in();
	    }
	}

	if ($is_alert_visible===true && !empty($fields_args['alert_limit_count']) && isset($_COOKIE[$element_id])) {
	    $user_cookie = explode('|', $_COOKIE[$element_id]);
	    if (!empty($user_cookie[0]) && !empty($user_cookie[1])) {
		$is_alert_visible = $user_cookie[0] !== $user_cookie[1] || $user_cookie[0] !== $fields_args['alert_limit_count'];
	    }
	}
    }
    $args=null;
    if ($is_alert_visible===true):
	?>
	<!-- module alert -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php $container_props=$container_class=null;
	    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
	<?php if ($fields_args['mod_title_alert'] !== ''): ?>
	    <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_alert'], $fields_args) . $fields_args['after_title']; ?>
	<?php endif; ?>

	<div class="alert-inner">
	    <div class="alert-content">
		<h3<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="heading_alert"<?php endif;?> class="alert-heading"><?php echo $fields_args['heading_alert'] ?></h3>
		<div class="tb_text_wrap"<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="text_alert"<?php endif; ?>>
		    <?php echo apply_filters('themify_builder_module_content', $fields_args['text_alert']);?>
		</div>
	    </div>
	    <!-- /alert-content -->
	    <?php if ($fields_args['action_btn_text_alert']) : ?>
		<div class="alert-button">
		    <a href="<?php echo $url; ?>" class="<?php echo $ui_class; ?>"<?php echo $button_attr; ?>>
			<span class="tb_alert_text"<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="action_btn_text_alert"<?php endif;?>><?php echo $fields_args['action_btn_text_alert'] ?></span>
		    </a>
		</div>
	    <?php endif; ?>
	</div>
	<div class="alert-close ti-close"></div>
	<!-- /alert-content -->
    </div>
    <?php endif; ?>
    <!-- /module alert -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
