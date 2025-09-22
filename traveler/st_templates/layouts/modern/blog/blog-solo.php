<?php
get_header();
$postsPerPage = get_option('posts_per_page', 9);
?>
<div id="st-content-wrapper" class="search-result-page st-blog-solo--wrapper">
    <div class="st-blog--banner">
        <?php 
            echo stt_elementorv2()->loadView('services/tour/components/banner-2');
        ?>
    </div>
    <div class="container">
        <div class="st-blog st-blog--search">
            <div class="row">
                <div class="col-sm-12 col-xs-12">
                    <div class="content">
                        <?php
                        global $wp_query;
                        if (have_posts()):
                            echo '<div class="blog-wrapper row">';
                            while (have_posts()) {
                                the_post();
                                echo st()->load_template('layouts/modern/blog/content', 2);
                            }
                            echo '</div>';
                        else:
                            echo st()->load_template('layouts/modern/blog/content', 'none');
                        endif;
                        wp_reset_postdata();
                        $paged = get_query_var( 'paged', 1 );
                        $paged = $paged + 1;
                        ?>
                        <input type="hidden" value="<?php echo esc_html($paged+1); ?>" id="solo_load_more_blog"/>
                    </div>
                </div>
            </div>
            <div class="pageinate d-flex justify-content-center">
                <?php 
                    global $wp_query;
                    
                    echo paginate_links( array(
                        'base'         => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
                        'total'        => $wp_query->max_num_pages,
                        'current'      => max( 1, get_query_var( 'paged' ) ),
                        'format'       => '?paged=%#%',
                        'show_all'     => false,
                        'type'         => 'plain',
                        'end_size'     => 0,
                        'mid_size'     => 0,
                        'prev_next'    => true,
                        'next_text'    => '<div class="solo-load-more-blog"><button>'.esc_html__('Next', 'traveler').'</button></div>',
                        'prev_text'    => '<div class="solo-load-more-blog"><button>'.esc_html__('Prev', 'traveler').'</button></div>',
                        'add_args'     => true,
                        'add_fragment' => '',
                    ) );
                ?>
            </div>
        </div>
    </div>
</div>
<?php
get_footer();