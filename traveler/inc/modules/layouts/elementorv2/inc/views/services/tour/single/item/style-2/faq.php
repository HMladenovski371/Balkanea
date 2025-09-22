<!--Tour FAQ-->
<?php
				$stack    = [];
				$tour_faqs = get_post_meta( get_the_ID(), 'tours_faq', true );
				if ( ! empty( $tour_faqs ) ) {?>
					<div class="st-faq">
						<h2 class="st-heading-section">
							<?php echo esc_html__( 'Frequently Asked Questions', 'traveler' ); ?>
						</h2>
						<div class="st-flex--faq row">
							<div class="col-md-12 st-faq--content st-left">
							<?php
							foreach ($tour_faqs as $tour_faq) {
								?>
									<div class="item">
										<div class="header">
											<h5><?php echo esc_html( $tour_faq['title'] ); ?></h5>
											<span class="arrow">
												<i class="fa fa-angle-down"></i>
											</span>
										</div>
										<div class="body">
										<?php echo balanceTags( $tour_faq['desc'] ); ?>
										</div>
									</div>
								<?php
							}
							?>
							</div>
						</div>
					</div>
					<?php
				}
				?>
				<!--End Tour FAQ-->