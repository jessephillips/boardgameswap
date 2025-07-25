<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo del_language_dir(); ?>" lang="<?php echo str_replace('_', '-', osc_current_user_locale()); ?>">
<head>
  <?php osc_current_web_theme_path('head.php'); ?>
  <meta name="robots" content="noindex, nofollow" />
  <meta name="googlebot" content="noindex, nofollow" />
  <script type="text/javascript" src="<?php echo osc_current_web_theme_js_url('jquery.validate.min.js'); ?>"></script>
</head>

<body id="body-user-register" class="lrf">
  <?php UserForm::js_validation(); ?>
  <?php osc_current_web_theme_path('header.php'); ?>

  <div id="i-forms" class="content register">
    <div class="inside">
      <!-- REGISTER FORM -->
      <div id="register" class="box">
        <div class="wrap">
          <h1><?php _e('Sign Up!', 'delta'); ?></h1>
          <h2><?php _e('It takes you just 1 minute!', 'delta'); ?></h2>

          <div class="user_forms register">
            <div class="inner">

              <?php if(function_exists('fl_call_after_install') || function_exists('gc_login_button') || function_exists('fjl_login_button')) { ?>
                <div class="social">
                  <?php if(function_exists('fl_call_after_install')) { ?>
                    <a class="facebook" href="<?php echo facebook_login_link(); ?>" title="<?php echo osc_esc_html(__('Connect with Facebook', 'delta')); ?>">
                      <i class="fab fa-facebook-square"></i>
                      <span><?php _e('Continue with Facebook', 'delta'); ?></span>
                    </a>
                  <?php } ?>

                  <?php if(function_exists('ggl_login_link')) { ?>
                    <a class="google" href="<?php echo ggl_login_link(); ?>" title="<?php echo osc_esc_html(__('Connect with Google', 'delta')); ?>">
                      <i class="fab fa-google"></i>
                      <span><?php _e('Continue with Google', 'delta'); ?></span>
                    </a>
                  <?php } ?>
                  
                  <?php if(function_exists('fjl_login_button')) { ?>
                    <a target="_top" href="javascript:void(0);" class="facebook fl-button fjl-button" onclick="fjlCheckLoginState();" title="<?php echo osc_esc_html(__('Connect with Facebook', 'delta')); ?>">
                      <i class="fab fa-facebook-square"></i>
                      <span><?php _e('Continue with Facebook', 'delta'); ?></span>
                    </a>
                  <?php } ?>
                </div>
              <?php } ?>

              <form name="register" id="register" action="<?php echo osc_base_url(true); ?>" method="post" >
                <input type="hidden" name="page" value="register" />
                <input type="hidden" name="action" value="register_post" />
                
                <?php osc_run_hook('user_pre_register_form'); ?>
                
                <fieldset>
                  <ul id="error_list"></ul>

                  <div class="row nm">
                    <label for="name"><span>First Name and Last Initial</span>
                    <span class="req">*</span></label> <span class="input-box"><?php UserForm::name_text(); ?></span>
                  </div>
                  
                  <div class="row em">
                    <label for="email"><span><?php _e('E-mail', 'delta'); ?></span><span class="req">*</span></label>
                    <span class="input-box"><?php UserForm::email_text(); ?></span>
                  </div>
                  
                  <div class="row p1">
                    <label for="password"><span><?php _e('Password', 'delta'); ?></span><span class="req">*</span></label>
                    <span class="input-box">
                      <?php UserForm::password_text(); ?>
                      <a href="#" class="toggle-pass" title="<?php echo osc_esc_html(__('Show/hide password', 'delta')); ?>"><i class="fa fa-eye-slash"></i></a>
                    </span>
                  </div>
                  
                  <div class="row p2">
                    <label for="password"><span><?php _e('Re-type password', 'delta'); ?></span><span class="req">*</span></label>
                    <span class="input-box">
                      <?php UserForm::check_password_text(); ?>
                      <a href="#" class="toggle-pass" title="<?php echo osc_esc_html(__('Show/hide password', 'delta')); ?>"><i class="fa fa-eye-slash"></i></a>
                    </span>
                  </div>

                  <!-- Address Info -->
                  <fieldset class="s2">
                    <div class="in">
                      <input type="hidden" name="countryId" id="countryId" class="sCountry" value="<?php echo $user['fk_c_country_code']; ?>"/>
                      <input type="hidden" name="regionId" id="regionId" class="sRegion" value="<?php echo $user['fk_i_region_id']; ?>"/>
                      <input type="hidden" name="cityId" id="cityId" class="sCity" value="<?php echo $user['fk_i_city_id']; ?>"/>
                      <div id="location-picker" class="loc-picker picker-v2 ctr-<?php echo (del_count_countries() == 1 ? 'one' : 'more'); ?>">
                        <label for="term2"><span><?php _e('Where is your pickup/meetup location?', 'delta'); ?></span><span class="req">*</span></label>

                        <div class="mini-box">
                          <input type="text" id="term2" class="term2" placeholder="<?php _e('State and City', 'delta'); ?>" value="<?php echo del_get_term('', $prepare['i_country'], $prepare['i_region'], $prepare['i_city']); ?>" autocomplete="off" readonly/>
                          <i class="fa fa-angle-down"></i>
                        </div>
                        
                        <?php $countries = Country::newInstance()->listAll(); ?>

                        <div class="shower-wrap">
                          <div id="shower" class="shower <?php if(is_array($countries) && count($countries) > 1) { ?>multi-country<?php } ?>">
                            <?php echo del_locbox_short($prepare['i_country'], $prepare['i_region'], $prepare['i_city']); ?>
                            <a href="#" class="btn btn-primary mbBg loc-confirm isMobile"><i class="far fa-check-square"></i> <?php _e('Submit', 'delta'); ?></a>

                            <div class="button-wrap isTablet isDesktop">
                              <a href="#" class="btn btn-primary mbBg loc-confirm"><i class="far fa-check-square"></i> <?php _e('Submit', 'delta'); ?></a>
                            </div>
                          </div>
                        </div>

                        <div class="loader"></div>
                      </div>

                      <div class="loc-more">

                        <div class="row address">
                          <label for="address"><?php _e('Address', 'delta'); ?></label>
                          <div class="input-box"><?php ItemForm::address_text($prepare); ?></div>
                        </div>

                        <div class="row zip">
                          <label for="address"><?php _e('ZIP', 'delta'); ?></label>
                          <div class="input-box"><?php ItemForm::zip_text($prepare); ?></div>
                        </div>
                      </div>
                      
                    </div>
                  </fieldset>

                  <!-- Pickup Pre -->
                  <div class="row">
                    <label for="pickup_pref">Pickup Preferences<br><span style="font-weight: normal;">(shown on your listings to make transactions faster/smoother)</span></label>
                    <div class="input-box">
                      <textarea name="pickup_pref" id="pickup_pref" class="short-text-area" placeholder="Examples: I prefer porch pickup at my house from 5pm-8pm."><?php echo osc_esc_html(osc_user_field('pickup_pref')); ?></textarea>
                    </div>
                  </div>

                  <!-- Payment Pref -->
                  <div class="row">
                    <label for="payment_pref">Payment Preferences</label>
                    <div class="input-box">
                      <textarea name="payment_pref" id="payment_pref" class="short-text-area" placeholder="Examples: Venmo, CashApp, PayPal, Zelle, ApplePay, Cash"><?php echo osc_esc_html(osc_user_field('payment_pref')); ?></textarea>
                    </div>
                  </div>

                  <div class="user-reg-hook"><?php osc_run_hook('user_register_form'); ?></div>

                  <?php del_show_recaptcha('register'); ?>

                  <button type="submit" class="complete-registration mbBg2"><?php _e('Create account', 'delta'); ?></button>

                  <div class="row bo">
                    <strong><?php _e('Already have account?', 'delta'); ?></strong>
                    <a href="<?php echo osc_user_login_url(); ?>"><?php _e('Log in', 'delta'); ?></a>
                  </div>
                </fieldset>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php osc_current_web_theme_path('footer.php'); ?>
  
  <script type="text/javascript">
    $(document).ready(function(){
      $('input[name="s_name"]').attr('placeholder', '<?php echo osc_esc_js(__('John D.', 'delta')); ?>').attr('required', true);
      $('input[name="s_email"]').attr('placeholder', '<?php echo osc_esc_js(__('your.email@dot.com', 'delta')); ?>').attr('required', true).prop('type', 'email');
      $('input[name="s_phone_mobile"]').attr('placeholder', '<?php echo osc_esc_js(__('+XXX XXX XXX', 'delta')); ?>');
      $('input[name="s_password"]').attr('placeholder', '<?php echo osc_esc_js(__('YourPass123!', 'delta')); ?>').attr('required', true);
      $('input[name="s_password2"]').attr('placeholder', '<?php echo osc_esc_js(__('YourPass123!', 'delta')); ?>').attr('required', true);
    });
  </script>
</body>
</html>