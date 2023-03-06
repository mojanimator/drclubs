<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;

use DrClubs\Api\Callbacks\AdminCallbacks;
use DrClubs\Api\Callbacks\BusinessCallbacks;
use DrClubs\Api\Callbacks\CptCallbacks;
use DrClubs\Api\Callbacks\ManagerCallbacks;
use DrClubs\Api\Callbacks\TaxCallbacks;
use DrClubs\Api\Callbacks\TestimonialCallbacks;
use DrClubs\Api\DrClubsApi;
use  DrClubs\Base\BaseController;
use  DrClubs\Api\SettingsApi;
use function DrClubs\f2e;
use function DrClubs\get_timezone_offset;
use DrClubs\Helper;

use function DrClubs\mix;
use function DrClubs\myLog;

use function DrClubs\str_replace_first;
use http\Env\Response;
use Morilog\Jalali\Jalalian;
use Tightenco\Collect\Support\Collection;
use WP_Screen;


class  BusinessController extends BaseController
{

    const LOGS_DEFAULT = ['customers' => ['fail' => 0, 'success' => 0], 'transactions' => ['fail' => 0, 'success' => 0],];
    const LOGS_TABLE = 'drclubs_logs';
    public static $admin_settings_option_group = 'drclubs_admin_option_settings';
    public static $admin_connect_option_group = 'drclubs_admin_connect_settings';
    public static $logs_option_group = 'drclubs_logs';
    public static $cache_option_group = 'drclubs_cache';
    public static $main_settings_page_slug = 'drclubs_settings';
    const DEFAULT_OPTIONS = [
        'user_can_register' => true,
        'user_can_use_balance' => true,
        'user_can_score_from_products' => true,
        'buy_trigger_status' => 'completed',
        'scores' => ['comment' => 0, 'lottery' => ["4", "2", "1", "0", "0", "0", "0", "0", "0", "0"], 'product' => 0],
        'limits' => ['comment' => 24, 'lottery' => 24],
        'units' => ['comment' => 'امتیاز', 'lottery' => 'امتیاز', 'product' => 'امتیاز', 'available' => ['امتیاز', 'ریال']],
        'customers_ui' => [
            'active' => true,
            'positions' =>
                [
                    'راست-بالا' => ['style' => 'right:0;top:0'],
                    'راست-وسط' => ['style' => 'right:0;top:30vh;transform:rotate(-90deg);transform-origin:right$bottom;'],
                    'راست-پایین' => ['style' => 'right:0;bottom:0'],
                    'وسط-پایین' => ['style' => 'right:40vw;bottom:0'],
                    'چپ-پایین' => ['style' => 'left:0;bottom:0'],
                    'چپ-وسط' => ['style' => 'left:0;top:30vh;transform:rotate(90deg);transform-origin:left$bottom;'],
                    'چپ-بالا' => ['style' => 'left:0;top:0'],
                    'selected' => 'راست-وسط'
                ],
            'margins' =>
                [
                    'ازراست' => ['style' => 'margin-right:$px', 'value' => 4],
                    'ازچپ' => ['style' => 'margin-left:$px', 'value' => 4],
                    'ازبالا' => ['style' => 'margin-top:$px', 'value' => 4],
                    'ازپایین' => ['style' => 'margin-bottom:$px', 'value' => 4],

                ]
        ],
        'customers' => [
            'level_amounts' => ['1' => 10000000, '2' => 5000000, '3' => 0],
            'emote_months' => ['1' => 1, '2' => 1, '3' => 3],
            'emotes' => ['1' => 'وفادار', '2' => 'از دست رفته', '3' => 'بی خیال'],
            'levels' => ['1' => 'طلایی', '2' => 'نقره ای', '3' => 'برنزی'],
        ]
    ];
    const DEFAULT_CACHES = ['register' => [], 'buy' => [], 'reward' => [],];

    public $callbacks;
    public $settings;
    public $setting_options;
    public $connect_options;
    public $API;
    public $userController;

    public $excelController;

    public function __construct()
    {
        parent::__construct();
        new Helper();
        $this->callbacks = new BusinessCallbacks();
        $this->settings = new SettingsApi();
        $this->API = new  DrClubsApi();
        $this->userController = new UserController();


    }


