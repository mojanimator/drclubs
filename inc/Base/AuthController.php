<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;

use DrClubs\Api\Callbacks\AdminCallbacks;
use DrClubs\Api\Callbacks\CptCallbacks;
use DrClubs\Api\Callbacks\TaxCallbacks;
use DrClubs\Api\Callbacks\TestimonialCallbacks;
use  DrClubs\Base\BaseController;
use  DrClubs\Api\SettingsApi;
use DrClubs\Helper;

class  AuthController extends BaseController
{

    public static $option_name = 'drclubs_plugin_auth';


    public function register()
    {


        if (!$this->activated('login_manager')) return;
//
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
        add_action('wp_head', [$this, 'add_auth_template']);
        add_action('wp_ajax_nopriv_drclubs_login', [$this, 'login']);
    }

    public function enqueue()
    {
        if (is_user_logged_in()) return;
        wp_enqueue_style('authStyle', $this->plugin_url . 'assets/css/auth.css');
        wp_enqueue_script('authScript', $this->plugin_url . 'assets/js/auth.js');

    }

    public function add_auth_template()
    {
        if (is_user_logged_in()) return;

        $file = $this->plugin_path . 'templates/auth.php';
        if (file_exists($file)) {
            load_template($file, true);
        }
    }

    public function login()
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


}