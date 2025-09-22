<div class="tab-pane fade show active" id="nav-book" role="tabpanel"
        aria-labelledby="nav-book-tab">
    <?php echo st()->load_template('layouts/elementor/common/loader'); ?>
    <?php
        $hotel_external      = get_post_meta( get_the_ID(), 'st_hotel_external_booking', true );
        $hotel_external_link = get_post_meta( get_the_ID(), 'st_hotel_external_booking_link', true );
        if(empty($hotel_external) || $hotel_external == 'off'){ ?>
        <form class="form form-check-availability-hotel" method="post">
                <input type="hidden" name="action" value="ajax_search_room">
                <input type="hidden" name="room_search" value="1">
                <input type="hidden" name="is_search_room" value="1">
				<input name="item_id" value="<?php echo esc_attr(get_the_ID()); ?>" type="hidden">
                <input type="hidden" name="room_parent"
                        value="<?php echo esc_attr(get_the_ID()); ?>">
                <?php echo st()->load_template('layouts/elementor/hotel/elements/search/date-inquiry', ''); ?>
                <?php echo st()->load_template('layouts/elementor/hotel/elements/search/guest', ''); ?>
                <div class="submit-group">
                <input class="btn btn-large btn-full upper"
                        type="submit"
                        name="submit"
                        value="<?php echo esc_html__('Check Availability', 'traveler') ?>">
                <input style="display:none;" type="submit"
                        class="btn btn-default btn-send-message"
                        data-id="<?php echo get_the_ID(); ?>" name="st_send_message"
                        value="<?php echo __('Send message', 'traveler'); ?>">
                </div>
                <div class="message-wrapper mt30"></div>
        </form>
        <?php } else {?>
            <div class="submit-group button-external-link pb-3">
                <a href="<?php echo esc_url($hotel_external_link); ?>" class="btn btn-large btn-full upper"><?php echo esc_html__( 'Explore', 'traveler' ); ?></a>
            </div>
        <?php }?>
</div>