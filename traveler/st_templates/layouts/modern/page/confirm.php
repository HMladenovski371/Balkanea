<?php
get_header();
$message = STTemplate::get_message();
?>
	<div class="gap"></div>
	<div id="st-content-wrapper" class="st-style-elementor">
		<?php
			$inner_style      = '';
			$thumb_id         = get_post_thumbnail_id( get_the_ID() );
			$menu_transparent = st()->get_option( 'menu_transparent', '' );
			$img              = wp_get_attachment_image_url( $thumb_id, 'full' );
			$inner_style      = Assets::build_css( 'background-image: url(' . esc_url( $img ) . ') !important;' );

		if ( $menu_transparent == 'on' ) {
			?>
			<div class="banner st-bg-feature <?php echo esc_attr( $inner_style ) ?>">
				<div class="container">
					<div class="st-banner-search-form style_2">
						<h1 class="st-banner-search-form__title"><?php the_title(); ?></h1>
						<?php echo st_breadcrumbs_new(); ?>
					</div>
				</div>
			</div>
		<?php } else { ?>
			<div class="st-breadcrumb">
				<div class="container">
					<ul>
						<li>
							<a href="<?php echo site_url( '/' ) ?>"><?php echo __( 'Home', 'traveler' ); ?></a>
						</li>
						<li>
							<span><?php echo get_the_title(); ?></span>
						</li>
					</ul>
				</div>
			</div>
			<?php
		}
		?>
	</div>
	<div class="container">
		<div class="row">
			<div class="st-confirm-order col-md-8 col-md-offset-2">
				<?php if ( $message['type'] ) : ?>
					<i class="fa fa-check box-iconn-successnew"></i>
				<?php else : ?>
					<i class="fa fa-close box-iconn-dangernew"></i>
				<?php endif; ?>
				<h2 class="text-center">
					<?php
					echo esc_html( $message['content'] );
					STTemplate::clear();
					?>
				</h2>
			</div>
		</div>
	</div>
<?php
get_footer();
