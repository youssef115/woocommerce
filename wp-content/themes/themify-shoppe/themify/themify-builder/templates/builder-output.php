<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly 
$args['builder_id'] = (int) $args['builder_id'];
?>
<div id="themify_builder_content-<?php echo $args['builder_id'] ; ?>" data-postid="<?php echo $args['builder_id'] ; ?>" class="themify_builder_content themify_builder_content-<?php echo $args['builder_id'] ; ?> themify_builder<?php if(isset(Themify_Builder_Stylesheet::$generateStyles[$args['builder_id']])):?> tb_generate_css<?php endif;?>"<?php if(isset(Themify_Builder_Stylesheet::$generateStyles[$args['builder_id']])):?> style="visibility:hidden;opacity:0;"<?php endif;?>>
    <?php
    foreach ($args['builder_output'] as $key => $row) {
        if (!empty($row)) {
            if (!isset($row['row_order'])) {
                $row['row_order'] = $key; // Fix issue with import content has same row_order number
            }
            Themify_Builder_Component_Row::template($key, $row, $args['builder_id'] , true);
        }
    } // end row loop
    ?>
</div>
<!-- /themify_builder_content -->
<?php if(!empty($args['pb_pagination'])): ?>
    <!-- themify_builder_pagination -->
	<?php echo $args['pb_pagination']; ?>
    <!-- /themify_builder_pagination -->
<?php endif; ?>
<?php $args=null;?>
