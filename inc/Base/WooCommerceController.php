<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;


use Automattic\WooCommerce\Utilities\NumberUtil;
use DateTime;
use DateTimeZone;
use function DrClubs\get_timezone_offset;
use DrClubs\Helper;
use function DrClubs\mylog;
use function DrClubs\str_replace_first;
use Tightenco\Collect\Support\Collection;
use WC_Cart;
use WC_Checkout;


class  WooCommerceController extends BaseController
{
    public $userController;
    public $balance;
    public $user;
    public $setting_options;
    public $waitingForBuy = false;
    const   CUSTOM_FIELD_KEY = 'drclubs_checkout_consumed_balance';
    const   ORDER_INFO_KEY = 'drclubs_order_info';
    const   PRODUCT_INFO_KEY = 'drclubs_product_info';


    /*TODO: نماد واحد های پولی

         IRT: تومان
         IRR: ریال
         IRHR: هزار ریال
         IRHT: هزار تومان

    */
    public function __construct()
    {
        parent::__construct();
        $this->userController = new UserController();
        $this->user = $this->userController->user();
        $this->setting_options = get_option(BusinessController::$admin_settings_option_group, BusinessController::DEFAULT_OPTIONS);

    }

    public static function toSiteCurrency($balance, $toCurrency = null)
    {
        if (!$toCurrency)
            $toCurrency = get_woocommerce_currency();

        if (is_numeric($balance) && $balance >= 0) {
            switch ($toCurrency) {
                case 'IRT':
                    return floor($balance / 10);
                case 'IRHT':
                    return floor($balance / 10000);
                case 'IRHR':
                    return floor($balance / 1000);
                default:
                    //IRR
                    return $balance;

            }
        }
        return null;
    }

    public static function toDrClubsCurrency($num)
    {
        $currency = get_woocommerce_currency();
        if (is_numeric($num) && $num >= 0) {
            switch ($currency) {
                case 'IRT':
                    return floor($num * 10);
                case 'IRHT':
                    return floor($num * 10000);
                case 'IRHR':
                    return $num * 1000;
                default:
                    //IRR
                    return $num;

            }
        }
        return $num;
    }

    public function register()
    {
        // Test to see if WooCommerce is active (including network activated).

        if (self::woocommerce_not_activated()) {
            self::woocommerce_not_activated_admin_notice();
            return;
        }
        add_action('woocommerce_init', function () {
            $this->balance = $this->userController->drclubs_get_user_info('balance');

        });

        add_action('wp_head', function () {
//            wp_enqueue_script('my-form-register', $this->plugin_url . 'assets/js/script.js', __FILE__);
            wp_enqueue_script('my-woocommerce-jquery', $this->plugin_url . 'assets/js/woocommerce.js', __FILE__);
//            wp_enqueue_style('my-styles', $this->plugin_url . 'assets/css/style.css', __FILE__);

        });

        add_action('woocommerce_after_order_notes', [$this, 'drclubs_checkout_field']);
        add_action('woocommerce_checkout_process', [$this, 'drclubs_checkout_field_validation']);
        add_action('woocommerce_checkout_update_order_meta', [$this, 'drclubs_checkout_field_update_order_meta'], 9999);
        add_action('woocommerce_after_calculate_totals', [$this, 'drclubs_after_calculate_total_in_checkout']);
        add_action('woocommerce_before_calculate_totals', [$this, 'drclubs_before_calculate_total_in_checkout']);

        add_action('woocommerce_cart_calculate_fees', [$this, 'add_custom_fees']);

//        add_action('woocommerce_checkout_update_user_meta', [$this, 'drclubs_update_consumed_balance']);
        add_filter('woocommerce_checkout_get_value', [$this, 'woocommerce_checkout_get_cached_value'], 9999, 2);

        add_action('woocommerce_checkout_update_order_review', [$this, 'custom_woocommerce_checkout_update_order_review']);

        add_action('woocommerce_calculated_total', [$this, 'drclubs_calculated_total'], 10, 2);

        add_action('woocommerce_checkout_create_order', [$this, 'drclubs_before_create_order'], 20, 1);
        add_action('woocommerce_new_order', [$this, 'drclubs_after_create_order'], 20, 1);

        // save the new field to order custom fields
        // display the custom field value on the admin order edition page
        add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'drclubs_add_custom_field_to_admin_orders'], 10, 1);
        //Display the custom field label and value in frontend orders and email notifications
        add_action('woocommerce_order_item_meta_end', [$this, 'drclubs_display_custom_field_to_frontend_orders'], 10, 3);
        add_filter("woocommerce_order_get_total", [$this, "drclubs_order_get_total"], 10, 2);
        add_action('woocommerce_order_after_calculate_totals', [$this, "drclubs_order_after_calculate_totals"], 10, 2);

        //when order completed send buy to drclubs
        add_action('woocommerce_order_status_changed', [$this, 'drclubs_consume_credit_when_order_changed_to_completed'], 10, 3);
        add_action('woocommerce_payment_complete', [$this, 'drclubs_consume_credit_when_payment_completed']);
