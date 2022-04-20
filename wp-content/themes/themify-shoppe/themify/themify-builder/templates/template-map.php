<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Template Map
 * 
 * Access original fields: $args['mod_settings']
 * @author Themify
 */
if (TFCache::start_cache($args['mod_name'], self::$post_id, array('ID' => $args['module_ID']))):

    $fields_default = array(
        'mod_title_map' => '',
        'address_map' => '',
        'latlong_map' => '',
        'zoom_map' => 8,
        'w_map' => '100',
        'w_map_static' => 500,
        'w_map_unit' => '%',
        'h_map' => '300',
        'h_map_unit' => 'px',
        'b_style_map' => 'solid',
        'b_width_map' => '',
        'b_color_map' => '',
        'type_map' => 'ROADMAP',
        'bing_type_map'=>'aerial',
        'scrollwheel_map' => 'disable',
        'draggable_map' => 'enable',
        'map_control' => 'no',
        'draggable_disable_mobile_map' => 'yes',
        'info_window_map' => '',
        'map_provider' => 'google',
        'map_display_type' => 'dynamic',
        'css_map' => '',
        'animation_effect' => ''
    );

    if (!empty($args['mod_settings']['address_map'])) {
        $args['mod_settings']['address_map'] = preg_replace('/\s+/', ' ', trim($args['mod_settings']['address_map']));
    }
    $fields_args = wp_parse_args($args['mod_settings'], $fields_default);
    if(isset($fields_args['unit_w']) && !isset($args['mod_settings']['w_map_unit']) && $fields_args['unit_w']==-1){
	$fields_args['w_map_unit'] ='px';
    }
    if(isset($fields_args['unit_h']) && !isset($args['mod_settings']['w_map_unit']) && $fields_args['unit_h']==-1){
	$fields_args['h_map_unit'] ='px';
    }
    unset($args['mod_settings']);
    
    $mod_name=$args['mod_name'];
    $builder_id = $args['builder_id'];
    $element_id = isset($args['element_id'])?'tb_'.$args['element_id']:$args['module_ID'];
    
    $info_window_map = $fields_args['info_window_map'] === '' ? sprintf('<b>%s</b><br/><p>%s</p>', __('Address', 'themify'), $fields_args['address_map']) : $fields_args['info_window_map'];

// Check if draggable should be disabled on mobile devices
    if ('enable' === $fields_args['draggable_map'] && 'yes' === $fields_args['draggable_disable_mobile_map'] && themify_is_touch()) {
        $fields_args['draggable_map'] = 'disable';
    }

    $container_class = apply_filters('themify_builder_module_classes', array(
        'module',
	'module-' . $mod_name,
	$element_id, 
	$fields_args['css_map'],
	self::parse_animation_effect($fields_args['animation_effect'], $fields_args)
    ), $mod_name, $element_id, $fields_args);
   
    if(!empty($fields_args['global_styles']) && Themify_Builder::$frontedit_active===false){
	$container_class[] = $fields_args['global_styles'];
    }
    $container_props = apply_filters('themify_builder_module_container_props', array(
        'class' => implode(' ', $container_class),
            ), $fields_args, $mod_name, $element_id);
    $args=null;
    $style = '';

    // specify border
    if ($fields_args['b_width_map'] !== '') {
        $style = 'border: ' . $fields_args['b_style_map'] . ' ' . $fields_args['b_width_map'] . 'px';
        if ($fields_args['b_color_map'] !== '') {
            $style.=' ' . Themify_Builder_Stylesheet::get_rgba_color($fields_args['b_color_map']);
        }
        $style .= ';';
    }
    ?>
    <!-- module map -->
    <div <?php echo self::get_element_attributes(self::sticky_element_props($container_props,$fields_args)); ?>>
	<?php $container_props=$container_class=null; 
	    do_action('themify_builder_background_styling',$builder_id,array('styling'=>$fields_args,'mod_name'=>$mod_name),$element_id,'module');
	?>
        <?php if ($fields_args['mod_title_map'] !== ''): ?>
            <?php echo $fields_args['before_title'] . apply_filters('themify_builder_module_title', $fields_args['mod_title_map'], $fields_args). $fields_args['after_title']; ?>
        <?php endif; ?>
            <?php if ( $fields_args['map_provider'] === 'google' && $fields_args['map_display_type'] === 'static'): ?>
		<?php
		$attr = 'key='.Themify_Builder_Model::getMapKey();
		if ($fields_args['address_map'] !== '') {
		    $attr .= '&center=' . $fields_args['address_map'];
		} elseif ($fields_args['latlong_map'] !== '') {
		    $attr .= '&center=' . $fields_args['latlong_map'];
		}
		$attr .= '&zoom=' . $fields_args['zoom_map'];
		$attr .= '&maptype=' . strtolower($fields_args['type_map']);
		$attr .= '&size=' . preg_replace('/[^0-9]/', '', $fields_args['w_map_static']) . 'x' . preg_replace('/[^0-9]/', '', $fields_args['h_map']);
		?>
		<img style="<?php esc_attr_e($style); ?>" src="https://maps.googleapis.com/maps/api/staticmap?<?php echo $attr; ?>" />

        <?php elseif ($fields_args['address_map'] !== '' || $fields_args['latlong_map'] !== ''):
           
            $style .= 'width:' . $fields_args['w_map'] . $fields_args['w_map_unit'] . ';';
            $style .= 'height:' . $fields_args['h_map'] . $fields_args['h_map_unit'] . ';';
            ?>
            <div
		data-map-provider="<?php echo $fields_args['map_provider'] ?>"
		data-address="<?php esc_attr_e( $fields_args['address_map'] !== '' ? $fields_args['address_map'] : $fields_args['latlong_map'] ) ?>"
		data-zoom="<?php echo $fields_args['zoom_map']; ?>"
		data-type="<?php echo $fields_args['map_provider'] === 'google'?$fields_args['type_map']:$fields_args['bing_type_map']; ?>"
		data-scroll="<?php echo $fields_args['scrollwheel_map'] === 'enable'; ?>"
		data-drag="<?php echo $fields_args['draggable_map'] === 'enable'; ?>"
		data-control="<?php echo $fields_args['map_control'] === 'no'; ?>"
		class="<?php echo $fields_args['map_provider'] === 'google'?'themify_map':'themify_bing_map'?> map-container"
		style="<?php  echo $style; ?>"
		data-info-window="<?php  esc_attr_e($info_window_map); ?>"
		data-reverse-geocoding="<?php echo empty($fields_args['address_map']) && !empty($fields_args['latlong_map']) ?>">
	    </div>
        <?php endif; ?>
	
    </div>
    <!-- /module map -->
<?php endif; ?>
<?php TFCache::end_cache(); ?>
