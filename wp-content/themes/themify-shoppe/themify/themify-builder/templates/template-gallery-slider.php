<?php ! defined( 'ABSPATH' ) && exit;
/**
 * Template Gallery Slider
 * 
 * Access original fields: $args['settings']
 * @author Themify
 */

if( $args['settings']['layout_gallery'] === 'slider' ) :
	$is_img_disabled = Themify_Builder_Model::is_img_php_disabled();

	$margins = '';

	if( $args['settings']['left_margin_slider'] !== '' ) {
		$margins .= 'margin-left:' . $args['settings']['left_margin_slider'] . 'px;';
	}

	if ($args['settings']['right_margin_slider'] !== '') {
		$margins .= 'margin-right:' . $args['settings']['right_margin_slider'] . 'px;';
	}

	foreach( array( 'slider', 'thumbs' ) as $mode ) :
        $is_slider = $mode === 'slider';
	    if(!$is_slider && 'yes' === $args['settings']['slider_thumbs']){
	        continue;
        }
?>

<ul<?php if(!$is_slider):?> id="<?php echo $args['module_ID']?>-thumbs"<?php endif;?> class="themify_builder_slider"
	data-id="<?php echo $args['module_ID'] ?>"
	data-visible="<?php echo ! $is_slider ? $args['settings']['visible_opt_slider'] : 1; ?>"
	data-tab-visible="<?php echo ! $is_slider ? $args['settings']['tab_visible_opt_slider'] : 1;?>"
	data-mob-visible="<?php echo ! $is_slider ? $args['settings']['mob_visible_opt_slider'] : 1;?>"
	data-scroll="1"
	data-auto-scroll="<?php echo $is_slider ? $args['settings']['auto_scroll_opt_slider'] : ''; ?>"
	data-speed="<?php echo $args['settings']['speed_opt_slider'] === 'slow' ? 4 : ($args['settings']['speed_opt_slider'] === 'fast' ? '.5' : 1) ?>"
	data-wrap="<?php echo $args['settings']['wrap_slider']; ?>"
	data-arrow="<?php echo $mode === ( $args['settings']['show_arrow_buttons_vertical'] ? 'slider' : 'thumbs' ) ? $args['settings']['show_arrow_slider'] : ''; ?>"
	data-pagination="0"
	data-effect="<?php echo $is_slider ? $args['settings']['effect_slider'] : 'scroll'; ?>" 
	data-height="<?php echo isset($args['settings']['horizontal']) && $args['settings']['horizontal'] === 'yes' ? 'variable' : $args['settings']['height_slider'] ?>"
	<?php $is_slider && printf( 'data-sync="#%s-thumbs"', $args['module_ID'] ); ?>
	data-pause-on-hover="<?php echo $args['settings']['pause_on_hover_slider'] ?>">

<?php foreach( $args['settings']['gallery_images'] as $i=>$image ) : ?>
	<li data-index="<?php echo $i?>">
	<div class="slide-inner-wrap"<?php $margins && printf( ' style="%s"', $margins ) ?>>
		<div class="slide-image gallery-icon"><?php

			$image_html = $is_img_disabled 
				? wp_get_attachment_image( $image->ID, 'full' )
				: themify_get_image( array(
					'w' => ! $is_slider ? $args['settings']['thumb_w_gallery'] : $args['settings']['s_image_w_gallery'],
					'h' => ! $is_slider ? $args['settings']['thumb_h_gallery'] : $args['settings']['s_image_h_gallery'],
					'ignore' => true,
					'alt' => get_post_meta( $image->ID, '_wp_attachment_image_alt', true ),
					'src' => wp_get_attachment_image_url( $image->ID, 'full' )
				) );

			$lightbox = '';
			$link=null;
			if( $is_slider===true){
				if( $args['settings']['link_opt'] === 'file' ) {
					$link = wp_get_attachment_image_src( $image->ID, $args['settings']['link_image_size'] );
					$link = $link[0];
					$lightbox = ' class="themify_lightbox"';
				} elseif( 'none' !== $args['settings']['link_opt'] ) {
					$link = get_attachment_link( $image->ID );
				}
			}
			if($is_slider===true && ! empty( $link )) {
				printf( '<a href="%s"%s>%s</a>', esc_url( $link ), $lightbox, $image_html );
			} else {
				echo $image_html;
			}
		?>
		</div>
		<?php if( ( $args['settings']['gallery_image_title'] && $image->post_title || ! $args['settings']['gallery_exclude_caption'] && $image->post_excerpt ) && $is_slider ) : ?>
			<div class="slide-content">
				<?php 
					$args['settings']['gallery_image_title'] && ! empty( $image->post_title )
						&& printf( '<h3 class="slide-title">%s</h3>', wp_kses_post( $image->post_title ) );

					! $args['settings']['gallery_exclude_caption'] && ! empty( $image->post_excerpt )
						&& printf( '<p>%s</p>', apply_filters( 'themify_builder_module_content', $image->post_excerpt ) );
				?>
			</div><!-- /slide-content -->
		<?php endif; ?>
	</div></li>
<?php endforeach; ?>
</ul>
<?php endforeach; ?>
<?php endif; ?>
