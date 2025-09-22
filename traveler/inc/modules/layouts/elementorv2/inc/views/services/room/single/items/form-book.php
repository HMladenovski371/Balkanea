<div class="st-form-book-wrapper relative">
    <div class="form-booking-price">
        <?php
            // Nikola getting price from session start
            $full_price = WC()->session->get('full_price', 0);
            // BAL-666 - START
            $free_cancellation = WC()->session->get('free_cancellation', 0);
            // BAL-666 - END
            
            if ($full_price == 0) {
                // If full price is not set, use sale price and format it.
                $price = TravelHelper::format_money($sale_price, true);
                WC()->session->set('full_price', $sale_price);
            } else {
                // $price = TravelHelper::format_money($full_price, true);
                // Adjust full price based on the currency.
                if (get_woocommerce_currency() === "MKD") {
                    $price = 'MKD ' . round( $full_price * 61.53 );
                } else {
                    $price = '€ ' . round( $full_price, 2 );
                }
                
                // // Set the converted price in the session (optional, if needed elsewhere).
            
                // // Format the price without including the currency symbol.
                // $price = TravelHelper::format_money($converted_price, false);
            }
            // Nikola getting price from session end
        
        if ($price_by_per_person == 'on') :
            echo __('From:', 'traveler');
            echo sprintf('<span class="price">%s</span>', $price); // Display price here - Nikola
            echo '<span class="unit">';
            echo sprintf(_n('/person', '/%d persons', $total_person, 'traveler'), $total_person);
            echo sprintf(_n('/night', '/%d nights', $numberday, 'traveler'), $numberday);
            echo '</span>';
        else:
            echo __('from ', 'traveler');
            echo sprintf('<span class="price">%s</span>', $price); // Display price here - Nikola
            echo '<span class="unit">';
            echo sprintf(_n('/night', '/ %d nights', $numberday, 'traveler'), $numberday);
            echo '</span>';
        endif; ?>
    </div>
    
    <?php
    /* 
     * Free cancellation notice
     * BAL-666 - START
    */
    if (!empty($free_cancellation)) {
        // Calculate the free cancellation date (assuming it's stored as days before arrival)
        // $cancellation_date = date('M j, Y', strtotime('+' . intval($free_cancellation) . ' days'));
        ?>
        <div class="free-cancellation-notice">
            <div class="cancellation-badge">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path>
                    <path d="m9 12 2 2 4-4"></path>
                </svg>
                <span><?php echo esc_html__('Free Cancellation', 'traveler'); ?></span>
            </div>
            <div class="cancellation-info">
                <?php echo esc_html__('Before', 'traveler'); ?> <strong><?php echo esc_html($free_cancellation); ?></strong>
            </div>
        </div>
        <!-- BAL-666 - END -->
        <style>
            .free-cancellation-notice {
                background-color: #fbfbfb;
                border-radius: 8px;
                padding: 10px 15px;
                margin: 10px 0 15px;
                display: flex;
                flex-direction: column;
                gap: 5px;
                padding-left: 9%;
            }
            .cancellation-badge {
                display: flex;
                align-items: center;
                gap: 6px;
                color: #28a745;
                font-weight: 600;
                font-size: 14px;
            }
            .cancellation-badge svg {
                stroke: #28a745;
            }
            .cancellation-info {
                font-size: 13px;
                color: #5a6268;
            }
            .cancellation-info strong {
                color: #212529;
            }
        </style>
    <?php } ?>
    
    <?php if($booking_type == 'instant_enquire') { ?>
    <div class="st-wrapper-form-booking">
        <nav>
            <ul class="nav nav-tabs d-flex align-items-center justify-content-between nav-fill-st" id="nav-tab"
                role="tablist">
                <li><a class="active text-center" id="nav-book-tab" data-bs-toggle="tab" data-bs-target="#nav-book"
                       role="tab" aria-controls="nav-home"
                       aria-selected="true"><?php echo esc_html__('Book', 'traveler') ?></a>
                </li>
                <li><a class="text-center" id="nav-inquirement-tab" data-bs-toggle="tab"
                       data-bs-target="#nav-inquirement"
                       role="tab" aria-controls="nav-profile"
                       aria-selected="false"><?php echo esc_html__('Inquiry', 'traveler') ?></a>
                </li>
            </ul>
        </nav>
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-book" role="tabpanel"
                 aria-labelledby="nav-book-tab">
                <?php
                echo stt_elementorv2()->loadView('services/room/single/items/form-book-instant', [
                    'price_by_per_person' => $price_by_per_person,
                    'sale_price' => $sale_price,
                    'numberday' => $numberday,
                    'hotel_id' => $hotel_id,
                    'room_id' => $room_id,
                    'room_external' => $room_external,
                    'room_external_link' => $room_external_link,
                ]);
                ?>
            </div>

            <div class="tab-pane fade " id="nav-inquirement" role="tabpanel"
                 aria-labelledby="nav-inquirement-tab">
                <div class="inquiry-v2">
                    <?php echo st()->load_template('email/email_single_service'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php } elseif($booking_type == 'enquire') { ?>
        <div class="inquiry-v2">
            <form id="form-booking-inpage" autocomplete="off" method="post" action="#booking-request" class="form single-room-form hotel-room-booking-form">
                <input type="hidden" name="action" value="hotel_add_to_cart">
                <input type="hidden" name="item_id" value="<?php echo get_the_ID(); ?>">
                <input style="display:none;" type="submit" class="btn btn-default btn-send-message" data-id="<?php echo get_the_ID();?>" name="st_send_message" value="<?php echo __('Send message', 'traveler');?>">
            </form>
            <?php echo st()->load_template('email/email_single_service'); ?>
        </div>
    <?php } else {

        echo stt_elementorv2()->loadView('services/room/single/items/form-book-instant', [
            'price_by_per_person' => $price_by_per_person,
            'sale_price' => $sale_price,
            'numberday' => $numberday,
            'hotel_id' => $hotel_id,
            'room_id' => $room_id,
            'room_external' => $room_external,
            'room_external_link' => $room_external_link,
        ]);

    } ?>
</div>
