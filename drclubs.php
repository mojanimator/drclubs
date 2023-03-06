<?php
/**
 * @package drclubs
 */

/*
 * Plugin Name: دکتر کلابز (افزونه باشگاه مشتریان)
 * Plugin URI: http://drclubs.ir
 * Description: راه اندازی باشگاه مشتریان توسط دکتر کلابز برای فروشگاه شما
 * Version: 1.0.0
 * Author: @develowper
 * Author URI: https://instagram.com/develowper
 * License: GPL v2
 * Text Domain: دکتر کلابز
 */

defined('ABSPATH') or die('Hi !');


if (file_exists(dirname(__FILE__) . '/vendor/autoload.php')) {
    require_once dirname(__FILE__) . '/vendor/autoload.php';
}

register_activation_hook(__FILE__, function () {
    DrClubs\Base\Activate::activate();
});
register_deactivation_hook(__FILE__, function () {
    DrClubs\Base\Deactivate::deactivate();
});

if (class_exists('DrClubs\\Init')) {

//    if (!\DrClubs\Base\WooCommerceController::woocommerce_not_activated())

//        add_action('woocommerce_init', function () {

    DrClubs\Init::register_services();
//        });
}


use DrClubs\Helper;
use DrClubs\Pages\Admin;

if (!class_exists('DrClubs')) {


    class DrClubs
    {
        private $pluginPath;

        public function __construct()
        {

            add_action('init', [$this, 'custom_post_type']);
            $this->pluginPath = plugin_basename(__FILE__);
        }


        function custom_post_type()
        {
            register_post_type(Helper::$postType, ['public' => true, 'label' => Helper::$postTypeLabel]);
        }


        function register()
        {


        }


    }


}