<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Gallery
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):

    $fields_default = array(
        'mod_title_gallery' => '',
        'layout_gallery' => 'grid',
        'image_size_gallery' => 'thumbnail',
        'shortcode_gallery' => '',
        'thumb_w_gallery' => '',
        'thumb_h_gallery' => '',
        's_image_w_gallery' => '',
        's_image_h_gallery' => '',
        's_image_size_gallery' => 'full',
        'appearance_gallery' => '',
        'css_gallery' => '',
        'gallery_images' => array(),
        'gallery_columns' => 3,
        'link_opt' => false,
        'link_image_size' => 'full',
        'rands' => '',
        'animation_effect' => '',
        'gallery_pagination' => false,
        'gallery_per_page' => '',
        'slider_thumbs' => false,
        'gallery_image_title' => false,
        'gallery_exclude_caption' => false,
        'layout_masonry' => '',
	'visible_opt_slider' => '',
        'tab_visible_opt_slider' => '',
        'mob_visible_opt_slider' => '',
        'auto_scroll_opt_slider' => 0,
        'speed_opt_slider' => '',
        'effect_slider' => 'scroll',
        'pause_on_hover_slider' => 'resume',
        'wrap_slider' => 'yes',
        'show_nav_slider' => 'yes',
        'show_arrow_slider' => 'yes',
        'show_arrow_buttons_vertical' => '',
        'unlink_feat_img_slider'=>'no',
        'unlink_post_title_slider'=>'no',
        'left_margin_slider' => '',
        'right_margin_slider' => '',
        'animation_effect' => '',
        'height_slider' => 'variable',
        'lightbox_title' => ''
    );
    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    $mod_name=$args['mod_name'];
    $builder_id = $args['builder_id'];
    $element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
    if ($fields_args['appearance_gallery'] !== '') {
        $fields_args['appearance_gallery'] = self::get_checkbox_data($fields_args['appearance_gallery']);
    }
    $columns = $fields_args['gallery_columns'];
    if ($fields_args['shortcode_gallery'] !== '') {
        $fields_args['gallery_images'] = Themify_Builder_Model::get_images_from_gallery_shortcode($fields_args['shortcode_gallery']);
        if (!$fields_args['link_opt']) {
            $fields_args['link_opt'] = Themify_Builder_Model::get_gallery_param_option($fields_args['shortcode_gallery']);
        }
        if (!empty($fields_args['gallery_columns'])) {
            $columns = $fields_args['gallery_columns'];
        }
        else{
            $columns = Themify_Builder_Model::get_gallery_param_option($fields_args['shortcode_gallery'], 'columns');
            if(empty($columns)){
                $columns = $fields_default['gallery_columns'];
            }
        }
        $sc_image_size = Themify_Builder_Model::get_gallery_param_option($fields_args['shortcode_gallery'], 'size');
        if (!empty($sc_image_size)) {
            $fields_args['image_size_gallery'] = $sc_image_size;
        }
    }

    $masonry_class = $fields_args['layout_masonry'] === 'masonry' && 'grid' === $fields_args['layout_gallery'] ? 'gallery-masonry' : '';
    $container_class = apply_filters('themify_builder_module_classes', array(
        'module gallery', 
	'module-' . $mod_name,
	$element_id, 
	'gallery-columns-' . $columns,
	$masonry_class, 
	'layout-' . $fields_args['layout_gallery'],
	$fields_args['appearance_gallery'],
	$fields_args['css_gallery'],
	self::parse_animation_effect($fields_args['animation_effect'], $fields_args)
                    ), $mod_name, $element_id, $fields_args);
    
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
    $container_props = apply_filters('themify_builder_module_container_props', array(
        'class' => implode(' ', $container_class),
            ), $fields_args, $mod_name, $element_id);
    $args=null;
    ?>
    <!-- module gallery -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php $container_props=$container_class=null; 
	    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
        <?php if ($fields_args['mod_title_gallery'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_gallery'], $fields_args). $fields_args['after_title']; ?>
        <?php endif; ?>
        <?php
        if (!empty($fields_args['gallery_images'])) {
            // render the template
            self::retrieve_template('template-' . $mod_name . '-' . $fields_args['layout_gallery'] . '.php', array(
                'module_ID' => $element_id,
                'mod_name' => $mod_name,
                'columns' => $columns,
                'settings' => $fields_args
                    ), '', '', true);
        }
	$fields_args=null;
        ?>

    </div>
    <!-- /module gallery -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
