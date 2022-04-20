<?php
/**
 * Template for cart
 * @package themify
 * @since 1.0.0
 */
?>

<div id="shopdock">
	<?php
	// check whether cart is not empty
	if (sizeof(WC()->cart->get_cart())>0):
		?>
		<div id="cart-wrap">
			<div id="cart-list">
				<?php get_template_part('includes/loop-product', 'cart'); ?>
			</div>
			<!-- /cart-list -->

			<div class="cart-total-checkout-wrap">
				<p class="cart-total">
					<span class="amount"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
					<a id="view-cart" href="<?php echo esc_url( wc_get_cart_url() ) ?>">
						<?php _e('view cart', 'themify') ?>
					</a>
				</p>

				<?php themify_checkout_start(); //hook ?>

				<p class="checkout-button">
					<button type="submit" class="button checkout white flat" onClick="document.location.href = '<?php echo esc_url( wc_get_checkout_url() ); ?>';
								return false;"><?php _e('Checkout', 'themify') ?></button>
				</p>
				<!-- /checkout-botton -->

				<?php themify_checkout_end(); //hook ?>
			</div>

		</div>
		<!-- /#cart-wrap -->
	<?php elseif(themify_get_cart_style()!=='link_to_cart'):?>
	<?php
		echo '<span class="empty-shopdock">';
		printf( themify_kses_link( __( 'Your cart is empty. Go to <a href="%s">Shop</a>.', 'themify' ) ), esc_url( themify_get_shop_permalink() ) );
		echo '</span>';
	?>
	<?php endif; // cart whether is not empty?>

	<?php themify_shopdock_end(); //hook ?>
</div>