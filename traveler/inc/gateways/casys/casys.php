<?php

if (!class_exists('is_available')) {
    class ST_Casys_Payment_Gateway extends STAbstactPaymentGateway
    {
        static private $_ints;

        private $default_status = true;

        private $_gatewayObject = null;

        private $_gateway_id = 'st_casys';

        function __construct()
        {
            add_filter('st_payment_gateway_st_casys_name', array($this, 'get_name'));
        }

        function get_option_fields()
        {
            return array(
                array(
                    'id' => 'skrill_email',
                    'label' => __('Email Address', 'traveler'),
                    'type' => 'text',
                    'section' => 'option_pmgateway',
                    'desc' => __('Your Skrill Email Address', 'traveler'),
                    'condition' => 'pm_gway_st_casys_enable:is(on)'
                ),
                array(
                    'id' => 'skrill_password',
                    'label' => __('Password', 'traveler'),
                    'type' => 'text',
                    'section' => 'option_pmgateway',
                    'desc' => __('Password', 'traveler'),
                    'condition' => 'pm_gway_st_casys_enable:is(on)'
                ),
                array(
                    'id' => 'skrill_enable_sandbox',
                    'label' => __('Enable Test Mode', 'traveler'),
                    'type' => 'on-off',
                    'section' => 'option_pmgateway',
                    'std' => 'on',
                    'desc' => __('Allow you to enable test mode', 'traveler'),
                    'condition' => 'pm_gway_st_casys_enable:is(on)'
                ),
            );
        }

        function _pre_checkout_validate()
        {
            return true;
        }

        function do_checkout($order_id)

        {

            if (!$this->is_available()) {

                return

                    [

                        'status' => 0,

                        'complete_purchase' => 0,

                        'error_type' => 'card_validate',

                        'error_fields' => '',

                    ];

            }



            $gateway = $this->_gatewayObject;

            $gateway->setEmail(st()->get_option('skrill_email', ''));

            $gateway->setPassword(st()->get_option('skrill_password', ''));



            if (st()->get_option('skrill_enable_sandbox', 'on') == 'on') {

                $gateway->setTestMode(true);

            }



            $total = get_post_meta($order_id, 'total_price', true);

            $total = round((float)$total, 2);

            $order_token_code = get_post_meta($order_id, 'order_token_code', true);



            $purchase = [

                'language' => 'EN',

                'amount' => number_format((float)$total, 2, '.', ''),

                'currency' => TravelHelper::get_current_currency('name'),

                'details' => ['item' => get_the_title($order_id)],

                'notifyUrl' => $this->get_return_url($order_id),

                'returnUrl' => $this->get_return_url($order_id),

                'cancelUrl' => $this->get_cancel_url($order_id)

            ];



            $response = $gateway->purchase($purchase)->send();

            if ($response->isSuccessful()) {

                return ['status' => true];

            } elseif ($response->isRedirect()) {

                return ['status' => true, 'redirect' => $response->getRedirectUrl()];

            } else {

                return array('status' => false, 'message' => $response->getMessage(), 'data' => $purchase);

            }

        }

// 		function do_checkout($order_id)
// 		{
// 			$pp = $this->get_authorize_url($order_id);

// 			if (isset($pp['redirect_form']) and $pp['redirect_form'])
// 				$pp_link = $pp['redirect_form'];

// 			do_action('st_before_redirect_stripe');



// 			if ($pp['status']) {
// 				return array(
// 					'status'  => true,
// 				);
// 			}else{
// 				return array(
// 					'status'  => FALSE,
// 					'message' => isset($pp['message']) ? $pp['message'] : FALSE,
// 					'data'    => isset($pp['data']) ? $pp['data'] : FALSE,
// 					'error_step'=>'after_get_authorize_url',
// 					'raw_response'=>$pp
// 				);
// 			}
// 		}


        function complete_purchase($order_id)
        {
            return true;
        }

        function check_complete_purchase($order_id)
        {
        }

        function html()
        {
            echo st()->load_template('gateways/c-pay');
        }

        function get_name()
        {
            return __('Casys', 'traveler');
        }

        function get_default_status()
        {
            return $this->default_status;
        }

        function is_available($item_id = false)
        {
            return true;
        }

        function getGatewayId()
        {
            return $this->_gateway_id;
        }

        function is_check_complete_required()
        {
            return true;
        }

        function get_logo()

        {
            return get_template_directory_uri() . '/img/gateway/skrill-logo.svg';
        }

        static function instance()
        {
            if (!self::$_ints) {
                self::$_ints = new self();
            }
            return self::$_ints;
        }

        static function add_payment($payment)
        {
            $payment['st_casys'] = self::instance();

            return $payment;
        }

    }

    add_filter('st_payment_gateways', ['ST_Casys_Payment_Gateway', 'add_payment']);
}