<div class="wrap">

    <h1>تنظیمات تکسونومی دکتر کلابز</h1>

    <?php settings_errors(); ?>

    <ul class="nav nav-tabs">
        <li class="active"><a href="#tab-1">تنظیمات</a></li>
        <li class=""><a href="#tab-2">بروزرسانی</a></li>
        <li class=""><a href="#tab-3">درباره</a></li>

    </ul>

    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <form method="post" action="options.php">
                <?php

                settings_fields('drclubs_plugin_settings');
                do_settings_sections(\DrClubs\Helper::$pageSlug);
                submit_button();
                ?>
            </form>
        </div>
        <div id="tab-2" class="tab-pane  ">
            <h3>بروزرسانی</h3>
        </div>
        <div id="tab-3" class="tab-pane  ">
            <h3>درباره</h3>
        </div>
    </div>


</div>
