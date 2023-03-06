<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;

use DrClubs\Api\DrClubsApi;
use DrClubs\Helper;
use function DrClubs\myLog;

class  UserController
{
    public $dcAPI;
    private $token;
    public $user;
    const USER_META_KEY = '_drclubs_user_data';


    public function __construct()
    {
        $this->dcAPI = new DrClubsApi();
        $this->token = $this->dcAPI->searchTokenInDB();

//        $this->user = get_user_meta(get_current_user_id(), self::USER_META_KEY, true);
//
//        if ($this->user)
//            $this->user = json_decode($this->user);
    }

    public static function deleteUserInfo($user_id)
    {
        myLog('مشتری یافت نشد: پاک کردن...');
        delete_user_meta($user_id, UserController::USER_META_KEY);
        $args = array(
            'numberposts' => -1,
            'post_type' => 'any',
            'fields' => 'ids',
            'author' => $user_id
        );
        $post_ids = get_posts($args);

        if (!empty($post_ids)) {
            global $wpdb;
            foreach ($post_ids as $id)
                $wpdb->delete($wpdb->postmeta, array('post_id' => $id, 'meta_key' => WooCommerceController::ORDER_INFO_KEY));
        }
    }

    public function register()
    {


        //add drclubs_id and phone to users
        add_action('manage_users_columns', [$this, 'modify_user_columns']);
//        add_action('admin_head', 'custom_admin_css');
        add_action('manage_users_custom_column', [$this, 'user_drclubs_id_column_content'], 10, 3);

        add_action('wp_ajax_drclubs_register_user', [$this, 'drclubs_register_user']);

        add_action('wp_ajax_drclubs_customer_actions', [$this, 'drclubs_customer_actions']);
    }

    function modify_user_columns($column_headers)
    {
        $column_headers['drclubs_id'] = 'شناسه دکتر کلابز';
//        $column_headers['drclubs_phone'] = 'شماره تماس';
        return $column_headers;
    }

//    function custom_admin_css()
//    {
//        echo '<style>
//    .column-custom_field {width: 8%}
//    </style>';
//    }

    function user_drclubs_id_column_content($value, $column_name, $user_id)
    {
        if (!current_user_can('edit_user')) {
            return false;
        }

        if ('drclubs_id' == $column_name) {
            $user = get_user_meta($user_id, self::USER_META_KEY, true);
            if ($user)
                $user = json_decode($user);
            return $this->create_drclubs_custom_cell(isset($user->Id) ? $user->Id : '', $user_id);
        }


        return $value;
    }

    private function create_drclubs_custom_cell($id, $user_id)
    {
        if ($id) return "<italic><small>$id</small></italic>";
        else {
            return '
<input onclick="' . ($this->token ? "createRegisterForm(this)" : "alert('ابتدا از منوی دکتر کلابز دکمه اتصال را بزنید')") . '"  id="drclubs-show-register-form-' . $user_id . '" data-url="' . admin_url('admin-ajax.php') . '" data-action="drclubs_register_user" data-nonce="' . wp_create_nonce("drclubs-register-user-${user_id}-nonce") . '"   data-user_id="' . $user_id . '" type="button" class="button widefat" value="ثبت نام" />';

        }

    }

    public function drclubs_customer_actions()
    {
        $user_id = $_POST['user_id'];

        if (!wp_verify_nonce($_POST["drclubs-customer-$user_id-actions-nonce"], "drclubs_customer_actions"))
            wp_send_json(['status' => 'error', 'message' => 'کد امنیتی نامعتبر است']);

        check_ajax_referer("drclubs_customer_actions", "drclubs-customer-$user_id-actions-nonce");

        $option = get_user_meta($user_id, UserController::USER_META_KEY, true);
        if (!$option || !is_string($option)) wp_send_json('کاربر یافت نشد', 400);
        $option = json_decode($option);

        if (!isset($option->Id)) wp_send_json('کاربر یافت نشد', 400);

        if ($_POST['command'] == 'حذف') {
            $res = $this->dcAPI->deleteUser($option->Id);
            if ($res['code'] == 200)
                update_user_meta($user_id, UserController::USER_META_KEY, null);
        }

//


        wp_send_json(isset($res['message']) ? $res['message'] : (isset($res['data']) ? $res['data'] : $res), $res['code']);
    }

    public function drclubs_register_user($data = null)

    {
        if ($data != null)
            $_POST = $data;

        if (!isset($_POST['cache']) && !wp_verify_nonce($_POST['nonce'], "drclubs-register-user-" . $_POST['user_id'] . "-nonce"))
            if (!$data)
                wp_send_json(['status' => 'error', 'message' => 'کد امنیتی نامعتبر است']);
            else
                return ['status' => 'error', 'message' => 'کد امنیتی نامعتبر است'];

        if (!$data)
            check_ajax_referer("drclubs-register-user-" . $_POST['user_id'] . "-nonce", 'nonce');

        unset($_POST['nonce']);
        unset($_POST['action']);


        $res = $this->dcAPI->registerUser($_POST);

        $code = $res['status'] == 'success' ? 200 : 400;

        if (!$data)
            wp_send_json($res, $code);
        else return $res;
    }


