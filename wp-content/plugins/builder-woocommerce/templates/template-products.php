<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Products
 * 
 * Access original fields: $args['mod_settings']
 */
global $paged;
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):
$fields_default = array(
	'mod_title_products' => '',
	'query_products' => 'all',
	'template_products' => 'list',
	'hide_free_products' => 'no',
	'hide_outofstock_products' => 'no',
	'layout_products' => 'grid3',
	'query_type' => 'category',
	'category_products' => '',
	'tag_products' => '',
	'hide_child_products'=>false,
	'post_per_page_products' => 6,
	'offset_products' => 0,
	'order_products' => 'ASC',
	'orderby_products' => 'title',
	'description_products' => 'none',
	'hide_feat_img_products' => 'no',
	'hover_image' => false,
	'image_size_products' => '',
	'img_width_products' => '',
	'img_height_products' => '',
	'unlink_feat_img_products' => 'no',
	'hide_post_title_products' => 'no',
	'show_product_categories' => 'no',
	'show_product_tags' => 'no',
	'unlink_post_title_products' => 'no',
	'hide_price_products' => 'no',
	'hide_add_to_cart_products' => 'no',
	'hide_rating_products' => 'no',
	'show_empty_rating' => 'no',
	'hide_sales_badge' => 'no',
	// slider settings
	'layout_slider' => '',
	'visible_opt_slider' => '',
	'mob_visible_opt_slider' => '',
	'auto_scroll_opt_slider' => 0,
	'scroll_opt_slider' => '',
	'speed_opt_slider' => '',
	'effect_slider' => 'scroll',
	'pause_on_hover_slider' => 'resume',
	'play_pause_control' => 'no',
	'pagination'=>'yes',
	'wrap_slider' => 'yes',
	'show_nav_slider' => 'yes',
	'show_arrow_slider' => 'yes',
	'show_arrow_buttons_vertical' => '',
	'left_margin_slider' => '',
	'right_margin_slider' => '',
	'height_slider' => 'variable',
	'hide_page_nav_products' => 'yes',
	'animation_effect' => '',
	'css_products' => '',
);
$fields_args = wp_parse_args( $args['mod_settings'], $fields_default );
$terms_id = $fields_args['query_type'] . '_products';
if ( isset( $args['mod_settings'][ $terms_id ] ) ) {
	$fields_args[ $terms_id ] = self::get_param_value( $args['mod_settings'][ $terms_id ] );
}
unset($args['mod_settings']);
$temp_terms = explode( ',', $fields_args[$terms_id] );
$terms = array();
$terms_exclude = array();
$is_string = false;
foreach ( $temp_terms as $t ) {
	$is_string = ! is_numeric( $t );
	$t = trim( $t );

	if ( '' !== $t ) {
		if( ! $is_string && $t < 0 ) {
			$terms_exclude[] = abs( $t );
		} else {
			$terms[] = $t;
		}
	}
}
$tax_field = $is_string ? 'slug' : 'id';

$query_args = array(
	'post_type' => 'product',
	'posts_per_page' => $fields_args['post_per_page_products'],
	'order' => $fields_args['order_products'],
);
$paged = self::get_paged_query();
$query_args['offset'] = ( ( $paged - 1 ) * $fields_args['post_per_page_products'] ) + $fields_args['offset_products'];

$query_args['meta_query'][] = WC()->query->stock_status_meta_query();
$query_args['meta_query']   = array_filter( $query_args['meta_query'] );
$taxonomy = 'tag' === $fields_args['query_type'] ? 'product_tag' : 'product_cat';
if( ! empty( $terms_exclude ) ) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => $taxonomy,
			'field' => $tax_field,
			'terms' => $terms_exclude,
			'include_children' => $fields_args['hide_child_products'] !=='yes',
			'operator' => 'NOT IN'
		)
	);

} else if( ! empty( $terms ) && ! in_array( '0', $terms ) ) {
	$query_args['tax_query'] = array(
		array(
			'taxonomy' => $taxonomy,
			'field' => $tax_field,
			'terms' => $terms,
			'include_children'=> $fields_args['hide_child_products'] !=='yes'
		)
	);
}

