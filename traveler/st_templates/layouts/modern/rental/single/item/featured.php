<div class="room-featured-items">
	<div class="row">
		<div class="col-xs-6 col-md-3">
			<div class="item has-matchHeight">
			<?php echo TravelHelper::getNewIcon( 'ico_square_blue', '', '32px' ); ?>
			<?php echo sprintf( __( 'S: %s', 'traveler' ), get_post_meta( get_the_ID(), 'rental_size', true ) ) ?><?php echo __( 'm<sup>2</sup>', 'traveler' ); ?>
			</div>
		</div>
		<div class="col-xs-6 col-md-3">
			<div class="item has-matchHeight">
			<?php echo TravelHelper::getNewIcon( 'ico_beds_blue', '', '32px' ); ?>
			<?php echo sprintf( __( 'Beds: %s', 'traveler' ), get_post_meta( get_the_ID(), 'rental_bed', true ) ) ?>
			</div>
		</div>
		<div class="col-xs-6 col-md-3">
			<div class="item has-matchHeight">
			<?php echo TravelHelper::getNewIcon( 'ico_adults_blue', '', '32px' ); ?>
			<?php echo sprintf( __( 'Adults: %s', 'traveler' ), get_post_meta( get_the_ID(), 'rental_max_adult', true ) ) ?>
			</div>
		</div>
		<div class="col-xs-6 col-md-3">
			<div class="item has-matchHeight">
			<?php echo TravelHelper::getNewIcon( 'ico_children_blue', '', '32px' ); ?>
			<?php echo sprintf( __( 'Children: %s', 'traveler' ), get_post_meta( get_the_ID(), 'rental_max_children', true ) ) ?>
			</div>
		</div>
	</div>
</div>
