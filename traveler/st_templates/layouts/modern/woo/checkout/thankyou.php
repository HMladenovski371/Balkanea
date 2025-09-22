<?php
/**
 * Modern Thankyou page - Balkanea Custom
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     8.1.0
 */
ini_set('display_errors', 0);
ini_set('log_errors', 1);
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$order_id = isset($order) ? $order->get_id() : 0;
if ($order_id == 0) {
    exit();
}

// Get order details
$order_status = $order->get_status();
global $wpdb;
$payment_status = 'pending';
$free_cancellation = null;

// Get room/hotel details from order
$order_items = $order->get_items();
$hotel_name = '';
$room_type = '';
foreach ($order_items as $item) {
    $hotel_name = $item->get_name();
    // Get room type from meta if available
    $room_meta = $item->get_meta('room_type');
    if ($room_meta) {
        $room_type = $room_meta;
    }
    break; // Get first item
}

if ($order->has_status('processing')) {
    $nonce = wp_create_nonce('order_booking_form');
    ?>
    <div class="balkanea-thankyou-wrapper">
        <div class="loading-overlay" id="loading-overlay">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <h3>Processing your booking...</h3>
                <p>Please wait while we confirm your reservation</p>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Make AJAX call to process the order
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'order_booking_finish',
                    data: <?php echo $order_id; ?>,
                    security: '<?php echo $nonce; ?>'
                },
                success: function(response) {
                    console.log('AJAX Response:', response);
                    
                    // Add fade-out class with !important
                    $('#loading-overlay').addClass('fade-out-important');
                    
                    // After transition completes, hide completely
                    setTimeout(function() {
                        $('#loading-overlay').addClass('hide-important');
                    }, 500);
                    
                    var paymentStatus = 'failed';
                    var freeCancel = null;
                    if (response.success) {
                        paymentStatus = 'paid';
                        if (response.data && response.data.free_cancellation) {
                            freeCancel = response.data.free_cancellation;
                        }
                        console.log('Order processed successfully');
                    } else {
                        paymentStatus = 'not_paid';
                        console.log('Order processing failed:', response.data ? response.data.message : 'Unknown error');
                    }
                    updatePaymentStatus(paymentStatus, freeCancel);
                },
                error: function(xhr, status, error) {
                    console.log('AJAX error:', error);
                    $('#loading-overlay').fadeOut(500);
                    updatePaymentStatus('failed', null);
                }
            });
            
            function updatePaymentStatus(status, freeCancellation) {
                var paymentMessages = {
                    paid: "<?php echo esc_js(__('Booking Confirmed Successfully', 'traveler')); ?>",
                    not_paid: "<?php echo esc_js(__('Booking Processing Failed', 'traveler')); ?>",
                    failed: "<?php echo esc_js(__('Booking Processing Failed', 'traveler')); ?>"
                };
                
                var statusClass = status === 'paid' ? 'success' : 'error';
                var statusIcon = status === 'paid' ? '✓' : '✗';
                
                $('#payment-status').removeClass('success error').addClass(statusClass);
                $('#status-icon').text(statusIcon);
                $('#status-message').text(paymentMessages[status]);
                
                if (status === 'paid') {
                    $('#success-details').show();
                    if (freeCancellation) {
                        $('#free-cancellation-date').text(freeCancellation);
                        $('#free-cancellation-info').show();
                    }
                } else {
                    $('#error-details').show();
                }
                
                $('#main-content').fadeIn(500);
            }
        });
        </script>
        
        <div id="main-content" style="display: none;">
    <?php
} else {
    echo '<div class="balkanea-thankyou-wrapper"><div id="main-content">';
}

