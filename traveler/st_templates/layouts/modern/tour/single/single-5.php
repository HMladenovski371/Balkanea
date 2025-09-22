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
	<div id="st-content-wrapper" class="st-single-tour style-2 st-single-tour-new">
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
					class="btn btn-mpopup btn-green"><?php echo esc_html__( 'Explore', 'traveler' ) ?></a>
				<?php
			}
			?>
		</div>
		<div class="st-tour-content">
			<div class="container">
				<div class="row">
					<div class="col-xs-12 col-sm-8 col-md-9">
						<div class="st-hotel-header">
							<div class="left">
								<h2 class="st-heading"><?php the_title(); ?></h2>
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
									<?php
									$avg = STReview::get_avg_rate();
									?>
									<div class="review-score-item">
										<?php
										echo st()->load_template( 'layouts/modern/common/star', '', [
											'star'  => $review_rate,
											'style' => 'style-2',
										] );
										?>
										<span class="per-total"><?php echo esc_attr( $avg ); ?>/5</span>
									</div>
									<p class="st-link style-2"><?php comments_number( esc_html__( 'from 0 review', 'traveler' ), esc_html__( 'from 1 review', 'traveler' ), esc_html__( 'from % reviews', 'traveler' ) ); ?></p>
								</div>
							</div>
						</div>

						<!--Tour Info-->
						<?php echo st()->load_template('layouts/modern/tour/single/items/infor'); ?>
						<!--End Tour info-->
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
									<?php
									$video_url = get_post_meta( get_the_ID(), 'video', true );
									if ( ! empty( $video_url ) ) {
										?>
										<a href="<?php echo esc_url( $video_url ); ?>"
											class="st-video-popup share-item"><?php echo TravelHelper::getNewIcon( 'video-player', '#FFFFFF', '20px', '20px' ) ?></a>
									<?php } ?>
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
						<!--Tour Overview-->
						<?php echo st()->load_template('layouts/modern/tour/single/items/over-view'); ?>
						<!--End Tour Overview-->

						<!--Tour highlight-->
						<?php echo st()->load_template('layouts/modern/tour/single/items/highlight'); ?>
						<!--End Tour highlight-->

						<!--Table Discount group -->
						<?php echo st()->load_template('layouts/modern/tour/single/items/discount'); ?>
						<!--End Table Discount group -->

						<!--Tour program-->
						<div class="st-program">
							<?php
							$tour_program_style = get_post_meta( get_the_ID(), 'tours_program_style', true );
							if ( empty( $tour_program_style ) ) {
								$tour_program_style = 'style1';
							}
							?>
							<div class="st-title-wrapper">
								<h3 class="st-section-title"><?php echo esc_html__( 'Itinerary', 'traveler' ); ?></h3>
							</div>
							<div class="st-program-list <?php echo esc_attr( $tour_program_style ); ?>">
								<?php
								echo st()->load_template( 'layouts/modern/tour/single/items/itenirary/' . esc_attr( $tour_program_style ) );
								?>
							</div>
						</div>
						<!--End Tour program-->

						<!--Tour Include/Exclude-->
						<?php echo st()->load_template('layouts/modern/tour/single/items/include-exclude'); ?>
						<!--End Tour Include/Exclude-->

						<?php echo st()->load_template('layouts/modern/tour/single/items/attribute'); ?>

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
						<?php echo st()->load_template('layouts/modern/tour/single/items/faq'); ?>
						<!--End Tour FAQ-->

						<!--Review Option-->
						<?php echo st()->load_template('layouts/modern/tour/single/items/review'); ?>
						<!--End Review Option-->
						<div class="stoped-scroll-section"></div>
					</div>
					<div class="col-xs-12 col-sm-4 col-md-3">
						<?php
						$info_price = STTour::get_info_price();
						?>
						<div class="widgets style2">
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
										'info_price' => $info_price,
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
								$list_badges = get_post_meta(get_the_ID(), 'list_badges', true);
								if(!empty($list_badges)){
									echo st()->load_template('layouts/modern/common/single/badge','', array('list_badges' => $list_badges));
								}?>
							</div>
						</div>
					</div>
				</div>
				<!--Related-->
				<?php echo st()->load_template('layouts/modern/tour/single/items/related'); ?>
				<!--End Related-->
			</div>
		</div>
	</div>
	<?php
endwhile;
