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
$hotel = $item['data']['st_booking_id'];
$item_id = $hotel;

$check_in = $item['data']['check_in'];
$check_out = $item['data']['check_out'];

$date_diff = STDate::dateDiff($check_in,$check_out);

$extras = isset($item['data']['extras']) ? $item['data']['extras'] : array();
$adult_number = intval($item['data']['adult_number']);
$child_number = intval($item['data']['child_number']);

$check_extra = false;
if(!empty($extras["value"]) && is_array(array_values($extras["value"]))){
	foreach(array_values($extras["value"]) as $value_number){
		if($value_number > 0){
			$check_extra = true;
			break;
		}
	}
}

?>
<div class="service-section">
    <div class="service-left">
        <h3 class="title"><a href="<?php echo get_permalink($hotel)?>"><?php echo get_the_title($hotel)?></a></h3>
        <?php
        $address = get_post_meta( $item_id, 'address', true);
        if( $address ):
            ?>
            <p class="address"><?php echo TravelHelper::getNewIcon('Ico_maps', '#666666', '15px', '15px', true); ?><?php echo esc_html($address); ?> </p>
        <?php
        endif;
        ?>
    </div>
    <div class="service-right">
        <?php echo get_the_post_thumbnail($hotel,array(110,110,'bfi_thumb'=>true), array('alt' => TravelHelper::get_alt_image(get_post_thumbnail_id($hotel )), 'class' => 'img-responsive'));?>
    </div>
</div>
<div class="info-section">
    <ul>
        <li><span class="label"><?php echo __('Name', 'traveler'); ?></span><span class="value"><?php echo get_the_title($item_id)?></span></li>
        <li>
            <span class="label">
                <?php echo __('Date', 'traveler'); ?>
            </span>
            <span class="value">
                <?php echo date_i18n( TravelHelper::getDateFormat(), strtotime( $check_in ) ); ?>
                -
                <?php echo date_i18n( TravelHelper::getDateFormat(), strtotime( $check_out ) ); ?>
                <?php
                $start = date( TravelHelper::getDateFormat(), strtotime( $check_in ) );
                $end   = date( TravelHelper::getDateFormat(), strtotime( $check_out ) );
                $date  = date( 'd/m/Y h:i a', strtotime( $check_in ) ) . '-' . date( 'd/m/Y h:i a', strtotime( $check_out ) );
                $args  = [
                    'start' => $start,
                    'end'   => $end,
                    'date'  => $date
                ];
                ?>
            </span>
        </li>
        <li class="ad-info">
            <ul>
                <li><span class="label"><?php echo __('Number of Night', 'traveler'); ?></span><span class="value">
                        <?php
                        if($date_diff>1){
                            printf(__('%d Nights', 'traveler'),$date_diff);
                        }else{
                            printf(__('%d Night', 'traveler'),$date_diff);
                        }
                        ?>
                    </span></li>
                <?php if($adult_number) {?>
                    <li><span class="label"><?php echo __('Number of Adult', 'traveler'); ?></span><span class="value"><?php echo esc_attr($adult_number); ?></span></li>
                <?php } ?>
                <?php if($child_number) {?>
                    <li><span class="label"><?php echo __('Number of Children', 'traveler'); ?></span><span class="value"><?php echo esc_attr($child_number); ?></span></li>
                <?php } ?>
            </ul>
        </li>
        <?php if($check_extra):
            ?>
			<li><span class="label"><?php echo __('Extra', 'traveler'); ?></span></li>
            <div class="extra-value">
                <?php
				$extra_type = isset($item['data']['extra_type']) ? $item['data']['extra_type'] : 'perday';
				$extra_unit = $extra_type == 'perday' ?  __('Per Night', 'traveler') : '';
                foreach ($extras['value'] as $name => $number):
                    $number_item = intval($extras['value'][$name]);
                    if ($number_item <= 0) $number_item = 0;
                    if ($number_item > 0):
                        $price_item = floatval($extras['price'][$name]);
                        if ($price_item <= 0) $price_item = 0;
                        ?>
						<div class="pull-right">
							<?php echo esc_html($extras['title'][$name]) . ': ' . esc_attr($number_item) . ' x ' . TravelHelper::format_money($price_item) . ' ' . $extra_unit; ?>
						</div>
                    <?php endif;
                endforeach;
                ?>
            </div>
        <?php endif; ?>
        <?php
        if(isset($item['data']['deposit_money'])):
            $deposit      = $item['data']['deposit_money'];
            if(!empty($deposit['type']) and !empty($deposit['amount'])){
                $deposite_amount = '';
                $deposite_type = '';
                switch($deposit['type']){
                    case "percent":
                        $deposite_amount = $deposit['amount'] . ' %';
                        $deposite_type = __('percent', 'traveler');
                        break;
                    case "amount":
                        $deposite_amount = TravelHelper::format_money($deposit['amount']);
                        $deposite_type = __('amount', 'traveler');
                        break;
                } ?>
                <li>
                    <span class="label">
                        <?php echo esc_html(__('Deposit','traveler')) ?>
                        <?php echo ' '.esc_html($deposite_type) ?>
                    </span>
                    <span class="value pull-right">
                        <?php
                        echo esc_html($deposite_amount);
                        ?>
                    </span>
                </li>
            <?php }
        endif; ?>

        <li class="guest-value">
            <?php st_print_order_item_guest_name($data['data']) ?>
        </li>
    </ul>
</div>
