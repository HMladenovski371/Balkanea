<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 03/06/2015
 * Time: 3:53 CH
 */
$format=TravelHelper::getDateFormat();
$div_id = "st_cart_item".md5(json_encode($st_booking_data['cart_item_key']));
$extras = isset($st_booking_data['extras']) ? $st_booking_data['extras'] : null;
?>
<p class="booking-item-description">
    <?php echo __('Date:','traveler');?>
	<?php echo date_i18n($format,strtotime($st_booking_data['check_in'])) ?>
	-
	<?php echo date_i18n($format,strtotime($st_booking_data['check_out'])) ?>
</p>

<?php
	$class = '';
	$id_collapse = '';
	if ( apply_filters( 'st_woo_cart_is_collapse', false ) ) {
		$class       = 'collapse';
		$id_collapse = 'collapseBookingDetail';
	}
?>

<div id="<?php echo esc_attr( $div_id ); ?>" >
	<p class="accordion-button collapsed <?= esc_attr( $id_collapse ) ?>"
		data-bs-toggle="collapse"
		data-bs-target="#collapseBookingDetail"
		aria-expanded="true"
		aria-controls="collapseBookingDetail"
	>
		<a data-toggle="collapse" href="#collapseBookingDetail" aria-expanded="true">
			<?php echo __( 'Booking Details', 'traveler' ); ?>
		</a>
	</p>
	<div id="<?= esc_attr( $id_collapse ) ?>"
		class="accordion-collapse <?= esc_attr( $class ) ?>"
	>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<div class="booking-item-description">
				<ul>
					<?php if($st_booking_data['adult_number']){?>
					<li><?php echo __('Adult:','traveler');?> <?php echo esc_html($st_booking_data['adult_number']); ?> </li>
					<?php }?>
					<?php if($st_booking_data['child_number']){?>
					<li><?php echo __('Children:','traveler');?> <?php echo esc_html($st_booking_data['child_number']); ?>    </li>
					<?php }?>
				</ul>
			</div>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<b class='booking-cart-item-title'><?php echo __( "Price Detail", 'traveler'); ?>: </b>
			<?php
			$room_id = $st_booking_data['st_booking_id'];
			$check_in = $st_booking_data['check_in'];
			$check_out = $st_booking_data['check_out'];
			$date_diff = STDate::dateDiff($check_in,$check_out);
			$origin_price = STPrice::getRentalPriceOnlyCustomPrice($room_id, strtotime($check_in), strtotime($check_out));
			?>
			<span class="value">
				<?php echo TravelHelper::format_money($origin_price) ?>
			</span>
			<span class="label">
				<?php echo sprintf(_n('(%s night)', '(%s nights)', $date_diff, 'traveler'), $date_diff) ?>
			</span>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
		<?php
			if(!empty($st_booking_data['extras']) and $st_booking_data['extra_price']):
				$extras = $st_booking_data['extras'];
					if(isset($extras['title']) && is_array($extras['title']) && count($extras['title'])): ?>
						<b class='booking-cart-item-title'><?php _e("Extra prices",'traveler') ?></b>
						<div class="booking-item-payment-price-amount">
						<?php
						$extra_type = isset($st_booking_data['extra_type']) ? $st_booking_data['extra_type'] : 'perday';
						$extra_unit = $extra_type == 'perday' ?  __('Per Night', 'traveler') : '';
						foreach($extras['title'] as $key => $title):
							$price_item = floatval($extras['price'][$key]);
							if($price_item <= 0) $price_item = 0;
							$number_item = intval($extras['value'][$key]);
							if($number_item <= 0) $number_item = 0;
						?>
							<?php if($number_item){ ?>
							<span style="padding-left: 10px ">
								<?php echo esc_attr($title).": ".esc_attr($number_item).' x '.TravelHelper::format_money($price_item) . ' ' . $extra_unit; ?>
							</span> <br />
							<?php }; ?>
						<?php endforeach;?>
						</div>
					<?php  endif; ?>
		<?php endif; ?>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php
				$discount = $st_booking_data['discount_rate'];
				$discount_type = $st_booking_data['discount_type'];
				$discount_html = $discount_type == 'amount' ? TravelHelper::format_money($discount) : $discount . '%' ;
				if (!empty($discount)){ ?>
					<b class='booking-cart-item-title'><?php echo __( "Discount/Night", 'traveler'); ?>: </b>
					<?php echo $discount_html; ?>
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
			}else {$tax  = 0  ;}
			?>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<b class='booking-cart-item-title'><?php echo __("Total amount" , 'traveler') ;  ?>:</b>
			<?php
			$include_tax_price =  get_option('woocommerce_prices_include_tax');
			if($include_tax_price == 'yes')
				echo TravelHelper::format_money($st_booking_data['ori_price'] );
			else
				echo TravelHelper::format_money($st_booking_data['ori_price'] + $tax );
			?>
		</div>
	</div>

</div>
