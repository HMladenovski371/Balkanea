<?php
$format=TravelHelper::getDateFormat();
$div_id = "st_cart_item".md5(json_encode($st_booking_data['cart_item_key']));
$data = $st_booking_data;
$item_id = $st_booking_data['st_booking_id'];
$data_price = $st_booking_data['data_price'];
if(!empty($data_price['adult_price']))
    $adult_price = ( (float) $data_price['adult_price'] > 0 ) ? (float) $data_price['adult_price'] : 0;
if(!empty($data_price['child_price']))
    $child_price = ( (float) $data_price['child_price'] > 0 ) ? (float) $data_price['child_price'] : 0;
if(!empty($data_price['infant_price']))
    $infant_price = ( (float) $data_price['infant_price'] > 0 ) ? (float) $data_price['infant_price'] : 0;

$tour_price_type = get_post_meta($item_id, 'tour_price_by', true);
?>
<?php if($tour_price_type != 'fixed_depart'){ ?>
<?php if(isset($data['type_tour'])): ?>
<p class="booking-item-description">
    <span><?php echo __('Type tour', 'traveler'); ?>: </span>
    <?php
    if($data['type_tour'] == 'daily_tour'){
        echo __('Daily Tour', 'traveler');
    }elseif($data['type_tour'] == 'specific_date'){
        echo __('Special Date', 'traveler');
    }
    ?>
</p>
<?php endif; ?>

<?php if(isset($data['type_tour']) && $data['type_tour'] == 'daily_tour'): ?>
<p class="booking-item-description"><span><?php echo __('Date', 'traveler'); ?>: </span><?php echo esc_html(date_i18n( $format , strtotime( $data['check_in'] ) ) . ($data['starttime'] != '' ? ' - ' . $data['starttime'] : '')); ?></p>
<p class="booking-item-description"><span><?php echo __('Duration', 'traveler'); ?>: </span><?php echo esc_html($data['duration']); ?></p>
<?php endif; ?>

<?php if(isset($data['type_tour']) && $data['type_tour'] == 'specific_date'): ?>
<p class="booking-item-description"><span><?php echo __('Date', 'traveler'); ?>: </span><?php echo esc_html(date_i18n( $format , strtotime( $data['check_in'] ) ) . ($data['starttime'] != '' ? ' - ' . $data['starttime'] : '')); ?></p>
<?php endif; ?>
<?php }else{ ?>
    <p><b><?php echo __('Fixed Departure', 'traveler'); ?></b></p>
    <p class="booking-item-description"><span><?php echo __('Date', 'traveler'); ?>: </span><?php echo TourHelper::getDayFromNumber(date_i18n( 'N' , strtotime( $data['check_in'] ) )) . ' ' . date_i18n( $format , strtotime( $data['check_in'] ) )?></p>
<?php } ?>

<?php if(empty($data_price['adult_price']) && empty($data_price['child_price']) && empty($data_price['infant_price']) && !empty($data['base_price'])): ?>
    <p class="booking-item-description"><span><?php echo __('Base price', 'traveler'); ?>: </span><?php echo TravelHelper::format_money($data['base_price']); ?></p>
<?php endif; ?>

<?php
	$class          = '';
	$class_collapse = '';
	$id_collapse    = '';
	if ( apply_filters( 'st_woo_cart_is_collapse', false ) ) {
		$class          = 'collapse';
		$class_collapse = 'collapseBookingDetail';
		$id_collapse    = 'collapse_' . md5( json_encode( $st_booking_data['cart_item_key'] ) );
	}
?>

