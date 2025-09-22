<?php
$all_attribute = TravelHelper::st_get_attribute_advance( 'st_rental' );
foreach ( $all_attribute as $key_attr => $attr ) {
	if ( ! empty( $attr['value'] ) ) {
		$get_label_tax = get_taxonomy( $attr['value'] );
		?>
			<div class="stt-attr-<?php echo esc_attr( $attr['value'] ); ?>">
				<?php
				if ( ! empty( $get_label_tax ) ) {
					echo '<h2 class="st-heading-section">' . esc_html( $get_label_tax->label ) . '</h2>';
				}
				?>
				<?php
				$facilities = get_the_terms( get_the_ID(), $attr['value'] );
				if ( $facilities ) {
					$count = count( $facilities );
					?>
					<div class="facilities" data-toggle-section="st-<?php echo esc_attr( $attr['value'] ); ?>"
						<?php
						if ( $count > 6 ) {
							echo 'data-show-all="st-' . esc_attr( $attr['value'] ) . '"
							data-height="150"';}
						?>
						>
						<div class="row">
							<?php
							foreach ( $facilities as $term ) {
								$icon     = TravelHelper::handle_icon( get_tax_meta( $term->term_id, 'st_icon' ) );
								$icon_new = TravelHelper::handle_icon( get_tax_meta( $term->term_id, 'st_icon_new' ) );
								if ( ! $icon ) {
									$icon = 'fa fa-cogs';
								}
								?>
									<div class="col-xs-6 col-sm-4">
										<div class="item has-matchHeight">
										<?php
										if ( ! $icon_new ) {
											echo '<i class="' . esc_attr( $icon ) . '"></i>' . esc_html( $term->name );
										} else {
											echo TravelHelper::getNewIcon( $icon_new, '#5E6D77', '24px', '24px' ) . esc_html( $term->name );
										}
										?>
										</div>
									</div>
								<?php
							}
							?>
						</div>
					</div>
					<?php if ( $count > 6 ) { ?>
						<a href="#" class="st-link block" data-show-target="st-<?php echo esc_attr( $attr['value'] ); ?>"
							data-text-less="<?php echo esc_html__( 'Show Less', 'traveler' ) ?>"
							data-text-more="<?php echo esc_html__( 'Show All', 'traveler' ) ?>">
							<span class="text"><?php echo esc_html__( 'Show All', 'traveler' ) ?></span>
							<i class="fa fa-caret-down ml3"></i>
						</a>
						<?php
					}
				}
				if ( $facilities ) {
					?>
					<div class="st-hr large"></div>
					<?php
				}
				?>
			</div>
		<?php
	}
}

?>
