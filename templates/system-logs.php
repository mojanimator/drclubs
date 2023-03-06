<section class="m-2 row  "
         style="font-family: Tanha,serif; display:flex; justify-content: center; ">

    <?php use Tightenco\Collect\Support\Collection;

    $info = get_option(\DrClubs\Base\BusinessController::$logs_option_group, \DrClubs\Base\BusinessController::LOGS_DEFAULT) ?>


    <canvas class="col-md-6" style="display: inline-block" id="chart-logs-costumers"
            data-background="<?php echo htmlspecialchars(json_encode(['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)',])); ?>"
            data-labels="<?php echo htmlspecialchars(json_encode(['ثبت نام موفق', 'ثبت نام ناموفق'])); ?>"
            data-data="<?php echo htmlspecialchars(json_encode([(isset($info['register']['success']) ? $info['register']['success'] : 0), (isset($info['register']['fail']) ? $info['register']['fail'] : 0)])); ?>"
    >

    </canvas>

    <canvas class="col-md-6" style="display: inline-block" id="chart-logs-transactions"
            data-background="<?php echo htmlspecialchars(json_encode(['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)',])); ?>"
            data-labels="<?php echo htmlspecialchars(json_encode(['تراکنش موفق', 'تراکنش ناموفق'])); ?>"
            data-data="<?php echo htmlspecialchars(json_encode([(isset($info['buy']['success']) ? $info['buy']['success'] : 0), (isset($info['buy']['fail']) ? $info['buy']['fail'] : 0)])); ?>"
    >

    </canvas>

    <?php

    $api = new \DrClubs\Api\DrClubsApi();
    $options = get_option(\DrClubs\Base\BusinessController::$admin_settings_option_group, \DrClubs\Base\BusinessController::DEFAULT_OPTIONS);
    $default_options = \DrClubs\Base\BusinessController::DEFAULT_OPTIONS;

    $emotes = isset($options['customers']['emotes']) ? $options['customers']['emotes'] : $default_options['customers']['emotes'];
    $levels = isset($options['customers']['levels']) ? $options['customers']['levels'] : $default_options['customers']['levels'];
    $emote_months = isset($options['customers']['emote_months']) ? $options['customers']['emote_months'] : $default_options['customers']['emote_months'];
    $level_amounts = isset($options['customers']['level_amounts']) ? $options['customers']['level_amounts'] : $default_options['customers']['level_amounts'];

    $emote_counts = [];
    foreach ($emotes as $month => $emote) {
        $emote_counts["$month"]['count'] = 0;
        $emote_counts["$month"]['timestamp'] = \Morilog\Jalali\Jalalian::now()->subMonths($month)->getTimestamp();
    }
    $amount_levels = [];
    foreach ($levels as $amount => $level) {
        $amount_levels["$amount"]['count'] = 0;
    }

    $logs = $api->log_table(['type' => 'buy', 'order_by' => 'created_at', 'order_dir' => 'desc'], 'GET');


    $user_group = (new  Collection($logs))->groupBy('user_id');

    //not buy at all
    $buy_user_ids = array_keys($user_group->all());
    foreach (get_users(array('fields' => 'ids', 'exclude' => $buy_user_ids,)) as $user) {
        $emote_counts['3']['count']++;
        $amount_levels['3']['count']++;
    }


    foreach ($user_group as $id => $user_buys) {

        foreach ($user_buys as $idx => $buy) {
            //مشتری وفادار- حداقل یک خرید جدید
            if ($buy->created_at > $emote_counts['1']['timestamp']) {
                $emote_counts['1']['count']++;
                break;
            }
            //مشتری از دست رفته- خرید بین وفادار و بی خیال
            if ($buy->created_at <= $emote_counts['2']['timestamp'] && $buy->created_at > $emote_counts['3']['timestamp']) {
                $emote_counts['2']['count']++;
                break;
            }
            if ($buy->created_at <= $emote_counts['3']['timestamp'] && $idx == count($user_group) - 1) {
                $emote_counts['3']['count']++;
                break;
            }
        }
    }

    foreach ($user_group as $id => $user_buys) {
        $sum = 0;
        foreach ($user_buys as $idx => $buy) {
            $sum += $buy->value;
        }
        if ($sum >= $level_amounts['1'] * 10) {
            $amount_levels['1']['count']++;
        } elseif ($sum >= $level_amounts['2'] * 10) {
            $amount_levels['2']['count']++;
        } else {
            $amount_levels['3']['count']++;
        }

    }

    ?>

    <canvas class="col-md-6  " style="display: inline-block" id="chart-logs-emotes"
            data-background="<?php echo htmlspecialchars(json_encode(['#4CAF50', '#FFA726', '#F44336',])); ?>"
            data-labels="<?php echo htmlspecialchars(json_encode(array_values($emotes))); ?>"
            data-data="<?php echo htmlspecialchars(json_encode([$emote_counts['1']['count'], $emote_counts['2']['count'], $emote_counts['3']['count'],])); ?>"
    >

    </canvas>
    <canvas class="col-md-6  " style="display: inline-block" id="chart-logs-levels"
            data-background="<?php echo htmlspecialchars(json_encode(['#FFC107', '#607D8B', '#795548',])); ?>"
            data-labels="<?php echo htmlspecialchars(json_encode(array_values($levels))); ?>"
            data-data="<?php echo htmlspecialchars(json_encode([$amount_levels['1']['count'], $amount_levels['2']['count'], $amount_levels['3']['count'],])); ?>"
    >

    </canvas>


</section>

<section class="p-1">
    <h3 style="font-family: Tanha,serif;margin: 0;" class="">تراکنش های اخیر</h3>

    <?php

    $table = new  \DrClubs\Api\Tables\TransactionsTable();
    $table->prepare_items();
    $table->display();

    ?>
</section>

<section class="p-1">
    <h3 style="font-family: Tanha,serif;" class="">لیست کاربران ثبت شده در دکتر کلابز</h3>
    <h5 style="font-family: Tanha,serif" class="">کاربران ثبت نام نشده در دکتر کلابز را از
        <a style="text-decoration: none"
           href="<?php echo get_admin_url(null, 'users.php'); ?>">جدول کاربران</a>
        ثبت نام
        کنید</h5>

    <?php

    $table = new  \DrClubs\Api\Tables\UsersTable();
    $table->prepare_items();
    $table->display();

    ?>
</section>