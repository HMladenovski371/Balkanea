<?php

use BankartPaymentGateway\Client\Client;
use BankartPaymentGateway\Client\Data\Customer;
use BankartPaymentGateway\Client\Transaction\Capture;
use BankartPaymentGateway\Client\Transaction\VoidTransaction;
use BankartPaymentGateway\Client\Transaction\Result;
use balkanea\includes\providers\HotelProvideFactory;
use balkanea\includes\providers\WorldotaProvider;
use balkanea\includes\Hotel;

require WP_PLUGIN_DIR . "/woocommerce-bankart-payment-gateway/classes/vendor/autoload.php";

if (file_exists('/home/balkanea/public_html/wp-content/plugins/balkanea/includes/providers/WorldotaProvider.php')) {
    require_once '/home/balkanea/public_html/wp-content/plugins/balkanea/includes/providers/WorldotaProvider.php';
}
if (file_exists('/home/balkanea/public_html/wp-content/plugins/balkanea/includes/providers/HotelProvideFactory.php')) {
    require_once '/home/balkanea/public_html/wp-content/plugins/balkanea/includes/providers/HotelProvideFactory.php';
}


/**
 * @package    WordPress
 * @subpackage Traveler
 * @since      1.0
 *
 * function
 *
 * Created by ShineTheme
 *
 */

global $wpdb;
//define('WP_DEBUG_DISPLAY', false);

if (!defined('ST_TEXTDOMAIN'))
    define('ST_TEXTDOMAIN', 'traveler');
if (!defined('ST_TRAVELER_VERSION')) {
    $theme = wp_get_theme();
    if ($theme->parent()) {
        $theme = $theme->parent();
    }
    define('ST_TRAVELER_VERSION', $theme->get('Version'));
}
define("ST_TRAVELER_DIR", get_template_directory());
define("ST_TRAVELER_URI", get_template_directory_uri());

// global $st_check_session;

// if ( is_session_started() === FALSE ){
//     $st_check_session = true;
//     session_start();
// }

$status = load_theme_textdomain('traveler', get_stylesheet_directory() . '/language');

get_template_part('inc/class.traveler');
get_template_part('inc/extensions/st-vina-install-extension');

add_filter('http_request_args', 'st_check_request_api', 10, 2);

function log_action( $message ) {
    $log_file = plugin_dir_path( __FILE__ ) . 'debug.log';
    file_put_contents( $log_file, date('Y-m-d H:i:s') . ': ' . $message . PHP_EOL, FILE_APPEND);
}

function st_check_request_api($parse, $url) {
    global $st_check_session;
    if ($st_check_session) {
        session_write_close();
    }

    return $parse;
}
function is_session_started()
{
    if ( php_sapi_name() !== 'cli' ) {
        if ( version_compare(phpversion(), '5.4.0', '>=') ) {
            return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
        } else {
            return session_id() === '' ? FALSE : TRUE;
        }
    }
    return FALSE;
}
add_filter('upload_mimes', 'traveler_upload_types', 1, 1);

function traveler_upload_types($mime_types) {
    $mime_types['svg'] = 'image/svg+xml';

    return $mime_types;
}

add_theme_support(
    'html5', array(
    'search-form',
    'comment-form',
    'comment-list',
    'gallery',
    'caption',
    )
);
function showallIcon(){
    include get_template_directory() . '/v2/fonts/fonts.php';
    if(!empty($fonts)){
        $count = 0;
        ?>
        <ul class="st-list-font-streamline">
            <?php foreach($fonts as $key=>$font){
            $count++;
            if($count < 1000){ ?>
                <li>
                    <?php echo $font; ?>
                    <span><?php echo esc_html($key);?></span>
                </li>
            <?php }
            ?>
        <?php } ?>

        </ul>
        <style>
            .st-list-font-streamline{
                list-style:none;padding:0px; margin:0px;
            }
            .st-list-font-streamline span{
                display:none;
                padding:10px;
                background: #cc0000;
                width: 100px;
            }
            .st-list-font-streamline svg{
                display:inline-block;
                width: 100%;
                height: 32px;
            }
            .st-list-font-streamline li{
                position: relative;
                width: 60px;
                height:60px;
                display:inline-block;
            }
            .st-list-font-streamline li:hover span{
                display:block;
                position: absolute;
                bottom: 100%;
            }
        </style>

    <?php }
}
add_action( 'remove_message_session', 'st_remove_message_session' );
function st_remove_message_session() {
	if ( is_session_started() === false ) {
		session_start();
	}
	$_SESSION['bt_message'] = [];
	session_write_close();
}

function my_hide_notices_to_all_but_super_admin(){
	if ( !empty($_GET['page'] ) && $_GET['page'] == 'st_traveler_options' ) {
		remove_all_actions( 'user_admin_notices' );
		remove_all_actions( 'admin_notices' );
	}
}

//Hristijan Change 1
/*function hotel_crud () {
    $current_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    if ($current_url == 'https://staging.balkanea.com/hotels/'){
       ?>
           <script src='../../../wp-plugin/Hotels_CRUD/main.js'></script>
       <?php
    }
}*/

