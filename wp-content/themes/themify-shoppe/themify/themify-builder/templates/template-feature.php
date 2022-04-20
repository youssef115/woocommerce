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

    $chart_vars = apply_filters('themify_chart_init_vars', array(
        'trackColor' => 'rgba(0,0,0,.1)',
        'size' => 150
    ));

    $fields_default = array(
        'mod_title_feature' => '',
        'title_feature' => '',
        'overlap_image_feature' => '',
        'overlap_image_width' => '',
        'overlap_image_height' => '',
        'layout_feature' => 'icon-top',
        'content_feature' => '',
        'circle_percentage_feature' => '',
        'circle_color_feature' => '',
        'circle_stroke_feature' => '',
        'icon_type_feature' => 'icon',
        'image_feature' => '',
        'icon_feature' => '',
        'icon_color_feature' => '',
        'icon_bg_feature' => '',
        'circle_size_feature' => 'medium',
        'custom_circle_size_feature' => '',
        'link_feature' => '',
        'feature_download_link'=>'',
        'link_options' => false,
        'lightbox_width' => '',
        'lightbox_height' => '',
        'lightbox_width_unit' => 'px',
        'lightbox_height_unit' => 'px',
        'css_feature' => '',
        'animation_effect' => ''
    );
    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
	$fields_args['lightbox_width_unit'] = $fields_args['lightbox_width_unit'] ? $fields_args['lightbox_width_unit'] : 'px';
	$fields_args['lightbox_height_unit'] = $fields_args['lightbox_height_unit'] ? $fields_args['lightbox_height_unit'] : 'px';
    unset($args['mod_settings']);
    $mod_name=$args['mod_name'];
    $builder_id = $args['builder_id'];
    $element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
    /* configure the chart size based on the option */
    if ($fields_args['circle_size_feature'] === 'large') {
        $chart_vars['size'] = 200;
    } elseif ($fields_args['circle_size_feature'] === 'medium') {
        $chart_vars['size'] = 150;
    } elseif ($fields_args['circle_size_feature'] === 'small') {
        $chart_vars['size'] = 100;
    }else{
	$chart_vars['size'] = isset($fields_args['custom_circle_size_feature']) ? $fields_args['custom_circle_size_feature'] : 150;
    }

    $fields_args['circle_percentage_feature'] = str_replace('%', '', $fields_args['circle_percentage_feature']); // remove % if added by user

    if ($fields_args['circle_percentage_feature'] === '') {
        $chart_class = 'no-chart';
        $fields_args['circle_percentage_feature'] = 0;
        $chart_vars['trackColor'] = 'rgba(0,0,0,0)'; // transparent
    } else {
        if ($fields_args['circle_percentage_feature'] > 100) {
            $fields_args['circle_percentage_feature'] = '100';
        }
        $chart_class = 'with-chart';
    }
    if ('' !== $fields_args['overlap_image_feature']) {
        $chart_class .= ' with-overlay-image';
    }
    $link_type = $link_attr = '';
    if (!empty($fields_args['link_options'])) {
        $link_type = 'regular';
        if ($fields_args['link_options'] === 'lightbox') {
            $link_type = 'lightbox';

            if ($fields_args['lightbox_width'] !== '' || $fields_args['lightbox_height'] !== '') {
                $lightbox_settings = array();
                $lightbox_settings[] = $fields_args['lightbox_width'] !== '' ? $fields_args['lightbox_width'] . $fields_args['lightbox_width_unit'] : '';
                $lightbox_settings[] = $fields_args['lightbox_height'] !== '' ? $fields_args['lightbox_height'] . $fields_args['lightbox_height_unit'] : '';

                $link_attr = sprintf('data-zoom-config="%s"', implode('|', $lightbox_settings));
            }
        } elseif ($fields_args['link_options'] === 'newtab') {
            $link_type = 'newtab';
        }
    }

    $container_class = apply_filters('themify_builder_module_classes', array(
        'module',
	'module-' . $mod_name,
	$element_id, 
	$chart_class,
	'layout-' . $fields_args['layout_feature'], 
	'size-' . $fields_args['circle_size_feature'],
	$fields_args['css_feature'], 
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
    <!-- module feature -->
   <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
        <?php
        // DYNAMIC STYLE
	$container_props=$container_class=null;
	do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
        $insetColor = $fields_args['icon_bg_feature'] !== '' ? esc_attr(Themify_Builder_Stylesheet::get_rgba_color($fields_args['icon_bg_feature'])) : '';
        $style = '<style type="text/css">';
        if ($fields_args['circle_stroke_feature']) {
            $fields_args['circle_stroke_feature'] = (int) $fields_args['circle_stroke_feature'];
            $circleBackground = $chart_vars['trackColor'];
            $circleColor = !empty($fields_args['circle_color_feature']) ? esc_attr(Themify_Builder_Stylesheet::get_rgba_color($fields_args['circle_color_feature'])): '';
            $style.=".{$element_id} .module-feature-chart-html5 {
			    box-shadow: inset 0 0 0 " . $fields_args['circle_stroke_feature'] . "px {$circleBackground};
			}
			.{$element_id} .chart-loaded.chart-html5-fill {
			    box-shadow: inset 0 0 0 " . $fields_args['circle_stroke_feature'] . "px {$circleColor};
		    }";
        }
        if ($insetColor !== '') {
            $style.=".{$element_id} .chart-html5-inset {background-color: {$insetColor}}";
        }
        $style.='</style>';
        echo $style;
        ?>

        <?php if ($fields_args['mod_title_feature'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_feature'], $fields_args) . $fields_args['after_title']; ?>
        <?php endif; ?>

        <div class="module-feature-image">
            <?php
            if ('' !== $fields_args['overlap_image_feature']) {
                echo themify_get_image('src=' . $fields_args['overlap_image_feature'] . '&w=' . $fields_args['overlap_image_width'] . '&h=' . $fields_args['overlap_image_height'] . '&ignore=true');
            };
	    if ( !empty($fields_args['feature_download_link']) && $fields_args['feature_download_link'] == 'yes'  ){
		    $link_attr .= ' download';
	    }
            ?>

            <?php if ('' !== $fields_args['link_feature']) : ?>
                <a href="<?php echo esc_url($fields_args['link_feature']); ?>" <?php
                if ('lightbox' === $link_type) : echo 'class="themify_lightbox"';
                elseif ('newtab' === $link_type):echo 'target="_blank" rel="noopener"';
                endif;
                ?> <?php echo $link_attr; ?>>
                   <?php endif; ?>

                <div class="module-feature-chart-html5"
                <?php if (!empty($fields_args['circle_percentage_feature'])): ?>
                         data-progress="0"
                         data-progress-end="<?php esc_attr_e($fields_args['circle_percentage_feature']) ?>"
                         data-size="<?php echo $chart_vars['size']; ?>"
                     <?php endif; ?>
                     >
                    <div class="chart-html5-circle">
                        <?php if (!empty($fields_args['circle_percentage_feature'])): ?>
                            <div class="chart-html5-mask chart-html5-full">
                                <div class="chart-html5-fill"></div>
                            </div>
                            <div class="chart-html5-mask chart-html5-half">
                                <div class="chart-html5-fill"></div>
                            </div>
                        <?php endif; ?>
                        <div class="chart-html5-inset<?php if ('icon' === $fields_args['icon_type_feature'] && '' !== $fields_args['icon_feature']) echo ' chart-html5-inset-icon' ?>">

                            <?php if (strpos($fields_args['icon_type_feature'], 'image') !== false) : ?>
				<?php if($fields_args['image_feature'] !== ''):?>
				    <?php $alt = ( $alt_text = Themify_Builder_Model::get_alt_by_url($fields_args['image_feature']) ) ? $alt_text : $fields_args['title_feature']; ?>
				    <img src="<?php echo esc_url($fields_args['image_feature']); ?>" alt="<?php echo esc_attr($alt); ?>" />
				<?php endif; ?>
                            <?php else : ?>
                                <?php if ('' !== $insetColor) : ?><div class="module-feature-background" style="background: <?php echo $insetColor; ?>"></div><?php endif; ?>
                                <?php if ('' !== $fields_args['icon_feature']) : ?><i class="module-feature-icon <?php echo esc_attr(themify_get_icon($fields_args['icon_feature'])); ?>"<?php echo !empty($fields_args['icon_color_feature'])? ' style="color: '. esc_attr(Themify_Builder_Stylesheet::get_rgba_color($fields_args['icon_color_feature'])).'"':''; ?>></i><?php endif; ?>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>

                <?php if ('' !== $fields_args['link_feature']) : ?>
                </a>
            <?php endif; ?>
        </div>

        <div class="module-feature-content">
			<?php
				if ( ''!== $fields_args['title_feature']  ) {
                                    
                                    ?>
                                        <h3 class="module-feature-title"<?php if (Themify_Builder::$frontedit_active===true && '' === $fields_args['link_feature']):?> contenteditable="false" data-name="title_feature"<?php endif;?>>
                                            <?php if ('' !== $fields_args['link_feature']):?>
                                                <?php if($link_type==='newtab'){
                                                    $link_attr .= ' target="_blank" rel="noopener"';
                                                }
                                                elseif($link_type==='lightbox'){
                                                     $link_attr .= ' class="themify_lightbox"';
                                                }
                                            ?>
                                                <a<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="title_feature"<?php endif;?> href="<?php echo esc_url( $fields_args['link_feature'] )?>"<?php echo $link_attr?>><?php echo $fields_args['title_feature'] ?></a>
                                            <?php else:?>
                                                <?php echo $fields_args['title_feature'] ?>
                                            <?php endif;?>
                                        </h3>
                                <?php
				}
			?>
	    <div class="tb_text_wrap<?php if(Themify_Builder::$frontedit_active===true):?> tb_editor_enable<?php endif;?>"<?php if(Themify_Builder::$frontedit_active===true):?> contenteditable="false" data-name="content_feature"<?php endif;?>>
		<?php echo $fields_args['content_feature'] !== ''?apply_filters('themify_builder_module_content',$fields_args['content_feature']):''; ?>
	    </div>
	</div>

    </div>
    <!-- /module feature -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
