<div id="reviews" data-toggle-section="st-reviews">
	<div class="row">
		<div class="col-xs-12 col-sm-4">
			<div class="review-box has-matchHeight">
				<?php
				echo st()->load_template( 'layouts/modern/hotel/single/items/review/score', '', [
					'count_review' => $count_review
				] );
				?>
			</div>
		</div>
		<div class="col-xs-12 col-sm-4">
			<div class="review-box has-matchHeight">
				<?php echo st()->load_template( 'layouts/modern/hotel/single/items/review/rating' ); ?>
			</div>
		</div>
		<div class="col-xs-12 col-sm-4">
			<div class="review-box has-matchHeight">
				<?php echo st()->load_template( 'layouts/modern/hotel/single/items/review/summary' ); ?>
			</div>
		</div>
	</div>
	<div class="review-pagination">
		<div class="summary">
			<?php
			$comments_count   = wp_count_comments( get_the_ID() );
			$total            = (int) $comments_count->approved;
			$comment_per_page = (int) get_option( 'comments_per_page', 10 );
			$paged            = (int) STInput::get( 'comment_page', 1 );
			$from             = $comment_per_page * ( $paged - 1 ) + 1;
			$to               = ( $paged * $comment_per_page < $total ) ? ( $paged * $comment_per_page ) : $total;
			?>
			<?php comments_number( __( '0 review on this Hotel', 'traveler' ), __( '1 review on this Hotel', 'traveler' ), __( '% reviews on this Hotel', 'traveler' ) ); ?>
			- <?php echo sprintf( __( 'Showing %1$s to %2$s', 'traveler' ), $from, $to ) ?>
		</div>
		<div id="reviews" class="review-list">
			<?php
			$offset         = ( $paged - 1 ) * $comment_per_page;
			$args           = [
				'number'  => $comment_per_page,
				'offset'  => $offset,
				'post_id' => get_the_ID(),
				'status'  => [ 'approve' ],
			];
			$comments_query = new WP_Comment_Query;
			$comments       = $comments_query->query( $args );

			if ( $comments ) :
				foreach ( $comments as $key => $comment ) :
					echo st()->load_template( 'layouts/modern/common/reviews/review', 'list', [ 'comment' => (object) $comment ] );
				endforeach;
			endif;
			?>
		</div>
	</div>
	<?php TravelHelper::pagination_comment( [ 'total' => $total ] ) ?>
	<?php
	if ( comments_open( get_the_ID() ) ) {
		?>
		<div id="write-review">
			<h4 class="heading">
				<a href="" class="toggle-section c-main f16"
					data-target="st-review-form">
					<?php echo __( 'Write a review', 'traveler' ) ?><i class="fa fa-angle-down ml5"></i>
				</a>
			</h4>
			<?php TravelHelper::comment_form(); ?>
		</div>
		<?php
	}
	?>
</div>
