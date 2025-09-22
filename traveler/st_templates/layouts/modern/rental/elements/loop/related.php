<?php
	/**
	 * Created by PhpStorm.
	 * User: Administrator
	 * Date: 14-11-2018
	 * Time: 8:16 AM
	 * Since: 1.0.0
	 * Updated: 1.0.0
	 */
	$post_id      = get_the_ID();
	$post_id      = TravelHelper::post_translated( $post_id );
	$thumbnail_id = get_post_thumbnail_id( $post_id );
	$start        = STInput::get( 'start' ) ? STInput::get( 'start' ) : date( TravelHelper::getDateFormat() );
	$end          = STInput::get( 'end' ) ? STInput::get( 'end' ) : date( TravelHelper::getDateFormat(), strtotime( '+ 1 day' ) );
	$url          = st_get_link_with_search( get_permalink( $post_id ), [ 'start', 'end', 'date', 'adult_number', 'child_number' ], $_GET );
	$start        = TravelHelper::convertDateFormat( $start );
	$end          = TravelHelper::convertDateFormat( $end );
	$numberday    = ( STDate::dateDiff( $start, $end ) == 0 ) ? 1 : STDate::dateDiff( $start, $end );
	$price        = STPrice::getSalePrice( get_the_ID(), strtotime( $start ), strtotime( $end ) );
	$orgin_price  = STPrice::getRentalPriceOnlyCustomPrice( get_the_ID(), strtotime( $start ), strtotime( $end ) );
	$info_price   = STRental::inst()->get_info_price();
	$min_price    = get_post_meta( get_the_ID(), 'min_price', true );
	$min_price    = floatval( $min_price ) * $numberday;
	$min_price    = ! empty( $price['total_price_not_bulk_discount'] ) ? floatval( $price['total_price_not_bulk_discount'] ) : $min_price;

	$regular_price = get_post_meta( get_the_ID(), 'price', true );
?>
<div class="item">
	<div class="thumb">
		<a href="<?php echo esc_url( $url ); ?>">
			<img src="<?php echo wp_get_attachment_image_url( $thumbnail_id, [ 80, 80 ] ); ?>" alt="<?php echo TravelHelper::get_alt_image( $thumbnail_id ); ?>"
				class="img-responsive img-full">
		</a>
	</div>
	<div class="content">
		<h3 class="title"><a href="<?php echo esc_url( $url ); ?>" class="st-link c-main"><?php the_title() ?></a></h3>
		<div class="price-wrapper">
			<?php if ( ! empty( $info_price['discount'] ) && $info_price['discount'] > 0 && $info_price['price_new'] > 0 ) : ?>
				<div class="price-regular">
					<span><?php echo esc_html__( TravelHelper::format_money( $orgin_price ), 'traveler' ) ?></span>
				</div>
			<?php endif; ?>
			<div class="price-sale">
				<span class="sale-top">
					<?php echo __( 'From ', 'traveler' ); ?>
				</span>
				<?php echo wp_kses( sprintf( __( '<span class="price">%1$s</span><span class="unit">/ %2$d night(s)</span>', 'traveler' ), TravelHelper::format_money( $min_price ), $numberday ), [ 'span' => [ 'class' => [] ] ] ) ?>
			</div>
		</div>
	</div>
</div>
