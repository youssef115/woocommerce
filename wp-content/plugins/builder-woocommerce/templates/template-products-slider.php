<?php
/**
 * @var $args['query_args'] the query parameters set by the module
 * @var $args['settings'] module config
 */

$arrow_vertical = $args['settings']['show_arrow_slider'] === 'yes' && $args['settings']['show_arrow_buttons_vertical'] === 'vertical' ? 'themify_builder_slider_vertical' : '';
$container_class =apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $args['mod_name'], 'module-slider', $args['module_ID'], 'themify_builder_slider_wrap', $arrow_vertical, 'clearfix', $args['settings']['layout_slider'],$args['settings']['css_products'] , self::parse_animation_effect( $args['settings']['animation_effect'], $args['settings'])
	), $args['mod_name'], $args['module_ID'], $args['settings'] );
if(!empty($args['element_id'])){
    $container_class[] = 'tb_'.$args['element_id'];
}
if(!empty($args['settings']['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $args['settings']['global_styles'];
}
$slide_margins = array();
$slide_margins[] = !empty($args['settings']['left_margin_slider']  ) ? sprintf( 'margin-left:%spx;', $args['settings']['left_margin_slider']  ) : '';
$slide_margins[] = !empty( $args['settings']['right_margin_slider'] ) ? sprintf( 'margin-right:%spx;', $args['settings']['right_margin_slider']  ) : '';
if(!empty($slide_margins)){
    $slide_margins = ' style="'.implode('',$slide_margins).'"';
}
$speed = $args['settings']['speed_opt_slider']==='slow'?4:($args['settings']['speed_opt_slider']==='fast'?'.5':1);
$container_props = apply_filters('themify_builder_module_container_props', array(
    'id' => $args['module_ID'],
    'class' => implode( ' ', $container_class),
	), $args['settings'], $args['mod_name'], $args['module_ID']);
?>
<!-- module products slider -->
<div id="<?php echo $args['module_ID']; ?>-loader" class="tb_slider_loader" style="<?php echo !empty($args['settings']['img_height_products']) ? 'height:'.$args['settings']['img_height_products'].'px;' : 'height:50px;'; ?>"></div>
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$args['settings'])); ?>>
	<?php $container_props=$container_class=null;?>
	<div class="woocommerce">

		<?php if ( $args['settings']['mod_title_products'] !== '' ): ?>
			<?php echo $args['settings']['before_title'] . apply_filters( 'themify_builder_module_title', $args['settings']['mod_title_products'] , $args['settings'] )  . $args['settings']['after_title']; ?>
		<?php endif; ?>

		<ul class="themify_builder_slider" 
			data-id="<?php echo $args['module_ID']; ?>" 
			data-visible="<?php echo $args['settings']['visible_opt_slider']; ?>"
			data-mob-visible="<?php echo $args['settings']['mob_visible_opt_slider'] ?>"
			data-scroll="<?php echo $args['settings']['scroll_opt_slider']; ?>" 
			data-auto-scroll="<?php echo $args['settings']['auto_scroll_opt_slider']; ?>"
			data-speed="<?php echo $speed; ?>"
			data-wrapper="<?php echo $args['settings']['wrap_slider']; ?>"
			data-arrow="<?php echo $args['settings']['show_arrow_slider'] ; ?>"
			data-pagination="<?php echo $args['settings']['pagination']; ?>"
			data-effect="<?php echo $args['settings']['effect_slider']; ?>" 
			data-height="<?php echo $args['settings']['height_slider'] ?>"
			data-pause-on-hover="<?php echo $args['settings']['pause_on_hover_slider']; ?>"
            data-play-controller="<?php echo $args['settings']['play_pause_control'] ?>" >

		<?php do_action( 'themify_builder_before_template_content_render' ); ?>

		<?php
		global $post;
		$builder_wc_temp_post = $post;
                $param_image = 'w='.$args['settings']['img_width_products'] .'&h='.$args['settings']['img_height_products'].'&ignore=true';
                if( Themify_Builder_Model::is_img_php_disabled() && $args['settings']['image_size_products']!=='' ) {
                    $param_image .= '&image_size=' . $args['settings']['image_size_products'];
                }
							
		$query = new WP_Query( $args['query_args'] );
		if( $query->have_posts() ) : while( $query->have_posts() ) : $query->the_post(); ?>

			<li>
				<div class="slide-inner-wrap"<?php echo $slide_margins; ?>>
					<?php
                        if( $args['settings']['hide_feat_img_products'] !== 'yes' && $post_image = apply_filters('woocommerce_single_product_image_thumbnail_html',themify_get_image( $param_image ),$post->id) ) { ?>
							<figure class="slide-image">
								<?php if( $args['settings']['unlink_feat_img_products'] === 'yes'): ?>
									<?php echo $post_image; ?>
								<?php else: ?>
									<a href="<?php the_permalink(); ?>" title="<?php echo the_title_attribute('echo=0'); ?>"><?php echo $post_image; ?></a>
								<?php endif; ?>
							</figure>
						<?php } // product image ?>

					<div class="slide-content">

						<?php if( $args['settings']['hide_sales_badge'] !== 'yes' ) : ?>
							<?php woocommerce_show_product_loop_sale_flash(); ?>
						<?php endif; ?>

						<?php if( $args['settings']['show_product_categories'] === 'yes' ): ?>
							<?php
                                $category_list = wc_get_product_category_list( $post->id );
                                if (!empty($category_list))
                                    echo '<div class="product-category-link">'.__('Category' ,'themify') .': '. $category_list .'</div>';
							?>

						<?php endif;?>
						<?php if( $args['settings']['show_product_tags'] === 'yes' ): ?>
							<?php
                                $tag_list = get_the_term_list( get_the_id(), 'product_tag','',', ' );
                                if(!empty($tag_list))
                                    echo '<div class="product-tag-link">'.__('Tag' ,'themify') .': '. $tag_list.'</div>'; ?>
						<?php endif;?>

						<?php if( $args['settings']['hide_post_title_products'] !== 'yes' ): ?>
							<?php if( $args['settings']['unlink_post_title_products'] === 'yes' ): ?>
								<h3><?php the_title(); ?></h3>
							<?php else: ?>
								<h3><a href="<?php the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
							<?php endif; //unlink post title ?>
						<?php endif; // hide post title ?>

						<?php
						if( $args['settings']['hide_rating_products'] !== 'yes' ) {
							woocommerce_template_loop_rating();
						} // product rating

						if( $args['settings']['hide_price_products'] !== 'yes' ) {
							woocommerce_template_loop_price();
						} // product price

						if( $args['settings']['hide_add_to_cart_products'] !== 'yes' ) {
							echo '<p class="add-to-cart-button">';
							woocommerce_template_loop_add_to_cart();
							echo '</p>';
						} // product add to cart
						?>

						<?php if($args['settings']['description_products']  === 'short' ) {
							woocommerce_template_single_excerpt();
						} elseif( $args['settings']['description_products']  === 'full' ) {
							the_content();
						}
						?>
					</div><!-- /slide-content -->
				</div>
			</li>

		<?php endwhile; wp_reset_postdata(); $post = $builder_wc_temp_post; unset( $builder_wc_temp_post,$query ); ?>
		<?php endif; ?>

		<?php do_action( 'themify_builder_after_template_content_render' ); ?>

		</ul>
	</div><!-- .woocommerce -->
</div>
<!-- /module products slider -->
