<h2 class="heading"><?php echo __( 'Summary', 'traveler' ) ?></h2>
<?php
$stats = STReview::get_review_summary();
if ( $stats ) {
	foreach ( $stats as $stat ) {
		?>
		<div class="item">
			<div class="progress">
				<div class="percent"
					style="width: <?php echo esc_attr( $stat['percent'] ); ?>%;"></div>
			</div>
			<div class="label">
				<?php echo esc_html( $stat['name'] ); ?>
				<div class="number"><?php echo esc_html( $stat['summary'] ) ?>
					/5
				</div>
			</div>
		</div>
		<?php
	}
}
?>
