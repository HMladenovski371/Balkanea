<div class="st-list-rooms relative" data-toggle-section="st-list-rooms">
	<?php echo st()->load_template( 'layouts/modern/common/loader' ); ?>
	<div class="fetch">
		<?php
		global $post;
		$hotel = new STHotel();
		$query = $hotel->search_room();
		while ( $query->have_posts() ) {
			$query->the_post();
			echo st()->load_template( 'layouts/modern/hotel/loop/room_item' );
		}
		wp_reset_postdata();
		?>
	</div>
</div>
