<?php
/**
 * @package    WordPress
 * @subpackage Traveler
 * @since      1.0
 *
 * Checkout template override
 */
?>

<?php wp_nonce_field( 'traveler_order', 'st_security' ); ?>

<?php
$booking_form = st()->load_template( 'hotel/booking_form', false, [
    'field_coupon' => false
] );
echo apply_filters( 'st_booking_form_billing', $booking_form );
?>

<?php if ( defined( 'ICL_LANGUAGE_CODE' ) && ICL_LANGUAGE_CODE ): ?>
    <input type="hidden" name="lang" value="<?php echo esc_attr( ICL_LANGUAGE_CODE ); ?>">
<?php endif; ?>

<?php do_action( 'st_booking_form_field' ); ?>

<div class="payment_gateways">
    <?php
    if ( ! isset( $post_id ) ) {
        $post_id = false;
    }
    STPaymentGateways::get_payment_gateways_html( $post_id );
    ?>
</div>

<div class="clearfix">
    <div class="row">
        <div class="col-sm-6">
            <?php if ( st()->get_option( 'booking_enable_captcha', 'on' ) == 'on' ) :
                $code = STCoolCaptcha::get_code();
                ?>
                <div class="form-group captcha_box">
                    <label for="field-hotel-captcha"><?php st_the_language( 'captcha' ); ?></label>
                    <img alt="<?php echo TravelHelper::get_alt_image(); ?>"
                         src="<?php echo STCoolCaptcha::get_captcha_url( $code ); ?>"
                         class="captcha_img">

                    <!-- Static field for user input -->
                    <input id="field-hotel-captcha"
                           type="text"
                           name="captcha_input"
                           value=""
                           class="form-control">

                    <!-- Hidden key -->
                    <input type="hidden"
                           name="st_security_key"
                           value="<?php echo esc_attr( $code ); ?>">
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php echo STCart::get_default_checkout_fields( 'st_check_create_account' ); ?>
<?php echo STCart::get_default_checkout_fields( 'st_check_term_conditions' ); ?>

<?php
$cart = STCart::get_carts();
$cart = base64_encode( serialize( $cart ) );
?>
<input type="hidden" name="st_cart" value="<?php echo esc_attr( $cart ); ?>">

<div class="alert form_alert hidden"></div>

<a href="#" onclick="return false"
   class="btn btn-primary btn-st-checkout-submit btn-st-big">
    <?php _e( 'Submit', 'traveler' ); ?>
    <i class="fa fa-spinner fa-spin"></i>
</a>

<!-- Captcha frontend validation -->
<script type="text/javascript">
jQuery(document).ready(function($){
    $('.btn-st-checkout-submit').on('click', function(e){
        var captcha = $('#field-hotel-captcha').val();
        if (captcha === '') {
            e.preventDefault();
            alert('<?php echo esc_js( __( 'Please enter the captcha code.', 'traveler' ) ); ?>');
            return false;
        }
    });
});
</script>
