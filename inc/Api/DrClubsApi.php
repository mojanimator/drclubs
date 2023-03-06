<?php

namespace DrClubs\Api;

use DrClubs\Base\Activate;
use DrClubs\Base\BusinessController;
use DrClubs\Base\UserController;
use DrClubs\Base\WooCommerceController;

use DrClubs\Helper;
use function DrClubs\myLog;
use function DrClubs\str_replace_first;
use Morilog\Jalali\Jalalian;
use PHPMailer\PHPMailer\Exception;
use Tightenco\Collect\Support\Collection;

class DrClubsApi
{


    public $link;
    private $token;
    private $options;

    const   URL = 'https://my.drclubs.ir/API/';
    const GET_TOKEN = self::URL . 'Authenticate/Token';
    const GET_VERSION = self::URL . 'Business/version';
    const BUSINESS_LAST_TRANSACTIONS = self::URL . 'Business/LastTenTransaction';
    const CUSTOMER_LAST_TRANSACTIONS = self::URL . 'CustomerAccount/LastTenTransaction';
    const GET_Business = self::URL . 'Business';
    const CREATE_USER = self::URL . 'customer';
    const GET_USER_INFO = self::URL . 'CustomerAccount';
    const DELETE_USER = self::URL . 'customer';
    const BUY = self::URL . 'CustomerAccount/Buy';
    const ADD_SCORE = self::URL . 'CustomerAccount/AddScore';
    const SUB_SCORE = self::URL . 'CustomerAccount/SubtractScore';
    const ADD_BALANCE = self::URL . 'CustomerAccount/AddBalance';
    const SUB_BALANCE = self::URL . 'CustomerAccount/SubtractBalance';
    const GET_CUSTOMER_ACCOUNT = self::URL . 'CustomerAccount';

    public function __construct()
    {
        $this->setting_options = get_option(\DrClubs\Base\BusinessController::$admin_settings_option_group);
        $this->connect_options = get_option(\DrClubs\Base\BusinessController::$admin_connect_option_group);
        $this->token = isset($this->connect_options['token']) ? $this->connect_options['token'] : null;

    }

