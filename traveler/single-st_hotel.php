 <?php
    /**
     * @package    WordPress
     * @subpackage Traveler
     * @since      1.0
     *
     * Single hotel
     *
     * Created by ShineTheme
     *
     */

 use balkanea\includes\BalkaneaTemplateLoader;
 use balkanea\includes\Hotel;

 /**/
 get_header();
 ?>
 <?php
 if (have_posts()) :
 while (have_posts()) : the_post(); ?>
     <?php
     global $wpdb;
     $post_id = get_the_ID();
     $stHotel = Hotel::getHotelByPostId($post_id);

     $hotel = [
         'stHotel' => $stHotel,
     ]
     ?>
 <div class="st-style-4 st-style-elementor singe-hotel-layout-5" id="st-content-wrapper" data-hid="<?php echo $stHotel->external_hid?>" data-id="<?php echo get_post_field('post_name', get_the_ID());?>">
     breadcrambs
     <div class="container st-single-service-content">
         <?php BalkaneaTemplateLoader::get_instance()->load_template_part('header', 'title', $hotel); ?>
         <?php BalkaneaTemplateLoader::get_instance()->load_template_part('content', 'gallery' ); ?>
     </div>
     <div class="container st-single-service-content">
         <div class="row">
             <div class="col-12 col-sm-12 col-md-12 col-lg-8 col-xxl-8">
                 <?php BalkaneaTemplateLoader::get_instance()->load_template_part('content', 'description' ); ?>
                 <?php BalkaneaTemplateLoader::get_instance()->load_template_part('content', 'facilities' ); ?>
                 <div class="st-hr"></div>
                 <?php BalkaneaTemplateLoader::get_instance()->load_template_part('content', 'rules', $hotel ); ?>
                 <div class="st-hr"></div>
                 <?php BalkaneaTemplateLoader::get_instance()->load_template_part('content', 'rooms' ); ?>
                 <div class="st-hr"></div>
                 <?php BalkaneaTemplateLoader::get_instance()->load_template_part('content', 'other-info', $hotel ); ?>
                 <?php BalkaneaTemplateLoader::get_instance()->load_template_part('content', 'reviews' ); ?>
             </div>
             <div class="ccol-12 col-sm-12 col-md-12 col-lg-4 col-xxl-4">
                 <?php BalkaneaTemplateLoader::get_instance()->load_template_part('widget', 'ask-question' ); ?>
             </div>
         </div>
     </div>
 </div>
 <?php endwhile;
 endif;
 ?>
<?php get_footer() ?>
