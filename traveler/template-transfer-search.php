<?php
/**
 * Template Name: Transfer Search Result
 */
$layout_theme = st()->get_option('st_theme_style','classic');
$google_api_key = st()->get_option('st_googlemap_enabled');

if(!st_check_service_available( 'st_cars' )) {
    wp_redirect( home_url() );
    die;
}
$menu_style = st()->get_option('menu_style_modern', '');
if($layout_theme == 'modern'){
    if(check_using_elementor()){
        $layout = get_post_meta(get_the_ID(), 'rs_layout_car_transfer', true);
        if(empty($layout)) $layout = '1';
        if($layout == 1){
			echo st()->load_template('layouts/elementor/car_transfer/search-page');
		} else {
            echo stt_elementorv2()->loadView('services/car_transfer/search-page/sidebar');
        }

    } else {
        echo st()->load_template('layouts/modern/car_transfer/search-page');
    }

    return;
}

global $st_search_query , $st_search_page_id;
$old_page_content = '';
while( have_posts() ) {
    the_post();
    $st_search_page_id = get_the_ID();
    $old_page_content  = get_the_content();
}

$transfer = new STCarTransfer();
$data = $st_search_query = $transfer->get_search_results();

get_header();

echo st()->load_template( 'search-loading' );
get_template_part( 'breadcrumb' );
$result_string = '';

?>
    <div class="mfp-with-anim mfp-dialog mfp-search-dialog mfp-hide" id="search-dialog">
        <?php echo st()->load_template( 'car_transfer/search-form' ); ?>
    </div>
    <div class="container mb20">
        <?php
            $transfer_from = (int)STInput::get('transfer_from','');
            $transfer_to = (int)STInput::get('transfer_to','');
            $roundtrip = STInput::get('roundtrip','');
            $route = $transfer->get_routes($transfer_from, $transfer_to, $roundtrip);
            $time = $transfer->get_driving_distance($transfer_from, $transfer_to, $roundtrip);
        ?>
        <div class="transfer-map mt20" data-route="<?php echo esc_attr( json_encode($route) ); ?>">
            <div class="transfer-map-content"></div>
            <div class="transfer-map-infor">
                <i class="fa fa-clock-o mr10"></i> <?php echo __('Travel time about: ', 'traveler'); ?>
                <?php
                    $hour = ( $time[ 'hour' ] >= 2 ) ? $time[ 'hour' ] . ' ' . esc_html__( 'hours', 'traveler' ) : $time[ 'hour' ] . ' ' . esc_html__( 'hour', 'traveler' );
                    $minute = ( $time[ 'minute' ] >= 2 ) ? $time[ 'minute' ] . ' ' . esc_html__( 'minutes', 'traveler' ) : $time[ 'minute' ] . ' ' . esc_html__( 'minute', 'traveler' );
                    echo esc_attr( $hour ) . ' ' . esc_attr( $minute ) . ' - ' . $time['distance'] . __('Km', 'traveler');
                ?>
            </div>
        </div>
        <?php echo apply_filters( 'the_content' , $old_page_content ); ?>
    </div>
<?php
wp_reset_query();
get_footer();
?>