function hotel_crud() {
    // Тековен URL
    $current_url = home_url( add_query_arg( null, null ) );

    // URL за hotels страницата
    $hotels_url = trailingslashit( home_url( '/hotels/' ) );

    // Проверка
    if ( trailingslashit( $current_url ) === $hotels_url ) {
        ?>
        <script src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../wp-plugin/Hotels_CRUD/main.js' ); ?>"></script>
        <?php
    }
}

//Hristijan change 2
/*function javascript_function() {
    try{
        wp_register_script('main-custom-js', 'https://staging.balkanea.com/wp-plugin/JS-file/main.js', ['jquery'], '1.0.0', true);
        wp_enqueue_script('main-custom-js');
    } catch (Exception $ex) {
        error_log('Caught exception: ' . $ex->getMessage());
    }
}*/

function javascript_function() {
    try {

        $script_url = plugin_dir_url(__FILE__) . 'JS-file/main.js';

        wp_register_script(
            'main-custom-js',
            $script_url,
            ['jquery'],
            '1.0.0',
            true
        );

        wp_enqueue_script('main-custom-js');
    } catch (Exception $ex) {
        error_log('Caught exception: ' . $ex->getMessage());
    }
}



//Dodadeno za JS TODO KIKO:
function enqueue_custom_js() {
    // Enqueue your custom JS file
    wp_enqueue_script(
        'main-custom-js',
        plugin_dir_url(__FILE__) . 'JS-file/main.js',
        ['jquery'],
        '1.0.0',
        true
    );

    // Pass PHP data into JavaScript
    wp_localize_script(
        'main-custom-js',
        'balkaneaApi',
        [
            'ajax_url'               => admin_url('admin-ajax.php'),
            'modal_toggle_url'       => content_url('/wp-plugin/APIs/modal_toggle.php'),
            'cancel_reservation_url' => content_url('/wp-plugin/APIs/cancel_reservation.php'),
            'generate_nonce_url'     => plugin_dir_url(__FILE__) . 'APIs/generate-nonce.php',
        ]
    );
}
add_action('wp_enqueue_scripts', 'enqueue_custom_js');


//END DOdadeno






// function custom_checkout_create_order( $order ) {
//     try{

//         $price_wc = WC()->session->get('full_price');

//         if (!isset($price_wc) || empty($price_wc)) {
//             error_log("Price_Wc is empty");
//             $price_wc = (float) get_post_meta($order->get_id(), '_order_total', true);
//         }

//         global $wpdb;

//         $order_id = $order->get_id();
//         $currency = $order->get_currency();
//         $order_total = (float) $price_wc;

//         if ($currency === 'MKD') {
//             $order_total = $order_total * 61.53;
//             update_post_meta($order_id, '_order_total', round($order_total));

//             foreach ($order->get_items() as $item_id => $item) {
//                 $product = $item->get_product();

//                 if ($product) {
//                     $original_price = $order_total;
//                     $item_price = $order_total;
//                     $sale_price = $order_total;
//                     if (empty($sale_price)) {
//                         $sale_price = $order_total;
//                     }
//                     $sale_price = (float) $order_total;
//                     $line_subtotal = $order_total;
//                     $qty = $item->get_quantity();

//                     $line_total = $line_subtotal * $qty;

//                     error_log("Updating order item meta for item ID: {$item_id}");
//                     error_log("Original Price: {$original_price}, Item Price: {$item_price}, Sale Price: {$sale_price}, Line Total: {$line_total}");


//                     wc_update_order_item_meta($item_id, '_st_ori_price', round($original_price));
//                     wc_update_order_item_meta($item_id, '_st_item_price', round($item_price));
//                     wc_update_order_item_meta($item_id, '_st_sale_price', round($sale_price));
//                     wc_update_order_item_meta($item_id, '_line_total', round($line_total));
//                     wc_update_order_item_meta($item_id, '_st_total_price_origin', round($line_total));
//                     wc_update_order_item_meta($item_id, '_st_total_price', round($line_total));
//                     wc_update_order_item_meta($item_id, '_line_subtotal', round($line_subtotal));
//                     wc_update_order_item_meta($item_id, '_order_currency', 'MKD');
//                 }
//             }

//             $order->calculate_totals();
//             $order->save();
//         }

//         $order_items = $order->get_items();
//         $isRateHawkHotel = false;

//         foreach ($order_items as $item_id => $item) {
//             $booking_id = wc_get_order_item_meta($item_id, '_st_st_booking_id', true);

//             if ($booking_id) {
//                 $author_id = $wpdb->get_var($wpdb->prepare(
//                     "SELECT post_author FROM {$wpdb->prefix}posts WHERE ID = %d",
//                     $booking_id
//                 ));

//                 if ($author_id == 6961) {
//                     $isRateHawkHotel = true;
//                     break;
//                 }
//             }
//         }

