<?php
/**
 * @package WordPress
 * @subpackage Traveler
 * @since 1.0
 *
 * hotel payment item row
 *
 * Created by ShineTheme
 *
 */
$item = $data;
$item_id = $item['data']['st_booking_id'];
$extras = isset($item['data']['data_equipment']) ? $item['data']['data_equipment'] : array();
$discount_rate = isset($item['data']['discount_rate']) ? $item['data']['discount_rate'] : '';
$check_in_timestamp  = $item['data']['check_in_timestamp'];
$check_out_timestamp = $item['data']['check_out_timestamp'];
$date_diff           = STCars::get_date_diff($check_in_timestamp,$check_out_timestamp);
$list_price_by_date = STCars::get_info_price($item_id,$check_in_timestamp,$check_out_timestamp);
$price_by_date      = 0;
foreach ( $list_price_by_date['list_price'] as $list_price ) {
	$price_by_date = $list_price['price'];
}

$currency = get_post_meta( $item_id, 'currency', true );

?>
<div class="service-section">
    <div class="service-left">
        <h3 class="title"><a href="<?php echo get_permalink($item_id) ?>"><?php echo get_the_title($item_id) ?></a></h3>
        <?php
        $address = get_post_meta($item_id, 'cars_address', true);
        if ($address):
            ?>
            <p class="address"><?php echo TravelHelper::getNewIcon('Ico_maps', '#666666', '15px', '15px', true); ?><?php echo esc_html($address); ?> </p>
        <?php
        endif;
        ?>
    </div>
    <div class="service-right">
        <?php echo get_the_post_thumbnail($item_id, array(110, 110, 'bfi_thumb' => true), array('alt' => TravelHelper::get_alt_image(get_post_thumbnail_id($item_id)), 'class' => 'img-responsive')); ?>
    </div>
</div>
<div class="info-section">
    <ul>
        <li>
			<span class="label">
				<?php echo __('Car type', 'traveler'); ?>
			</span>
            <span class="value">
					<?php
					$cartype = get_the_terms($item_id, 'st_category_cars');
					if (!is_wp_error($cartype) && !empty($cartype)) {
						$cartype_html = '';
						foreach ($cartype as $type) {
							$cartype_html .= $type->name . ', ';
						}
						if (!empty($cartype_html)) {
							echo substr($cartype_html, 0, -2);
						}
					}
					?>
			</span>
        </li>
        <!--Add Info-->
        <li>
            <span class="label"><?php echo __('Pick Up From', 'traveler'); ?></span>
            <span class="value"><?php echo esc_html($item['data']['pick_up']); ?></span>
        </li>
        <li>
            <span class="label"><?php echo __('Drop Off To', 'traveler'); ?></span>
            <span class="value"><?php echo esc_html($item['data']['drop_off']); ?></span>
        </li>
        <?php
            if(isset($item['data']['data_destination']) && !empty($item['data']['data_destination'])){
        ?>
        <li>
            <span class="label"><?php echo __('Est. Distance', 'traveler'); ?></span>
            <span class="value"><?php echo round($item['data']['data_destination'], 2); ?> <?php echo strtolower(STCars::get_price_unit('label')) ?></span>
        </li>
        <?php }?>
        <li>
            <span class="label"><?php echo __('Date', 'traveler'); ?></span>
            <span class="value"><?php echo date(TravelHelper::getDateFormat() . ', H:i A', $item['data']['check_in_timestamp']); ?> - <?php echo date(TravelHelper::getDateFormat() . ', H:i A', $item['data']['check_out_timestamp']); ?></span>
        </li>
		<li>
			<span class="label"><?php echo __( 'Number of Date', 'traveler' ); ?></span>
			<span class="value"><?php echo esc_html( $date_diff ) ?></span>
		</li>
        <?php
			$is_custom_price = get_post_meta($item_id, 'is_custom_price', true);
			$price_by_date   = $price_by_date / ( 1 - $discount_rate / 100 );
			if ( isset($item['data']['car_title_sale_price']['title_price']) && !empty($item['data']['car_title_sale_price']['title_price'])) {
				if ( $is_custom_price == 'price_by_number' ) {
					?>
					<li>
						<?php $unit = st()->get_option('cars_price_unit', 'day'); ?>
						<span class="label">
							<?php printf( esc_html__( 'Price by number of %s', 'traveler' ), $unit ) ?>
						</span>
					</li>
					<li class="extra-value">
						<span class="label"><?php echo esc_html($item['data']['car_title_sale_price']['title_price']['title']); ?></span>
						<span class="value"><?php echo TravelHelper::format_money_from_db($item['data']['car_title_sale_price']['title_price']['price'], $currency); ?></span>
					</li>
					<?php
				} else {
					?>
					<li class="extra-value">
						<span class="label"><?php echo esc_html__( 'Price by date', 'traveler' ) ?></span>
						<span class="value"><?php echo TravelHelper::format_money_from_db($price_by_date, $currency); ?></span>
					</li>
					<?php
				}
			}
        ?>
        <?php
        /*diff date*/
        /*$date1 = strtotime(date(TravelHelper::getDateFormat() . ', H:i ', $item['data']['check_in_timestamp']));
        $date2 = strtotime(date(TravelHelper::getDateFormat() . ', H:i ', $item['data']['check_out_timestamp']));
        $diff = abs($item['data']['check_out_timestamp'] - $item['data']['check_in_timestamp']);
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24)
                       / (30*60*60*24));
        $days_extra = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));*/
        $days_extra = $item['data']['numberday'];

		$data_price = get_post_meta($order_id, 'data_prices', true);
		// var_dump(count($extras['value']));
        ?>
        <?php if (isset($data_price['price_equipment']) && $data_price['price_equipment'] > 0): ?>
            <li>
                <span class="label"><?php echo __('Extra', 'traveler'); ?></span>
            </li>
            <li class="extra-value">
                <?php
                foreach ($extras['value'] as $name => $number):
                    $number_item = intval($extras['value'][$name]);
                    if ($number_item <= 0) $number_item = 0;
                    if ($number_item > 0):
                        $price_item = floatval($extras['price'][$name]);
                        if ($price_item <= 0) $price_item = 0;
                        $price_type = !empty($extras['price_type'][$name]) ? $extras['price_type'][$name] : "";
                        ?>
                        <span class="pull-right">
                            <?php
                            if($price_type == 'fixed'){
                                echo esc_html($extras['title'][$name]) . ' (' . TravelHelper::format_money_from_db($price_item, $currency) . ') x ' . esc_html($number_item) . ' ' . __('Item(s)', 'traveler');
                            }else{
                                echo esc_html($extras['title'][$name]) . ' (' . TravelHelper::format_money_from_db($price_item, $currency) . ') x ' . esc_html($number_item) . ' ' . __('Item(s)', 'traveler'). ' x '.esc_html($days_extra).' '. __('Day(s)', 'traveler');
                            }

                            ?>
                            </span> <br/>
                    <?php endif;
                endforeach;
                ?>
            </li>
        <?php endif; ?>
    </ul>
</div>
