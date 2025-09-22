<?php
get_header();
wp_enqueue_script( 'filter-tour-js' );
?>
<div id="st-content-wrapper" class="search-result-page st-tours st-tour--solo" data-style="grid8">
	<div class="st-tour--solo__banner">
		<?php echo st()->load_template( 'layouts/modern/blog/banner' ); ?>
		<?php st_breadcrumbs_new() ?>
	</div>

	<div class="search-form-top">
		<?php echo st()->load_template( 'layouts/modern/tour/elements/search-form-solo', '', [ 'container' => 'container' ] ); ?>
	</div>
	<div class="container">
		<div class="st-hotel-result">
			<div class="row">
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

				global $wp_query, $st_search_query;
				$tour = STTour::get_instance();
				$tour->alter_search_query();
				query_posts( $query );
				$st_search_query = $wp_query;
				$tour->remove_alter_search_query();
				wp_reset_query();
				echo st()->load_template( 'layouts/modern/tour/elements/solo' );

				echo st()->load_template( 'layouts/modern/tour/elements/sidebar-solo' );
				?>
			</div>
		</div>
	</div>
</div>
<?php
get_footer();