    public function drclubs_get_user_info($what = 'refresh', $user_id = null, $user = null)
    {
        $current_user_id = 0;

        if ($user_id == null) {
            $user_id = get_current_user_id();
            $current_user_id = $user_id;

        }

        if ($user)
            $this->user = $user;
        else {

            $this->user = get_user_meta($user_id, self::USER_META_KEY, true);
            if ($this->user)
                $this->user = json_decode($this->user);

        }

        if ($what == 'balance')
            return isset($this->user->balance) ? WooCommerceController::toSiteCurrency($this->user->balance) : 0;
        if (!isset($this->user->Id))
            return null;

        if ($what == 'all') {
            return (object)[
                'balance' => isset($this->user->balance) ? $this->user->balance : null,
                'score' => isset($this->user->score) ? $this->user->score : null,
                'purchaseVolume' => isset($this->user->purchaseVolume) ? $this->user->purchaseVolume : null,
                'purchaseCount' => isset($this->user->purchaseCount) ? $this->user->purchaseCount : null,
            ];

        }

        $info = $this->dcAPI->getCustomerInfo($user_id, $current_user_id == $user_id ? 'current_user' : null, $this->user);

        //default balance is rial (IRR)
        if ($what == 'balance_refresh') {
            myLog($this->user);
            return isset($info->balance) ? WooCommerceController::toSiteCurrency($info->balance) : 0;
        }
        return $info;
    }

    public
    static function uuid()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    public function buy(\WC_Order $order, $drclubs_user_meta = null)
    {
        if (!$drclubs_user_meta)
            $drclubs_user_meta = get_user_meta($order->get_user_id(), self::USER_META_KEY, true);
        if ($drclubs_user_meta)
            $drclubs_user_meta = json_decode($drclubs_user_meta);

        $drclubs_id = isset($drclubs_user_meta->Id) ? $drclubs_user_meta->Id : null;

        if (!$drclubs_id) {
            // try register user again
            //register new users with phone and name after create order
            try {
                myLog("register before buy...");


                $user = $order->get_user();
//            myLog($order->get_base_data());
//            myLog($order);
//            mylog($order->get_meta());
//            myLog($user);
                if ($user)
                    $phone = BusinessController::toDrClubsPhone($user->user_login);

                if (!$order->get_billing_phone() && $phone)
                    $order->set_billing_phone($phone);
                if ($order->get_billing_phone() && !$phone)
                    $phone = BusinessController::toDrClubsPhone($order->get_billing_phone());
                if ($order->get_billing_mobile() && !$phone)
                    $phone = BusinessController::toDrClubsPhone($order->get_billing_mobile());

                myLog("  phone is: $phone");
                $user_id = $order->get_user_id();
                mylog("order user id: $user_id");
                //check maybe user registered before and not login
                if (!is_numeric($user_id) || $user_id <= 0) {
                    $tmp = get_user_by('login', $order->get_billing_phone());
                    $tmp = $tmp ? $tmp : get_user_by('login', $order->get_billing_mobile());
                    $tmp = $tmp ? $tmp : get_user_by('login', $phone);
                    $beforeUser = $tmp ? $tmp : get_user_by('email', $order->get_billing_email());
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
                $info = $this->dcAPI->getCustomerInfo($user_id);
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

                $res = $this->drclubs_register_user($data);
                mylog('register result');
                mylog($res);

                if (isset($res['message']) && is_string($res['message'])) {
                    if (str_contains($res['message'], 'شماره تماس تکراری است')) {
                        //get user that has this phone and update this user
                        $res = $this->dcAPI->getCustomerInfo($user_id, 'phone', $phone);
                    }
                }

                mylog('$res');
                mylog($res);
                if (isset($res['data']))
                    $drclubs_id = $res['data']->customerId;
                if (isset($res->Id))
                    $drclubs_id = $res->Id;
                mylog($drclubs_id);
            } catch (\Exception $e) {
                mylog($e->getTraceAsString());
            }

        }

        $res = $this->dcAPI->buy($order, ['drclubs_id' => $drclubs_id]);

//        if (isset($res['data']->transactionId)) {
//            $drclubs_order_meta['consumed_at'] = current_time('Y/m/d H:i:s');

//        }
        return $res;
    }

    public static function user($user_id = 0)
    {
        if (!$user_id)
            $user_id = get_current_user_id();

        if ($user_id == 0)
            return null;
        $data = get_user_meta($user_id, self::USER_META_KEY, true);

        if ($data) {

            return json_decode($data);
        }
        return null;
    }
}