    public static function getVersion()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::GET_VERSION);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$res || $code != 200)
            return '1.0.0';
        return $res;
    }

    public function getLastTransactions($id = 'BUSINESS')
    {
        if (!$id) return [];
        if ($id == 'BUSINESS')
            $link = self::BUSINESS_LAST_TRANSACTIONS;
        else
            $link = self::CUSTOMER_LAST_TRANSACTIONS . "/$id";
        $res = $this->request($link);
        if ($res['status'] == 'success') return $res['data'];

        $this->refreshToken();

        $res = $this->request($link);

        if ($res['status'] == 'success') return $res['data'];
        return [];


    }

    public function getCustomerAccount($user_id = null)
    {
        if (!$user_id)
            $user_id = get_current_user_id();

        $res = get_user_meta($user_id, UserController::USER_META_KEY, true);
        if (!$res) return [];
        $res = json_decode($res);
        if (!isset ($res) || !isset($res->Id)) return [];

        $link = self::GET_CUSTOMER_ACCOUNT . "/$res->Id";

        $res = $this->request($link);
        if ($res['status'] == 'success') return $res['data'];

        $this->refreshToken();

        $res = $this->request($link);

        if ($res['status'] == 'success') return $res['data'];
        return [];


    }

    public function tokenIsValid()
    {
        //get version with token to check token is valid

        $this->token = $this->searchTokenInDB();

        $res = $this->request(self::GET_Business);

        if ($res['status'] != 'success')
            $this->token = null;

        return $this->token;


    }

    public function disconnect()
    {
        $this->token = null;

        unset($this->connect_options['token']);


        update_option(BusinessController::$admin_connect_option_group, $this->connect_options);
        return true;
    }

    function connect($username, $password)
    {


        if (!$username || !$password)
            return null;

        if ($this->tokenIsValid())
            return true;

        $link = self::GET_TOKEN;
//        $link = str_replace_first('*', $username, $link);
//        $link = str_replace_first('*', $password, $link);

        $res = $this->request($link, ['Username' => $username, 'Password' => $password], 'POST');


        if ($res['status'] == 'success')
            if (isset($res['data']->token)) {
                $this->token = $res['data']->token;

                $this->connect_options['token'] = $this->token;
                $this->connect_options['username'] = $username;
                $this->connect_options['password'] = $password;
                update_option(BusinessController::$admin_connect_option_group, $this->connect_options);
                return true;
            }
        return false;
    }


    public function getCustomerInfo($user_id, $type = null, $typeVal = null)
    {
        $user = null;

        if ($typeVal && $type == 'phone') {
            myLog("find user by  $typeVal :");
            $link = self::CREATE_USER . '/ByMobile' . "/$typeVal";
            $res = $this->request($link, ['cache' => true], 'GET');
            if ($res['status'] != 'success') {
                $this->refreshToken();
                //reconnect with new token
                $res = $this->request($link, ['cache' => true], 'GET');

            }

            if (isset($res['data']) && is_array($res['data']) && count($res['data']) > 0) {
                $user = $res['data'][0];
                $user = [
                    'Id' => isset($user->id) ? $user->id : '',
                    'Fname' => isset($user->fname) ? $user->fname : '',
                    'Lname' => isset($user->lname) ? $user->lname : '',
                    'NationalCode' => isset($user->nationalCode) ? $user->nationalCode : '',
                    'Gender' => isset($user->gender) ? $user->gender : '',
                    'BornDate' => isset($user->bornDate) ? str_replace('-', '/', explode('T', $user->bornDate)[0]) : '',
                    'PhoneNumber' => isset($user->phoneNumber) ? $user->phoneNumber : '',
                    'CardNumber' => isset($user->cardNumber) ? $user->cardNumber : '',
                    'CardIsCredit' => isset($user->cardIsCredit) ? $user->cardIsCredit : false,
                    'Enabled' => isset($user->enabled) ? $user->enabled : true,
                    'balance' => 0,
                    'purchaseVolume' => 0,
                    'purchaseCount' => 0,
                    'score' => 0,
                    'lastTransactions' => []
                ];

                update_user_meta($user_id, UserController::USER_META_KEY, json_encode($user, JSON_UNESCAPED_UNICODE));
                $user = (object)$user;
                myLog("drclubs for user $user_id updated!");

            } else {
                UserController::deleteUserInfo($user_id);
            }
        } else if ($typeVal && $type == 'current_user') {
            myLog('current user');
            $user = $typeVal;
        } else {
            $user = get_user_meta($user_id, UserController::USER_META_KEY, true);
            if ($user)
                $user = json_decode($user);

        }

        if (!$user) return null;
        if (!isset($user->Id)) return null;

        $link = self::GET_USER_INFO . "/$user->Id";

        $res = $this->request($link, [], 'GET');

        if ($res['status'] != 'success') {
            $this->refreshToken();
            //reconnect with new token
            $res = $this->request($link, [], 'GET');

        }
        if ($res['status'] != 'success') {
            myLog('get customer info failed:');
            myLog($res);
        }
        if (isset($res['message']) && str_contains($res['message'], 'مشتری یافت نشد')) {

            UserController::deleteUserInfo($user_id);
            return -1;
        }

        if ($res['status'] != 'success') return null;
        if (isset($res['data'])) {

            myLog($res['data']->customerId);
            $info = $res['data'];
            if (isset($info->score))
                $user->score = $info->score;
            if (isset($info->balance))
                $user->balance = $info->balance;
            if (isset($info->purchaseVolume))
                $user->purchaseVolume = $info->purchaseVolume;
            if (isset($info->purchaseCount))
                $user->purchaseCount = $info->purchaseCount;

            $tmp = [];
            $tmp2 = $this->getLastTransactions($user->Id);

            foreach ($tmp2 as $lastTransaction) {


                if (isset($lastTransaction->description))
                    unset($lastTransaction->description);
                $tmp[] = $lastTransaction;

            }
            $user->lastTransactions = $tmp;

            update_user_meta($user_id, UserController::USER_META_KEY, json_encode($user, JSON_UNESCAPED_UNICODE));


        }
        if ($typeVal && $type == 'phone') return $user;
        if ($type && isset($res['data']->$type))
            return $res['data']->$type;
        if (isset($res['data']))
            return $res['data'];
        return null;
    }

    public function registerUser($params)
    {

//        return ['status' => 'error', 'message' => $_SERVER['CONTENT_LENGTH']];
        myLog('start register:');
        myLog($params);
        if (!isset($params['cache']) && !current_user_can('manage_options') && (!isset($this->setting_options['user_can_register']) || $this->setting_options['user_can_register'] == false))
            return ['status' => 'error', 'message' => 'فقط ادمین می تواند کاربران را ثبت نام کند', 'code' => 400];
//        if (!$this->tokenIsValid())
//            return ['status' => 'error', 'message' => 'توکن معتبر نیست. لطفا از منوی اصلی دکمه اتصال را بزنید'];

        $link = self::CREATE_USER;

        $res = get_user_meta($params['user_id'], UserController::USER_META_KEY, true);
        if ($res)
            $res = json_decode($res);
        if (isset ($res) && isset($res->Id))
            return ['status' => 'error', 'message' => 'این کاربر از قبل در باشگاه مشتریان عضو شده است.', 'code' => 400];


        $res = $this->request($link, $params, 'POST', [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->token]);
        myLog('first try register:');

        if (!isset($res['data'])) {
//            $this->refreshToken();
            myLog('refresh token :');
            $this->refreshToken();
            $res = $this->request($link, $params, 'POST', [
                'Accept: application/json',
                'Authorization: Bearer ' . $this->token]);
            myLog('second try register :');
            if (!isset($res['data']->customerId))
                myLog($res);
        }
        $status = 'error';
        if (isset($res['message'])) {
            if (str_contains($res['message'], 'شماره تماس تکراری است')) {
                //get user that has this phone and update this user
                $info = $this->getCustomerInfo($params['user_id'], 'phone', $params['PhoneNumber']);

                myLog('find and refresh user:');
                myLog($info);
            }
        } elseif (isset($res['data']->customerId)) {
            myLog('register success! :');
            myLog($res);
            $status = 'success';

            update_user_meta($params['user_id'], UserController::USER_META_KEY, json_encode([
                'Id' => $res['data']->customerId,
                'Fname' => isset($params['Fname']) ? $params['Fname'] : '',
                'Lname' => isset($params['Lname']) ? $params['Lname'] : '',
                'NationalCode' => isset($params['NationalCode']) ? $params['NationalCode'] : '',
                'Gender' => isset($params['Gender']) ? $params['Gender'] : '',
                'BornDate' => isset($params['BornDate']) ? $params['BornDate'] : '',
                'PhoneNumber' => isset($params['PhoneNumber']) ? $params['PhoneNumber'] : '',
                'CardNumber' => isset($params['CardNumber']) ? $params['CardNumber'] : '',
                'CardIsCredit' => isset($params['CardIsCredit']) ? $params['CardIsCredit'] : false,
                'Enabled' => isset($params['Enabled']) ? $params['Enabled'] : true,
                'balance' => 0,
                'purchaseVolume' => 0,
                'purchaseCount' => 0,
                'score' => 0,
            ], JSON_UNESCAPED_UNICODE));

        }

        $this->cache_release_data('register', $params, $res['code']);

        return ['status' => $status, 'code' => $res['code'], 'message' => isset($res['message']->errors) ? $res['message']->errors : (isset($res['message']) ? $res['message'] : $res)];
    }

    private function request($link, $data = [], $method = 'GET', $headers = null)
    {

        if (!get_current_user_id() && !isset($data['cache']))
            return ['status' => 'error', 'message' => 'ابتدا وارد شوید یا ثبت نام کنید'];

        $ch = curl_init();
        $headers = $headers ?: array(
            'Accept: application/json',
            "Content-Type: application/json",
            'Authorization: Bearer ' . $this->searchTokenInDB()
        );
        if (count($data) == 1 && isset($data['cache'])) unset($data['cache']);
        if (count($data) > 0)
            $link = $link . '?' . http_build_query($data);

        curl_setopt($ch, CURLOPT_URL, "$link");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


//        curl_setopt($ch, CURLOPT_HEADER, 1);
//

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($method == 'POST') {
//            $data = json_encode($data);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        // Timeout in seconds
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);


        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$res)
            return ['status' => 'error', 'code' => 0, 'message' => $res];
