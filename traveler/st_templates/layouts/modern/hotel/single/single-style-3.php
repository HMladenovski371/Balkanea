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
			<div class="st-hotel-map-area clearfix">
			<?php
			if ( ! empty( $gallery_array ) ) {
				?>
				<div class="st-gallery" data-nav="false" data-width="100%"
					data-allowfullscreen="true">
					<div class="fotorama" data-auto="false">
						<?php
						foreach ( $gallery_array as $value ) {
							?>
							<img src="<?php echo wp_get_attachment_image_url( $value, [ 1108, 600 ] ) ?>" alt="<?php echo get_the_title(); ?>">
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

			if ( ! wp_is_mobile() ) {
				$default = apply_filters('st_hotel_property_near_by_params', [
					'number'      => '12',
					'range'       => '50',
					'show_circle' => 'no',
				]);
				extract( $default );
				$hotel           = new STHotel();
				$location_center = '[' . $lat . ',' . $lng . ']';
				$map_lat_center  = $lat;
				$map_lng_center  = $lng;

				$map_icon = st()->get_option( 'st_hotel_icon_map_marker', '' );
				$map_icon = get_template_directory_uri() . '/v2/images/markers/ico_mapker_hotel.png';
				if ( empty( $map_icon ) ) {
					$map_icon = get_template_directory_uri() . '/v2/images/markers/ico_mapker_hotel.png';
				}

				$data_map = [];
				global $post;
				if ( st()->get_option( 'st_show_hotel_nearby' ) == 'on' ) {
					$data = $hotel->get_near_by( get_the_ID(), $range, $number );
					if ( ! empty( $data ) ) {
						$stt = 1;
						foreach ( $data as $post ) :
							setup_postdata( $post );
							$map_lat = get_post_meta( get_the_ID(), 'map_lat', true );
							$map_lng = get_post_meta( get_the_ID(), 'map_lng', true );
							if ( ! empty( $map_lat ) and ! empty( $map_lng ) and is_numeric( $map_lat ) and is_numeric( $map_lng ) ) {
								$data_val                         = [
									'id'          => get_the_ID(),
									'post_id'     => get_the_ID(),
									'name'        => get_the_title(),
									'description' => '',
									'lat'         => (float) $map_lat,
									'lng'         => (float) $map_lng,
									'icon_mk'     => $map_icon,
									'featured'    => get_the_post_thumbnail_url( get_the_ID() ),
									'url'         => get_permalink( get_the_ID() ),
								];
								$post_type                        = get_post_type();
								$data_map[ $stt ]['id']           = get_the_ID();
								$data_map[ $stt ]['name']         = get_the_title();
								$data_map[ $stt ]['post_type']    = $post_type;
								$data_map[ $stt ]['lat']          = $map_lat;
								$data_map[ $stt ]['lng']          = $map_lng;
								$data_map[ $stt ]['icon_mk']      = $map_icon;
								$data_map[ $stt ]['content_html'] = preg_replace( '/^\s+|\n|\r|\s+$/m', '', st()->load_template( 'layouts/modern/hotel/elements/property', false, [ 'data' => $data_val ] ) );
								++$stt;
							}
							endforeach;
						wp_reset_postdata();
					}
				}

				$properties = $hotel->properties_near_by( get_the_ID(), $lat, $lng, $range );
				if ( ! empty( $properties ) ) {
					foreach ( $properties as $key => $val ) {
						$data_map[] = [
							'id'           => get_the_ID(),
							'name'         => $val['name'],
							'post_type'    => 'st_hotel',
							'lat'          => (float) $val['lat'],
							'lng'          => (float) $val['lng'],
							'icon_mk'      => ( empty( $val['icon'] ) ) ? 'http://maps.google.com/mapfiles/marker_black.png' : $val['icon'],
							'content_html' => preg_replace( '/^\s+|\n|\r|\s+$/m', '', st()->load_template( 'layouts/modern/hotel/elements/property', false, [ 'data' => $val ] ) ),

						];
					}
				}

				$data_map_origin = [];
				$data_map_origin = [
					'id'          => $post_id,
					'post_id'     => $post_id,
					'name'        => get_the_title(),
					'description' => '',
					'lat'         => $lat,
					'lng'         => $lng,
					'icon_mk'     => $map_icon,
					'featured'    => get_the_post_thumbnail_url( $post_id ),
				];
				$data_map[]      = [
					'id'           => $post_id,
					'name'         => get_the_title(),
					'post_type'    => 'st_hotel',
					'lat'          => $lat,
					'lng'          => $lng,
					'icon_mk'      => $map_icon,
					'content_html' => preg_replace( '/^\s+|\n|\r|\s+$/m', '', st()->load_template( 'layouts/modern/hotel/elements/property', false, [ 'data' => $data_map_origin ] ) ),

				];

				$data_map = json_encode( $data_map, JSON_FORCE_OBJECT );
				?>
				<?php
				$google_api_key = st()->get_option( 'st_googlemap_enabled' );
				if ( $google_api_key === 'on' ) {
					?>
						<div class="st-map hidden-xs hidden-sm">
							<div class="google-map gmap3" id="list_map"
								data-data_show='<?php echo str_ireplace( [ "'" ], '\"', $data_map ); ?>'
								data-lat="<?php echo trim( $lat ) ?>"
								data-lng="<?php echo trim( $lng ) ?>"
								data-icon="<?php echo esc_url( $marker_icon ); ?>"
								data-zoom="<?php echo (int) $zoom; ?>" data-disablecontrol="true"
								data-showcustomcontrol="true"
								data-style="normal">
							</div>
						</div>
					<?php } else { ?>
						<div class="st-map-box hidden-xs hidden-sm">
							<div class="google-map-mapbox" data-lat="<?php echo trim( $lat ) ?>"
								data-data_show='<?php echo str_ireplace( [ "'" ], '\"', $data_map ); ?>'
								data-lng="<?php echo trim( $lng ) ?>"
								data-icon="<?php echo esc_url( $marker_icon ); ?>"
								data-zoom="<?php echo (int) $zoom; ?>" data-disablecontrol="true"
								data-showcustomcontrol="true"
								data-style="normal">
								<div id="st-map">
								</div>
							</div>
						</div>
					<?php } ?>
				<?php
			}
			?>

			</div>
			<div class="container">
				<div class="st-hotel-content">
					<div class="row">
						<div class="col-xs-12 col-md-9 ">
							<div class="st-hotel-header">
								<div class="left">
								<?php echo st()->load_template( 'layouts/modern/common/star', '', [ 'star' => $hotel_star ] ); ?>
									<h2 class="st-heading"><?php the_title(); ?></h2>
									<div class="sub-heading">
										<?php
										if ( $address ) {
											echo TravelHelper::getNewIcon( 'ico_maps_add_2', '#5E6D77', '16px', '16px' );
											echo esc_html( $address );
										}
										?>
										<a href="" class="st-link font-medium hidden-md hidden-lg" data-toggle="modal"
											data-target="#st-modal-show-map"> <?php echo esc_html__( 'View on map', 'traveler' ) ?></a>
										<?php
										if ( wp_is_mobile() ) {
											?>
											<div class="modal fade modal-map" id="st-modal-show-map" tabindex="-1" role="dialog"
												aria-labelledby="myModalLabel">
												<div class="modal-dialog">
													<div class="modal-content">
														<div class="modal-header">
															<button type="button" class="close" data-dismiss="modal" aria-label="Close">
																<?php echo TravelHelper::getNewIcon( 'Ico_close' ); ?></button>
															<div class="modal-title"><?php the_title(); ?></div>
														</div>
														<div class="modal-body">
															<?php
															$default = apply_filters('st_hotel_property_near_by_params', [
																'number'      => '12',
																'range'       => '50',
																'show_circle' => 'no',
															]);
															extract( $default );
															$hotel           = new STHotel();
															$location_center = '[' . $lat . ',' . $lng . ']';
															$map_lat_center  = $lat;
															$map_lng_center  = $lng;
															$map_icon        = st()->get_option( 'st_hotel_icon_map_marker', '' );
															if ( empty( $map_icon ) ) {
																$map_icon = get_template_directory_uri() . '/v2/images/markers/ico_mapker_hotel.png';
															}
															$data_map = [];
															$stt      = 1;
															global $post;
															if ( st()->get_option( 'st_show_hotel_nearby' ) == 'on' ) {
																$data = $hotel->get_near_by( get_the_ID(), $range, $number );
																if ( ! empty( $data ) ) {
																	foreach ( $data as $post ) :
																		setup_postdata( $post );
																		$map_lat = get_post_meta( get_the_ID(), 'map_lat', true );
																		$map_lng = get_post_meta( get_the_ID(), 'map_lng', true );
																		if ( ! empty( $map_lat ) and ! empty( $map_lng ) and is_numeric( $map_lat ) and is_numeric( $map_lng ) ) {
																			$data_val                         = [
																				'id' => get_the_ID(),
																				'post_id' => get_the_ID(),
																				'name' => get_the_title(),
																				'description' => '',
																				'lat' => (float) $map_lat,
																				'lng' => (float) $map_lng,
																				'icon_mk' => $map_icon,
																				'featured' => get_the_post_thumbnail_url( get_the_ID() ),
																				'url' => get_permalink( get_the_ID() ),
																			];
																			$post_type                        = get_post_type();
																			$data_map[ $stt ]['id']           = get_the_ID();
																			$data_map[ $stt ]['name']         = get_the_title();
																			$data_map[ $stt ]['post_type']    = $post_type;
																			$data_map[ $stt ]['lat']          = $map_lat;
																			$data_map[ $stt ]['lng']          = $map_lng;
																			$data_map[ $stt ]['icon_mk']      = $map_icon;
																			$data_map[ $stt ]['content_html'] = preg_replace( '/^\s+|\n|\r|\s+$/m', '', st()->load_template( 'layouts/modern/hotel/elements/property', false, [ 'data' => $data_val ] ) );

																			++$stt;
																		}
																	endforeach;
																	wp_reset_postdata();
																}
															}
															$properties = $hotel->properties_near_by( get_the_ID(), $lat, $lng, $range );
															if ( ! empty( $properties ) ) {
																foreach ( $properties as $key => $val ) {
																	$data_map[] = [
																		'id' => get_the_ID(),
																		'name' => $val['name'],
																		'post_type' => 'st_hotel',
																		'lat' => (float) $val['lat'],
																		'lng' => (float) $val['lng'],
																		'icon_mk' => ( empty( $val['icon'] ) ) ? 'http://maps.google.com/mapfiles/marker_black.png' : $val['icon'],
																		'content_html' => preg_replace( '/^\s+|\n|\r|\s+$/m', '', st()->load_template( 'layouts/modern/hotel/elements/property', false, [ 'data' => $val ] ) ),

																	];
																}
															}



															$data_map_origin = [];
															$data_map_origin = [
																'id' => $post_id,
																'post_id' => $post_id,
																'name' => get_the_title(),
																'description' => '',
																'lat' => $lat,
																'lng' => $lng,
																'icon_mk' => $map_icon,
																'featured' => get_the_post_thumbnail_url( $post_id ),
															];
															$data_map[]      = [
																'id' => $post_id,
																'name' => get_the_title(),
																'post_type' => 'st_hotel',
																'lat' => $lat,
																'lng' => $lng,
																'icon_mk' => $map_icon,
																'content_html' => preg_replace( '/^\s+|\n|\r|\s+$/m', '', st()->load_template( 'layouts/modern/hotel/elements/property', false, [ 'data' => $data_map_origin ] ) ),

															];

															$data_map = json_encode( $data_map, JSON_FORCE_OBJECT );
															?>
																<?php
																$google_api_key = st()->get_option( 'st_googlemap_enabled' );
																if ( $google_api_key === 'on' ) {
																	?>
																	<div class="st-map mt30">
																		<div class="google-map gmap3" id="list_map"
																			data-data_show='<?php echo str_ireplace( [ "'" ], '\"', $data_map ); ?>'
																			data-lat="<?php echo trim( $lat ) ?>"
																			data-lng="<?php echo trim( $lng ) ?>"
																			data-icon="<?php echo esc_url( $marker_icon ); ?>"
																			data-zoom="<?php echo (int) $zoom; ?>" data-disablecontrol="true"
																			data-showcustomcontrol="true"
																			data-style="normal">
																		</div>
																	</div>
																<?php } else { ?>
																	<div class="st-map-box mt30">
																		<div class="google-map-mapbox" data-lat="<?php echo trim( $lat ) ?>"
																			data-data_show='<?php echo str_ireplace( [ "'" ], '\"', $data_map ); ?>'
																			data-lng="<?php echo trim( $lng ) ?>"
																			data-icon="<?php echo esc_url( $marker_icon ); ?>"
																			data-zoom="<?php echo (int) $zoom; ?>" data-disablecontrol="true"
																			data-showcustomcontrol="true"
																			data-style="normal">
																			<div id="st-map">
																			</div>
																		</div>
																	</div>
																<?php } ?>
														</div>
														<script type="text/javascript">

														</script>
													</div>
												</div>
											</div>
											<?php
										}
										?>
									</div>
								</div>
								<div class="right">
									<div class="review-score">
										<div class="head clearfix">
											<div class="left">
												<span class="head-rating"><?php echo TravelHelper::get_rate_review_text( $review_rate, $count_review ); ?></span>
												<span class="text-rating"><?php comments_number( __( 'from 0 review', 'traveler' ), __( 'from 1 review', 'traveler' ), __( 'from % reviews', 'traveler' ) ); ?></span>
											</div>
											<div class="score">
											<?php echo esc_html( $review_rate ); ?><span>/5</span>
											</div>
										</div>
										<div class="foot">
											<?php echo esc_html__( '100% guests recommend', 'traveler' ) ?>
										</div>
									</div>
								</div>
							</div>
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
									<?php if ( comments_open() and st()->get_option( 'hotel_review' ) == 'on' ) { ?>
									<div role="tabpanel" class="tab-pane" id="reviews-tab">
										<?php
										echo st()->load_template( 'layouts/modern/hotel/single/items/review', '', [
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
							<h2 class="st-heading-section"><?php echo esc_html__( 'Rooms', 'traveler' ) ?>
								<a href="#" class="pull-right toggle-section" data-target="st-list-rooms">
									<i class="fa fa-angle-up"></i>
								</a>
							</h2>
							<?php echo st()->load_template( 'layouts/modern/hotel/single/items/list-room' ); ?>
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
								<?php echo st()->load_template( 'layouts/modern/common/single/information-contact' ); ?>
								<?php if ( comments_open() && st()->get_option( 'hotel_review' ) == 'on' ) { ?>
									<div class="widget-box review-box">
										<?php echo st()->load_template( 'layouts/modern/hotel/single/items/review/rating' ); ?>
									</div>
									<div class="widget-box review-box">
										<?php echo st()->load_template( 'layouts/modern/hotel/single/items/review/summary' ); ?>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
			$st_show_hotel_nearby = st()->get_option( 'st_show_hotel_nearby', 'off' );
			if ( $st_show_hotel_nearby == 'on' ) {
				?>
				<div class="container">
					<div class="st-hr x-large"></div>
					<h2 class="st-heading text-center"><?php echo __( 'Hotel Nearby', 'traveler' ) ?></h2>
					<div class="services-grid services-nearby hotel-nearby grid mt50">
						<div class="row">
							<?php
							global $post;
							$hotel        = new STHotel();
							$nearby_posts = $hotel->get_near_by();
							if ( $nearby_posts ) {
								foreach ( $nearby_posts as $key => $post ) {
									setup_postdata( $post );
									$hotel_star  = (int) get_post_meta( get_the_ID(), 'hotel_star', true );
									$price       = STHotel::get_price();
									$address     = get_post_meta( get_the_ID(), 'address', true );
									$review_rate = STReview::get_avg_rate();
									$is_featured = get_post_meta( get_the_ID(), 'is_featured', true );
									?>
										<div class="col-xs-12 col-sm-6 col-md-3">
											<div class="item">
												<div class="featured-image">
													<?php
													if ( $is_featured == 'on' ) {
														?>
														<div class="featured"><?php echo __( 'Featured', 'traveler' ) ?></div>
														<?php
													}
													?>
													<a href="<?php the_permalink() ?>">
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
							}
							?>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	<?php
	endwhile;
