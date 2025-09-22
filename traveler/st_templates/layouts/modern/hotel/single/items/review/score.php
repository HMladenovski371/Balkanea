<h2 class="heading"><?php echo __( 'Review score', 'traveler' ) ?></h2>
<div class="review-box-score">
	<?php
	$avg = STReview::get_avg_rate();
	?>
	<div class="review-score">
		<?php echo esc_attr( $avg ); ?><span class="per-total">/5</span>
	</div>
	<div class="review-score-text"><?php echo TravelHelper::get_rate_review_text( $avg, $count_review ); ?></div>
	<div class="review-score-base">
		<?php echo __( 'Based on', 'traveler' ) ?>
		<span><?php comments_number( __( '0 review', 'traveler' ), __( '1 review', 'traveler' ), __( '% reviews', 'traveler' ) ); ?></span>
	</div>
</div>
