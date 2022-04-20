<?php
/**
 * Partial template for pagination.
 * Creates numbered pagination or displays button for infinite scroll based on user selection
 * @since 1.0.0
 */
global $themify;

if ( 'infinite' === themify_get( 'setting-more_posts' ) || '' == themify_get( 'setting-more_posts' ) ) {
	global $wp_query, $themify,$total_pages;
	if(!isset($total_pages)){
		$total_pages=$wp_query->max_num_pages;
	}
	$current_page = get_query_var( 'paged' );
	if(empty($current_page)){
		$current_page=get_query_var( 'page' );
		if(empty($current_page)){
			$current_page=1;
		}
	}
	if ( $total_pages > $current_page ) {
		echo '<p id="load-more"><a data-total="'.$total_pages.'" data-current="'.$current_page.'" href="' . next_posts( $total_pages, false ) . '">' . __( 'Load More', 'themify' ) . '</a></p>';
	}
	$total_pages=null;
} else {
	if ( 'prevnext' === themify_get( 'setting-entries_nav' ) && 'post' === get_post_type() ) { ?>
		<div class="post-nav">
			<span class="prev"><?php next_posts_link( __( '&laquo; Older Entries', 'themify' ) ) ?></span>
			<span class="next"><?php previous_posts_link( __( 'Newer Entries &raquo;', 'themify' ) ) ?></span>
		</div>
	<?php 
	} else {
		themify_pagenav( '', '', $themify->query );
	}
} // infinite
?>