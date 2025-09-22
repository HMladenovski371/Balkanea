<?php
$search_tax_advance = st()->get_option( 'attribute_search_form_tour', 'st_tour_type' );
$terms_posts        = wp_get_post_terms( get_the_ID(), $search_tax_advance );
$arr_id_term_post   = [];

if ( ! isset( $terms_posts->errors ) ) {
    foreach ( $terms_posts as $term_post ) {
        $arr_id_term_post[] = $term_post->term_id;
    }
    $args = [
        'posts_per_page' => 4,
        'post_type'      => 'st_tours',
        'post_author'    => get_post_field( 'post_author', get_the_ID() ),
        'post__not_in'   => [ $post_id ],
        'orderby'        => 'rand',
        'tax_query'      => [
            'taxonomy' => $search_tax_advance,
            'terms'    => $arr_id_term_post,
            'field'    => 'term_id',
            'operator' => 'IN',
        ],
    ];
} else {
    $args = [
        'posts_per_page' => 4,
        'post_type'      => 'st_tours',
        'post_author'    => get_post_field( 'post_author', get_the_ID() ),
        'post__not_in'   => [ $post_id ],
        'orderby'        => 'rand',
    ];
}
global $post;
$old_post = $post;
$query    = new WP_Query( $args );
if($query->have_posts()) {
    wp_enqueue_script('owlcarousel');
    wp_enqueue_style('owlcarousel');
    $responsive = [
        '992' => [
            'items' => 3,
        ],
        '768' => [
            'items' => 2
        ],
        '0' => [
            'items' => 1
        ]
    ];
?>
<div class="st-relate st-hotel-room-content">
    <div class="relate-rooms">
        <h2 class="st-heading-section"><?php echo esc_html__('You might also like', 'traveler'); ?></h2>
        <div class="inner service-list-wrapper rental-grid service-tour">
            <?php
            echo '<div class="owl-carousel st-owl-slider" data-items="3" data-margin="30" data-responsive="'. esc_attr(json_encode($responsive)) .'">';
                while ($query->have_posts()) {
                    $query->the_post();
                    echo '<div class="item-slide">';
                        echo stt_elementorv2()->loadView('services/tour/loop/grid-2');
                    echo '</div>';
                }
                wp_reset_postdata();
            echo '</div>';
            ?>
        </div>
    </div>
</div>

<?php
wp_reset_postdata();
$post = $old_post;
}