//        myLog($res);
        $res = json_decode($res);

        if ($code != 200)
            return ['status' => 'error', 'code' => $code, 'message' => (isset($res->message) ? $res->message : (isset($res->errors) ? $res->errors : $res))];
        return ['status' => 'success', 'code' => $code, 'data' => $res];
    }

    function searchTokenInDB()
    {
        if ($this->token)
            return $this->token;
        else {
            $data = $this->connect_options;

            if (isset($data['token'])) {
                $this->token = $data['token'];
            }
            return $this->token;


        }
    }

    private function refreshToken($save = true)
    {
//کاربر عادی نمی تواند توکن رفرش کند
//        if (!isset($this->connect_options['token']) && !current_user_can('manage_options') && (!isset($this->setting_options['user_can_register']) || $this->setting_options['user_can_register'] == false))
//            return false;

        if (isset($this->connect_options['username']) && isset($this->connect_options['password'])) {


            $res = $this->request(self::GET_TOKEN, ['Username' => $this->connect_options['username'], 'Password' => $this->connect_options['password']], 'POST');
//            myLog($res);
            if ($res['status'] == 'success')
                if (isset($res['data']->token) && $save) {
                    $this->token = $res['data']->token;

                    $this->connect_options['token'] = $this->token;
                    update_option(BusinessController::$admin_connect_option_group, $this->connect_options);
                    return $this->token;
                } else
                    return $res['data']->token;

        }
//        myLog('false');
        return false;
    }

    public function getValidToken()
    {
        $token = $this->tokenIsValid();
        if (!$token)
            $this->refreshToken();


        return $this->token;
    }

    public function buy(\WC_Order $order, $drclubs_data)
    {
        myLog('buy process:');
        $drclubs_order_meta = $order->get_meta(WooCommerceController::ORDER_INFO_KEY, true);
        $drclubs_id = $drclubs_data['drclubs_id'];

        myLog("drclubs id: $drclubs_id");
        myLog("order meta...");
        myLog($drclubs_order_meta);
        if (!isset($drclubs_id) || !isset($drclubs_order_meta['consumed_credit']) || $drclubs_order_meta['consumed_credit'] < 0 || (isset($drclubs_order_meta['consumed_at']) && $drclubs_order_meta['consumed_at'] != null)) {
            myLog('اطلاعات خرید نامعتبر است و یا قبلا انجام شده است');
            return ['status' => 'error', 'message' => 'اطلاعات خرید نامعتبر است و یا قبلا انجام شده است'];
        }


        $link = self::BUY;

        if (isset($this->setting_options['user_can_use_balance']) && !$this->setting_options['user_can_use_balance'])
            $drclubs_consumed_credit = 0;
        else
            $drclubs_consumed_credit = WooCommerceController::toDrClubsCurrency($drclubs_order_meta['consumed_credit']);
        $extraData = json_encode(['order_id' => $order->get_id(), 'customer_id' => $drclubs_id,]);
        $total = WooCommerceController::toDrClubsCurrency($order->get_total());
        $data = ['order_id' => $order->get_id(), 'CustomerId' => $drclubs_id, 'Amount' => intval($total) + intval($drclubs_consumed_credit), 'Credit' => $drclubs_consumed_credit, 'Description' => $extraData];
        if (isset($drclubs_data['cache']))
            $data['cache'] = true;
        $res = $this->request($link, $data, 'POST', [
            'Accept: application/json',
            "Content-Type:  multipart/form-data",
            'Authorization: Bearer ' . $this->token]);

        if ($res['code'] != 200) {
            $this->refreshToken();

            $res = $this->request($link, $data, 'POST', [
                'Accept: application/json',
                "Content-Type:  multipart/form-data",
                'Authorization: Bearer ' . $this->token]);
        }

        myLog($data);
        myLog($res);
        //save transaction logs

        if ($res['code'] == 200) {
            $this->log_table(['user_id' => $order->get_user_id(), 'type' => 'buy', 'value' => intval($total)], 'INSERT');

            $drclubs_order_meta['consumed_at'] = current_time('timestamp');
            $drclubs_order_meta['transaction_id'] = $res['data']->transactionId;


            foreach ($drclubs_order_meta['products_reward'] as $unit => $reward) {

                $data = [
                    'user_id' => $order->get_user_id(),
                    'Amount' => $reward,
                    'type' => 'order_' . $order->get_id() . '_reward',
                    'CustomerId' => $drclubs_id,
                    'Description' => json_encode(['user_id' => $order->get_user_id(), 'type' => 'buy_products_reward']),
                ];
                if (isset($drclubs_data['cache']))
                    $data['cache'] = true;
                $this->setReward($data, $unit);
            }
            $this->getCustomerInfo($order->get_user_id());


            $order->update_meta_data(WooCommerceController::ORDER_INFO_KEY, $drclubs_order_meta);
            update_post_meta($order->get_id(), WooCommerceController::ORDER_INFO_KEY, $drclubs_order_meta);

            if (isset(WC()->session) && WC()->session->get(WooCommerceController::CUSTOM_FIELD_KEY)) WC()->session->set(WooCommerceController::CUSTOM_FIELD_KEY, 0);

        }

        $this->cache_release_data('buy', ['order_id' => $order->get_id(), 'CustomerId' => $drclubs_id,], $res['code']);
        return $res;
    }

    private function getOneTimeValidToken()
    {
        return $this->refreshToken(false);
    }

    public function log_data($key1, $key2, $cmnd = 'INC')
    {
        $option = get_option(BusinessController::$logs_option_group, BusinessController::LOGS_DEFAULT);

        if ($cmnd == 'INC') {
            if (isset($option[$key1][$key2]) && is_numeric($option[$key1][$key2]))
                $option[$key1][$key2]++;
            else
                $option[$key1][$key2] = 0;
            update_option(BusinessController::$logs_option_group, $option);

        } elseif ($cmnd == 'GET') {
            if ($key1 && $key2 && isset($option[$key1][$key2]))
                return $option[$key1][$key2];
            elseif ($key1 && isset($option[$key1][$key2]))
                return $option[$key1];
            else return $option;
        }
    }

    public function log_table($query, $cmnd = 'INSERT')
    {
        Activate::create_drclubs_logs_database_table();
        $type = isset($query['type']) ? $query['type'] : null;
        $user_id = isset($query['user_id']) ? $query['user_id'] : null;
        $value = isset($query['value']) ? $query['value'] : null;
        $from = isset($query['from']) ? $query['from'] : null;
        $to = isset($query['to']) ? $query['to'] : null;
        $order_by = isset($query['to']) ? $query['to'] : null;
        $page = isset($query['page']) ? $query['page'] : null;
        $order_dir = isset($query['order_dir']) ? $query['order_dir'] : null;
        $paginate = isset($query['paginate']) ? $query['paginate'] : null;
        global $wpdb;
        $table = $wpdb->prefix . BusinessController::LOGS_TABLE;

        if ($cmnd == 'INSERT')
            $wpdb->insert($table, array(
                'user_id' => $user_id,
                'type' => $type,
                'value' => $value,
            ));
        if ($cmnd == 'GET') {
            $query = "SELECT id,user_id,type,value, UNIX_TIMESTAMP(created_at) AS created_at  FROM $table ";

            if ($user_id)
                $query = $wpdb->prepare($query . "WHERE `user_id` = %d ", $user_id);
            if ($type)
                $query = $wpdb->prepare($query . ($user_id ? 'AND' : 'WHERE') . " `type` = %s ", $type);
            if ($from && $to)
                $query = $wpdb->prepare($query . ($user_id || $type ? 'AND' : 'WHERE') . " UNIX_TIMESTAMP(created_at) BETWEEN %d AND %d ", $from->getTimestamp(), $to->addDays(1)->getTimestamp());
            elseif ($from)
                $query = $wpdb->prepare($query . ($user_id || $type ? 'AND' : 'WHERE') . " UNIX_TIMESTAMP(created_at) >= %d ", $from->getTimestamp());
            elseif ($to)
                $query = $wpdb->prepare($query . ($user_id || $type || $from ? 'AND' : 'WHERE') . " UNIX_TIMESTAMP(created_at) <= %d ", $to->addDays(1)->getTimestamp());

            if ($order_by)
                $query = $wpdb->prepare($query . " ORDER BY %s %s ", $order_by, $order_dir);

            if ($page)
                $query = $wpdb->prepare($query . " Limit  %s , %s ", $page, $paginate);

//        if ($group_by)

            $res = $wpdb->get_results($query);
//            if (!$res) return [];
//

            return $res;

        }
    }

    public
    function cache_release_data($key, $data, $code)
    {

        $option = get_option(BusinessController::$cache_option_group, BusinessController::DEFAULT_CACHES);
        if (!isset($option[$key])) {
            $option = BusinessController::DEFAULT_CACHES;
            $option[$key] = [];
        }
        $cache_items = $option[$key]; //key=register  ,buy

        //delete from cache if server response

        if ($code == 200 || $code == 400) { //release order or customer from cache
            foreach ($cache_items as $idx => $item) {
                if ((isset($data['user_id']) && $idx == $data['user_id']) || (isset($data['order_id']) && $idx == $data['order_id'])) {
                    unset($cache_items[$idx]);
                    $option[$key] = $cache_items;
                    update_option(BusinessController::$cache_option_group, $option);
                }
            }
            if ($code == 200)
                $this->log_data($key, 'success', 'INC');
        } //add to cache if server failed (not user mistakes[code 400])

        else if ($code != 400) {

            $idx = isset($data['user_id']) ? $data['user_id'] : (isset($data['order_id']) ? $data['order_id'] : null);
            if (!$idx) return;
            $cache_items[$idx] = json_encode($data, JSON_UNESCAPED_UNICODE);
            $option[$key] = $cache_items;
            update_option(BusinessController::$cache_option_group, $option);
            $this->log_data($key, 'fail', 'INC');

        }

    }

    public
    function deleteUser($Id)
    {
        $link = self::DELETE_USER . $Id;
        return $this->request($link, [], 'DELETE');
    }


