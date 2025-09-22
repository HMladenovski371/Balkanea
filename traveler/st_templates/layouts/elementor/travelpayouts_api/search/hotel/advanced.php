<?php
$use_whitelabel = st()->get_option( 'tp_redirect_option', 'off' );
$button_class   = '';
if ( $use_whitelabel == 'on' ) {
	$button_class = 'btn-tp-search-hotels';
}
?>

<div class="form-button d-inline-block d-lg-flex align-items-center justify-content-between form-passengers-class  form-extra-field">
    <button class="btn btn-primary btn-search <?php echo esc_attr($button_class); ?>" type="submit"><?php echo esc_html__('Search', 'traveler'); ?></button>
</div>
