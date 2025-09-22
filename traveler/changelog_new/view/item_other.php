<?php
/**
 * Created by PhpStorm.
 * User: HanhDo
 * Date: 5/8/2019
 * Time: 11:55 AM
 */
$type = '';
if (isset($v['type']))
    $type = 'more';
?>
<div class="col-lg-4 col-md-6 col-sm-6">
    <?php if ($type == 'more') { ?>
        <div class="item more">
            <img src="<?php echo esc_url($url) . '/img/other/' . esc_html($v['icon']); ?>"
                 alt="<?php echo esc_attr($v['heading']) ?>"/>
            <h5><?php echo esc_attr($v['heading']); ?></h5>
        </div>
    <?php } else { ?>
        <div class="item">
            <img src="<?php echo esc_url($url) . '/img/other/' . esc_html($v['icon']); ?>"
                 alt="<?php echo esc_attr($v['heading']) ?>"/>
            <h5><?php echo esc_attr($v['heading']); ?></h5>
            <p class="desc"><?php echo esc_html($v['desc']); ?></p>
        </div>
    <?php } ?>
</div>
