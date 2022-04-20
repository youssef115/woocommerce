<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
///////////////////////////////////////
// Switch Template Layout Types
///////////////////////////////////////
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))) {
    $slider_default = array(
        'layout_slider' => '',
        'img_h_slider' => '',
        'img_w_slider' => '',
        'image_size_slider' => '',
        'css_slider' => '',
        'animation_effect' => '',
	'grid_layout_testimonial'=>'grid3',
	'masonry'=>'disable'
    );

    $settings = wp_parse_args($args['mod_settings'], $slider_default);
    unset($args['mod_settings']);
    
    $mod_name=$args['mod_name'];
    $builder_id = $args['builder_id'];
    $element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
    
    $container_class =  apply_filters('themify_builder_module_classes', array(
        'module clearfix', 
	'module-' . $mod_name, 
	$element_id,
	$settings['css_slider'],
	$settings['layout_slider'],
	self::parse_animation_effect($settings['animation_effect'], $settings)
    ), $mod_name, $element_id, $settings);
    
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
    $container_props = apply_filters('themify_builder_module_container_props', array(
        'id' => $element_id,
        'class' => implode(' ',$container_class)
            ), $settings, $mod_name, $element_id);
    $settings['margin'] = '';
    $masonry = 'enable' === $settings['masonry']?' masonry': '';
    ?>
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$settings)); ?>>
        <ul class="themify_builder_testimonial loops-wrapper builder-posts-wrap <?php echo $settings['grid_layout_testimonial'],$masonry; ?>">
                <?php
		$container_props=$container_class=null;
		do_action('themify_builder_background_styling',$builder_id,array('styling'=>$settings,'mod_name'=>$mod_name),$element_id,'module');
                self::retrieve_template('template-' . $mod_name . '-content.php', array(
                    'module_ID' => $element_id,
                    'mod_name' => $mod_name,
                    'settings' => $settings
		), '', '', true);
                ?>
        </ul>
    </div>
        <?php
}
TFCache::end_cache();
