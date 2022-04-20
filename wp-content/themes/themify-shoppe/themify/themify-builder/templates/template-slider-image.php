<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Slider Image
 * 
 * Access original fields: $args['settings']
 * @author Themify
 */
if (!empty($args['settings']['img_content_slider'])):
    $image_w = $args['settings']['img_w_slider'];
    $image_h = $args['settings']['img_h_slider'];
    $is_img_disabled = Themify_Builder_Model::is_img_php_disabled();
    if ($is_img_disabled===true) {
        // get image preset
        global $_wp_additional_image_sizes;
        $preset = $args['settings']['image_size_slider'] !== '' ? $args['settings']['image_size_slider'] : themify_builder_get('setting-global_feature_size', 'image_global_size_field');
        if (isset($_wp_additional_image_sizes[$preset]) && $image_size_slider !== '') {
            $image_w = (int) $_wp_additional_image_sizes[$preset]['width'];
            $image_h = (int) $_wp_additional_image_sizes[$preset]['height'];
        } else {
            $image_w = $image_w !== '' ? $image_w : get_option($preset . '_size_w');
            $image_h = $image_h !== '' ? $image_h : get_option($preset . '_size_h');
        }
    } else {
        $param_image_src = array('w' => $image_w, 'h' => $image_h, 'ignore' => true);
    }
    ?>
    <!-- module slider image -->

    <?php foreach ($args['settings']['img_content_slider'] as $content): ?>
        <?php $image_title = isset($content['img_title_slider']) ? trim($content['img_title_slider']) : '';
		$isAlt=false;
	?>
        <li>
            <div class="slide-inner-wrap"<?php if ($args['settings']['margin'] !== ''): ?> style="<?php echo $args['settings']['margin']; ?>"<?php endif; ?>>
                <?php if ( ! empty( $content['img_url_slider'] ) ) : ?>
                    <div class="slide-image">
                        <?php
			    if ( $image_title===''  ) {
				$image_title = Themify_Builder_Model::get_alt_by_url( $content['img_url_slider'] );
				$isAlt=true;
			    }
                        if ($is_img_disabled===true) {
							$upload_dir = wp_upload_dir();
							$base_url = $upload_dir['baseurl'];
							$attachment_id = themify_get_attachment_id_from_url($content['img_url_slider'], $base_url);
							if (!empty($attachment_id)) {
								$image = wp_get_attachment_image($attachment_id, $preset);
                                $image=preg_replace('/width=\"([0-9]{1,})\"/','width="'.$image_w.'"',$image);
                                $image=preg_replace('/height=\"([0-9]{1,})\"/','height="'.$image_h.'"',$image);
							}else{
								$image = '<img src="' . esc_url($content['img_url_slider']) . '" alt="' . esc_attr($image_title) .'" width="' . $image_w . '" height="' . $image_h . '" />';
							}
                        } else {
                            $param_image_src['src'] = $content['img_url_slider'];
                            $param_image_src['alt'] = $image_title;
                            $image = themify_get_image($param_image_src);
                        }
                        ?>
                        <?php if (!empty($content['img_link_slider'])): ?>
                            <?php
                            $attr = '';
                            if (isset($content['img_link_params'])) {
                                $attr = $content['img_link_params'] === 'lightbox' ? ' data-rel="' . $args['module_ID'] . '" class="themify_lightbox"' : ($content['img_link_params'] === 'newtab' ? ' target="_blank" rel="noopener"' : '');
                            }
                            ?>
                            <a href="<?php echo esc_url(trim($content['img_link_slider'])); ?>" alt="<?php echo esc_attr( $image_title ); ?>"<?php echo $attr; ?>>
                                <?php echo $image; ?>
                            </a>
                        <?php else: ?>
                            <?php echo $image; ?>
                        <?php endif; ?>
                    </div><!-- /slide-image -->
                <?php endif; ?>

                <?php if (($isAlt===false && $image_title !== '') || isset($content['img_caption_slider'])): ?>
                    <div class="slide-content tb_text_wrap">

                        <?php if ($isAlt===false && $image_title !== ''): ?>
                            <h3 class="slide-title">
                                <?php if (!empty($content['img_link_slider'])): ?>
                                    <a href="<?php echo esc_url($content['img_link_slider']); ?>"<?php echo $attr; ?>><?php echo wp_kses_post($image_title); ?></a>
                                <?php else: ?>
                                    <?php echo $image_title; ?>
                                <?php endif; ?>
                            </h3>
                        <?php endif; ?>

                        <?php
                        if (isset($content['img_caption_slider'])) {
                            echo apply_filters('themify_builder_module_content', $content['img_caption_slider']);
                        }
                        ?>
                    </div><!-- /slide-content -->
                <?php endif; ?>
            </div>
        </li>
    <?php endforeach; ?>
<?php endif; ?>
