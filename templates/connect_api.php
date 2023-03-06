<form id="drclubs-auth-form" method="post" data-url="<?php echo admin_url('admin-ajax.php') ?>" action="#">

    <div class="auth-btn">
        <button type="button" value="دکتر کلابز" id="drclubs-show-auth-form"> دکتر کلابز
            <img src="<?php echo plugins_url() . '/drclubs/assets/img/logo.png'; ?>" width="40px" alt="">
        </button>

    </div>

    <div id="drclubs-auth-container" class="auth-container">
        <a href="#" id="drclubs-auth-close" class="close">&times;</a>

        <h2>ورود</h2>
        <label for="username">نام کاربری</label>
        <input type="text" id="username" name="username">
        <label for="password">گذرواژه</label>
        <input type="password" id="password" name="password">
        <input type="submit" value="ورود" name="submit" class="submit_button">

        <p class="status" data-message="status"></p>
        <p class="actions">
            <a href="<?php echo wp_lostpassword_url(); ?>">فراموشی رمز عبور</a>
            <span>|</span>
            <a href="<?php echo wp_registration_url(); ?>">ثبت نام</a>
        </p>

        <input type="hidden" name="action" value="drclubs_login">
        <?php wp_nonce_field('ajax-login-nonce', 'drclubs_auth') ?>
    </div>

</form>