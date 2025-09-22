<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 16-11-2018
 * Time: 8:47 AM
 * Since: 1.0.0
 * Updated: 1.0.0
 */
while ( have_posts() ) :
	the_post();
	$price        = STHotel::get_price();
	$post_id      = get_the_ID();
	$hotel_star   = (int) get_post_meta( $post_id, 'hotel_star', true );
	$address      = get_post_meta( $post_id, 'address', true );
	$review_rate  = STReview::get_avg_rate();
	$count_review = get_comment_count( $post_id )['approved'];
	$lat          = get_post_meta( $post_id, 'map_lat', true );
	$lng          = get_post_meta( $post_id, 'map_lng', true );
	$zoom         = get_post_meta( $post_id, 'map_zoom', true );

	$gallery       = get_post_meta( $post_id, 'gallery', true );
	$gallery_array = explode( ',', $gallery );
	$marker_icon   = st()->get_option( 'st_hotel_icon_map_marker', '' );
	?>
	<div id="st-content-wrapper">
		<?php st_breadcrumbs_new() ?>
		<div class="container">
			<div class="st-hotel-content">
				<div class="hotel-target-book-mobile">
					<div class="price-wrapper">
						<?php echo wp_kses( sprintf( __( 'from <span class="price">%s</span>', 'traveler' ), TravelHelper::format_money( $price ) ), [ 'span' => [ 'class' => [] ] ] ) ?>
					</div>
					<a href=""
						class="btn btn-mpopup btn-green"><?php echo esc_html__( 'Check Availability', 'traveler' ) ?></a>
				</div>
			</div>

			<!--Header-->
			<?php
				echo st()->load_template( 'layouts/modern/hotel/single/items/header', '', [
					'post_id'      => $post_id,
					'hotel_star'   => $hotel_star,
					'review_rate'  => $review_rate,
					'count_review' => $count_review,
					'address'      => $address,
					'lat'          => $lat,
					'lng'          => $lng,
					'zoom'         => $zoom,
					'marker_icon'  => $marker_icon,
				] );
			?>
			<!--End Header-->

			<div class="row">
				<div class="col-xs-12 col-md-9">
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
						<?php
					}
					?>
					<div class="st-hr"></div>
					<h2 class="st-heading-section"><?php echo esc_html__( 'Description', 'traveler' ) ?>
						<a href="#" class="pull-right toggle-section"
							data-target="st-description">
							<i class="fa fa-angle-up"></i>
						</a>
					</h2>
					<div class="st-description" data-toggle-section="st-description" >
						<?php the_content(); ?>
					</div>
					<div class="st-hr large"></div>

					<!--Attribute-->
					<?php echo st()->load_template( 'layouts/modern/hotel/single/items/attributes' ); ?>
					<!--End Attribute-->

					<!--Rules-->
					<h2 class="st-heading-section"><?php echo esc_html__( 'Rules', 'traveler' ) ?>
						<a href="#" class="pull-right toggle-section"
							data-target="st-properties"><i
									class="fa fa-angle-up"></i>
						</a>
					</h2>
					<?php echo st()->load_template( 'layouts/modern/hotel/single/items/rules' ); ?>
					<!--End Rules-->
					<div class="st-hr large"></div>

					<!--List Room-->
					<h2 class="st-heading-section"><?php echo esc_html__( 'Rooms', 'traveler' ) ?>
						<a href="#" class="pull-right toggle-section" data-target="st-list-rooms">
							<i class="fa fa-angle-up"></i>
						</a>
					</h2>
					<?php echo st()->load_template( 'layouts/modern/hotel/single/items/list-room' ); ?>
					<!--End List Room-->

					<!--Review-->
					<?php if ( comments_open() && st()->get_option( 'hotel_review' ) == 'on' ) { ?>
						<div class="st-hr large"></div>
						<h2 class="st-heading-section"><?php echo esc_html__( 'Reviews', 'traveler' ) ?>
							<a href="#" class="pull-right toggle-section" data-target="st-reviews">
								<i class="fa fa-angle-up"></i>
							</a>
						</h2>
						<?php
						echo st()->load_template( 'layouts/modern/hotel/single/items/review/review', '', [
							'count_review' => $count_review,
						] );
						?>
					<?php } ?>
					<!--End Review-->
					<div class="stoped-scroll-section"></div>
				</div>
				<div class="col-xs-12 col-md-3">
					<div class="widgets">
						<div class="fixed-on-mobile" data-screen="992px">
							<div class="close-icon hide">
								<?php echo TravelHelper::getNewIcon( 'Ico_close' ); ?>
							</div>
							<div class="form-book-wrapper relative">
								<div class="form-head">
									<?php
									if ( STHotel::is_show_min_price() ) :
										_e( 'from', 'traveler' );
									else :
										_e( 'avg', 'traveler' );
									endif;
									?>
									<?php echo wp_kses( sprintf( __( ' <span class="price">%s</span> <span class="unit"> /night</span>', 'traveler' ), TravelHelper::format_money( $price ) ), [ 'span' => [ 'class' => [] ] ] ) ?>
								</div>

								<!--Form-booking-->
								<?php echo st()->load_template( 'layouts/modern/hotel/single/items/form-booking' ); ?>
								<!--End Form-booking-->
							</div>

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
										<h4 class="media-heading"><a
													href="<?php echo st_get_author_posts_url( get_the_ID(), 70 ); ?>"
													class="author-link"><?php echo TravelHelper::get_username( $author_id ); ?></a>
										</h4>
										<p><?php echo sprintf( __( 'Member Since %s', 'traveler' ), ! empty( $userdata->user_registered ) ? date( 'Y', strtotime( $userdata->user_registered ) ) : '' ) ?></p>
									</div>
									<!--
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
										<?php } ?> -->
								</div>
							</div>
							<?php echo st()->load_template( 'layouts/modern/common/single/information-contact' ); ?>
						</div>
					</div>
				</div>
			</div>
			<?php
			$st_show_hotel_nearby = st()->get_option( 'st_show_hotel_nearby', 'off' );
			if ( $st_show_hotel_nearby == 'on' ) {
				global $post;
				$hotel        = new STHotel();
				$nearby_posts = $hotel->get_near_by();
				if ( $nearby_posts ) {
					?>
					<div class="st-hr x-large"></div>
					<h2 class="st-heading text-center"><?php echo __( 'Hotel Nearby', 'traveler' ) ?></h2>
					<div class="services-grid services-nearby hotel-nearby grid mt50">
						<div class="row">
							<?php
							foreach ( $nearby_posts as $key => $post ) {
								setup_postdata( $post );
								$hotel_star  = (int) get_post_meta( get_the_ID(), 'hotel_star', true );
								$price       = STHotel::get_price();
								$address     = get_post_meta( get_the_ID(), 'address', true );
								$review_rate = STReview::get_avg_rate();
								$is_featured = get_post_meta( get_the_ID(), 'is_featured', true );
								?>
									<div class="col-xs-12 col-sm6 col-md-3">
										<div class="item">
											<div class="featured-image">
												<?php
												if ( $is_featured == 'on' ) {
													?>
													<div class="featured"><?php echo __( 'Featured', 'traveler' ) ?></div>
													<?php
												}
												?>
												<a href="<?php the_permalink(); ?>">
													<img src="<?php echo get_the_post_thumbnail_url( get_the_ID(), 'large' ); ?>"
														alt="" class="img-responsive img-full">
												</a>
												<?php echo st()->load_template( 'layouts/modern/common/star', '', [ 'star' => $hotel_star ] ); ?>
											</div>
											<h3 class="title">
												<a href="<?php the_permalink(); ?>" class="st-link c-main">
													<?php the_title(); ?>
												</a>
											</h3>
											<div class="sub-title">
												<?php
												if ( $address ) {
													echo TravelHelper::getNewIcon( 'ico_maps_search_box', '', '10px' );
													echo esc_html( $address );
												}
												?>
											</div>
											<div class="reviews">
												<span class="rate"><?php echo esc_attr( $review_rate ); ?>/5
												<?php echo TravelHelper::get_rate_review_text( $review_rate, $count_review ); ?></span><span
												class="summary"><?php comments_number( __( '0 review', 'traveler' ), __( '1 review', 'traveler' ), __( '% reviews', 'traveler' ) ); ?></span>
											</div>
											<div class="price-wrapper">
												<?php
												if ( STHotel::is_show_min_price() ) :
													_e( 'from', 'traveler' );
												else :
													_e( 'avg', 'traveler' );
												endif;
												?>
												<?php echo wp_kses( sprintf( __( ' <span class="price">%s</span><span class="unit">/night</span>', 'traveler' ), TravelHelper::format_money( $price ) ), [ 'span' => [ 'class' => [] ] ] ); ?>
											</div>
										</div>
									</div>
									<?php
							}
							wp_reset_query();
							wp_reset_postdata();
							?>
						</div>
					</div>
					<?php
				}
			}
			?>
		</div>
	</div>
	<?php
endwhile;
