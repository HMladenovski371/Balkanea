<?php
wp_enqueue_script('bootstrap-datepicker.js');
wp_enqueue_script('bootstrap-datepicker-lang.js');
wp_enqueue_script('custom_hotel_room_inbox');

$booking_data = $message_data['booking_data'];

$extra = '';
$room_id = '';
$check_in = '';
$check_out = '';
$adult_number = '';
$child_number = '';
$infant_number = '';
$room_num_search = '';
$controls = '';
$guest_titles = '';
if (empty($room_id)) {
    $room_id = isset($message_data['post_id']) ? $message_data['post_id'] : 0;
    $hotel_id  = get_post_meta( $room_id, 'room_parent', true );
}
if(!empty($booking_data)){
    $booking_data = json_decode($booking_data, true);
	$extra = isset($booking_data['extra_price']) ? $booking_data['extra_price'] : '';
    $room_id = isset($booking_data['room_id']) ? $booking_data['room_id'] : '';
    $check_in = isset($booking_data['check_in']) ? $booking_data['check_in'] : '';
	$check_out = isset($booking_data['check_out']) ? $booking_data['check_out'] : '';
	$adult_number = isset($booking_data['adult_number']) ? $booking_data['adult_number'] : '';
	$child_number = isset($booking_data['child_number']) ? $booking_data['child_number'] : '';
	$infant_number = isset($booking_data['infant_number']) ? $booking_data['infant_number'] : '';
    $room_num_search = isset($booking_data['room_num_search']) ? $booking_data['room_num_search'] : '';
	$controls = isset($booking_data['guest_name']) ? $booking_data['guest_name'] : '';
	$guest_titles = isset($booking_data['guest_title']) ? $booking_data['guest_title'] : '';
}

if (!empty($_REQUEST['check_in'])) {
	$check_in = STInput::request('check_in');
	$check_out = STInput::request('check_out');
	$adult_number = STInput::request('adult_number');
	$child_number = STInput::request('child_number');
	$infant_number = STInput::request('infant_number');
	$room_num_search = STInput::request('room_num_search');
	$extra = STInput::request("extra_price");
	$controls = STInput::request('guest_name');
	$guest_titles = STInput::request('guest_title');
}

$item_id = $post_id;
if (empty($item_id)) {
	$item_id = $room_id;
}
if(empty($room_id)){
    $room_id = $message_data['post_id'];
}
$booking_period = intval(get_post_meta($item_id, 'hotel_booking_period', TRUE));
$date= new DateTime();
if($booking_period){
	if($booking_period==1) $date->modify('+1 day');
	else $date->modify('+'.($booking_period).' days');
}

$bg_thumb = '';
if(has_post_thumbnail($room_id)){
	$bg_thumb = get_the_post_thumbnail_url($room_id, 'full');
}else{
	$bg_thumb = get_template_directory_uri() . '/img/no-image.png';
}

$num_room = intval(get_post_meta($room_id, 'number_room', true));
$st_is_booking_modal = apply_filters('st_is_booking_modal', false);
$st_room_external_booking = get_post_meta($room_id, 'st_room_external_booking', true);
$external = STRoom::get_external_url($room_id);
if(get_post_type($room_id) == 'st_hotel') {?>
    <form id="form-booking-inpage" class="single-room-form form-has-guest-name" method="post">
        <div class="st-inbox-form-book">
            <?php if(!empty($bg_thumb)){ ?>
                <a href="<?php echo get_the_permalink($post_id); ?>">
                    <div class="thumb" style="background-image: url('<?php echo esc_url($bg_thumb); ?>')"></div>
                </a>
            <?php } ?>
            <h3><a href="<?php echo get_the_permalink($post_id); ?>"><?php echo get_the_title($post_id); ?></a></h3>
            <div class="section">
                <div class="package-book-now-button text-center">
                    <a class="btn btn-primary btn_hotel_booking"
                        href="<?php echo get_the_permalink($post_id); ?>"><?php echo __('Book Now', 'traveler'); ?></a>
                </div>
            </div>
        </div>
    </form>
<?php } else { ?>
    <form id="form-booking-inpage" class="single-room-form form-has-guest-name" method="post">
        <div class="st-inbox-form-book">
            <?php if(!empty($bg_thumb)){ ?>
                <a href="<?php echo get_the_permalink($room_id); ?>">
                    <div class="thumb" style="background-image: url('<?php echo esc_url($bg_thumb); ?>')"></div>
                </a>
            <?php } ?>
            <h3><a href="<?php echo get_the_permalink($room_id); ?>"><?php echo get_the_title($room_id); ?></a></h3>
            <div class="section">
                <div class="package-book-now-button">
                    <?php
                    if (!get_option('permalink_structure')) {
                        echo '<input type="hidden" name="st_hotel"  value="' . esc_attr($rental_obj->post_name) . '">';
                    }
                    ?>
                    <input type="hidden" name="action" value="hotel_add_to_cart">
                    <input type="hidden" name="item_id" value="<?php echo esc_html($item_id); ?>">
                    <input type="hidden" name="room_id" value="<?php echo esc_html($room_id); ?>">
                    <div class="div_book" data-date-format="<?php echo TravelHelper::getDateFormatJs(); ?>">
                        <div class="booking-meta">
                            <div class="meta-item">
                                <div class="meta-title"><i class="fa fa-calendar"></i> <?php echo __('Check in', 'traveler') ?></div>
                                <div class="meta-value">
                                    <input
                                            data-post-id="<?php echo esc_attr($room_id); ?>"
                                            placeholder="<?php echo TravelHelper::getDateFormatJs(__("Select date", 'traveler')); ?>"
                                            class="form-control checkin_hotel"
                                            value="<?php echo esc_html($check_in); ?>"
                                            name="check_in"
                                            readonly
                                            type="text">
                                </div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-title"><i class="fa fa-calendar"></i> <?php echo __('Check out', 'traveler') ?></div>
                                <div class="meta-value">
                                    <input
                                            data-post-id="<?php echo esc_attr($room_id); ?>"
                                            placeholder="<?php echo TravelHelper::getDateFormatJs(__("Select date", 'traveler')); ?>"
                                            class="form-control checkout_hotel"
                                            value="<?php echo esc_html($check_out); ?>"
                                            name="check_out"
                                            readonly
                                            type="text">
                                </div>
                            </div>

                            <div class="message_box mt10"></div>
                            <!--End extra price-->
                        </div>
                        <?php echo STTemplate::message(); ?>
                        <div class="div_btn_book_tour">
                            <a class="btn btn-primary btn-st-add-cart" href="<?php echo get_the_permalink($room_id);?>"><?php echo __('Book Now', 'traveler'); ?> </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
<?php }
?>

