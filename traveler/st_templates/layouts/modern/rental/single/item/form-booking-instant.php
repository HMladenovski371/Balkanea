<div class="form-book-wrapper">
	<?php echo st()->load_template( 'layouts/modern/common/loader' ); ?>
	<div class="form-head">

	<?php if ( $min_price < $orgin_price ) : ?>
		<div class="price-regular">
			<span>
				<?php echo esc_html__( TravelHelper::format_money( $orgin_price ), 'traveler' ) ?>
			</span>
		</div>
	<?php endif; ?>

	<?php
	if ( isset( $number_day ) && $number_day > 1 ) {
		echo wp_kses( sprintf( __( '<div class="price-min">from <span class="price">%1$s</span> per %2$s nights</div>', 'traveler' ), TravelHelper::format_money( $min_price ), $number_day ), [
			'span' => [ 'class' => [] ],
			'div'  => [ 'class' => [] ],
		] );
	} else {
		echo wp_kses( sprintf( __( '<div class="price-min">from <span class="price">%s</span> per night</div>', 'traveler' ), TravelHelper::format_money( $min_price ) ), [
			'span' => [ 'class' => [] ],
			'div'  => [ 'class' => [] ],
		] );
	}
	?>
	</div>
	<?php if ( empty( $room_external ) || $room_external == 'off' ) { ?>
		<form id="form-booking-inpage" class="form form-has-guest-name single-room-form rental-booking-form" method="post">
			<input name="action" value="rental_add_cart" type="hidden">
			<input name="item_id" value="<?php echo esc_attr( get_the_ID() ); ?>" type="hidden">
			<?php wp_nonce_field( 'room_search', 'room_search' ) ?>
			<?php
			$current_calendar        = TravelHelper::get_current_available_calendar( get_the_ID() );
			$current_calendar_reverb = date( 'm/d/Y', strtotime( $current_calendar ) );

			$start    = STInput::get( 'start', date( TravelHelper::getDateFormat(), strtotime( $current_calendar ) ) );
			$end      = STInput::get( 'end', date( TravelHelper::getDateFormat(), strtotime( '+ 1 day', strtotime( $current_calendar ) ) ) );
			$date     = STInput::get( 'date', date( 'd/m/Y h:i a', strtotime( $current_calendar ) ) . '-' . date( 'd/m/Y h:i a', strtotime( '+1 day', strtotime( $current_calendar ) ) ) );
			$has_icon = ( isset( $has_icon ) ) ? $has_icon : false;
			?>
			<div class="form-group form-date-field form-date-hotel-room clearfix
			<?php
			if ( $has_icon ) {
				echo ' has-icon ';}
			?>
				"
				data-format="<?php echo TravelHelper::getDateFormatMoment() ?>" data-availability-date="<?php echo esc_attr( $current_calendar_reverb ); ?>">
				<?php
				if ( $has_icon ) {
					echo '<i class="field-icon fa fa-calendar"></i>';
				}
				?>
				<div class="date-wrapper clearfix">
					<div class="check-in-wrapper">
						<label><?php echo __( 'Check In - Out', 'traveler' ); ?></label>
						<div class="render check-in-render"><?php echo esc_attr( $start ); ?></div> - <div class="render check-out-render"><?php echo esc_html( $end ); ?></div>
					</div>
				</div>
				<input type="hidden" class="check-in-input"
						value="<?php echo esc_attr( $start ) ?>" name="start">
				<input type="hidden" class="check-out-input"
						value="<?php echo esc_attr( $end ) ?>" name="end">
				<input type="text" class="check-in-out"
						data-minimum-day="<?php echo esc_attr( $booking_period ); ?>"
						data-room-id="<?php echo esc_attr( $room_id ) ?>"
						data-action="st_get_availability_rental_single"
						value="<?php echo esc_attr( $date ); ?>" name="date">
			</div>
			<?php
			$has_icon     = ( isset( $has_icon ) ) ? $has_icon : false;
			$adult_number = STInput::get( 'adult_number', 1 );
			$child_number = STInput::get( 'child_number', 0 );
			?>
			<div class="form-group form-extra-field dropdown clearfix field-guest
			<?php
			if ( $has_icon ) {
				echo ' has-icon ';}
			?>
				">
				<?php
				if ( $has_icon ) {
					echo TravelHelper::getNewIcon( 'ico_guest_search_box' );
				}
				?>
				<div id="dropdown-advance" class="dropdown" data-toggle="dropdown">
					<label><?php echo __( 'Guests', 'traveler' ); ?></label>
					<div class="render">
						<span class="adult" data-text="<?php echo __( 'Adult', 'traveler' ); ?>" data-text-multi="<?php echo __( 'Adults', 'traveler' ); ?>"><?php echo sprintf( _n( '%s Adult', '%s Adults', esc_attr( $adult_number ), 'traveler' ), esc_attr( $adult_number ) ) ?></span>
						-
						<span class="children" data-text="<?php echo __( 'Child', 'traveler' ); ?>"
								data-text-multi="<?php echo __( 'Children', 'traveler' ); ?>"><?php echo sprintf( _n( '%s Child', '%s Children', esc_attr( $child_number ), 'traveler' ), esc_attr( $child_number ) ); ?></span>
					</div>
				</div>
				<ul class="dropdown-menu" aria-labelledby="dropdown-advance">
					<li class="item">
						<label><?php echo esc_html__( 'Adults', 'traveler' ) ?></label>
						<div class="select-wrapper">
							<div class="st-number-wrapper">
								<input type="text" name="adult_number" value="<?php echo esc_attr( $adult_number ); ?>" class="form-control adult_number st-input-number" autocomplete="off" readonly data-min="1" data-max="<?php echo (int) get_post_meta( $room_id, 'rental_max_adult', true ) ?>"/>
							</div>
						</div>
					</li>
					<li class="item">
						<label><?php echo esc_html__( 'Children', 'traveler' ) ?></label>
						<div class="select-wrapper">
							<div class="st-number-wrapper">
								<input type="text" name="child_number" value="<?php echo esc_attr( $child_number ); ?>" class="form-control child_number st-input-number" autocomplete="off" readonly data-min="0" data-max="<?php echo (int) get_post_meta( $room_id, 'rental_max_children', true ) ?>"/>
							</div>
						</div>
					</li>
					<span class="hidden-lg hidden-md hidden-sm btn-close-guest-form"><?php echo __( 'Close', 'traveler' ); ?></span>
				</ul>
				<i class="fa fa-angle-down arrow"></i>
			</div>
			<div class="guest_name_input hidden"
				data-placeholder="<?php echo esc_html__( 'Guest %d name', 'traveler' ) ?>"
				data-hide-adult="<?php echo get_post_meta( get_the_ID(), 'disable_adult_name', true ) ?>"
				data-hide-children="<?php echo get_post_meta( get_the_ID(), 'disable_children_name', true ) ?>"
				data-hide-infant="<?php echo get_post_meta( get_the_ID(), 'disable_infant_name', true ) ?>">
				<label><span><?php echo esc_html__( 'Guest Name', 'traveler' ) ?></span> <span class="required">*</span></label>
				<div class="guest_name_control">
					<?php
					$controls     = STInput::request( 'guest_name' );
					$guest_titles = STInput::request( 'guest_title' );
					if ( ! empty( $controls ) and is_array( $controls ) ) {
						foreach ( $controls as $k => $control ) {
							?>
							<div class="control-item mb10">
								<select name="guest_title[]" class="form-control">
									<option value="mr" <?php selected( 'mr', isset( $guest_titles[ $k ] ) ? $guest_titles[ $k ] : '' ) ?>><?php echo esc_html__( 'Mr', 'traveler' ) ?></option>
									<option value="miss" <?php selected( 'miss', isset( $guest_titles[ $k ] ) ? $guest_titles[ $k ] : '' ) ?> ><?php echo esc_html__( 'Miss', 'traveler' ) ?></option>
									<option value="mrs" <?php selected( 'mrs', isset( $guest_titles[ $k ] ) ? $guest_titles[ $k ] : '' ) ?>><?php echo esc_html__( 'Mrs', 'traveler' ) ?></option>
								</select>
								<?php printf( '<input class="form-control " placeholder="%s" name="guest_name[]" value="%s">', sprintf( esc_html__( 'Guest %d name', 'traveler' ), $k + 2 ), esc_attr( $control ) ); ?>
							</div>
							<?php
						}
					}
					?>
				</div>
				<script type="text/html" id="guest_name_control_item">
					<div class="control-item mb10">
						<select name="guest_title[]" class="form-control">
							<option value="mr"><?php echo esc_html__( 'Mr', 'traveler' ) ?></option>
							<option value="miss"><?php echo esc_html__( 'Miss', 'traveler' ) ?></option>
							<option value="mrs"><?php echo esc_html__( 'Mrs', 'traveler' ) ?></option>
						</select>
						<?php printf( '<input class="form-control " placeholder="%s" name="guest_name[]" value="">', esc_html__( 'Guest  name', 'traveler' ) ); ?>
					</div>
				</script>
			</div>


			<?php echo st()->load_template( 'layouts/modern/rental/elements/search/extra', '' ); ?>
			<div class="submit-group">
				<button class="btn btn-large btn-full upper font-medium btn_hotel_booking btn-book-ajax"
						type="submit"
						name="submit">
					<?php echo __( 'Book Now', 'traveler' ) ?>
					<i class="fa fa-spinner fa-spin hide"></i>
				</button>
				<input style="display:none;" type="submit" class="btn btn-default btn-send-message" data-id="<?php echo get_the_ID(); ?>" name="st_send_message" value="<?php echo __( 'Send message', 'traveler' ); ?>">
			</div>
			<div class="mt30 message-wrapper">
				<?php echo STTemplate::message() ?>
			</div>
		</form>
	<?php } else { ?>
		<div class="submit-group mb30">
			<a href="<?php echo esc_url( $room_external_link ); ?>" class="btn btn-large btn-full upper"><?php echo esc_html__( 'Explore', 'traveler' ); ?></a>
			<form id="form-booking-inpage" class="form form-has-guest-name single-room-form rental-booking-form" method="post">
				<input name="action" value="rental_add_cart" type="hidden">
				<input name="item_id" value="<?php echo esc_attr( $room_id ); ?>" type="hidden">
				<?php wp_nonce_field( 'room_search', 'room_search' ) ?>
				<?php
				$current_calendar        = TravelHelper::get_current_available_calendar( get_the_ID() );
				$current_calendar_reverb = date( 'm/d/Y', strtotime( $current_calendar ) );

				$start = STInput::get( 'start', date( TravelHelper::getDateFormat(), strtotime( $current_calendar ) ) );
				$end   = STInput::get( 'end', date( TravelHelper::getDateFormat(), strtotime( '+ 1 day', strtotime( $current_calendar ) ) ) );
				$date  = STInput::get( 'date', date( 'd/m/Y h:i a', strtotime( $current_calendar ) ) . '-' . date( 'd/m/Y h:i a', strtotime( '+1 day', strtotime( $current_calendar ) ) ) );

				?>
				<div class="form-group form-date-field form-date-hotel-room clearfix
				<?php
				if ( $has_icon ) {
					echo ' has-icon ';}
				?>
					"
						data-format="<?php echo TravelHelper::getDateFormatMoment() ?>" data-availability-date="<?php echo esc_attr( $current_calendar_reverb ); ?>">

					<input type="hidden" class="check-in-input"
							value="<?php echo esc_attr( $start ) ?>" name="start">
					<input type="hidden" class="check-out-input"
							value="<?php echo esc_attr( $end ) ?>" name="end">
					<input type="text" class="check-in-out"
							data-minimum-day="<?php echo esc_attr( $booking_period ); ?>"
							data-room-id="<?php echo esc_attr( $room_id ) ?>"
							data-action="st_get_availability_rental_single"
							value="<?php echo esc_attr( $date ); ?>" name="date">
				</div>
				<input style="display:none;" type="submit" class="btn btn-default btn-send-message" data-id="<?php echo get_the_ID(); ?>" name="st_send_message" value="<?php echo __( 'Send message', 'traveler' ); ?>">
			</form>
		</div>
	<?php } ?>
</div>