<div id="<?php echo esc_attr( $div_id ); ?>" >
	<p class="accordion-button collapsed <?= esc_attr( $class_collapse ) ?>"
		data-bs-toggle="collapse"
		data-bs-target="#<?= esc_attr( $id_collapse ) ?>"
		aria-expanded="true"
		aria-controls="<?= esc_attr( $id_collapse ) ?>"
	>
		<a data-toggle="collapse" href="#<?= esc_attr( $id_collapse ) ?>" aria-expanded="true">
			<?php echo __( 'Booking Details', 'traveler' ); ?>
		</a>
	</p>
	<div id="<?= esc_attr( $id_collapse ) ?>"
		class="accordion-collapse <?= esc_attr( $class ) ?>"
	>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<div class="booking-item-description">
				<p class="booking-item-description">
					<?php if (!empty($data['adult_number'])) :?>
						<b><?php echo __('Adult', 'traveler'); ?>: </b><?php echo esc_html($data['adult_number']); ?>
						<?php if(!empty($data_price['adult_price'])): ?>
						x
						<?php
							echo TravelHelper::format_money($data['adult_price']);
							endif;
						?>
						<br>
					<?php endif ; ?>
					<?php if (!empty($data['child_number'])) :?>
						<b><?php echo __('Child', 'traveler'); ?>: </b><?php echo esc_html($data['child_number']); ?>
						<?php if(!empty($data_price['child_price'])): ?>
						x
						<?php
							echo TravelHelper::format_money($data['child_price']);
							endif
						?>
						<br>
					<?php endif ; ?>
					<?php if (!empty($data['infant_number'])) :?>
						<b><?php echo __('Infant', 'traveler'); ?>: </b><?php echo esc_html($data['infant_number']); ?>
						<?php if(!empty($data_price['infant_price'])): ?>
						x
						<?php
							echo TravelHelper::format_money( $data['infant_price'] );
							endif;
						?>
						<br>
					<?php endif ; ?>
				</p>
			</div>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php
				$discount = $st_booking_data['discount_rate'];
				$tour_price_by = get_post_meta($item_id, 'tour_price_by', true);
				if (!empty($discount)){ ?>
					<b class='booking-cart-item-title'>
						<?php if ( $tour_price_by === 'person' ) : ?>
							<?php echo __('Discount/Person:', 'traveler'); ?>
						<?php else: ?>
							<?php echo __('Discount:', 'traveler'); ?>
						<?php endif; ?>
					</b>
					<?php
					$discount_type = get_post_meta( $st_booking_data['st_booking_id'], 'discount_type', true );
					if($discount_type == 'amount'){
						echo esc_attr(TravelHelper::format_money($discount));
					}else{
						echo esc_attr($discount) . '%';
					}
					?>
				<?php }
			?>
		</div>

		<div class="cart_item_group" style="margin-bottom: 10px">
			<?php
				$total_bulk_discount = !empty($st_booking_data['data_price']['total_bulk_discount']) ? floatval($st_booking_data['data_price']['total_bulk_discount']): 0;
				if($total_bulk_discount > 0){ ?>
					<b class='booking-cart-item-title'><?php echo __('Bulk Discount', 'traveler'); ?>: </b>
					<?php echo TravelHelper::format_money($total_bulk_discount); ?>
				<?php }
			?>
		</div>

		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php  if ( get_option( 'woocommerce_tax_total_display' ) == 'itemized' ) {
				$wp_cart = WC()->cart->cart_contents;
				$item = $wp_cart[$st_booking_data['cart_item_key']];
				$tax = $item['line_tax'];
				if (!empty($tax)) { ?>
					<b class='booking-cart-item-title'><?php echo __( "Tax", 'traveler'); ?>: </b>
					<?php echo TravelHelper::format_money($tax);?>
				<?php }
			}else{$tax = 0;}
			?>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php

			if(!empty($st_booking_data['extras']) and $st_booking_data['extra_price']):
				$extras = $st_booking_data['extras'];
				if(isset($extras['title']) && is_array($extras['title']) && count($extras['title'])): ?>
					<b class='booking-cart-item-title'><?php _e("Extra prices",'traveler') ?></b>
					<div class="booking-item-payment-price-amount">
						<?php foreach($extras['title'] as $key => $title):
							$price_item = floatval($extras['price'][$key]);
							if($price_item <= 0) $price_item = 0;
							$number_item = intval($extras['value'][$key]);
							if($number_item <= 0) $number_item = 0;
							?><?php if($number_item){ ?>
							<span style="padding-left: 10px ">
								<?php echo esc_html($title) . ' (' . TravelHelper::format_money($price_item) . ') x ' . esc_attr($number_item) . ' ' . __('Item(s)', 'traveler'); ?>
							</span> <br />
						<?php };?>
						<?php endforeach;?>
					</div>
				<?php  endif; ?>
			<?php endif; ?>
		</div>
		<!-- Tour Package -->
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php
			if(!empty($st_booking_data['package_hotel']) and $st_booking_data['package_hotel_price']):
				$hotel_data = $st_booking_data['package_hotel'];
				if(is_array($hotel_data) && count($hotel_data)): ?>
					<b class='booking-cart-item-title'><?php _e("Selected Hotels",'traveler'); ?></b>
					<div class="booking-item-payment-price-amount">
						<?php foreach($hotel_data as $key => $val):
							?>
							<span style="padding-left: 10px ">
								<?php echo esc_attr( $val->hotel_name ) . ': ' . esc_html( $val->qty ) . ' x <b>' . TravelHelper::format_money( $val->hotel_price ) . '</b>'; ?>
							</span> <br />
						<?php endforeach;?>
					</div>
				<?php  endif; ?>
			<?php endif; ?>
		</div>

		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php
			if(!empty($st_booking_data['package_activity']) and $st_booking_data['package_activity_price']):
				$activity_data = $st_booking_data['package_activity'];
				if(is_array($activity_data) && count($activity_data)): ?>
					<b class='booking-cart-item-title'><?php _e("Selected Activities",'traveler'); ?></b>
					<div class="booking-item-payment-price-amount">
						<?php foreach($activity_data as $key => $val):
							?>
							<span style="padding-left: 10px ">
								<?php echo esc_attr( $val->activity_name ) . ': ' . esc_html( $val->qty ) . ' x <b>' . TravelHelper::format_money( $val->activity_price ) . '</b>'; ?>
							</span> <br />
						<?php endforeach;?>
					</div>
				<?php  endif; ?>
			<?php endif; ?>
		</div>

		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php
			if(!empty($st_booking_data['package_car']) and $st_booking_data['package_car_price']):
				$car_data = $st_booking_data['package_car'];
				if(is_array($car_data) && count($car_data)): ?>
					<b class='booking-cart-item-title'><?php _e("Selected Cars",'traveler'); ?></b>
					<div class="booking-item-payment-price-amount">
						<?php foreach($car_data as $key => $val):
							?>
							<span style="padding-left: 10px ">
								<?php echo esc_attr($val->car_name).": ".esc_attr($val->car_quantity).' x <b>'.TravelHelper::format_money($val->car_price) . '</b>'; ?>
							</span> <br />
						<?php endforeach;?>
					</div>
				<?php  endif; ?>
			<?php endif; ?>
		</div>

		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php
			if(!empty($st_booking_data['package_flight']) and $st_booking_data['package_flight_price']):
				$flight_data = $st_booking_data['package_flight'];

				if(is_array($flight_data) && count($flight_data)): ?>
					<b class='booking-cart-item-title'><?php _e("Selected Flight",'traveler'); ?></b>
					<div class="booking-item-payment-price-amount">
						<?php foreach($flight_data as $key => $val):
							$name_flight_package = $val->flight_origin . ' <i class="fa fa-long-arrow-right"></i> ' . $val->flight_destination;
							$price_flight_package = '';
							if($val->flight_price_type == 'business'){
								$price_flight_package = TravelHelper::format_money($val->flight_price_business);
							}else{
								$price_flight_package = TravelHelper::format_money($val->flight_price_economy);
							}
							?>
							<span style="padding-left: 10px ">
								<?php echo esc_html($name_flight_package).": ".' x <b>'.esc_html($price_flight_package) . '</b>'; ?>
							</span> <br />
						<?php endforeach;?>
					</div>
				<?php  endif; ?>
			<?php endif; ?>
		</div>
		<!-- End Tour Package -->
	</div>
</div>
