<?php 
WC_Frontend_Scripts::load_scripts();
global $themify,$wp_query;
$query = !empty($themify->query_products )?new WP_Query( $themify->query_products ):$wp_query;
?>
<?php if ( $query->have_posts() ) : ?>
		<?php
		$themify->query = $query;
		do_action('woocommerce_before_shop_loop'); 
		if($query->found_posts>1 && themify_get( 'product_show_sorting_bar' ) === 'yes' ){
			get_template_part( 'loop/order');
		}
		woocommerce_product_loop_start(); 
		woocommerce_product_subcategories();
		?>

		<?php while ( $query->have_posts() ) : $query->the_post(); ?>

			<?php wc_get_template_part( 'content', 'product' ); ?>

		<?php endwhile; // end of the loop. ?>
		
		<?php
		woocommerce_product_loop_end();
		do_action('woocommerce_after_shop_loop');
		if ($themify->page_navigation !== 'yes' && $query->found_posts>1){
			global $total_pages;
			$total_pages=$query->max_num_pages;
			get_template_part( 'includes/pagination');
		}
	?>
<?php else: ?>

	<?php wc_get_template( 'loop/no-products-found.php' ); ?>

<?php endif;
if(!empty($themify->query_products )){
    wp_reset_postdata();
}