//score or balance
    public
    function setReward($data, $unit = 'امتیاز')
    {
//        Helper::$MY_LOG_ACTIVE = true;
        myLog("set Reward: $unit");
        myLog($data);
        if (!in_array($unit, BusinessController::DEFAULT_OPTIONS['units']['available'])) return true;

        if ($data['Amount'] == 0) {
            $this->log_table(array(
                'user_id' => $data['user_id'],
                'type' => $data['type'],
                'value' => isset($data['value']) ? $data['value'] : $data['Amount'],
            ));
            return true;
        }
        $link = '';
        if ($unit == 'امتیاز')
            if ($data['Amount'] > 0)
                $link = self::ADD_SCORE;
            else
                $link = self::SUB_SCORE;
        if ($unit == 'ریال')
            if ($data['Amount'] > 0)
                $link = self::ADD_BALANCE;
            else
                $link = self::SUB_BALANCE;


        $res = $this->request($link, $data, 'POST', [
            'Accept: application/json',
            'Authorization: Bearer ' . $this->token]);

        if ($res['status'] != 'success') {
            $this->refreshToken();
            $res = $this->request($link, $data, 'POST', [
                'Accept: application/json',
                'Authorization: Bearer ' . $this->token]);
        }

        myLog($res);
//        Helper::$MY_LOG_ACTIVE = false;

        if ($res['status'] == 'success') {


            $this->log_table(array(
                'user_id' => $data['user_id'],
                'type' => $data['type'],
                'value' => isset($data['value']) ? $data['value'] : $data['Amount'],
            ));

        }
        return $res['status'] == 'success';
    }
}

