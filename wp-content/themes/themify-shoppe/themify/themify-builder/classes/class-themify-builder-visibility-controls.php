<?php

/**
 * The Builder Visibility Controls class.
 * This is used to show the visibility controls on all rows and modules.
 *
 * @package	Themify_Builder
 * @subpackage Themify_Builder/classes
 */
class Themify_Builder_Visibility_Controls {

    /**
     * Constructor.
     * 
     * @param object Themify_Builder $builder 
     */
    public function __construct() {
	add_filter('themify_builder_row_classes', array($this, 'row_classes'), 10, 3);
	add_filter('themify_builder_subrow_classes', array($this, 'subrow_classes'), 10, 4);
	add_filter('themify_builder_module_classes', array($this, 'module_classes'), 10, 5);
    }

    /**
    * Append visibility controls CSS classes to rows.
    *
    * @param	array $classes
    * @param	array $row
    * @param	string $builder_id
    * @access 	public
    * @return 	array
    */
    public function row_classes($classes, $row, $builder_id) {
            return !empty($row['styling'])?$this->get_classes($row['styling'], $classes, 'row'):$classes;
    }

    /**
     * Append visibility controls CSS classes to subrows.
     *
     * @param	array $classes
     * @param	array $subrow
     * @param	string $builder_id
     * @access 	public
     * @return 	array
     */
    public function subrow_classes($classes, $subrow, $builder_id) {
            return !empty($subrow['styling'])?$this->get_classes($subrow['styling'], $classes, 'row'):$classes;
    }

    /**
     * Append visibility controls CSS classes to modules.
     * 
     * @param	array $classes
     * @param	string $mod_name
     * @param	string $module_ID
     * @param	array $args
     * @access 	public
     * @return 	array
     */
    public function module_classes($classes, $mod_name = null, $module_ID = null, $args = array()) {
        return $this->get_classes($args, $classes, 'module');
    }

    private function get_classes($args, $classes, $type) {
        $elements = array('desktop', 'tablet', 'tablet_landscape', 'mobile');
        $hide_all = isset($args['visibility_all']) && $args['visibility_all'] === 'hide_all';
        foreach ($elements as $e) {
            if ((isset($args['visibility_' . $e]) && $args['visibility_' . $e] === 'hide') || $hide_all) {
                if (!Themify_Builder::$frontedit_active) {
                    $classes[] = 'hide-' . $e;
                } elseif ($type === 'row') {
                    $classes[] = 'tb_visibility_hidden';
                    break;
                }
            }
        }
        if( ( isset( $args['sticky_visibility'] ) && $args['sticky_visibility'] === 'hide') || $hide_all ){
	        $classes[] = 'hide-on-stick';
        }
        return $classes;
    }

}