//
        add_filter('woocommerce_product_data_tabs', [$this, 'drclubs_add_tab_to_admin_products'], 10, 2);
        add_action('woocommerce_product_data_panels', [$this, 'drclubs_add_tab_panel_to_admin_products'], 10, 2);
//        add_action('woocommerce_product_after_variable_attributes', [$this, 'drclubs_admin_variable_product_custom_field'], 10, 3);
//        add_action('woocommerce_save_product_variation', [$this, 'drclubs_save_admin_variable_product_custom_field'], 20, 2);
        add_action('woocommerce_process_product_meta', [$this, 'drclubs_save_admin_product_field']);


        add_action('woocommerce_after_shop_loop_item', [$this, 'drclubs_add_reward_meta_to_product_list_front'], 60, 0);
        add_action('woocommerce_before_add_to_cart_form', [$this, 'drclubs_add_reward_meta_to_product_list_front'], 60, 0);

        add_action('save_post_shop_order', [$this, 'drclubs_calculate_order_product_rewards'], 10, 3);


    }

    public function drclubs_calculate_order_product_rewards($postId, \WP_Post $post, $update)
    {
        $active = isset($this->setting_options['user_can_score_from_products']) && $this->setting_options['user_can_score_from_products'];
        if (!$active) return;

        $order = wc_get_order($postId);
        $order_options = $order->get_meta(self::ORDER_INFO_KEY, true);
        $order_options = is_array($order_options) ? $order_options : [];

        $sum = [];
        foreach (BusinessController::DEFAULT_OPTIONS['units']['available'] as $unit) {
            $sum[$unit] = 0;
        }
        foreach ($order->get_items() as $item_id => $item) {


            $product_options = get_post_meta(isset($item['product_id']) ? $item['product_id'] : $item->get_product_id(), self::PRODUCT_INFO_KEY, true);

            $active = isset($product_options['reward_active']) ? $product_options['reward_active'] : true;

            if (!$active) continue;
            $reward = isset($product_options['reward']) ? $product_options['reward'] : null;
            $reward = $reward ? $reward : (isset($this->setting_options['scores']['product']) ? $this->setting_options['scores']['product'] : BusinessController::DEFAULT_OPTIONS['scores']['product']);


            $unit = isset($product_options['reward_unit']) ? $product_options['reward_unit'] : null;
            $unit = $unit ? $unit : (isset($this->setting_options['units']['product']) ? $this->setting_options['units']['product'] : BusinessController::DEFAULT_OPTIONS['units']['product']);

            $sum[$unit] += $reward;
        }

        $order_options['products_reward'] = $sum;
        update_post_meta($postId, self::ORDER_INFO_KEY, $order_options);

    }

    public function drclubs_add_reward_meta_to_product_list_front()
    {
        $active = isset($this->setting_options['user_can_score_from_products']) && $this->setting_options['user_can_score_from_products'];
        if (!$active) return;

        global $post;
        $product_options = get_post_meta($post->ID, self::PRODUCT_INFO_KEY, true);

        $active2 = isset($product_options['reward_active']) ? $product_options['reward_active'] : null;
        if (!$active && !$active2) return;
        $reward = isset($product_options['reward']) ? $product_options['reward'] : null;
        $reward = $reward ? $reward : (isset($this->setting_options['scores']['product']) ? $this->setting_options['scores']['product'] : BusinessController::DEFAULT_OPTIONS['scores']['product']);

        $unit = isset($product_options['reward_unit']) ? $product_options['reward_unit'] : null;
        $unit = $unit ? $unit : (isset($this->setting_options['units']['product']) ? $this->setting_options['units']['product'] : BusinessController::DEFAULT_OPTIONS['units']['product']);

        if (!$reward) return;

        echo '<div class="wc-block-components-product-metadata   p-1 d-block">
        <img class="d-inline" height="24" width="24" src="' . BaseController::get_assets_path() . 'img/logo.png' . '" alt="">
        <span class=”product-meta-label”>جایزه باشگاه مشتریان:</span>
        <strong>' . "$reward $unit" . '</strong>
            </div>';
    }


    public function drclubs_save_admin_product_field($post_id)
    {
        $product_options = get_post_meta($post_id, self::PRODUCT_INFO_KEY, true);
        $product_options = $product_options ? $product_options : [];
        $active = isset($_POST[self::PRODUCT_INFO_KEY . "_reward_active_$post_id"]) ? $_POST[self::PRODUCT_INFO_KEY . "_reward_active_$post_id"] : false;
        $reward = isset($_POST[self::PRODUCT_INFO_KEY . "_reward_$post_id"]) ? $_POST[self::PRODUCT_INFO_KEY . "_reward_$post_id"] : 0;
        $unit = isset($_POST[self::PRODUCT_INFO_KEY . "_reward_unit_$post_id"]) ? $_POST[self::PRODUCT_INFO_KEY . "_reward_unit_$post_id"] : null;


        if (is_numeric($reward) && in_array($unit, BusinessController::DEFAULT_OPTIONS['units']['available'])) {

            $product_options['reward'] = $reward;
            $product_options['reward_active'] = $active;
            $product_options['reward_unit'] = $unit;
            update_post_meta($post_id, self::PRODUCT_INFO_KEY, $product_options);

        }

    }

    public function drclubs_add_tab_panel_to_admin_products()
    {
        global $woocommerce, $post;

        $product_options = get_post_meta($post->ID, self::PRODUCT_INFO_KEY, true);

        $active = isset($product_options['reward_active']) ? $product_options['reward_active'] : false;

        $reward = isset($product_options['reward']) ? $product_options['reward'] : null;
        $reward = $reward ? $reward : (isset($this->setting_options['scores']['product']) ? $this->setting_options['scores']['product'] : BusinessController::DEFAULT_OPTIONS['scores']['product']);

        $unit = isset($product_options['reward_unit']) ? $product_options['reward_unit'] : null;
        $unit = $unit ? $unit : (isset($this->setting_options['units']['product']) ? $this->setting_options['units']['product'] : BusinessController::DEFAULT_OPTIONS['units']['product']);
        $arr = [];
        foreach (BusinessController::DEFAULT_OPTIONS['units']['available'] as $item) {
            $arr[] = '
            
            <span style=" " class="  font-bold  " for="' . $item . '">
            <input style="" type="radio" class="regular-text   " id="' . $item . '" name="' . self::PRODUCT_INFO_KEY . "_reward_unit_$post->ID" . '" value="' . $item . '" ' . ($unit == $item ? 'checked' : ' ') . ' />
' . $item . '</span>
            
            ';

        }

        echo '<div id="drclubs_tab" class="panel woocommerce_options_panel">';

        woocommerce_wp_checkbox(array(
            'id' => '_drclubs_reward',
            'name' => self::PRODUCT_INFO_KEY . "_reward_active_$post->ID",
            'cbvalue' => true,
            'value' => $active,
            'label' => 'جایزه از خرید این محصول',
            'desc_tip' => true,
            'description' => 'با غیر فعال کردن، جایزه این محصول غیر فعال خواهد شد',
            'class' => 'mx-1',

        ));


        echo '
      <div   class="pt-2 font-bold">واحد جایزه خرید محصول</div>
      <div style="font-size: small;color:dimgrey" class="px-1">جایزه کاربر بر اساس واحد تعیین شده خواهد بود</div>
      <div class="  p-1"> ' . implode(' ', $arr) . '</div>
      ';
        woocommerce_wp_text_input(
            array(
                'id' => '_drclubs_reward_input',
                'name' => self::PRODUCT_INFO_KEY . "_reward_$post->ID",
                'value' => $reward,

                'placeholder' => 'امتیاز',
                'label' => 'جایزه باشگاه مشتریان ' . "($unit)",
                'desc_tip' => true,
                'type' => 'number',
                'class' => 'mx-1',
                'custom_attributes' => array(
                    'step' => 'any',
                    'min' => '0'
                )
            )
        );

        echo '</div>';
    }

    public function drclubs_add_tab_to_admin_products($tabs)
    {
        add_action('admin_head', function () {
            echo "<style>
        #drclubs_tab::before {
            content:   " . '\f101' . "!important ;font-family: Dashicons;
        } 
    </style>";
        });
        $tabs['drclubs'] = array(
            'label' => 'دکتر کلابز',
            'target' => 'drclubs_tab',
            'priority' => 90,
        );
        return $tabs;
    }


    function drclubs_consume_credit_when_payment_completed($order_id)
    {
        $order = wc_get_order($order_id);

        mylog("payment_completed order: $order_id");
//        $this->userController->buy($order, $this->user);

    }

    public
    function drclubs_consume_credit_when_order_changed_to_completed($order_id, $old_status, $new_status)
    {
        if ($this->waitingForBuy) {
            mylog("preventing send repeated buy .waiting .....");
            return;
        }
        $this->waitingForBuy = true;
        $buyOrderStatus = isset($this->setting_options['buy_trigger_status']) ? $this->setting_options['buy_trigger_status'] : wc_get_order_status_name(BusinessController::DEFAULT_OPTIONS['buy_trigger_status']);

        //cancelled
        //processing در حال انجام
        //pending در انتظار پرداخت
        //on-hold در انتظار بررسی
        //failed ناموفق
        //refunded مسترد شده
        //completed تکمیل شده
        //failed لغو شده
        //
//        var_dump($old_status);
//        var_dump($new_status);
        $new_status_name = wc_get_order_status_name($new_status);
        mylog("drclubs order for trigger buy must be : $buyOrderStatus" . PHP_EOL . "drclubs order $order_id  changed to order: $new_status_name");
        if ($new_status_name == $buyOrderStatus /*|| $new_status == 'processing'*/) {
            $res = $this->userController->buy(wc_get_order($order_id), $this->user);
        }
        $this->waitingForBuy = false;

    }


    public
    function drclubs_add_custom_field_to_admin_orders(\WC_Order $order)
    {
        // Get the custom field value
//        $drclubs_consumed = get_post_meta($order->get_id(), self::ORDER_INFO_KEY, true);
        $drclubs_consumed = $order->get_meta(self::ORDER_INFO_KEY, true);
        $drclubs_consumed_credit = isset($drclubs_consumed['consumed_credit']) ? $drclubs_consumed['consumed_credit'] : '';
        // Display the custom field:

        echo '<p><strong>' . 'برداشت از دکتر کلابز' . ': </strong>' . $drclubs_consumed_credit . ' ' . get_woocommerce_currency_symbol();

        $offset = get_timezone_offset('ASia/Tehran', 'utc');
        $orderTimestamp = isset($drclubs_consumed['consumed_at']) ? $drclubs_consumed['consumed_at'] : 0;
        $time = date_i18n('F d, Y H:i', $orderTimestamp /* - $offset*/);
        echo ' | <strong> وضعیت: </strong><span>' . (isset($drclubs_consumed['consumed_at']) ? (' اعمال شده در تاریخ ' . $time) : 'پس از تکمیل شدن سفارش، محاسبه خواهد شد') . '</span>';

        echo '</p>';
    }

    public
    function drclubs_display_custom_field_to_frontend_orders($item_id, $item, \WC_Order $order)
    {
        // Get the custom field value
//        $drclubs_consumed_credit = get_post_meta($order->get_id(), self::ORDER_INFO_KEY, true);
//        $drclubs_consumed_credit = isset($drclubs_consumed_credit['consumed_credit']) ? $drclubs_consumed_credit['consumed_credit'] : '';
//        // Display the custom field:
//        echo '<p><strong>' . __('برداشت از دکتر کلابز', 'woocommerce') . ': </strong>' . $drclubs_consumed_credit . '</p>';

    }


    function custom_woocommerce_checkout_update_order_review($post_data)
    {

        // Convert $post_data string to array and clean it
        $post_arr = array();
        parse_str($post_data, $post_arr);
        wc_clean($post_arr);

        if (isset($post_arr[self::CUSTOM_FIELD_KEY])) {
            WC()->session->set(self::CUSTOM_FIELD_KEY, ($post_arr[self::CUSTOM_FIELD_KEY] >= 0 ? $post_arr[self::CUSTOM_FIELD_KEY] : 0));
        }
    }

    function woocommerce_checkout_get_cached_value($value, $index)
    {


        if ($index == self::CUSTOM_FIELD_KEY)
            return isset(WC()->session) ? WC()->session->get(self::CUSTOM_FIELD_KEY, 0) : 0;

        return $value;
    }

