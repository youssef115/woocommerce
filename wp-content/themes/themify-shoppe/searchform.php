<?php
/**
 * Template for search form.
 * @package themify
 * @since 1.0.0
 */
?>
<form method="get" id="searchform" action="<?php echo home_url(); ?>/">

	<i class="icon-search"></i>

	<input type="text" name="s" id="s" title="<?php _e( 'Search', 'themify' ); ?>" placeholder="<?php _e( 'Search', 'themify' ); ?>" value="<?php echo get_search_query(); ?>" />

	<?php if(themify_is_woocommerce_active() && 'product' === themify_get( 'setting-search_post_type','all',true )): ?>
        <input type="hidden" name="post_type" value="product" />
	<?php endif; ?>

</form>
