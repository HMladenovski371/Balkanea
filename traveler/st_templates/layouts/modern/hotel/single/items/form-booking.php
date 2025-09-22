<nav>
	<ul class="nav nav-tabs nav-fill-st" id="nav-tab" role="tablist">
		<li class="active"><a id="nav-book-tab" data-toggle="tab" href="#nav-book" role="tab" aria-controls="nav-home" aria-selected="true"><?php echo esc_html__( 'Book', 'traveler' ) ?></a></li>
		<li><a id="nav-inquirement-tab" data-toggle="tab" href="#nav-inquirement" role="tab" aria-controls="nav-profile" aria-selected="false"><?php echo esc_html__( 'Inquiry', 'traveler' ) ?></a></li>
	</ul>
</nav>
<div class="tab-content py-3 px-3 px-sm-0" id="nav-tabContent">
	<div class="tab-pane fade in active" id="nav-book" role="tabpanel"
		aria-labelledby="nav-book-tab">
		<?php echo st()->load_template( 'layouts/modern/common/loader' ); ?>
		<?php 
		$hotel_external      = get_post_meta( get_the_ID(), 'st_hotel_external_booking', true );
		$hotel_external_link = get_post_meta( get_the_ID(), 'st_hotel_external_booking_link', true );
		if(empty($hotel_external) || $hotel_external == 'off'){ ?>
			<form class="form form-check-availability-hotel clearfix" method="post">
				<input type="hidden" name="action" value="ajax_search_room">
				<input type="hidden" name="room_search" value="1">
				<input type="hidden" name="is_search_room" value="1">
				<input type="hidden" name="room_parent"
						value="<?php echo esc_attr( get_the_ID() ); ?>">
				<?php echo st()->load_template( 'layouts/modern/hotel/elements/search/date-enquire', '' ); ?>
				<?php echo st()->load_template( 'layouts/modern/hotel/elements/search/guest', '' ); ?>
				<div class="form-group submit-group">
					<input class="btn btn-large btn-full upper"
							type="submit"
							name="submit"
							value="<?php echo esc_html__( 'Check Availability', 'traveler' ) ?>">
					<input style="display:none;" type="submit"
							class="btn btn-default btn-send-message"
							data-id="<?php echo get_the_ID(); ?>" name="st_send_message"
							value="<?php echo __( 'Send message', 'traveler' ); ?>">
				</div>
				<div class="message-wrapper mt30"></div>
			</form>
		<?php } else {?>
			<div class="submit-group button-external-link pb-3">
				<a href="<?php echo esc_url($hotel_external_link); ?>" class="btn btn-large btn-full upper"><?php echo esc_html__( 'Explore', 'traveler' ); ?></a>
			</div>
        <?php }?>
	</div>
	<div class="tab-pane fade " id="nav-inquirement" role="tabpanel"
		aria-labelledby="nav-inquirement-tab">
		<?php echo st()->load_template( 'email/email_single_service' ); ?>
	</div>
</div>
