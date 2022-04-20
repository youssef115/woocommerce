<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Callout
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):

    $fields_default = array(
        'mod_title_callout' => '',
        'appearance_callout' => '',
        'layout_callout' => '',
        'color_callout' => 'tb_default_color',
        'heading_callout' => '',
        'text_callout' => '',
        'action_btn_link_callout' => '#',
        'open_link_new_tab_callout' => '',
        'action_btn_text_callout' => false,
        'action_btn_color_callout' => 'tb_default_color',
        'action_btn_appearance_callout' => '',
        'css_callout' => '',
        'background_repeat' => '',
        'animation_effect' => ''
    );

    if (isset($args['mod_settings']['appearance_callout'])) {
        $args['mod_settings']['appearance_callout'] = self::get_checkbox_data($args['mod_settings']['appearance_callout']);
    }
    if (isset($args['mod_settings']['action_btn_appearance_callout'])) {
        $args['mod_settings']['action_btn_appearance_callout'] = self::get_checkbox_data($args['mod_settings']['action_btn_appearance_callout']);
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
	$fields_args['layout_callout'],
	$fields_args['color_callout'],
	$fields_args['css_callout'], 
	$fields_args['appearance_callout'], 
	$fields_args['background_repeat'],
	self::parse_animation_effect($fields_args['animation_effect'], $fields_args)
                    ), $mod_name, $element_id, $fields_args);
   
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
    $container_props = apply_filters('themify_builder_module_container_props', array(
        'class' =>  implode(' ', $container_class),
            ), $fields_args, $mod_name, $element_id);
    
    $ui_class = implode(' ', array('ui', 'builder_button', $fields_args['action_btn_color_callout'], $fields_args['action_btn_appearance_callout']));
    $args=null;
    ?>
    <!-- module callout -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php $container_props=$container_class=null;
	    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
        <?php if ($fields_args['mod_title_callout'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_callout'], $fields_args). $fields_args['after_title']; ?>
        <?php endif; ?>

        <div class="callout-inner">
            <div class="callout-content">
                <h3 class="callout-heading"<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="heading_callout"<?php endif;?>><?php echo $fields_args['heading_callout'] ?></h3>
                <div class="tb_text_wrap"<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="text_callout"<?php endif; ?>>
		    <?php echo apply_filters('themify_builder_module_content', $fields_args['text_callout']);?>
                </div>
            </div>
            <!-- /callout-content -->
            <?php if ($fields_args['action_btn_text_callout']) : ?>
                <div class="callout-button">
                        <a href="<?php echo esc_url($fields_args['action_btn_link_callout']); ?>" class="<?php echo $ui_class; ?>"<?php echo 'yes' === $fields_args['open_link_new_tab_callout'] ? ' rel="noopener" target="_blank"' : ''; ?>>
                            <span class="tb_callout_text"<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="action_btn_text_callout"<?php endif;?>><?php echo $fields_args['action_btn_text_callout'] ?></span>
                        </a>
                    </div>
                <?php endif; ?>
        </div>
        <!-- /callout-content -->
    </div>
    <!-- /module callout -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
