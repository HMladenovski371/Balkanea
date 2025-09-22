<?php
/**
 * @package WordPress
 * @subpackage Traveler
 * @since 1.0
 *
 * Content search flight
 *
 * Created by ShineTheme
 */

wp_enqueue_script( 'bootstrap-datepicker.js' );
wp_enqueue_script( 'bootstrap-datepicker-lang.js' );
wp_enqueue_script( 'affilate-api.js' );

$fields = [
	[
		'title'       => esc_html__( 'Origin', 'traveler' ),
		'name'        => 'origin',
		'placeholder' => esc_html__( 'Origin', 'traveler' ),
		'layout_col'  => '6',
		'layout2_col' => '6',
		'is_required' => 'on',
	],
	[
		'title'       => esc_html__( 'Destination', 'traveler' ),
		'name'        => 'destination',
		'placeholder' => esc_html__( 'Destination', 'traveler' ),
		'layout_col'  => '6',
		'layout2_col' => '6',
		'is_required' => 'on',
	],
	[
		'title'       => esc_html__( 'Depart date', 'traveler' ),
		'name'        => 'depart',
		'placeholder' => esc_html__( 'Depart date', 'traveler' ),
		'layout_col'  => '4',
		'layout2_col' => '4',
		'is_required' => 'on',
	],
	[
		'title'       => esc_html__( 'Return date', 'traveler' ),
		'name'        => 'return',
		'placeholder' => esc_html__( 'Return date', 'traveler' ),
		'layout_col'  => '4',
		'layout2_col' => '4',
		'is_required' => 'off',
	],
	[
		'title'       => esc_html__( 'Passengers/Class', 'traveler' ),
		'name'        => 'passengers-class',
		'placeholder' => esc_html__( 'Passengers/Class', 'traveler' ),
		'layout_col'  => '4',
		'layout2_col' => '4',
		'is_required' => 'off',
	],
];

$st_direction = ! empty( $st_direction ) ? $st_direction : 'horizontal';

$marker = st()->get_option( 'tp_marker', '326912' );
$class  = '';
$id     = 'id="sticky-nav"';
if ( isset( $in_tab ) ) {
	$class = 'in_tab';
	$id    = '';
}

if ( ! isset( $field_size ) ) {
	$field_size = '';
}
?>
<div class="search-form hotel-search-form-home hotel-search-form <?php echo esc_attr( $class ); ?>" <?php echo ( $id ); ?>>
	<?php
	$tp_show_api_info    = st()->get_option( 'tp_show_api_info', 'on' );
	$use_whitelabel      = st()->get_option( 'tp_redirect_option', 'off' );
	$tp_locale_default   = st()->get_option( 'tp_locale_default', 'en' );
	$tp_currency_default = st()->get_option( 'tp_currency_default', 'usd' );
	$action              = 'https://jetradar.com/searches/new';
	$target              = '_blank';
	$button_class        = '';
	if ( $use_whitelabel == 'on' ) {
		$button_class = 'btn-tp-search-flights';
		$action       = '';
		$target       = '_self';
		$page_id      = st()->get_option( 'tp_whitelabel_page', '' );

		if ( empty( $page_id ) ) {
			$whitelabel_name = st()->get_option( 'tp_whitelabel', 'whitelabel.travelerwp.com' );
		} else {
			$whitelabel_name = get_permalink( $page_id ) . '/#/flights';
		}
		echo '<input type="hidden" id="current_url" name="current_url" value="' . esc_url( $whitelabel_name ) . '/">';
	}

	?>
	<form role="search" method="get" class="search main-search" autocomplete="off" action="<?php echo esc_url( $action ); ?>" target="<?php echo esc_attr( $target ); ?>">
		<input type="hidden" name="marker" value="<?php echo esc_attr( $marker ); ?>">
		<?php
		echo '<input type="hidden" id="tp_locale_default" name="tp_locale_default" value="' . esc_attr( $tp_locale_default ) . '">';
		echo '<input type="hidden" id="tp_currency_default" name="tp_currency_default" value="' . esc_attr( $tp_currency_default ) . '">';
		?>
		<div class="row">
			<div class="col-lg-5 col-md-5">
				<div class="row">
					<div class="col-lg-5 col-md-6 field-origin">
						<?php echo TravelHelper::getNewIcon( 'ico_maps_search_box' ); ?>
						<?php echo st()->load_template( 'layouts/modern/travelpayouts_api/search/flight/field-origin' ); ?>
					</div>
					<div class="col-lg-6 col-md-6 field-destination">
						<?php echo TravelHelper::getNewIcon( 'ico_maps_search_box' ); ?>
						<?php echo st()->load_template( 'layouts/modern/travelpayouts_api/search/flight/field-destination' ); ?>
						<span class="border-right"></span>
					</div>
				</div>
			</div>
			<div class="col-lg-3 col-md-3">
				<div class="row field-depart">
					<?php echo TravelHelper::getNewIcon( 'ico_calendar_search_box' ); ?>
					<?php
					$starttext = TravelHelper::getDateFormatMomentText();
					$endtext   = TravelHelper::getDateFormatMomentText();
					?>

					<div data-tp-date-format="<?php echo TravelHelper::getDateFormatMoment() ?>" class="form-group input-daterange  form-group-lg form-group-icon-left" data-next="3">
						<div class="check-in-wrapper">
							<label><?php echo __( 'Depart - Return', 'traveler' ); ?></label>
							<div class="render check-in-render"><?php echo esc_html( $starttext ); ?></div><span> - </span><div class="render check-out-render"><?php echo esc_html( $endtext ); ?></div>
						</div>
						<!-- <label for="field-depart-date"><?php echo esc_html__( 'Check in - Checkout', 'traveler' ); ?></label> -->
						<input required placeholder="<?php echo esc_attr( TravelHelper::getDateFormatJs() ); ?>" class="tp_depart_date required" readonly value="" type="text"/>
						<input type="hidden" name="depart_date" class="tp-date-from" value="">
						<input type="hidden" name="return_date" class="tp-date-to" value="">
					</div>
				</div>
			</div>
			<div class="col-lg-2 col-md-2 field-passenger hidden-sm">
				<?php echo TravelHelper::getNewIcon( 'ico_guest_search_box' ); ?>
				<?php echo st()->load_template( 'layouts/modern/travelpayouts_api/search/flight/field-passengers-class' ); ?>
			</div>
			<div class="col-lg-2 col-md-2">
				<div class="form-button">
					<button class="btn btn-primary btn-search btn-bookingdc-search-hotels <?php echo esc_attr( $button_class ); ?>" type="submit"><?php echo esc_html__( 'SEARCH', 'traveler' ); ?></button>
				</div>
			</div>
		</div>
		<?php
		if ( $use_whitelabel == 'on' ) {
			echo '<input type="hidden" name="with_request" value="true">';
		}
		?>
	</form>
</div>
