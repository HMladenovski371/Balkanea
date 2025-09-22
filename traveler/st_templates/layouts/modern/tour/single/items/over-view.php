<?php
global $post;
$content = $post->post_content;
if ( ! empty( $content ) ) {
	?>
	<div class="st-overview">
		<h3 class="st-section-title"><?php echo __( 'Overview', 'traveler' ); ?></h3>
		<div class="st-description" data-toggle-section="st-description">
			<?php the_content(); ?>
		</div>
	</div>
<?php } ?>