//         if ($isRateHawkHotel) {
//             error_log("Hotel is rateHawk");
//             $partner_order_id = isset($_COOKIE['partner_order_id']) ? $_COOKIE['partner_order_id'] : null;
//             $order_data = isset($_COOKIE[$partner_order_id . '_order_data']) ? $_COOKIE[$partner_order_id . '_order_data'] : null;
//             $free_cancellation = isset($_COOKIE[$partner_order_id . '_free_cancellation_before']) ? $_COOKIE[$partner_order_id . '_free_cancellation_before'] : null;
//             $payment_details = isset($_COOKIE[$partner_order_id]) ? $_COOKIE[$partner_order_id] : null;

//             try {

//                 $meta_keys = ['payment_details' => $payment_details, 'partner_order_id' => $partner_order_id, '_order_data' => $order_data, 'free_cancellation' => $free_cancellation];
//                 foreach ($meta_keys as $key => $value) {
//                     if ($value) {
//                         $wpdb->insert(
//                             $wpdb->prefix . 'postmeta',
//                             array(
//                                 'post_id' => $order_id,
//                                 'meta_key' => $key,
//                                 'meta_value' => $value
//                             ),
//                             array('%d', '%s', '%s')
//                         );
//                         if ($wpdb->last_error) {
//                             error_log("Failed to insert $key: " . $wpdb->last_error);
//                         }
//                     }else {
//                         error_log("{$key} cookie details missing.");
//                     }
//                 }

//             } catch (Exception $e) {
//                 error_log('Caught exception: ' . $e->getMessage());
//             }
//         }
//         } catch (Exception $ex) {
//             error_log('Caught exception: ' . $ex->getMessage());
//         }
// }

function custom_checkout_create_order( $order ) {
        error_log("Order details: ");
        error_log(print_r($order, true));
        global $wpdb;

        $order_id = $order->get_id();

        $partner_order_id = isset($_COOKIE['partner_order_id']) ? $_COOKIE['partner_order_id'] : null;
        $order_data = isset($_COOKIE[$partner_order_id . '_order_data']) ? $_COOKIE[$partner_order_id . '_order_data'] : null;
        $free_cancellation = isset($_COOKIE[$partner_order_id . '_free_cancellation_before']) ? $_COOKIE[$partner_order_id . '_free_cancellation_before'] : null;
        $payment_details = isset($_COOKIE[$partner_order_id]) ? $_COOKIE[$partner_order_id] : null;

        try {

            $meta_keys = ['payment_details' => $payment_details, 'partner_order_id' => $partner_order_id, '_order_data' => $order_data, 'free_cancellation' => $free_cancellation];
            error_log("Cookies: ");
            error_log(print_r($meta_keys, true));

            foreach ($meta_keys as $key => $value) {
                if ($value) {
                    $wpdb->insert(
                        $wpdb->prefix . 'postmeta',
                        array(
                            'post_id' => $order_id,
                            'meta_key' => $key,
                            'meta_value' => $value
                        ),
                        array('%d', '%s', '%s')
                    );
                    if ($wpdb->last_error) {
                        error_log("Failed to insert $key: " . $wpdb->last_error);
                    }
                }else {
                    error_log("{$key} cookie details missing.");
                }
            }

        } catch (Exception $e) {
            error_log('Caught exception: ' . $e->getMessage());
        }
}

function custom_checkout_init_webhook() {

    if ( isset( $_GET['failed'] ) && $_GET['failed'] == 1 ) {
        wc_print_notice( 'Your previous payment attempt was unsuccessful. Please try again.', 'error' );
    }

}

