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
// $extras = isset($item['data']['data_equipment']) ? $item['data']['data_equipment'] : array();
$extras = isset($item['data']['extras']) ? $item['data']['extras'] : array();

$time = $item['data']['distance'];
$hour = ( $time[ 'hour' ] >= 2 ) ? esc_html($time[ 'hour' ]) . ' ' . esc_html__( 'hours', 'traveler' ) : $time[ 'hour' ] . ' ' . esc_html__( 'hour', 'traveler' );
$minute = ( $time[ 'minute' ] >= 2 ) ? esc_html($time[ 'minute' ]) . ' ' . esc_html__( 'minutes', 'traveler' ) : esc_html($time[ 'minute' ]) . ' ' . esc_html__( 'minute', 'traveler' );
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
					echo __('Car Transfer', 'traveler');
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
			if(!empty($time[ 'hour' ]) || !empty($time[ 'minutes' ]) || !empty($time[ 'distance' ])){ ?>
				<li>
					<span class="label"><?php echo __('Est. Distance', 'traveler'); ?></span>
					<span class="value">
						<?php
						echo esc_attr( $hour ) . ' ' . esc_attr( $minute ) . ' - ' .esc_html( $time['distance']) . __('Km', 'traveler');
						?>
					</span>
				</li>
			<?php }
		?>
        <li>
			<span class="label"><?php echo __('Pickup Date', 'traveler'); ?></span>
			<span class="value"><?php echo date(TravelHelper::getDateFormat() . ', H:i A', $item['data']['check_in_timestamp']); ?></span>
        </li>
        <?php
		$price_type = get_post_meta( $item_id, 'price_type', true );
		$price_unit = '';
		switch ($price_type) {
			case 'distance':
				$price_unit = __(' Per Km', 'traveler');
				break;
			case 'passenger':
				$price_unit = __(' Per Person', 'traveler');
				break;
			case 'fixed':
				$price_unit = __('', 'traveler');
				break;
		}
		if(!empty($time[ 'distance' ])){
			echo '<li>
			<span class="label">'. __("Direction Price", 'traveler').'</span> ' . '(' . esc_html( $time['distance']) . __('Km', 'traveler') .')
			<span class="value">'.TravelHelper::format_money($item['data']['price']). $price_unit . '</span>
			</li>';
		}
		if($item['data']['has_return'] === 'yes'){

			echo '<li>
				<span class="label">'. __("Towards Price", 'traveler').'</span> ' . '(' . esc_html( $time['distance_return']) . __('Km', 'traveler') .')
				<span class="value">'.TravelHelper::format_money($item['data']['price_return']). $price_unit . '</span>
			</li>';
		}



        /*diff date*/
        /*$date1 = strtotime(date(TravelHelper::getDateFormat() . ', H:i ', $item['data']['check_in_timestamp']));
        $date2 = strtotime(date(TravelHelper::getDateFormat() . ', H:i ', $item['data']['check_out_timestamp']));
        $diff = abs($item['data']['check_out_timestamp'] - $item['data']['check_in_timestamp']);
        $years = floor($diff / (365*60*60*24));
        $months = floor(($diff - $years * 365*60*60*24)
                       / (30*60*60*24));
        $days_extra = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));*/
        $days_extra = $item['data']['numberday'];
        ?>
        <?php if (!empty($item['data']['extras']) && $item['data']['extra_price']): ?>
            <li>
                <span class="label"><?php echo __('Extra', 'traveler'); ?></span>
            </li>
            <li class="extra-value">

				<?php
				foreach ($extras as $name => $number):
					$number_item = intval($number['number']);
					$price_item = floatval($number['price']);
					if ($price_item <= 0) $price_item = 0;
					?>
					<span class="pull-left">
						<?php
							echo esc_html($number['title']) . ' (' . TravelHelper::format_money($price_item) . ') x ' . esc_html($number_item) . ' ' . __('Item(s)', 'traveler');
						?>
					</span> <br/>
				<?php endforeach;
				?>

            </li>
        <?php endif; ?>
    </ul>
</div>