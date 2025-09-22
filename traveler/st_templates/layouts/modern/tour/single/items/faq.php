<?php
$tour_faq = get_post_meta( get_the_ID(), 'tours_faq', true );
if ( ! empty( $tour_faq ) ) {
	?>
	<div class="st-faq ">
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
					<h5><?php echo balanceTags( $v['title'] ); ?></h5>
					<span class="arrow">
						<i class="fa fa-angle-down"></i>
					</span>
				</div>
				<div class="body">
					<?php echo do_shortcode( wpautop( $v['desc'] ) ); ?>
				</div>
			</div>
			<?php
			++$i;
		}
		?>
	</div>
	<?php
}
?>
