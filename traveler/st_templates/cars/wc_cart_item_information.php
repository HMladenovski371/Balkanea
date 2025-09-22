<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 02/06/2015
 * Time: 3:32 CH
 */
if (!empty($st_booking_data['data_equipment'])) {
    $selected_equipments = $st_booking_data['data_equipment'];
}

$pick_up_date = $st_booking_data['check_in_timestamp'];
$pick_up_time = isset($st_booking_data['check_in_time']) ? $st_booking_data['check_in_time'] : "";
$drop_off_date = $st_booking_data['check_out_timestamp'];
$drop_off_time = isset($st_booking_data['check_out_time']) ? $st_booking_data['check_out_time'] : "";

$diff = abs($drop_off_date - $pick_up_date);
$years = floor($diff / (365*60*60*24));
$months = floor(($diff - $years * 365*60*60*24)
               / (30*60*60*24));
$days_extra = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
$format = TravelHelper::getDateFormat();
$div_id = "st_cart_item" . md5(json_encode($st_booking_data['cart_item_key']));

$numberdays = (int)$st_booking_data['numberday'];
?>
<p class="booking-item-description">
    <?php echo __('Date:', 'traveler'); ?>
	<?php echo date_i18n($format . ', H:i A', $pick_up_date);?>
    -
	<?php echo date_i18n($format . ', H:i A', $drop_off_date);?>
    </br>

	<?php
	if (isset($st_booking_data['car_title_sale_price']['title_price']) && !empty($st_booking_data['car_title_sale_price']['title_price'])) {
		$unit = st()->get_option('cars_price_unit', 'day');
		printf( esc_html__( 'Price by number of %s: ', 'traveler' ), $unit );
		echo esc_html($st_booking_data['car_title_sale_price']['title_price']['title']) . ' ';
		echo TravelHelper::format_money($st_booking_data['car_title_sale_price']['title_price']['price']);
	}
	?>

	</br>

    <?php echo __('Location:', 'traveler'); ?>
    <?php if(!empty($st_booking_data['location_id'])){?>
        <?php echo get_the_title($st_booking_data['location_id']); ?>
    <?php } else {?>
        <?php if (!empty($st_booking_data['location_id_pick_up']) && !empty($st_booking_data['location_id_drop_off'])): ?>
            <?php echo get_the_title($st_booking_data['location_id_pick_up']); ?> <i
                    class="fa fa-long-arrow-right"></i> <?php echo get_the_title($st_booking_data['location_id_drop_off']) ?>
        <?php else: ?>
            <?php echo __('None', 'traveler'); ?>
        <?php endif; ?>
    <?php }?>
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
			<p class="booking-item-description">
				<b class='booking-cart-item-title'><?php echo __("Car price", 'traveler'); ?>  </b>
				:
				<?php
				if ($st_booking_data['duration_unit'] == 'day') {
					echo TravelHelper::format_money($st_booking_data['sale_price']) . ' / ';
					if($numberdays == 1){
						echo __("day", 'traveler');
					}else{
						echo sprintf(__('%s days', 'traveler'), $st_booking_data['numberday']);
					}
				}
				if ($st_booking_data['duration_unit'] == 'hour') {

					echo TravelHelper::format_money($st_booking_data['sale_price']) . ' / ';
					if($numberdays == 1){
						echo __("hour", 'traveler');
					}else{
						echo sprintf(__('%s hours', 'traveler'), $st_booking_data['numberday']);
					}
				}
				if ($st_booking_data['duration_unit'] == "distance") {
					$type_distance = st()->get_option("cars_price_by_distance", "kilometer");
					echo TravelHelper::format_money($st_booking_data['item_price']) . ' / ';
					if ($type_distance == "kilometer") {
						echo __("kilometer", 'traveler');
					} else {
						echo __("mile", 'traveler');
					}
				}
				?>
			</p>
			<?php
			if ($st_booking_data['duration_unit'] == "distance") {
			?>
				<p class="booking-item-description">
					<b class='booking-cart-item-title'><?php echo __("Distance", 'traveler'); ?>  </b>
					:
					<?php
						$type_distance = st()->get_option("cars_price_by_distance", "kilometer");
						echo TravelHelper::format_money($st_booking_data['data_destination']) . ' ';
						if ($type_distance == "kilometer") {
							echo __("kilometer", 'traveler');
						} else {
							echo __("mile", 'traveler');
						}
					?>
				</p>
			<?php } ?>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<p class="booking-item-description">
				<?php
				if (!empty($st_booking_data['data_equipment']) && $st_booking_data['price_equipment']):
					$extras = $selected_equipments;
					$new_layout = st()->get_option('st_theme_style', 'modern');
					if ( isset($new_layout) && $new_layout === 'classic' ) :
						if (isset($extras) && is_array($extras) && count($extras)): ?>
							<b class='booking-cart-item-title'><?php _e("Equipments price", 'traveler') ?></b>
							<div class="booking-item-payment-price-amount">
								<?php
								foreach ($extras as $key => $item):
									$title = isset($item->title) ? $item->title : '';
									$price_item = isset($item->price) ? floatval($item->price) : 0;
									if ($price_item <= 0) $price_item = 0;
									$number_item = isset($item->number_item) ? intval($item->number_item) : 0;
									$price_type = isset($item->price_unit) ? $item->price_unit : '';
									if ($number_item <= 0) $number_item = 0;
									if ($number_item) { ?>
										<span style="padding-left: 10px ">
											<?php
											if($price_type == 'fixed'){
												echo esc_attr($title) . ": " . esc_attr($number_item) . ' x ' . TravelHelper::format_money($price_item);
											}else{
												echo esc_attr($title) . ": " . esc_attr($number_item) . ' x ' . TravelHelper::format_money($price_item) .' x '.esc_html($numberdays).' '. __('Day(s)', 'traveler');
											}
											?>
										</span>
										<br/>
										<?php
									}
								endforeach; ?>
							</div>
							<?php
						endif;
					else :
						if (isset($extras['title']) && is_array($extras['title']) && count($extras['title'])): ?>
							<b class='booking-cart-item-title'><?php _e("Equipments price", 'traveler') ?></b>
							<div class="booking-item-payment-price-amount">
								<?php
								foreach ($extras['title'] as $key => $title):
									$price_item = floatval($extras['price'][$key]);
									if ($price_item <= 0) $price_item = 0;
									$number_item = intval($extras['value'][$key]);
									$price_type = $extras['price'][$key];
									if ($number_item <= 0) $number_item = 0;
									if ($number_item) { ?>
										<span style="padding-left: 10px ">
											<?php
											if($price_type == 'fixed'){
												echo esc_attr($title) . ": " . esc_attr($number_item) . ' x ' . TravelHelper::format_money($price_item);
											}else{
												echo esc_attr($title) . ": " . esc_attr($number_item) . ' x ' . TravelHelper::format_money($price_item) .' x '.esc_html($numberdays).' '. __('Day(s)', 'traveler');
											}
											?>
												</span> <br/>
										<?php
									}; ?>
									<?php
								endforeach; ?>
							</div>
							<?php
						endif; ?>
						<?php
					endif;
				endif; ?>
			</p>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php
			$discount = $st_booking_data['discount_rate'];
			if (!empty($discount)) { ?>
				<b class='booking-cart-item-title'><?php echo __("Discount", 'traveler'); ?>: </b>
				<?php echo esc_attr($discount) . "%" ?>
			<?php }
			?>
		</div>
	</div>
</div>
