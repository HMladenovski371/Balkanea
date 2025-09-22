<?php
/*
  Template Name: Fullwidth
 */
if(New_Layout_Helper::isCheckWooPage()){
    echo st()->load_template('layouts/modern/page/page');
    return;
}
if(New_Layout_Helper::isNewLayout()){
    get_header();
    $style = st()->get_option('blog_single_style_modern', 1);
    ?>
    <div id="st-content-wrapper" class="<?php if(check_using_elementor()){echo'st-style-elementor';} ?> st-page-default">
        <?php if($style == 2){
            echo stt_elementorv2()->loadView('services/tour/components/banner-2');
        } else { ?>
            <?php echo st()->load_template('layouts/modern/hotel/elements/banner'); ?>
            <?php st_breadcrumbs_new() ?>
        <?php }?>
        <div class="st-blog">
            <?php
            if ( have_posts() ) {
                the_post();
                the_content();
            }
            wp_reset_postdata();
            ?>
        </div>
    </div>
    <?php
    get_footer();
}