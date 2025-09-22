<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 02/06/2015
 * Time: 3:32 CH
 */
$format=TravelHelper::getDateFormat();
$div_id = "st_cart_item".md5(json_encode($st_booking_data['cart_item_key']));
$room_id = $st_booking_data['room_id'] ? $st_booking_data['room_id'] : 0;
$price_by_per_person = get_post_meta( $room_id, 'price_by_per_person', true );

$check_in = $st_booking_data['check_in'];
$check_out = $st_booking_data['check_out'];
$date_diff = STDate::dateDiff($check_in,$check_out);
$number_room = intval($st_booking_data['room_num_search']);
$adult_number = intval($st_booking_data['adult_number']);
$child_number = intval($st_booking_data['child_number']);
if (class_exists('WC_Product')) {
    $origin_price = WC()->session->get('full_price', 0); // Nikola display dynamic price WC session START 
    // $origin_price = STPrice::getRoomPriceOnlyCustomPrice($room_id, strtotime($check_in), strtotime($check_out), $number_room, $adult_number, $child_number ); // Old code
}


?>

<p class="booking-item-description">
    <?php echo __('Date:','traveler');?>
	<?php  echo date_i18n($format,strtotime($st_booking_data['check_in'])) ?>
	-
	<?php echo date_i18n($format,strtotime($st_booking_data['check_out'])) ?>
	</br>
	<?php echo __('Number of Night: ','traveler');?><?php echo esc_html($date_diff) ?>
</p>

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
			<?php $room_link = get_post_permalink($st_booking_data['room_id']) ; ?>
			<?php echo esc_html__('Room', 'traveler'); ?>: <a style="color: inherit" href="<?php echo esc_attr($room_link) ; ?>"><b class='booking-cart-item-title'><?php echo get_the_title($st_booking_data['room_id']); ?></b></a>
			<ul class="booking-item-description">
				<li><?php echo __('Adult:','traveler');?> <?php echo esc_html($st_booking_data['adult_number']); ?></li>
				<li><?php echo __('Children:','traveler');?> <?php echo esc_html($st_booking_data['child_number']); ?></li>
				<?php
				if ( $price_by_per_person == 'on' ) : ?>
					<li><?php echo __('Number of rooms:','traveler');?> <?php echo ($st_booking_data['room_num_search']); ?></li>
					<li><?php echo __('Adult Price:','traveler');?> <?php echo TravelHelper::format_money(isset($st_booking_data['adult_price']) ? $st_booking_data['adult_price'] : 0) ; ?></li>
					<li><?php echo __('Child Price:','traveler');?> <?php echo TravelHelper::format_money(isset($st_booking_data['child_price']) ? $st_booking_data['child_price'] : 0) ; ?></li>
					<?php
				else: ?>
					<li>
						<?php echo __('Number of rooms:','traveler');?> <?php echo ($st_booking_data['room_num_search']); ?>
					</li>
					<?php
				endif; ?>
			</ul>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<b class='booking-cart-item-title'><?php echo __( "Price Detail", 'traveler'); ?>: </b>
			<span class="value">
                <?php echo TravelHelper::format_money($origin_price);?> <!-- Nikola display dynamic price WC session START-->
			</span>
			<span class="label">
				<?php echo sprintf(_n('(%s night)', '(%s nights)', $date_diff, 'traveler'), $date_diff) ?>
			</span>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
		<?php
			if(!empty($st_booking_data['extras']) and $st_booking_data['extra_price']):
				$extra_price_unit = get_post_meta($room_id, 'extra_price_unit', true);
				$extra_unit       = $extra_price_unit == 'perday' ?  __('Per Night', 'traveler') : '';

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
								<?php echo esc_attr($title).": ".esc_attr($number_item).' x '.TravelHelper::format_money($price_item) . ' ' . $extra_unit; ?>
							</span> <br />
							<?php };?>
						<?php endforeach;?>
						</div>
					<?php  endif; ?>
		<?php endif; ?>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php
				$discount = $st_booking_data['discount_rate'];
				if (!empty($discount)){ ?>
					<b class='booking-cart-item-title'><?php echo __( "Discount/Night", 'traveler'); ?>: </b>
					<?php
					$discount_type = get_post_meta( $st_booking_data['room_id'], 'discount_type', true );
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
				$total_bulk_discount = !empty($st_booking_data['total_bulk_discount']) ? floatval($st_booking_data['total_bulk_discount']): 0;
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
			}else {$tax = 0 ;}
			?>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<b class='booking-cart-item-title'><?php echo __("Total amount" , 'traveler') ;  ?>:</b>
                <?php echo TravelHelper::format_money($origin_price);?> <!-- Nikola display dynamic price WC session START-->
		</div>
	</div>
</div>
