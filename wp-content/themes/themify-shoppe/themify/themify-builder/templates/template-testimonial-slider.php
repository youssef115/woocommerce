<?php

if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Testimonial
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
$fields_default = array(
    'mod_title_testimonial' => '',
    'layout_testimonial' => 'image-top',
    'tab_content_testimonial' => '',
    'css_testimonial' => ''
);
$fields_args = wp_parse_args($args['mod_settings'], $fields_default);
$fields_args['css_slider'] = $fields_args['css_testimonial'];
$fields_args['mod_title_slider'] = $fields_args['mod_title_testimonial'];
$fields_args['layout_slider'] = $fields_args['layout_testimonial']!==''?$fields_args['layout_testimonial']:$fields_default['layout_testimonial'];
$fields_args['layout_display_slider'] = 'content';
unset($args['mod_settings'], $fields_args['css_testimonial'], $fields_args['mod_title_testimonial'], $fields_args['layout_testimonial']);
if ( ! isset($fields_args['type_testimonial']) || $fields_args['type_testimonial'] === 'slider' ) {
	self::retrieve_template('template-slider.php', array(
	    'module_ID' => $args['module_ID'],
	    'mod_name' => $args['mod_name'],
	    'element_id'=>!empty($args['element_id'])?$args['element_id']:null,
	    'builder_id'=>$args['builder_id'],
	    'mod_settings' => $fields_args
	), '', '', true);
} else {
	self::retrieve_template('template-testimonial-grid.php', array(
		'module_ID' => $args['module_ID'],
		'mod_name' => $args['mod_name'],
		'element_id'=>!empty($args['element_id'])?$args['element_id']:null,
		'builder_id'=>$args['builder_id'],
		'mod_settings' => $fields_args
	), '', '', true);
}
$args=$fields_args=null;