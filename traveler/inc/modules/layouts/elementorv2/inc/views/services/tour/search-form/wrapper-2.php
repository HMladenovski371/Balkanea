<?php
$result_page = st()->get_option('tours_search_result_page');
if (isset($container)) {
    $class = $container;
} else {
    $class = '';
}
$list_field = st()->get_option('tour_form_fields');
?>
<div class="search-form-wrapper auto-height-form-search  st-search-form-tour">
    <div class="<?php echo esc_attr($class) ?> tour-search-form-home style3">
        <div class="search-form">
            <form action="<?php echo esc_url(get_the_permalink($result_page)); ?>" class="form" method="get">
                <?php
                echo stt_elementorv2()->loadView('services/tour/search-form/location-2', ['has_icon' => true]);
                echo stt_elementorv2()->loadView('services/tour/search-form/date-2',['has_icon' => true]);
                echo stt_elementorv2()->loadView('services/tour/search-form/guest-2',['has_icon' => true]);
                ?>
              <div class="button-search-wrapper">
            <button class="btn btn-primary btn-search">
                <span class="stt-icon stt-icon-search"></span>
                <?php echo esc_html__('Search', 'traveler'); ?>
            </button>
        </div>
            </form>
        </div>
    </div>
</div>