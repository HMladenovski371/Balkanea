<?php
$inner_style = '';
$thumb_id = get_post_thumbnail_id(get_the_ID());
$img = wp_get_attachment_image_url($thumb_id, 'full');
$inner_style = Assets::build_css("background-image: url(" . esc_url($img) . ") !important;");
$content = (is_home()) ? get_post_meta(get_option('page_for_posts', true), 'page_header_text', true) : get_post_meta(get_the_ID(), 'page_header_text', true);

?>
<div class="banner st-banner-solo <?php echo esc_attr($inner_style) ?>">
    <div class="container">
        <div class="banner-headding">
            <?php 
                if(!is_page()){?>
                    <h2 class="banner-content-solo">
                        <?php the_archive_title('', ''); ?>
                    </h2>
                <?php } else { ?>
                    <h1 class="tag_h1"><?php echo get_the_title(); ?></h1>
                    <?php if (!empty($content)) { ?>
                        <h2 class="banner-content-solo">
                            <?php echo esc_html($content); ?>
                        </h2>
                    <?php } ?>
                <?php }
            ?>
            
        </div>
        

    </div>
    <?php st_breadcrumbs_new() ?>
</div>