if ($order) : ?>
    <?php if ($order->has_status('failed')) : ?>
        <div class="booking-container">
            <div class="booking-header error">
                <div class="status-badge">
                    <span class="status-icon">✗</span>
                    <h1>Payment Failed</h1>
                </div>
                <p class="subtitle">Unfortunately, your booking could not be processed</p>
            </div>
            
            <div class="booking-details">
                <div class="error-message">
                    <h3>What happened?</h3>
                    <p>The payment was declined by your bank or payment provider. This can happen for various security reasons.</p>
                    
                    <div class="action-buttons">
                        <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="btn btn-primary">Try Payment Again</a>
                        <?php if (is_user_logged_in()) : ?>
                            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="btn btn-secondary">My Account</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <?php
        $customer_name = $order->get_billing_first_name();
        if (!$customer_name) $customer_name = $order->get_billing_email();
        ?>
        
        <div class="booking-container">
            <div class="booking-header" id="payment-status">
                <div class="status-badge">
                    <span class="status-icon" id="status-icon">
                        <?php echo !$order->has_status('processing') ? '✓' : ''; ?>
                    </span>
                    <h1 id="status-message">
                        <?php echo !$order->has_status('processing') ? 'Booking Confirmed Successfully' : ''; ?>
                    </h1>
                </div>
                <p class="subtitle">Hello <?php echo esc_html($customer_name); ?>, your reservation details are below</p>
            </div>
            
            <div id="success-details" <?php echo $order->has_status('processing') ? 'style="display:none;"' : ''; ?>>
                <!-- Booking Summary -->
                <div class="booking-summary">
                    <div class="summary-row">
                        <div class="summary-item">
                            <h3>Account Number</h3>
                            <p class="account-number">240010118293905</p>
                        </div>
                    </div>
                    
                    <div class="summary-row">
                        <div class="summary-item">
                            <h3>Accommodation</h3>
                            <p class="hotel-name"><?php echo esc_html($hotel_name); ?></p>
                            <?php if ($room_type): ?>
                                <p class="room-type"><?php echo esc_html($room_type); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="summary-item">
                            <h3>Total Amount Paid</h3>
                            <p class="total-amount"><?php echo $order->get_formatted_order_total(); ?></p>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Details -->
                <div class="payment-details">
                    <h3>Payment Information</h3>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="label">Order Number:</span>
                            <span class="value">#<?php echo $order->get_order_number(); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Order Date:</span>
                            <span class="value"><?php echo $order->get_date_created()->format('Y/m/d'); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Payment Method:</span>
                            <span class="value"><?php echo $order->get_payment_method_title(); ?></span>
                        </div>
                        <div class="detail-item">
                            <span class="label">Email Confirmation:</span>
                            <span class="value"><?php echo $order->get_billing_email(); ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Free Cancellation -->
                <div id="free-cancellation-info" style="display: none;">
                    <div class="cancellation-policy">
                        <h3>Cancellation Policy</h3>
                        <p>Free cancellation until <strong id="free-cancellation-date"></strong></p>
                        <p class="policy-note">Cancel your booking free of charge before the specified date. After this date, cancellation fees may apply.</p>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="action-section">
                    <div class="action-buttons">
                        <a href="<?php echo home_url(); ?>" class="btn btn-primary">Book Another Trip</a>
                        <a href="https://staging.balkanea.com/function-user-settings/?sc=booking-history" class="btn btn-secondary">View Booking</a>
                        <button onclick="window.print()" class="btn btn-secondary">Print Confirmation</button>
                    </div>
                </div>
                
                <!-- Contact Info -->
                <div class="contact-section">
                    <h3>Need Help?</h3>
                    <p>If you have any questions about your booking, please contact our support team.</p>
                    <div class="contact-methods">
                        <div class="contact-item">
                            <strong>Email:</strong> support@balkanea.com
                        </div>
                        <div class="contact-item">
                            <strong>Phone:</strong> +389 2 XXX XXXX
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="error-details" style="display: none;">
                <div class="error-message">
                    <h3>What can you do?</h3>
                    <ul>
                        <li>Check your card details and try again</li>
                        <li>Contact your bank to ensure international payments are enabled</li>
                        <li>Try a different payment method</li>
                        <li>Contact our support team for assistance</li>
                    </ul>
                    
                    <div class="action-buttons">
                        <a href="<?php echo esc_url($order->get_checkout_payment_url()); ?>" class="btn btn-primary">Try Again</a>
                        <a href="mailto:support@balkanea.com" class="btn btn-secondary">Contact Support</a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id()); ?>
    <?php do_action('woocommerce_thankyou', $order->get_id()); ?>
<?php else : ?>
    <div class="booking-container">
        <div class="booking-header success">
            <div class="status-badge">
                <span class="status-icon">✓</span>
                <h1>Thank You</h1>
            </div>
            <p class="subtitle">Your order has been received successfully</p>
        </div>
    </div>
<?php endif; ?>

        </div> <!-- Close main-content -->
    </div> <!-- Close balkanea-thankyou-wrapper -->

<style>
/* Hide page title */
.page-title { display: none; }

/* Main wrapper */
.balkanea-thankyou-wrapper {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    min-height: 100vh;
    padding: 20px;
    margin: 0;
    width: 100%;
}

/* Loading overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.95);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.loading-content {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    width: 90%;
}

.loading-spinner {
    width: 48px;
    height: 48px;
    border: 3px solid #e2e8f0;
    border-top: 3px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

.loading-content h3 {
    margin: 0 0 8px;
    color: #1e293b;
    font-weight: 600;
}

.loading-content p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Main container */
.booking-container {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    overflow: hidden;
}

/* Header styles */
.booking-header {
    text-align: center;
    padding: 48px 32px 32px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    position: relative;
}

.booking-header.success {
    background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
}

.booking-header.error {
    background: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
}

.status-badge {
    display: inline-block;
}

