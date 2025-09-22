<div class="info">
	<div class="row">
		<div class="col-md-12">
			<div class="item_booking_detail">
				<strong><?php echo __( 'First name ', 'traveler' ); ?></strong>:
				<?php
				$billing_first_name = get_post_meta( $order_id, '_billing_first_name', true );
				if ( empty( $billing_first_name ) ) {
					global $wpdb;
					$querystr           = 'SELECT first_name
								FROM  ' . $wpdb->prefix . "wc_order_addresses
								WHERE order_id = '{$order_id}'
								";
					$billing_first_name = $wpdb->get_row( $querystr, OBJECT )->first_name;
				}
				echo $billing_first_name;
				?>
			</div>
		</div>
		<div class="col-md-12">
			<div class="item_booking_detail">
				<strong><?php echo __( 'Last name ', 'traveler' ); ?></strong>:
				<?php
				$billing_last_name = get_post_meta( $order_id, '_billing_last_name', true );
				if ( empty( $billing_last_name ) ) {
					global $wpdb;
					$querystr          = 'SELECT last_name
								FROM  ' . $wpdb->prefix . "wc_order_addresses
								WHERE
								order_id = '{$order_id}'
								";
					$billing_last_name = $wpdb->get_row( $querystr, OBJECT )->last_name;
				}
				echo $billing_last_name;
				?>
			</div>
		</div>
		<div class="col-md-12">
			<div class="item_booking_detail">
				<strong><?php echo __( 'Email ', 'traveler' ); ?></strong>:
				<?php
				$billing_email = get_post_meta( $order_id, '_billing_email', true );
				if ( empty( $billing_email ) ) {
					global $wpdb;
					$querystr      = 'SELECT email
								FROM  ' . $wpdb->prefix . "wc_order_addresses
								WHERE
								order_id = '{$order_id}'
								";
					$billing_email = $wpdb->get_row( $querystr, OBJECT )->email;
				}
				echo $billing_email;
				?>
			</div>
		</div>
		<div class="col-md-12">
			<div class="item_booking_detail">
				<strong><?php echo __( 'Phone ', 'traveler' ); ?></strong>:
				<?php
				$billing_phone = get_post_meta( $order_id, '_billing_phone', true );
				if ( empty( $billing_phone ) ) {
					global $wpdb;
					$querystr      = 'SELECT phone
								FROM  ' . $wpdb->prefix . "wc_order_addresses
								WHERE
								order_id = '{$order_id}'
								";
					$billing_phone = $wpdb->get_row( $querystr, OBJECT )->phone;
				}
				echo $billing_phone;
				?>
			</div>
		</div>
		<div class="col-md-12">
			<div class="item_booking_detail">
				<strong><?php echo __( 'Address Line 1', 'traveler' ); ?></strong>:
				<?php
				$billing_address_1 = get_post_meta( $order_id, '_billing_address_1', true );
				if ( empty( $billing_address_1 ) ) {
					global $wpdb;
					$querystr      = 'SELECT address_1
								FROM  ' . $wpdb->prefix . "wc_order_addresses
								WHERE
								order_id = '{$order_id}'
								";
					$billing_address_1 = $wpdb->get_row( $querystr, OBJECT )->address_1;
				}
				echo $billing_address_1;
				?>
			</div>
		</div>
		<div class="col-md-12">
			<div class="item_booking_detail">
				<strong><?php echo __( 'Address Line 2', 'traveler' ); ?></strong>:
				<?php
				$billing_address_2 = get_post_meta( $order_id, '_billing_address_2', true );
				if ( empty( $billing_address_2 ) ) {
					global $wpdb;
					$querystr      = 'SELECT address_2
								FROM  ' . $wpdb->prefix . "wc_order_addresses
								WHERE
								order_id = '{$order_id}'
								";
					$billing_address_2 = $wpdb->get_row( $querystr, OBJECT )->address_2;
				}
				echo $billing_address_2;
				?>
			</div>
		</div>
		<div class="col-md-12">
			<div class="item_booking_detail">
				<strong><?php echo __( 'City', 'traveler' ); ?></strong>:
				<?php
				$billing_city = get_post_meta( $order_id, '_billing_city', true );
				if ( empty( $billing_city ) ) {
					global $wpdb;
					$querystr      = 'SELECT city
								FROM  ' . $wpdb->prefix . "wc_order_addresses
								WHERE
								order_id = '{$order_id}'
								";
					$billing_city = $wpdb->get_row( $querystr, OBJECT )->city;
				}
				echo $billing_city;
				?>
			</div>
		</div>
		<div class="col-md-12">
			<div class="item_booking_detail">
				<strong><?php echo __( 'State/Province/Region', 'traveler' ); ?></strong>:
				<?php
				$billing_state = get_post_meta( $order_id, '_billing_state', true );
				if ( empty( $billing_state ) ) {
					global $wpdb;
					$querystr      = 'SELECT state
								FROM  ' . $wpdb->prefix . "wc_order_addresses
								WHERE
								order_id = '{$order_id}'
								";
					$billing_state = $wpdb->get_row( $querystr, OBJECT )->state;
				}
				echo $billing_state;
				?>
			</div>
		</div>
		<div class="col-md-12">
			<div class="item_booking_detail">
				<strong><?php echo __( 'ZIP code/Postal code', 'traveler' ); ?></strong>:
				<?php
				$billing_postcode = get_post_meta( $order_id, '_billing_postcode', true );
				if ( empty( $billing_postcode ) ) {
					global $wpdb;
					$querystr      = 'SELECT postcode
								FROM  ' . $wpdb->prefix . "wc_order_addresses
								WHERE
								order_id = '{$order_id}'
								";
					$billing_postcode = $wpdb->get_row( $querystr, OBJECT )->postcode;
				}
				echo $billing_postcode;
				?>
			</div>
		</div>
		<div class="col-md-12">
			<div class="item_booking_detail">
				<strong><?php echo __( 'Country', 'traveler' ); ?></strong>:
				<?php
				$billing_country = get_post_meta( $order_id, '_billing_country', true );
				if ( empty( $billing_country ) ) {
					global $wpdb;
					$querystr      = 'SELECT country
								FROM  ' . $wpdb->prefix . "wc_order_addresses
								WHERE
								order_id = '{$order_id}'
								";
					$billing_country = $wpdb->get_row( $querystr, OBJECT )->country;
				}
				echo $billing_country;
				?>
			</div>
		</div>
	</div>

</div>
