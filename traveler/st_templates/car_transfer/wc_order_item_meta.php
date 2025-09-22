<?php
	/**
	 * Created by PhpStorm.
	 * User: MSI
	 * Date: 14/07/2015
	 * Time: 3:17 CH
	 */
	$item_data = isset( $item['item_meta'] ) ? $item['item_meta'] : [];
?>
<ul class="wc-order-item-meta-list">

	<?php if ( isset( $item_data['_st_pick_up'] ) and $item_data['_st_pick_up'] ) { ?>
		<li>
			<span class="meta-label"><?php _e( 'Pick-up:', 'traveler' ) ?></span>
			<span class="meta-data">
			<?php
			if ( $item_data['_st_pick_up'] ) {
				echo esc_html( $item_data['_st_pick_up'] );
			}

			?>
				</span>
		</li>
		<?php

	}
	?>

	<?php if ( isset( $item_data['_st_drop_off'] ) and $item_data['_st_drop_off'] ) { ?>
		<li>
			<span class="meta-label"><?php _e( 'Drop-off:', 'traveler' ) ?></span>
			<span class="meta-data">
			<?php
			if ( $item_data['_st_drop_off'] ) {
				echo esc_html( $item_data['_st_drop_off'] );
			}
			?>
		</span>
		</li>
		<?php

	}
	?>

	<?php if ( isset( $item_data['_st_check_in_timestamp'] ) ) : ?>
		<li>
			<span class="meta-label"><?php _e( 'Date:', 'traveler' ) ?></span>
			<span
				class="meta-data"><?php echo date_i18n( TravelHelper::getDateFormat() . ' H:i A', $item_data['_st_check_in_timestamp'] ) ?>

			</span>
		</li>
	<?php endif; ?>
	<?php
	if ( isset( $item_data['_st_distance'] ) ) :
		?>
		<li>
			<span class="meta-label"><?php _e( 'Distance:', 'traveler' ) ?></span>
			<span
				class="meta-data">
				<?php
					$time   = $item_data['_st_distance'];
					$hour   = ( $time['hour'] >= 2 ) ? $time['hour'] . ' ' . esc_html__( 'hours', 'traveler' ) : $time['hour'] . ' ' . esc_html__( 'hour', 'traveler' );
					$minute = ( $time['minute'] >= 2 ) ? $time['minute'] . ' ' . esc_html__( 'minutes', 'traveler' ) : $time['minute'] . ' ' . esc_html__( 'minute', 'traveler' );
					echo esc_attr( $hour ) . ' ' . esc_attr( $minute ) . ' - ' . esc_html( $time['distance'] ) . __( 'Km', 'traveler' );
				?>
			</span>
			<?php
			if ( ! empty( $item_data['_st_roundtrip'] ) && $item_data['_st_roundtrip'] === 'yes' ) {
				echo ' (' . esc_html__( 'Return', 'traveler' ) . ')';
			}
			?>
		</li>
	<?php endif; ?>

	<?php if ( ! empty( $item_data['_st_price'] ) ) :
		$price_type = get_post_meta( $item_data['_st_car_id'], 'price_type', true );
		$price_unit = '';
		switch ($price_type) {
			case 'distance':
				$price_unit = __(' Per Km', 'traveler');
				break;
			case 'passenger':
				$price_unit = __(' Per Person', 'traveler');
				break;
			case 'fixed':
				$price_unit = __('', 'traveler');
				break;
		}
		?>
		<li>
			<span class="meta-label"><?php echo __( 'Direction Price ', 'traveler' ) . '(' . esc_html( $item_data['_st_distance']['distance'] ) . __( 'Km ', 'traveler' ) . ') :' ?></span>
			<span class="meta-data">
				<?php echo TravelHelper::format_money( $item_data['_st_price'] ) . $price_unit ?>
			</span>
		</li>
	<?php endif; ?>

	<?php if ( ! empty( $item_data['_st_has_return'] ) ) : ?>
		<li>
			<span class="meta-label"><?php echo __( 'Towards Price', 'traveler' ) . '(' . esc_html( $item_data['_st_distance']['distance_return'] ) . __( 'Km ', 'traveler' ) . ') :' ?></span>
			<span class="meta-data">
				<?php echo TravelHelper::format_money( $item_data['_st_price_return'] ) . $price_unit ?>
			</span>
		</li>
	<?php endif; ?>

	<?php if ( isset( $item_data['_st_extras'] ) && $item_data['_st_extras'] && ! empty( $item_data['_st_extras'] ) ) : ?>
		<li>
			<span class="meta-label"><?php echo __( 'Extras(s):', 'traveler' ) ?></span>
			<span class="meta-data">
				<?php
				echo '</br>';
				foreach ( $item_data['_st_extras'] as $key => $data ) {
					echo '&nbsp;&nbsp;&nbsp;- ' . esc_html( $data['title'] ) . ': ' . TravelHelper::format_money( $data['price'] ) . ' (x' . esc_html( $data['number'] ) . ')' . ' <br>';
				}
				echo '</br>';
				?>
			</span>
		</li>
	<?php endif; ?>

	<?php if ( ! empty( $item_data['_st_passenger'] ) ) : ?>
		<li>
			<span class="meta-label"><?php echo __( 'Passenger(s) : ', 'traveler' ) ?></span>
			<span class="meta-data">
				<?php
				echo esc_html( $item_data['_st_passenger'] );
				?>
			</span>
		</li>
	<?php endif; ?>

	<?php if ( ! empty( $item_data['_st_discount_rate'] ) ) : ?>
		<li>
			<span class="meta-label"><?php echo __( 'Discount: ', 'traveler' ) ?></span>
			<span class="meta-data">
				<?php echo esc_attr( $item_data['_st_discount_rate'] ) . '%' ?>
			</span>
		</li>
	<?php endif; ?>
</ul>
