<?php
$list_demo = ST_QuickView_Demo::inst()->getListDemo();
$list_elementor_demo = ST_QuickView_Demo::inst()->getListElementorDemo();
?>
<div class="st-quickview-backdrop"></div>
<div class="st-quickview-demo">
    <div class="toolbar-icon">
        <?php echo '<span>20+</span> Demos'; ?>
    </div>
    <div class="toolbar-content">
        <div class="close">
           <img src="<?php echo get_template_directory_uri() . '/quickview_demo/img/icon/ico_expan.svg'; ?>" />
        </div>
        <div class="toolbar-header">
            <p><a href="<?php echo home_url(''); ?>"><img src="<?php echo get_template_directory_uri() . '/quickview_demo/img/icon/traveler_logo.svg' ?>" alt="<?php echo __('Traveler Logo', 'traveler'); ?>"/></a></p>
            <h1><?php echo __('Get All 20+ Pre-Made Website At Only 79$', 'traveler'); ?></h1>
        </div>

        <div class="toolbar-body">
            <div class="qv-demo-tab">
            <a href="#" class="active" data-tab="elementor">Elementor Layout</a>
                <a href="#"  data-tab="modern">WP Bakery Page Builder Layout</a>
               
            </div>
            <div class="qv-demo-tab-wrapper">
            <?php
                if(!empty($list_demo)){
                    echo '<div class="modern-layout item-tab" data-tab="modern"><div class="row">';
                    foreach ($list_demo as $k => $v){
                        include( locate_template( 'quickview_demo/item.php', false, false ) );
                    }
                    echo '</div></div>';
                }
                if(!empty($list_elementor_demo)){
                    echo '<div class="old-layout item-tab active" data-tab="elementor"><div class="row">';
                    foreach ($list_elementor_demo as $k => $v){
                        include( locate_template( 'quickview_demo/item_elementor.php', false, false ) );
                    }
                    echo '</div></div>';
                }
            ?>
            </div>
        </div>
    </div>
    <div class="toolbar-footer">
        <a href="https://1.envato.market/e0D5j" class="btn btn-success btn-lg"><?php echo __('Buy Traveler Now', 'traveler'); ?></a>
    </div>
</div>