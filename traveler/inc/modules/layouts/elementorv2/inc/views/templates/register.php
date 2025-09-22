<?php get_header();
$login_page = get_the_permalink(st()->get_option("page_user_login"));
?>
<div id="st-content-wrapper" class="st-style-elementor">
    <?php
    $menu_transparent = st()->get_option('menu_transparent', '');
    if($menu_transparent === 'on'){
        $thumb_id = get_post_thumbnail_id(get_the_ID());

        if($thumb_id){
            $img_url = wp_get_attachment_image_url($thumb_id, 'full');
            echo stt_elementorv2()->loadView('components/banner', ['img_url' => $img_url]);
        }

    }?>
</div>
<div class="container">
    <div id="st-login-form-page" class="st-login-class-wrapper">
        <div class="modal-dialog" role="document">
            <div class="modal-content st-border-radius relative">
                <?php echo st()->load_template('layouts/modern/common/loader'); ?>
                <div class="modal-header d-sm-flex d-md-flex justify-content-between align-items-center">
                    <ul class="account-tabs">
                        <li data-bs-target="login-component"><a href="<?php echo esc_url($login_page) ?>"><?php echo esc_html__('Sign in', 'traveler'); ?></a></li>
                        <li class="active" data-bs-target="register-component"><?php echo esc_html__('Sign up', 'traveler'); ?></li>
                    </ul>
                </div>
                <div class="modal-body relative">
                    <div class="login-form-wrapper register-component active">
                        <div class="heading"><?php echo esc_html__('Create an account', 'traveler'); ?></div>
                        <form action="#" class="form" method="post">
                            <input type="hidden" name="st_theme_style" value="modern"/>
                            <input type="hidden" name="action" value="st_registration_popup">
                            <input type="hidden" name="post_id" value="<?php echo get_the_ID();?>">
                            <div class="form-group">
                                <input type="text" class="form-control" name="username" autocomplete="off"
                                       placeholder="<?php echo esc_html__('Username *', 'traveler') ?>">
                            </div>
                            <div class="form-group">
                                <input type="text" class="form-control" name="fullname" autocomplete="off"
                                       placeholder="<?php echo esc_html__('Full Name', 'traveler') ?>">
                            </div>
                            <div class="form-group">
                                <input type="email" class="form-control" name="email" autocomplete="off"
                                       placeholder="<?php echo esc_html__('Email *', 'traveler') ?>">
                            </div>
                            <div class="form-group field-password">
                                <input type="password" class="form-control" name="password" autocomplete="off"
                                       placeholder="<?php echo esc_html__('Password', 'traveler') ?>">
                                <span class="stt-icon stt-icon-eye ic-view"></span>
                                <span class="stt-icon stt-icon-eye-blind ic-hide"></span>
                            </div>

                            <?php
                            $allow_partner = st()->get_option('setting_partner', 'off');
                            if ($allow_partner == 'on') {
                                ?>
                                <div class="form-group user-type">
                                    <label class="block" for="normal-user">
                                        <input checked id="normal-user" type="radio" class="mr5" name="register_as"
                                               value="normal"> <span data-toggle="tooltip" data-placement="right"
                                                                     title="<?php echo esc_html__('Used for booking services', 'traveler') ?>"><?php echo esc_html__('Normal User', 'traveler') ?></span>
                                    </label>
                                    <label class="block" for="partner-user">
                                        <input id="partner-user" type="radio" class="mr5" name="register_as"
                                               value="partner">
                                        <span data-toggle="tooltip" data-placement="right"
                                              title="<?php echo esc_html__('Used for upload and booking services', 'traveler') ?>"><?php echo esc_html__('Partner User', 'traveler') ?></span>
                                    </label>
                                </div>
                            <?php } else { ?>
                                <input type="hidden" name="register_as" value="normal">
                            <?php } ?>

                            <div class="form-group">
                                <input type="submit" name="submit" class="form-submit"
                                       value="<?php echo esc_html__('Register', 'traveler') ?>">
                            </div>

                            <div class="st-icheck mt-2">
                                <div class="st-icheck-item">
                                    <label for="term">
                                        <?php $term_id = get_option('wp_page_for_privacy_policy'); ?>
                                        <input id="term" type="checkbox" name="term"
                                               class="mr5"> <?php echo wp_kses(sprintf(__('I confirm that I have read and accepted the <a class="st-link" href="%s">privacy policy</a>', 'traveler'), get_the_permalink($term_id)), ['a' => ['href' => [], 'class' => []]]); ?>
                                        <span class="checkmark fcheckbox"></span>
                                    </label>
                                </div>
                            </div>

                            <div class="message-wrapper mt-2"></div>
                            <?php
								if (
									is_plugin_active('traveler-social-login/traveler-social-login.php') &&
									(
										st_social_channel_status('facebook') ||
										st_social_channel_status('google') ||
										st_social_channel_status('twitter')
									)
								):
							?>
                                <div class="advanced">
                                    <p class="text-center f14 c-grey"><span><?php echo esc_html__('or sign in with', 'traveler') ?></span></p>
                                    <div class="social-login">
                                        <?php if (st_social_channel_status('facebook')): ?>
                                            <a onclick="return false" href="#"
                                            class="btn_login_fb_link st_login_social_link" data-channel="facebook">
                                            <div class="st-login-facebook">
                                                    <div
                                                        onlogin="startLoginWithFacebook()"
                                                        class="fb-login-button"
                                                        data-width="100%"
                                                        data-height="48px"
                                                        data-max-rows="1"
                                                        data-size="large"
                                                        login_text="<?php echo esc_html__('Continue with Facebook', 'traveler') ?>"
                                                        data-scope="public_profile, email">
                                                    </div>
                                                </div>
                                            </a>
                                        <?php endif; ?>
                                        <?php if (st_social_channel_status('google')):
                                                echo do_shortcode('[st-google-login type="login"]');
                                            ?>

                                        <?php endif; ?>
                                        <?php if (st_social_channel_status('twitter')): ?>
                                            <a href="<?php echo site_url() ?>/social-login/twitter"
                                            onclick="return false"
                                            class="btn_login_tw_link st_login_social_link" data-channel="twitter">
                                                <span id="button-twitter">
                                                    <span class="icon">
                                                        <svg style="color: white" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-twitter" viewBox="0 0 16 16"> <path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.382A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z" fill="white"></path> </svg>
                                                    </span>
                                                    <span class="text"><?php echo esc_html__('Twitter', 'traveler') ?></span>
                                                </span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
