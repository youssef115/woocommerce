<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Post
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$mod_name = $args['mod_name'];
$fields_default = array(
    'mod_title_' . $mod_name => '',
    'layout_' . $mod_name => 'grid4',
    'post_type_' . $mod_name => $mod_name,
	'term_type' => 'all', // Query By option
    'type_query_' . $mod_name => 'category',
    'category_' . $mod_name => '',
    'query_slug_' . $mod_name => '',
    'sticky_' . $mod_name => 'no',
    'post_per_page_' . $mod_name => '',
    'offset_' . $mod_name => '',
    'order_' . $mod_name => 'desc',
    'orderby_' . $mod_name => 'date',
    'meta_key_' . $mod_name => '',
    'display_' . $mod_name => 'content',
    'hide_feat_img_' . $mod_name => 'no',
    'image_size_' . $mod_name => '',
    'img_width_' . $mod_name => '',
    'img_height_' . $mod_name => '',
    'unlink_feat_img_' . $mod_name => 'no',
    'hide_post_title_' . $mod_name => 'no',
    'unlink_post_title_' . $mod_name => 'no',
    'hide_post_date_' . $mod_name => 'no',
    'hide_post_meta_' . $mod_name => 'no',
    'hide_page_nav_' . $mod_name => 'yes',
    'animation_effect' => '',
    'css_' . $mod_name => '',
    'auto_fullwidth_'. $mod_name => false
);
if (isset($args['mod_settings']['category_' . $mod_name])) {
    $args['mod_settings']['category_' . $mod_name] = self::get_param_value($args['mod_settings']['category_' . $mod_name]);
}
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
unset($args['mod_settings']);
$builder_id = $args['builder_id'];
$element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
if ($fields_args['layout_' . $mod_name]==='') {
    $fields_args['layout_' . $mod_name] = $fields_default['layout_' . $mod_name];
}
$animation_effect = self::parse_animation_effect($fields_args['animation_effect'], $fields_args);

$container_class = apply_filters('themify_builder_module_classes', array(
    'module', 'module-' . $mod_name, $element_id, $fields_args['css_' . $mod_name]
                ), $mod_name, $element_id, $fields_args);

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
if(!empty($fields_args['auto_fullwidth_' . $mod_name]) && $fields_args['auto_fullwidth_' . $mod_name] ){
    $container_class[] = 'tb_fullwidth_image';
}

$container_props = apply_filters('themify_builder_module_container_props', array(
    'class' => implode(' ', $container_class),
), $fields_args, $mod_name, $element_id);

