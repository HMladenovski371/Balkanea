<?php
$post_id = get_the_ID();
$post_translated = TravelHelper::post_translated($post_id);
$thumbnail_id = get_post_thumbnail_id($post_translated);
$duration = get_post_meta(get_the_ID(), 'duration_day', true);
$info_price = STTour::get_info_price();
$address = get_post_meta($post_translated, 'address', true);
$review_rate = STReview::get_avg_rate();
$price = STTour::get_info_price();
$count_review = get_comment_count($post_translated)['approved'];
$class_image = 'image-feature st-hover-grow';
$url = st_get_link_with_search(get_permalink($post_translated), array('start', 'date', 'adult_number', 'child_number'), $_GET);
$main_color = st()->get_option( 'main_color', '#ec927e' );
?>
<div class="services-item item-elementor grid-2" itemscope itemtype="https://schema.org/Trip">
    <div class="item service-border st-border-radius">
        <div class="featured-image">


            <a href="<?php echo esc_url($url); ?>">
                <img itemprop="image" src="<?php echo wp_get_attachment_image_url($thumbnail_id, array(450, 300)); ?>" alt="<?php echo TravelHelper::get_alt_image(); ?>" class="<?php echo esc_attr($class_image); ?>" />
            </a>
            <?php
            $list_country = get_post_meta(get_the_ID(), 'multi_location', true);
            $list_country = preg_replace("/(\_)/", "", $list_country);
            $list_country = explode(",", $list_country);
            $solo_location_name = '';
            if (!empty($list_country)) {
                if (!empty($location_name && !empty($location_id))) {
                    if (in_array($location_id, $list_country)) {
                        $solo_location_name = $location_name;
                        $color_location = get_post_meta($location_id, 'color', true);
            ?>
                        <span class="ml5 f14 address st-location--style4" style="background:<?php echo esc_attr($color_location) ?>"><?php echo esc_html(get_the_title($location_id)); ?></span>
                    <?php

                    } else {
                        $color_location = get_post_meta($list_country[0], 'color', true);
                    ?>
                        <span class="ml5 f14 address st-location--style4" style="background:<?php echo esc_attr($color_location) ?>"><?php echo esc_html(get_the_title($list_country[0])); ?></span>
                    <?php
                    }
                } else {
                    $color_location = get_post_meta($list_country[0], 'color', true);
                    ?>
                    <span class="ml5 f14 address st-location--style4" style="background:<?php echo esc_attr($color_location) ?>"><?php echo esc_html(get_the_title($list_country[0])); ?></span>
            <?php
                }
            }
            ?>
            <?php echo st_get_avatar_in_list_service(get_the_ID(), 70) ?>
        </div>
        <div class="content-item">

            <h3 class="title" itemprop="name">
                <a href="<?php echo esc_url($url); ?>" class="c-main"><?php echo get_the_title($post_translated) ?></a>
            </h3>
            <?php
            $description_tour = get_post(get_the_ID());
            if (!empty($description_tour)) {
            ?>
                <div class="st-tour--description"><?php the_excerpt() ?></div>
            <?php
            }
            ?>
             <div class="fixed-bottoms">
                <div class="st-tour--feature st-tour--tablet">
                    <div class="st-tour__item">
                        <div class="item__icon">
                            <?php echo TravelHelper::getNewIcon('icon-calendar-tour-solo', $main_color , '24px', '24px'); ?>
                        </div>
                        <div class="item__info">
                            <h4 class="info__name"><?php echo esc_html__('Duration', 'traveler'); ?></h4>
                            <p class="info__value">
                                <?php
                                $duration = get_post_meta(get_the_ID(), 'duration_day', true);
                                echo esc_html($duration);
                                ?>
                            </p>
                        </div>
                    </div>
                    <div class="st-tour__item">
                        <div class="item__icon">
                            <?php echo TravelHelper::getNewIcon('icon-service-tour-solo', $main_color , '24px', '24px'); ?>
                        </div>
                        <div class="item__info">
                            <h4 class="info__name"><?php echo esc_html__('Group Size', 'traveler'); ?></h4>
                            <p class="info__value">
                                <?php
                                $max_people = get_post_meta(get_the_ID(), 'max_people', true);
                                if (empty($max_people) or $max_people == 0 or $max_people < 0) {
                                    echo esc_html__('Unlimited', 'traveler');
                                } else {
                                    if ($max_people == 1)
                                        echo sprintf(esc_html__('%s person', 'traveler'), $max_people);
                                    else
                                        echo sprintf(esc_html__('%s people', 'traveler'), $max_people);
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="section-footer">
                <div class="st-flex space-between st-price__wrapper">

                    <div class="right">

                        <span class=" price--tour">
                            <?php echo sprintf(esc_html__('%s', 'traveler'), STTour::get_price_html(get_the_ID())); ?>
                        </span>
                    </div>
                    <div class="st-btn--book">
                        <a href="<?php echo esc_attr(get_permalink(get_the_ID())); ?>"><?php echo esc_html__('BOOK NOW', 'traveler'); ?></a>
                    </div>
                </div>
            </div>
           

        </div>
    </div>
</div>