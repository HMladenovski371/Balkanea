<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 13-11-2018
 * Time: 3:00 PM
 * Since: 1.0.0
 * Updated: 1.0.0
 */

if(!isset($position))
	$position = '';

$use_whitelabel = st()->get_option( 'tp_redirect_option', 'off' );
$button_class   = '';
if ( $use_whitelabel == 'on' ) {
	$button_class = 'btn-tp-search-hotels';
}
?>
<div class="form-button">
    <button class="btn btn-primary btn-search <?php echo esc_attr($button_class); ?>" type="submit"><?php echo esc_html__('SEARCH', 'traveler'); ?></button>
</div>