    public function register()
    {


        $this->connect_options = get_option(self::$admin_connect_option_group);

        $this->setting_options = get_option(self::$admin_settings_option_group, self::DEFAULT_OPTIONS);

        if (!$this->setting_options)
            $this->setting_options = self::DEFAULT_OPTIONS;

//        unset($this->setting_options['customers_ui']);
//        update_option(self::$admin_settings_option_group, self::DEFAULT_OPTIONS);

        if (isset($this->connect_options['token']))
            $this->API->connect(isset($this->connect_options['username']) ? $this->connect_options['username'] : '', isset($this->connect_options['password']) ? $this->connect_options['password'] : '');


//        $this->setConnectSettings();
        add_action('woocommerce_init', function () {


            $this->setSettings();


            $this->settings->register();

            add_action('wp_ajax_drclubs_prepare', [$this, 'prepareCustomerUi']);
            add_action('wp_ajax_nopriv_drclubs_prepare', [$this, 'prepareCustomerUi']);
//        add_action('wp_head', [$this, 'add_auth_template']);
            add_action('wp_ajax_update', [$this, 'connectAPI']);
            add_action('wp_ajax_make_lottery', [$this, 'makeLottery']);
            add_action('wp_ajax_nopriv_make_lottery', [$this, 'makeLottery']);

            add_action('wp_ajax_get_logs', [$this, 'getLogs']);
            add_action('wp_ajax_nopriv_get_logs', [$this, 'getLogs']);
            add_action('wp_enqueue_scripts', [$this, 'enqueue']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue']);

            add_action('wp_head', [$this, 'add_customer_button_to_front'], 99, 1);

            //score after approve comment
            add_action('transition_comment_status', [$this, 'add_score_after_comment_approved'], 10, 3);
            add_action('wp_insert_comment', [$this, 'add_score_for_pre_approved_comments'], 99, 2);

//        add_action('wp_head', [$this, 'add_auth_template']);
//        add_action('wp_ajax_nopriv_drclubs_login', [$this, 'login']);


            add_action('wp_login', [$this, 'drclubs_get_customer_info'], 99);


            add_action('bulk_actions-users', [$this, 'drclubs_add_bulk_register_to_users_table']);
            add_filter('handle_bulk_actions-users', [$this, 'drclubs_action_bulk_register'], 10, 3);
            add_action('admin_notices', [$this, 'drclubs_bulk_action_admin_notice'], 99);
        });
    }


    public function drclubs_bulk_action_admin_notice()
    {

        if (!empty($_REQUEST['drclubs-bulk-register-users'])) {
            $all = intval($_REQUEST['drclubs-bulk-register-users']);
            $success = intval($_REQUEST['drclubs-bulk-register-success']);
            echo '<div class="notice notice-success is-dismissible">
          <p><strong>ثبت نام کاربران با موفقیت اتمام یافت</strong></p>
          <p><strong>کاربران پردازش شده : </strong><span>' . $all . '</span></p>
          <p><strong>ثبت نام موفق : </strong><span>' . $success . '</span></p>
         </div>';
        }
    }

    public function drclubs_action_bulk_register($redirect_to, $doaction, $post_ids)
    {
        if ($doaction !== 'drclubs-bulk-register') {
            return $redirect_to;
        }
        // if user selected  'select all' checkbox then ignore pagination and loop through all users in database
        $all = 0;
        $success = 0;
        set_time_limit(0); // safe_mode is off
        if (get_user_count() == count($post_ids) || count($post_ids) == get_user_meta(get_current_user_id(), 'users_per_page', true)) {

            myLog('bulk register all ' . get_user_count() . ' users:');
            foreach (get_users(array('fields' => array('ID', 'user_login'))) as $idx => $user) {
                $all++;
//                Helper::$MY_LOG_ACTIVE = false;
                $phone = self::toDrClubsPhone($user->user_login);
//                Helper::$MY_LOG_ACTIVE = true;
                myLog("index:$idx, id:$user->ID, phone:$phone");
                if (!$phone) continue;
                $meta = $this->userController->user($user->ID);
                if (isset($meta->Id)) {
                    myLog("registered before");
//                    Helper::$MY_LOG_ACTIVE = false;
                    continue;
                }
//                Helper::$MY_LOG_ACTIVE = false;
                $params = ['nonce' => wp_create_nonce("drclubs-register-user-$user->ID-nonce"),
                    'Fname' => $user->first_name ? $user->first_name : ($user->display_name ? $user->display_name : ($user->nickname ? $user->nickname : $phone)),
                    'Lname' => $user->last_name,
                    'PhoneNumber' => $phone,
                    'user_id' => $user->ID,
                    'Enabled' => 'true',
                ];
                $res = $this->API->registerUser($params);
                if (isset($res['status']) && $res['status'] == 'success')
                    $success++;
            }
        } else {
            myLog('bulk register ' . count($post_ids) . ' users:');
            foreach ($post_ids as $idx => $user_id) {
                $all++;
//                Helper::$MY_LOG_ACTIVE = false;
                $user = get_userdata($user_id);
                $phone = self::toDrClubsPhone($user->user_login);
//                Helper::$MY_LOG_ACTIVE = true;
                myLog("index:$idx, id:$user_id, phone:$phone");
                if (!$phone) continue;
                $meta = $this->userController->user($user->ID);
                if (isset($meta->Id)) {
                    myLog("registered before");
//                    Helper::$MY_LOG_ACTIVE = false;
                    continue;
                }

//                Helper::$MY_LOG_ACTIVE = false;
                $params = ['nonce' => wp_create_nonce("drclubs-register-user-$user_id-nonce"),
                    'Fname' => $user->first_name ? $user->first_name : ($user->display_name ? $user->display_name : ($user->nickname ? $user->nickname : $phone)),
                    'Lname' => $user->last_name,
                    'PhoneNumber' => $phone,
                    'user_id' => $user_id,
                    'Enabled' => 'true',
                ];
                $res = $this->API->registerUser($params);
                if (isset($res['status']) && $res['status'] == 'success')
                    $success++;
            }
        }
//        Helper::$MY_LOG_ACTIVE = true;
        myLog("bulk register finished. all= $all success=$success");
        $redirect_to = add_query_arg(['drclubs-bulk-register-users' => $all, 'drclubs-bulk-register-success' => $success], $redirect_to);
//        $redirect_to = add_query_arg('drclubs-bulk-register-success', $success, $redirect_to);

        return $redirect_to;
    }

    public function drclubs_add_bulk_register_to_users_table($bulk_actions)
    {

        $bulk_actions['drclubs-bulk-register'] = 'ثبت نام دکتر کلابز';
        return $bulk_actions;
    }

    public function drclubs_get_customer_info($user)
    {
        myLog('login');
        $user = get_user_by('login', $user);
//        myLog($user);
        $phone = isset ($user->user_login) ? $user->user_login : null;
        if (!$phone) return;

        $phone = self::toDrClubsPhone($phone);

        if (str_starts_with($phone, '09'))
            $this->API->getCustomerInfo($user->ID, 'phone', $phone);
        else
            $this->API->getCustomerInfo($user->ID);
    }

    public function prepareCustomerUi()
    {
        if (!is_admin()) wp_send_json(['status' => 'error', 'message' => 'admin_page']);
        $active = isset($this->setting_options['customers_ui']['active']) && $this->setting_options['customers_ui']['active'];
        if (!$active) wp_send_json(['status' => 'error', 'message' => 'plugin_not_active']);

        $positions = isset($this->setting_options['customers_ui']['positions']) ? $this->setting_options['customers_ui']['positions'] : self::DEFAULT_OPTIONS['customers_ui']['positions'];
        $margins = isset($this->setting_options['customers_ui']['margins']) ? $this->setting_options['customers_ui']['margins'] : self::DEFAULT_OPTIONS['customers_ui']['margins'];
        $levels = isset($this->setting_options['customers']['levels']) ? $this->setting_options['customers']['levels'] : self::DEFAULT_OPTIONS['customers']['levels'];

        $level_amounts = isset($this->setting_options['customers']['level_amounts']) ? $this->setting_options['customers']['level_amounts'] : self::DEFAULT_OPTIONS['customers']['level_amounts'];
        $level_name = $levels[array_keys($levels)[count(array_keys($levels)) - 1]];
        $level = array_keys($levels)[count(array_keys($levels)) - 1];
        $user = 0;
        $account = json_encode([]);
        $transactions = [];
        $user_id = get_current_user_id();
        $logTypes = ['comment' => "ثبت نظر", 'lottery' => "گردونه شانس", 'buy' => 'خرید'];
        $limits = isset($this->setting_options['limits']) ? $this->setting_options['limits'] : self::DEFAULT_OPTIONS['limits'];
        $scores = isset($this->setting_options['scores']) ? $this->setting_options['scores'] : self::DEFAULT_OPTIONS['scores'];
        $lotteryUnits = isset($this->setting_options['units']['lottery']) ? $this->setting_options['units']['lottery'] : self::DEFAULT_OPTIONS['units']['lottery'];
        if ($user_id) {
            $user = $this->userController->user();

            if ($user) {
                $account = $this->userController->drclubs_get_user_info('all', $user_id, $user);
                $transactions = isset($user->lastTransactions) && is_array($user->lastTransactions) ? $user->lastTransactions : [];
                foreach ($transactions as $transaction) {

                    $transaction->dateTime = stripslashes(isset($transaction->dateTime) ? date_i18n('d F,Y H:i', (new \DateTime($transaction->dateTime))->getTimestamp()) : '?');
                    $transaction->description = "";
                    $transaction->referenceType = "";
                    $transaction->id = "";

                    if (is_int($account) && $account == -1)
                        $user = 0;
                    if (!$account || (is_int($account) && $account == -1))
                        $account = [];

                    if (isset($account->purchaseVolume))
                        foreach ($level_amounts as $lvl => $amount) {

                            if ($account->purchaseVolume >= $amount * 10) {

                                $level = $lvl;
                                $level_name = $levels[$lvl];
                                break;
                            }
                        }
                    if (isset($account->purchaseVolume))
                        $account->purchaseVolume = WooCommerceController::toSiteCurrency($account->purchaseVolume);
                    if (isset($account->balance))
                        $account->balance = WooCommerceController::toSiteCurrency($account->balance);

                    $account = $account;
                }
            }

            $user = $user ?: 0;


        }

        wp_send_json([
            'status' => 'success',
            'transactions' => $transactions,
            'positions' => $positions,
            'margins' => $margins,
            'log_types' => $logTypes,
            'level' => $level,
            'level_name' => $level_name,
            'limits' => $limits,
            'scores' => $scores,
            'lottery_unit' => $lotteryUnits,
            'account' => $account,
            'user_id' => $user_id,
            'currency' => get_woocommerce_currency_symbol(),
            'lottery_nonce' => wp_create_nonce('lottery_nonce'),
            'log_nonce' => wp_create_nonce('log_nonce'),
            'log_nonce_action' => "log_nonce",
            'drclubs_log_action' => "get_logs",
            'user' => $user,
            'drclubs_register_nonce' => wp_create_nonce("drclubs-register-user-${user_id}-nonce"),
            'drclubs_register_action' => "drclubs_register_user",
            'ajax_url' => admin_url('admin-ajax.php'),
            'login_url' => get_permalink(wc_get_page_id('myaccount')),
            'register_url' => get_permalink(wc_get_page_id('myaccount')) . '?action=register',
            'assets_path' => BaseController::get_assets_path(),
        ]);
    }


    public function add_customer_button_to_front()
    {

        return;
        if (is_admin()) return;
        $active = isset($this->setting_options['customers_ui']['active']) && $this->setting_options['customers_ui']['active'];

        if (!$active) return;
        $positions = json_encode(isset($this->setting_options['customers_ui']['positions']) ? $this->setting_options['customers_ui']['positions'] : self::DEFAULT_OPTIONS['customers_ui']['positions']);
        $margins = json_encode(isset($this->setting_options['customers_ui']['margins']) ? $this->setting_options['customers_ui']['margins'] : self::DEFAULT_OPTIONS['customers_ui']['margins'], JSON_UNESCAPED_UNICODE);
        $levels = isset($this->setting_options['customers']['levels']) ? $this->setting_options['customers']['levels'] : self::DEFAULT_OPTIONS['customers']['levels'];
        $level_amounts = isset($this->setting_options['customers']['level_amounts']) ? $this->setting_options['customers']['level_amounts'] : self::DEFAULT_OPTIONS['customers']['level_amounts'];
        $level_name = $levels[array_keys($levels)[count(array_keys($levels)) - 1]];
        $level = array_keys($levels)[count(array_keys($levels)) - 1];
        $user = 0;
        $account = json_encode([]);
        $transactions = json_encode([]);
        $user_id = get_current_user_id();
        $logTypes = json_encode(['comment' => "ثبت نظر", 'lottery' => "گردونه شانس", 'buy' => 'خرید'], JSON_UNESCAPED_UNICODE);
        $limits = json_encode(isset($this->setting_options['limits']) ? $this->setting_options['limits'] : self::DEFAULT_OPTIONS['limits']);
        $scores = json_encode(isset($this->setting_options['scores']) ? $this->setting_options['scores'] : self::DEFAULT_OPTIONS['scores']);
        $lotteryUnits = isset($this->setting_options['units']['lottery']) ? $this->setting_options['units']['lottery'] : self::DEFAULT_OPTIONS['units']['lottery'];
        if ($user_id) {
            $user = $this->userController->user();

//            myLog('user maybe null ' . $user_id);
//            myLog($user);

            if ($user) {

                $account = $this->userController->drclubs_get_user_info('all', $user_id, $user);
//                myLog('account');
//                myLog($account);
//                myLog('lastTransactions');
//                myLog($user->lastTransactions);


                $transactions = isset($user->lastTransactions) && is_array($user->lastTransactions) ? $user->lastTransactions : [];
                foreach ($transactions as $transaction) {

                    $transaction->dateTime = stripslashes(isset($transaction->dateTime) ? date_i18n('d*F,Y*H:i', (new \DateTime($transaction->dateTime))->getTimestamp()) : '?');
                    $transaction->description = "";
                    $transaction->referenceType = "";
                    $transaction->id = "";

//                    $transaction['dateTime'] = stripslashes(isset($transaction['dateTime']) ? date_i18n('d*F,Y*H:i', (new \DateTime($transaction['dateTime']))->getTimestamp()) : '?');
//                    $transaction['description'] = "";
//                    $transaction['referenceType'] = "";
//                    $transaction['id'] = "";
//                    if (isset($transaction['description']))
//                        unset($transaction['description']);
//                    if (isset($transaction['referenceType']))
//                        unset($transaction['referenceType']);
//                    if (isset($transaction['id']))
//                        unset($transaction['id']);
                }

//                myLog($transactions);
                $transactions = json_encode($transactions, JSON_UNESCAPED_UNICODE);


                //user not found =>deleted from sie
                if (is_int($account) && $account == -1)
                    $user = 0;
                if (!$account || (is_int($account) && $account == -1))
                    $account = [];

                if (isset($account->purchaseVolume))
                    foreach ($level_amounts as $lvl => $amount) {

                        if ($account->purchaseVolume >= $amount * 10) {

                            $level = $lvl;
                            $level_name = $levels[$lvl];
                            break;
                        }
                    }


                if (isset($account->purchaseVolume))
                    $account->purchaseVolume = WooCommerceController::toSiteCurrency($account->purchaseVolume);
                if (isset($account->balance))
                    $account->balance = WooCommerceController::toSiteCurrency($account->balance);

                $account = json_encode($account);


            }
        }

        $user = $user ? str_replace(' ', '*~', json_encode($user, JSON_UNESCAPED_UNICODE)) : 0;


        ?>
        <script>
            //            document.addEventListener("DOMContentLoaded", function () {

            let js = document.createElement("script");
            js.type = "text/javascript";
            js.src = "<?php echo(BaseController::get_assets_path() . 'js/customer-ui.js')?>";
            //            js.async = false;
            //            js.defer = false;
            js.id = "drclubs-customer-ui-js";
            js.setAttribute("data-positions", '<?php echo $positions ?>');
            js.setAttribute("data-transactions", '<?php echo $transactions ?>');
            js.setAttribute("data-log_types", '<?php echo $logTypes ?>');
            js.setAttribute("data-level", "<?php echo $level ?>");
            js.setAttribute("data-level_name", "<?php echo $level_name ?>");
            js.setAttribute("data-margins", '<?php echo $margins ?>');
            js.setAttribute("data-limits", '<?php echo $limits  ?>');
            js.setAttribute("data-scores", '<?php echo $scores?>');
            js.setAttribute("data-lottery_unit", '<?php echo $lotteryUnits ?>');
            js.setAttribute("data-account", '<?php echo $account ?>');
            js.setAttribute("data-user_id",<?php echo $user_id ?>);
            js.setAttribute("data-currency", "<?php echo get_woocommerce_currency_symbol() ?>");
            js.setAttribute("data-lottery_nonce", "<?php echo wp_create_nonce('lottery_nonce') ?>");
            js.setAttribute("data-log_nonce", "<?php echo wp_create_nonce('log_nonce') ?>");
            js.setAttribute("data-log_nonce_action", "log_nonce");
            js.setAttribute("data-drclubs_log_action", "get_logs");
            js.setAttribute("data-user", '<?php echo $user ?>');
            js.setAttribute("data-drclubs_register_nonce", "<?php  echo wp_create_nonce("drclubs-register-user-${user_id}-nonce") ?>");
            js.setAttribute("data-drclubs_register_action", "drclubs_register_user");
            js.setAttribute("data-ajax_url", "<?php echo admin_url('admin-ajax.php'); ?>");
            js.setAttribute("data-login_url", "<?php echo get_permalink(wc_get_page_id('myaccount')) ?>");
            js.setAttribute("data-register_url", "<?php echo(get_permalink(wc_get_page_id('myaccount')) . '?action=register') ?>");
            js.setAttribute("data-assets_path", "<?php echo BaseController::get_assets_path() ?>");
            let header = document.querySelector('head');
            header.appendChild(js);


            //            });
        </script>
        <?php

        /*
                $script = '<script id="drclubs-customer-ui"
                    data-positions=' . $positions . '
                        data-transactions=' . $transactions . '
                        data-log_types=' . json_encode(['comment' => "ثبت*نظر", 'lottery' => "گردونه*شانس", 'buy' => 'خرید'], JSON_UNESCAPED_UNICODE) . '
                        data-level=' . $level . '
                        data-level_name=' . $level_name . '
                        data-margins=' . $margins . '
                        data-limits=' . json_encode(isset($this->setting_options['limits']) ? $this->setting_options['limits'] : self::DEFAULT_OPTIONS['limits']) . '
                        data-scores=' . json_encode(isset($this->setting_options['scores']) ? $this->setting_options['scores'] : self::DEFAULT_OPTIONS['scores']) . '
                        data-lottery_unit=' . (isset($this->setting_options['units']['lottery']) ? $this->setting_options['units']['lottery'] : self::DEFAULT_OPTIONS['units']['lottery']) . '
                        data-account=' . ($account) . '
                        data-user_id=' . $user_id . '
                        data-currency=' . get_woocommerce_currency_symbol() . '
                        data-lottery_nonce=' . wp_create_nonce('lottery_nonce') . '
                        data-log_nonce=' . wp_create_nonce('log_nonce') . '
                        data-log_nonce_action="log_nonce"
                        data-drclubs_log_action="get_logs"
                        data-user=' . $user . '
                        data-drclubs_register_nonce=' . wp_create_nonce("drclubs-register-user-${user_id}-nonce") . '
                        data-drclubs_register_action="drclubs_register_user"
                        data-ajax_url="' . admin_url('admin-ajax.php') . '"
                        data-login_url="' . get_permalink(wc_get_page_id('myaccount')) . '"
                        data-register_url="' . get_permalink(wc_get_page_id('myaccount')) . '?action=register"
                        data-assets_path=' . BaseController::get_assets_path() . '
                        type="text/javascript"
                        src="' . BaseController::get_assets_path() . 'js/customer-ui.js' . '"
              async="async" defer="defer" ></script>';
        */
//        wp_add_inline_script('drclubs-customer-ui', 'document.addEventListener("DOMContentLoaded", function () {
//        console.log("hi");
//        }');

//    wp_enqueue_script('drclubs-customer-ui');
//        wp_register_script('drclubs-customer-ui', BaseController::get_assets_path() . 'js/customer-ui.js');
//        wp_enqueue_script('drclubs-customer-ui', BaseController::get_assets_path() . 'js/customer-ui.js');
//        wp_localize_script('drclubs-customer-ui', 'dataset', array(
//                'alert' => __('Hey! You have clicked the button!', 'pippin'),
//                'message' => __('You have clicked the other button. Good job!', 'pippin')
//            )
//        );
//        echo $script;

    }


    public
    function getLogs()
    {
        Activate::create_drclubs_logs_database_table();

        if (!wp_verify_nonce($_GET['nonce'], $_GET['nonce_action'])) wp_send_json('کد امنیتی اشتباه است');
        global $wpdb;
        $table = $wpdb->prefix . BusinessController::LOGS_TABLE;

        $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $per = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];


        $period = isset($_GET['period']) ? $_GET['period'] : null;
        $type = isset($_GET['type']) ? $_GET['type'] : null;
        $types = isset($_GET['types']) ? $_GET['types'] : null;
        $user_id = isset($_GET['user_id']) ? $_GET['user_id'] : null;
        $order_dir = isset($_GET['order_dir']) ? $_GET['order_dir'] : 'DESC';
        $order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'created_at';
        $page = isset($_GET['page']) ? $_GET['page'] : null;
        $paginate = isset($_GET['paginate']) ? $_GET['paginate'] : 12;
        $group_by = isset($_GET['group_by']) ? $_GET['group_by'] : $type;
        $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : null;
        $from = isset($_GET['dateFrom']) ? \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', str_replace($per, $eng, $_GET['dateFrom'])) : null;
        $to = isset($_GET['dateTo']) ? \Morilog\Jalali\Jalalian::fromFormat('Y/m/d', str_replace($per, $eng, $_GET['dateTo'])) : null;

        $X_labels = [];

        $types = array_map(function ($e) {
            return "'$e'";
        }, $types);

        if ($timestamp == 'd' || !$timestamp) {

        } elseif ($timestamp == 'm') { //from day 1 : to day 30 or 31
            if ($c = $from->getDay() - 1 > 0)
                $from = $from->subDays($c);
            if ($c = $to->getMonthDays() - $to->getDay() > 0)
                $to = $to->addDays($c);
        } elseif ($timestamp == 'y') { //from day 1 : to day 30 or 31
            if ($c = $from->getDay() - 1 > 0)
                $from = $from->subDays($c);
            if ($c = $from->getMonth() - 1 > 0)
                $from = $from->subMonths($c);
            if ($c = $to->getMonthDays() - $to->getDay() > 0)
                $to = $to->addDays($c);
            if ($c = 12 - $to->getMonth() > 0)
                $to = $to->addMonths($c);
        }
        $tmp = $from;


        //fill all times
        while ($tmp->lessThanOrEqualsTo($to)) {

            if ($timestamp == 'd' || !$timestamp) {
                $X_labels[] = $tmp->format('Y/m/d');
                $tmp = $tmp->addDays(1);
            } elseif ($timestamp == 'm') {
                $X_labels[] = $tmp->format('Y/m');
                $tmp = $tmp->addMonths(1);
            } elseif ($timestamp == 'y') {
                $X_labels[] = $tmp->format('Y');
                $tmp = $tmp->addYears(1);
            }
        }


        $query = "SELECT id,user_id,type,value, UNIX_TIMESTAMP(created_at) AS created_at  FROM $table ";

        if ($user_id)
            $query = $wpdb->prepare($query . "WHERE `user_id` = %d ", $user_id);
        if ($type)
            $query = $wpdb->prepare($query . ($user_id ? 'AND' : 'WHERE') . " `type` = %s ", $type);
        if ($types)
            $query = $query . ($user_id || $type ? 'AND' : 'WHERE') . " `type` IN ( " . implode(' , ', $types) . " ) ";
        if ($from && $to)
            $query = $wpdb->prepare($query . ($user_id || $type || $types ? 'AND' : 'WHERE') . " UNIX_TIMESTAMP(created_at) BETWEEN %d AND %d ", $from->getTimestamp(), $to->addDays(1)->getTimestamp());
        elseif ($from)
            $query = $wpdb->prepare($query . ($user_id || $type || $types ? 'AND' : 'WHERE') . " UNIX_TIMESTAMP(created_at) >= %d ", $from->getTimestamp());
        elseif ($to)
            $query = $wpdb->prepare($query . ($user_id || $type || $types || $from ? 'AND' : 'WHERE') . " UNIX_TIMESTAMP(created_at) <= %d ", $to->addDays(1)->getTimestamp());

        if ($order_by)
            $query = $wpdb->prepare($query . " ORDER BY %s %s ", $order_by, $order_dir);

        if ($page)
            $query = $wpdb->prepare($query . " Limit  %s , %s ", $page, $paginate);

//        if ($group_by)
        $res = $wpdb->get_results($query);

//        if (!$res) return [];

//        @ini_set('display_errors', 1);
        $result = (new  Collection($res))->groupBy('type')->map(function (Collection $type) use ($timestamp) {

            return $type->groupBy(function ($data) use ($timestamp) {
                if ($timestamp == 'm')
                    return Jalalian::forge($data->created_at)->format('Y/m');
                elseif ($timestamp == 'y')
                    return Jalalian::forge($data->created_at)->format('Y');
                else
                    return Jalalian::forge($data->created_at)->format('Y/m/d');
            });
        });


        wp_send_json(['datas' => $result, 'dates' => $X_labels]);

    }

