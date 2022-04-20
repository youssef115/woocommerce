<?php
if ( !defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly
/**
 * Template Social Share
 *
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if ( TFCache::start_cache( $args['mod_name'], self::$post_id, array( 'ID' => $args['module_ID'] ) ) ):

	$fields_default = array(
        'mod_title' => '',
		'mod_title_social_share' => '',
		'networks' => '',
		'style' => 'badge',
		'size' => 'normal',
		'shape' => 'none',
		'arrangement' => 'h',
		'title' => 'yes',
		'animation_effect' => '',
		'css' => ''
	);
	$fields_args = wp_parse_args( $args['mod_settings'], $fields_default );
	unset( $args['mod_settings'] );
	
	$mod_name=$args['mod_name'];
	$builder_id = $args['builder_id'];
	$element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
	
	if($fields_args['networks']!==''){
	    $fields_args['networks'] = explode( '|', $fields_args['networks'] );
	    if ( !empty( $fields_args['networks'] ) ) {
		    //$url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
		    $info = array(
			    'fb' => array( 'icon' => 'ti-facebook', 'title' => __( 'Facebook', 'themify' ),'type'=>'facebook' ),
			    'tw' => array( 'icon' => 'ti-twitter-alt', 'title' => __( 'Twitter', 'themify' ),'type'=>'twitter' ),
			    'lk' => array( 'icon' => 'ti-linkedin', 'title' => __( 'Linkedin', 'themify' ),'type'=>'linkedin' ),
			    'pi' => array( 'icon' => 'ti-pinterest', 'title' => __( 'Pinterest', 'themify' ),'type'=>'pinterest' ),
			    'em' => array( 'icon' => 'ti-email', 'title' => __( 'Email', 'themify' ),'type'=>'email' )
		    );
	    }
	}
	$container_class = apply_filters( 'themify_builder_module_classes', array(
		'module',
		'module-' . $mod_name,
		$element_id,
		$fields_args['css'],
		'tb_ss_style_' . $fields_args['style'],
		'tb_ss_arrangement_' . $fields_args['arrangement'],
		'tb_ss_size_' . $fields_args['size'],
		'tb_ss_shape_' . $fields_args['shape'],
		self::parse_animation_effect( $fields_args['animation_effect'], $fields_args )
	), $mod_name, $element_id, $fields_args );
	
	if ( !empty( $fields_args['global_styles'] ) && Themify_Builder::$frontedit_active === false ) {
		$container_class[] = $fields_args['global_styles'];
	}
	$container_props = apply_filters( 'themify_builder_module_container_props', array( 'class' => implode( ' ', $container_class ) ), $fields_args, $mod_name, $element_id );
	$loop = $ThemifyBuilder->in_the_loop===true || in_the_loop();
	?>
    <!-- module social share -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args));?> data-title="<?php echo true === $loop?the_title():wp_title(); ?>" data-url="<?php echo true===$loop?the_permalink():''; ?>">
		<?php $container_props = $container_class = null; 
		    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
		?>
        <?php if ($fields_args['mod_title'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title'], $fields_args). $fields_args['after_title']; ?>
        <?php endif; ?>
		<div class="module-social-share-wrapper">
		<?php if ( $fields_args['mod_title_social_share'] !== '' ): ?>
			<?php echo $fields_args['before_title'] . apply_filters( 'themify_builder_module_title', $fields_args['mod_title_social_share'], $fields_args ) . $fields_args['after_title']; ?>
		<?php endif; ?>
	<?php if($fields_args['networks']!==''):?>
		<?php foreach ( $fields_args['networks'] as $net ): ?>
			 <div class="ss_anchor_wrap">
            <a href="#" data-type="<?php echo $info[ $net ]['type']; ?>">
                <i class="tb_social_share_icon <?php echo $info[ $net ]['icon']; ?>"></i>
                <?php if('no' === $fields_args['title']): ?>
                <span class="tb_social_share_title"><?php echo $info[ $net ]['title']; ?></span>
                <?php endif; ?>
            </a>
			 </div>
	    <?php endforeach; ?>
	<?php endif;?>
		</div>
    </div>
    <!-- /module social share -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
