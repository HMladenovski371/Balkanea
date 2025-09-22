<?php
/**
 * Created by PhpStorm.
 * User: HanhDo
 * Date: 5/7/2019
 * Time: 3:03 PM
 */
?>
<div class="col-lg-6 col-md-6">
    <div class="item">
        <img src="<?php echo esc_url($url) . '/img/plugin/' . $v['thumb']; ?>" alt="Traveler Plugin"/>
        <div class="plugin-info">
            <h5><?php echo esc_attr($v['heading']); ?></h5>
            <div class="desc"><?php echo esc_html($v['desc']); ?></div>
        </div>
    </div>
</div>