    public
    function makeLottery($user_id = null)
    {


        myLog('makeLottery');

        if (!wp_verify_nonce($_POST['nonce'], $_POST['nonce_action'])) wp_send_json('کد امنیتی اشتباه است');
        if (!$user_id)
            $user_id = get_current_user_id();
        if (isset($_POST['score']) && isset($_POST['segment'])) {
            $res = $this->setReward($user_id, 'lottery', $_POST['score']);
            wp_send_json($res === true ? ['status' => 'success', 'segment' => $_POST['segment'] + 1, 'score' => intval($_POST['score'])] : ($res === false ? 'مشکلی در ثبت امتیاز پیش آمد.' : $res));
        }


        $items = $this->setting_options['scores']['lottery'];
        $key = array_rand($items);
        $win = $items[$key];

        $res = $this->setReward($user_id, 'lottery', $win, true);

        wp_send_json($res ? $res : ['status' => 'success', 'segment' => $key + 1, 'score' => intval($win)]);

    }

    public
    function add_score_for_pre_approved_comments($comment_id, \WP_Comment $comment)
    {
        if (!$comment->comment_approved) return;
        $this->setReward($comment->user_id, 'comment', isset($this->setting_options['scores']['comment']) ? $this->setting_options['scores']['comment'] : self::DEFAULT_OPTIONS['scores']['comment']);
    }

