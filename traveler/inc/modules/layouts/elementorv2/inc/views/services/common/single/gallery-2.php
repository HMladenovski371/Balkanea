<?php
$gallery       = get_post_meta( get_the_ID(), 'gallery', true );
$gallery_array = explode( ',', $gallery );

$post_type = get_post_type( get_the_ID() );
$layout    = get_post_meta( get_the_ID(), 'st_custom_layout_new', true );
$class     = $post_type === 'st_tours' && $layout == 9 || $post_type === 'st_activity' && $layout == 5 ? 'mod-tour-2' : '';
$rtl       = st()->get_option( 'right_to_left' );

if ( ! empty( $gallery_array ) && is_array( $gallery_array ) ) { ?>
	<div class="st-gallery st-border-radius style-slider <?= esc_attr( $class ) ?>" data-rtl="<?= esc_attr( $rtl ) ?>">
		<div class="owl-carousel">
		<?php
		foreach ( $gallery_array as $key => $value ) {
			$alt = get_post_meta( $value, '_wp_attachment_image_alt', true );
			if ( ! $alt ) {
				$alt = get_the_title( get_the_ID() );
			}
			?>
			<img class="item-gallery"
				src="<?php echo wp_get_attachment_image_url( $value, 'full' ) ?>"
				alt="<?php echo $alt; ?>"
			>
		<?php } ?>
		</div>
		<div class="count"></div>

		<?php if ( $post_type === 'st_tours' && $layout == 9 || $post_type === 'st_activity' && $layout == 5 ) : ?>
			<div class="shares dropdown">
				<div class="btn-group">
					<a href="#st-gallery-popup" class="btn btn-transparent has-icon radius st-gallery-popup"><span class="stt-icon stt-icon-category"></span><?php echo esc_html__( 'All photos', 'traveler' ); ?></a>
					<div id="st-gallery-popup" class="hidden">
						<?php
						foreach ( $gallery_array as $k => $v ) {
							if ( ! empty( $v ) ) {
								echo '<a href="' . wp_get_attachment_image_url( $v, 'full' ) . '">' . __( 'Gallery', 'traveler' ) . '</a>';
							}
						}
						?>
					</div>
				</div>
			</div>
		<?php endif; ?>
	</div>
<?php } ?>
