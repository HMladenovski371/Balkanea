<!--Tour Banner-->
<div class="info__bg">
    <?php
    $url = get_the_post_thumbnail_url($post_id, 'full');
    if (has_post_thumbnail()) {

        the_post_thumbnail([649, 396], [
            'alt'   => TravelHelper::get_alt_image(),
            'class' => 'img-responsive',
        ]);

    ?>
    <?php
    } else {
        echo '<img src="' . get_template_directory_uri() . '/img/no-image.png' . '" alt="Default Thumbnail" class="img-responsive" />';
    }
    ?>
</div>

<div class="info__btn--group">
    <div class="group__left">
        <a href="#st-gallery-popup" class="group__gallery--popup  has-icon  st-gallery-popup"><?php echo TravelHelper::getNewIcon('icon-gallery-solo', '#FFFFFF', '40px', '35px') ?></a>
        <?php if (!empty($gallery)) { ?>
            <a href="#st-gallery-popup" class="has-icon  st-gallery-popup group__link--gallery"><?php echo __('More Photos', 'traveler') ?></a>
        <?php } ?>
        <div id="st-gallery-popup" class="hidden">
            <?php
            if (!empty($gallery_array)) {
                foreach ($gallery_array as $k => $v) {
                    echo '<a href="' . wp_get_attachment_image_url($v, 'full') . '">' . __('Image', 'traveler') . '</a>';
                }
            }
            ?>
        </div>
    </div>
    <div class="group__right">
        <?php
        $video_url = get_post_meta(get_the_ID(), 'video', true);
        if (!empty($video_url)) {
        ?>
            <a href="<?php echo esc_url($video_url); ?>" class="group__video--popup   has-icon  st-video-popup"><?php echo TravelHelper::getNewIcon('icon-video-solo', '#FFFFFF', '40px', '35px') ?></a>
            <a href="<?php echo esc_url($video_url); ?>" class="has-icon  st-video-popup group__link--video"><?php echo __('Tour Video', 'traveler') ?></a>
        <?php } ?>
    </div>


</div>