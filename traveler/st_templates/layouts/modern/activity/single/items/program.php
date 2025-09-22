<?php
$tour_program_style = get_post_meta( get_the_ID(), 'activity_program_style', true );
if ( empty( $tour_program_style ) ) {
	$tour_program_style = 'style1';
}
if ( $tour_program_style == 'style1' || $tour_program_style == 'style3' ) {
	$tour_programs = get_post_meta( get_the_ID(), 'activity_program', true );
} else {
	$tour_programs = get_post_meta( get_the_ID(), 'activity_program_bgr', true );
}
if ( ! empty( $tour_programs ) ) {
	?>
		<div class="st-program">
			<div class="st-title-wrapper">
				<h3 class="st-section-title"><?php echo __( 'What you will do', 'traveler' ); ?></h3>
				<?php if ( $tour_program_style == 'style1' ) { ?>
					<span class="expand"
						data-ex="1"
						data-text-more="<?php echo __( 'Expand All', 'traveler' ); ?>"
						data-text-less="<?php echo __( 'Collapse All', 'traveler' ); ?>"
					>
						<?php echo __( 'Expand All', 'traveler' ); ?>
					</span>
				<?php } ?>
			</div>

			<div class="st-program-list <?php echo esc_attr( $tour_program_style ); ?>">
				<?php
				$tour_program_style = get_post_meta( get_the_ID(), 'activity_program_style', true );
				if ( empty( $tour_program_style ) ) {
					$tour_program_style = 'style1';
				}
				echo st()->load_template( 'layouts/modern/activity/single/items/itenirary/' . esc_html( $tour_program_style ) );
				?>
			</div>
		</div>
	<?php
}
?>