//    public function drclubs_update_consumed_balance($customer_id, $posted)
//    {
//        var_dump($posted);
//
//        WC()->session->set(self::CUSTOM_FIELD_KEY, sanitize_text_field($posted[self::CUSTOM_FIELD_KEY]));
//        update_user_meta($customer_id, self::CUSTOM_FIELD_KEY, sanitize_text_field($posted[self::CUSTOM_FIELD_KEY]));
//    }

    function add_custom_fees(WC_Cart $cart_object)
    {
        if (is_admin() && !defined('DOING_AJAX'))
            return;
        if (!is_checkout()) return;
        if (isset($this->setting_options['user_can_use_balance']) && !$this->setting_options['user_can_use_balance']) return;

        $drclubs_fee = isset(WC()->session) ? WC()->session->get(self::CUSTOM_FIELD_KEY, 0) : 0;
        $drclubs_fee = $drclubs_fee > $this->balance ? ($this->balance >= 0 ? $this->balance : 0) : $drclubs_fee;

//        if (!WC()->session->get('calculating', false))
        $cart_object->add_fee('برداشت از اعتبار دکتر کلابز', ($drclubs_fee), false);

//        foreach ($cart_object->get_fees() as $key => $fee) {
//
//            if ($fee->name == 'برداشت از اعتبار دکتر کلابز') {
//
////                $fee->tax = 0;
//                $cart_object->set_total_tax(500);
//                mylog($cart_object->get_total_tax());
//            }
//
//        }
    }


