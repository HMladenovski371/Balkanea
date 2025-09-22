<?php
$tour_faq = get_post_meta( get_the_ID(), 'activity_faq', true );
if ( ! empty( $tour_faq ) ) {
	?>
	<div class="st-faq">
		<h3 class="st-section-title">
			<?php echo __( 'FAQs', 'traveler' ); ?>
		</h3>
		<?php
		$i = 0;
		foreach ( $tour_faq as $k => $v ) {
			?>
			<div class="item <?php echo ( $i == 0 ) ? 'active' : ''; ?>">
				<div class="header">
					<?php echo TravelHelper::getNewIcon( 'question-help-message', '#5E6D77', '18px', '18px' ); ?>
					<h5><?php echo esc_html( $v['title'] ); ?></h5>
					<span class="arrow">
						<i class="fa fa-angle-down"></i>
					</span>
				</div>

				<?php if ( ! empty( $v['desc'] ) ) : ?>
					<div class="body">
						<?php echo balanceTags( nl2br( $v['desc'] ) ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
			++$i;
		}
		?>
	</div>
	<?php
}
?>
