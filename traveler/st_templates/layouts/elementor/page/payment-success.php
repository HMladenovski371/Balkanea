<?php
get_header();
$order_code       = STInput::get( 'order_code' );
$order_token_code = STInput::get( 'order_token_code' );

if ( $order_token_code ) {
	$order_code = STOrder::get_order_id_by_token( $order_token_code )->post_id;
}

$user_id = get_current_user_id();


if ( ! $order_code or get_post_type( $order_code ) != 'st_order' ) {
	wp_redirect( home_url( '/' ) );
	exit;
}
$status_order      = get_post_meta( $order_code, 'status', true );
$gateway           = get_post_meta( $order_code, 'payment_method', true );
$st_payment_method = get_post_meta( $order_code, 'payment_method', true );
$item_id = get_post_meta( $order_code, 'item_id', true );
$room_id = get_post_meta( $order_code, 'room_id', true );
if(!empty($room_id)){
	$item_id = $room_id;
}
if($item_id == 'car_transfer'){
	$item_id = get_post_meta( $order_code, 'car_id', true );
}

do_action( 'st_destroy_cart_complete' );
?>
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
		<?php $page_style = st()->get_option( 'page_checkout_style', 1 ); ?>
		<div class="st-checkout-page <?php echo 'style-' . esc_attr( $page_style ) ?>">
			<?php
			if ( isset( $_REQUEST['order_token_code'] ) && $status_order !== 'complete' && $st_payment_method === 'st_razor' ) {
				do_action( 'st_receipt_st_razor', $order_code );
			}
			if ( isset( $_REQUEST['order_token_code'] ) && $status_order !== 'complete' && $st_payment_method === 'st_iyzico' ) {
				do_action( 'st_receipt_st_iyzico', $order_code );
			}
			if ( isset( $_REQUEST['order_token_code'] ) && $status_order === 'complete' && $st_payment_method === 'st_razor' ) {
				do_action( 'st-sendmail-razor-pay', $order_code );
			}
			$is_show_infomation_allow = STPaymentGateways::gateway_success_page_validate( $gateway , $order_code  ) ?? false;

			do_action( 'remove_message_session' );
			echo st()->load_template( 'layouts/elementor/page/booking_infomation', null, [ 'order_code' => $order_code ] );
			?>
		</div>

		<?php

			$upsell = get_post_meta($item_id,'st_upsell', true);
			if(!empty($upsell['type-service']) && !empty($upsell['list-item'])){
				$args_upsell = [
					'post_type' => $upsell['type-service'],
					'post__in' => $upsell['list-item'],
					'posts_per_page' => -1
				];
				$query_service = new WP_Query($args_upsell);
				if($upsell['type-service'] == 'st_cars'){
					$class_car = ' car-layout4';
				} else {
					$class_car = '';
				}

				?>
				<hr>
        		<h2 class="st-heading-section mb-3 mt-5"><?php echo esc_html__('You May Also Like', 'traveler'); ?></h2>
				<div id="modern-search-result" class="modern-search-result list-tab-wrapper style_2" data-layout="4">
					<div class="service-list-wrapper service-tour<?php echo esc_attr($class_car);?> row">
						<?php
							switch ($upsell['type-service']) {
								case 'st_activity':
									$result_tour_page = st()->get_option('activity_search_result_page');
									$layout_activity = get_post_meta( $result_tour_page, 'rs_layout_activity', true );
									if($query_service->have_posts()) :
										while($query_service->have_posts()) : $query_service->the_post();
											if($layout_activity == 3 || $layout_activity == 4){
												echo '<div class="col-lg-3 col-md-6 col-12 item-service">';
												echo stt_elementorv2()->loadView('services/activity/loop/grid');
												echo '</div>';
											} else {
												echo st()->load_template('layouts/elementor/activity/loop/normal-grid', '',array('item_row'=> 4));
											}

										endwhile;
									endif;
									break;
								case 'st_hotel':
									$result_hotel_page = st()->get_option('hotel_search_result_page');
									$layout_hotel = get_post_meta( $result_hotel_page, 'rs_layout', true );
									if($query_service->have_posts()) :
										while($query_service->have_posts()) : $query_service->the_post();
											if($layout_hotel == 5 || $layout_hotel == 6){
												echo '<div class="col-lg-3 col-md-6 col-12 item-service">';
												echo stt_elementorv2()->loadView('services/hotel/loop/grid');
												echo '</div>';
											} else {
												echo st()->load_template('layouts/elementor/hotel/loop/normal-grid', '',array('item_row'=> 4));
											}

										endwhile;
									endif;
									break;
								case 'st_tours':
									$result_tour_page = st()->get_option('tours_search_result_page');
									$layout_tour = get_post_meta( $result_tour_page, 'rs_layout_tour', true );
									if($query_service->have_posts()) :
										while($query_service->have_posts()) : $query_service->the_post();
											if($layout_tour == 5 || $layout_tour == 6){
												echo '<div class="col-lg-3 col-md-6 col-12 item-service">';
												echo stt_elementorv2()->loadView('services/tour/loop/grid');
												echo '</div>';
											} else {
												echo st()->load_template('layouts/elementor/tour/loop/normal-grid', '',array('item_row'=> 4));
											}

										endwhile;
									endif;
									break;
								case 'st_rental':
									$result_rental_page = st()->get_option('rental_search_result_page');
									$layout_rental = get_post_meta( $result_rental_page, 'rs_layout_rental', true );
									if($query_service->have_posts()) :
										while($query_service->have_posts()) : $query_service->the_post();
											if($layout_rental == 4 || $layout_rental == 5){
												echo '<div class="col-lg-3 col-md-6 col-12 item-service">';
												echo stt_elementorv2()->loadView('services/rental/loop/grid');
												echo '</div>';
											} else {
												echo st()->load_template('layouts/elementor/rental/loop/normal-grid', '',array('item_row'=> 4));
											}


										endwhile;
									endif;
									break;
								case 'st_cars':
									$result_car_page = st()->get_option('cars_search_result_page');
									$layout_car = get_post_meta( $result_car_page, 'rs_layout_car', true );
									if($query_service->have_posts()) :
										while($query_service->have_posts()) : $query_service->the_post();
											if($layout_car == 3 || $layout_car == 4){
												echo '<div class="col-lg-3 col-md-6 col-12 item-service">';
												echo stt_elementorv2()->loadView('services/car/loop/grid');
												echo '</div>';
											} else {
												echo st()->load_template('layouts/elementor/car/loop/normal-grid', '',array('item_row'=> 4));
											}

										endwhile;
									endif;
									break;
								default:
									# code...
									break;
							}
						?>
					</div>
				</div>
			<?php }
		?>
	</div>

<?php
get_footer();
?>
