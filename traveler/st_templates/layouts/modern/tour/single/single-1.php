<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 20-12-2018
 * Time: 1:55 PM
 * Since: 1.0.0
 * Updated: 1.0.0
 */
while ( have_posts() ) :
	the_post();
	$post_id                        = get_the_ID();
	$address                        = get_post_meta( $post_id, 'address', true );
	$review_rate                    = STReview::get_avg_rate();
	$count_review                   = STReview::count_review( $post_id );
	$lat                            = get_post_meta( $post_id, 'map_lat', true );
	$lng                            = get_post_meta( $post_id, 'map_lng', true );
	$zoom                           = get_post_meta( $post_id, 'map_zoom', true );
	$enable_street_views_google_map = get_post_meta( $post_id, 'enable_street_views_google_map', true );
	$gallery                        = get_post_meta( $post_id, 'gallery', true );
	$gallery_array                  = explode( ',', $gallery );
	$marker_icon                    = st()->get_option( 'st_tours_icon_map_marker', '' );
	$tour_external                  = get_post_meta( get_the_ID(), 'st_tour_external_booking', true );
	$tour_external_link             = get_post_meta( get_the_ID(), 'st_tour_external_booking_link', true );
	$booking_type                   = st_get_booking_option_type();
	$map_iframe                     = get_post_meta( $post_id, 'map_iframe', true );
	$is_iframe                      = get_post_meta( get_the_ID(), 'is_iframe', true );
	$tour_type                      = get_post_meta( get_the_ID(), 'type_tour', true );
	?>
	<div id="st-content-wrapper" class="st-single-tour">
		<?php st_breadcrumbs_new() ?>
		<div class="hotel-target-book-mobile">
			<div class="price-wrapper">
				<?php echo wp_kses( sprintf( __( 'from <span class="price">%s</span>', 'traveler' ), STTour::get_price_html( get_the_ID() ) ), [ 'span' => [ 'class' => [] ] ] ) ?>
			</div>
			<?php
			if ( $tour_external == 'off' || empty( $tour_external ) ) {
				?>
				<a href=""
					class="btn btn-mpopup btn-green">
					<?php
					if ( $booking_type == 'enquire' ) {
						echo esc_html__( 'Inquiry', 'traveler' );
					} else {
						echo esc_html__( 'Book Now', 'traveler' );
					}
					?>
				</a>
				<?php
			} else {
				?>
				<a href=""
					class="btn btn-mpopup btn-green"><?php echo esc_html__( 'Book Now', 'traveler' ) ?></a>
				<?php
			}
			?>
		</div>
		<?php
		if ( has_post_thumbnail() ) {
			$url = get_the_post_thumbnail_url( $post_id, 'full' );
			?>
			<div class="tour-featured-image featured-image-background"
				style="background-image: url('<?php echo esc_url( $url ); ?>')">
				<div class="container">
					<div class="st-gallery">
						<div class="shares dropdown">
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
						<div class="btn-group">
							<?php
							$video_url = get_post_meta( get_the_ID(), 'video', true );
							if ( ! empty( $video_url ) ) {
								?>
								<a href="<?php echo esc_url( $video_url ); ?>"
									class="btn btn-transparent has-icon radius st-video-popup"><?php echo TravelHelper::getNewIcon( 'video-player', '#FFFFFF', '18px', '18px' ) ?><?php echo __( 'Tour Video', 'traveler' ) ?></a>
							<?php } ?>
							<?php
							if ( ! empty( $gallery ) ) {
								?>
									<a href="#st-gallery-popup"
										class="btn btn-transparent has-icon radius st-gallery-popup"><?php echo TravelHelper::getNewIcon( 'camera-retro', '#FFFFFF', '18px', '18px' ) ?><?php echo __( 'More Photos', 'traveler' ) ?></a>

								<?php
							}
							?>

							<div id="st-gallery-popup" class="hidden">
								<?php
								if ( ! empty( $gallery_array ) ) {
									foreach ( $gallery_array as $k => $v ) {
										if ( ! empty( $v ) ) {
											echo '<a href="' . wp_get_attachment_image_url( $v, 'full' ) . '">' . __( 'Image', 'traveler' ) . '</a>';
										}
									}
								}
								?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<div class="st-tour-content">
			<div class="container">
				<div class="row">
					<div class="col-xs-12 col-md-9">
						<div class="st-hotel-header">
							<div class="left">
								<h1 class="st-heading"><?php the_title(); ?></h1>
								<div class="sub-heading">
									<?php
									if ( $address ) {
										echo TravelHelper::getNewIcon( 'ico_maps_add_2', '#5E6D77', '16px', '16px' );
										echo esc_html( $address );
									}
									?>
								</div>
							</div>
							<div class="right">
								<div class="review-score style-2">
									<span class="head-rating"><?php echo TravelHelper::get_rate_review_text( $review_rate, $count_review ); ?></span>
									<?php
									echo st()->load_template( 'layouts/modern/common/star', '', [
										'star'  => $review_rate,
										'style' => 'style-2',
									] );
									?>
									<p class="st-link"><?php comments_number( __( 'from 0 review', 'traveler' ), __( 'from 1 review', 'traveler' ), __( 'from % reviews', 'traveler' ) ); ?></p>
								</div>
							</div>
						</div>

						<!--Tour Info-->
						<?php echo st()->load_template( 'layouts/modern/tour/single/items/infor' ); ?>
						<!--End Tour info-->

						<!--Tour Overview-->
						<?php echo st()->load_template( 'layouts/modern/tour/single/items/over-view' ); ?>
						<!--End Tour Overview-->

						<!--Tour highlight-->
						<?php echo st()->load_template( 'layouts/modern/tour/single/items/highlight' ); ?>
						<!--End Tour highlight-->

						<!--Table Discount group -->
						<?php echo st()->load_template( 'layouts/modern/tour/single/items/discount' ); ?>
						<!--End Table Discount group -->

						<!--Tour program-->
						<?php
						$tour_program_style = get_post_meta( get_the_ID(), 'tours_program_style', true );
						if ( empty( $tour_program_style ) ) {
							$tour_program_style = 'style1';
						}
						if ( $tour_program_style == 'style1' or $tour_program_style == 'style3' ) {
							$tour_programs = get_post_meta( get_the_ID(), 'tours_program', true );
						} else {
							$tour_programs = get_post_meta( get_the_ID(), 'tours_program_bgr', true );
						}
						if ( ! empty( $tour_programs ) ) {
							?>
							<div class="st-program">
								<div class="st-title-wrapper">
									<h3 class="st-section-title"><?php echo __( 'Itinerary', 'traveler' ); ?></h3>
									<?php if ( $tour_program_style == 'style1' ) { ?>
										<span class="expand" data-ex="1"
												data-text-more="<?php echo __( 'Expand All', 'traveler' ); ?>"
												data-text-less="<?php echo __( 'Collapse All', 'traveler' ); ?>"><?php echo __( 'Expand All', 'traveler' ); ?></span>
									<?php } ?>
								</div>

								<div class="st-program-list <?php echo esc_attr( $tour_program_style ); ?>">
									<?php
									$tour_program_style = get_post_meta( get_the_ID(), 'tours_program_style', true );
									if ( empty( $tour_program_style ) ) {
										$tour_program_style = 'style1';
									}
									echo st()->load_template( 'layouts/modern/tour/single/items/itenirary/' . esc_attr( $tour_program_style ) );
									?>
								</div>
							</div>
						<?php } ?>
						<!--End Tour program-->

						<!--Tour Include/Exclude-->
						<?php echo st()->load_template( 'layouts/modern/tour/single/items/include-exclude' ); ?>
						<!--End Tour Include/Exclude-->

						<?php echo st()->load_template( 'layouts/modern/tour/single/items/attribute' ); ?>

						<!--Tour Map-->
						<?php
						echo st()->load_template( 'layouts/modern/tour/single/items/map', '', [
							'post_id'                        => $post_id,
							'lat'                            => $lat,
							'lng'                            => $lng,
							'zoom'                           => $zoom,
							'address'                        => $address,
							'marker_icon'                    => $marker_icon,
							'map_iframe'                     => $map_iframe,
							'is_iframe'                      => $is_iframe,
							'enable_street_views_google_map' => $enable_street_views_google_map
						] );
						?>
						<!--End Tour Map-->

						<!--Tour FAQ-->
						<?php echo st()->load_template( 'layouts/modern/tour/single/items/faq' ); ?>
						<!--End Tour FAQ-->

						<!--Review Option-->
						<?php echo st()->load_template( 'layouts/modern/tour/single/items/review' ); ?>
						<!--End Review Option-->
						<div class="stoped-scroll-section"></div>
					</div>
					<div class="col-xs-12 col-md-3">
						<?php
						$info_price = STTour::get_info_price();
						?>
						<div class="widgets">
							<div class="fixed-on-mobile" id="booking-request" data-screen="992px">
								<div class="close-icon hide">
									<?php echo TravelHelper::getNewIcon( 'Ico_close' ); ?>
								</div>

								<?php
								if ( $booking_type == 'instant_enquire' ) {
									echo st()->load_template('layouts/modern/tour/single/items/form-booking', 'instant-enquire', [
										'info_price'         => $info_price,
										'tour_type'          => $tour_type,
										'tour_external'      => $tour_external,
										'tour_external_link' => $tour_external_link
									] );
								} elseif ( $booking_type == 'enquire' ) {
									echo st()->load_template('layouts/modern/tour/single/items/form-booking', 'enquire', [
										'info_price' => $info_price
									] );
								} else {
									echo st()->load_template('layouts/modern/tour/single/items/form-booking', 'instant', [
										'info_price'         => $info_price,
										'tour_type'          => $tour_type,
										'tour_external'      => $tour_external,
										'tour_external_link' => $tour_external_link
									] );
								}
								?>

								<?php
								$allow_partner = st()->get_option( 'setting_partner', 'off' );
								if ( $allow_partner == 'on' ) {
									$list_badges = get_post_meta( get_the_ID(), 'list_badges', true );
									if ( ! empty( $list_badges ) ) {
										echo st()->load_template( 'layouts/modern/common/single/badge', '', [ 'list_badges' => $list_badges ] );
									}
									?>
									<div class="owner-info widget-box">
										<h4 class="heading"><?php echo __( 'Organized by', 'traveler' ) ?></h4>
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
												<h4 class="media-heading"><a
															href="<?php echo st_get_author_posts_url( get_the_ID(), 70 ); ?>"
															class="author-link"><?php echo TravelHelper::get_username( $author_id ); ?></a>
												</h4>
												<p><?php echo sprintf( __( 'Member Since %s', 'traveler' ), date( 'Y', strtotime( $userdata->user_registered ) ) ) ?></p>
												<?php
												$arr_service = STUser_f::getListServicesAuthor( $userdata );
												$review_data = STUser_f::getReviewsDataAuthor( $arr_service, $userdata );
												if ( ! empty( $review_data ) ) {
													$avg_rating = STUser_f::getAVGRatingAuthor( $review_data );
													?>
													<div class="author-review-box">
														<div class="author-start-rating">
															<div class="stm-star-rating">
																<div class="inner">
																	<div class="stm-star-rating-upper"
																		style="width:<?php echo (float) $avg_rating / 5 * 100; ?>%;"></div>
																	<div class="stm-star-rating-lower"></div>
																</div>
															</div>
														</div>
														<p class="author-review-label">
															<?php printf( __( '%d Reviews', 'traveler' ), count( $review_data ) ); ?>
														</p>
													</div>
													<?php
												}
												?>
											</div>
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
									<?php echo st()->load_template( 'layouts/modern/common/single/information-contact' ); ?>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
				<!--Related-->
				<?php echo st()->load_template( 'layouts/modern/tour/single/items/related' ); ?>
				<!--End Related-->
			</div>
		</div>
	</div>
	<?php
endwhile;
