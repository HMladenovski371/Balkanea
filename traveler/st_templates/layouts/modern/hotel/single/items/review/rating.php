<h2 class="heading"><?php echo __( 'Traveler rating', 'traveler' ) ?></h2>
<?php $total = get_comments_number(); ?>
<?php $rate_exe = STReview::count_review_by_rate( null, 5 ); ?>
<div class="item">
	<div class="progress">
		<div class="percent green"
			style="width: <?php echo TravelHelper::cal_rate( $rate_exe, $total ) ?>%;"></div>
	</div>
	<div class="label">
		<?php echo esc_html__( 'Excellent', 'traveler' ) ?>
		<div class="number"><?php echo esc_html( $rate_exe ); ?></div>
	</div>
</div>
<?php $rate_good = STReview::count_review_by_rate( null, 4 ); ?>
<div class="item">
	<div class="progress">
		<div class="percent darkgreen"
			style="width: <?php echo TravelHelper::cal_rate( $rate_good, $total ) ?>%;"></div>
	</div>
	<div class="label">
		<?php echo __( 'Very Good', 'traveler' ) ?>
		<div class="number"><?php echo esc_html( $rate_good ); ?></div>
	</div>
</div>
<?php $rate_avg = STReview::count_review_by_rate( null, 3 ); ?>
<div class="item">
	<div class="progress">
		<div class="percent yellow"
			style="width: <?php echo TravelHelper::cal_rate( $rate_avg, $total ) ?>%;"></div>
	</div>
	<div class="label">
		<?php echo __( 'Average', 'traveler' ) ?>
		<div class="number"><?php echo esc_html( $rate_avg ); ?></div>
	</div>
</div>
<?php $rate_poor = STReview::count_review_by_rate( null, 2 ); ?>
<div class="item">
	<div class="progress">
		<div class="percent orange"
			style="width: <?php echo TravelHelper::cal_rate( $rate_poor, $total ) ?>%;"></div>
	</div>
	<div class="label">
		<?php echo __( 'Poor', 'traveler' ) ?>
		<div class="number"><?php echo esc_html( $rate_poor ); ?></div>
	</div>
</div>
<?php $rate_terible = STReview::count_review_by_rate( null, 1 ); ?>
<div class="item">
	<div class="progress">
		<div class="percent red"
			style="width: <?php echo TravelHelper::cal_rate( $rate_terible, $total ) ?>%;"></div>
	</div>
	<div class="label">
		<?php echo __( 'Terrible', 'traveler' ) ?>
		<div class="number"><?php echo esc_html( $rate_terible ); ?></div>
	</div>
</div>
