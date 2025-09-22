<?php
/**
 * Cpay Payment for WooCommerce
 *
 * Provides Payment gateway for Cpay processor.
 *
 * @class       WC_Cpay_Payment_Gateway
 * @extends     WC_Payment_Gateway
 * @version     1.2
 * @package     WooCommerce/Classes/Payment
 * @author      Mitko Kockovski
 */
class Wc_Payment_Gateway_For_Cpay_With_Woocommerce extends WC_Payment_Gateway {
	/**
	 * Constructor for the gateway.
	 */
	 
    public function log_action( $message ) {
        $log_file = plugin_dir_path( __FILE__ ) . 'debug.log';
        file_put_contents( $log_file, date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL, FILE_APPEND);
    }
	 
	public function __construct() {

		$this->id                 = 'cpay_gateway';
		$this->icon               = '';
		$this->has_fields         = false;
		$this->method_title       = __( 'Cpay Payment Gateway', 'payment-gateway-for-cpay-with-woocommerce' );
		$this->method_description = __( 'Allows your store to use the Cpay Payment Gateway method.', 'payment-gateway-for-cpay-with-woocommerce' );
		$this->payment_url        = apply_filters( 'cpay_payment_endpoint', 'https://www.cpay.com.mk/client/Page/default.aspx?xml_id=/mk-MK/.loginToPay/.simple/' );
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title           = $this->get_option( 'title' );
		$this->description     = $this->get_option( 'description' );
		$this->merchant_name   = $this->get_option( 'merchant_name' );
		$this->merchant_number = $this->get_option( 'merchant_number' );
		$this->password        = $this->get_option( 'testing_mode', 'no' ) == 'yes' ? 'TEST_PASS' : $this->get_option( 'password' );
		$this->auto_redirect   = $this->get_option( 'enable_auto_redirect', 'no' ) == 'yes' ? 1 : 0;

		$this->currency          = 'MKD';
		$this->currency_exchange = apply_filters( 'woo_casys_exchange_rate', $this->get_option( 'currency_exchange' ) ? intval( $this->get_option( 'currency_exchange' ) ) : 1, get_woocommerce_currency() );

		// Actions.
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);
		// 3D functions.
		add_action( 'woocommerce_api_' . $this->id, array( $this, 'secure_3d_process_response' ), 10, 1 );
		if ( $this->auto_redirect ) {
			add_action( 'wp_footer', array( $this, 'add_3d_container_to_footer' ) );
			add_filter( 'woocommerce_update_order_review_fragments', array( $this, 'refresh_form' ), 10, 1 );
		} else {
			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		}
	}

	public function receipt_page( $order_id ) {
		$this->display_3d_form( $order_id );
	}

	/**
	 * Initialize Gateway Settings Form Fields
	 */
	public function init_form_fields() {

		$this->form_fields = apply_filters(
			'wc_cpay_form_fields',
			array(
				'enabled'              => array(
					'title'   => __( 'Enable/Disable', 'payment-gateway-for-cpay-with-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Cpay Payment Gateway', 'payment-gateway-for-cpay-with-woocommerce' ),
					'default' => 'yes',
				),
				'testing_mode'         => array(
					'title'   => __( 'Testing mode', 'payment-gateway-for-cpay-with-woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Testing the integration', 'payment-gateway-for-cpay-with-woocommerce' ),
					'default' => 'no',
				),
				'title'                => array(
					'title'       => __( 'Title', 'payment-gateway-for-cpay-with-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'This controls the title for the payment method the customer sees during checkout.', 'payment-gateway-for-cpay-with-woocommerce' ),
					'default'     => __( 'Cpay Payment Gateway', 'payment-gateway-for-cpay-with-woocommerce' ),
					'desc_tip'    => true,
				),
				'description'          => array(
					'title'       => __( 'Description', 'payment-gateway-for-cpay-with-woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'payment-gateway-for-cpay-with-woocommerce' ),
					'default'     => __( 'Please remit payment to Store Name upon pickup or delivery.', 'payment-gateway-for-cpay-with-woocommerce' ),
					'desc_tip'    => true,
				),
				'merchant_name'        => array(
					'title'       => __( 'Merchant Name', 'payment-gateway-for-cpay-with-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'You need to ask your bank processor for this value.', 'payment-gateway-for-cpay-with-woocommerce' ),
					'default'     => __( 'Your name', 'payment-gateway-for-cpay-with-woocommerce' ),
					'desc_tip'    => true,
				),
				'merchant_number'      => array(
					'title'       => __( 'Merchant Code', 'payment-gateway-for-cpay-with-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'You need to ask your bank processor for this value. Used only for status transaction', 'payment-gateway-for-cpay-with-woocommerce' ),
					'default'     => __( '0000000000', 'payment-gateway-for-cpay-with-woocommerce' ),
					'desc_tip'    => true,
				),
				'password'             => array(
					'title'       => __( 'Password', 'payment-gateway-for-cpay-with-woocommerce' ),
					'type'        => 'password',
					'description' => __( 'You need to ask your bank processor for this value. Used only for status transaction', 'payment-gateway-for-cpay-with-woocommerce' ),
					'default'     => __( 'TEST_PASS', 'payment-gateway-for-cpay-with-woocommerce' ),
					'desc_tip'    => true,
				),
				// 'currency'             => array(
				// 	'title'       => __( 'Casys Currency', 'payment-gateway-for-cpay-with-woocommerce' ),
				// 	'type'        => 'select',
				// 	'options'     => get_woocommerce_currencies(),
				// 	'description' => __( 'If left blank it will take the WooCommerce currency.', 'payment-gateway-for-cpay-with-woocommerce' ),
				// 	'default'     => get_woocommerce_currency(),
				// 	'desc_tip'    => true,
				// ),
				'currency_exchange'    => array(
					'title'       => __( 'Exchange rate', 'payment-gateway-for-cpay-with-woocommerce' ),
					'type'        => 'text',
					'description' => __( 'For EURO 61.5 for example', 'payment-gateway-for-cpay-with-woocommerce' ),
					'default'     => __( '1', 'payment-gateway-for-cpay-with-woocommerce' ),
					'desc_tip'    => true,
				),
				'enable_auto_redirect' => array(
					'title'   => __( 'Enable automatically redirecting to cpay page', 'woocommerce' ),
					'type'    => 'checkbox',
					'label'   => __( 'Some browsers prevent this functionality.', 'woocommerce' ),
					'default' => 'yes',
				),
			)
		);
		log_action($this->form_fields);
	}

	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id  Created order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {

		wc_add_notice( __( 'Redirecting to payment page.', 'payment-gateway-for-cpay-with-woocommerce' ), 'success' );
		$return = array(
			'result'   => 'success',
			'refresh'  => true,
			'messages' => "\n\t<div class=\"woocommerce-message\" role=\"alert\">" . __( 'Redirecting to payment page.', 'payment-gateway-for-cpay-with-woocommerce' ) . "</div>\n\t",
		);
		if ( $this->auto_redirect ) {
			WC()->session->set( $this->id . '_order_id', $order_id );
		} else {
			$order = wc_get_order( $order_id );
			$return['redirect'] = add_query_arg( 'order', $order->get_id(), add_query_arg( 'key', $order->get_order_key(), $order->get_checkout_payment_url( true ) ) );
		}
		return $return;
	}

	/**
	 * Add container to footer to refresh fragments.
	 */
	public function add_3d_container_to_footer() {
		?>
		<div class="<?php echo esc_attr( $this->id ); ?>-3d-secure-form-container"></div>
		<?php
	}

	/**
	 * Refresh the 3d secure form.
	 *
	 * @param  array $fragments Contain refresh fragments.
	 * @return array
	 */
	public function refresh_form( $fragments ) {
		$fragments[ '.' . esc_attr( $this->id ) . '-3d-secure-form-container' ] = $this->get_display_3d_form();
		return $fragments;
	}

	/**
	 * Display the form that's automatically submitted on the front-end.
	 *
	 * @return string
	 */
	protected function get_display_3d_form() {
		ob_start();
		$this->display_3d_form();
		return ob_get_clean();
	}

	/**
	 * 3D secure form that automatically submits.
	 *
	 * @return void
	 */
	public function display_3d_form( $order_id = 0 ) {

		?>
		<div class="<?php echo esc_attr( $this->id ); ?>-3d-secure-form-container">
			<?php
			if ( 0 === $order_id ) {
				$order_id = WC()->session->get( $this->id . '_order_id' );
			}
			if ( $order_id ) {
				// $order_id = WC()->session->get( $this->id . '_order_id' );
				WC()->session->__unset( $this->id . '_order_id' );
				$order                 = wc_get_order( $order_id );
				$redirect_url          = get_site_url() . '/?wc-api=' . esc_attr( $this->id ) . '&order=' . $order_id;
				$redirect_url_2        = get_site_url() . '/?wc-api=' . esc_attr( $this->id ) . '&order=' . $order_id . '&failed=1';
				$order_total           = ceil( apply_filters( 'casys_order_total', $order->get_total() ) );
				$order_total_formatted = ( $order_total * $this->currency_exchange ) * 100;
				$billing_first_name    = $order->get_billing_first_name();
				$billing_last_name     = $order->get_billing_last_name();
				$billing_address       = $order->get_billing_address_1();
				$billing_town          = $order->get_billing_city();
				$billing_zip           = $order->get_billing_postcode();
				$billing_phone         = $order->get_billing_phone();
				$billing_email         = $order->get_billing_email();

				$pay_to_merchant = $this->merchant_number;
				$merchant_name   = $this->merchant_name;
				$md5password     = $this->password;

				$details_1 = 'Order #' . $order_id;
				$details_2 = $order_id;

				$currency                = $this->currency ? $this->currency : $order->get_currency();
				$currency                = apply_filters( 'casys_order_currency', $currency );
				$org_currency            = $order->get_currency();
				$order_total_formatted_2 = sprintf( '%03d', mb_strlen( $order_total_formatted, 'UTF-8' ) );
				$pay_to_merchant_2       = sprintf( '%03d', mb_strlen( $pay_to_merchant, 'UTF-8' ) );
				$merchant_name_2         = sprintf( '%03d', mb_strlen( $merchant_name, 'UTF-8' ) );
				$currency_2              = sprintf( '%03d', mb_strlen( $currency, 'UTF-8' ) );
				$details_1_2             = sprintf( '%03d', mb_strlen( $details_1, 'UTF-8' ) );
				$details_2_2             = sprintf( '%03d', mb_strlen( $details_2, 'UTF-8' ) );
				$payment_ok_url_2        = sprintf( '%03d', mb_strlen( $redirect_url, 'UTF-8' ) );
				$payment_fail_url_2      = sprintf( '%03d', mb_strlen( $redirect_url_2, 'UTF-8' ) );
				$shipping_first_name     = sprintf( '%03d', mb_strlen( $billing_first_name, 'UTF-8' ) );
				$shipping_last_name      = sprintf( '%03d', mb_strlen( $billing_last_name, 'UTF-8' ) );
				$shipping_address        = sprintf( '%03d', mb_strlen( $billing_address, 'UTF-8' ) );
				$shipping_city           = sprintf( '%03d', mb_strlen( $billing_town, 'UTF-8' ) );
				$shipping_zip            = sprintf( '%03d', mb_strlen( $billing_zip, 'UTF-8' ) );
				$shipping_phone          = sprintf( '%03d', mb_strlen( $billing_phone, 'UTF-8' ) );
				$shipping_email          = sprintf( '%03d', mb_strlen( $billing_email, 'UTF-8' ) );
				$order_total_2           = sprintf( '%03d', mb_strlen( $order_total, 'UTF-8' ) );
				$org_currency_2          = sprintf( '%03d', mb_strlen( $org_currency, 'UTF-8' ) );
				
				log_action($check_sum_header . $order_total_formatted . $pay_to_merchant . $merchant_name . $currency . $details_1 . $details_2 . $redirect_url . $redirect_url_2 . $billing_first_name . $billing_last_name . $billing_address . $billing_town . $billing_zip . $billing_phone . $billing_email . $order_total . $org_currency . $md5password);
				
				$CheckSumHeader = "16AmountToPay,PayToMerchant,MerchantName,AmountCurrency,Details1,Details2,PaymentOKURL,PaymentFailURL,FirstName,LastName,Address,City,Zip,Telephone,Email,OriginalAmount," . $AmountToPay2 . $PayToMerchant2 . $MerchantName2 . $AmountCurrency2 . $Details12 . $Details22 . $PaymentOKURL2 . $PaymentFailURL2 . $FirstName2 . $LastName2 . $Address2 . $City2 . $Zip2 . $Telephone2 . $Email2 . $OriginalAmount2;
                $CheckSumHeader2 = $CheckSumHeader . $AmountToPay . $PayToMerchant . $MerchantName . $AmountCurrency . $Details1 . $Details2 . $PaymentOKURL . $PaymentFailURL . $FirstName . $LastName . $Address . $City . $Zip . $Telephone . $Email . $OriginalAmount . $md5password;
				
				// if ( $this->currency !== $org_currency ) {
				// 	$check_sum_header   = '17AmountToPay,PayToMerchant,MerchantName,AmountCurrency,Details1,Details2,PaymentOKURL,PaymentFailURL,FirstName,LastName,Address,City,Zip,Telephone,Email,OriginalAmount,OriginalCurrency,' . $order_total_formatted_2 . $pay_to_merchant_2 . $merchant_name_2 . $currency_2 . $details_1_2 . $details_2_2 . $payment_ok_url_2 . $payment_fail_url_2 . $shipping_first_name . $shipping_last_name . $shipping_address . $shipping_city . $shipping_zip . $shipping_phone . $shipping_email . $order_total_2 . $org_currency_2;
				// 	$check_sum_header_2 = $check_sum_header . $order_total_formatted . $pay_to_merchant . $merchant_name . $currency . $details_1 . $details_2 . $redirect_url . $redirect_url_2 . $billing_first_name . $billing_last_name . $billing_address . $billing_town . $billing_zip . $billing_phone . $billing_email . $order_total . $org_currency . $md5password;
				// } else {
				// 	$check_sum_header   = '16AmountToPay,PayToMerchant,MerchantName,AmountCurrency,Details1,Details2,PaymentOKURL,PaymentFailURL,FirstName,LastName,Address,City,Zip,Telephone,Email,OriginalAmount,' . $order_total_formatted_2 . $pay_to_merchant_2 . $merchant_name_2 . $currency_2 . $details_1_2 . $details_2_2 . $payment_ok_url_2 . $payment_fail_url_2 . $shipping_first_name . $shipping_last_name . $shipping_address . $shipping_city . $shipping_zip . $shipping_phone . $shipping_email . $order_total_2;
				// 	$check_sum_header_2 = $check_sum_header . $order_total_formatted . $pay_to_merchant . $merchant_name . $currency . $details_1 . $details_2 . $redirect_url . $redirect_url_2 . $billing_first_name . $billing_last_name . $billing_address . $billing_town . $billing_zip . $billing_phone . $billing_email . $order_total . $md5password;
				// }
				$check_sum = md5( $check_sum_header_2 );
				$language  = 'mk-MK';
				?>
				<form name="cpayForm" method="post" action="<?php echo esc_url( $this->payment_url ); ?>" id="cpayForm" target="_self">
					<input id='AmountToPay' name='AmountToPay' value='<?php echo esc_attr( $order_total_formatted ); ?>' type='hidden' />
					<input id='PayToMerchant' name='PayToMerchant' value='<?php echo esc_attr( $pay_to_merchant ); ?>' type='hidden' />
					<input id='MerchantName' name='MerchantName' value='<?php echo esc_attr( $merchant_name ); ?>' type='hidden' />
					<input id='AmountCurrency' name='AmountCurrency' value='<?php echo esc_attr( $currency ); ?>' type='hidden' />
					<input id='Details1' name='Details1' value='<?php echo esc_attr( $details_1 ); ?>' type='hidden' />
					<input id='Details2' name='Details2' value='<?php echo esc_attr( $details_2 ); ?>' type='hidden' />
					<input id='PaymentOKURL' size='10' name='PaymentOKURL' value='<?php echo esc_attr( $redirect_url ); ?>' type='hidden' />
					<input id='PaymentFailURL' size='10' name='PaymentFailURL' value='<?php echo esc_attr( $redirect_url_2 ); ?>' type='hidden' />
					<input id='CheckSumHeader' name='CheckSumHeader' value='<?php echo esc_attr( $check_sum_header ); ?>' type='hidden' />
					<input id='CheckSum' name='CheckSum' value='<?php echo esc_attr( $check_sum ); ?>' type='hidden' />
					<input id='FirstName' size='10' name='FirstName' value='<?php echo esc_attr( $billing_first_name ); ?>' type='hidden' />
					<input id='LastName' size='10' name='LastName' value='<?php echo esc_attr( $billing_last_name ); ?>' type='hidden' />
					<input id='Address' size='10' name='Address' value='<?php echo esc_attr( $billing_address ); ?>' type='hidden' />
					<input id='City' size='10' name='City' value='<?php echo esc_attr( $billing_town ); ?>' type='hidden' />
					<input id='Zip' size='10' name='Zip' value='<?php echo esc_attr( $billing_zip ); ?>' type='hidden' />
					<input id='Telephone' size='10' name='Telephone' value='<?php echo esc_attr( $billing_phone ); ?>' type='hidden' />
					<input id='Email' size='10' name='Email' value='<?php echo esc_attr( $billing_email ); ?>' type='hidden' />
					<input id='OriginalAmount' name='OriginalAmount' value='<?php echo esc_attr( $order_total ); ?>' type='hidden' />
					<?php if ( $this->currency !== $org_currency ) { ?>
						<input id='OriginalCurrency' name='OriginalCurrency' value='<?php echo esc_attr( $org_currency ); ?>' type='hidden' />
					<?php } ?>
					<input class='button' value='Плати' type='submit'/>
				</form>
				<script>
					jQuery('#cpayForm').submit();
				</script>
			<?php } ?>
		</div>
		<?php
	}
	/**
	 * Process 3D response and validate data. Redirect the user to the right location based on the processing data.
	 *
	 * @param array $api_request
	 * @return void
	 */
	public function secure_3d_process_response( $api_request ) {
		// Make sure we don't get any error reported.
		// Note to WP Plugin reviewer: If this function has even warning the headers will be set and the user will not be redirected. But for your sake here is a fix for this....
		if ( ! WP_DEBUG ) {
			error_reporting( 0 );
		}
		$order          = wc_get_order( intval( $_REQUEST['order'] ) );
		$processed_data = $this->prepare_order_checksum( $order, true, sanitize_text_field( $_REQUEST['cPayPaymentRef'] ) );
		if ( isset( $_REQUEST['failed'] ) && ! empty( $_REQUEST['failed'] ) ) {
			$return_url = get_permalink( wc_get_page_id( 'checkout' ) );
			wc_add_notice( __( 'Issue processing payment. Please check your card information.', 'payment-gateway-for-cpay-with-woocommerce' ), 'error' );
		} else {
			if (
				sanitize_text_field( $_REQUEST['ReturnCheckSum'] ) === $processed_data['return_check_sum'] &&
				sanitize_text_field( $_REQUEST['ReturnCheckSumHeader'] ) === $processed_data['return_check_sum_header'] &&
				sanitize_text_field( $_REQUEST['CheckSum'] ) === $processed_data['check_sum'] &&
				sanitize_text_field( $_REQUEST['CheckSumHeader'] ) === $processed_data['check_sum_header']
			) {
				$return_url = $this->get_return_url( $order );
				$order->payment_complete( sanitize_text_field( $_REQUEST['cPayPaymentRef'] ) );
				$order->save();
			} else {
				$return_url = get_permalink( wc_get_page_id( 'checkout' ) );
				wc_add_notice( __( 'Hash values error. Please check parameters posted to 3D secure page.', 'payment-gateway-for-cpay-with-woocommerce' ), 'error' );
			}
		}
		wp_safe_redirect( $return_url );
		exit();
	}

	/**
	 * Generate checksums for transaction.
	 *
	 * @param WC_Order $order           The WooCommerce order.
	 * @param false    $response_header Should we generate the response checksum.
	 * @param string   $cpay_ref        The cpay reference number.
	 *
	 * @return array
	 */
	private function prepare_order_checksum( $order, $response_header = false, $cpay_ref = '' ) {
		$order_id              = $order->get_id();
		$redirect_url          = get_site_url() . '/?wc-api=' . esc_attr( $this->id ) . '&order=' . $order_id;
		$redirect_url_2        = get_site_url() . '/?wc-api=' . esc_attr( $this->id ) . '&order=' . $order_id . '&failed=1';
		$order_total           = ceil( apply_filters( 'casys_order_total', $order->get_total() ) );
		$order_total_formatted = ( $order_total * $this->currency_exchange ) * 100;
		$billing_first_name    = $order->get_billing_first_name();
		$billing_last_name     = $order->get_billing_last_name();
		$billing_address       = $order->get_billing_address_1();
		$billing_town          = $order->get_billing_city();
		$billing_zip           = $order->get_billing_postcode();
		$billing_phone         = $order->get_billing_phone();
		$billing_email         = $order->get_billing_email();

		$pay_to_merchant = $this->merchant_number;
		$merchant_name   = $this->merchant_name;
		$md5password     = $this->password;

		$details_1 = 'Order #' . $order_id;
		$details_2 = $order_id;

		$currency                = $this->currency ? $this->currency : $order->get_currency();
		$currency                = apply_filters( 'casys_order_currency', $currency );
		$org_currency            = $order->get_currency();
		$order_total_formatted_2 = sprintf( '%03d', mb_strlen( $order_total_formatted, 'UTF-8' ) );
		$pay_to_merchant_2       = sprintf( '%03d', mb_strlen( $pay_to_merchant, 'UTF-8' ) );
		$merchant_name_2         = sprintf( '%03d', mb_strlen( $merchant_name, 'UTF-8' ) );
		$currency_2              = sprintf( '%03d', mb_strlen( $currency, 'UTF-8' ) );
		$details_1_2             = sprintf( '%03d', mb_strlen( $details_1, 'UTF-8' ) );
		$details_2_2             = sprintf( '%03d', mb_strlen( $details_2, 'UTF-8' ) );
		$payment_ok_url_2        = sprintf( '%03d', mb_strlen( $redirect_url, 'UTF-8' ) );
		$payment_fail_url_2      = sprintf( '%03d', mb_strlen( $redirect_url_2, 'UTF-8' ) );
		$shipping_first_name     = sprintf( '%03d', mb_strlen( $billing_first_name, 'UTF-8' ) );
		$shipping_last_name      = sprintf( '%03d', mb_strlen( $billing_last_name, 'UTF-8' ) );
		$shipping_address        = sprintf( '%03d', mb_strlen( $billing_address, 'UTF-8' ) );
		$shipping_city           = sprintf( '%03d', mb_strlen( $billing_town, 'UTF-8' ) );
		$shipping_zip            = sprintf( '%03d', mb_strlen( $billing_zip, 'UTF-8' ) );
		$shipping_phone          = sprintf( '%03d', mb_strlen( $billing_phone, 'UTF-8' ) );
		$shipping_email          = sprintf( '%03d', mb_strlen( $billing_email, 'UTF-8' ) );
		$order_total_2           = sprintf( '%03d', mb_strlen( $order_total, 'UTF-8' ) );
		$org_currency_2          = sprintf( '%03d', mb_strlen( $org_currency, 'UTF-8' ) );
		$cpay_ref_2              = sprintf( '%03d', mb_strlen( $cpay_ref, 'UTF-8' ) );
		if ( $this->currency !== $org_currency ) {
			$check_sum_header   = '17AmountToPay,PayToMerchant,MerchantName,AmountCurrency,Details1,Details2,PaymentOKURL,PaymentFailURL,FirstName,LastName,Address,City,Zip,Telephone,Email,OriginalAmount,OriginalCurrency,' . $order_total_formatted_2 . $pay_to_merchant_2 . $merchant_name_2 . $currency_2 . $details_1_2 . $details_2_2 . $payment_ok_url_2 . $payment_fail_url_2 . $shipping_first_name . $shipping_last_name . $shipping_address . $shipping_city . $shipping_zip . $shipping_phone . $shipping_email . $order_total_2 . $org_currency_2;
			$check_sum_header_2 = $check_sum_header . $order_total_formatted . $pay_to_merchant . $merchant_name . $currency . $details_1 . $details_2 . $redirect_url . $redirect_url_2 . $billing_first_name . $billing_last_name . $billing_address . $billing_town . $billing_zip . $billing_phone . $billing_email . $order_total . $org_currency . $md5password;
			if ( $response_header ) {
				$return_check_sum_header   = '18PayToMerchant,AmountToPay,MerchantName,AmountCurrency,Details1,Details2,PaymentOKURL,PaymentFailURL,FirstName,LastName,Address,City,Zip,Telephone,Email,OriginalAmount,OriginalCurrency,cPayPaymentRef,' . $pay_to_merchant_2 . $order_total_formatted_2 . $merchant_name_2 . $currency_2 . $details_1_2 . $details_2_2 . $payment_ok_url_2 . $payment_fail_url_2 . $shipping_first_name . $shipping_last_name . $shipping_address . $shipping_city . $shipping_zip . $shipping_phone . $shipping_email . $order_total_2 . $org_currency_2 . $cpay_ref_2;
				$return_check_sum_header_2 = $return_check_sum_header . $pay_to_merchant . $order_total_formatted . $merchant_name . $currency . $details_1 . $details_2 . $redirect_url . $redirect_url_2 . $billing_first_name . $billing_last_name . $billing_address . $billing_town . $billing_zip . $billing_phone . $billing_email . $order_total . $org_currency . $cpay_ref . $md5password;
			}
		} else {
			$check_sum_header   = '16AmountToPay,PayToMerchant,MerchantName,AmountCurrency,Details1,Details2,PaymentOKURL,PaymentFailURL,FirstName,LastName,Address,City,Zip,Telephone,Email,OriginalAmount,' . $order_total_formatted_2 . $pay_to_merchant_2 . $merchant_name_2 . $currency_2 . $details_1_2 . $details_2_2 . $payment_ok_url_2 . $payment_fail_url_2 . $shipping_first_name . $shipping_last_name . $shipping_address . $shipping_city . $shipping_zip . $shipping_phone . $shipping_email . $order_total_2;
			$check_sum_header_2 = $check_sum_header . $order_total_formatted . $pay_to_merchant . $merchant_name . $currency . $details_1 . $details_2 . $redirect_url . $redirect_url_2 . $billing_first_name . $billing_last_name . $billing_address . $billing_town . $billing_zip . $billing_phone . $billing_email . $order_total . $md5password;
			if ( $response_header ) {
				$return_check_sum_header   = '17PayToMerchant,AmountToPay,MerchantName,AmountCurrency,Details1,Details2,PaymentOKURL,PaymentFailURL,FirstName,LastName,Address,City,Zip,Telephone,Email,OriginalAmount,cPayPaymentRef,' . $pay_to_merchant_2 . $order_total_formatted_2 . $merchant_name_2 . $currency_2 . $details_1_2 . $details_2_2 . $payment_ok_url_2 . $payment_fail_url_2 . $shipping_first_name . $shipping_last_name . $shipping_address . $shipping_city . $shipping_zip . $shipping_phone . $shipping_email . $order_total_2 . $cpay_ref_2;
				$return_check_sum_header_2 = $return_check_sum_header . $pay_to_merchant . $order_total_formatted . $merchant_name . $currency . $details_1 . $details_2 . $redirect_url . $redirect_url_2 . $billing_first_name . $billing_last_name . $billing_address . $billing_town . $billing_zip . $billing_phone . $billing_email . $order_total . $cpay_ref . $md5password;
			}
		}
		$check_sum = md5( $check_sum_header_2 );
		$return    = array(
			'check_sum'        => $check_sum,
			'check_sum_header' => $check_sum_header,
		);
		if ( $response_header ) {
			$return_check_sum                  = strtoupper( md5( $return_check_sum_header_2 ) );
			$return['return_check_sum']        = $return_check_sum;
			$return['return_check_sum_header'] = $return_check_sum_header;
		}
		return $return;
	}
}
