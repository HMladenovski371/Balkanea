<?php
get_header();
wp_enqueue_script('filter-tour');

$sidebar_pos = get_post_meta(get_the_ID(), 'rs_tour_sidebar_pos', true);
if(empty($sidebar_pos)) {
    $sidebar_pos = 'left';
}
?>
    <div id="st-content-wrapper" class="st-style-elementor search-result-page tour-layout8" data-layout="8" data-format="popup">
        <?php echo stt_elementorv2()->loadView('services/tour/components/banner-2'); ?>
        <div class="st-banner-search-form style_3">
            <div class="st-search-form-el  container">
                <div class="st-search-el search-form-v3">
                    <?php echo stt_elementorv2()->loadView('services/tour/search-form/wrapper-2'); ?>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="st-results st-hotel-result st-search-tour">
                <div class="row">
                    <?php
                    if ($sidebar_pos == 'left'){
                        echo stt_elementorv2()->loadView('services/tour/components/sidebar-2', ['format' => 'popupmap']);
                    }
                    ?>
                    <?php
                    $query           = array(
                        'post_type'      => 'st_tours' ,
                        'post_status'    => 'publish' ,
                        's'              => ''
                    );
                    global $wp_query , $st_search_query;
                    $tour = STTour::get_instance();
                    $tour->alter_search_query();
                    query_posts($query);
                    $st_search_query = $wp_query;
                    $tour->remove_alter_search_query();
                    wp_reset_query();

                    if (TravelHelper::is_wpml()) {
                        global $sitepress;
                        $sitepress->switch_lang($current_lang, true);
                    }

                    echo stt_elementorv2()->loadView('services/tour/components/content-3');

                    if($sidebar_pos == 'right') {
                        echo stt_elementorv2()->loadView('services/tour/components/sidebar-2', ['format' => 'popupmap']);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php
get_footer();
