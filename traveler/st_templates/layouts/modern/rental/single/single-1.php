<?php
	/**
	 * Created by PhpStorm.
	 * User: Administrator
	 * Date: 20-11-2018
	 * Time: 8:08 AM
	 * Since: 1.0.0
	 * Updated: 1.0.0
	 */
while ( have_posts() ) :
	the_post();
	$room_id   = get_the_ID();
	$post_id   = get_the_ID();
	$thumbnail = get_the_post_thumbnail_url( $room_id, 'full' );

	$current_calendar               = TravelHelper::get_current_available_calendar( get_the_ID() );
	$current_calendar_reverb        = date( 'm/d/Y', strtotime( $current_calendar ) );
	$enable_street_views_google_map = get_post_meta( $post_id, 'enable_street_views_google_map', true );

	$start           = STInput::get( 'start', date( TravelHelper::getDateFormat(), strtotime( $current_calendar ) ) );
	$end             = STInput::get( 'end', date( TravelHelper::getDateFormat(), strtotime( '+ 1 day', strtotime( $current_calendar ) ) ) );
	$date            = STInput::get( 'date', date( 'd/m/Y h:i a', strtotime( $current_calendar ) ) . '-' . date( 'd/m/Y h:i a', strtotime( '+1 day', strtotime( $current_calendar ) ) ) );
	$room_num_search = (int) STInput::get( 'rental_number', 1 );
	if ( $room_num_search <= 0 ) {
		$room_num_search = 1;
	}
	$start = TravelHelper::convertDateFormat( $start );
	$end   = TravelHelper::convertDateFormat( $end );

	$orgin_price = STPrice::getRentalPriceOnlyCustomPrice( get_the_ID(), strtotime( $start ), strtotime( $end ) );
	$price       = STPrice::getSalePrice( get_the_ID(), strtotime( $start ), strtotime( $end ) );
	$number_day  = STDate::dateDiff( $start, $end );
	if ( $number_day == 0 ) {
		$number_day = 1;
	}
	$min_price   = ! empty( get_post_meta( get_the_ID(), 'min_price', true ) ) ? get_post_meta( get_the_ID(), 'min_price', true ) : 0;
	$min_price   = $min_price * $number_day;
	$min_price   = ! empty( $price['total_price_not_bulk_discount'] ) ? floatval( $price['total_price_not_bulk_discount'] ) : $min_price;
	$review_rate = STReview::get_avg_rate();

	$gallery       = get_post_meta( $room_id, 'gallery', true );
	$gallery_array = explode( ',', $gallery );

	$booking_period = (int) get_post_meta( $room_id, 'rentals_booking_period', true );
	$location       = get_post_meta( $room_id, 'multi_location', true );
	if ( ! empty( $location ) ) {
		$location = explode( ',', $location );
		if ( isset( $location[0] ) ) {
			$location = str_replace( '_', '', $location[0] );
		} else {
			$location = false;
		}
	}
	$address     = get_post_meta( $room_id, 'address', true );
	$marker_icon = st()->get_option( 'st_rental_icon_map_marker', '' );

	$room_external      = get_post_meta( get_the_ID(), 'st_rental_external_booking', true );
	$room_external_link = get_post_meta( get_the_ID(), 'st_rental_external_booking_link', true );

	$booking_type = st_get_booking_option_type();
	?>
		<div id="st-content-wrapper">
			<?php st_breadcrumbs_new() ?>
			<?php if ( ! empty( $gallery_array ) ) { ?>
				<div class="st-flickity st-gallery">
					<div class="carousel"
						data-flickity='{ "wrapAround": true, "pageDots": false }'>
						<?php
						foreach ( $gallery_array as $value ) {
							?>
								<div class="item"
									style="background-image: url('<?php echo wp_get_attachment_image_url( $value, [ 1200, 900 ] ) ?>')"></div>
								<?php
						}
						?>
					</div>
					<div class="shares dropdown">
						<?php
						$video_url = get_post_meta( get_the_ID(), 'video', true );
						if ( ! empty( $video_url ) ) {
							?>
							<a href="<?php echo esc_url( $video_url ); ?>"
								class="st-video-popup share-item"><?php echo TravelHelper::getNewIcon( 'video-player', '#FFFFFF', '20px', '20px' ) ?></a>
							<?php
						}
						?>
						<a href="#" class="share-item social-share">
							<?php echo TravelHelper::getNewIcon( 'ico_share', '', '20px', '20px' ) ?>
						</a>
						<ul class="share-wrapper">
							<li><a class="facebook"
									href="https://www.facebook.com/sharer/sharer.php?u=<?php the_permalink() ?>&amp;title=<?php the_title() ?>"
									target="_blank" rel="noopener" original-title="Facebook"><i
											class="fa fa-facebook fa-lg"></i></a></li>
							<li><a class="twitter"
									href="https://twitter.com/share?url=<?php the_permalink() ?>&amp;title=<?php the_title() ?>"
									target="_blank" rel="noopener" original-title="Twitter"><i
											class="fa fa-twitter fa-lg"></i></a></li>
							<li><a class="no-open pinterest"
							href="http://pinterest.com/pin/create/bookmarklet/?url=<?php the_permalink() ?>&is_video=false&description=<?php the_title() ?>&media=<?php echo get_the_post_thumbnail_url( get_the_ID() ) ?>"
									target="_blank" rel="noopener" original-title="Pinterest"><i
											class="fa fa-pinterest fa-lg"></i></a></li>
							<li><a class="linkedin"
									href="https://www.linkedin.com/shareArticle?mini=true&amp;url=<?php the_permalink() ?>&amp;title=<?php the_title() ?>"
									target="_blank" rel="noopener" original-title="LinkedIn"><i
											class="fa fa-linkedin fa-lg"></i></a></li>
						</ul>
						<?php echo st()->load_template( 'layouts/modern/hotel/loop/wishlist' ); ?>
					</div>
				</div>
			<?php } ?>
			<div class="st-hotel-room-content">
				<div class="hotel-target-book-mobile">
					<div class="price-wrapper">
						<?php echo wp_kses( sprintf( __( 'from <span class="price">%s</span>', 'traveler' ), TravelHelper::format_money( $min_price ) ), [ 'span' => [ 'class' => [] ] ] ) ?>
					</div>
					<?php
					if ( $room_external == 'off' || empty( $room_external ) ) {
						?>
						<a href=""
							class="btn btn-mpopup btn-green">
							<?php
							if ( $booking_type == 'enquire' ) {
								echo esc_html__( 'Inquiry', 'traveler' );
							} else {
								echo esc_html__( 'Check Availability', 'traveler' );
							}
							?>
						</a>
						<?php
					} else {
						?>
						<a href="<?php echo esc_url( $room_external_link ); ?>"
							class="btn btn-green"><?php echo esc_html__( 'Explore', 'traveler' ) ?></a>
						<?php
					}
					?>
				</div>
				<div class="container">
					<div class="row">
						<div class="col-xs-12 col-sm-8 col-md-9">
							<!--Heading-->
							<?php
							echo st()->load_template( 'layouts/modern/rental/single/item/heading', '', [
								'review_rate' => $review_rate
							] );
							?>
							<!--End Heading-->

							<div class="st-hr large"></div>

							<!-- Featured -->
							<?php echo st()->load_template( 'layouts/modern/rental/single/item/featured' ) ?>
							<!-- End Featured -->

							<div class="st-hr large"></div>

							<h2 class="st-heading-section"><?php echo __( 'Description', 'traveler' ) ?></h2>
							<div class="st-description"
								data-toggle-section="st-description"
							>
								<?php the_content(); ?>
							</div>
							<div class="st-hr large"></div>

							<!-- Discount -->
							<?php echo st()->load_template( 'layouts/modern/rental/single/item/discount' ); ?>
							<!-- End Discount -->

							<div class="st-hr large"></div>

							<!-- Attribute -->
							<?php echo st()->load_template( 'layouts/modern/rental/single/item/attribute' ); ?>
							<!-- End Attribute -->

							<!-- Calendar -->
							<?php
							echo st()->load_template( 'layouts/modern/rental/single/item/calendar', '', [
								'booking_period' => $booking_period
							] );
							?>
							<!-- End Calendar -->

							<div class="st-hr large"></div>

							<!-- Map -->
							<?php
							echo st()->load_template( 'layouts/modern/rental/single/item/map', '', [
								'location'                       => $location,
								'enable_street_views_google_map' => $enable_street_views_google_map,
								'address'                        => $address,
								'marker_icon'                    => $marker_icon
							] );
							?>
							<!-- End Map -->

							<!-- Review -->
							<?php
							echo st()->load_template( 'layouts/modern/rental/single/item/review', '', [
								'review_rate' => $review_rate
							] );
							?>
							<!-- End Review -->

							<div class="stoped-scroll-section"></div>
						</div>
						<div class="col-xs-12 col-sm-4 col-md-3">
							<div class="widgets">
								<div class="fixed-on-mobile" data-screen="992px">
									<div class="close-icon hide">
										<?php echo TravelHelper::getNewIcon( 'Ico_close' ); ?>
									</div>

									<?php
									if ( $booking_type == 'instant_enquire' ) {
										echo st()->load_template('layouts/modern/rental/single/item/form-booking', 'instant-enquire',
                                        [
                                            'min_price' => $min_price,
                                            'orgin_price' => $orgin_price,
                                            'room_id' => $room_id,
                                            'room_external' => $room_external,
                                            'room_external_link' => $room_external_link,
                                            'booking_period' => $booking_period,
                                            'number_day' => $number_day,
                                        ]);
									} elseif ( $booking_type == 'enquire' ) {
										echo st()->load_template('layouts/modern/rental/single/item/form-booking', 'enquire',
                                        [
                                            'min_price' => $min_price,
                                            'orgin_price' => $orgin_price,
                                            'room_id' => $room_id,
                                            'room_external' => $room_external,
                                            'room_external_link' => $room_external_link,
                                            'booking_period' => $booking_period,
                                            'number_day' => $number_day,
                                        ]);
									} else {
										echo st()->load_template('layouts/modern/rental/single/item/form-booking', 'instant',
                                        [
                                            'min_price' => $min_price,
                                            'orgin_price' => $orgin_price,
                                            'room_id' => $room_id,
                                            'room_external' => $room_external,
                                            'room_external_link' => $room_external_link,
                                            'booking_period' => $booking_period,
                                            'number_day' => $number_day,
                                        ]);
									}
									?>
									<?php
										$list_badges = get_post_meta(get_the_ID(), 'list_badges', true);
										if(!empty($list_badges)){
											echo st()->load_template('layouts/modern/common/single/badge','', array('list_badges' => $list_badges));
										}
									?>
									<div class="owner-info widget-box">
										<h4 class="heading"><?php echo __( 'Owner', 'traveler' ) ?></h4>
										<div class="media">
											<div class="media-left">
											<?php
											$author_id = get_post_field( 'post_author', get_the_ID() );
											$userdata  = get_userdata( $author_id );
											?>
												<a href="<?php echo st_get_author_posts_url( get_the_ID(), 70 ); ?>">
												<?php
												echo st_get_profile_avatar( $author_id, 60 );
												?>
												</a>
											</div>
											<div class="media-body">
												<h4 class="media-heading"><a href="<?php echo st_get_author_posts_url( get_the_ID(), 70 ); ?>" class="author-link"><?php echo TravelHelper::get_username( $author_id ); ?></a></h4>
												<p><?php echo sprintf( __( 'Member Since %s', 'traveler' ), date( 'Y', strtotime( $userdata->user_registered ) ) ) ?></p>
											</div>
											<?php
											$enable_inbox = st()->get_option( 'enable_inbox' );
											if ( $enable_inbox === 'on' ) {
												?>
													<div class="st_ask_question">
														<?php
														if ( ! is_user_logged_in() ) {
															?>
															<a href="" class="login btn btn-primary upper mt5" data-toggle="modal" data-target="#st-login-form"><?php echo __( 'Ask a Question', 'traveler' ); ?></a>
														<?php } else { ?>
															<a href="" id="btn-send-message-owner" class="btn-send-message-owner btn btn-primary upper mt5" data-id="<?php echo get_the_ID(); ?>"><?php echo __( 'Ask a Question', 'traveler' ); ?></a>
														<?php } ?>
													</div>
											<?php } ?>
										</div>
									</div>
									<?php echo st()->load_template( 'layouts/modern/common/single/information-contact' ); ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="container">
				<!-- Related -->
				<?php
				echo st()->load_template( 'layouts/modern/rental/single/item/related', '', [
					'current_calendar' => $current_calendar,
					'start'            => $start,
					'end'              => $end,
					'start'            => $start,
					'end'              => $end,
					'number_day'       => $number_day
				] );
				?>
				<!-- End Related -->
			</div>
		</div>
	<?php
	endwhile;
