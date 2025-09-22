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
	$count_review                   = get_comment_count( $post_id )['approved'];
	$lat                            = get_post_meta( $post_id, 'map_lat', true );
	$lng                            = get_post_meta( $post_id, 'map_lng', true );
	$zoom                           = get_post_meta( $post_id, 'map_zoom', true );
	$enable_street_views_google_map = get_post_meta( $post_id, 'enable_street_views_google_map', true );
	$gallery                        = get_post_meta( $post_id, 'gallery', true );
	$gallery_array                  = explode( ',', $gallery );
	$marker_icon                    = st()->get_option( 'st_activity_icon_map_marker', '' );

	$activity_external      = get_post_meta( get_the_ID(), 'st_activity_external_booking', true );
	$activity_external_link = get_post_meta( get_the_ID(), 'st_activity_external_booking_link', true );

	$booking_type                   = st_get_booking_option_type();
	$icon_duration_single_activity  = st()->get_option( 'icon_duration_single_activity', '<i class="lar la-clock"></i>' );
	$icon_cancel_single_activity    = st()->get_option( 'icon_cancel_single_activity', '<i class="las la-ban"></i>' );
	$icon_groupsize_single_activity = st()->get_option( 'icon_groupsize_single_activity', '<i class="las la-user-friends"></i>' );
	$icon_language_single_activity  = st()->get_option( 'icon_language_single_activity', '<i class="las la-language"></i>' );
	?>
		<div id="st-content-wrapper" class="st-single-tour style-2">
		<?php st_breadcrumbs_new() ?>
			<div class="hotel-target-book-mobile">
				<div class="price-wrapper">
				<?php echo wp_kses( sprintf( __( 'from <span class="price">%s</span>', 'traveler' ), STActivity::inst()->get_price_html( get_the_ID() ) ), [ 'span' => [ 'class' => [] ] ] ) ?>
				</div>
			<?php
			if ( $activity_external == 'off' || empty( $activity_external ) ) {
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
					<a href=""
						class="btn btn-mpopup btn-green"><?php echo esc_html__( 'Explore', 'traveler' ) ?></a>
					<?php
			}
			?>
			</div>
			<div class="st-tour-content">
				<div class="container">
					<div class="row">
						<div class="col-xs-12 col-sm-8 col-md-9">
							<!--Header-->
							<?php
							echo st()->load_template('layouts/modern/activity/single/items/header', '', [
								'address'      => $address,
								'review_rate'  => $review_rate,
								'count_review' => $count_review
							]);
							?>
							<!--End Header-->

							<!--Activity Info-->
							<?php
							echo st()->load_template('layouts/modern/activity/single/items/info', '', [
								'icon_duration_single_activity'  => $icon_duration_single_activity,
								'icon_cancel_single_activity'    => $icon_cancel_single_activity,
								'icon_groupsize_single_activity' => $icon_groupsize_single_activity,
								'icon_language_single_activity'  => $icon_language_single_activity
							]);
							?>
							<!--End Activity info-->

							<?php
							if ( ! empty( $gallery_array ) ) {
								?>
									<div class="st-gallery" data-width="100%"
										data-nav="thumbs" data-allowfullscreen="true">
										<div class="fotorama" data-auto="false">
											<?php
											foreach ( $gallery_array as $value ) {
												?>
													<img src="<?php echo wp_get_attachment_image_url( $value, [ 870, 555 ] ) ?>" alt="<?php echo get_the_title(); ?>">
													<?php
											}
											?>
										</div>
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
									</div>
									<?php
							}
							?>
							<!--Activity Overview-->
							<?php echo st()->load_template('layouts/modern/activity/single/items/over-view'); ?>
							<!--End Activity Overview-->

							<!--Activity highlight-->
							<?php echo st()->load_template('layouts/modern/activity/single/items/highlight'); ?>
							<!--End Activity highlight-->

							<!--Table Discount group -->
							<?php echo st()->load_template('layouts/modern/activity/single/items/discount'); ?>
							<!--End Table Discount group -->

							<!--Activity program-->
							<?php echo st()->load_template('layouts/modern/activity/single/items/program'); ?>
							<!--End Activity program-->

							<!--Activity Include/Exclude-->
							<?php echo st()->load_template('layouts/modern/activity/single/items/include-exclude'); ?>
							<!--End Activity Include/Exclude-->

							<!--Activity Attribute-->
							<?php echo st()->load_template('layouts/modern/activity/single/items/attribute'); ?>
							<!--End Activity Attribute---->

							<!--Activity Map-->
							<div class="st-hr large st-height2"></div>
							<?php
							echo st()->load_template('layouts/modern/activity/single/items/map', '', [
								'lat'                            => $lat,
								'lng'                            => $lng,
								'zoom'                           => $zoom,
								'address'                        => $address,
								'marker_icon'                    => $marker_icon,
								'enable_street_views_google_map' => $enable_street_views_google_map
							]);
							?>
							<!--End Activity Map-->

							<!--Activity FAQ-->
							<?php echo st()->load_template('layouts/modern/activity/single/items/faq'); ?>
							<!--End Activity FAQ-->

							<!--Review Option-->
							<?php echo st()->load_template('layouts/modern/activity/single/items/review'); ?>
							<!--End Review Option-->
							<div class="stoped-scroll-section"></div>
						</div>
						<div class="col-xs-12 col-sm-4 col-md-3">
							<?php
							$info_price = STActivity::inst()->get_info_price();
							?>
							<div class="widgets">
								<div class="fixed-on-mobile" id="booking-request" data-screen="992px">
									<div class="close-icon hide">
									<?php echo TravelHelper::getNewIcon( 'Ico_close' ); ?>
									</div>

									<?php
									if ( $booking_type == 'instant_enquire' ) {
										echo st()->load_template('layouts/modern/activity/single/items/form-booking', 'instant-enquire', [
											'info_price' => $info_price,
											'activity_external' => $activity_external,
											'activity_external_link' => $activity_external_link
										] );
									} elseif ( $booking_type == 'enquire' ) {
										echo st()->load_template('layouts/modern/activity/single/items/form-booking', 'enquire', [
											'info_price' => $info_price
										] );
									} else {
										echo st()->load_template('layouts/modern/activity/single/items/form-booking', 'instant', [
											'info_price' => $info_price,
											'activity_external' => $activity_external,
											'activity_external_link' => $activity_external_link
										] );
									}
									?>

									<?php
									$list_badges = get_post_meta(get_the_ID(), 'list_badges', true);
										if(!empty($list_badges)){
											echo st()->load_template('layouts/modern/common/single/badge','', array('list_badges' => $list_badges));
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
												<h4 class="media-heading"><a href="<?php echo st_get_author_posts_url( get_the_ID(), 70 ); ?>" class="author-link"><?php echo TravelHelper::get_username( $author_id ); ?></a></h4>
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

					<!--Related-->
					<?php echo st()->load_template('layouts/modern/activity/single/items/related'); ?>
					<!--End Related-->
				</div>
			</div>
		</div>
	<?php
	endwhile;
