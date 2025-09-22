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
			<span class="head-rating"><?php echo TravelHelper::get_rate_review_text( $review_rate, $count_review ); ?></span>
			<?php
			echo st()->load_template( 'layouts/modern/common/star', '', [
				'star'  => $review_rate,
				'style' => 'style-2',
			] );
			?>
			<p class="st-link"><?php comments_number( __( 'from 0 review', 'traveler' ), __( 'from 1 review', 'traveler' ), __( 'from % reviews', 'traveler' ) ); ?></p>
		</div>
	</div>
</div>
