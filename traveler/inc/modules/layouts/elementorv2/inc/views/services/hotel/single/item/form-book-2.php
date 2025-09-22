<div class="st-form-book-wrapper relative" style="margin-bottom: 40px;">
	<div class="st-wrapper-form-booking form-date-search">
		<nav>
			<ul class="nav nav-tabs d-flex align-items-center justify-content-between nav-fill-st" id="nav-tab" role="tablist">
				<li><a class="active text-center" id="nav-book-tab" data-bs-toggle="tab" data-bs-target="#nav-book"
										role="tab" aria-controls="nav-home"
										aria-selected="true"><?php echo esc_html__( 'Book', 'traveler' ) ?></a>
				</li>
				<li><a class="text-center" id="nav-inquirement-tab" data-bs-toggle="tab" data-bs-target="#nav-inquirement"
						role="tab" aria-controls="nav-profile"
						aria-selected="false"><?php echo esc_html__( 'Inquiry', 'traveler' ) ?></a>
				</li>
			</ul>
		</nav>
		<div class="st-form-head-book d-flex justify-content-between align-items-center"></div>
		<div class="tab-content" id="nav-tabContent">
			<div class="tab-pane fade show active" id="nav-book" role="tabpanel" aria-labelledby="nav-book-tab">
				<div class="st-form-booking-action">
					<?php
					$hotel_external      = get_post_meta( get_the_ID(), 'st_hotel_external_booking', true );
					$hotel_external_link = get_post_meta( get_the_ID(), 'st_hotel_external_booking_link', true );
					if ( empty( $hotel_external ) || $hotel_external == 'off' ) :
						?>
						<div class="form-check-availability-full relative">
							<div class="st-banner-search-form style_2">
								<div class="st-search-form-el st-border-radius">
									<div class="st-search-el search-form-v2">
										<?php
										echo stt_elementorv2()->loadView( 'services/hotel/single/item/form-check-availability', [
											'price'   => $price,
											'post_id' => $post_id,
										] );
										?>
									</div>
								</div>
							</div>
						</div>
					<?php else : ?>
						<div class="submit-group button-external-link mb30">
							<a href="<?php echo esc_url( $hotel_external_link ); ?>" class="btn btn-green btn-large btn-full upper"><?php echo esc_html__( 'Explore', 'traveler' ); ?></a>
						</div>
					<?php endif; ?>
				</div>
			</div>
			<div class="tab-pane fade " id="nav-inquirement" role="tabpanel"
					aria-labelledby="nav-inquirement-tab">
					<div class="inquiry-v2">
						<?php echo st()->load_template( 'email/email_single_service' ); ?>
					</div>

			</div>
		</div>
	</div>


</div>
