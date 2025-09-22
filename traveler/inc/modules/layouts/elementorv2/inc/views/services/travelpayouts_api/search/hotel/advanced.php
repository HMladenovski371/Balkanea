<?php
$use_whitelabel = st()->get_option( 'tp_redirect_option', 'off' );
$button_class   = '';
if ( $use_whitelabel == 'on' ) {
	$button_class = 'btn-tp-search-hotels';
}
?>
<div class="form-button d-inline-block d-lg-flex align-items-center justify-content-between form-passengers-class  form-extra-field">
	<div class="button-search-wrapper">
		<button class="btn btn-primary btn-search btn-bookingdc-search-hotels <?php echo esc_attr($button_class); ?>">
			<span class="stt-icon stt-icon-search-normal"></span>
			<?php echo esc_html__( 'Search', 'traveler' ); ?>
		</button>
	</div>
</div>
