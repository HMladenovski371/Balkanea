   <!--Tour highlight-->
   <?php
    $tours_highlight = get_post_meta(get_the_ID(), 'tours_highlight', true);
    if (!empty($tours_highlight)) {
        $arr_highlight = explode("\n", trim($tours_highlight));
        ?>
        <div class="st-highlight">
            <h2 class="st-heading-section"><?php echo __('Tour Highlights', 'traveler'); ?></h2>
            <ul class="row">
                <?php
                if (!empty($arr_highlight)) {
                    foreach ($arr_highlight as $k => $v) {
                        echo '<li class="col-12 col-sm-6">' . TravelHelper::getNewIcon( 'check-1', '#36bca1', '15px', '15px' ) . esc_html( $v ) . '</li>';
                    }
                }
                ?>
            </ul>
        </div>
    <?php } ?>
    <!--End Tour highlight-->