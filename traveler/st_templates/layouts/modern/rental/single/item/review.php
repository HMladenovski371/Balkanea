<?php
if ( comments_open() && st()->get_option( 'rental_review' ) == 'on' ) { ?>
	<div class="st-hr"></div>
	<div class="st-flex space-between">
		<h2 class="st-heading-section mg0"><?php echo esc_html__( 'Review', 'traveler' ); ?></h2>
		<div class="f18 font-medium15">
			<span class="mr15"><?php comments_number( __( '0 review', 'traveler' ), __( '1 review', 'traveler' ), __( '% reviews', 'traveler' ) ); ?></span>
			<?php
			echo st()->load_template( 'layouts/modern/common/star', '', [
				'star'    => $review_rate,
				'style'   => 'style-2',
				'element' => 'span',
			] );
			?>
		</div>
	</div>
	<div id="reviews" class="hotel-room-review">
		<div class="review-pagination">
			<div id="reviews" class="review-list">
				<?php
				$comments_count   = wp_count_comments( get_the_ID() );
				$total            = (int) $comments_count->approved;
				$comment_per_page = (int) get_option( 'comments_per_page', 10 );
				$paged            = (int) STInput::get( 'comment_page', 1 );
				$from             = $comment_per_page * ( $paged - 1 ) + 1;
				$to               = ( $paged * $comment_per_page < $total ) ? ( $paged * $comment_per_page ) : $total;
				$offset           = ( $paged - 1 ) * $comment_per_page;
				$args             = [
					'number'  => $comment_per_page,
					'offset'  => $offset,
					'post_id' => get_the_ID(),
					'status'  => [ 'approve' ],
				];
				$comments_query   = new WP_Comment_Query;
				$comments         = $comments_query->query( $args );

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
						<a href="" class="toggle-section c-main f16" data-target="st-review-form">
							<?php echo __( 'Write a review', 'traveler' ) ?><i class="fa fa-angle-down ml5"></i>
						</a>
					</h4>
					<?php TravelHelper::comment_form(); ?>
				</div>
			<?php
		}
		?>
	</div>
<?php } ?>
