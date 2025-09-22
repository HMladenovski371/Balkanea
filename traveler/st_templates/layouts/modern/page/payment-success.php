<?php
$order_code = STInput::get('order_code');
$order_token_code=STInput::get('order_token_code');

if($order_token_code)
{
	$order_code=STOrder::get_order_id_by_token($order_token_code)->post_id;

}
$user_id = get_current_user_id();


if (!$order_code or get_post_type($order_code) != 'st_order') {
	wp_redirect(home_url('/'));
	exit;
}
$status_order = get_post_meta($order_code,'status',true);
$gateway=get_post_meta($order_code,'payment_method',true);
$st_payment_method = get_post_meta($order_code, 'payment_method', true);

$item_id = get_post_meta( $order_code, 'item_id', true );
$room_id = get_post_meta( $order_code, 'room_id', true );
if(!empty($room_id)){
	$item_id = $room_id;
}
if($item_id == 'car_transfer'){
	$item_id = get_post_meta( $order_code, 'car_id', true );
}
get_header();
do_action('st_destroy_cart_complete');

?>
	<div id="st-content-wrapper">
		<div class="st-breadcrumb">
			<div class="container">
				<ul>
					<li>
						<a href="<?php echo site_url('/') ?>"><?php echo __('Home', 'traveler'); ?></a>
					</li>
					<li>
						<span><?php echo get_the_title(); ?></span>
					</li>
				</ul>
			</div>
		</div>
		<div class="container">
			<div class="st-checkout-page">
				<?php
				if(isset($_REQUEST['order_token_code']) && $status_order  !== 'complete' && $st_payment_method === 'st_razor'){
					do_action( 'st_receipt_st_razor', $order_code );
				}
				if(isset($_REQUEST['order_token_code']) && $status_order  === 'complete' && $st_payment_method === 'st_razor'){
					do_action( 'st-sendmail-razor-pay', $order_code );
				}
				echo STTemplate::message();
				STTemplate::clear();
				$is_show_infomation_allow = STPaymentGateways::gateway_success_page_validate($gateway , $order_code);
				if($is_show_infomation_allow) {
					echo st()->load_template('layouts/modern/page/booking_infomation',null,array('order_code'=>$order_code));
				}else{
					echo st()->load_template('layouts/modern/page/booking_infomation',null,array('order_code'=>$order_code));
				}
				?>
			</div>
		</div>
	</div>

	<div class="container">
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
				if($upsell['type-service'] == 'st_rental'){
					$class_rental = ' st-rental';
				} else {
					$class_rental = '';
				}
				?>
				<hr>
        		<h2 class="st-heading-section mb-3 mt-5"><?php echo esc_html__('You May Also Like', 'traveler'); ?></h2>
				<div id="modern-search-result" class="modern-search-result list-tab-wrapper style_2<?php echo esc_attr($class_rental);?>">
					<div class="service-list-wrapper service-tour<?php echo esc_attr($class_car);?> row">
						<?php
								switch ($upsell['type-service']) {
									case 'st_activity':
										$result_tour_page = st()->get_option('activity_search_result_page');
										$layout_activity = get_post_meta( $result_tour_page, 'rs_layout_activity', true );
										if($query_service->have_posts()) :
											while($query_service->have_posts()) : $query_service->the_post();
												echo st()->load_template('layouts/modern/activity/elements/loop/grid', '',array('top_search'=> true));
											endwhile;
										endif;
										break;
									case 'st_hotel':
										$result_hotel_page = st()->get_option('hotel_search_result_page');
										$layout_hotel = get_post_meta( $result_hotel_page, 'rs_layout', true );
										if($query_service->have_posts()) :
											while($query_service->have_posts()) : $query_service->the_post();
												echo st()->load_template('layouts/modern/hotel/elements/loop/normal-grid', '',array('fullwidth'=> true));
											endwhile;
										endif;
										break;
									case 'st_tours':
										$result_tour_page = st()->get_option('tours_search_result_page');
										$layout_tour = get_post_meta( $result_tour_page, 'rs_layout_tour', true );
										if($query_service->have_posts()) :
											while($query_service->have_posts()) : $query_service->the_post();
												echo st()->load_template('layouts/modern/tour/elements/loop/grid', '',array('col'=> 3));
											endwhile;
										endif;
										break;
									case 'st_rental':
										$result_rental_page = st()->get_option('rental_search_result_page');
										$layout_rental = get_post_meta( $result_rental_page, 'rs_layout_rental', true );
										if($query_service->have_posts()) :
											while($query_service->have_posts()) : $query_service->the_post();
												echo '<div class="col-lg-3 col-md-6 col-sm-6 col-xs-12 ">';
												echo st()->load_template('layouts/modern/rental/elements/loop/grid', '',array('col'=> 3));
												echo '</div>';
											endwhile;
										endif;
										break;
									case 'st_cars':
										$result_car_page = st()->get_option('cars_search_result_page');
										$layout_car = get_post_meta( $result_car_page, 'rs_layout_car', true );
										if($query_service->have_posts()) :
											while($query_service->have_posts()) : $query_service->the_post();
												echo st()->load_template('layouts/modern/car/elements/loop/grid', '',array('top_search'=> true));
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
			<?php
			wp_reset_postdata();
			}
		?>
	</div>
<?php
get_footer();
?>