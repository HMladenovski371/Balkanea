<?php
/**
 * Created by PhpStorm.
 * User: HanhDo
 * Date: 5/6/2019
 * Time: 11:31 AM
 */
$status = 'normal';
if(isset($v['status']))
    $status = $v['status'];
?>
<div class="col-sm-6">
    <div class="item has-matchHeight <?php echo esc_html($status); ?>">
        <a href="<?php echo esc_url($v['url']); ?>" title="<?php echo esc_attr($v['heading']); ?>" target="_blank">
            <!--<span class="view-more">View More</span>-->
            <div class="new">New</div>
            <!--<div class="soon">Soon</div>-->
            <div class="soon">Hot</div>
            <img src="<?php echo esc_url(get_template_directory_uri() . '/quickview_demo/img/' . esc_html($v['thumb'])); ?>" alt="<?php echo esc_attr($v['heading']); ?>" class="img-responsive">
        </a>
        <span class="heading"><svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 26 9" style="enable-background:new 0 0 26 9;" xml:space="preserve">
                    <path d="M26,4.7C26,4.6,26,4.4,26,4.3c0-0.1-0.1-0.1-0.1-0.2l-3.5-3.5c-0.2-0.2-0.5-0.2-0.7,0s-0.2,0.5,0,0.7L24.3,4
                    H0.5C0.2,4,0,4.2,0,4.5S0.2,5,0.5,5h23.8l-2.7,2.7c-0.2,0.2-0.2,0.5,0,0.7c0.1,0.1,0.2,0.1,0.4,0.1s0.3,0,0.4-0.1l3.5-3.5
                    C25.9,4.8,25.9,4.8,26,4.7z"></path>
                </svg><?php echo esc_attr($v['heading']); ?></span>
    </div>
</div>