.status-icon {
    display: inline-block;
    width: 64px;
    height: 64px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    line-height: 64px;
    font-size: 32px;
    font-weight: bold;
    margin-bottom: 16px;
}

.booking-header h1 {
    margin: 0 0 8px;
    font-size: 28px;
    font-weight: 700;
    letter-spacing: -0.02em;
}

.subtitle {
    margin: 0;
    font-size: 16px;
    opacity: 0.9;
    font-weight: 400;
}

/* Booking summary */
.booking-summary {
    padding: 32px;
    border-bottom: 1px solid #e2e8f0;
}

.summary-row {
    display: flex;
    gap: 32px;
    margin-bottom: 24px;
}

.summary-row:last-child {
    margin-bottom: 0;
}

.summary-item {
    flex: 1;
}

.summary-item h3 {
    margin: 0 0 8px;
    font-size: 14px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.account-number {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    font-family: 'Monaco', 'Menlo', monospace;
    letter-spacing: 0.05em;
    margin: 0;
}

.hotel-name {
    font-size: 20px;
    font-weight: 600;
    color: #1e293b;
    margin: 0 0 4px;
}

.room-type {
    font-size: 16px;
    color: #64748b;
    margin: 0;
}

.total-amount {
    font-size: 28px;
    font-weight: 700;
    color: #059669;
    margin: 0;
}

/* Payment details */
.payment-details {
    padding: 32px;
    border-bottom: 1px solid #e2e8f0;
}

.payment-details h3 {
    margin: 0 0 24px;
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
}

.detail-item:last-child {
    border-bottom: none;
}

.label {
    font-weight: 500;
    color: #64748b;
}

.value {
    font-weight: 600;
    color: #1e293b;
}

/* Cancellation policy */
.cancellation-policy {
    padding: 32px;
    background: #f0f9ff;
    border-left: 4px solid #3b82f6;
    border-bottom: 1px solid #e2e8f0;
}

.cancellation-policy h3 {
    margin: 0 0 12px;
    font-size: 18px;
    font-weight: 600;
    color: #1e40af;
}

.cancellation-policy p {
    margin: 0 0 8px;
    color: #1e293b;
}

.policy-note {
    font-size: 14px;
    color: #64748b;
}

/* Action section */
.action-section {
    padding: 32px;
    border-bottom: 1px solid #e2e8f0;
}

.action-buttons {
    display: flex;
    gap: 16px;
    flex-wrap: wrap;
    justify-content: center;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    text-align: center;
    transition: all 0.2s;
    border: none;
    cursor: pointer;
    min-width: 140px;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
}

.btn-secondary:hover {
    background: #e2e8f0;
    transform: translateY(-1px);
}

/* Contact section */
.contact-section {
    padding: 32px;
    text-align: center;
    background: #f8fafc;
}

.contact-section h3 {
    margin: 0 0 12px;
    font-size: 18px;
    font-weight: 600;
    color: #1e293b;
}

.contact-section p {
    margin: 0 0 20px;
    color: #64748b;
}

.contact-methods {
    display: flex;
    gap: 32px;
    justify-content: center;
    flex-wrap: wrap;
}

.contact-item {
    color: #475569;
    font-size: 14px;
}

.contact-item strong {
    color: #1e293b;
}

/* Error styles */
.error-message {
    padding: 32px;
    text-align: center;
}

.error-message h3 {
    margin: 0 0 16px;
    color: #1e293b;
    font-size: 18px;
    font-weight: 600;
}

.error-message ul {
    text-align: left;
    max-width: 400px;
    margin: 0 auto 24px;
    padding-left: 20px;
}

.error-message li {
    margin-bottom: 8px;
    color: #64748b;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .balkanea-thankyou-wrapper {
        padding: 12px;
    }
    
    .booking-header {
        padding: 32px 20px 24px;
    }
    
    .booking-header h1 {
        font-size: 24px;
    }
    
    .status-icon {
        width: 48px;
        height: 48px;
        line-height: 48px;
        font-size: 24px;
    }
    
    .summary-row {
        flex-direction: column;
        gap: 20px;
    }
    
    .account-number {
        font-size: 18px;
    }
    
    .total-amount {
        font-size: 24px;
    }
    
    .detail-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-methods {
        flex-direction: column;
        gap: 12px;
    }
    
    .action-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 280px;
    }
    
    .booking-summary, .payment-details, .cancellation-policy, .action-section, .contact-section {
        padding: 24px 20px;
    }
}

@media (max-width: 480px) {
    .loading-content {
        padding: 24px;
    }
    
    .booking-header h1 {
        font-size: 20px;
    }
    
    .subtitle {
        font-size: 14px;
    }
}

.fade-out-important {
    opacity: 0 !important;
    transition: opacity 500ms ease-in-out !important;
    pointer-events: none !important;
}

.hide-important {
    display: none !important;
}

</style>
