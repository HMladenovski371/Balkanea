<?php
/**
 * Created by PhpStorm.
 * User: MSI
 * Date: 14/07/2015
 * Time: 3:17 CH
 */
$item_data=isset($item['item_meta'])?$item['item_meta']:array();
$format=TravelHelper::getDateFormat();

$room_id = $item_data['_st_st_booking_id'];
$check_in = $item_data['_st_check_in'];
$check_out = $item_data['_st_check_out'];
$date_diff = STDate::dateDiff($check_in,$check_out);
$origin_price = STPrice::getRentalPriceOnlyCustomPrice($room_id, strtotime($check_in), strtotime($check_out));

?>
<ul class="wc-order-item-meta-list">
    <?php if(isset($item_data['_st_check_in'])): $data= $item_data['_st_check_in']; ?>
        <li>
            <span class="meta-label"><?php _e('Date:','traveler') ?></span>
            <span class="meta-data"><?php
                echo esc_html($data);
                ?>
                <?php if(isset($item_data['_st_check_out'])){ $data=$item_data['_st_check_out']; ?>
                    -
                    <?php echo esc_html($data); ?>
                <?php }?>


            </span>
        </li>
    <?php endif;?>

    <?php if(isset($item_data['_st_adult_number'])):?>
        <li>
            <span class="meta-label"><?php _e('Adult:','traveler') ?></span>
            <span class="meta-data"><?php echo esc_html($item_data['_st_adult_number']) ?></span>
        </li>
    <?php endif; ?>
    <?php if(isset($item_data['_st_child_number'])):?>
        <li>
            <span class="meta-label"><?php _e('Children:','traveler') ?></span>
            <span class="meta-data"><?php echo esc_html($item_data['_st_child_number']) ?></span>
        </li>
    <?php endif;?>

	<li>
		<?php echo __( 'Price Detail', 'traveler' ); ?>:
		<?php echo TravelHelper::format_money( $origin_price ) ?>
		<?php echo sprintf( _n( '(%s night)', '(%s nights)', $date_diff, 'traveler' ), $date_diff ) ?>
	</li>

    <?php if(isset($item_data['_st_extras']) and ($extra_price = $item_data['_st_extra_price'])): $data=$item_data['_st_extras'];?>
        <li>
        <p><?php echo __("Extra prices"  ,'traveler') .": "; ?></p>
        <ul>
        <?php
		$extra_type = isset($item_data['_st_extra_type']) ? $item_data['_st_extra_type'] : 'perday';
		$extra_unit = $extra_type == 'perday' ?  __('Per Night', 'traveler') : '';
        if(!empty($data['title']) and  is_array($data['title'])){
            foreach ($data['title'] as $key => $title) { ?>
                <?php if($data['value'][$key]){ ?>
                    <li style="padding-left: 10px "> <?php echo esc_attr($title) ;?>:
                    <?php
                        echo esc_html($data['value'][$key]) ;?> x <?php echo TravelHelper::format_money($data['price'][$key]) . ' ' . $extra_unit ;
                    ?>
                </li>
                <?php }?>
            <?php }
        }
        ?>
        </ul>
        </li>
    <?php endif; ?>
    <?php if(isset($item_data['_st_discount_rate'])):
		$data = $item_data['_st_discount_rate'];
		$discount_type = $item_data['_st_discount_type'];
		$discount_html = $discount_type == 'amount' ? TravelHelper::format_money($data) : $data . '%' ;
		?>
        <?php if (!empty($data)) {?><li><p>
            <?php echo __("Discount/Night"  ,'traveler') .": "; ?>
            <?php echo $discount_html; ?>
        <?php } ;?></p></li>
    <?php endif; ?>

	<?php if (isset($item_data['_st_total_bulk_discount'])) :
		$total_bulk_discount = $item_data['_st_total_bulk_discount'];
		?>
		<?php if ($total_bulk_discount > 0) {?><li><p>
            <?php echo __("Bulk Discount"  ,'traveler') .": "; ?>
            <?php echo TravelHelper::format_money($total_bulk_discount); ?>
        <?php } ;?></p></li>
	<?php endif; ?>

    <?php if(isset($item_data['_line_tax'])): $data=$item_data['_line_tax'];?>
            <?php  if (!empty($data)) {?><li><p>
            <?php echo __("Tax"  ,'traveler') .": "; ?>
            <?php echo TravelHelper::format_money($data) ;?>
        <?php } ;?></p></li>
    <?php endif; ?>

</ul>