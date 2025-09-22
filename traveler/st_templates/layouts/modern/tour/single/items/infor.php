<?php
$icon_duration_single_tour  = st()->get_option( 'icon_duration_single_tour', '<i class="lar la-clock"></i>' );
$icon_tourtype_single_tour  = st()->get_option( 'icon_tourtype_single_tour', '<i class="las la-shoe-prints"></i>' );
$icon_groupsize_single_tour = st()->get_option( 'icon_groupsize_single_tour', '<i class="las la-user-friends"></i>' );
$icon_language_single_tour  = st()->get_option( 'icon_language_single_tour', '<i class="las la-language"></i>' );
$style                      = !empty( $style ) ? $style : '';
?>
<div class="st-tour-feature <?= esc_attr( $style ) ?>">
	<div class="row">
		<div class="col-xs-6 col-lg-3">
			<div class="item">
				<div class="icon">
					<?php echo htmlspecialchars_decode( $icon_duration_single_tour ); ?>
				</div>
				<div class="info">
					<div class="name"><?php echo __( 'Duration', 'traveler' ); ?></div>
					<p class="value">
						<?php
						$duration = get_post_meta( get_the_ID(), 'duration_day', true );
						echo esc_html( $duration );
						?>
					</p>
				</div>
			</div>
		</div>
		<div class="col-xs-6 col-lg-3">
			<div class="item">
				<div class="icon">
					<?php echo htmlspecialchars_decode( $icon_tourtype_single_tour ); ?>
				</div>
				<div class="info">
					<div class="name"><?php echo __( 'Tour Type', 'traveler' ); ?></div>
					<p class="value">
						<?php
						$tour_type = get_post_meta( get_the_ID(), 'type_tour', true );
						if ( $tour_type == 'daily_tour' ) {
							echo __( 'Daily Tour', 'traveler' );
						} else {
							echo __( 'Specific Tour', 'traveler' );
						}
						?>
					</p>
				</div>
			</div>
		</div>
		<div class="col-xs-6 col-lg-3">
			<div class="item">
				<div class="icon">
					<?php echo htmlspecialchars_decode( $icon_groupsize_single_tour ); ?>
				</div>
				<div class="info">
					<div class="name"><?php echo __( 'Group Size', 'traveler' ); ?></div>
					<p class="value">
						<?php
						$max_people = get_post_meta( get_the_ID(), 'max_people', true );
						if ( empty( $max_people ) or $max_people == 0 or $max_people < 0 ) {
							echo __( 'Unlimited', 'traveler' );
						} elseif ( $max_people == 1 ) {
								echo sprintf( __( '%s person', 'traveler' ), $max_people );
						} else {
							echo sprintf( __( '%s people', 'traveler' ), $max_people );
						}
						?>
					</p>
				</div>
			</div>
		</div>
		<div class="col-xs-6 col-lg-3">
			<div class="item">
				<div class="icon">
					<?php echo htmlspecialchars_decode( $icon_language_single_tour ); ?>
				</div>
				<div class="info">
					<div class="name"><?php echo __( 'Languages', 'traveler' ); ?></div>
					<p class="value">
						<?php
						$term_list    = wp_get_post_terms( get_the_ID(), 'languages' );
						$str_term_arr = [];
						if ( ! is_wp_error( $term_list ) && ! empty( $term_list ) ) {
							foreach ( $term_list as $k => $v ) {
								array_push( $str_term_arr, $v->name );
							}

							echo implode( ', ', $str_term_arr );
						} else {
							echo '___';
						}
						?>
					</p>
				</div>
			</div>
		</div>
	</div>
</div>
