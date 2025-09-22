<?php 
$my_theme = wp_get_theme();
$version  = $my_theme->get( 'Version' );
$check    = get_option( 'st_completed_update_location_nested', '' );
$check2   = get_option( 'st_completed_update_location_relationships', '' );
$check3   = get_option( 'st_duplicated_data', '' );
if ( STInput::request( 'page', '' ) == 'st-upgrade-data' ) {
    $upgraded = false;
    if ( $check && $check3 && $check2 ) {
        $upgraded = true;
    }
    echo balanceTags( $this->load_view( 'upgrade/index', false, [
        'upgraded' => $upgraded
    ] ) );
}
?>