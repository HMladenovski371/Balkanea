<?php

    /**

     * @package    WordPress

     * @subpackage Traveler

     * @since      1.0

     *

     * Admin cars booking edit

     *

     * Created by ShineTheme

     *

     */



    $st_tab = STInput::request('car_type','normal');

    $item_id = isset( $_GET[ 'order_item_id' ] ) ? $_GET[ 'order_item_id' ] : false;



    if($st_tab == 'normal')

        $order_item_id = get_post_meta( $item_id, 'item_id', true );

    else

	    $order_item_id = get_post_meta( $item_id, 'car_id', true );



    $order = $item_id;



    if ( !isset( $page_title ) ) {

        $page_title = __( 'Edit Car Booking', 'traveler' );

    }

	$currency = get_post_meta( $item_id, 'currency', true );

?>

<div class="wrap">

    <?php echo '<h2>' . $page_title . '</h2>'; ?>

    <?php STAdmin::message() ?>

    <div id="post-body" class="columns-2">

        <div id="post-body-content">

            <div class="postbox-container">

                <form method="post" action="" id="form-booking-admin" class="main-search">

                    <?php wp_nonce_field( 'shb_action', 'shb_field' ) ?>

                    <div id="poststuff">

                        <div class="postbox">

                            <div class="handlediv" title="<?php _e( 'Click to toggle', 'traveler' ) ?>"><br></div>

                            <h3 class="hndle ui-sortable-handle">

                                <span><?php _e( 'Order Information', 'traveler' ) ?></span></h3>

                            <div class="inside">

                                <div class="form-row">

                                    <label class="form-label" for=""><?php _e( 'Customer ID', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $id_user = '';

                                            $pl_name = '';

                                            if ( $item_id ) {

                                                $id_user = get_post_meta( $item_id, 'id_user', true );



                                                if ( $id_user ) {

                                                    $user = get_userdata( $id_user );

                                                    if ( $user ) {

                                                        $pl_name = $user->ID . ' - ' . $user->user_email;

                                                    }

                                                }

                                            }

                                        ?>

                                        <input readonly type="text" name="id_user"

                                               value="<?php echo esc_attr( $pl_name ); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>



                                <?php ob_start(); ?>



                                <div class="form-row">

                                    <label class="form-label" for=""><?php _e( 'Customer First Name', 'traveler' ) ?>

                                        <span class="require"> (*)</span></label>

                                    <div class="controls">

                                        <?php

                                            $st_first_name = isset( $_POST[ 'st_first_name' ] ) ? $_POST[ 'st_first_name' ] : get_post_meta( $item_id, 'st_first_name', true );

                                        ?>

                                        <input type="text" name="st_first_name" value="<?php echo esc_attr($st_first_name); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label" for=""><?php _e( 'Customer Last Name', 'traveler' ) ?>

                                        <span class="require"> (*)</span></label>

                                    <div class="controls">

                                        <?php

                                            $st_last_name = isset( $_POST[ 'st_last_name' ] ) ? $_POST[ 'st_last_name' ] : get_post_meta( $item_id, 'st_last_name', true );

                                        ?>

                                        <input type="text" name="st_last_name" value="<?php echo esc_attr($st_last_name); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label" for=""><?php _e( 'Customer Email', 'traveler' ) ?><span

                                            class="require"> (*)</span></label>

                                    <div class="controls">

                                        <?php

                                            $st_email = isset( $_POST[ 'st_email' ] ) ? $_POST[ 'st_email' ] : get_post_meta( $item_id, 'st_email', true );

                                        ?>

                                        <input type="text" name="st_email" value="<?php echo esc_attr($st_email); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label" for=""><?php _e( 'Customer Phone', 'traveler' ) ?><span

                                            class="require"> (*)</span></label>

                                    <div class="controls">

                                        <?php

                                            $st_phone = isset( $_POST[ 'st_phone' ] ) ? $_POST[ 'st_phone' ] : get_post_meta( $item_id, 'st_phone', true );

                                        ?>

                                        <input type="text" name="st_phone" value="<?php echo esc_attr($st_phone); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label"

                                           for=""><?php _e( 'Customer Address line 1', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $st_address = isset( $_POST[ 'st_address' ] ) ? $_POST[ 'st_address' ] : get_post_meta( $item_id, 'st_address', true );

                                        ?>

                                        <input type="text" name="st_address" value="<?php echo esc_attr($st_address); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label"

                                           for=""><?php _e( 'Customer Address line 2', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $st_address2 = isset( $_POST[ 'st_address2' ] ) ? $_POST[ 'st_address2' ] : get_post_meta( $item_id, 'st_address2', true );

                                        ?>

                                        <input type="text" name="st_address2" value="<?php echo esc_attr($st_address2); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label"

                                           for=""><?php _e( 'Customer City', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $st_city = isset( $_POST[ 'st_city' ] ) ? $_POST[ 'st_city' ] : get_post_meta( $item_id, 'st_city', true );

                                        ?>

                                        <input type="text" name="st_city" value="<?php echo esc_attr($st_city); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label"

                                           for=""><?php _e( 'State/Province/Region', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $st_province = isset( $_POST[ 'st_province' ] ) ? $_POST[ 'st_province' ] : get_post_meta( $item_id, 'st_province', true );

                                        ?>

                                        <input type="text" name="st_province" value="<?php echo esc_attr($st_province); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label"

                                           for=""><?php _e( 'ZIP code/Postal code', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $st_zip_code = isset( $_POST[ 'st_zip_code' ] ) ? $_POST[ 'st_zip_code' ] : get_post_meta( $item_id, 'st_zip_code', true );

                                        ?>

                                        <input type="text" name="st_zip_code" value="<?php echo esc_attr($st_zip_code); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label" for=""><?php _e( 'Country', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $st_country = isset( $_POST[ 'st_country' ] ) ? $_POST[ 'st_country' ] : get_post_meta( $item_id, 'st_country', true );

                                        ?>

                                        <input type="text" name="st_country" value="<?php echo esc_attr($st_country); ?>"

                                               class="form-control form-control-admin">

                                    </div>

                                </div>



                                 <?php

                                $custommer = @ob_get_clean();

                                echo apply_filters( 'st_customer_infomation_edit_order', $custommer,$item_id );

                                ?>





                                <div class="form-row">

                                    <label class="form-label" for=""><?php _e( 'Car', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            if($st_tab == 'normal')

                                                $car_id = isset( $_POST[ 'item_id' ] ) ? $_POST[ 'item_id' ] : get_post_meta( $item_id, 'item_id', true );

                                            else

	                                            $car_id = isset( $_POST[ 'item_id' ] ) ? $_POST[ 'item_id' ] : get_post_meta( $item_id, 'car_id', true );

                                        ?>

                                        <strong><?php echo get_the_title( $car_id ); ?></strong>

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label" for=""><?php _e( 'Price', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <div id="item-price-wrapper">

                                            <?php

                                                $price  = '';

                                                $car_id = isset( $_POST[ 'item_id' ] ) ? $_POST[ 'item_id' ] : $order_item_id;

                                                if ( intval( $car_id ) > 0 && get_post_type( $car_id ) == 'st_cars' ) {

                                                    if($st_tab == 'normal'){

	                                                    $price = floatval( get_post_meta( $car_id, 'cars_price', true ) );

	                                                    $price = TravelHelper::format_money_from_db( $price, $currency ) . ' / ' . STAdminCars::get_price_unit();

                                                    }else{

	                                                    $price    = STAdminCars::get_min_max_price_transfer($car_id);

	                                                    $price = TravelHelper::format_money_from_db( (float)$price['min_price'], $currency );

                                                    }

                                                }

                                            ?>

                                            <strong><?php echo esc_attr($price); ?></strong>

                                        </div>

                                    </div>

                                </div>

                                <div class="form-row">
                                    <?php $coupon_code = get_post_meta($item_id, 'coupon_code', true); ?>
                                    <label class="form-label" for=""><?php echo _e('Coupon code: ','traveler');?><?php echo esc_html($coupon_code);?></label>

                                    <div class="controls">

                                    <?php
                                        $data_price = get_post_meta($item_id, 'data_prices', true);
                                        if(!$data_price) $data_price = array();
                                        $coupon_price   = isset($data_price['coupon_price']) ? $data_price['coupon_price'] : 0;
                                    ?>
                                    <?php if ($coupon_price) { ?>
                                    <strong> - <?php echo TravelHelper::format_money_from_db($coupon_price, $currency); ?></strong>
                                    <?php } ?>
                                    </div>

                                </div>

                                <?php
                                $pick_up = get_post_meta( $item_id, 'location_id_pick_up', true );
                                if(!empty($pick_up)){
                                ?>
                                <div class="form-row">

                                    <label class="form-label" for=""><?php _e( 'Pick Up', 'traveler' ) ?></label>

                                    <div class="controls">
                                        <strong><?php echo get_the_title( ) ?></strong>
                                    </div>

                                </div>
                                <?php }?>
                                <div class="form-row">

                                    <label class="form-label"

                                           for=""><?php _e( 'Drop Off', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $drop_off = get_post_meta( $item_id, 'location_id_drop_off', true );

                                        ?>

                                        <strong><?php echo get_the_title( $drop_off ) ?></strong>

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label"

                                           for="check_in"><?php _e( 'Check in', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $check_in = get_post_meta( $item_id, 'check_in', true );

                                            if ( !empty( $check_in ) ) {

												$check_in = date( TravelHelper::getDateFormat(), strtotime( $check_in ) );

                                            } else {

                                                $check_in = '';

                                            }

                                        ?>

                                        <strong><?php echo esc_attr($check_in); ?></strong>

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label"

                                           for="check_in_time"><?php _e( 'Check in time', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $check_in_time = get_post_meta( $item_id, 'check_in_time', true );

                                        ?>

                                        <strong><?php echo esc_attr($check_in_time); ?></strong>

                                    </div>

                                </div>

								<?php if ( get_post_meta( $car_id, 'car_type', true ) == 'normal' ) : ?>
                                <div class="form-row">

                                    <label class="form-label"

                                           for="check_out"><?php _e( 'Check out', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $check_out = get_post_meta( $item_id, 'check_out', true );

                                            if ( !empty( $check_out ) ) {

                                                $check_out = date( TravelHelper::getDateFormat(), strtotime( $check_out ) );

                                            } else {

                                                $check_out = '';

                                            }

                                        ?>

                                        <strong><?php echo esc_html($check_out); ?></strong>

                                    </div>

                                </div>

                                <div class="form-row">

                                    <label class="form-label"

                                           for="check_out_time"><?php _e( 'Check out time', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php

                                            $check_out_time = isset( $_POST[ 'check_out_time' ] ) ? $_POST[ 'check_out_time' ] : get_post_meta( $item_id, 'check_out_time', true );

                                        ?>

                                        <strong><?php echo esc_html($check_out_time); ?></strong>

                                    </div>

                                </div>
								<?php endif; ?>


                                <?php st_admin_print_order_item_guest_name([

                                    'guest_name'=>get_post_meta($item_id,'guest_name',true),

                                    'guest_title'=>get_post_meta($item_id,'guest_title',true),

                                ]); ?>

								<?php
								if(!empty($discount_rate = get_post_meta($item_id, 'discount_rate', true))){
									$discount_type = get_post_meta($order_item_id, 'discount_type', true);
									?>
									<div class="form-row">
										<label class="form-label" for=""><?php _e( 'Discount', 'traveler' ) ?></label>
										<div class="controls">
											<strong>
												<?php
												if ($discount_type == 'amount') {
													echo TravelHelper::format_money_from_db($discount_rate, $currency);
												} else {
													echo esc_html($discount_rate) . '%';
												} ?>
											</strong>
										</div>
									</div>
								<?php } ?>


                                <?php if($st_tab == 'normal'): ?>

                                <div class="form-row">

                                    <label class="form-label"

                                           for=""><?php _e( 'Equipment Price List', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <div id="item-equipment-wrapper">

                                            <?php

                                                $car_id = isset( $_POST[ 'item_id' ] ) ? $_POST[ 'item_id' ] : $order_item_id;

                                                if ( intval( $car_id ) > 0 && get_post_type( $car_id ) == 'st_cars' ) {

													$extras = get_post_meta( $item_id, 'data_equipment', true );
													$days_extra = get_post_meta( $item_id, 'numberday', true );

													$unit_extra = st()->get_option('cars_price_unit', 'day');
													$text_unit_extra = '';
													switch ($unit_extra) {
														case 'distance':
														case 'hour':
															$text_unit_extra = __('Hour(s)', 'traveler');
															break;
														default:
															$text_unit_extra = __('Day(s)', 'traveler');
															break;
													}
													foreach ($extras['value'] as $name => $number):
														$number_item = intval($extras['value'][$name]);
														if ($number_item <= 0) $number_item = 0;
														if ($number_item > 0):
															$price_item = floatval($extras['price'][$name]);
															if ($price_item <= 0) $price_item = 0;
															$price_type = $extras['price'][$name];
															?>
															<span class="pull-left">
															<?php
															if ($price_type == 'fixed') {
																echo esc_html($extras['title'][$name]) . ' (' . TravelHelper::format_money_from_db( $price_item, $currency ) . ') x ' . esc_html($number_item) . ' ' . __('Item(s)', 'traveler');
															} else {
																echo esc_html($extras['title'][$name]) . ' (' . TravelHelper::format_money_from_db( $price_item, $currency ) . ') x ' . esc_html($number_item) . ' ' . __('Item(s)', 'traveler') . ' x ' . esc_html($days_extra) . ' ' . esc_html($text_unit_extra);
															}

															?>
															</span> <br/>
														<?php endif;
													endforeach;

                                                }

                                            ?>

                                        </div>

                                    </div>

                                </div>

                                <?php endif; ?>

                                <?php if ( st()->get_option( 'tax_enable', 'off' ) == 'on' && st()->get_option( 'st_tax_include_enable', 'off' ) == 'off' ) { ?>

                                    <div class="form-row">

                                        <label class="form-label" for=""><?php _e( 'Tax', 'traveler' ) ?></label>

                                        <div class="controls">

                                            <?php

                                                $tax = floatval( st()->get_option( 'tax_value', 0 ) );

                                            ?>

                                            <strong><?php echo esc_attr( $tax ) . '(%)'; ?></strong>

                                        </div>

                                    </div>

                                <?php } ?>




								<?php
								$item = get_post_meta($item_id, 'st_cart_info', true);
								if ( get_post_meta( $car_id, 'car_type', true ) == 'normal' ) {
									$item = $item[$order_item_id];
								} else {
									$item = $item['car_transfer'];
								}
								$price_with_tax = (float)$item['data']['price_with_tax'];
								$price_with_tax -= $coupon_price;
								$total_price = 0;

								$deposit_status = get_post_meta($item_id, 'deposit_money', true);
								if((is_array($deposit_status) && !empty($deposit_status['type']) && floatval($deposit_status['amount']) > 0)){
									$deposit_price = isset($data_price['deposit_price']) ? $data_price['deposit_price'] : 0;
									$total_price = $deposit_price;
									?>
									<div class="form-row">
										<label class="form-label" for=""><?php _e( 'Total', 'traveler' ) ?></label>
										<div class="controls">
											<strong><?php echo TravelHelper::format_money_from_db( $price_with_tax, $currency ); ?></strong>
										</div>
									</div>
									<div class="form-row">
										<label class="form-label" for=""><?php _e( 'Deposit', 'traveler' ) ?></label>
										<div class="controls">
											<strong><?php echo TravelHelper::format_money_from_db( $deposit_price, $currency ); ?></strong>
										</div>
									</div>
									<?php
									if ( ! empty( $booking_fee_price = get_post_meta( $item_id, 'booking_fee_price', true ) ) ) {
										$total_price = $total_price + $booking_fee_price;
										?>
										<div class="form-row">
											<label class="form-label" for=""><?php _e( 'Fee', 'traveler' ) ?></label>
											<div class="controls">
												<strong><?php echo TravelHelper::format_money_from_db( $booking_fee_price, $currency ); ?></strong>
											</div>
										</div>
									<?php } ?>
									<div class="form-row">
										<label class="form-label" for=""><?php _e( 'Pay Amount', 'traveler' ) ?></label>
										<div class="controls">
											<strong><?php echo TravelHelper::format_money_from_db( $total_price, $currency ); ?></strong>
										</div>
									</div>
								<?php } else { ?>
									<?php
									if ( ! empty( $booking_fee_price = get_post_meta( $item_id, 'booking_fee_price', true ) ) ) {
										$price_with_tax = $price_with_tax + $booking_fee_price;
										?>
										<div class="form-row">
											<label class="form-label" for=""><?php _e( 'Fee', 'traveler' ) ?></label>
											<div class="controls">
												<strong><?php echo TravelHelper::format_money_from_db( $booking_fee_price, $currency ); ?></strong>
											</div>
										</div>
									<?php } ?>
									<div class="form-row">
										<label class="form-label" for=""><?php _e( 'Pay Amount', 'traveler' ) ?></label>
										<div class="controls">
											<strong><?php echo TravelHelper::format_money_from_db( $price_with_tax, $currency ); ?></strong>
										</div>
									</div>
								<?php } ?>


                                <?php

                                    $st_note = get_post_meta( $item_id, 'st_note', true );

                                    if(!empty($st_note)){

                                ?>

                                <div class="form-row">

                                    <label class="form-label"

                                           for="st_note"><?php _e( 'Special Requirements', 'traveler' ) ?></label>

                                    <div class="controls">

                                        <?php echo esc_html( $st_note ); ?>

                                    </div>

                                </div>

                                <?php } ?>
                                <div class="form-row">

                                    <label class="form-label" for=""><?php _e('Transaction ID Stripe','traveler')?></label>

                                    <div class="controls">

                                        <?php

                                        $transaction_id = ( get_post_meta( $item_id, 'transaction_id', true ) );

                                        ?>

                                        <strong><?php echo esc_html($transaction_id); ?></strong>

                                    </div>

                                </div>
                                <div class="form-row">

                                    <label class="form-label" for="status"><?php _e('Status','traveler')?></label>

                                    <div class="controls">

                                        <select data-block="" class="" name="status">

                                            <?php $status=get_post_meta($item_id,'status',true); ?>

                                            <option value="pending" <?php selected($status,'pending') ?> ><?php _e('Pending','traveler')?></option>

                                            <option value="incomplete" <?php selected($status,'incomplete') ?> ><?php _e('Incomplete','traveler')?></option>

                                            <option value="complete" <?php selected($status,'complete') ?> ><?php _e('Complete','traveler')?></option>

                                            <option value="canceled" <?php selected($status,'canceled') ?> ><?php _e('Canceled','traveler')?></option>

											<option value="cancelling" <?php selected($status,'cancelling') ?> ><?php _e('Cancelling','traveler')?></option>

                                        </select>

                                    </div>

                                </div>

                                <div class="form-row">

                                    <div class="controls">

                                        <input type="submit" name="submit"

                                               value="<?php echo __( 'Save', 'traveler' ) ?>"

                                               class="button button-primary ">

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>