    function add_score_after_comment_approved($new_status, $old_status, \WP_Comment $comment)
    {

        if ($new_status != 'approved') return;
        $this->setReward($comment->user_id, 'comment', isset($this->setting_options['scores']['comment']) ? $this->setting_options['scores']['comment'] : self::DEFAULT_OPTIONS['scores']['comment']);
    }

    public
    function setReward($user_id, $type, $score, $just_check_errors = false)
    {
        Activate::create_drclubs_logs_database_table();

        global $wpdb;
        $table = $wpdb->prefix . self::LOGS_TABLE;
        $message = null;
        $limit_hour = $this->setting_options['limits'][$type];

        $res = $wpdb->get_results($wpdb->prepare("SELECT user_id,type, UNIX_TIMESTAMP(created_at) AS created_at  FROM $table WHERE `type` = %s AND `user_id` = %d AND `created_at` > now() - INTERVAL $limit_hour HOUR ORDER By `created_at` DESC LIMIT 1 ", $type, $user_id));

        if (count($res) > 0) {
            $offset = (24 * 3600 - (time() - $res[0]->created_at)) / 60;

            $offset = $offset < 0 ? 0 : $offset;
            if ($offset >= 60) {
                $diff = ceil($offset / 60);
                $message = "لطفا $diff ساعت دیگر اقدام کنید";
                return $message;
            } else {
                $diff = ceil($offset);
                $message = "لطفا $diff دقیقه دیگر اقدام کنید";
                return $message;
            }

        } //user scored in this limit before
        if (!$user_id) {
            $message = "ابتدا وارد شوید یا ثبت نام کنید";
            return $message;
        }
        $user = get_user_meta($user_id, UserController::USER_META_KEY, true);

        if (!$user) {
            $message = "ابتدا در باشگاه مشتریان ثبت نام کنید";
            return $message;
        }

        $user = json_decode($user);

        if (!isset($user->Id))
            return "کاربر یافت نشد";

        if ($just_check_errors) return $message;

        $result = $this->API->setReward([
            'user_id' => $user_id,
            'type' => $type,
            'CustomerId' => $user->Id,
            'Amount' => $score,
            'Description' => json_encode(['user_id' => $user_id, 'type' => $type])
        ], isset($this->setting_options['units'][$type]) ? $this->setting_options['units'][$type] : self::DEFAULT_OPTIONS['units'][$type]);

        if ($result == true)
            $info = $this->API->getCustomerInfo($user_id, get_current_user_id() == $user_id ? 'current_user' : null, $user);
//        if ($result) {
//
//            $wpdb->insert($table, array(
//                'user_id' => $user_id,
//                'type' => $type,
//                'value' => $score,
//            ));
//        }

        return $result;
    }

