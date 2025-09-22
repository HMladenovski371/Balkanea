<?php
wp_enqueue_script('filter-tour');
while (have_posts()) : the_post();
    $post_id = get_the_ID();
    $address = get_post_meta($post_id, 'address', true);
    $review_rate = STReview::get_avg_rate();
    $count_review = STReview::count_review($post_id);
    $gallery            = get_post_meta($post_id, 'gallery', true);
    $gallery_array      = explode(',', $gallery);
    $tour_external = get_post_meta(get_the_ID(), 'st_tour_external_booking', true);
    $tour_external_link = get_post_meta(get_the_ID(), 'st_tour_external_booking_link', true);
    $booking_type = st_get_booking_option_type();
    $tour_type = get_post_meta(get_the_ID(), 'type_tour', true); ?>
    <div id="st-content-wrapper" class="st-style-4 st-style-elementor st-single-tour st-single-tour-<?php echo esc_attr($style_single); ?>">
        <div class="container st-single-service-content">
            <?php echo stt_elementorv2()->loadView('services/tour/single/item/style-2/top-infor', array(
                'address' => $address,
                'review_rate' => $review_rate,
                'count_review' => $count_review,
            )) ?>
        </div>
        <?php
        echo stt_elementorv2()->loadView('components/banner');
        ?>
        <div class="st-content--info">
            <div class="container st-single-service-content">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-8">
                    <?php echo stt_elementorv2()->loadView('services/common/single/gallery-3', array('gallery_array' => $gallery_array, 'gallery' => $gallery , 'post_id' => $post_id)) ?>
                        <div class="st-hotel-content">
                            <div class="hotel-target-book-mobile d-flex justify-content-between align-items-center">
                                <div class="price-wrapper">
                                    <div id="mobile-price">
                                        <?php echo wp_kses(sprintf(__('From:<span class="price">%s</span>', 'traveler'), STTour::get_price_html(get_the_ID())), ['span' => ['class' => []]]) ?>
                                    </div>

                                    <div class="st-review-booking-form">
                                        <div class="st-review-box-top d-flex align-items-center">
                                            <i class="stt-icon-star1"></i>
                                            <?php
                                            $count_review = get_comment_count($post_id)['approved'];
                                            $avg = STReview::get_avg_rate();
                                            ?>
                                            <div class="review-score">
                                                <?php echo esc_attr($avg); ?>
                                            </div>
                                            <div class="review-score-base text-center">
                                                <span>(<?php comments_number(__('0 review', 'traveler'), __('1 review', 'traveler'), __('% reviews', 'traveler')); ?>)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                if ($tour_external == 'off' || empty($tour_external)) {
                                ?>
                                    <a href="" class="btn-v2 btn-primary btn-mpopup btn-green">
                                        <?php
                                        if ($booking_type == 'enquire') {
                                            echo esc_html__('Inquiry', 'traveler');
                                        } else {
                                            echo esc_html__('Check', 'traveler');
                                        }
                                        ?>
                                    </a>
                                <?php
                                } else {
                                ?>
                                    <a href="" class="btn-v2 btn btn-mpopup"><?php echo esc_html__('Check', 'traveler') ?></a>
                                <?php
                                }
                                ?>
                            </div>
                        </div>
                        <div class="st-description" id="st-description">
                            <?php
                            if (isset($title)) {
                                echo '<h2 class="st-heading-section">' . esc_html($title) . '</h2>';
                            } else { ?>
                                <h2 class="st-heading-section">
                                    <?php

                                    echo __('Overview', 'traveler');


                                    ?>
                                </h2>
                            <?php }
                            the_content();
                            ?>
                        </div>
                        <?php echo stt_elementorv2()->loadView('services/tour/single/item/infor', array('tour_type' => $tour_type)); ?>
                        <?php echo stt_elementorv2()->loadView('services/tour/single/item/discount'); ?>
                        <?php echo stt_elementorv2()->loadView('services/tour/single/item/style-2/include-exclude') ?>
                        <div class="st-program--wrapper st-program-parent">
                            <?php echo stt_elementorv2()->loadView('services/tour/single/item/style-2/itenirary', ['post_id' => $post_id]) ?>
                        </div>
                        <div class="st-content--hightlight">
                            <?php echo stt_elementorv2()->loadView('services/tour/single/item/style-2/highlights') ?>
                        </div>
                        <?php echo stt_elementorv2()->loadView('services/tour/single/item/style-2/faq', ['post_id' => $post_id]) ?>
                        <div id="st-attributes">
                            <?php echo stt_elementorv2()->loadView('services/tour/single/item/style-2/attributes', ['post_type' => 'st_tours']) ?>
                        </div>
                        <div class="stoped-scroll-section"></div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-4">
                        <div class="widgets sticky-top">
                            <div class="fixed-on-mobile st-fixed-form-booking" data-screen="992px">
                                <div class="close-icon hide">
                                    <?php echo TravelHelper::getNewIcon('Ico_close'); ?>
                                </div>
                                <?php
                                $info_price = STTour::get_info_price();
                                echo stt_elementorv2()->loadView('services/tour/single/item/form-book', [
                                    'info_price' => $info_price,
                                    'tour_external' => $tour_external,
                                    'tour_external_link' => $tour_external_link,
                                    'tour_type' => $tour_type,
                                    'booking_type' => $booking_type,
                                    'review_rate' => $review_rate,
                                ]);
                                ?>
                                <?php
                                $list_badges = get_post_meta(get_the_ID(), 'list_badges', true);
                                if (!empty($list_badges)) {
                                    echo st()->load_template('layouts/modern/common/single/badge', '', array('list_badges' => $list_badges));
                                }
                                ?>
                                <?php echo st()->load_template('layouts/elementor/hotel/single/item/owner-info', '', array('size_avatar_custom' => 90)); ?>

                                <?php echo st()->load_template('layouts/modern/common/single/information-contact'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="related">
            <div class="container">
                <?php echo stt_elementorv2()->loadView('services/tour/single/item/relate-1', ['post_id' => $post_id]) ?>
            </div>
        </div>
    </div>

<?php endwhile;