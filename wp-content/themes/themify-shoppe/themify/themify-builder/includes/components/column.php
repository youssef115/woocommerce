<?php

class Themify_Builder_Component_Column extends Themify_Builder_Component_Base {

    public function get_name() {
	return 'column';
    }

       public function get_form_settings($onlyStyle=false) {
	$styles = $this->get_styling();
	if($onlyStyle===true){
	    return $styles;
	}
	$col_form_settings = array(
	    'styling' => array(
		'name' => __('Column Styling', 'themify'),
		'options' => $styles
	    )
	);
	return apply_filters('themify_builder_column_lightbox_form_settings', $col_form_settings);
    }

    /**
     * Get template column.
     * 
     * @param int $rows Row key
     * @param array $row 
     * @param array $cols 
     * @param array $col 
     * @param string $builder_id 
     */
    public static function template($rows, $row, $cols, $col, $builder_id, $order_classes = array(), $echo = false) {
	$print_column_classes = array('module_column tb-column');
	if(isset($col['grid_class'])){
	    $print_column_classes[] = str_replace(array('first', 'last'), array('', ''), $col['grid_class']);
	}
	$column_tag_attrs = array();
	$is_styling = !empty($col['styling']);
	$column_order = isset($col['column_order'])?$col['column_order']:$cols;
	$video_data='';
	if (Themify_Builder::$frontedit_active===false) {
	    if (isset($order_classes[$cols])) {
		$print_column_classes[] = $order_classes[$cols];
	    }
	    $order_classes=null;
	    $print_column_classes[] = 'tb_' . $builder_id . '_column module_column_' . $column_order;
	    if (isset($row['row_order']) && $row['row_order']!=='') {
		$print_column_classes[] = 'module_column_' . $builder_id . '-' . $row['row_order'] . '-' . $column_order;
	    }
	    if (isset($col['element_id'])) {
		$print_column_classes[] = 'tb_' . $col['element_id'];
	    }
	}
	if ($is_styling===true) {
	    if (!empty($col['styling']['background_repeat'])) {
			$print_column_classes[] = $col['styling']['background_repeat'];
	    }
	    if (!empty( $col['styling']['global_styles'] ) ) {
		$print_column_classes = Themify_Global_Styles::add_class_to_components( $print_column_classes,  $col['styling'],$builder_id);
	    }
	    if (isset($col['styling']['background_type']) && $col['styling']['background_type'] === 'image' && isset($col['styling']['background_zoom']) && $col['styling']['background_zoom'] === 'zoom' && $col['styling']['background_repeat'] === 'repeat-none') {
			$print_column_classes[] = 'themify-bg-zoom';
	    }
	    if (!empty($col['styling']['custom_css_column'])) {
			$print_column_classes[] = $col['styling']['custom_css_column'];
	    }
	    // background video
	    $video_data = ' '.self::get_video_background($col['styling']) ;
	}
	$column_tag_attrs['class'] = implode(' ', $print_column_classes);
	$print_column_classes=null;

	if (!empty($col['grid_width']) && Themify_Builder::$frontedit_active===false) {
	    $column_tag_attrs['style'] = 'width: ' . $col['grid_width'] . '%';
	}


	if (!$echo) {
	    $output = PHP_EOL; // add line break
	    ob_start();
	}
	// Start Column Render ######
	?>
	<div <?php echo self::get_element_attributes($column_tag_attrs),$video_data; ?>>
	    <?php
	    $column_tag_attrs=$video_data=null;
	    if ($is_styling===true) {
		do_action('themify_builder_background_styling', $builder_id, $col, $row['row_order'] . '-' . $column_order, 'column');
		self::background_styling($col, 'column',$builder_id);
	    }
	    ?>
	    <?php if (!empty($col['modules'])): ?>
	        <div class="tb-column-inner">
		    <?php
		    foreach ($col['modules'] as $k => $mod) {
				if (isset($mod['mod_name'])) {
					$identifier = array($rows, $cols, $k); // define module id
					Themify_Builder_Component_Module::template($mod, $builder_id, true, $identifier);
				}
				if (!empty($mod['cols'])) {// Check for Sub-rows
					Themify_Builder_Component_SubRow::template($row['row_order'], $cols, $k, $mod, $builder_id, true);
				}
		    }
		    ?>
	        </div>
	    <?php endif; ?>
	</div>
	<?php
	// End Column Render ######

	if (!$echo) {
	    $output .= ob_get_clean();
	    // add line break
	    $output .= PHP_EOL;
	    return $output;
	}
    }

