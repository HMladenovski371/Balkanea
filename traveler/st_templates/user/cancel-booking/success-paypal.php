<?php
/**
 * @since 1.2.8
 *   success paypal
 **/
?>

<div class="booking-cancel-notice">
	<img src="<?php echo get_template_directory_uri(); ?>/v2/images/ico_success.svg" alt="Booking Cancel Success"/>
	<div class="notice-success">
		<h4><?php echo __( 'You had sent successful refund request to us.', 'traveler' ); ?></h4>
		<p><?php echo __( 'Please wait for confirmation from our billing team!', 'traveler' ); ?></p>
	</div>
</div>

<div class="alert alert-info mt20" role="alert">
	<p><strong><?php echo __( 'Admin will give a refund for you with your account:', 'traveler' ); ?></strong></p>
	<p class="mt20"><strong><?php echo __( 'Your paypal email: ', 'traveler' ) ?></strong> <em><?php echo esc_html( $cancel_data['your_paypal']['paypal_email'] ); ?></em></p>
	<p class="mt10"><strong><?php echo __( 'Amount: ', 'traveler' ) ?></strong> <em><?php echo TravelHelper::format_money_raw( $cancel_data['refunded'], $cancel_data['currency'] ); ?></em></p>
	<!-- <p class="mt20"><strong><?php echo __( 'Description: ', 'traveler' ) ?></strong> <em><?php echo esc_html( $cancel_data['detail'] ); ?></em></p> -->
</div>