function handle_successful_payment($order_id) {
    if ($order_id == 0) {
        exit();
    }

    try {
        $nonce = wp_create_nonce('order_booking_form');

        $data = [
            'data' => $order_id,
            'type' => 'order_booking_finish',
            'security' => $nonce
        ];
//Hristijan Change
       // $url = 'https://staging.balkanea.com/wp-plugin/APIs/order_booking_form.php';
        $url = plugin_dir_url(__FILE__) . 'APIs/order_booking_form.php'; //treba da se proveri kade se naogja orderZ_booking
        $response = wp_remote_post($url, [
            'method'    => 'POST',
            'timeout'   => 0,
            'blocking'  => false,
            'body'      => $data,
            'headers'   => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        if (is_wp_error($response)) {
            error_log('Error sending the request: ' . $response->get_error_message());
            return;
        }

        exit();

    } catch (Exception $error) {
        error_log($error->getMessage());
    }
}


function handle_order_status_changed ($order_id, $from, $to ){

    if ($order_id == 0) {
        return;
    }

    try {
        $order = wc_get_order($order_id);

        if ($order != null)
    	{
            if(( $from==='processing' && $to==='cancelled') || ($from === 'processing' && $to === 'failed')){
                $nonce = wp_create_nonce('cancel_order');

                $data = array(
                    'order_id' => $order_id,
                    'security' => $nonce
                );
//Hristijan Change
                //$url = 'https://staging.balkanea.com/wp-plugin/APIs/cancel_reservation.php';
                $url = plugin_dir_url(__FILE__) . 'APIs/cancel_reservation.php';
                $response = wp_remote_post($url, [
                    'method'    => 'POST',
                    'timeout'   => 0,
                    'blocking'  => false,
                    'body'      => $data,
                    'headers'   => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                ]);

                if (is_wp_error($response)) {
                    error_log('Error sending the request: ' . $response->get_error_message());
                }

        	    $transaction_id = $order->get_transaction_id();
        	    $gateway_options = get_option("woocommerce_bankart_payment_gateway_mcvisa_cards_settings");
        	    $apiKey = $gateway_options["apiKey"];
        	    $apiUser = $gateway_options["apiUser"];
        	    $apiPassword = $gateway_options["apiPassword"];
        	    $sharedSecret = $gateway_options['sharedSecret'];

                $merchantTransactionId = 'v-'.date('Y-m-d').'-'.uniqid();
                $client = new Client($apiUser, $apiPassword, $apiKey, $sharedSecret);
                $cancel = new VoidTransaction();
                $cancel
                  ->setTransactionId($merchantTransactionId)
                  ->setReferenceTransactionId($transaction_id);

                $result = $client->void($cancel);
            }
    	}
    } catch (Exception $error) {
        error_log($error->getMessage());
    }
}

function handle_completed_order($order_id) {
    \balkanea\includes\Log::info('[BookingInfo]: entered ' . __METHOD__);
    $order = wc_get_order($order_id);

    if ($order != null)
	{
	    try {
    	    $transaction_id = $order->get_transaction_id();
    	    $gateway_options = get_option("woocommerce_bankart_payment_gateway_mcvisa_cards_settings");
    	    $apiKey = $gateway_options["apiKey"];
    	    $apiUser = $gateway_options["apiUser"];
    	    $apiPassword = $gateway_options["apiPassword"];
    	    $sharedSecret = $gateway_options['sharedSecret'];
            $currency = $order->get_currency();
            $amount = $order->get_total();
            if ($currency != 'MKD'){
                $amount = round ( $order->get_total() * 61.53 );
                $currency = 'MKD';
            }
            $merchantTransactionId = 'c-'.date('Y-m-d').'-'.uniqid();

            $client = new Client($apiUser, $apiPassword, $apiKey, $sharedSecret);

            $capture = new Capture();

            $capture
              ->setTransactionId($merchantTransactionId)
              ->setAmount($amount)
              ->setCurrency($currency)
              ->setReferenceTransactionId($transaction_id);

            $result = $client->capture($capture);
        }
        catch (Exception $e) {
            error_log($e->getMessage());
        }
	}
}

function handle_order_status_failed ($order_id, $reason){
    error_log("Order ID : $order_id failed with reason: ");
    error_log(print_r($reason, true));

    $transaction_id = $order->get_transaction_id();
    $gateway_options = get_option("woocommerce_bankart_payment_gateway_mcvisa_cards_settings");
    $apiKey = $gateway_options["apiKey"];
    $apiUser = $gateway_options["apiUser"];
    $apiPassword = $gateway_options["apiPassword"];
    $sharedSecret = $gateway_options['sharedSecret'];

    $merchantTransactionId = 'v-'.date('Y-m-d').'-'.uniqid();
    $client = new Client($apiUser, $apiPassword, $apiKey, $sharedSecret);
    $cancel = new VoidTransaction();
    $cancel
      ->setTransactionId($merchantTransactionId)
      ->setReferenceTransactionId($transaction_id);

    $result = $client->void($cancel);

}

function custom_update_cart_prices($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if (did_action('woocommerce_before_calculate_totals') >= 2) {
        return; // Prevent running multiple times
    }

    foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
        error_log("cart_items ".json_encode($cart_item));
        $room_id = isset($cart_item['st_booking_data']['room_id']) ? $cart_item['st_booking_data']['room_id'] : WC()->session->get('full_price');

        if (!$room_id) {
            error_log("Room ID missing for cart item: " . print_r($cart_item, true));
            continue;
        }

        $price_wc = get_post_meta($room_id, 'price', true);

        if (!$price_wc) {
            error_log("Price missing for Room ID: " . $room_id);
            continue;
        }

        error_log("Updating price for Room ID: " . $room_id . " with price: " . $price_wc);

        // Update price
        $cart_item['data']->set_price((float) $price_wc);

        // Ensure price updates correctly in cart contents
        $cart->cart_contents[$cart_item_key]['extra_price'] = (float) $price_wc;
        $cart->cart_contents[$cart_item_key]['ori_price'] = (float) $price_wc;
        $cart->cart_contents[$cart_item_key]['sale_price'] = (float) $price_wc;
        $cart->cart_contents[$cart_item_key]['total_price'] = (float) $price_wc;
    }
}

add_action('woocommerce_before_calculate_totals', 'custom_update_cart_prices', 10, 1);
// add_action('woocommerce_order_set_failed', 'handle_order_status_failed', 10, 2);
//add_action( 'woocommerce_order_status_changed', 'handle_order_status_changed', 10, 3 ); // Cancel the reservation,on order change from status processing to cancelled
add_action('woocommerce_checkout_init', 'custom_checkout_init_webhook', 10); // Display error message for failed transaction
// add_action( 'woocommerce_order_status_processing', 'handle_successful_payment' ); // TODO: Sending email to the customer
//add_action( 'woocommerce_checkout_order_created', 'custom_checkout_create_order', 20, 1 ); // Adding important data for RateHawk calls if is RateHawk hotel
add_action( 'woocommerce_order_status_completed', 'handle_completed_order', 10, 1 ); // Capture the transaction

add_action ('wp_footer', 'javascript_function');
add_action ('wp_footer', 'hotel_crud');
add_action( 'in_admin_header', 'my_hide_notices_to_all_but_super_admin', 99 );

//get_template_part('demo/landing_function');
//get_template_part('demo/demo_functions');
//get_template_part('quickview_demo/functions');
//get_template_part('user_demo/functions');

//NEW CODE FOR FORGOT PASS REDIRECT START
function custom_password_reset_redirect() {
    wp_safe_redirect( add_query_arg( 'password_reset', 'true', home_url() ) );
    exit;
}
add_action( 'after_password_reset', 'custom_password_reset_redirect' );
//NEW CODE FOR FORGOT PASS REDIRECT END



add_filter( 'woocommerce_currency_symbol', 'custom_currency_symbol', 10, 2 );
function custom_currency_symbol( $currency_symbol, $currency ) {
    if ( $currency == 'MKD' ) {
        $currency_symbol = 'MKD';
    }
    return $currency_symbol;
}


function enqueue_custom_billing_script() {
    // Enqueue jQuery (if not already loaded)
    wp_enqueue_script('jquery');

    // Enqueue your custom script
    wp_enqueue_script(
        'custom-billing-script',
        get_template_directory_uri() . '/js/custom-billing.js', // Adjust the path as needed
        array('jquery'),
        '1.0',
        true // Load in the footer
    );
}
add_action('wp_enqueue_scripts', 'enqueue_custom_billing_script');

function redirect_cart_to_checkout() {
    if (is_cart()) {
        wp_safe_redirect(wc_get_checkout_url());
        exit;
    }
}
//add_action('template_redirect', 'redirect_cart_to_checkout');

/*Read images from cdn if guid is cdn*/
add_filter( 'wp_get_attachment_url', function( $url, $post_id ) {
    $post = get_post( $post_id );

    // Check if the GUID is a full CDN URL
    if ( $post && filter_var( $post->guid, FILTER_VALIDATE_URL ) ) {
        $cdn_host = 'cdn.worldota.net'; // Change to your CDN domain

        if ( strpos( $post->guid, $cdn_host ) !== false ) {
            return $post->guid;
        }
    }

    return $url; // default
}, 10, 2 );



// Booking Notes Field!!

// 1. Add Booking Notes Field
add_action('woocommerce_after_checkout_form', 'add_booking_notes_custom_field');

function add_booking_notes_custom_field($checkout) {
    echo '<div>';

    woocommerce_form_field('booking_notes', array(
        'type'        => 'textarea',
        'class'       => array('form-row-wide'),
        'label'       => __('Booking Notes'),
        'placeholder' => __('Let us know if you have special requests or comments.'),
    ), $checkout->get_value('booking_notes'));

    echo '</div>';
}

// 2. Save Booking Notes Field
add_action('woocommerce_checkout_create_order', 'save_booking_notes_custom_field', 10, 2);

function save_booking_notes_custom_field($order, $data) {
    if (!empty($_POST['booking_notes'])) {
        $order->update_meta_data('_booking_notes', sanitize_textarea_field($_POST['booking_notes']));
    }
}

// 3. Display in Admin Order Page
add_action('woocommerce_admin_order_data_after_billing_address', 'display_booking_notes_admin_order', 10, 1);

function display_booking_notes_admin_order($order) {
    $notes = get_post_meta($order->get_id(), '_booking_notes', true);
    if (!empty($notes)) {
        echo '<strong>' . __('Booking Notes') . ':</strong><br>' . nl2br(esc_html($notes)) . '<br/>';
    }
}

// 4. Add to Order Email
add_filter('woocommerce_email_order_meta_fields', 'add_booking_notes_to_emails', 10, 3);

function add_booking_notes_to_emails($fields, $sent_to_admin, $order) {
    $notes = get_post_meta($order->get_id(), '_booking_notes', true);
    if (!empty($notes)) {
        $fields['booking_notes'] = array(
            'label' => __('Booking Notes'),
            'value' => $notes,
        );
    }
    return $fields;
}
//Hristijan Change
// Send new order email to both admin and customer billing email
//add_filter('woocommerce_email_recipient_new_order', 'send_new_order_email_to_both', 10, 2);
/*function send_new_order_email_to_both($recipient, $order) {
    if (!$order) return $recipient;

    $billing_email = $order->get_billing_email();
    if ($billing_email) {
        // Add customer billing email to existing recipients
        $recipient .= ', ' . $billing_email;
    }

    return $recipient;
}*/

function send_new_order_email_to_both($recipient, $order) {
    //$log_file = WP_CONTENT_DIR . '/order_email_debug.log';
    $current_filter = current_filter(); // Добиј тековен филтер

    if (!$order) {
       /* file_put_contents(
            $log_file,
            date('Y-m-d H:i:s') . " - No order object. Recipient: {$recipient}. Current filter: {$current_filter}\n",
            FILE_APPEND
        );*/
        return $recipient;
    }

    // Проверка дали е New Order емаил
    if ($current_filter === 'woocommerce_email_recipient_new_order') {
        $recipient = get_option('admin_email');
/* if ( $recipient === 'admin@balkanea.com' ) {
    $recipient = 'hristijan999@yahoo.com';
}  */
       /* file_put_contents(
            $log_file,
            date('Y-m-d H:i:s') . " - New Order email. Recipient forced to admin: {$recipient}. Order ID: {$order->get_id()}. Current filter: {$current_filter}\n",
            FILE_APPEND
        );*/
    } else {
        // За сите други емаили, додај и billing email
        $billing_email = $order->get_billing_email();
        if ($billing_email) {
            $recipient .= ', ' . $billing_email;

           /* file_put_contents(
                $log_file,
                date('Y-m-d H:i:s') . " - Other email. Recipient updated with billing: {$recipient}. Order ID: {$order->get_id()}. Current filter: {$current_filter}\n",
                FILE_APPEND
            );*/
        }
    }

    return $recipient;
}

add_filter('woocommerce_email_recipient_new_order', 'send_new_order_email_to_both', 10, 2);
add_filter('woocommerce_email_recipient_customer_processing_order', 'send_new_order_email_to_both', 10, 2);
add_filter('woocommerce_email_recipient_customer_completed_order', 'send_new_order_email_to_both', 10, 2);

//End of hristijan change

// Send cancelled order email to customer's billing email
add_filter('woocommerce_email_recipient_cancelled_order', 'send_cancelled_order_email_to_both', 10, 2);
function send_cancelled_order_email_to_both($recipient, $order) {
    error_log("Order: ");
    error_log(print_r($order, true));
    error_log("Mail to: ");
    error_log(print_r($recipient, true));

    if (!$order) return $recipient;

    $billing_email = $order->get_billing_email();
    error_log("Adding new: ");
    error_log(print_r($billing_email, true));
    if ($billing_email) {
        // Add customer billing email to existing recipients
        $recipient .= ', ' . $billing_email;
    }

    error_log("After add: ");
    error_log(print_r($recipient, true));

    return $recipient;
}


/**/


function enable_page_excerpts() {
    add_post_type_support('page', 'excerpt');
}
add_action('init', 'enable_page_excerpts');



add_action( 'woocommerce_order_status_completed_to_cancelled', 'wc_cancelled_order_notification', 10, 2 );
/**
 * This is the same internal WooCommerce function that handles notifications for cancelled orders.
 */
function wc_cancelled_order_notification( $order_id, $order = null ) {
    //if ( ! $order instanceof WC_Order ) {
        $order = wc_get_order( $order_id );
    //}

    // This triggers the same notification WooCommerce uses.
    do_action( 'woocommerce_order_status_processing_to_cancelled_notification', $order_id, $order );
}


add_action( 'woocommerce_order_status_cancelled', 'send_cancelled_order_email_to_customer', 10, 1 );
function send_cancelled_order_email_to_customer( $order_id ) {
    if ( !$order_id ) return;

    $order = wc_get_order( $order_id );

    // Load WooCommerce mailer
    $mailer = WC()->mailer();

    // Get the cancelled order email object
    $mails = $mailer->get_emails();

    if ( ! empty( $mails['WC_Email_Cancelled_Order'] ) ) {
        $mails['WC_Email_Cancelled_Order']->recipient = $order->get_billing_email(); // Set recipient to customer
        $mails['WC_Email_Cancelled_Order']->trigger( $order_id );
    }
}

add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', 'custom_terms_text' );
function custom_terms_text( $text ) {
    return "I have read and agree to the platform’s terms and conditions";
}

add_action( 'woocommerce_order_status_cancelled', 'send_booking_cancelled_email_to_customer_role_based', 10, 1 );
function send_booking_cancelled_email_to_customer_role_based( $order_id ) {
    if ( ! $order_id ) return;

    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    $current_user_id = get_current_user_id();
    $order_user_id   = (int) $order->get_user_id();

    // Load WooCommerce mailer
    $mailer     = WC()->mailer();
    $recipient  = $order->get_billing_email();

    // Decide subject and heading
    if ( $current_user_id && $current_user_id === $order_user_id ) {
        // Customer cancelled
        $subject = sprintf( 'You cancelled your booking #%s', $order->get_order_number() );
        $heading = 'Your booking has been cancelled';
        $body_message = sprintf(
            'Hi %s,<br><br>You have successfully cancelled your booking <strong>#%s</strong>.<br>If this was a mistake, you can make a new booking anytime.',
            esc_html( $order->get_billing_first_name() ),
            $order->get_order_number()
        );
    } else {
        // Admin cancelled
        $subject = sprintf( 'Your booking #%s was cancelled', $order->get_order_number() );
        $heading = 'Booking cancelled by admin';
        $body_message = sprintf(
            'Hi %s,<br><br>We’re sorry, but your booking <strong>#%s</strong> was cancelled by our team. Please contact us if you need more information.',
            esc_html( $order->get_billing_first_name() ),
            $order->get_order_number()
        );
    }

    // Build styled WooCommerce email
    ob_start();
    wc_get_template(
        'emails/custom-booking-cancelled.php',
        array(
            'order'        => $order,
            'email_heading'=> $heading,
            'body_message' => $body_message,
            'sent_to_admin'=> false,
            'plain_text'   => false,
            'email'        => $mailer
        )
    );
    $message = ob_get_clean();

    // Send email
    $mailer->send( $recipient, $subject, $message );
}

/*todo move this code to plugin balkanea codebase*/
/*add_action('woocommerce_review_order_after_order_total', 'add_free_cancellation_info');

function add_free_cancellation_info() {
    $book_hash = isset($_GET['book_hash']) ? sanitize_text_field($_GET['book_hash']) : '';
    $free_cancellation_date = '';

    // Check if session and booking data exist
    if (!empty($_SESSION['room_data'][$book_hash])) {
        $roomData = json_decode($_SESSION['room_data'][$book_hash]);

        if (!empty($roomData->freeCancellationBefore)) {
            try {
                $datetime = new DateTime($roomData->freeCancellationBefore);
                $datetime->modify('-1 day');
                $free_cancellation_date = \balkanea\includes\providers\BalkaneaHelper::convert_to_wp_date_format($datetime->format('d/m/Y')) ;
            } catch (Exception $e) {
                error_log("Error formatting freeCancellationBefore: " . $e->getMessage());
            }
        }
    }

    // Output the cancellation info row
    echo '<tr class="free-cancellation-row">';
    if (!empty($free_cancellation_date)) {
        echo '<td>' . esc_html__('Free cancellation before', 'woocommerce') . '</td>';
        echo '<td>' . esc_html($free_cancellation_date) . '</td>';
    } else {
        echo '<td colspan="2">' . esc_html__('No free cancellation available', 'woocommerce') . '</td>';
    }
    echo '</tr>';
}
*/

//todo  this must be fired after booking is confirmed from ratehawk and only for teting
//add_action('woocommerce_order_status_changed', 'simple_cancel_booking_hook', 10, 4);

function simple_cancel_booking_hook($order_id, $old_status, $new_status, $order) {
    error_log("================================CANCEL FROM ADMIN================================");
    $cancellable_statuses = ['processing', 'pending', 'on-hold'];

    error_log("Statuses: " . $cancellable_statuses);

    if (in_array($new_status, $cancellable_statuses)) {
        cancel_booking_order($order_id);
    }

    error_log("================================CANCEL FROM END================================");
}

function cancel_booking_order($order_id) {
    error_log("Cancelling order: ". $order_id);
    $order = wc_get_order($order_id);

    if (!$order) {
        error_log("Order not found: {$order_id}");
        return;
    }

    $partner_order_id = get_post_meta($order_id, 'partner_order_id', true);

    if (!$partner_order_id) {
        error_log("partner_order_id not found");
        return;
    }

    $provider = "reathawak";
    $worldota_provider = HotelProvideFactory::create($provider);
    $api_cancellation_result = $worldota_provider->cancel_booking($partner_order_id);

    // Cancel the order
    $order->update_status('cancelled', 'Order cancelled by admin');

    error_log("Order {$order_id} cancelled successfully");
}


add_action( 'woocommerce_email_order_details', 'add_hotel_name_to_email', 5, 4 );
function add_hotel_name_to_email( $order, $sent_to_admin, $plain_text, $email ) {
    if ( ! $order instanceof WC_Order ) return;

    foreach ( $order->get_items() as $item_id => $item ) {
        // Get the hotel ID
        $hotel_id = wc_get_order_item_meta( $item_id, 'room_parent', true );

        if ( $hotel_id ) {
            $hotel_name = get_the_title( $hotel_id );
            echo '<strong>Hotel:</strong> ' . esc_html( $hotel_name ) . '<br/>';
        }
    }
}


add_action( 'woocommerce_order_status_completed_to_cancelled', 'send_notification_to_bank_for_refund' );
function send_notification_to_bank_for_refund($order_id) {
    if ( ! $order_id ) return;

    $order = wc_get_order( $order_id );
    if ( ! $order ) return;

    $mailer     = WC()->mailer();
    $recipient  = "ivuchich@marratech.ai"; //"osporuvanja@nlb.mk";

    $date_paid =  $order->get_date_paid();
    // Convert to Macedonian local time
    $local_date_paid = clone $date_paid;
    $local_date_paid->setTimezone(new DateTimeZone('Europe/Skopje'));

    $subject = 'Baranje za povrat na sredstva od otkazhana rezervacija';
    //$heading = 'Baranje za povrat na sredstva od otkazhana rezervacija';
    $body_message = "<div style='text-align:left; padding:15px; background-color:#ffffff;'>Почитувани НЛБ,<br><br>туку што добивме известување за откажана резервација направена преку порталот Balkanea.com.<br>" .
                            "Ве молиме за рефундирање на средствата од резервацијата за која деталите ви ги приложуваме во продолжение.<br><br>" .
    	"Резервацијата е направена од: <strong>" . $order->get_billing_first_name() . " " . $order->get_billing_last_name() . "</strong><br>" .
    	"Датум/време на уплата: <strong>" . $local_date_paid->format('d.m.Y H:i:s') . "</strong><br>" .
    	"Вкупна сума: <strong>" . $order->total . " " . $order->currency . "</strong><br>" .
    	"ID на банковна трансакција: <strong>" . $order->transaction_id . "</strong><br><br>" .
    	"Ве молиме, да добиеме информација за конечниот статус на мејл: <strong>admin@balkanea.com.</strong><br><br>" .
    	"Благодариме.<br><br>" . "Со почит,<br>тимот на <strong>Balkanea</strong></div>";

    //$body_message = "Test";

    // Build styled WooCommerce email
    ob_start();
    // wc_get_template(
    // 'emails/custom-booking-cancelled.php',
    //     array(
    //         'order'        => $order,
    //     	'email_heading'=> $heading,
    //     	'body_message' => $body_message,
    //     	'sent_to_admin'=> false,
    //     	'plain_text'   => false,
    //     	'email'        => $mailer
    //     )
    // );

    // $message = ob_get_clean();

    // Send email
    $mailer->send( $recipient, $subject, $body_message );
}


//overriding customer e-mail messages:

//styles:

add_action( 'woocommerce_email_header', function() {
    echo '<link href="https://fonts.googleapis.com/css?family=Poppins:400,600&display=swap" rel="stylesheet" type="text/css">';
});

add_filter( 'woocommerce_email_styles', function( $css, $email ) {
    $css .= '
        table[id$="template_container"] * {
          font-family: "Poppins", "Segoe UI", Roboto, Tahoma, Arial, sans-serif; !important;
          color: #00332A !important;
        }
    ';
    return $css;
}, 10, 2 );



add_action( 'woocommerce_email_order_details', 'my_custom_email_order_details_before_room_details', 5, 4 );
function my_custom_email_order_details_before_room_details( $order, $sent_to_admin, $plain_text, $email ) {
    //$meta_data = $order->get_meta_data();
    $hotel_name = $order->get_meta('hotel_name');
    $hotel_address = $order->get_meta('hotel_address');
    echo '<strong>Hotel:</strong> ' . esc_html( $hotel_name ) . '<br/>';
    echo '<strong>Location:</strong> ' . esc_html( $hotel_address ) . '<br/><br/>';
}

add_action( 'woocommerce_email_after_order_table', 'my_custom_email_order_details_after_room_details', 20, 4 );
function my_custom_email_order_details_after_room_details( $order, $sent_to_admin, $plain_text, $email ) {
    //$meta_data = $order->get_meta_data();
    if ($order->get_meta('free_cancellation') !== null && $order->get_meta('free_cancellation') !== '') {
        $free_cancellation = (new DateTime($order->get_meta('free_cancellation')))->format('d.m.Y H:i:s');
        echo '<br /><strong>Free cancellation before:</strong> ' . $free_cancellation . '<br/>';
    }

    //$order_data = $order->get_meta('_order_data');
    //$order_data = json_decode($order_data, true);

    //var_dump($order_data);
    //var_dump($order_data->get_meta('start'));


    $start_date = $order->get_meta('check_in');
    $end_date = $order->get_meta('check_out');
    echo '<strong>Start Date:</strong> ' . $start_date . '<br/>';
    echo '<strong>End Date:</strong> ' . $end_date . '<br/>';

    $adult_number = $order->get_meta('adults');
    $child_number = $order->get_meta('children');
    echo '<strong>Number of Guests:</strong><br/>';
    echo '<strong>Adults:</strong> ' . $adult_number . '<br/>';
    echo '<strong>Children:</strong> ' . $child_number . '<br/>';

    $rooms = $order->get_meta('rooms');
    $nights = $order->get_meta('nights');
    echo '<strong>Rooms:</strong> ' . $rooms . '<br/>';
    echo '<strong>Nights staying:</strong> ' . $nights . '<br/><br/>';

    // foreach ( $meta_data as $meta ) {
    //     echo $meta->key . ' : ' . $meta->value . '<br>';
    // }

    // if ( ! $order instanceof WC_Order ) return;
    // foreach ( $order->get_items() as $item_id => $item ) {
    //     // Get the hotel ID
    //     $hotel_id = wc_get_order_item_meta( $item_id, 'room_parent', true );

    //     if ( $hotel_id ) {
    //         $hotel_name = get_the_title( $hotel_id );
    //         echo '<strong>Hotel:</strong> ' . esc_html( $hotel_name ) . '<br/>';
    //     }
    // }
}

add_action('woocommerce_payment_complete', 'custom_order_status_after_payment', 10, 1);
function custom_order_status_after_payment($order_id) {
    $order = wc_get_order($order_id);
    
    $order->update_status('pending', 'Hotel booking awaiting confirmation.');

}
