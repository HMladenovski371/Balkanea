<?php
$terms = get_terms($taxonomy, ['hide_empty' => false, 'parent' => 0]);
?>
<div class="sidebar-item pag st-icheck st-border-radius">
    <div class="item-title d-flex justify-content-between align-items-center">
        <h4><?php echo esc_html($title); ?></h4>
        <i class="fa fa-angle-up" aria-hidden="true"></i>
    </div>
    <div class="item-content">
		<?php
		$term_parent = 0;
		New_Layout_Helper::listTaxTreeFilter($taxonomy, 0, -1, 'st_cars', true, $term_parent, 0, 4);
		?>

		<?php if ( count( $terms ) > 4 ) : ?>
        	<button class="btn btn-link btn-more-item"><?php echo __('More', 'traveler'); ?> <i class="fa fa-caret-down"></i></button>
		<?php endif; ?>
    </div>
</div>