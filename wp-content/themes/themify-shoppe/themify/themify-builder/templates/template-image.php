<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Image
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):

    $fields_default = array(
        'mod_title_image' => '',
        'style_image' => '',
        'url_image' => '',
        'appearance_image' => '',
        'caption_on_overlay' => '',
        'image_size_image' => '',
        'width_image' => '',
        'auto_fullwidth' => false,
        'height_image' => '',
        'title_image' => '',
        'link_image' => '',
        'param_image' => '',
        'image_zoom_icon' => '',
        'lightbox_width' => '',
        'lightbox_height' => '',
	'lightbox_width_unit' => 'px',
	'lightbox_height_unit' => 'px',
        'alt_image' => '',
        'caption_image' => '',
        'css_image' => '',
        'animation_effect' => ''
    );

    if (isset($args['mod_settings']['appearance_image'])) {
        $args['mod_settings']['appearance_image'] = self::get_checkbox_data($args['mod_settings']['appearance_image']);
    }
    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    
    $mod_name=$args['mod_name'];
    $builder_id = $args['builder_id'];
    $element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
    
    $lightbox_size_unit_width = $fields_args['lightbox_width_unit']?$fields_args['lightbox_width_unit']:'px';
    $lightbox_size_unit_height = $fields_args['lightbox_height_unit']?$fields_args['lightbox_height_unit']:'px';
    
    $container_class = apply_filters('themify_builder_module_classes', array(
        'module', 
	'module-' . $mod_name,
	$element_id,
	$fields_args['appearance_image'], 
	$fields_args['style_image'], 
	$fields_args['css_image'], 
	self::parse_animation_effect($fields_args['animation_effect'], $fields_args)
                    ), $mod_name, $element_id, $fields_args);
    
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
    if (  'yes' === $fields_args['caption_on_overlay']  && ('' === $fields_args['style_image'] || $fields_args['style_image'])){
		$container_class[]= ' active-caption-hover';
    }
    if ($fields_args['auto_fullwidth']=='1') {
        $container_class[]=' auto_fullwidth';
    }
    $lightbox = $fields_args['param_image'] === 'lightbox';
    $zoom = $fields_args['image_zoom_icon'] === 'zoom';
    $zoom_icon = $fields_args['param_image'] === 'lightbox' ? 'ti-search' : 'ti-new-window';
    $newtab = !$lightbox && $fields_args['param_image'] === 'newtab';
    $lightbox_data = !empty($fields_args['lightbox_width']) || !empty($fields_args['lightbox_height']) ? sprintf(' data-zoom-config="%s|%s"'
                    , $fields_args['lightbox_width'] . $lightbox_size_unit_width, $fields_args['lightbox_height'] . $lightbox_size_unit_height) : false;
    $image_alt = '' !== $fields_args['alt_image'] ? $fields_args['alt_image'] : wp_strip_all_tags($fields_args['caption_image']);
    $image_title = $fields_args['title_image'];
    if ($image_alt === '') {
        $image_alt = $image_title;
    }
    if (Themify_Builder_Model::is_img_php_disabled()) {
        $preset = $fields_args['image_size_image'] !== '' ? $fields_args['image_size_image'] : themify_builder_get('setting-global_feature_size', 'image_global_size_field');
        $upload_dir = wp_upload_dir();
        $base_url = $upload_dir['baseurl'];
        $attachment_id = themify_get_attachment_id_from_url($fields_args['url_image'], $base_url);
		if (!empty($attachment_id)) {
            $image = wp_get_attachment_image($attachment_id, $preset);
			if($fields_args['width_image']!==''){
				$image=preg_replace('/width=\"([0-9]{1,})\"/','width="'.$fields_args['width_image'].'"',$image);
			}
			if($fields_args['height_image']!==''){
				$image=preg_replace('/height=\"([0-9]{1,})\"/','height="'.$fields_args['height_image'].'"',$image);
			}
        }
		else{
			$image = '<img src="' . esc_url($fields_args['url_image']) . '" alt="' . esc_attr($image_alt) . (!empty($image_title) ? ( '" title="' . esc_attr($image_title) ) : '' ) . '" width="' . $fields_args['width_image'] . '" height="' . $fields_args['height_image'] . '" />';
		}
      
    } else {
        $image = themify_get_image('src=' . esc_url($fields_args['url_image']) . '&w=' . $fields_args['width_image'] . '&h=' . $fields_args['height_image'] . '&alt=' . $image_alt . (!empty($image_title) ? ( '&title=' . $image_title ) : '' ) . '&ignore=true');
    }

    $container_props = apply_filters('themify_builder_module_container_props', array(
        'class' => implode(' ', $container_class),
            ), $fields_args, $mod_name, $element_id);
    $args=null;
    ?>
    <!-- module image -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php $container_props=$container_class=null; 
	    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
        <?php if ($fields_args['mod_title_image'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_image'], $fields_args). $fields_args['after_title']; ?>
        <?php endif; ?>
        <div class="image-wrap">
            <?php if ($fields_args['link_image'] !== ''): ?>
                <a href="<?php echo esc_url($fields_args['link_image']); ?>"
                   <?php if ($lightbox) : ?>class="lightbox-builder themify_lightbox"<?php echo $lightbox_data; ?><?php endif; ?>
                   <?php if ($newtab): ?> rel="noopener" target="_blank"<?php endif; ?>>
                       <?php if ($zoom): ?>
                        <span class="zoom <?php echo $zoom_icon; ?>"></span>
                    <?php endif; ?>
                    <?php echo $image; ?>
                </a>
            <?php else: ?>
                <?php echo $image; ?>
            <?php endif; ?>

            <?php if ('image-overlay' !== $fields_args['style_image']): ?>
            </div>
            <!-- /image-wrap -->
        <?php endif; ?>

        <?php if ($image_title !== '' || $fields_args['caption_image'] !== ''): ?>
            <div class="image-content">
                <?php if ($image_title !== ''): ?>
                    <h3 class="image-title"<?php if($fields_args['link_image'] === '' && Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="title_image"<?php endif;?>>
                        <?php if ($fields_args['link_image'] !== ''): ?>
                            <a<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="title_image"<?php endif; ?> href="<?php echo esc_url($fields_args['link_image']); ?>" 
                               <?php if ($lightbox) : ?> class="lightbox-builder themify_lightbox"<?php echo $lightbox_data; ?><?php endif; ?>
                               <?php if ($newtab): ?> rel="noopener" target="_blank"<?php endif; ?>>
                                   <?php echo $image_title; ?>
                            </a>
                        <?php else: ?>
                            <?php echo $image_title; ?>
                        <?php endif; ?>
                    </h3>
                <?php endif; ?>

		<?php if ($fields_args['caption_image'] !== ''): ?>
                    <div class="image-caption tb_text_wrap"<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="caption_image"<?php endif; ?>>
                        <?php echo apply_filters('themify_builder_module_content', $fields_args['caption_image']); ?>
                </div>
                <!-- /image-caption -->
                <?php endif; ?>
            </div>
            <!-- /image-content -->
        <?php endif; ?>

    <?php if ('image-overlay' === $fields_args['style_image']): ?>
        </div>
        <!-- /image-wrap -->
    <?php endif; ?>
    </div>
    <!-- /module image -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>