if( $fields_args['query_products'] === 'onsale' ) {
	$product_ids_on_sale = wc_get_product_ids_on_sale();
	$product_ids_on_sale[] = 0;
	$query_args['post__in'] = $product_ids_on_sale;
} elseif( $fields_args['query_products'] === 'featured' ) {
	if( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '>=' ) ) {
		$query_args['tax_query'][] = array(
			'taxonomy'	=> 'product_visibility',
			'field'		=> 'name',
			'terms'		=> 'featured',
			'operator'	=> 'IN'
		);
	} else {
		$query_args['meta_query'][] = array(
			'key'	=> '_featured',
			'value' => 'yes'
		);
	}
} elseif ('toprated' === $fields_args['query_products']){
	$query_args['orderby']['top_rated']  = 'DESC';
	$query_args['meta_query']['top_rated'] = array(
		'key'	=> '_wc_average_rating',
		'type'    => 'NUMERIC'
	);
}

switch ( $fields_args['orderby_products'] ) {
	case 'price' :
		$query_args['meta_query'][$fields_args['orderby_products']] = array(
			'key'	=> '_price',
			'type'    => 'NUMERIC',
		);
		break;
	case 'sales' :
		$query_args['meta_query'][$fields_args['orderby_products']] = array(
			'key'	=> 'total_sales',
			'type'    => 'NUMERIC',
		);
		break;
	case 'sku' :
		$query_args['meta_query'][$fields_args['orderby_products']] = array(
			'key'	=> '_sku'
		);
		break;
}
$query_args['orderby'][$fields_args['orderby_products']]  = $fields_args['order_products'];
if ( $fields_args['hide_free_products'] === 'yes' ) {
	$query_args['meta_query'][] = array(
		'key'     => '_price',
		'value'   => 0,
		'compare' => '>',
		'type'    => 'DECIMAL',
	);
}
if( $fields_args['hide_outofstock_products'] === 'yes' ) {
 	if( version_compare( WOOCOMMERCE_VERSION, '3.0.0', '>=' ) ) {
 		$query_args['tax_query'][] = array(
			'taxonomy'	=> 'product_visibility',
			'field'		=> 'name',
			'terms'		=> array( 'exclude-from-catalog', 'outofstock' ),
			'operator'	=> 'NOT IN'
		);
 	} else {
 		$query_args['meta_query'][] = array(
 			'key'     => '_stock_status',
 			'value'   => 'outofstock',
 			'compare' => 'NOT IN'
 		);
 	}
 }
$is_theme_template = false;
if( $fields_args['template_products'] === 'list' && Themify_Builder_Model::is_loop_template_exist( 'query-product.php', 'includes' ) ) {
	$theme_layouts = apply_filters( 'builder_woocommerce_theme_layouts', array() );
        // check if the chosen layout is supported by the theme
        $is_theme_template = in_array( $fields_args['layout_products'], $theme_layouts,true );
}
	if( 'yes' ===$fields_args['hide_rating_products']  ) {
		add_filter( 'option_woocommerce_enable_review_rating', 'builder_woocommerce_return_no' );
	} else {
		// enable ratings despite the option configured in WooCommerce > Settings
		add_filter( 'option_woocommerce_enable_review_rating', 'builder_woocommerce_return_yes' );
		if( 'show' === $fields_args['show_empty_rating'] ) {
			// Always show rating even for 0 rating
			add_filter( 'woocommerce_product_get_rating_html', array('Builder_Woocommerce','product_get_rating_html'), 10, 3 );
		}
	}
