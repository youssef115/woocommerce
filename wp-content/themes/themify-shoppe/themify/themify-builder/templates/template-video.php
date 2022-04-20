<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Video
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):

    $fields_default = array(
        'mod_title_video' => '',
        'style_video' => '',
        'url_video' => '',
        'autoplay_video' => 'no',
        'mute_video' => 'no',
        'width_video' => '',
        'unit_video' => 'px',
        'title_video' => '',
        'title_link_video' => false,
        'caption_video' => '',
        'css_video' => '',
        'animation_effect' => '',
	'o_i_c'=>'',
	'o_i'=>'',
	'o_w'=>'',
	'o_h' => '',
    );

    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    
    $mod_name=$args['mod_name'];
    $builder_id = $args['builder_id'];
    $element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
    
    if($fields_args['o_i_c']!==''){
	$fields_args['o_i_c'] = self::get_checkbox_data($fields_args['o_i_c']);
    }
    $video_maxwidth = $fields_args['width_video'] !== '' ? $fields_args['width_video'] . $fields_args['unit_video'] : '';
    $video_autoplay_css = $fields_args['autoplay_video'] === 'yes' ? 'video-autoplay' : '';
    $container_class = apply_filters('themify_builder_module_classes', array(
        'module',
	'module-' . $mod_name,
	$element_id, 
	$fields_args['style_video'], 
	$fields_args['css_video'], 
	self::parse_animation_effect($fields_args['animation_effect'], $fields_args), 
	$video_autoplay_css
    ), $mod_name, $element_id, $fields_args);
  
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
	if ( $fields_args['o_i_c'] === '1' && ! empty( $fields_args['o_i'] ) ) {
		$container_class[] = 'tb_has_cover';
	}
    $container_props = apply_filters('themify_builder_module_container_props', array(
        'class' => implode(' ', $container_class),
            ), $fields_args, $mod_name, $element_id);
    $args=null;
    add_filter('oembed_result', array('TB_Video_Module', 'modify_youtube_embed_url'), 10, 3);
	$muted = ($fields_args['mute_video'] === 'yes') ? ' data-muted="1"' : '';
	$autoplay = ($fields_args['autoplay_video'] === 'yes') ? ' data-autoplay="1"' : '';
	$url = esc_url( $fields_args['url_video'] );
    ?>

    <!-- module video -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php 
	    $container_props=$container_class=null;
	    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
        <?php if ($fields_args['mod_title_video'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_video'], $fields_args). $fields_args['after_title']; ?>
        <?php endif; ?>
        <div class="video-wrap"<?php echo '' !== $video_maxwidth ? ' style="max-width:' . $video_maxwidth . ';"' : ''; ?>>

			<div class="tb_video_overlay" data-href="<?php echo $url ?>"<?php echo $muted; ?><?php echo $autoplay; ?>>
				<span class="tb_video_play"></span>
				<?php if ( $fields_args['o_i_c'] === '1' && ! empty( $fields_args['o_i'] ) ) : ?>
					<?php echo themify_get_image(array('src'=>$fields_args['o_i'],'w' => $fields_args['o_w'],'alt'=>$fields_args['title_video'], 'h' => $fields_args['o_h'], 'ignore' => true))?>
				<?php endif; ?>
			</div>

        </div>
        <!-- /video-wrap -->

        <?php if ('' !== $fields_args['title_video'] || '' !== $fields_args['caption_video']): ?>
            <div class="video-content">
                <?php if ('' !== $fields_args['title_video']): ?>
                    <h3 class="video-title"<?php if(Themify_Builder::$frontedit_active===true && !$fields_args['title_link_video']):?> contenteditable="false" data-name="title_video"<?php endif;?>>
                        <?php if ($fields_args['title_link_video']) : ?>
                            <a<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="title_video"<?php endif;?> href="<?php echo esc_url($fields_args['title_link_video']); ?>"><?php echo $fields_args['title_video']; ?></a>
                        <?php else: ?>
                        <?php echo $fields_args['title_video']; ?>
                        <?php endif; ?>
                    </h3>
                <?php endif; ?>

                <?php if ('' !== $fields_args['caption_video']): ?>
                    <div class="video-caption tb_text_wrap"<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="caption_video"<?php endif;?>>
                        <?php echo apply_filters('themify_builder_module_content', $fields_args['caption_video']); ?>
                    </div>
                    <!-- /video-caption -->
                <?php endif; ?>
            </div>
            <!-- /video-content -->
        <?php endif; ?>
    </div>
    <!-- /module video -->
    <?php remove_filter('oembed_result', array('TB_Video_Module', 'modify_youtube_embed_url'), 10, 3); ?>
<?php endif; ?>
<?php TFCache::end_cache(); ?>