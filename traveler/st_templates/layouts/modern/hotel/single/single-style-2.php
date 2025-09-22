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
						<div class="col-xs-12 col-md-9 ">
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
							<div class="st-tabs">
								<ul class="nav nav-tabs" role="tablist">
									<li role="presentation" class="active"><a href="#description-tab"
																				aria-controls="description-tab" role="tab"
																				data-toggle="tab"><?php echo __( 'Description', 'traveler' ) ?></a>
									</li>
									<li role="presentation"><a href="#facilities-tab" aria-controls="facilities-tab"
																role="tab"
																data-toggle="tab"><?php echo __( 'Facilities', 'traveler' ) ?></a>
									</li>
									<li role="presentation"><a href="#rules-tab" aria-controls="rules-tab" role="tab"
																data-toggle="tab"><?php echo __( 'Rules', 'traveler' ) ?></a>
									</li>
								<?php if ( comments_open() and st()->get_option( 'hotel_review' ) == 'on' ) { ?>
									<li role="presentation"><a href="#reviews-tab" aria-controls="reviews-tab"
																role="tab"
																data-toggle="tab"><?php echo __( 'Reviews', 'traveler' ) ?></a>
									</li>
									<?php } ?>
								</ul>
								<div class="tab-content">
									<div role="tabpanel" class="tab-pane active" id="description-tab">
										<div class="row">
											<div class="col-xs-12 col-sm-3 col-sm-push-9 col-md-4 col-md-push-8 hotel-logo">
											<?php
												$logo = get_post_meta( get_the_ID(), 'logo', true );
											if ( $logo ) {
												echo '<img src="' . esc_url( $logo ) . '" class="img-responsivve">';
											}
											?>
											</div>
											<div class="col-xs-12 col-sm-9 col-sm-pull-3 col-md-8 col-md-pull-4">
												<div class="st-description" data-toggle-section="st-description">
													<?php the_content(); ?>
												</div>
											</div>
										</div>
									</div>
									<div role="tabpanel" class="tab-pane" id="facilities-tab">
										<?php echo st()->load_template( 'layouts/modern/hotel/single/items/attributes' ); ?>
									</div>
									<div role="tabpanel" class="tab-pane" id="rules-tab">
										<?php echo st()->load_template( 'layouts/modern/hotel/single/items/rules' ); ?>
									</div>
									<?php if ( comments_open() && st()->get_option( 'hotel_review' ) == 'on' ) { ?>
									<div role="tabpanel" class="tab-pane" id="reviews-tab">
										<?php
										echo st()->load_template( 'layouts/modern/hotel/single/items/review/review', '', [
											'count_review' => $count_review,
										] );
										?>
									</div>
									<?php } ?>
								</div>
							</div>

							<?php
							$hotel_external      = get_post_meta( get_the_ID(), 'st_hotel_external_booking', true );
							$hotel_external_link = get_post_meta( get_the_ID(), 'st_hotel_external_booking_link', true );
							$class = '';
							if(empty($hotel_external) || $hotel_external == 'off') {
								$class = 'inline';
							}
							?>
							<div class="form-book-wrapper form-single-style-2 relative <?php echo $class ?>">
								<?php echo st()->load_template( 'layouts/modern/hotel/single/items/form-booking' ); ?>
							</div>

							<!--List Room-->
							<h2 class="st-heading-section"><?php echo esc_html__( 'Rooms', 'traveler' ) ?>
								<a href="#" class="pull-right toggle-section" data-target="st-list-rooms">
									<i class="fa fa-angle-up"></i>
								</a>
							</h2>
							<?php echo st()->load_template( 'layouts/modern/hotel/single/items/list-room' ); ?>
							<!--End List Room-->
						</div>
						<div class="col-xs-12 col-md-3 ">
							<div class="widgets">

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
								<?php
								$st_show_hotel_nearby = st()->get_option( 'st_show_hotel_nearby', 'off' );
								if ( $st_show_hotel_nearby == 'on' ) {
									?>
									<div class="widget-box blog default">
										<h2 class="heading"><?php echo __( 'RELATED HOTEL', 'traveler' ) ?></h2>
										<div class="related-services related-hotel">
											<?php
											global $post;
											$hotel        = new STHotel();
											$nearby_posts = $hotel->get_near_by();
											if ( $nearby_posts ) {
												foreach ( $nearby_posts as $key => $post ) {
													setup_postdata( $post );
													$hotel_star = (int) get_post_meta( get_the_ID(), 'hotel_star', true );
													$price      = STHotel::get_price();
													?>
														<div class="item">
															<div class="media">
																<div class="media-left">
																	<a href="<?php the_permalink() ?>">
																		<img class="media-object img-full"
																			src="<?php echo get_the_post_thumbnail_url( get_the_ID(), 'thumbnail' ); ?>">
																	</a>
																</div>
																<div class="media-body">
																<?php echo st()->load_template( 'layouts/modern/common/star', '', [ 'star' => $hotel_star ] ); ?>
																	<h4 class="media-heading"><a
																				href="<?php the_permalink(); ?>"
																				class="st-link c-main"><?php the_title(); ?></a>
																	</h4>
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
														</div>
														<div class="hr"></div>
														<?php
												}
												wp_reset_query();
												wp_reset_postdata();
											}
											?>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	<?php
	endwhile;
