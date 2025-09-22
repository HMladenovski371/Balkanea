<?php
get_header();
wp_enqueue_script( 'filter-tour-js' );
?>
	<div id="st-content-wrapper" class="search-result-page style-2 st-tours">
		<div id="tour-top-search"></div>
		<?php echo st()->load_template( 'layouts/modern/hotel/elements/banner' ); ?>
		<div class="search-form-top">
			<?php
			$container = 'container';
			echo st()->load_template( 'layouts/modern/tour/elements/search-form-new', '', [ 'container' => $container ] );
			?>
		</div>
		<div class="container">
			<div class="st-hotel-result tour-top-search">
				<?php
				$query       = [
					'post_type'   => 'st_tours',
					'post_status' => 'publish',
					's'           => '',
					'orderby'     => 'date',
					'order'       => 'DESC',
				];
				$is_featured = st()->get_option( 'is_featured_search_tour', 'off' );
				if ( ! empty( $is_featured ) && $is_featured == 'on' ) {
					$query['meta_query'] = [
						'relation' => 'OR',
						[
							'key'     => 'is_featured',
							'compare' => 'EXISTS',
						],
						[
							'key'     => 'is_featured',
							'compare' => 'NOT EXISTS',
						],
					];
					$query['orderby']    = 'meta_value date';
				}

				global $wp_query , $st_search_query;
				$tour = STTour::get_instance();
				$tour->alter_search_query();
				query_posts( $query );
				$st_search_query = $wp_query;
				$tour->remove_alter_search_query();
				wp_reset_query();
				echo st()->load_template( 'layouts/modern/tour/elements/content2' );
				?>
			</div>
		</div>
	</div>
<?php
echo st()->load_template( 'layouts/modern/hotel/elements/popup/date' );
echo st()->load_template( 'layouts/modern/hotel/elements/popup/guest' );
get_footer();
