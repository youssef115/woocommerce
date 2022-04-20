<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Icon
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):

    $fields_default = array(
        'mod_title_icon' => '',
        'icon_size' => '',
        'icon_style' => '',
        'icon_arrangement' => 'icon_horizontal',
	'icon_position' => '',
        'content_icon' => array(),
        'animation_effect' => '',
        'css_icon' => ''
    );

    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    
    $mod_name=$args['mod_name'];
    $builder_id = $args['builder_id'];
    $element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
    
    $container_class = apply_filters('themify_builder_module_classes', array(
        'module',
	'module-'.$mod_name,
	$element_id,
	$fields_args['css_icon'],
	$fields_args['icon_size'],
	$fields_args['icon_style'],
	$fields_args['icon_arrangement'],
	$fields_args['icon_position'], 
	self::parse_animation_effect($fields_args['animation_effect'], $fields_args)
                    ), $mod_name, $element_id, $fields_args);
   
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
    $container_props = apply_filters('themify_builder_module_container_props', array(
        'class' => implode(' ', $container_class),
    ), $fields_args, $mod_name, $element_id);
    ?>
    <!-- module icon -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php 
	    $container_props=$container_class=null;
	    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
        <?php if ($fields_args['mod_title_icon'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_icon'], $fields_args). $fields_args['after_title']; ?>
        <?php endif; ?>
	<div class="module-<?php echo $mod_name; ?>">
            <?php
            $content_icon = array_filter($fields_args['content_icon']);
	    $args=null;
            foreach ($content_icon as $i=>$content):
                $content = wp_parse_args($content, array(
                    'label' => '',
                    'link' => '',
                    'icon' => '',
                    'new_window' => false,
                    'icon_color_bg' => 'tb_default_color',
                    'link_options' => '',
                    'lightbox_width' => '',
                    'lightbox_height' => '',
                    'lightbox_width_unit' => 'px',
                    'lightbox_height_unit' => 'px'
                ));
	            $content['lightbox_width_unit'] = $content['lightbox_width_unit'] ? $content['lightbox_width_unit'] : 'px';
	            $content['lightbox_height_unit'] = $content['lightbox_height_unit'] ? $content['lightbox_height_unit'] : 'px';
                $link_target = $content['link_options'] === 'newtab' ? ' rel="noopener" target="_blank"' : '';
                $link_lightbox_class = $content['link_options'] === 'lightbox' ? ' class="lightbox-builder themify_lightbox"' : '';
                $lightbox_data = !empty($content['lightbox_width']) || !empty($content['lightbox_height']) ? sprintf(' data-zoom-config="%s|%s"'
                                , $content['lightbox_width'] . $content['lightbox_width_unit']
                                , $content['lightbox_height'] . $content['lightbox_height_unit']) : false;
                ?>
                <div class="module-icon-item">
                    <?php if ($content['link']): ?>
                        <a href="<?php echo esc_attr($content['link']) ?>"<?php echo $link_target, $lightbox_data, $link_lightbox_class ?>>
                        <?php endif; ?>
                        <?php if ($content['icon']): ?>
                            <i class="<?php echo themify_get_icon($content['icon']); ?> ui <?php echo $content['icon_color_bg'] ?>"></i>
                        <?php endif; ?>
                        <?php if ($content['label']): ?>
                            <span<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="label" data-index="<?php echo $i?>" data-repeat="content_icon"<?php endif;?>><?php echo $content['label'] ?></span>
                        <?php endif; ?>
                        <?php if ($content['link']): ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- /module icon -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
