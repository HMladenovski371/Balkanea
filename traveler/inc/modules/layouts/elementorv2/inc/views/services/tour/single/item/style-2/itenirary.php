<?php
$tour_program_style = get_post_meta( get_the_ID(), 'tours_program_style', true );
if ( empty( $tour_program_style ) ) {
	$tour_program_style = 'style1';
}
if ( $tour_program_style == 'style1' || $tour_program_style == 'style3' ) {
	$tour_programs = get_post_meta( get_the_ID(), 'tours_program', true );
} else {
	$tour_programs = get_post_meta( get_the_ID(), 'tours_program_bgr', true );
}
if ( ! empty( $tour_programs ) ) {
	?>
	<div class="st-program st-program--padding st-maxheight">
		<div class="st-title-wrapper st-program--title">
			<h3 class="st-section-title st-title__item st-heading-section"><?php echo esc_html__( 'Tour Itinerary', 'traveler' ); ?></h3>
		</div>

		<div class="st-program-list <?php echo esc_attr( $tour_program_style ); ?>">
			<div class="owl-carousel-wrapper">
				<div class="owl-carousel owl-tour-program-7">
					<?php
						echo stt_elementorv2()->loadView( 'services/tour/single/item/itenirary/' . esc_attr( $tour_program_style ) );
					?>
				</div>
			</div>
		</div>
	</div>
<?php } ?>