    public
    function connectAPI()
    {


//        check_ajax_referer(self::$admin_connect_option_group . '-options', '_wpnonce');


        if (!isset($_POST['_wpnonce']))
            wp_send_json(['status' => 'error', 'message' => 'عبارت امنیتی اشتباه است']);

        $nonce = $_POST['_wpnonce'];
        if (!wp_verify_nonce($nonce, self::$admin_connect_option_group . '-options'))
            wp_send_json(['status' => 'error', 'message' => 'عبارت امنیتی اشتباه است']);


        if (!current_user_can('manage_options'))
            wp_send_json(['status' => 'error', 'message' => 'تنها ادمین سایت مجاز به دسترسی است']);

        $username = isset($_POST['drclubs_admin_connect_settings']['username']) ? $_POST['drclubs_admin_connect_settings']['username'] : '';
        $password = isset($_POST['drclubs_admin_connect_settings']['password']) ? $_POST['drclubs_admin_connect_settings']['password'] : '';


        if (!isset($_POST['drclubs_admin_connect_settings']['status']))

            wp_send_json($this->API->disconnect());
//        else

        wp_send_json($this->API->connect($username, $password));

    }

    public
    function enqueue()
    {
        //     if (is_front_page()) {
//        wp_enqueue_style('bootstrap', BaseController::get_assets_path() . 'css/bootstrap.rtl.min.css');
        if (!is_admin()) {
            $active = isset($this->setting_options['customers_ui']['active']) && $this->setting_options['customers_ui']['active'];
            if (!$active) return;
            echo "<script id='drclubs-customer-ui-js' data-asset_url='" . BaseController::get_assets_path() . "' data-ajax_url='" . admin_url('admin-ajax.php') . "' src='" . (BaseController::get_assets_path() . 'js/customer-ui.js') . "'></script>";
//            wp_enqueue_script('drclubs-customer-ui', BaseController::get_assets_path() . 'js/customer-ui.js', null, null, true,);

        }
//            $this->add_customer_button_to_front();
        //   }
    }

