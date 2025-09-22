<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

    <p>Hi <?php echo esc_html( $order->get_billing_first_name() ); ?>,</p>

    <p>Your booking <strong>#<?php echo $order->get_order_number(); ?></strong> has been successfully cancelled.</p>

    <p>If this was a mistake, you can always make a new booking on our website.</p>

<?php
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
do_action( 'woocommerce_email_footer', $email );
