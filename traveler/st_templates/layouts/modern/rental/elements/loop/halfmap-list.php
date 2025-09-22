<?php
global $post;
$url         = st_get_link_with_search( get_permalink(), [ 'start', 'end', 'date', 'adult_number', 'child_number' ], $_GET );
$start       = STInput::get( 'start' ) ? STInput::get( 'start' ) : date( TravelHelper::getDateFormat() );
$end         = STInput::get( 'end' ) ? STInput::get( 'end' ) : date( TravelHelper::getDateFormat(), strtotime( '+ 1 day' ) );
$start       = TravelHelper::convertDateFormat( $start );
$end         = TravelHelper::convertDateFormat( $end );
$price       = STPrice::getSalePrice( get_the_ID(), strtotime( $start ), strtotime( $end ) );
$price       = ! empty( $price['total_price_not_bulk_discount'] ) ? floatval( $price['total_price_not_bulk_discount'] ) : $min_price;
$orgin_price = STPrice::getRentalPriceOnlyCustomPrice( get_the_ID(), strtotime( $start ), strtotime( $end ) );
$info_price  = STRental::inst()->get_info_price();
$numberday   = STDate::dateDiff( $start, $end );
if ( $numberday == 0 ) {
	$numberday = 1;
}

$min_price     = get_post_meta( get_the_ID(), 'min_price', true );
$min_price     = floatval( $min_price ) * $numberday;
$min_price     = ! empty( $price['total_price_not_bulk_discount'] ) ? floatval( $price['total_price_not_bulk_discount'] ) : $min_price;
$regular_price = get_post_meta( get_the_ID(), 'price', true );
?>
<div class="item-service item-service-halfmap">
	<div class="item-service-inner has-matchHeight">
		<div class="row">
			<div class="col-lg-6 col-md-12 col-sm-6">
				<div class="thumb">
					<?php if ( ! empty( $info_price['discount'] ) && $info_price['discount'] > 0 && $info_price['price_new'] > 0 ) { ?>
						<?php echo STFeatured::get_sale( $info_price['discount'] ); ?>
					<?php } ?>
					<?php if ( is_user_logged_in() ) { ?>
						<?php $data = STUser_f::get_icon_wishlist(); ?>
						<div class="service-add-wishlist login <?php echo ( $data['status'] ) ? 'added' : ''; ?>"
							data-id="<?php echo get_the_ID(); ?>"
							data-type="<?php echo get_post_type( get_the_ID() ); ?>"
							title="<?php echo ( $data['status'] ) ? __( 'Remove from wishlist', 'traveler' ) : __( 'Add to wishlist', 'traveler' ); ?>">
							<i class="fa fa-heart"></i>
							<div class="lds-dual-ring"></div>
						</div>
					<?php } else { ?>
						<a href="" class="login" data-toggle="modal" data-target="#st-login-form">
							<div class="service-add-wishlist"
								title="<?php echo __( 'Add to wishlist', 'traveler' ); ?>">
								<i class="fa fa-heart"></i>
								<div class="lds-dual-ring"></div>
							</div>
						</a>
					<?php } ?>
					<div class="service-tag bestseller">
						<?php echo STFeatured::get_featured(); ?>
					</div>
					<a href="<?php echo esc_url( $url ) ?>">
						<?php
						if ( has_post_thumbnail() ) {
							the_post_thumbnail( [ 450, 417 ], [
								'alt'   => TravelHelper::get_alt_image(),
								'class' => 'img-responsive',
							] );
						} else {
							echo '<img src="' . get_template_directory_uri() . '/img/no-image.png' . '" alt="Default Thumbnail" class="img-responsive" />';
						}
						?>
					</a>
					<?php do_action( 'st_list_compare_button', get_the_ID(), get_post_type( get_the_ID() ) ); ?>
					<ul class="icon-group text-color booking-item-rating-stars hidden-lg hidden-md hidden-sm">
						<?php
						$avg = STReview::get_avg_rate();
						echo TravelHelper::rate_to_string( $avg );
						?>
					</ul>
				</div>
			</div>
			<div class="col-lg-6 col-md-12 col-sm-6 item-content">
				<ul class="icon-group text-color booking-item-rating-stars hidden-xs">
					<?php
					$avg = STReview::get_avg_rate();
					echo TravelHelper::rate_to_string( $avg );
					?>
				</ul>
				<h4 class="service-title"><a
							href="<?php echo esc_url( $url ); ?>"><?php echo get_the_title(); ?></a></h4>
				<?php if ( $address = get_post_meta( get_the_ID(), 'address', true ) ) : ?>
					<p class="service-location"><?php echo TravelHelper::getNewIcon( 'Ico_maps', '#666666', '15px', '15px', true ); ?><?php echo esc_html( $address ); ?></p>
				<?php endif; ?>
				<div class="service-review">
					<?php
					$count_review = STReview::count_comment( get_the_ID() );
					$avg          = STReview::get_avg_rate();
					?>
					<span class="rating"><?php echo esc_html( $avg ); ?>/5 <?php echo TravelHelper::get_rate_review_text( $avg, $count_review ); ?></span>
					<span class="st-dot"></span>
					<span class="review"><?php echo esc_html( $count_review ) . ' ' . _n( esc_html__( 'Review', 'traveler' ), esc_html__( 'Reviews', 'traveler' ), $count_review ); ?></span>
				</div>
				<?php if ( ! empty( $info_price['discount'] ) && $info_price['discount'] > 0 && $info_price['price_new'] > 0 ) : ?>
					<div class="price-regular">
						<span>
							<?php echo esc_html__( TravelHelper::format_money( $orgin_price ), 'traveler' ) ?>
						</span>
					</div>
				<?php endif; ?>
				<div class="service-price">
					<span>
						<?php echo TravelHelper::getNewIcon( 'thunder', '#ffab53', '10px', '16px' ); ?>
						<?php _e( 'From', 'traveler' ) ?>
					</span>
					<span class="price">
						<?php
						echo TravelHelper::format_money( $price );
						?>
					</span>
					<span><?php echo sprintf( __( '/ %d night(s)', 'traveler' ), $numberday ); ?></span>
				</div>
			</div>

		</div>
	</div>
</div>
