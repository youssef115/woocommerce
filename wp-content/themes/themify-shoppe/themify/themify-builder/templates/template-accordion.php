<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Accordion
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):

    $fields_default = array(
        'mod_title_accordion' => '',
        'layout_accordion' => 'plus-icon-button',
        'expand_collapse_accordion' => 'toggle',
        'color_accordion' => 'tb_default_color',
        'accordion_appearance_accordion' => '',
        'content_accordion' => array(),
        'animation_effect' => '',
        'icon_accordion' => '',
        'icon_color_accordion' => '',
        'icon_active_accordion' => '',
        'icon_active_accordion' => '',
        'icon_active_color_accordion' => '',
        'css_accordion' => ''
    );

    if (isset($args['mod_settings']['accordion_appearance_accordion'])) {
        $args['mod_settings']['accordion_appearance_accordion'] = self::get_checkbox_data($args['mod_settings']['accordion_appearance_accordion']);
    }
    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    $mod_name=$args['mod_name'];
    $builder_id = $args['builder_id'];
    $element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
    $container_class = apply_filters('themify_builder_module_classes', array(
        'module', 
	'module-' . $mod_name, 
	$element_id, 
	$fields_args['css_accordion'], 
	self::parse_animation_effect($fields_args['animation_effect'], 
	$fields_args)
    ), $mod_name, $element_id, $fields_args);
   
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
    $container_props = apply_filters('themify_builder_module_container_props', array(
        'class' => implode(' ', $container_class),
        'data-behavior' => $fields_args['expand_collapse_accordion']
    ), $fields_args, $mod_name, $element_id);

    $ui_class = implode(' ', array('ui', 'module-' . $mod_name, $fields_args['layout_accordion'], $fields_args['accordion_appearance_accordion'], $fields_args['color_accordion']));
    $args=null;
    ?>
    <!-- module accordion -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php 
	$container_props=$container_class=null; 
	do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
        <?php if ($fields_args['mod_title_accordion'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_accordion'], $fields_args) . $fields_args['after_title']; ?>
        <?php endif; ?>

        <ul class="<?php echo $ui_class ?>">
            <?php
            $content_accordion = array_filter($fields_args['content_accordion']);
            foreach ($content_accordion as $i=>$content):
                $content = wp_parse_args($content, array(
                    'title_accordion' => '',
                    'default_accordion' => 'closed',
                    'text_accordion' => '',
                ));
                ?>
                <li<?php if ($content['default_accordion'] === 'open'):?> class="builder-accordion-active"<?php endif;?>>
                    <div class="accordion-title">
                        <a href="#acc-<?php echo $element_id . '-' . $i; ?>" aria-controls="acc-<?php echo $element_id . '-' . $i; ?>" aria-expanded="<?php echo ($content['default_accordion'] === 'open')? "true" : "false" ; ?>">
                            <?php if ($fields_args['icon_accordion'] !== '') : ?><i class="accordion-icon <?php echo themify_get_icon($fields_args['icon_accordion']); ?>"></i><?php endif; ?>
                            <?php if ($fields_args['icon_active_accordion'] !== '') : ?><i class="accordion-active-icon fa <?php echo $fields_args['icon_active_accordion']; ?>"></i><?php endif; ?>
			    <span class="tb_title_accordion"<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-repeat="content_accordion" data-name="title_accordion" data-index="<?php echo $i?>"<?php endif;?>><?php echo $content['title_accordion']; ?></span>
                        </a>
                    </div>
                    <div id="acc-<?php echo $element_id . '-' . $i; ?>" aria-hidden="<?php echo ($content['default_accordion'] === 'open')? "false" : "true" ; ?>" class="accordion-content clearfix <?php if ($content['default_accordion'] !== 'open'): ?> default-closed<?php endif; ?>">
                        <?php if ($content['text_accordion']!==''):?>
			    <div<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="text_accordion" data-repeat="content_accordion" data-index="<?php echo $i?>"<?php endif;?>  class="<?php if(Themify_Builder::$frontedit_active===true):?>tb_editor_enable <?php endif;?>tb_text_wrap">
				<?php echo apply_filters('themify_builder_module_content', $content['text_accordion']);?>
			    </div>
                        <?php endif;?>
                        
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>

    </div>
    <!-- /module accordion -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