    /**
     * Get template sub-column
     * @param int|string $rows 
     * @param int|string $cols 
     * @param int|string $modules 
     * @param int $col_key 
     * @param array $sub_col 
     * @param string $builder_id 
     * @param boolean $echo 
     */
    public static function template_sub_column($rows, $cols, $modules, $col_key, $sub_col, $builder_id, $order_classes = array(), $echo = false) {
	$print_sub_col_classes = array('sub_column module_column');
	if(isset($sub_col['grid_class'])){
	    $print_sub_col_classes[] = str_replace(array('first', 'last'), array('', ''), $sub_col['grid_class']);
	}
	$is_styling = !empty($sub_col['styling']);
	$video_data='';
	if (Themify_Builder::$frontedit_active===false) {
	    if (isset($order_classes[$col_key])) {
		$print_sub_col_classes[] = $order_classes[$col_key];
	    }
	    $order_classes=null;
	    $print_sub_col_classes[] = 'sub_column_post_' . $builder_id . ' sub_column_' . $rows . '-' . $cols . '-' . $modules . '-' . $col_key;
	    if (isset($sub_col['element_id'])) {
		$print_sub_col_classes[] = 'tb_' . $sub_col['element_id'];
	    }
	}
	$sub_row_class ='sub_row_' . $rows . '-' . $cols . '-' . $modules;
	if ($is_styling===true) {
	    if (!empty($sub_col['styling']['background_repeat'])) {
		$print_sub_col_classes[] = $sub_col['styling']['background_repeat'];
	    }
	    if(!empty($sub_col['styling']['global_styles'])){
		$print_sub_col_classes = Themify_Global_Styles::add_class_to_components( $print_sub_col_classes,  $sub_col['styling'],$builder_id);
	    }
	    if (isset($sub_col['styling']['background_type']) && $sub_col['styling']['background_type'] === 'image' && isset($sub_col['styling']['background_zoom']) && $sub_col['styling']['background_zoom'] === 'zoom' && $sub_col['styling']['background_repeat'] === 'repeat-none') {
		$print_sub_col_classes[] = 'themify-bg-zoom';
	    }
	    if (!empty($sub_col['styling']['custom_css_column'])) {
		$print_sub_col_classes[] = $sub_col['styling']['custom_css_column'];
	    }
	    $video_data = ' ' . self::get_video_background($sub_col['styling']);
	}
	$print_sub_col_classes = implode(' ', $print_sub_col_classes);
	if (!$echo) {
	    $output = PHP_EOL; // add line break
	    ob_start();
	}
	?>
	<div <?php echo !empty($sub_col['grid_width']) && Themify_Builder::$frontedit_active===false ? 'style="width:' . $sub_col['grid_width'] . '%;"' : '' ?> class="<?php echo $print_sub_col_classes ?>"<?php echo $video_data ?>> 
	    <?php
	    $video_data = $print_sub_col_classes=null;
	    if ($is_styling===true) {
		$sub_column_order = $rows . '-' . $cols . '-' . $modules . '-' . $col_key;
		do_action('themify_builder_background_styling', $builder_id, $sub_col, $sub_column_order, 'sub_column');
		self::background_styling($sub_col, 'sub_column',$builder_id);
		$sub_column_order=null;
	    }
	    ?>
	    <?php if (!empty($sub_col['modules'])): ?>
	        <div class="tb-column-inner">
		    <?php
		    foreach ($sub_col['modules'] as $sub_module_k => $sub_module) {
			Themify_Builder_Component_Module::template($sub_module, $builder_id, true, array($sub_row_class, $col_key, $sub_module_k));
		    }
		    ?>
	        </div>
	    <?php endif; ?>
	</div>
	<?php
	// End Sub-Column Render ######

	if (!$echo) {
	    $output .= ob_get_clean();
	    // add line break
	    $output .= PHP_EOL;
	    return $output;
	}
    }

}
