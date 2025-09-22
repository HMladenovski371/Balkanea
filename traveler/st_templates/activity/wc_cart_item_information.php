<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 03/06/2015
 * Time: 3:53 CH
 */
$div_id = "st_cart_item".md5(json_encode($st_booking_data['cart_item_key']));
?>
<p class="booking-item-description">
    <?php echo __( 'Date:' , 'traveler' ); ?>
    <?php echo date_i18n( TravelHelper::getDateFormat() , strtotime( $st_booking_data[ 'check_in' ] ) ) ?>
</p>
<?php if(isset($st_booking_data['starttime'])){?>
<p class="booking-item-description">
    <?php echo __( 'Start Time:' , 'traveler' ); ?>
    <?php echo esc_html($st_booking_data['starttime']); ?>
</p>
<?php } ?>

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
			<?php
				$data_price = $st_booking_data['data_price'];
				$adult_price = $data_price['adult_price'];
				$child_price = $data_price['child_price'];
				$infant_price = $data_price['infant_price'];
			?>
			<p class="booking-item-description">
				<?php if (!empty($st_booking_data['adult_number'])) :?>
					<b><?php echo __('Adult', 'traveler'); ?>: </b><?php echo esc_html($st_booking_data['adult_number']); ?>
					<?php if(!empty($data_price['adult_price'])): ?>
					x
					<?php
						echo TravelHelper::format_money($st_booking_data['adult_price']);
						endif;
					?>
					<br>
				<?php endif ; ?>
			</p>
			<p class="booking-item-description">
				<?php if (!empty($st_booking_data['child_number'])) :?>
					<b><?php echo __('Children', 'traveler'); ?>: </b><?php echo esc_html($st_booking_data['child_number']); ?>
					<?php if(!empty($data_price['child_price'])): ?>
					x
					<?php
						echo TravelHelper::format_money($st_booking_data['child_price']);
						endif;
					?>
					<br>
				<?php endif ; ?>
			</p>
			<p class="booking-item-description">
				<?php if (!empty($st_booking_data['infant_number'])) :?>
					<b><?php echo __('Infant', 'traveler'); ?>: </b><?php echo esc_html($st_booking_data['infant_number']); ?>
					<?php if(!empty($data_price['infant_price'])): ?>
					x
					<?php
						echo TravelHelper::format_money($st_booking_data['infant_price']);
						endif;
					?>
					<br>
				<?php endif ; ?>
			</p>
		</div>

		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php
				$discount = $st_booking_data['discount_rate'];
				if (!empty($discount) && isset($st_booking_data['st_booking_id'])){ ?>
					<b class='booking-cart-item-title'><?php echo __( "Discount/Person", 'traveler'); ?>: </b>
					<?php
					$discount_type = get_post_meta( $st_booking_data['st_booking_id'], 'discount_type', true );
					if($discount_type == 'amount'){
						echo esc_attr(TravelHelper::format_money($discount));
					}else{
						echo esc_attr($discount) . '%';
					}?>
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
			}else {$tax = 0 ;}
			?>
		</div>
		<div class="cart_item_group" style='margin-bottom: 10px'>
			<?php

			if(!empty($st_booking_data['extras']) && $st_booking_data['extra_price']):
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
	</div>
</div>
