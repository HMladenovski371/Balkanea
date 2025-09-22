<?php
$style = get_post_meta(get_the_ID(), 'rs_style_tour', true);
if (empty($style)) {
    $style = 'grid';
}
$result_string = '';
$result_string = balanceTags(STTour::get_instance()->get_result_string());
global $wp_query, $st_search_query;
if ($st_search_query) {
    $query = $st_search_query;
} else {
    $query = $wp_query;
}

if (empty($format)) {
    $format = '';
}

if (empty($layout)) {
    $layout = '';
}

?>
<div class="col-sm-12 col-md-12 col-lg-8">
	<?php
	echo stt_elementorv2()->loadView( 'services/hotel/components/toolbar', [
		'style'        => $style,
		'has_filter'   => true,
		'post_type'    => 'st_tours',
		'service_text' => __( 'New tour', 'traveler' ),
	] );
	?>
    <div id="modern-search-result" class="modern-search-result list-tab-wrapper style_3" data-layout="8">
        <?php echo st()->load_template('layouts/elementor/common/loader', 'content'); ?>
        <?php
        if ($style == 'grid') {
            echo '<div class="service-list-wrapper service-tour row">';
        } else {
            echo '<div class="service-list-wrapper service-tour list-style">';
        }
        ?>
        <?php
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                echo ($style == 'grid') ? '<div class="col-12 col-md-6 col-lg-6 item-service">' : '<div class="col-12 item-service">';
                if ($style == 'grid') {
                    echo apply_filters('st_elementor_loop_tour_grid_mod_service_view',stt_elementorv2()->loadView('services/tour/loop/grid') , 'style_3');
                }
                echo '</div>';
            }
        } else {
            echo ($style == 'grid') ? '<div class="col-12">' : '';
            echo st()->load_template('layouts/elementor/tour/loop/none');
            echo ($style == 'grid') ? '</div>' : '';
        }
        wp_reset_query();
        echo '</div>';
        ?>
    </div>
    <div class="pagination moderm-pagination" id="moderm-pagination">
        <?php echo TravelHelper::paging(false, false); ?>
    </div>
</div>