//correct total when recalculate in order section
    public
    function drclubs_order_get_total($value, \WC_Order $order)
    {
        global $pagenow;
        if ($pagenow == 'edit.php' && (isset($_GET['post_type']) && $_GET['post_type'] == 'shop_order')) return $value;
        if ($pagenow == 'post.php' && (isset($_GET['action']) && $_GET['action'] == 'edit')) return $value;
        if (!is_admin()) return $value;
//        mylog('drclubs_order_get_total');

//        $order->get_total(null);

//        if (defined('DOING_AJAX')) return $value;
//            return $value;

//        if (!is_admin())
//            return $value;
//        return get_post_meta($order->get_id(), '_order_total', true);

        foreach ($order->get_fees() as $key => $fee) {

            if ($fee->get_name() == 'برداشت از اعتبار دکتر کلابز') {
                $total = $value - $fee->get_amount() - $fee->get_amount();

                if ($total < 0)
                    $total = 0;

//                $order->set_total($total);

                return $total;

            }
//
        }
        return $value;

    }

    public
    function drclubs_order_after_calculate_totals($and_taxes, \WC_Order $order)
    {
//        mylog('drclubs_order_after_calculate_totals');
        foreach ($order->get_fees() as $key => $fee) {

            if ($fee->get_name() == 'برداشت از اعتبار دکتر کلابز') {
                $total = $order->get_total(null) - $fee->get_amount() - $fee->get_amount();


                if ($total < 0)
                    $total = 0;
                $order->set_total($total);

//                $order->set_total($total);
                update_post_meta($order->get_id(), '_order_total', $total);


            }
//
        }


    }

    public
    function drclubs_calculated_total($total, WC_Cart $cart)
    {


        $drclubs_fee = isset(WC()->session) ? WC()->session->get(self::CUSTOM_FIELD_KEY, 0) : 0;
        $drclubs_fee = $drclubs_fee > $this->balance ? ($this->balance >= 0 ? $this->balance : 0) : $drclubs_fee;

        foreach ($cart->get_fees() as $key => $fee) {

            if ($fee->name == 'برداشت از اعتبار دکتر کلابز') {
                myLog("calculate total:$total , drclubs fee:$drclubs_fee");
                if ($fee->amount > ($total - $drclubs_fee)) {
                    WC()->session->set(self::CUSTOM_FIELD_KEY, ($total - $drclubs_fee));

                }

            }

        }

        return $total - $drclubs_fee - $drclubs_fee;

    }

    public
    function drclubs_after_create_order($order_id)
    {
        $drclubs_fee = isset(WC()->session) ? WC()->session->get(self::CUSTOM_FIELD_KEY, 0) : 0;
        update_post_meta($order_id, self::ORDER_INFO_KEY, ['consumed_credit' => sanitize_text_field($drclubs_fee), 'consumed_at' => null]);

    }

    public
    function drclubs_before_create_order(\WC_Order $order)
    {

        //register user with phone and name in checkout
        $user_id = $order->get_user_id();
        $user = $order->get_user();
        $phone = $order->get_billing_phone();
        $phone = $phone ? $phone : $order->get_billing_mobile();
        $phone = BusinessController::toDrClubsPhone($phone);
        mylog("order user : $user_id");
//        mylog($user);
//        mylog("order :");
//        mylog($order->get_base_data());
//        mylog($order->get_meta());
        //check maybe user registered before and not login
        if (!is_numeric($user_id) || $user_id <= 0) {
            mylog("find user   : ");
            $tmp = get_user_by('login', $order->get_billing_phone());
            $tmp = get_user_by('login', $order->get_billing_mobile());
            $tmp = $tmp ? $tmp : get_user_by('login', $phone);
            mylog("find user by : " . $phone);
            $beforeUser = $tmp ? $tmp : get_user_by('email', $order->get_billing_email());
            mylog("find user by : " . $order->get_billing_email());
            mylog('before user:');
            mylog($beforeUser);
            if ($beforeUser && $beforeUser instanceof \WP_User) {
                $user_id = $beforeUser->ID;

                mylog('before user found:');
                mylog($user_id);
                $order->set_customer_id($user_id);
                wp_set_current_user($user_id);
            }
        }


        $pass = null;
        // if user not registered in site =>register
        if (!is_numeric($user_id) || $user_id <= 0) {
            $pass = wp_generate_password();
            $login = $phone ? $phone : $order->get_billing_email();
            $user_id = wp_insert_user([
                'user_pass' => $pass,
                'user_login' => $login,
                'user_email' => is_email($order->get_billing_email()) ? $order->get_billing_email() : null,
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
            ]);
            if (!is_numeric($user_id)) {
                mylog("insert user error: $login");
                mylog($user_id);
                return;
            }
            mylog('user_id created');
            mylog($user_id);
            $order->set_customer_id($user_id);
        }
        //not registered successfully => return
        if (!is_numeric($user_id)) return;

        //user is in drclubs before =>return
        $info = $this->userController->dcAPI->getCustomerInfo($user_id);
        if (isset($info)) return;

        $data = [
            'nonce' => wp_create_nonce("drclubs-register-user-$user_id-nonce"),
            'Fname' => $order->get_billing_first_name(),
            'Lname' => $order->get_billing_last_name(),
            'PhoneNumber' => $phone,
            'user_id' => $user_id,
            'Enabled' => 'true',
            'cache' => true,
        ];

        $res = $this->userController->drclubs_register_user($data);
        mylog('register result');
        mylog($res);
        if (isset($res['message']) && is_string($res['message'])) {
            if (str_contains($res['message'], 'شماره تماس تکراری است')) {
                //get user that has this phone and update this user
                $info = $this->userController->dcAPI->getCustomerInfo($user_id, 'phone', $phone);
            }
        }

        if (get_current_user_id()) return;
//        if (!get_current_user_id() && $pass)
//            $newUser = wp_signon([
//                'user_password' => $pass,
//                'user_login' => $order->get_billing_phone() ? $order->get_billing_phone() : $order->get_billing_email(),
//                'remember' => true,
//            ]);
//        if (!($newUser instanceof \WP_User)) return;

        wp_clear_auth_cookie();
        wp_set_current_user($user_id);
        wp_set_auth_cookie($user_id);
//        mylog('drclubs_register_user:');
//        mylog($res);

        //remove drclubs fee for recalculate
//        foreach ($order->get_items('fee') as $key => $fee) {
//            if ($fee->get_name() == 'برداشت از اعتبار دکتر کلابز') {
//                mylog($fee->calculate_taxes());
//                $order->remove_item($key);
//            }
//        }


//        $order->save();
////        $ct = $this->calculate_totals($order);
//        WC()->session->set('calculating', true);
//        $ct = $order->calculate_totals();
//        WC()->session->set('calculating', false);
//
//        $drclubs_fee = WC()->session->get(self::CUSTOM_FIELD_KEY) ?: 0; // مقدار برداشته شده از کیف پول
//
//        if ($drclubs_fee > $ct)
//            $drclubs_fee = $ct;
//        WC()->session->set(self::CUSTOM_FIELD_KEY, $drclubs_fee);
//        error_log(print_r($order, true));


    }

    public
    function drclubs_before_calculate_total_in_checkout(WC_Cart $cart_object)
    {

        //remove tax for drclubs fee
//        var_dump($cart_object->get_taxes());
//        foreach ($cart_object->get_taxes() as $tax) {
//            if ($tax->name == 'برداشت از اعتبار دکتر کلابز')
//                break;
//        }
//
//        $cart_object->get_total();
//
//        if ($cart_object->get_total(null) - $drclubs_fee <= 0) {
//            WC()->session->set(self::CUSTOM_FIELD_KEY, $drclubs_fee - $cart_object->get_total(null));
//        }

//        $drclubs_fee = WC()->session->get(self::CUSTOM_FIELD_KEY);
//        $drclubs_fee = $drclubs_fee > $this->balance ? ($this->balance >= 0 ? $this->balance : 0) : $drclubs_fee;
//
//        $cart_object->add_fee('برداشت از اعتبار دکتر کلابز', -($drclubs_fee), false);


    }

    public
    function drclubs_after_calculate_total_in_checkout(WC_Cart $cart_object)
    {
        global $wpdb;
        global $woocommerce;
        if (is_admin() && !defined('DOING_AJAX'))
            return;

        if (!is_checkout()) return;
//        if (!WC()->session->get('calculating', false)) {
//
//            $drclubs_fee = WC()->session->get(self::CUSTOM_FIELD_KEY);
//            $drclubs_fee = $drclubs_fee > $this->balance ? ($this->balance >= 0 ? $this->balance : 0) : $drclubs_fee;
//
//            foreach ($cart_object->get_fees() as $key => $fee) {
//                if ($fee->name == 'برداشت از اعتبار دکتر کلابز') {
//                    $cart_object->remove_cart_item($key);
//                    WC()->session->set('calculating', true);
//                    $ct = $cart_object->calculate_totals();
//                    if ($drclubs_fee > $ct)
//                        $drclubs_fee = $ct;
//                    WC()->session->set(self::CUSTOM_FIELD_KEY, $drclubs_fee);
////                    $cart_object->add_fee('برداشت از اعتبار دکتر کلابز', $drclubs_fee);
//                    $cart_object->calculate_totals();
//
//
//                }
//            }
////            $cart_object->add_fee('برداشت از اعتبار دکتر کلابز', $drclubs_fee);
//            WC()->session->set('calculating', false);
//
//
//        }

//        $drclubs_fee = WC()->session->get(self::CUSTOM_FIELD_KEY) ?: 0; // مقدار برداشته شده از کیف پول
//        $drclubs_fee = $drclubs_fee > $this->balance ? ($this->balance >= 0 ? $this->balance : 0) : $drclubs_fee;
//
//        foreach ($cart_object->get_fees() as $key => $fee) {
//
//            if ($fee->name == 'برداشت از اعتبار دکتر کلابز') {
//                mylog($fee);
//                if ($fee->total < $fee->amount) {
//                    WC()->session->set(self::CUSTOM_FIELD_KEY, $fee->total);
//
//                }
//
//            }
//
//        }

//
//        $cart_object->add_fee('برداشت از اعتبار دکتر کلابز', -($drclubs_fee), false);


//
//        $cart_object->set_total($cart_object->get_total(null) - 5000);

//        foreach ($cart_object->get_cart() as $cart_item) {
//
//            $wc_product = $cart_item['data'];
//            // My custom field which is returning the additional cost
//            $pickup_price = $cart_item['drclubs_customer_credit'];
//
//            $product_price = method_exists($wc_product, 'get_price') ? floatval($wc_product->get_price()) : floatval($wc_product->price);
//            $new_price = $product_price + $pickup_price;
//            method_exists($wc_product, 'set_price') ? $wc_product->set_price($new_price) : $wc_product->price = $new_price;
//        }
    }

    function drclubs_checkout_field_update_order_meta($order_id)

    {


//        var_dump(self::CUSTOM_FIELD_KEY);
//        die;
//        if (!empty($_POST[self::CUSTOM_FIELD_KEY])) {
//
//            update_post_meta($order_id, self::CUSTOM_FIELD_KEY, sanitize_text_field($_POST[self::CUSTOM_FIELD_KEY]));
//
//        }


    }

    function drclubs_checkout_field_validation()

    {
        $consumed_balance = isset($_POST[self::CUSTOM_FIELD_KEY]) && is_numeric($_POST[self::CUSTOM_FIELD_KEY]) ? $_POST[self::CUSTOM_FIELD_KEY] : 0;

        myLog('before checkout balance:' . $this->balance);
        $this->balance = $this->userController->drclubs_get_user_info('balance_refresh');
        $this->balance = $this->balance ? $this->balance : 0;
        myLog('after checkout balance:' . $this->balance);

        if ($consumed_balance > $this->balance) {
//            if (isset(WC()->session) && WC()->session->get(self::CUSTOM_FIELD_KEY)) WC()->session->set(self::CUSTOM_FIELD_KEY, 0);
            wc_add_notice(__("مقدار مصرفی از کیف پول دکتر کلابز بیشتر از  $this->balance است"), 'error');

        }
    }

    function drclubs_checkout_field(WC_Checkout $checkout)

    {
        if (isset($this->setting_options['user_can_use_balance']) && !$this->setting_options['user_can_use_balance']) return;

        $dc_balance = $this->balance;
        $login_button = '<a style="  text-decoration: none; " href="' . wp_login_url() . '">ابتدا وارد شوید یا ثبت نام کنید</a>';
        $user_id = get_current_user_id();
        $drclubs_register_button = '<button  data-url="' . admin_url('admin-ajax.php') . '" data-action="drclubs_register_user" data-nonce="' . wp_create_nonce("drclubs-register-user-${user_id}-nonce") . '"   data-user_id="' . $user_id . '"  id="drclubs-create-register-form" class="w-100 button-primary color "   value="ساخت کیف پول"  >ساخت کیف پول</button> ';

        echo '<div style="font-family: Tanha,serif; border-radius: 4px;box-shadow: 0 1px 1px rgba(0, 0, 0, 0.04);padding: 1rem;background-color: #eee" class="text-right    " id="drclubs_checkout_field">
<h5 style="font-family: Tanha,serif;display: inline-block">' . __('کیف پول دکتر کلابز') . '</h5>
 <img class="" height="24px" width="24px"  src="' . $this->plugin_url . 'assets/img/logo.png' . '"  alt="" />';

        woocommerce_form_field(self::CUSTOM_FIELD_KEY, array(

            'type' => 'number',
            'required' => false,
            'class' => [

                'my-field-class form-row-wide widefat'

            ],

            'label' => $dc_balance >= 0 ? (' اعتبار : ' . '<strong>' . ($dc_balance - ($checkout->get_value(self::CUSTOM_FIELD_KEY) ?: 0)) . '</strong>' . ' (' . get_woocommerce_currency_symbol() . ')') : (is_user_logged_in() ? $drclubs_register_button : $login_button),

            'placeholder' => __('برداشت از کیف پول'),


        ),
//            WC()->session->get(self::CUSTOM_FIELD_KEY)
            $checkout->get_value(self::CUSTOM_FIELD_KEY) ?: WC()->session->get(self::CUSTOM_FIELD_KEY, 0)

        );
        echo '<input id="drclubs_checkout_first_balance" type="hidden" value="' . $dc_balance . '" />';
        echo '<button id="drclubs-update-checkout-ui" type="button"
         
                class="button   w-100"  >اعمال
              </button>';
        echo '<small>' . 'با فعالسازی این کیف پول، درصدی از خرید شما بعنوان اعتبار برای خریدهای بعدی قابل استفاده خواهد بود' . '</small>';
        echo '</div>';

    }

    static function woocommerce_not_activated()
    {
        $plugin_path = trailingslashit(WP_PLUGIN_DIR) . 'woocommerce/woocommerce.php';

        return
            (!in_array($plugin_path, wp_get_active_and_valid_plugins())
                && !in_array($plugin_path, self::wp_get_active_network_plugins()));

    }

    static function woocommerce_not_activated_admin_notice()
    {
        add_action('admin_notices', function () {

            $woocommerceLink = admin_url() . 'plugin-install.php?tab=plugin-information&plugin=woocommerce&TB_iframe=true&width=600&height=550';
            global $pagenow;
            if (is_admin()/*  $pagenow == 'options-general.php'*/) {
                echo '<div class="notice notice-warning is-dismissible text-right">
             <p class="font-bold">جهت نمایش کیف پول دکتر کلابز در صفحه خرید کاربر، نیاز به فعال سازی پلاگین <a href="' . $woocommerceLink . '">ووکامرس</a> می باشد</p>
         </div>';
            }
        });

    }

    static function wp_get_active_network_plugins()
    {
        $active_plugins = (array)get_site_option('active_sitewide_plugins', array());
        if (empty($active_plugins)) {
            return array();
        }

        $plugins = array();
        $active_plugins = array_keys($active_plugins);
        sort($active_plugins);

        foreach ($active_plugins as $plugin) {
            if (!validate_file($plugin)                     // $plugin must validate as file.
                && '.php' === substr($plugin, -4)             // $plugin must end with '.php'.
                && file_exists(WP_PLUGIN_DIR . '/' . $plugin) // $plugin must exist.
            ) {
                $plugins[] = WP_PLUGIN_DIR . '/' . $plugin;
            }
        }

        return $plugins;
    }


}