<?php
if (!defined('ABSPATH'))
    exit; // Exit if accessed directly
/**
 * Builder Frontend Panel HTML
 */
$post_id =Themify_Builder::$builder_active_id;
$is_admin = is_admin();
$post_id = empty( $post_id ) && true === $is_admin && !empty($_GET['post']) ? $_GET['post'] : $post_id;

$module_categories = array(
	'general' => array(
		'label' => __( 'General', 'themify' ),
		'active' => true,
	),
	'addon' => array(
		'label' => __( 'Addons', 'themify' ),
		'active' => true,
	),
	'site' => array(
		'label' => __( 'Site', 'themify' ),
		'active' => true,
	),
);
$module_categories = apply_filters( 'themify_module_categories', $module_categories );
?>
<template id="tb_toolbar_template">
    <div id="tb_toolbar" class="clearfix">
	<span class="tb_docked_minimize"></span>
	<div class="tb_toolbar_add_modules_wrap">
	    <span class="tb_toolbar_add_modules"></span>
	    <div id="tb_module_panel" class="tb_modules_panel_wrap">
        <div class="tb_resizable tb_resizable-e" data-axis="x"></div>
        <div class="tb_resizable tb_resizable-s" data-axis="y"></div>
        <div class="tb_resizable tb_resizable-st" data-axis="-y"></div>
        <div class="tb_resizable tb_resizable-w" data-axis="w"></div>
        <div class="tb_resizable tb_resizable-se" data-axis="se"></div>
        <div class="tb_resizable tb_resizable-we" data-axis="sw"></div>
        <div class="tb_resizable tb_resizable_nw" data-axis="nw"></div>
        <div class="tb_resizable tb_resizable_ne" data-axis="ne"></div>
        <div class="tb_drag_top">
		    <div class="tb_drag_handle"></div>
		    <div class="ti-close tb_float_close"></div>
		    <div class="ti-minus tb_float_minimize"></div>
		    <div>
			<span class="tb_ui_dropdown_label" tabindex="-1"><?php _e('Modules', 'themify') ?></span>
			<ul class="tb_module_types">
			    <li class="current" data-hide="tb_module_panel_tab" data-target="tb_module_panel_modules_wrap"><?php _e('Modules', 'themify') ?></li>
			    <li data-hide="tb_module_panel_tab" data-target="tb_module_panel_rows_wrap"><?php _e('Blocks', 'themify') ?></li>
			    <li data-hide="tb_module_panel_tab" data-target="tb_module_panel_library_wrap"><?php _e('Saved', 'themify') ?></li>
			</ul>
		    </div>
		</div>
		<div class="tb_module_panel_container<?php echo apply_filters('tb_toolbar_module', '') ?>">
		    <div class="tb_module_panel_search">
			<input type="text" class="tb_module_panel_search_text" data-search="true" autofocus required pattern=".*\S.*"/>
			<i class="tb_clear_input"></i>
		    </div>
		    <div class="tb_module_panel_tab tb_module_panel_modules_wrap">
		    	<div class="tb_module_panel_tab_acc tb_module_panel_tab_acc_component tb-grid-options-container">
		    		<div class="tb_module_panel_tab_acc_title js-tb_module_panel_acc"><h4><?php _e( 'Rows', 'themify' ); ?></h4></div>
		    		<div class="tb_module_panel_tab_acc_content">
			<ul class="tb_rows_grid">
			    <li>
				<div class="tb_row_grid tb_row_grid_1" data-slug="1">
				    <div class="tb_row_grid_title"><?php _e('One Column Row', 'themify'); ?></div>
				    <a href="#" data-type="row" class="add_module_btn tb_disable_sorting"></a>
				</div>
			    </li>
			    <li>
				<div class="tb_row_grid tb_row_grid_2" data-slug="2">
				    <div class="tb_row_grid_title"><?php _e('Two Columns Row', 'themify'); ?></div>
				    <a href="#" data-type="row" class="add_module_btn tb_disable_sorting"></a>
				</div>
			    </li>
			    <li>
				<div class="tb_row_grid tb_row_grid_3" data-slug="3">
				    <div class="tb_row_grid_title"><?php _e('Three Columns Row', 'themify'); ?></div>
				    <a href="#" data-type="row" class="add_module_btn tb_disable_sorting"></a>
				</div>
			    </li>
			    <li>
				<div class="tb_row_grid tb_row_grid_4" data-slug="4">
				    <div class="tb_row_grid_title"><?php _e('Four Columns Row', 'themify'); ?></div>
				    <a href="#" data-type="row" class="add_module_btn tb_disable_sorting"></a>
				</div>
			    </li>
			    <li>
				<div class="tb_row_grid tb_row_grid_5" data-slug="5">
				    <div class="tb_row_grid_title"><?php _e('Five Columns Row', 'themify'); ?></div>
				    <a href="#" data-type="row" class="add_module_btn tb_disable_sorting"></a>
				</div>
			    </li>
			    <li>
				<div class="tb_row_grid tb_row_grid_6" data-slug="6">
				    <div class="tb_row_grid_title"><?php _e('Six Columns Row', 'themify'); ?></div>
				    <a href="#" data-type="row" class="add_module_btn tb_disable_sorting"></a>
				</div>
			    </li>
			</ul>
			<div class="tb_page_break_module">
			    <div class="tb_page_break_title"><?php _e('Page Break', 'themify'); ?></div>
			    <a href="#" data-type="page_break" class="add_module_btn tb_disable_sorting"></a>
			</div>
		    		</div>
		    	</div>

                <div class="tb_module_panel_tab_acc tb_module_panel_tab_acc_component">
                    <div class="tb_module_panel_tab_acc_content tb_module_category_content tb_module_panel_tab_acc_content_favorite" data-category="favorite"></div>
                </div>
			<?php foreach ( $module_categories as $class => $category ) : ?>
    			<div class="tb_module_panel_tab_acc tb_module_panel_tab_acc_component tb_cat_<?php echo $class ?>" data-active="<?php echo $category['active'] ? 1 : 0; ?>" data-cat="<?php echo $class ?>">
					<div class="tb_module_panel_tab_acc_title js-tb_module_panel_acc"><h4><?php echo $category['label']; ?></h4></div>
					<div class="tb_module_panel_tab_acc_content tb_module_category_content" data-category="<?php echo $class; ?>"></div>
		    	</div>
			<?php endforeach; ?>

		    </div>
		    <!-- /tb_module_panel_modules_wrap -->
		    <div class="tb_module_panel_tab tb_module_panel_rows_wrap">
			<div class="tb_ui_dropdown">
			    <span class="tb_ui_dropdown_label" tabindex="-1"><?php _e('All', 'themify') ?></span>
			    <!-- /tb_row_cat_filter_active -->
			    <ul class="tb_ui_dropdown_items">
				<li><?php _e('All', 'themify') ?></li>
			    </ul>
			    <!-- /tb_row_cat_filter -->
			</div>
			<!-- /tb_row_cat_filter_wrap -->
			<div class="tb_predesigned_rows_list">
			    <div class="tb_predesigned_rows_container"></div>
			</div>
			<!-- /tb_predesigned_rows_list -->
		    </div>
		    <div class="tb_module_panel_tab tb_module_panel_library_wrap">
			<span class="tb_ui_dropdown_label" tabindex="-1"><?php _e('Rows', 'themify') ?></span>
			<ul class="tb_module_types tb_library_types">
			    <li class="current" data-type="row" data-hide="tb_library_item" data-target="tb_item_row"><?php _e('Rows', 'themify') ?></li>
			    <li data-type="module" data-hide="tb_library_item" data-target="tb_item_module"><?php _e('Modules', 'themify') ?></li>
			    <li data-type="part" data-hide="tb_library_item" data-target="tb_item_part"><?php _e('Layout Parts', 'themify') ?></li>
			</ul>
			<!-- /tb_library_types -->
			<div class="tb_library_item_list"></div>
			<!-- /tb_library_item_list -->
		    </div>
		    <!-- /tb_module_panel_library_wrap -->
		</div>
	    </div>
	</div>
	<!-- /tb_module_panel -->

	<ul class="tb_toolbar_menu">
	    <li class="tb_toolbar_zoom_menu">
		<a href="#" class="tb_toolbar_zoom_menu_toggle tb_tooltip" data-zoom="100">
		    <i class="ti-zoom-in"></i>
		    <span><?php _e('Zoom', 'themify'); ?></span>
		</a>
		<ul>
		    <li><a href="#" class="tb_zoom" data-zoom="50"><?php _e('50%', 'themify'); ?></a></li>
		    <li><a href="#" class="tb_zoom" data-zoom="75"><?php _e('75%', 'themify'); ?></a></li>
		    <li><a href="#" class="tb_zoom" data-zoom="100"><?php _e('100%', 'themify'); ?></a></li>
		</ul>
	    </li>
	    <li class="tb_toolbar_divider hide-if-backend"></li>
	    <li class="hide-if-backend"><a href="#" class="tb_tooltip tb_toolbar_builder_preview"><i class="ti-layout-media-center-alt"></i><span><?php _e('Preview', 'themify'); ?></span></a></li>
	    <li class="tb_toolbar_divider hide-if-backend"></li>
	    <?php
	    $breakpoints = themify_get_breakpoints();
	    $breakpoints = array_merge(array('desktop'=>''),$breakpoints);
	    $popular_devices = themify_get_popular_devices();
	    $cus_css = get_post_meta( $post_id, 'tbp_custom_css', true );
	    ?>
	    <li class="tb_toolbar_desktop_switcher">
		<a href="javascript:void(0);" class="tb_tooltip tb_compact tb_compact_switcher breakpoint-desktop"><i class="ti-desktop"></i>
		    <span><?php _e('Desktop',  'themify'); ?></span>
		</a>
		<ul class="tb_toolbar_down">
		    <?php foreach ($breakpoints as $b => $v): ?>
			<li class="tb_toolbar_<?php echo strtolower( $b ); ?>_switcher">
			    <a href="#" class="tb_tooltip tb_breakpoint_switcher breakpoint-<?php echo $b?>" tabindex="-1"><i class="<?php if($b==='tablet_landscape'):?>ti-tablet <?php endif;?>ti-<?php echo $b?>"></i>
			    <?php $b = $b==='tablet_landscape' ? __( 'Tablet Landscape', 'themify' ) : ucfirst($b);?>
			    <span><?php printf('%s', $b);?></span></a>
                <?php if($is_admin===false && 'desktop' !== strtolower($b) ): ?>
                <ul class="tb_popular_devices">
                   <?php $devices = 'mobile' === strtolower($b) ? $popular_devices['mobile'] : $popular_devices['tablet']; ?>
                   <?php foreach ($devices as $device=>$size): ?>
                   <?php printf('<li data-width="%s" data-height="%s">%s</li>',$size[0],$size[1],$device); ?>
                   <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </li>
		    <?php endforeach; ?>
		</ul>
	    </li>
	    <li class="tb_toolbar_divider"></li>
	    <li class="tb_toolbar_undo">
		<a href="javascript:void(0);" class="tb_tooltip tb_compact tb_compact_undo tb_disabled"><i class="ti-back-left"></i><span><?php _e('Undo (CTRL+Z)', 'themify'); ?></span></a>
		<ul class="tb_toolbar_down">
		    <li><a href="#" class="tb_tooltip tb_undo_redo tb_undo_btn tb_disabled"><i class="ti-back-left"></i><span><?php _e('Undo (CTRL+Z)', 'themify'); ?></span></a></li>
		    <li><a href="#" class="tb_tooltip tb_undo_redo tb_redo_btn tb_disabled"><i class="ti-back-right"></i><span><?php _e('Redo (CTRL+SHIFT+Z)', 'themify'); ?></span></a></li>
		</ul>
	    </li>
	    <li class="tb_toolbar_divider"></li>
	    <li class="tb_toolbar_import">
		<a href="javascript:void(0);" class="tb_compact tb_tooltip"><i class="ti-import"></i><span><?php _e('Import', 'themify'); ?></span></a>
		<ul class="tb_toolbar_down">
		    <li>
			<a href="javascript:void(0);" class="tb_import_btn tb_tooltip" tabindex="-1"><i class="ti-import"></i><span><?php _e('Import', 'themify'); ?></span></a>
			<ul>
			    <li><a href="#" data-component="file" class="tb_import"><?php _e('Import From File', 'themify'); ?></a></li>
			    <li><a href="#" data-component="page" class="tb_import"><?php _e('Import From Page', 'themify'); ?></a></li>
			    <li><a href="#" data-component="post" class="tb_import"><?php _e('Import From Post', 'themify'); ?></a></li>
			</ul>
		    </li>
		    <li class="tb_toolbar_export"><a href="<?php echo wp_nonce_url('?themify_builder_export_file=true&postid=' . $post_id, 'themify_builder_export_nonce') ?>" class="tb_tooltip tb_export_link"><i class="ti-export"></i><span><?php _e('Export', 'themify'); ?></span></a></li>
		</ul>
	    </li>
	    <li class="tb_toolbar_divider"></li>
	    <li class="tb_toolbar_layout"><a href="javascript:void(0);" class="tb_tooltip" tabindex="-1"><i class="ti-layout"></i><span><?php _e('Layouts',  'themify'); ?></span></a>
		<ul>
		    <li><a href="#" class="tb_load_layout"><?php _e('Load Layout', 'themify'); ?></a></li>
		    <li><a href="#" class="tb_save_layout"><?php _e('Save as Layout', 'themify'); ?></a></li>
		</ul>
	    </li>
	    <li class="tb_toolbar_divider"></li>
	    <li><a href="#" class="tb_tooltip tb_dup_link"><i class="ti-layers"></i><span><?php _e('Duplicate this page', 'themify'); ?></span></a></li>
	    <li class="tb_toolbar_divider"></li>
	    <li><a href="#" class="tb_tooltip tb_custom_css<?php echo trim($cus_css) !== '' ? ' tb_tooltip_active' : ''; ?>"><span><?php _e('Custom CSS', 'themify'); ?></span><?php _e('CSS', 'themify'); ?></a></li>
	    <li class="tb_toolbar_divider"></li>
	    <li class="tb_change_mode">
		<a href="javascript:void(0);" class="tb_tooltip" tabindex="-1"><i class="ti-panel"></i><span><?php _e('Interface Options', 'themify'); ?></span></a>
		<ul>
		    <li class="switch-wrapper">
			<div class="tb_switcher">
			    <label>
				<input type="checkbox" class="tb-checkbox toggle_switch tb_mode">
				<div data-on="<?php _e('Hover Mode','themify')?>" data-off="<?php _e('Hover Mode','themify')?>" class="switch_label"></div>
			    </label>
			</div>
		    </li>
		    <li class="switch-wrapper">
			<div class="tb_switcher">
			    <label>
				<input type="checkbox" class="tb-checkbox toggle_switch tb_right_click_mode" checked="checked">
				<div data-on="<?php _e('Right Click','themify')?>" data-off="<?php _e('Right Click','themify')?>" class="switch_label"></div>
			    </label>
			</div>
		    </li>
            <?php if(!$is_admin): ?>
            <li class="switch-wrapper">
                <div class="tb_switcher">
                    <label>
                        <input type="checkbox" class="tb-checkbox toggle_switch tb_padding_dragging_mode" checked="checked">
                        <div data-on="<?php _e('Padding Dragging','themify')?>" data-off="<?php _e('Padding Dragging','themify')?>" class="switch_label"></div>
                    </label>
                </div>
            </li>
            <?php endif; ?>
		</ul>
	    </li>
	    <li class="tb_toolbar_divider"></li>
	    <li><a href="javascript:void(0);" class="tb_tooltip tb_help_btn"><i class="ti-help"></i><span><?php _e('Help', 'themify'); ?></span></a></li>
	</ul>

	<div class="tb_toolbar_save_wrap">
	    <?php if (get_post_status($post_id) !== 'auto-draft'): ?>
    	    <div class="tb_toolbar_backend_edit">
		    <?php if ($is_admin===true): ?>
			<a href="<?php echo get_permalink($post_id) ?>#builder_active" id="tb_switch_frontend" class="tb_switch_frontend"><i class="ti-arrow-right"></i><span><?php _e('Frontend', 'themify'); ?></span></a>
		    <?php else: ?>
			<a href="<?php echo get_edit_post_link($post_id); ?>" id="tb_switch_backend"><i class="ti-arrow-left"></i><span><?php esc_html_e('Backend', 'themify'); ?></span></a>
		    <?php endif; ?>
    	    </div>
	    <?php endif; ?>
	    <?php if (false === $is_admin): ?>
		<div class="tb_toolbar_close">
		    <a href="#" class="tb_tooltip tb_toolbar_close_btn" title="<?php _e('ESC', 'themify') ?>"><i class="ti-close"></i><span><?php _e('Close', 'themify'); ?></span></a>
		</div>
	    <?php endif;?>
	    <!-- /tb_toolbar_close -->
	    <div class="tb_toolbar_save_btn">
		<a href="#" class="tb_toolbar_save" title="<?php _e('Ctrl + S', 'themify') ?>"><?php _e('Save', 'themify'); ?></a>
		<div tabindex="1" class="tb_toolbar_revision_btn">
		    <span class="ti-angle-down"></span>
		    <ul>
			<li><a href="#" class="tb_revision tb_save_revision"><?php _e('Save as Revision', 'themify'); ?></a></li>
			<li><a href="#" class="tb_revision tb_load_revision"><?php _e('Load Revision', 'themify'); ?></a></li>
		    </ul>
		</div>
	    </div>
	    <!-- /tb_toolbar_save_btn -->
	</div>
	<!-- /tb_toolbar_save_wrap -->
	<!-- Global Styles breadcrumb -->
	<?php if($is_admin===false && Themify_Global_Styles::$isGlobalEditPage===true){
		Themify_Global_Styles::breadcrumb(); 
	    }
	?>
	<!-- /Global Styles breadcrumb -->
    </div>
	
</template>
