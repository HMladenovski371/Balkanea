<?php
$terms = get_terms($taxonomy, ['hide_empty' => false, 'parent' => 0]);
?>
<div class="sidebar-item pag st-icheck st-border-radius">
    <div class="item-title d-flex justify-content-between align-items-center">
        <div><?php echo esc_html($title); ?></div>
		<?php if ( count( $terms ) >= 1 ) : ?>
        	<i class="fa fa-angle-up" aria-hidden="true"></i>
		<?php endif; ?>
    </div>
    <div class="item-content">
        <?php $term_parent = 0; New_Layout_Helper::listTaxTreeFilter($taxonomy, 0, -1, 'st_hotel', true, $term_parent, 0, 4); ?>

		<?php if ( count( $terms ) > 4 ) : ?>
        	<button class="btn btn-link btn-more-item"><?php echo __('See more', 'traveler'); ?> <span class="stt-icon stt-icon-arrow-down"></span></button>
		<?php endif; ?>
    </div>
</div>