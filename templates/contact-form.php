<form id="drclubs-testimonial-form" action="#" method="post" data-url="<?php echo admin_url('admin-ajax.php'); ?>">


    <div class="field-container">
        <input type="text" name="name" id="name" class="field-input" placeholder="نام"/>
        <small class="field-msg error" data-error="invalidName"> نام ضروری است</small>
    </div>
    <div class="field-container">
        <input type="text" name="email" id="email" class="field-input" placeholder="ایمیل"/>
        <small class="field-msg error" data-error="invalidEmail"> ایمیل ضروری است</small>
    </div>
    <div class="field-container">
        <textarea name="message" id="message" class="field-input" placeholder="متن پیام"></textarea>
        <small class="field-msg error" data-error="invalidMessage"> متن پیام ضروری است</small>
    </div>

    <div class="field-container">
        <div>
            <button type="submit" class="btn btn-default btn-lg btn-sunset-form">ارسال</button>
        </div>

        <small class="field-msg js-form-submission"> در حال ارسال ...</small>
        <small class="field-msg success js-form-success"> پیام شما با موفقیت ارسال شد!</small>
        <small class="field-msg error  js-form-error"> ارسال ناموفق بود. لطفا مجدد تلاش کنید.</small>
    </div>

    <input type="hidden" name="action" value="submit_testimonial">
    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce("testimonial-nonce") ?>">

</form>