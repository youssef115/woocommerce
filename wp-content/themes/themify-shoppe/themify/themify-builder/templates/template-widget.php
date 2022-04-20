<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly
/**
 * Template Widget
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
	global $wp_widget_factory;

	$fields_default = array(
		'mod_title_widget' => '',
		'class_widget' => '',
		'instance_widget' => array(),
		'custom_css_widget' => '',
		'background_repeat' => '',
		'animation_effect' => ''
	);
	$fields_args = wp_parse_args( $args['mod_settings'], $fields_default );
	unset( $args['mod_settings'] );

$mod_name=$args['mod_name'];
$builder_id = $args['builder_id'];
$element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];

$container_class = apply_filters( 'themify_builder_module_classes', array(
		'module', 
    'module-' . $mod_name, 
    $element_id,
	    $fields_args['custom_css_widget'], 
	    $fields_args['background_repeat'], 
	    self::parse_animation_effect( $fields_args['animation_effect'], $fields_args )
), $mod_name, $element_id, $fields_args );

if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
    $container_class[] = $fields_args['global_styles'];
}
$container_props = apply_filters('themify_builder_module_container_props', array(
		'class' => implode( ' ', $container_class),
), $fields_args, $mod_name, $element_id);
$args=null;
?>
<!-- module widget -->
<div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php
	$container_props=$container_class=null;
	do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	$tmp = is_numeric($builder_id) && get_post_status ( $builder_id )?get_post( $builder_id, OBJECT ):'';// $builder_id can be generated string from AAP module
	if(!empty($tmp) || $tmp===''){
	    if($tmp!==''){
		global $post;
		$post=$tmp;
		setup_postdata( $post );
	    }
	    if ( $fields_args['mod_title_widget'] !== '' ) {
		echo $fields_args['before_title'] . apply_filters( 'themify_builder_module_title', $fields_args['mod_title_widget'], $fields_args ). $fields_args['after_title'];
	    }
	    do_action( 'themify_builder_before_template_content_render' );

	    if ( $fields_args['class_widget'] !== '' && class_exists( $fields_args['class_widget'] ) && isset( $wp_widget_factory->widgets[ $fields_args['class_widget'] ] ) ) {
		// Backward compatibility with how widget data used to be saved.
		$fields_args['instance_widget'] = TB_Widget_Module::sanitize_widget_instance( $fields_args['instance_widget'] );
		    //WC gets by widget_id not by widget-id,which is bug of WC
		the_widget( $fields_args['class_widget'], $fields_args['instance_widget'],array('widget_id'=>isset($fields_args['instance_widget']['widget-id'])?$fields_args['instance_widget']['widget-id']: $args['module_ID']) );

	    }
	    do_action('themify_builder_after_template_content_render');
	    if($tmp!==''){
		wp_reset_postdata();
	    }
	}
	?>
</div><!-- /module widget -->