if( $is_theme_template ) {
	global $themify;
	$themify_save = clone $themify;

	// $themify->page_navigation = $hide_page_nav_products;
	$themify->page_navigation = $fields_args['hide_page_nav_products']; // hide navigation links
	$themify->query_products = $query_args;
	$themify->post_layout = $fields_args['layout_products'];
	$themify->product_archive_show_short = $fields_args['description_products'];
	$themify->product_archive_show_rating = 'yes' === $fields_args['hide_rating_products'] ? false : true;
	$themify->unlink_product_title = $fields_args['unlink_post_title_products'];
	$themify->hide_product_title = $fields_args['hide_post_title_products'];
	$themify->show_product_categories = $fields_args['show_product_categories'];
	$themify->show_product_tags = $fields_args['show_product_tags'];
	$themify->hide_product_image = $fields_args['hide_feat_img_products'];
	$themify->unlink_product_image = $fields_args['unlink_feat_img_products'];
	$themify->width = $fields_args['img_width_products'];
	$themify->height = $fields_args['img_height_products'];
	$themify->load_from_products_module = true; // flag, used by Shoppe theme
	$themify->products_hover_image = 'yes' === $fields_args['hover_image'];
	if (Themify_Builder_Model::is_img_php_disabled() && $fields_args['image_size_products'] !== ''){
            $themify->image_setting .= 'image_size=' . $fields_args['image_size_products'] . '&';
	}
	if( 'yes' === $fields_args['hide_add_to_cart_products'] ) {
		add_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string' );
	}
	if( 'yes' === $fields_args['hide_sales_badge'] ) {
		add_filter( 'woocommerce_sale_flash', '__return_empty_string' );
	}
	if( 'yes' ===$fields_args['hide_price_products']  ) {
		add_filter( 'woocommerce_get_price_html', '__return_empty_string' );
	}
	$animation_effect = self::parse_animation_effect( $fields_args['animation_effect'], $fields_args );
	$container_class = apply_filters( 'themify_builder_module_classes', array(
			'module', 'module-' . $args['mod_name'], $args['module_ID'], $fields_args['css_products']
		), $args['mod_name'], $args['module_ID'], $fields_args );
	if(!empty($args['element_id'])){
	    $container_class[] = 'tb_'.$args['element_id'];
	}
	if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	    $container_class[] = $fields_args['global_styles'];
	}
	$container_props = apply_filters( 'themify_builder_module_container_props', array(
		'id' => $args['module_ID'],
		'class' =>implode(' ',  $container_class),
	), $fields_args, $args['mod_name'], $args['module_ID'] );

	if($animation_effect!==''){
            self::add_post_class( $animation_effect );
        }
	?>
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
		<?php $container_props=$container_class=null;?>
		<?php if ( $fields_args['mod_title_products'] !== '' ): ?>
			<?php echo $fields_args['before_title'] . apply_filters( 'themify_builder_module_title', $fields_args['mod_title_products'], $fields_args )  . $fields_args['after_title']; ?>
		<?php endif; ?>

		<?php do_action( 'themify_builder_before_template_content_render' ); ?>

		<?php get_template_part( 'includes/query-product' ); ?>
		
		<?php do_action( 'themify_builder_after_template_content_render' ); ?>
	</div>
	<?php
	// reset config
	$themify = clone $themify_save;

	remove_filter( 'woocommerce_loop_add_to_cart_link', '__return_empty_string' );
	remove_filter( 'woocommerce_sale_flash', '__return_empty_string' );
	remove_filter( 'woocommerce_get_price_html', '__return_empty_string' );
	$themify_save=null;

} else {
    // render the template
	self::retrieve_template( 'template-'.$args['mod_name'].'-'.$fields_args['template_products'].'.php', array(
		'module_ID' => $args['module_ID'],
		'mod_name' => $args['mod_name'],
		'query_args' => $query_args,
		'settings' => $fields_args,
		'element_id'=>isset($args['element_id'])?$args['element_id']:null
	), '', '', true );

}
	remove_filter( 'option_woocommerce_enable_review_rating', 'builder_woocommerce_return_no' );
	remove_filter( 'option_woocommerce_enable_review_rating', 'builder_woocommerce_return_yes' );
	remove_filter( 'woocommerce_product_get_rating_html', array('Builder_Woocommerce','product_get_rating_html') );


 endif; ?>
<?php TFCache::end_cache(); ?>
