<?php
$search_tax_advance = st()->get_option( 'attribute_search_form_tour', 'st_tour_type' );
$terms_posts        = wp_get_post_terms( get_the_ID(), $search_tax_advance );
$arr_id_term_post   = [];
foreach ( $terms_posts as $term_post ) {
	if ( ! empty( $term_post->term_id ) ) {
		$arr_id_term_post[] = $term_post->term_id;
	}
}
$args = [
	'posts_per_page' => 4,
	'post_type'      => 'st_tours',
	'post_author'    => get_post_field( 'post_author', get_the_ID() ),
	'post__not_in'   => [ get_the_ID() ],
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
			?>
			<div class="col-xs-12 col-sm-6 col-md-3">
				<div class="item has-matchHeight">
					<div class="featured">
						<a href="<?php the_permalink() ?>">
							<img src="<?php echo get_the_post_thumbnail_url( get_the_ID(), [ 800, 600 ] ) ?>"
								alt="<?php echo TravelHelper::get_alt_image() ?>"
								class="img-responsive">
						</a>
						<?php echo st()->load_template( 'layouts/modern/hotel/loop/wishlist-2' ); ?>
						<?php echo st_get_avatar_in_list_service( get_the_ID(), 70 ); ?>
					</div>
					<div class="body">
						<?php
						$address = get_post_meta( get_the_ID(), 'address', true );
						if ( $address ) {
							echo TravelHelper::getNewIcon( 'ico_maps_add_2', '#5E6D77', '16px', '16px' );
							echo '<span class="ml5 f14 address">' . esc_html( $address ) . '</span>';
						}
						?>
						<h3 class="title">
							<a href="<?php the_permalink() ?>" class="st-link c-main">
								<?php the_title(); ?>
							</a>
						</h3>
						<?php
						$review_rate = STReview::get_avg_rate();
						echo st()->load_template( 'layouts/modern/common/star', '', [
							'star'  => $review_rate,
							'style' => 'style-2',
						] );
						?>
						<p class="review-text"><?php comments_number( __( '0 review', 'traveler' ), __( '1 review', 'traveler' ), __( '% reviews', 'traveler' ) ); ?></p>
						<div class="st-flex space-between">
							<div class="left st-flex">
								<?php echo TravelHelper::getNewIcon( 'time-clock-circle-1', '#5E6D77', '16px', '16px' ); ?>
								<span class="duration"><?php echo get_post_meta( get_the_ID(), 'duration_day', true ); ?></span>
							</div>
							<div class="right st-flex">
								<?php echo TravelHelper::getNewIcon( 'thunder', '#FFAB53', '9px', '16px', false ); ?>
								<span class="price">
									<?php echo sprintf( esc_html__( 'from %s', 'traveler' ), STTour::get_price_html( get_the_ID() ) ); ?>
								</span>
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
