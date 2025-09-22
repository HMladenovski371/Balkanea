<?php
$current_calendar = TravelHelper::get_current_available_calendar( get_the_ID() );
$start            = STInput::get( 'start', date( TravelHelper::getDateFormat(), strtotime( $current_calendar ) ) );
$end              = STInput::get( 'end', date( TravelHelper::getDateFormat(), strtotime( '+ 1 day', strtotime( $current_calendar ) ) ) );
$start            = TravelHelper::convertDateFormat( $start );
$end              = TravelHelper::convertDateFormat( $end );
$number_day       = STDate::dateDiff( $start, $end );
if ( $number_day == 0 ) {
	$number_day = 1;
}

$search_tax_advance = st()->get_option( 'attribute_search_form_rental', 'rental_types' );
$terms_posts        = wp_get_post_terms( get_the_ID(), $search_tax_advance );
$arr_id_term_post   = [];
foreach ( $terms_posts as $term_post ) {
	$arr_id_term_post[] = $term_post->term_id;
}
$args = [
	'posts_per_page' => 4,
	'post_type'      => 'st_rental',
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
	<div class="row row-wrapper mt50">
		<?php
		while ( $query->have_posts() ) :
			$query->the_post();
			?>
			<div class="col-xs-12 col-sm-6 col-md-3">
				<?php
				$url   = st_get_link_with_search( get_permalink(), [ 'start', 'end', 'date', 'adult_number', 'child_number' ], $_GET );
				$price = STPrice::getSalePrice( get_the_ID(), strtotime( $start ), strtotime( $end ) );

				$min_price = get_post_meta( get_the_ID(), 'min_price', true );
				$min_price = floatval( $min_price ) * $number_day;
				$min_price = ! empty( $price['total_price_not_bulk_discount'] ) ? floatval( $price['total_price_not_bulk_discount'] ) : $min_price;
				?>
				<div class="search-result-page st-rental st-tours">
					<div class="item-service grid-item has-matchHeight">
						<div class="featured-image">
							<?php echo STFeatured::get_featured(); ?>
							<?php if ( is_user_logged_in() ) { ?>
								<?php $data = STUser_f::get_icon_wishlist(); ?>
								<div class="service-add-wishlist login <?php echo ( $data['status'] ) ? 'added' : ''; ?>"
									data-id="<?php echo get_the_ID(); ?>" data-type="<?php echo get_post_type( get_the_ID() ); ?>"
									title="<?php echo ( $data['status'] ) ? __( 'Remove from wishlist', 'traveler' ) : __( 'Add to wishlist', 'traveler' ); ?>">
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
							<a href="<?php echo esc_url( $url ); ?>">
								<?php
								if ( has_post_thumbnail() ) {
									the_post_thumbnail( [ 740, 560 ], [
										'alt'   => TravelHelper::get_alt_image(),
										'class' => 'img-responsive',
									] );
								} else {
									echo '<img src="' . get_template_directory_uri() . '/img/no-image.png' . '" alt="Default Thumbnail" class="img-responsive" />';
								}
								?>
							</a>
							<?php do_action( 'st_list_compare_button', get_the_ID(), get_post_type( get_the_ID() ) ); ?>
							<div class="price-wrapper">
								<?php echo wp_kses( sprintf( __( '<span class="price">From %1$s</span><span class="unit">/ %2$d night(s)</span>', 'traveler' ), TravelHelper::format_money( $min_price ), $number_day ), [ 'span' => [ 'class' => [] ] ] ) ?>
							</div>
						</div>
						<div class="item-content">
							<h4 class="service-title"><a href="<?php echo esc_url( $url ); ?>"><?php echo get_the_title(); ?></a></h4>
							<?php if ( $address = get_post_meta( get_the_ID(), 'address', true ) ) : ?>
								<p class="service-location"><?php echo esc_html( $address ); ?></p>
							<?php endif; ?>
							<div class="service-review">
								<?php
								$count_review = STReview::count_comment( get_the_ID() );
								$avg          = STReview::get_avg_rate();
								?>
								<span class="rate">
									<?php echo esc_html( $avg ) . '/5'; ?>
									<span class="rate-text"><?php echo TravelHelper::get_rate_review_text( $avg, $count_review ); ?></span>
								</span>
								<span class="review"><?php echo esc_html( $count_review ) . ' ' . _n( esc_html__( 'Review', 'traveler' ), esc_html__( 'Reviews', 'traveler' ), $count_review ); ?></span>
							</div>
							<div class="amenities clearfix">
								<span class="amenity total" data-toggle="tooltip" title="<?php echo esc_attr__( 'No. People', 'traveler' ) ?>"><?php echo TravelHelper::getNewIcon( 'ico_people_1', '', '22px', '22px', false ); ?><?php echo (int) get_post_meta( get_the_ID(), 'rental_max_adult', true ) + (int) get_post_meta( get_the_ID(), 'rental_max_children', true ); ?></span>
								<span class="amenity bed" data-toggle="tooltip" title="<?php echo esc_attr__( 'No. Bed', 'traveler' ) ?>"><?php echo TravelHelper::getNewIcon( 'ico_bed_1', '', '20px', '22px', false ); ?><?php echo (int) get_post_meta( get_the_ID(), 'rental_bed', true ) ?></span>
								<span class="amenity bath" data-toggle="tooltip" title="<?php echo esc_attr__( 'No. Bathroom', 'traveler' ) ?>"><?php echo TravelHelper::getNewIcon( 'ico_bathroom_1', '', '22px', '22px', false ); ?><?php echo (int) get_post_meta( get_the_ID(), 'rental_bath', true ) ?></span>
								<span class="amenity size" data-toggle="tooltip" title="<?php echo esc_attr__( 'Square', 'traveler' ) ?>"><?php echo TravelHelper::getNewIcon( 'ico_square_1', '', '21px', '21px', false ); ?><?php echo get_post_meta( get_the_ID(), 'rental_size', true ); ?><?php echo __( 'm<sup>2</sup>', 'traveler' ); ?></span>
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
