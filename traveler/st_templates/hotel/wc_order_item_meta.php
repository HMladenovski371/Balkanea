<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 14/07/2015
 * Time: 3:17 CH
 */
$item_data = isset( $item['item_meta'] ) ? $item['item_meta'] : [];

$format = TravelHelper::getDateFormat();

$check_in     = $item_data['_st_check_in'];
$check_out    = $item_data['_st_check_out'];
if ( TravelHelper::getDateFormat() === 'd/m/Y' ) {
	$check_in  = str_replace( '/', '-', $check_in );
	$check_out = str_replace( '/', '-', $check_out );
}
$date_diff    = STDate::dateDiff( $check_in, $check_out );
$number_room  = intval( $item_data['_st_room_num_search'] );
$adult_number = intval( $item_data['_st_adult_number'] );
$child_number = intval( $item_data['_st_child_number'] );
$origin_price = STPrice::getRoomPriceOnlyCustomPrice( $item_data['_st_room_id'], strtotime( $check_in ), strtotime( $check_out ), $number_room, $adult_number, $child_number );
?>

<ul class="wc-order-item-meta-list">
	<?php
	if ( isset( $item_data['_st_room_id'] ) ) :
		$data = $item_data['_st_room_id'];
		?>
		<li>
			<span class="meta-label"><?php _e( 'Room:', 'traveler' ) ?></span>
			<span class="meta-data"><?php echo sprintf( '<a href="%s">%s</a>', get_permalink( $data ), get_post( $data )->post_title ) ?></span>
		</li>
	<?php endif; ?>
	<?php
	if ( isset( $item_data['_st_check_in'] ) ) :
		$data = $item_data['_st_check_in'];
		?>
		<li>
			<span class="meta-label"><?php _e( 'Date:', 'traveler' ) ?></span>
			<span class="meta-data">
			<?php
				echo esc_html( $data );
			?>
				<?php
				if ( isset( $item_data['_st_check_out'] ) ) {
					$data = $item_data['_st_check_out'];
					?>
					-
					<?php echo esc_html( $data ); ?>
				<?php } ?>
			</span>
		</li>
	<?php endif; ?>

	<?php if ( isset( $item_data['_st_adult_number'] ) ) : ?>
		<li>
			<span class="meta-label"><?php _e( 'Adult:', 'traveler' ) ?></span>
			<span class="meta-data"><?php echo esc_html( $item_data['_st_adult_number'] ); ?></span>
		</li>
	<?php endif; ?>
	<?php if ( isset( $item_data['_st_child_number'] ) ) : ?>
		<li>
			<span class="meta-label"><?php _e( 'Children:', 'traveler' ) ?></span>
			<span class="meta-data"><?php echo esc_html( $item_data['_st_child_number'] ); ?></span>
		</li>
	<?php endif; ?>
	<?php
	if ( isset( $item_data['_st_room_num_search'] ) ) :
		$data = $item_data['_st_room_num_search'];
		?>
		<?php
		if ( get_post_meta( $item_data['_st_room_id'], 'price_by_per_person', true ) == 'on' ) :
			?>
			<li>
				<span class="meta-label"><?php _e( 'Number of rooms:', 'traveler' ) ?></span>
				<span class="meta-data"><?php echo esc_html( $item_data['_st_room_num_search'] ); ?></span>
			</li>
			<?php
		else :
			$order_data_id_woo = STUser_f::get_booking_meta( $item_data['_st_wc_order_id'] );
			$order             = wc_get_order( $order_data_id_woo['wc_order_id'] );
			$st_item_price     = wc_price( intval( floatval( $item_data['_st_item_price'] ) ), [ 'currency' => $order->get_currency() ] );

			?>
			<li>
				<span class="meta-label"><?php _e( 'Number of rooms:', 'traveler' ) ?></span>
				<span class="meta-data"><?php echo esc_html( $item_data['_st_room_num_search'] ); ?></span>
			</li>
			<?php
		endif;
		?>
	<?php endif; ?>

	<li>
		<?php echo __( 'Price Detail', 'traveler' ); ?>:
		<?php echo TravelHelper::format_money( $origin_price ) ?>
		<?php echo sprintf( _n( '(%s night)', '(%s nights)', $date_diff, 'traveler' ), $date_diff ) ?>
	</li>

	<?php
	if ( isset( $item_data['_st_extras'] ) and ( $extra_price = $item_data['_st_extra_price'] ) ) :
		$data = $item_data['_st_extras'];
		?>
		<li>
		<p><?php echo __( 'Extra prices', 'traveler' ) . ': '; ?></p>
		<ul>
		<?php
		if ( ! empty( $data['title'] ) and is_array( $data['title'] ) ) {
			foreach ( $data['title'] as $key => $title ) {
				?>
				<?php if ( $data['value'][ $key ] ) { ?>
					<li style="padding-left: 10px "> <?php echo esc_attr( $title ); ?>:
						<?php echo esc_html( $data['value'][ $key ] ); ?>
						x
						<?php
						echo TravelHelper::format_money( $data['price'][ $key ] );
						echo esc_html__( ' Per Night', 'traveler' );
						?>
					</li>
				<?php } ?>
				<?php
			}
		}
		?>
		</ul>
		</li>
	<?php endif; ?>
	<?php
	if ( isset( $item_data['_st_discount_rate'] ) && ! empty( $item_data['_st_discount_rate'] ) ) :
		$discount = $item_data['_st_discount_rate'];
		?>
		<li>
			<?php echo __( 'Discount/Night', 'traveler' ) . ': '; ?>
			<?php
			$discount_type = get_post_meta( $item_data['_st_room_id'], 'discount_type', true );
			if ( $discount_type == 'amount' ) {
				echo esc_attr( TravelHelper::format_money( $discount ) );
			} else {
				echo esc_attr( $discount ) . '%';
			}
			?>
		</li>
	<?php endif; ?>

	<?php
	if ( isset( $item_data['_st_total_bulk_discount'] ) ) :
		$total_bulk_discount = $item_data['_st_total_bulk_discount'];
		?>
		<?php
		if ( $total_bulk_discount > 0 ) {
			?>
<li>
			<?php echo __( 'Bulk Discount', 'traveler' ) . ': '; ?>
			<?php echo TravelHelper::format_money( $total_bulk_discount ); ?>
		<?php } ?></li>
	<?php endif; ?>

	<?php
	if ( isset( $item_data['_line_tax'] ) ) :
		$data = st_wc_parse_order_item_meta( $item_data['_line_tax'] );
		?>
		<?php
		if ( ! empty( $data ) ) {
			?>
			<li><p>
			<?php echo __( 'Tax', 'traveler' ) . ': '; ?>
			<?php echo TravelHelper::format_money( $data ); ?>
			</p></li>
		<?php } ?>
	<?php endif; ?>


</ul>
