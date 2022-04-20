<?php
/**
 * Shopdock Templates Functions
 *
 * Override woocommerce templates
 *
 * @author    Themify
 * @category  Core
 * @package   Shopdock
 */
/**
 * Shopdock dock bar
 **/
if ( ! function_exists( 'shopdock_dock_bar' ) ) {
	function shopdock_dock_bar() {
		global $woocommerce;
		$placeholder_width = 65;
		$placeholder_height = 65;
		$qty = 0; 
	?>
	<div id="addon-shopdock" class="shopdock_cart" <?php if ( sizeof( $woocommerce->cart->get_cart() ) == 0 ) : ?>style="display:none;"<?php endif; ?>>
	<div class="shopdock-inner pagewidth">
		<?php if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) : ?>
			<div id="cart-slider">
				<ul class="cart-slides">
					<?php
						$carts = array_reverse( $woocommerce->cart->get_cart() );

						foreach ( $carts as $cart_item_key => $cart_item ) :
							// Add support for MNM plugin
							if( isset( $cart_item['mnm_container'] ) ) continue;

							$_product = $cart_item['data'];

							if ( $_product->exists() && $cart_item['quantity'] > 0 ) :
								// store qty
								$qty += $cart_item['quantity'];
								$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					?>
					<li>
						<div class="product">
							<div class="product-imagewrap">
								<?php if ( $cart_item['quantity'] > 1 ): ?>
									<div class="quantity-tip"><?php echo $cart_item['quantity']; ?></div>
								<?php endif; ?>
								<a href="<?php echo esc_url( version_compare( WOOCOMMERCE_VERSION, '3.3.0', '>=' )
									? wc_get_cart_remove_url( $cart_item_key ) : $woocommerce->cart->get_remove_url( $cart_item_key ) ); ?>" class="shopdock-remove-product"><?php _e( 'Remove', 'wc_shopdock' ); ?></a>
								<a href="<?php echo get_permalink( $cart_item['product_id'] ); ?>" title="<?php echo get_the_title( $cart_item['product_id'] ); ?>">
									<?php 
										if ( has_post_thumbnail( $cart_item['product_id'] ) ) {
											$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
											echo $product_permalink ? sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ) : $thumbnail;
										} else {
									?>
										<img src="<?php echo woocommerce_placeholder_img_src(); ?>" alt="Placeholder" width="<?php echo $placeholder_width; ?>" height="<?php echo $placeholder_height; ?>" />
									<?php } ?>
								</a>
							</div>
						</div>
					</li>
					<?php endif; endforeach; ?>
				</ul>
			</div>
		<?php endif; do_action( 'woocommerce_cart_contents' ); ?>

		<div class="checkout-wrap clearfix">
			<p class="checkout-button">
				<button type="submit" class="button checkout" onclick="location.href='<?php echo esc_url( wc_get_checkout_url() ); ?>'"><?php _e( 'Checkout', 'wc_shopdock' ); ?></button>
			</p>
			<p class="cart-total">
				<span id="cart-loader" class="hide"></span>
				<span class="total-item"><?php echo sprintf( __( '%d items', 'wc_shopdock' ), $qty ); ?></span>
				<?php
					if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
						echo '(' . $woocommerce->cart->get_cart_total() . ')';
					}
				?>
			</p>
		</div>
	  <!-- /.cart-checkout -->
	</div>
	<!-- /.pagewidth -->
  </div>
  <!-- /#shopdock -->
  <?php
	} // end shopdock_bar
}
/**
 * Hook shopdock template loop product thumbnail
 **/
if (!function_exists('shopdock_template_loop_product_thumbnail')) {
	function shopdock_template_loop_product_thumbnail() {
	    // avoid conflict with themify themes (double image rendering)
		$already_has_image = has_action( 'woocommerce_before_shop_loop_item_title', 'themify_theme_product_image' );
		if( !$already_has_image ) echo shopdock_get_product_thumbnail();
	}
}
/**
 * Shopdock Product Thumbnail function
 * addition: wrapping the image
 **/
if (!function_exists('shopdock_get_product_thumbnail')) {
	function shopdock_get_product_thumbnail( $size = 'shop_catalog', $placeholder_width = 0, $placeholder_height = 0 ) {
		global $post, $woocommerce;
		if ( ! $placeholder_width ) {
			if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
				$placeholder_width = wc_get_image_size( $size );
				$placeholder_width = $placeholder_width['width'];
			} else {
				$placeholder_width = $woocommerce->get_image_size( $size );
			}
		}
		if ( ! $placeholder_height ) {
			if ( version_compare( WOOCOMMERCE_VERSION, "2.1" ) >= 0 ) {
				$placeholder_height = wc_get_image_size( $size );
				$placeholder_height = $placeholder_height['height'];
			} else {
				$placeholder_height = $woocommerce->get_image_size( $size );
			}
		}
		$html = '<div class="product-image">';
		$html .= '<a href="'.get_permalink().'">';
		if ( has_post_thumbnail() ) {
			$html .= get_the_post_thumbnail($post->ID, $size);
		}
		else {
			$html .= '<img src="'. woocommerce_placeholder_img_src().'" alt="Placeholder" width="' . $placeholder_width . '" height="' . $placeholder_height . '" />';
		}
		$html .= '</a>';
		$html .= '</div>';
		return $html;
	}
}
/** Loop ******************************************************************/
/**
 * Products Loop add to cart
 **/
if ( ! function_exists( 'shopdock_template_loop_add_to_cart' ) ) {
	function shopdock_template_loop_add_to_cart() {
		woocommerce_get_template( 'loop/add-to-cart.php', '', 'woocommerce-shopdock', SHOPDOCK_DIR . '/templates/' );
	}
}

/**
 * Get the Shopdock element
 *
 * @since 1.1.4
 */
function shopdock_wp_ajax_dock_bar() {
	shopdock_dock_bar();
	die;
}
