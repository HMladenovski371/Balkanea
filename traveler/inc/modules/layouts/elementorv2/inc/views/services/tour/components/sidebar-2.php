<?php
$filters = get_post_meta(get_the_ID(), 'rs_filter_tour', true);

if (!isset($format))
    $format = '';
?>
<div class="col-lg-4 col-md-4 sidebar-filter">
    <div class="sidebar-item-wrapper">
        <h3 class="sidebar-title"><?php echo __('FILTER BY', 'traveler'); ?></h3>
        <div class="close-sidebar"><span class="stt-icon stt-icon-close"></span></div>
        <?php
        if (!empty($filters)) {
            $array_tem = array();
            foreach ($filters as $k => $v) {
                if (isset($v['rs_filter_type_taxonomy'])) {
                    $array_tem['taxonomy'] = $v['rs_filter_type_taxonomy'];
                }
                if (isset($v['title'])) {
                    $array_tem['title'] = $v['title'];
                }
                echo stt_elementorv2()->loadView('services/tour/components/sidebar-2/' . esc_attr($v['rs_filter_type']), ['title' => $v['title'], 'taxonomy' => $v['rs_filter_type_taxonomy']]);
            }
        }
        ?>
    </div>
</div>
