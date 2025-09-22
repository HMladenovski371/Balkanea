<?php
/**
 * WooCommerce Booking History Template
 * Modified to use wc_get_orders with proper mapping
 * This is a custom generated file by Nikola
 */

if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

$current_user_id = get_current_user_id();
$statuses = [
    "pending" => [
        "label" => __("Pending payment", "traveler"),
        "color" => "#FF9900",
    ],
    "processing" => [
        "label" => __("Processing", "traveler"),
        "color" => "#0066CC",
    ],
    "on-hold" => [
        "label" => __("On hold", "traveler"),
        "color" => "#FFCC00",
    ],
    "completed" => [
        "label" => __("Completed", "traveler"),
        "color" => "#33CC33",
    ],
    "cancelled" => [
        "label" => __("Cancelled", "traveler"),
        "color" => "#FF3333",
    ],
    "refunded" => [
        "label" => __("Refunded", "traveler"),
        "color" => "#9933FF",
    ],
    "failed" => [
        "label" => __("Failed", "traveler"),
        "color" => "#CC0000",
    ],
    "cancel-request" => [
        "label" => __("Cancel Request", "traveler"),
        "color" => "#FF6633",
    ],
    "draft" => [
        "label" => __("Draft", "traveler"),
        "color" => "#999999",
    ],
    "incomplete" => [
        "label" => __("Incomplete", "traveler"),
        "color" => "#777",
    ],
];

$room_url = 'https://staging.balkanea.com/hotel-room';

// Get orders using wc_get_orders
$args = [
    'customer_id' => $current_user_id,
    'limit' => 15,
    'status' => ['completed', 'processing', 'on-hold', 'pending', 'cancelled', 'refunded', 'failed'],
    'orderby' => 'date',
    'order' => 'DESC',
    'return' => 'objects'
];

$wc_orders = wc_get_orders($args);

// Map WooCommerce orders to the expected booking format
$bookings = [];
foreach ($wc_orders as $order) {
    // Get order items (rooms)
    $items = $order->get_items();
    
    foreach ($items as $item_id => $item) {
        // Get room/product details
        $product = $item->get_product();
        $product_id = $item->get_product_id();
        $room_post = get_post($product_id);
        
        // Create booking object with mapped values
        $booking = new stdClass();
        $booking->wc_order_id = $order->get_id();
        $booking->user_id = $order->get_customer_id();
        $booking->room_id = $product_id;
        $booking->status = $order->get_status();
        $booking->created = $order->get_date_created()->format('Y-m-d H:i:s');
        $booking->order_total = $order->get_total();
        $booking->billing_country = $order->get_billing_country();
        
        // Get room/product details
        if ($room_post) {
            $booking->post_title = $room_post->post_title;
            $booking->post_name = $room_post->post_name;
            $booking->ID = $room_post->ID;
        } else {
            $booking->post_title = $item->get_name();
            $booking->post_name = sanitize_title($item->get_name());
            $booking->ID = $product_id;
        }
        
        // Get free cancellation from order meta
        $booking->free_cancellation = $order->get_meta('free_cancellation');
        
        $bookings[] = $booking;
    }
}

?>