self::add_post_class($animation_effect);
global $paged, $wp;
$paged = self::get_paged_query();
?>
<?php
    add_filter('themify_after_post_title_parse_args', array('Themify_Builder_Component_Module','post_title_tag'));
    ?>
    <!-- module post -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
        <?php
	$container_props=$container_props=null;
	do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
        if ($fields_args['mod_title_' . $mod_name] !== '') {
            echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_' . $mod_name], $fields_args) . $fields_args['after_title'];
        }
        // The Query
        do_action('themify_builder_before_template_content_render');
        $order = $fields_args['order_' . $mod_name];
        $orderby = $fields_args['orderby_' . $mod_name];
        $meta_key = $fields_args['meta_key_' . $mod_name];
        $limit = $fields_args['post_per_page_' . $mod_name];
	if (empty($limit)) {
	    $limit = get_option('posts_per_page');
	}
        $mod_name_query = isset($fields_args['term_type']) && $fields_args['term_type']==='post_slug'?$fields_args['term_type']:$fields_args['type_query_' . $mod_name] ;
        $offset = $fields_args['offset_' . $mod_name];
        $args = array(
			'post_status' => 'publish',
			'posts_per_page' => $limit,
			'ptb_disable'=>true,
			'order' => $order,
			'orderby' => $orderby,
			'suppress_filters' => false,
			'cache_results' => false,
			'paged' => $paged,
			'post_type' => $fields_args['post_type_' . $mod_name],
			'ignore_sticky_posts' => true
        );

		if ( 'all' === $fields_args['term_type'] ) {
			if ( $fields_args["sticky_{$mod_name}"] === 'yes' ) {
				$args['ignore_sticky_posts'] = false;
			}
		} else if ( 'post_slug' ===  $fields_args['term_type'] ) {
			if ( ! empty( $fields_args['query_slug_' . $mod_name] ) ) {
				$args['post__in'] = Themify_Builder_Model::parse_slug_to_ids($fields_args['query_slug_' . $mod_name], $args['post_type']);
			}
        } else {
            $terms = $mod_name === 'post' && isset($fields_args["{$mod_name_query}_post"]) ? $fields_args["{$mod_name_query}_post"] : $fields_args['category_' . $mod_name];
            $query_taxonomy = $mod_name !== 'post' ? $mod_name . '-category' : $mod_name_query;
			Themify_Builder_Model::parseTermsQuery( $args, $terms, $query_taxonomy );
            $mod_name_query = $query_taxonomy;
        }
        if( ! empty( $meta_key ) && ($orderby==='meta_value' || $orderby==='meta_value_num')) {
                $args[ 'meta_key' ] = $meta_key;
        }
        // add offset posts
        if ($offset !== '') {
            $args['offset'] = ( ( $paged - 1 ) * $limit ) + $offset;
        }
       
        $the_query = new WP_Query();
        $args = apply_filters("themify_builder_module_{$mod_name}_query_args", $args, $fields_args);  
		$posts = $the_query->query($args);
		$post_type_class = ! empty( $fields_args['post_type_' . $mod_name] ) && $fields_args['post_type_' . $mod_name] !== 'post'
			? $fields_args['post_type_' . $mod_name] . ' ' : ''; 
		
	
        ?>
        <div class="builder-posts-wrap clearfix loops-wrapper <?php echo $post_type_class,' ',apply_filters('themify_builder_module_loops_wrapper', $fields_args['layout_' . $mod_name],$fields_args,$mod_name) ?>">
            <?php
		if(!empty($posts)){
		$isLoop=$ThemifyBuilder->in_the_loop===true;
		$is_img_disabled = Themify_Builder_Model::is_img_php_disabled();
		$is_themify_theme = ($mod_name === 'post' && themify_is_themify_theme()) || (Themify_Builder_Model::is_loop_template_exist('loop-' . $mod_name . '.php', 'includes'));
		// if the active theme is using Themify framework use theme template loop (includes/loop.php file)
		if ($is_themify_theme===true) {
		    // save a copy
		    global $themify;
		    $themify_save = clone $themify;

		    // override $themify object
		    $themify->hide_image = $fields_args['hide_feat_img_' . $mod_name];
		    $themify->unlink_image = $fields_args['unlink_feat_img_' . $mod_name];
		    $themify->hide_title = $fields_args['hide_post_title_' . $mod_name];
		    $themify->width = $fields_args['img_width_' . $mod_name];
		    $themify->height = $fields_args['img_height_' . $mod_name];
		    $themify->image_setting = 'ignore=true&';
		    $themify->is_builder_loop = true;
		    if ($is_img_disabled===true && $fields_args['image_size_' . $mod_name] !== '') {
			$themify->image_setting .='image_size=' . $fields_args['image_size_' . $mod_name] . '&';
		    }
		    $themify->unlink_title = $fields_args['unlink_post_title_' . $mod_name];
		    $themify->display_content = $fields_args['display_' . $mod_name];
		    $themify->hide_date = $fields_args['hide_post_date_' . $mod_name];
		    $themify->hide_meta = $fields_args['hide_post_meta_' . $mod_name];
		    $themify->post_layout = $fields_args['layout_' . $mod_name];
		    if('post' === $mod_name){
			$themify->post_module_hook = $mod_name;
		    }

		    // hooks action
		    do_action_ref_array('themify_builder_override_loop_themify_vars', array($themify, $mod_name, $fields_args));
		    $ThemifyBuilder->in_the_loop = true;
		    $out = '';
		    if ($posts) {
			$out = $mod_name === 'post' ? themify_get_shortcode_template($posts) : themify_get_shortcode_template($posts, 'includes/loop', $mod_name);
		    }
		    // revert to original $themify state
		    $themify = clone $themify_save;
		    echo $out;
		} else{
		    // use builder template
		    global $post;
		    $temp_post = $post;
		    $param_image = 'w=' . $fields_args['img_width_' . $mod_name] . '&h=' . $fields_args['img_height_' . $mod_name] . '&ignore=true';
		    if ($is_img_disabled===true && $fields_args['image_size_' . $mod_name] !== '') {
			$param_image .='&image_size=' . $fields_args['image_size_' . $mod_name];
		    }
		    $cl = 'post clearfix';
		    if ($mod_name !== 'post') {
			$cl.=' ' . $mod_name . '-post';
		    }
		    $is_comment_open = themify_builder_get('setting-comments_posts');
		    $ThemifyBuilder->in_the_loop = true;
		    foreach ($posts as $post): setup_postdata($post);
			?>

			<?php themify_post_before(); // hook   ?>

			<article id="post-<?php the_ID(); ?>" <?php post_class($cl); ?>>

			    <?php themify_post_start(); // hook   ?>

			    <?php
			    if ($fields_args['hide_feat_img_' . $mod_name] !== 'yes') {

				//check if there is a video url in the custom field
				if ($vurl = themify_builder_get('video_url')) {
				    global $wp_embed;

				    themify_before_post_image(); // Hook

				    echo $wp_embed->run_shortcode('[embed]' . esc_url($vurl) . '[/embed]');

				    themify_after_post_image(); // Hook
				} elseif ($post_image = themify_get_image($param_image)) {

				    themify_before_post_image(); // Hook 
				    ?>

				    <figure class="post-image">
					<?php if ($fields_args['unlink_feat_img_' . $mod_name] === 'yes'): ?>
					    <?php echo $post_image; ?>
					<?php else: ?>
					    <a href="<?php echo themify_get_featured_image_link(); ?>"><?php echo $post_image; ?></a>
					<?php endif; ?>
				    </figure>

				    <?php
				    themify_after_post_image(); // Hook
				}
			    }
			    ?>

			    <div class="post-content">

				<?php if ($fields_args['hide_post_date_' . $mod_name] !== 'yes'): ?>
				    <time datetime="<?php the_time('o-m-d') ?>" class="post-date" pubdate><?php echo get_the_date(apply_filters('themify_loop_date', '')) ?></time>
				<?php endif; //post date   ?>

				<?php if ($fields_args['hide_post_title_' . $mod_name] !== 'yes'): ?>
				    <?php themify_before_post_title(); // Hook ?>
				    <?php if ($fields_args['unlink_post_title_' . $mod_name] === 'yes'): ?>
					<h2 class="post-title"><?php the_title(); ?></h2>
				    <?php else: ?>
					<h2 class="post-title"><a href="<?php echo themify_get_featured_image_link(); ?>"><?php the_title(); ?></a></h2>
				    <?php endif; //unlink post title    ?>
				    <?php themify_after_post_title(); // Hook ?> 
				<?php endif; //post title  ?>    

				<?php if ($fields_args['hide_post_meta_' . $mod_name] !== 'yes'): ?>
				    <p class="post-meta"> 
					<span class="post-author"><?php the_author_posts_link() ?></span>
					<span class="post-category">
									    <?php if ( $mod_name === 'post' ) :
											    echo get_the_category_list(', ', '', $post->ID);
										    else :
										    $terms = wp_get_post_terms($post->ID, $mod_name . '-category', array( "fields" => "names" ));
										    if( !is_wp_error($terms) && !empty($terms) ) {
											    echo implode(', ', $terms);
										    }
										    endif;
									    ?>
									    </span>
					<?php the_tags(' <span class="post-tag">', ', ', '</span>'); ?>
					<?php if (!$is_comment_open && comments_open()) : ?>
					    <span class="post-comment"><?php comments_popup_link(__('0 Comments', 'themify'), __('1 Comment', 'themify'), __('% Comments', 'themify')); ?></span>
					<?php endif; //post comment    ?>
				    </p>
				<?php endif; //post meta   ?>    

				<?php
				// fix the issue more link doesn't output
				global $more;
				$more = 0;
				if ($fields_args['display_' . $mod_name] === 'excerpt') {
				    the_excerpt();
				} elseif ($fields_args['display_' . $mod_name] !== 'none') {
				    the_content(themify_builder_get('setting-default_more_text') ? themify_builder_get('setting-default_more_text') : __('More &rarr;', 'themify'));
				}
				edit_post_link(__('Edit', 'themify'), '<span class="edit-button">[', ']</span>');
				?>
				<?php if ($mod_name === 'testimonial'): ?>
				    <p class="testimonial-author">
					<?php
					echo themify_builder_testimonial_author_name($post, 'yes');
					?>
				    </p>
				<?php endif; ?>

			    </div>
			    <!-- /.post-content -->
			    <?php themify_post_end(); // hook    ?>

			</article>
			<?php themify_post_after(); // hook     ?>

			<?php
		    endforeach;


		    wp_reset_postdata();
		    $post = $temp_post;
		} // end $is_theme_template
		$ThemifyBuilder->in_the_loop = $isLoop;
	    }
            elseif (current_user_can('publish_posts')) {
                printf(__('<a href="%s" target="_blank">Click here</a> to add post and assign it in %s', 'themify')
                        , $args['post_type'] !== 'post' ? admin_url('post-new.php?post_type=' . $args['post_type']) : admin_url('post-new.php')
                        , $fields_args['category_' . $mod_name] !== '' ? $fields_args['category_' . $mod_name] : $mod_name_query );
            }
            ?>
        </div><!-- .builder-posts-wrap -->
        <?php if ('yes' !== $fields_args['hide_page_nav_' . $mod_name]): ?>
            <?php echo self::get_pagenav('', '', $the_query, $offset) ?>
        <?php endif; ?>
        <?php
         do_action('themify_builder_after_template_content_render');
        self::remove_post_class($animation_effect);
        ?>
    </div>
    <!-- /module post -->
<?php remove_filter('themify_after_post_title_parse_args', array('Themify_Builder_Component_Module','post_title_tag')); ?>

