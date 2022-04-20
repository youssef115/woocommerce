<?php
/**
 * @var $args['query_args'] the query parameters set by the module
 * @var $args['settings'] module config
 */

$container_class =apply_filters( 'themify_builder_module_classes', array(
		'module', 'module-' . $args['mod_name'], $args['module_ID'],$args['settings']['css_products'] ,self::parse_animation_effect( $args['settings']['animation_effect'], $args['settings'] )
	), $args['mod_name'], $args['module_ID'], $args['settings'] );
if(isset($args['element_id'])){
    $container_class[] = 'tb_'.$args['element_id'];
}
if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters( 'themify_builder_module_container_props', array(
	'id' => $args['module_ID'],
	'class' => implode(' ', $container_class),
), $args['settings'], $args['mod_name'], $args['module_ID'] );
?>
<!-- module products -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$args['settings'])); ?>>
	<?php $container_props=$container_class=null;?>
	<div class="woocommerce">

		<?php if ( $args['settings']['mod_title_products'] !== '' ): ?>
			<?php echo $args['settings']['before_title'] . apply_filters( 'themify_builder_module_title', $args['settings']['mod_title_products'], $args['settings'] ) . $args['settings']['after_title']; ?>
		<?php endif; ?>

		<?php do_action( 'themify_builder_before_template_content_render' ); ?>

		<?php
		global $post;
		$builder_wc_temp_post = $post;
		$query = new WP_Query( $args['query_args'] );
		if( $query->have_posts() ) : ?>
			<?php
				$param_image = 'w='.$args['settings']['img_width_products'] .'&h='.$args['settings']['img_height_products'].'&ignore=true';
				if( Themify_Builder_Model::is_img_php_disabled() && $args['settings']['image_size_products']!=='' ) {
					$param_image .= '&image_size=' . $args['settings']['image_size_products'];
				}
			?>
			<div class="wc-products <?php echo 'loops-wrapper ',apply_filters('themify_builder_module_loops_wrapper', $args['settings']['layout_products'],$args['settings'],'products'); ?>">

			<?php
            while( $query->have_posts() ) :
                $query->the_post();
			    $product = wc_get_product( $post->ID );
            ?>
				<div id="product-<?php the_ID(); ?>" <?php post_class( apply_filters( 'woocommerce_post_class', array('post','product','clearfix'), $product )); ?>>
					<?php $post_image = apply_filters('woocommerce_single_product_image_thumbnail_html',themify_get_image( $param_image ));
					if ( '' === $post_image ) {
						$post_image = wc_placeholder_img();
					}
					if('yes' === $args['settings']['hover_image']){
						$attachment_ids = $product->get_gallery_image_ids();
						if ( is_array( $attachment_ids ) && !empty($attachment_ids) ){
							$first_image_url = wp_get_attachment_url( $attachment_ids[0] );
							if(Themify_Builder_Model::is_img_php_disabled() && $args['settings']['image_size_products']!==''){
								$first_image_url = themify_do_img($first_image_url, $args['settings']['img_width_products'], $args['settings']['img_height_products']);
							}
                            $post_image.='<img src="'.$first_image_url.'" class="themify_product_second_image" />';
                        }
                    }
					do_action( 'woocommerce_before_shop_loop_item' );
					?>
					<?php if ( $args['settings']['hide_feat_img_products'] !== 'yes' ):?>
						<figure class="post-image">
							<?php if ( $args['settings']['unlink_feat_img_products']  === 'yes' ): ?>
									<?php echo $post_image; ?>
							<?php else: ?>
									<a href="<?php echo the_permalink(); ?>"><?php echo $post_image; ?></a>
							<?php endif; ?>
						</figure>
					<?php endif;?>

					<div class="post-content">

						<?php if($args['settings']['hide_sales_badge']!== 'yes' ) : ?>
							<?php woocommerce_show_product_loop_sale_flash(); ?>
						<?php endif; ?>

						<?php if( $args['settings']['show_product_categories'] === 'yes' ): ?>
							<?php
							$category_list = wc_get_product_category_list( $post->id );
							if (!empty($category_list))
								echo '<div class="product-category-link">'.__('Category' ,'themify') .': '. $category_list .'</div>';
							?>

						<?php endif;?>
						<?php
                        if( $args['settings']['show_product_tags'] === 'yes' ): ?>
							<?php
							$tag_list = get_the_term_list( get_the_id(), 'product_tag','',', ' );
							if(!empty($tag_list))
								echo '<div class="product-tag-link">'.__('Tag' ,'themify') .': '. $tag_list.'</div>'; ?>
						<?php endif;?>


                        <?php if ($args['settings']['hide_post_title_products']  !== 'yes' ) : ?>
							<?php if ($args['settings']['unlink_post_title_products']  === 'yes' ) : ?>
								<h3><?php the_title(); ?></h3>
							<?php else: ?>
								<h3><a href="<?php echo the_permalink(); ?>" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
							<?php endif; //unlink product title ?>
						<?php endif; //product title ?>    

						<?php
						if( $args['settings']['hide_rating_products'] !== 'yes' ) {
							woocommerce_template_loop_rating();
						} // product rating

						if( $args['settings']['hide_price_products'] !== 'yes' ) {
							woocommerce_template_loop_price();
						} // product price

						
						if( $args['settings']['description_products'] === 'short' ) {
								woocommerce_template_single_excerpt();
						} elseif('none' !== $args['settings']['description_products'] ) {
								the_content();
						}
						 // product description

						if( $args['settings']['hide_add_to_cart_products'] !== 'yes' ) {
							echo '<p class="add-to-cart-button">';
							woocommerce_template_loop_add_to_cart();
							echo '</p>';
						} // product add to cart
						?>

						<?php edit_post_link(__('Edit', 'themify'), '<span class="edit-button">[', ']</span>'); ?>

					</div><!-- /.post-content -->
					
				</div><!-- product-<?php the_ID(); ?> -->

			<?php endwhile; wp_reset_postdata(); $post = $builder_wc_temp_post; unset( $builder_wc_temp_post ); ?>

			</div>

		<?php endif; ?>

		<?php if( 'no' === $args['settings']['hide_page_nav_products']  ) {
			echo self::get_pagenav( '', '', $query, $args['settings']['offset_products'] );
		} ?>

		<?php do_action( 'themify_builder_after_template_content_render' );	?>
	</div><!-- .woocommerce -->
</div>
<!-- /module products -->