<div class="woocommerce-booking-history">
    <h2><?php echo __('Booking History', 'traveler'); ?></h2>

    <input id='current_user' type='hidden' value=<?php echo $current_user_id ?> />

    <div class="tabs-container">
        <ul class="nav nav-tabs" id="booking-tabs">
            <li class="active"><a href="#tab-all" data-toggle="tab"><?php echo __('All', 'traveler'); ?></a></li>
            <?php foreach ($statuses as $status_key => $status_label) : ?>
                <li><a href="#tab-<?php echo esc_attr($status_key); ?>" data-toggle="tab"><?php echo esc_html($status_label['label']); ?></a></li>
            <?php endforeach; ?>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade in active" id="tab-all">
                <?php if (!empty($bookings)) : ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th><?php echo __('Product', 'traveler'); ?></th>
                                <th><?php echo __('Cost', 'traveler'); ?></th>
                                <th><?php echo __('Status', 'traveler'); ?></th>
                                <th><?php echo __('Date Created', 'traveler'); ?></th>
                                <th><?php echo __('Free Cancellation', 'traveler'); ?></th>
                                <th><?php echo __('Actions', 'traveler'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking) : ?>
                                <tr>
                                    <td><?php echo esc_html($booking->wc_order_id); ?></td>
                                    <td><?php echo esc_html($booking->post_title); ?></td>
                                    <td>
                                        <?php 
                                            // Get currency from the WooCommerce order
                                            $order = wc_get_order($booking->wc_order_id);
                                            $currency = $order ? $order->get_currency() : get_woocommerce_currency();
                                            $total = esc_html($booking->order_total);
                                            echo "{$currency} {$total}"; 
                                        ?>
                                    </td>

                                    <td>
                                        <?php 
                                            $status_key = $booking->status;
                                            $status_data = $statuses[$status_key] ?? null;
                            
                                            if ($status_data) {
                                                echo '<span style="color: ' . esc_attr($status_data['color']) . ';">' . esc_html($status_data['label']) . '</span>';
                                            } else {
                                                echo esc_html(__('Unknown', 'traveler'));
                                            }
                                        ?>
                                    </td>
                                    <td><?php echo esc_html($booking->created); ?></td>
                                    <td>
                                    <?php
                                        // Free cancellation mapping
                                        if (empty($booking->free_cancellation)) {
                                            echo esc_html("N/A");
                                        } else {
                                            $free_cancellation_date = DateTime::createFromFormat('d/m/Y', $booking->free_cancellation);
                                            $now = new DateTime();
                                    
                                            if ($free_cancellation_date && $free_cancellation_date < $now) {
                                                echo esc_html("Free cancellation period is over");
                                            } else {
                                                echo esc_html($booking->free_cancellation);
                                            }
                                        }
                                    ?>
                                    </td>

                                    <td>
                                        <button 
                                            onclick="ViewModal(this)"
                                            class="btn btn-info view-booking-details" 
                                            data-toggle="modal" 
                                            data-target="#bookingDetailsModal"
                                            data-id="<?php echo esc_attr($booking->wc_order_id); ?>"
                                            data-product="<?php echo esc_html($booking->post_title); ?>"
                                            data-cost="<?php echo esc_html(strip_tags(wc_price($booking->order_total))); ?>"
                                            data-status="<?php echo esc_html($statuses[$status_key]['label'] ?? __('Unknown', 'traveler')); ?>"
                                            data-date="<?php echo esc_html($booking->created); ?>"
                                        >
                                            <?php echo __('View', 'traveler'); ?>
                                        </button>
                                        <?php 
                                            // Format both dates to the same format (Y-m-d) for comparison
                                            $show_cancel_button = false;
                                            
                                            if (!empty($booking->free_cancellation)) {
                                                // Parse the free cancellation date
                                                $free_cancellation_date = DateTime::createFromFormat('Y-m-d\TH:i:s', $booking->free_cancellation);
                                                
                                                if ($free_cancellation_date) {
                                                    // Format both dates to Y-m-d for comparison (ignoring time)
                                                    $free_cancellation_formatted = $free_cancellation_date->format('Y-m-d');
                                                    $today_formatted = (new DateTime())->format('Y-m-d');
                                                    
                                                    // Show button if free cancellation date is today or in the future
                                                    $show_cancel_button = ($free_cancellation_formatted > $today_formatted);
                                                }
                                            }
                                            
                                            if ($show_cancel_button): 
                                            ?>
                                                <button 
                                                    onclick="cancelBooking(<?php echo esc_attr($booking->wc_order_id); ?>, true)"
                                                >
                                                    <?php echo __('Cancel', 'traveler'); ?>
                                                </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button id="load-more-button" class="btn btn-primary">
                        <span></span>
                        <?php echo __('Load More', 'traveler'); ?>
                    </button>
                <?php else : ?>
                    <p><?php echo __('No bookings found.', 'traveler'); ?></p>
                <?php endif; ?>
            </div>

            <?php foreach ($statuses as $status_key => $status_label) : ?>
                <div class="tab-pane fade" id="tab-<?php echo esc_attr($status_key); ?>">
                    <?php
                    $filtered_bookings = array_filter($bookings, function ($booking) use ($status_key) {
                        return $booking->status === $status_key;
                    });
                    ?>
                    <?php if (!empty($filtered_bookings)) : ?>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#ID</th>
                                    <th><?php echo __('Product', 'traveler'); ?></th>
                                    <th><?php echo __('Cost', 'traveler'); ?></th>
                                    <th><?php echo __('Status', 'traveler'); ?></th>
                                    <th><?php echo __('Actions', 'traveler'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($filtered_bookings as $booking) : ?>
                                    <tr>
                                        <td><?php echo esc_html($booking->wc_order_id); ?></td>
                                        <td><?php echo esc_html($booking->post_title); ?></td>
                                        <td><?php echo esc_html(strip_tags(wc_price($booking->order_total))); ?></td>
                                        <td>
                                            <span style="color: <?php echo esc_attr($status_label['color']); ?>;">
                                                <?php echo esc_html($status_label['label']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button 
                                                onclick="ViewModal(this)"
                                                class="btn btn-info view-booking-details" 
                                                data-toggle="modal" 
                                                data-target="#bookingDetailsModal"
                                                data-id="<?php echo esc_attr($booking->wc_order_id); ?>"
                                                data-product="<?php echo esc_html($booking->post_title); ?>"
                                                data-cost="<?php echo esc_html(strip_tags(wc_price($booking->order_total))); ?>"
                                                data-status="<?php echo esc_html($status_label['label']); ?>"
                                                data-date="<?php echo esc_html($booking->created); ?>"
                                            >
                                                <?php echo __('View', 'traveler'); ?>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <button id="load-more-button" class="btn btn-primary">
                            <span></span>
                            <?php echo __('Load More', 'traveler'); ?>
                        </button>
                    <?php else : ?>
                        <p><?php echo __('No bookings for this status.', 'traveler'); ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="bookingDetailsModal" tabindex="-1" role="dialog" aria-labelledby="bookingDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingDetailsModalLabel"><?php echo __('Booking Details', 'traveler'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>

<script>
// Define AJAX URL and nonces for JavaScript
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
var booking_nonce = '<?php echo wp_create_nonce('booking_nonce'); ?>';
</script>

<script>
    function ViewModal(button) {
        const current_user = document.querySelector('#current_user').value;
        const order_id = button.getAttribute("data-id");
        const product = button.getAttribute("data-product");
        
        document.getElementById('bookingDetailsModalLabel').innerHTML = 'Booking Details: <strong>' + product + '</strong>';
        
        // Show loading state
        document.querySelector('.modal-body').innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
        
        jQuery.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json', // Important: specify JSON
            data: {
                action: 'load_booking_modal',
                current_user: current_user,
                order_id: order_id,
                security: booking_nonce
            },
            success: function(response) {
                console.log('Response type:', typeof response);
                console.log('Full response:', response);
                
                // Check if response is successful and has data
                if (response && response.success && response.data) {
                    // The HTML content is in response.data
                    document.querySelector('.modal-body').innerHTML = response.data;
                } else if (response && !response.success) {
                    // Handle error response
                    const errorMessage = response.data && response.data.message ? response.data.message : 'Unknown error';
                    document.querySelector('.modal-body').innerHTML = '<div class="alert alert-danger">Error: ' + errorMessage + '</div>';
                } else {
                    // Unexpected response format
                    console.error('Unexpected response format:', response);
                    document.querySelector('.modal-body').innerHTML = '<div class="alert alert-danger">Unexpected response format</div>';
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                document.querySelector('.modal-body').innerHTML = '<div class="alert alert-danger">Request failed: ' + error + '</div>';
            }
        });
    }



    function cancelBooking(orderId, isFree) {
        console.log("Clicked");
        console.log(orderId);
        console.log(isFree);
        const confirmMessage = isFree 
            ? 'Are you sure you want to cancel this booking? This action cannot be undone.' 
            : 'Are you sure you want to cancel this booking? Cancellation fees may apply.';
        
        if (confirm(confirmMessage)) {
            // Find and disable the button with loading state
            const cancelBtn = document.querySelector('[onclick*="cancelBooking(' + orderId + '"]');
            if (cancelBtn) {
                cancelBtn.disabled = true;
                cancelBtn.classList.add('loading');
                cancelBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Cancelling...';
            }
            
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cancel_booking',
                    order_id: orderId,
                    security: booking_nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('Booking cancelled successfully');
                        jQuery('#bookingDetailsModal').modal('hide');
                        location.reload();
                    } else {
                        alert('Error cancelling booking: ' + (response.data.message || 'Unknown error'));
                        // Re-enable button on error
                        if (cancelBtn) {
                            cancelBtn.disabled = false;
                            cancelBtn.classList.remove('loading');
                            cancelBtn.innerHTML = '<i class="fa fa-times"></i> ' + (isFree ? 'Cancel Booking (Free)' : 'Cancel Booking');
                        }
                    }
                },
                error: function(xhr, status, error) {
                    alert('Error processing cancellation request: ' + error);
                    // Re-enable button on error
                    if (cancelBtn) {
                        cancelBtn.disabled = false;
                        cancelBtn.classList.remove('loading');
                        cancelBtn.innerHTML = '<i class="fa fa-times"></i> ' + (isFree ? 'Cancel Booking (Free)' : 'Cancel Booking');
                    }
                }
            });
        }
    }


    
    document.addEventListener("DOMContentLoaded", function() {
        let offset = 16;
        const current_user = document.querySelector('#current_user').value;
    
        document.getElementById('load-more-button').addEventListener('click', function() {
            document.querySelector('#load-more-button span').classList.add('fa', 'fa-spinner', 'fa-spin');
            
            jQuery.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'load_more_bookings',
                    offset: offset,
                    current_user: current_user,
                    security: booking_nonce
                },
                success: function(response) {
                    if (response.success) {
                        document.querySelector("#tab-all > table > tbody").innerHTML += response.data;
                        offset += 15; // Changed to match your LIMIT
                    } else {
                        document.getElementById('load-more-button').style.display = 'none';
                    }
                    
                    document.querySelector('#load-more-button span').classList.remove('fa', 'fa-spinner', 'fa-spin');
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    document.querySelector('#load-more-button span').classList.remove('fa', 'fa-spinner', 'fa-spin');
                }
            });
        });
    });

</script>
