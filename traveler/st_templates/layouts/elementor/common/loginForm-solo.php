<div class="modal fade form-login--solo" id="st-login-form" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog--width" role="document" style="max-width: 450px;">
        <div class="modal-content relative">
            <?php echo st()->load_template('layouts/modern/common/loader'); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <?php echo TravelHelper::getNewIcon('Ico_close') ?>
                </button>
                <div class="modal-title"><?php echo esc_html__('Log In', 'traveler') ?></div>
                <span class="modal-sub-title"><?php echo esc_html__('Log in to add new things to your wishlist and access your any device', 'traveler') ?></span>
            </div>
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
                    <div class="row">
                        <div class="col-xs-12 col-sm-12">
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
                        </div>
                        <div class="col-12 col-sm-12">
                            <?php if (st_social_channel_status('google')):
                                echo do_shortcode('[st-google-login type="login"]');
                                ?>
                            <?php endif; ?>
                        </div>
                        <div class="col-xs-12 col-sm-12">
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
                </div>
            <?php endif; ?>
            <div class="modal-body relative">
                <form action="#" class="form" method="post">
                    <input type="hidden" name="st_theme_style" value="modern"/>
                    <input type="hidden" name="action" value="st_login_popup">
                    <input type="hidden" name="post_id" value="<?php echo get_the_ID();?>">
                    <?php if (st_social_channel_status('facebook') || st_social_channel_status('google') || st_social_channel_status('twitter') ): ?>
                        <div class="form-group form-padding">
                            <label class="title-form"><?php echo esc_html__('Or','traveler') ?></label>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <input type="text" class="form-control" name="username" autocomplete="off"
                               placeholder="<?php echo esc_html__('Email or Username', 'traveler') ?>">

                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" name="password" autocomplete="off"
                               placeholder="<?php echo esc_html__('Password', 'traveler') ?>">

                    </div>
                    <div class="mt20 st-flex space-between st-icheck">
                        <div class="st-icheck-item">
                            <label for="remember-me" class="c-grey">
                                <input type="checkbox" name="remember" id="remember-me"
                                       value="1"> <?php echo esc_html__('Remember me', 'traveler') ?>
                                <span class="checkmark fcheckbox"></span>
                            </label>
                        </div>
                        <a href="#" class="st-link open-loss-password"
                           data-toggle="modal"><?php echo esc_html__('Forgot Password?', 'traveler') ?></a>
                    </div>
                    <div class="form-group">
                        <input type="submit" name="submit" class="form-submit"
                               value="<?php echo esc_html__('Log In', 'traveler') ?>">
                    </div>
                    <div class="message-wrapper mt20"></div>


                    <div class="mt20 c-grey font-medium f14 text-center">
                        <?php echo esc_html__('New here?', 'traveler') ?>
                        <a href="#"
                           class="st-link open-signup"
                           data-toggle="modal"><?php echo esc_html__('Create an Account', 'traveler') ?></a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
