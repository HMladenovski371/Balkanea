<div class="room-heading">
	<div class="left">
		<div class="st-heading"><?php the_title(); ?></div>
	</div>
	<div class="right">
		<div class="review-score style-2">
			<?php
			echo st()->load_template( 'layouts/modern/common/star', '', [
				'star'  => $review_rate,
				'style' => 'style-2',
			] );
			?>
			<p class="st-link mb0"><?php comments_number( __( 'from 0 review', 'traveler' ), __( 'from 1 review', 'traveler' ), __( 'from % reviews', 'traveler' ) ); ?></p>
		</div>
	</div>
</div>
