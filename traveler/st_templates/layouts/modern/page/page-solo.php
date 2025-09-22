<div id="st-content-wrapper" class="st-style-elementor search-result-page">
    <?php echo stt_elementorv2()->loadView('services/tour/components/banner-2'); ?>
    
    <div class="container">
        <div class="row">
            <div class="col-12">
                <?php 
                    if(have_posts()) : while (have_posts()) : the_post();
                        the_content();
                    endwhile;endif;
                ?>
            </div>
        </div>
    </div>
</div>