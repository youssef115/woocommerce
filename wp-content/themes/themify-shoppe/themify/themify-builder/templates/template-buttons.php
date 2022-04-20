<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Buttons
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):

    $fields_default = array(
        'mod_title_button' => '',
        'buttons_size' => '',
        'buttons_shape' => 'circle',
        'buttons_style' => 'solid',
        'fullwidth_button' => '',
	'nofollow_link'=> '',
        'download_link'=> '',
        'display' => 'buttons-horizontal',
	'alignment'=>'',
        'content_button' => array(),
        'animation_effect' => '',
        'css_button' => ''
    );

    /* for old button style args*/
    if ( isset($args['mod_settings']['buttons_style']) && in_array( $args['mod_settings']['buttons_style'], array('circle', 'rounded', 'squared' ),true) ) {
	    $args['mod_settings']['buttons_shape'] = $args['mod_settings']['buttons_style'];
	    unset($args['mod_settings']['buttons_style']);
    }
    /* End of old button style args */
    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    unset($args['mod_settings']);
    $mod_name=$args['mod_name'];
    $builder_id = $args['builder_id'];
    $element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
    $container_class = apply_filters('themify_builder_module_classes', array(
	'module',
	'module-' . $mod_name,
	$element_id,
	$fields_args['buttons_size'],
	$fields_args['buttons_style'],
	$fields_args['buttons_shape'],
	$fields_args['css_button'],
	self::parse_animation_effect($fields_args['animation_effect'], $fields_args),
    ), $mod_name, $element_id, $fields_args);
    if(!empty($fields_args['fullwidth_button'])){
	$fields_args['alignment'] = $fields_args['display']='';
    }
    elseif($fields_args['alignment']!==''){
	$container_class[] = 'tb-align-' . $fields_args['alignment'];

    }
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
    $container_props = apply_filters('themify_builder_module_container_props', array(
        'class' => implode(' ', $container_class),
            ), $fields_args, $mod_name, $element_id);
    ?>
    <!-- module buttons -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php $container_props=$container_class=null; 
	    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
        <?php if ($fields_args['mod_title_button'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_button'], $fields_args) . $fields_args['after_title']; ?>
        <?php endif; ?>
	<div class="module-<?php echo $mod_name?>">
            <?php
	    $args=null;
            $content_button = array_filter($fields_args['content_button']);
            foreach ($content_button as $i=>$content):
                $content = wp_parse_args($content, array(
                    'label' => '',
                    'link' => '',
                    'icon' => '',
                    'icon_alignment' => 'left',
                    'link_options' => false,
                    'lightbox_width' => '',
                    'lightbox_height' => '',
                    'lightbox_width_unit' => 'px',
                    'lightbox_height_unit' => 'px',
                    'button_color_bg' => 'tb_default_color'
                ));

                $link_css_clsss = array('ui builder_button');
                $link_attr = array();

                if ($content['link_options'] === 'lightbox') {
                    $link_css_clsss[] = 'themify_lightbox';

                    if ($content['lightbox_width']!=='' || $content['lightbox_height']!=='') {
                        $lightbox_settings = array();
                        if($content['lightbox_width']!==''){
                            $lightbox_settings[] = $content['lightbox_width'].$content['lightbox_width_unit'];
                        }
                        if($content['lightbox_height']!==''){
                            $lightbox_settings[] = $content['lightbox_height'].$content['lightbox_height_unit'];
                        }
                        $link_attr[] = sprintf('data-zoom-config="%s"', implode('|', $lightbox_settings));
                    }
                } elseif ($content['link_options'] === 'newtab') {
                    $nofollow = $fields_args['nofollow_link'] === 'yes' ? 'nofollow ':'';
                    $link_attr[] = 'target="_blank" rel="'.$nofollow.'noopener"';
                }
                $link_css_clsss[] = $content['button_color_bg'];
                if ( $fields_args['nofollow_link'] === 'yes' && $content['link_options'] !== 'newtab' ){
		            $link_attr[] = 'rel="nofollow"';
                }

                if ($fields_args['download_link'] === 'yes'  ){
		    $link_attr[] = 'download';
                }
		$icon =$content['icon']? sprintf( '<i class="%s"></i>', themify_get_icon($content['icon'] )):'';
                ?>
                <div class="module-buttons-item <?php echo $fields_args['fullwidth_button']?> <?php echo $fields_args['display']?>">
		    <?php if( $content['link'] ):?>
			<a href="<?php echo esc_url( $content['link'] )?>" class="<?php echo implode( ' ', $link_css_clsss )?>" <?php echo implode( ' ', $link_attr )?>>
		    <?php endif;?>
		    <?php if( $content['icon_alignment'] !== 'right' && $icon!==''):?>
			    <?php echo $icon?>
		    <?php endif;?>
			<span<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="label" data-repeat="content_button" data-index="'.$i.'"<?php endif;?>><?php echo $content['label']?></span>
		    <?php if( $content['icon_alignment'] === 'right' && $icon!==''):?>
			    <?php echo $icon?>
		    <?php endif;?>
		    <?php if( $content['link'] ):?>
			</a>
		    <?php endif;?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <!-- /module buttons -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
