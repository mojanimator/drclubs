<div class="drclubs   p-1" style="font-family: Tanha,serif; ">
    <div class="m-card   " style=" display: flex;align-items: center; justify-content:   flex-start;">

        <a href="https://drclubs.ir">
            <img class="m-1" height="48px"
                 src="<?php echo \DrClubs\Base\BaseController::get_assets_path() . 'img/logo.png'; ?>"
                 alt="">
        </a>
        <h1 class="m-1 " style="font-family: Tanha,serif">تنظیمات باشگاه مشتریان دکتر کلابز</h1>
        <?php $v = \DrClubs\Api\DrClubsApi::getVersion() ?>

        <strong class="m-1 text-primary"><?php echo(isset($v) ? 'نسخه ' . $v : ''); ?></strong>
    </div>
    <div class="  widefat"
         style="font-family: Tanha,serif">
        <?php settings_errors(\DrClubs\Base\BusinessController::$admin_connect_option_group);
        ?>
    </div>
    <div class="m-card  p-1   ">
        <form id="drclubs-connect-api-form" class="  " method="post"
              data-url="<?php echo admin_url('admin-ajax.php') ?>" action="#">
            <?php \DrClubs\Base\BaseController::includeLoader(); ?>
            <?php

            settings_fields(\DrClubs\Base\BusinessController::$admin_connect_option_group);
            do_settings_fields(\DrClubs\Base\BusinessController::$main_settings_page_slug, 'drclubs_business_connect_section');

            //            do_settings_sections(\DrClubs\Base\BusinessController::$main_settings_page_slug);
            ?>
            <!--            <input type="hidden" name="action" value="drclubs_connect_api">-->
            <div class="status font-bold"></div>

            <?php

            submit_button('اتصال', 'btn-success hide ', null, null, null);
            ?>
        </form>
    </div>

    <div class="  widefat"
         style="font-family: Tanha,serif">
        <?php settings_errors(\DrClubs\Base\BusinessController::$admin_settings_option_group);
        ?>
    </div>

    <ul class="nav dc-nav-tabs " style="font-family: Tanha,serif">
        <li class="active"><a href="#tab-1">تنظیمات</a></li>
        <li class=""><a href="#tab-2">گزارش سیستم</a></li>
        <li class=""><a href="#tab-3">بروزرسانی</a></li>
        <li class=""><a href="#tab-4">تماس با ما</a></li>

    </ul>

    <div class="dc-tab-content">
        <div id="tab-1" class="dc-tab-pane active">
            <form class="m-1" method="post" action="options.php">
                <?php

                settings_fields(\DrClubs\Base\BusinessController::$admin_settings_option_group);
                //                do_settings_sections(\DrClubs\Base\BusinessController::$main_settings_page_slug);
                do_settings_fields(\DrClubs\Base\BusinessController::$main_settings_page_slug, 'drclubs_business_options_section');
                submit_button('اعمال تغییرات');
                ?>
            </form>
        </div>

        <div id="tab-2" class="dc-tab-pane  ">
            <?php require_once \DrClubs\Base\BaseController::getTemplatePath() . 'system-logs.php'; ?>

        </div>

        <div id="tab-3" class="dc-tab-pane  ">
            <section class="      m-2"
                     style="font-family: Tanha,serif;padding: 1rem;line-height: 2em;border-radius: 1rem; ">
                <div>نسخه فعلی: <strong>   <?php echo $v; ?></strong></div>

                <?php echo(\DrClubs\Base\BaseController::updateExists() ?
                    '<div>  <strong>بروز رسانی موجود است</strong> <a style="text-decoration:none;" href="' . \DrClubs\Base\BaseController::PLUGIN_LINK . '">لینک دریافت</a></div>' :
                    'نسخه شما بروز است')
                ?>

            </section>
        </div>
        <div id="tab-4" class="dc-tab-pane   ">
            <?php require_once \DrClubs\Base\BaseController::getTemplatePath() . 'contact-us.html'; ?>
        </div>
    </div>


</div>