    public
    function add_auth_template()
    {
        if (is_user_logged_in()) return;

        $file = $this->plugin_path . 'templates/auth.php';
        if (file_exists($file)) {
            load_template($file, true);
        }
    }

    public
    function login()
    {
        check_ajax_referer('ajax-login-nonce', 'drclubs_auth');

        $info = [
            'user_login' => $_POST['username'],
            'user_password' => $_POST['password'],
            'remember' => true,

        ];
        $user_signon = wp_signon($info, false);

        if (is_wp_error($user_signon)) {
            echo json_encode([
                'status' => false,
                'message' => 'نام کاربری یا کلمه عبور نادرست است'
            ]);
            wp_die();
        }

        echo json_encode([
            'status' => true,
            'message' => 'با موفقیت وارد شدید!'
        ]);

        wp_die();
    }


    public
    function setSettings()
    {


        $args = [
            [
                'option_group' => self::$admin_connect_option_group,
                'option_name' => self::$admin_connect_option_group,
//                'callback' => [$this->callbacks, 'checkboxSanitize']
            ],
            /****************************/
            [
                'option_group' => self::$admin_settings_option_group,
                'option_name' => self::$admin_settings_option_group,
                'callback' => [$this->callbacks, 'settingsValidate']
            ]
        ];

        $this->settings->setSettings($args);

        $args = [
            [
                'id' => 'drclubs_business_connect_section',
                'title' => '<h2 style="font-family: Tanha,serif" > اتصال به دکتر کلابز </h2>',
                'callback' => [$this->callbacks, 'admin_section'],
                'page' => self::$main_settings_page_slug,

            ],
            /****************************/
            [
                'id' => 'drclubs_business_options_section',
                'title' => '<h2 style="font-family: Tanha,serif" >تنظیمات دکتر کلابز</h2>',
                'callback' => [$this->callbacks, 'admin_option_section'],
                'page' => self::$main_settings_page_slug,

            ]
        ];

        $this->settings->setSections($args);

        $lottery = array();
        $lottery[] = [
            'title' => 'گردونه شانس',
            'option_name' => self::$admin_settings_option_group . "[units][lottery]",
            'label_for' => 'unit_lottery',
            'labelStyle' => 'display:block;margin-top:1rem',

            'values' => isset($this->setting_options['units']['available']) ? $this->setting_options['units']['available'] : self::DEFAULT_OPTIONS['units']['available'],
            'value' => isset($this->setting_options['units']['lottery']) ? $this->setting_options['units']['lottery'] : self::DEFAULT_OPTIONS['units']['lottery'],
            'label' => 'واحد جایزه گردونه شانس',
            'description' => 'جایزه کاربر بر اساس واحد تعیین شده خواهد بود',
            'type' => 'radio',

        ];
        foreach ((isset($this->setting_options['scores']['lottery']) ? $this->setting_options['scores']['lottery'] : self::DEFAULT_OPTIONS['scores']['lottery']) as $idx => $score)
            $lottery[] = [
                'title' => 'گردونه شانس',
                'option_name' => self::$admin_settings_option_group . "[scores][lottery][$idx]",
                'label_for' => "score_lottery_$idx",
                'labelStyle' => 'display:block;margin-top:.2rem',
                'inputStyle' => 'max-width:5rem;',
                'wrapperStyle' => 'display: inline-block;',
                'value' => isset($this->setting_options['scores']['lottery'][$idx]) ? $this->setting_options['scores']['lottery'][$idx] : self::DEFAULT_OPTIONS['scores']['lottery'][$idx],
                'label' => " شانس " . ($idx + 1),
                'hint' => 'کاربر میتواند با گرداندن گردونه شانس، امتیاز کسب کند ' . '(در صورت صفر بودن تمام شانس ها، غیر فعال خواهد شد)',


            ];
        $lottery[] = [
            'title' => 'گردونه شانس',
            'option_name' => self::$admin_settings_option_group . "[limits][lottery]",
            'label_for' => 'limit_lottery',
            'labelStyle' => 'display:block;',
            'wrapperStyle' => 'display: block;',
            'value' => isset($this->setting_options['limits']['lottery']) ? $this->setting_options['limits']['lottery'] : self::DEFAULT_OPTIONS['limits']['lottery'],
            'label' => 'محدودیت استفاده(ساعت)',
            'description' => 'در این محدوده،گردونه شانس فقط یکبار قابل استفاده است'

        ];


        $args = [
            /***********connect to api*****************/
            [
                'id' => 'drclubs_username', //must be option_name of settings
                'title' => '',
                'callback' => [$this->callbacks, 'textField'],
                'page' => self::$main_settings_page_slug,
                'section' => 'drclubs_business_connect_section',
                'args' => [
                    'option_name' => self::$admin_connect_option_group . "[username]",
                    'label_for' => 'drclubs_username',
                    'classes' => 'ui-toggle  ',
                    'styles' => 'display:block;margin-top:.2rem',
                    'value' => isset($this->connect_options['username']) ? $this->connect_options['username'] : '',
                    'label' => 'نام کاربری'
                ]
            ],
            [
                'id' => 'drclubs_password', //must be option_name of settings
                'title' => '',
                'callback' => [$this->callbacks, 'textField'],
                'page' => self::$main_settings_page_slug,
                'section' => 'drclubs_business_connect_section',
                'args' => [
                    'option_name' => self::$admin_connect_option_group . "[password]",
                    'label_for' => 'drclubs_password',
                    'classes' => 'ui-toggle',
                    'styles' => 'display:block;margin-top:.2rem',
                    'value' => isset($this->connect_options['password']) ? $this->connect_options['password'] : '',
                    'label' => 'رمز عبور'

                ]
            ], [
                'id' => 'drclubs_status', //must be option_name of settings
                'title' => '',
                'callback' => [$this->callbacks, 'checkBoxField'],
                'page' => self::$main_settings_page_slug,
                'section' => 'drclubs_business_connect_section',
                'args' => [
                    'option_name' => self::$admin_connect_option_group . '[status]',
                    'label_for' => 'drclubs_status',
                    'classes' => 'ui-toggle ',
                    'value' => isset($this->connect_options['token']) ? true : false,
                    'label' => 'وضعیت'

                ]
            ],
            /************customer front ui ****************/
            [
                'id' => 'customer_ui', //must be option_name of settings
                'title' => '',
                'callback' => [$this->callbacks, 'uiGroup'],
                'page' => self::$main_settings_page_slug,
                'section' => 'drclubs_business_options_section',
                'args' => [
                    'options' => isset($this->setting_options['customers_ui']) ? $this->setting_options['customers_ui'] : self::DEFAULT_OPTIONS['customers_ui'],
                    'default_options' => self::DEFAULT_OPTIONS['customers_ui'],
                    'option_name' => self::$admin_settings_option_group . '[customers_ui]',
                    'title' => 'پنل مشتریان',
                ]

            ], /************customer levels ****************/
            [
                'id' => 'customer_levels', //must be option_name of settings
                'title' => '',
                'callback' => [$this->callbacks, 'levelsGroup'],
                'page' => self::$main_settings_page_slug,
                'section' => 'drclubs_business_options_section',
                'args' => [
                    'options' => isset($this->setting_options['customers']) ? $this->setting_options['customers'] : self::DEFAULT_OPTIONS['customers'],
                    'default_options' => self::DEFAULT_OPTIONS['customers'],
                    'option_name' => self::$admin_settings_option_group . '[customers]',
                    'title' => 'رده بندی مشتریان',

                ]

            ],
            /***********user can register in front?*****************/
            [
                'id' => 'user_can_register', //must be option_name of settings
                'title' => '',
                'callback' => [$this->callbacks, 'checkBoxField'],
                'page' => self::$main_settings_page_slug,
                'section' => 'drclubs_business_options_section',
                'args' => [
                    'option_name' => self::$admin_settings_option_group . '[user_can_register]',
                    'label_for' => 'user_can_register',
                    'classes' => 'ui-toggle',
                    'value' => isset($this->setting_options['user_can_register']) ? $this->setting_options['user_can_register'] : false,
                    'label' => 'امکان ثبت نام در باشگاه مشتریان توسط کابر',
                    'description' => ' در صورتی که میخواهید باشگاه مشتریان  فقط توسط ادمین ساخته شود، این گزینه را غیر فعال کنید',

                ]
            ], /***********user can buy with balance?*****************/
            [
                'id' => 'user_can_use_balance', //must be option_name of settings
                'title' => '',
                'callback' => [$this->callbacks, 'checkBoxField'],
                'page' => self::$main_settings_page_slug,
                'section' => 'drclubs_business_options_section',
                'args' => [
                    'option_name' => self::$admin_settings_option_group . '[user_can_use_balance]',
                    'label_for' => 'user_can_use_balance',
                    'classes' => 'ui-toggle',
                    'value' => isset($this->setting_options['user_can_use_balance']) ? $this->setting_options['user_can_use_balance'] : self::DEFAULT_OPTIONS['user_can_use_balance'],
                    'label' => 'امکان خرید با کیف پول',
                    'description' => 'کاربر میتواند از موجودی کیف پول خود برای خرید استفاده کند',

                ]
            ],

            /************product rewards  ****************/


            [
                'id' => 'product_reward', //must be option_name of settings
                'title' => '',
                'callback' => [$this->callbacks, 'textFieldGroup'],
                'page' => self::$main_settings_page_slug,
                'section' => 'drclubs_business_options_section',
                'args' => [

                    [
                        'title' => 'خرید محصولات',
                        'option_name' => self::$admin_settings_option_group . "[user_can_score_from_products]",
                        'label_for' => 'user_can_score_from_products_active',
                        'labelStyle' => 'display:block;margin-top:1rem',

                        'value' => isset($this->setting_options['user_can_score_from_products']) ? $this->setting_options['user_can_score_from_products'] : self::DEFAULT_OPTIONS['user_can_score_from_products'],
                        'label' => 'فعال',
                        'description' => 'مشتری از خرید هر محصول، جایزه دریافت می کند',
                        'type' => 'checkbox',

                    ],
                    ['title' => 'خرید محصولات',
                        'option_name' => self::$admin_settings_option_group . "[units][product]",
                        'label_for' => 'unit_product',
                        'labelStyle' => 'display:block;margin-top:1rem',

                        'values' => isset($this->setting_options['units']['available']) ? $this->setting_options['units']['available'] : self::DEFAULT_OPTIONS['units']['available'],
                        'value' => isset($this->setting_options['units']['product']) ? $this->setting_options['units']['product'] : self::DEFAULT_OPTIONS['units']['product'],
                        'label' => 'واحد جایزه خرید محصول',
                        'description' => 'جایزه کاربر بر اساس واحد تعیین شده خواهد بود',
                        'type' => 'radio',

                    ]
                    ,
                    ['title' => 'خرید محصولات',
                        'option_name' => self::$admin_settings_option_group . "[buy_trigger_status]",
                        'label_for' => 'buy_trigger_status',
                        'labelStyle' => 'display:block;margin-top:1rem',

                        'values' => wc_get_order_statuses(),
                        'value' => isset($this->setting_options['buy_trigger_status']) ? $this->setting_options['buy_trigger_status'] : wc_get_order_status_name(self::DEFAULT_OPTIONS['buy_trigger_status']),
                        'label' => 'زمان ثبت امتیاز خرید در باشگاه مشتریان',
                        'description' => 'جایزه کاربر پس از تغییر وضعیت سفارش به این حالت ثبت خواهد شد',
                        'type' => 'radio',

                    ]
                    ,
                    [
                        'title' => 'ثبت نظر کاربران',
                        'option_name' => self::$admin_settings_option_group . "[scores][product]",
                        'label_for' => 'score_product',
                        'labelStyle' => 'display:block;margin-top:1rem',

                        'value' => isset($this->setting_options['scores']['product']) ? $this->setting_options['scores']['product'] : self::DEFAULT_OPTIONS['scores']['product'],
                        'label' => 'جایزه خرید محصول',
                        'description' => 'پس از خرید، اعمال خواهد شد.' . '(می توانید در تنظیمات هر محصول، جایزه متفاوت تعیین کنید)',
                        'type' => 'number',

                    ]
                    ,
                ]
            ],
            /************comment scores and limits****************/


            [
                'id' => 'comment_score_limit', //must be option_name of settings
                'title' => '',
                'callback' => [$this->callbacks, 'textFieldGroup'],
                'page' => self::$main_settings_page_slug,
                'section' => 'drclubs_business_options_section',
                'args' => [

                    [
                        'title' => 'ثبت نظر کاربران',
                        'option_name' => self::$admin_settings_option_group . "[units][comment]",
                        'label_for' => 'unit_comment',
                        'labelStyle' => 'display:block;margin-top:1rem',

                        'values' => isset($this->setting_options['units']['available']) ? $this->setting_options['units']['available'] : self::DEFAULT_OPTIONS['units']['available'],
                        'value' => isset($this->setting_options['units']['comment']) ? $this->setting_options['units']['comment'] : self::DEFAULT_OPTIONS['units']['comment'],
                        'label' => 'واحد جایزه ثبت نظر',
                        'description' => 'جایزه کاربر بر اساس واحد تعیین شده خواهد بود',
                        'type' => 'radio',

                    ]
                    ,
                    [
                        'title' => 'ثبت نظر کاربران',
                        'option_name' => self::$admin_settings_option_group . "[scores][comment]",
                        'label_for' => 'score_comment',
                        'labelStyle' => 'display:block;margin-top:1rem',

                        'value' => isset($this->setting_options['scores']['comment']) ? $this->setting_options['scores']['comment'] : self::DEFAULT_OPTIONS['scores']['comment'],
                        'label' => 'جایزه ثبت نظر',
                        'description' => 'پس از ثبت و تایید نظر، جایزه به کاربر اضافه خواهد شد. ' . '(در صورت صفر بودن، غیر فعال خواهد شد)',
                        'type' => 'number',

                    ]
                    , [
                        'title' => 'ثبت نظر کاربران',
                        'option_name' => self::$admin_settings_option_group . "[limits][comment]",
                        'label_for' => 'limit_comment',
                        'labelStyle' => 'display:block;',
                        'value' => isset($this->setting_options['limits']['comment']) ? $this->setting_options['limits']['comment'] : self::DEFAULT_OPTIONS['limits']['comment'],
                        'label' => 'محدودیت جایزه ثبت نظر(ساعت)',
                        'description' => 'در این محدوده، فقط یکبار جایزه محاسبه می شود',
                        'type' => 'number',
                    ]]
            ],
            /************Lottery   scores and limits****************/


            [
                'id' => 'lottery_score_limit', //must be option_name of settings
                'title' => '',
                'callback' => [$this->callbacks, 'textFieldGroup'],
                'page' => self::$main_settings_page_slug,
                'section' => 'drclubs_business_options_section',
                'args' => $lottery,
                'type' => 'number',

            ],
        ];


        $this->settings->setFields($args);


    }

    public
    static function toDrClubsPhone($phone)
    {
        myLog("phone before change: $phone");
        if (str_starts_with($phone, '989')) {
            $phone = preg_replace('/98/', '0', $phone, 1);
        } elseif (str_starts_with($phone, '+989')) {
            $phone = preg_replace(['/\+/', '/98/'], ['', '0'], $phone, 1);
        } elseif (str_starts_with($phone, '+09')) {
            $phone = preg_replace('/\+/', '', $phone, 1);
        } elseif (str_starts_with($phone, '9') && strlen($phone) == 10) {
            $phone = "0$phone";
        } elseif (str_starts_with($phone, '09') && strlen($phone) == 11) {
            $phone = "$phone";
        } elseif (!is_numeric()) {
            $phone = '';
        }
        myLog("phone after change: $phone");
        return $phone;
    }

}