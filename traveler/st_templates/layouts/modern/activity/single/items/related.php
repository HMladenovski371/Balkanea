<?php
$search_tax_advance = st()->get_option( 'attribute_search_form_activity', 'activity_types' );
$terms_posts        = wp_get_post_terms( get_the_ID(), $search_tax_advance );
$arr_id_term_post   = [];
foreach ( $terms_posts as $term_post ) {
	$arr_id_term_post[] = $term_post->term_id;
}
$args = [
	'posts_per_page' => 4,
	'post_type'      => 'st_activity',
	'post_author'    => get_post_field( 'post_author', get_the_ID() ),
	'post__not_in'   => [ get_the_id() ],
	'orderby'        => 'rand',
	'tax_query'      => [
		[
			'taxonomy' => $search_tax_advance,
			'terms'    => $arr_id_term_post,
			'field'    => 'term_id',
			'operator' => 'IN',
		],
	],
];
global $post;
$old_post = $post;
$query    = new WP_Query( $args );
if ( $query->have_posts() ) :
	?>
		<div class="st-hr large"></div>
		<h2 class="heading text-center f28 mt50"><?php echo esc_html__( 'You might also like', 'traveler' ) ?></h2>
		<div class="st-list-tour-related row mt50">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			$info_price = STActivity::inst()->get_info_price();
			$url        = st_get_link_with_search( get_permalink(), [ 'start' ], $_GET );
			?>
				<div class="col-xs-12 col-sm-6 col-md-3">
					<div class="item-service grid-item has-matchHeight">
						<div class="service-border">
							<div class="thumb">
								<?php if ( ! empty( $info_price['discount'] ) && $info_price['discount'] > 0 && $info_price['price_new'] > 0 ) { ?>
									<?php echo STFeatured::get_sale( $info_price['discount'] ); ?>
								<?php } ?>
								<?php if ( is_user_logged_in() ) { ?>
									<?php $data = STUser_f::get_icon_wishlist( 2 ); ?>
									<div class="service-add-wishlist login <?php echo ( $data['status'] ) ? 'added' : ''; ?>" data-id="<?php echo get_the_ID(); ?>" data-type="<?php echo get_post_type( get_the_ID() ); ?>" title="<?php echo ( $data['status'] ) ? __( 'Remove from wishlist', 'traveler' ) : __( 'Add to wishlist', 'traveler' ); ?>">
										<i class="fa fa-heart"></i>
										<div class="lds-dual-ring"></div>
									</div>
								<?php } else { ?>
									<a href="#" class="login" data-toggle="modal" data-target="#st-login-form">
										<div class="service-add-wishlist" title="<?php echo __( 'Add to wishlist', 'traveler' ); ?>">
											<i class="fa fa-heart"></i>
											<div class="lds-dual-ring"></div>
										</div>
									</a>
								<?php } ?>
								<div class="service-tag bestseller">
									<?php echo STFeatured::get_featured(); ?>
								</div>
								<a href="<?php echo esc_url( $url ); ?>">
									<?php
									if ( has_post_thumbnail() ) {
										the_post_thumbnail( [ 680, 500 ], [
											'alt'   => TravelHelper::get_alt_image(),
											'class' => 'img-responsive',
										] );
									} else {
										echo '<img src="' . get_template_directory_uri() . '/img/no-image.png' . '" alt="Default Thumbnail" class="img-responsive" />';
									}
									?>
								</a>
								<?php echo st_get_avatar_in_list_service( get_the_ID(), 70 ) ?>
							</div>
							<?php if ( $address = get_post_meta( get_the_ID(), 'address', true ) ) : ?>
								<p class="service-location plr15 st-flex justify-left"><?php echo TravelHelper::getNewIcon( 'Ico_maps', '#666666', '15px', '15px', true ); ?><span class="ml5"><?php echo esc_html( $address ); ?></span></p>
							<?php endif; ?>
							<h4 class="service-title plr15"><a href="<?php echo esc_url( $url ); ?>"><?php echo get_the_title(); ?></a></h4>

							<div class="service-review plr15">
								<ul class="icon-group text-color booking-item-rating-stars">
									<?php
									$avg = STReview::get_avg_rate();
									echo TravelHelper::rate_to_string( $avg );
									?>
								</ul>
								<?php $count_review = STReview::count_comment( get_the_ID() ); ?>
								<span class="review"><?php echo esc_html( $count_review ) . ' ' . _n( esc_html__( 'Review', 'traveler' ), esc_html__( 'Reviews', 'traveler' ), $count_review ); ?></span>
							</div>

							<div class="section-footer">
								<div class="footer-inner plr15">
									<div class="service-price">
										<span>
											<?php echo TravelHelper::getNewIcon( 'thunder', '#ffab53', '10px', '16px' ); ?>
											<span class="fr_text"><?php _e( 'from', 'traveler' ) ?></span>
										</span>
										<span class="price">
											<?php echo STActivity::inst()->get_price_html( get_the_ID() ); ?>
										</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			<?php
		endwhile;
		?>
		</div>
	<?php
	endif;
wp_reset_postdata();
$post = $old_post;
?>
