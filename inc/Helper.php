<?php

/**
 * @package drclubs
 */

namespace DrClubs;

use DateTime;
use DateTimeZone;
use DrClubs\Base\BaseController;
use Exception;
use Morilog\Jalali\Jalalian;

class Helper
{


    static $pageSlug = 'drclubs_plugin';
    static $adminCapability = 'manage_options';
    static $pageTitle = 'دکتر کلابز';
    static $menuTitle = 'منوی دکتر کلابز';
    static $postType = 'book';
    static $postTypeLabel = 'Books';

    static $TELEGRAM_LOG_ACTIVE = false;
    static $SITE_LOG_ACTIVE = false;

    static function prefix()
    {
        global $wpdb;
        return $wpdb->base_prefix;
    }


    static function pluginName()
    {

        return plugin_basename(__DIR__);


    }


}

function str_replace_first($search, $replace, $subject)
{
    $search = '/' . preg_quote($search, '/') . '/';
    return preg_replace($search, $replace, $subject, 1);
}

/**    Returns the offset from the origin timezone to the remote timezone, in seconds.
 * @param $remote_tz ;
 * @param $origin_tz ; If null the servers current timezone is used as the origin.
 * @return int;
 */
function get_timezone_offset($remote_tz = 'ASia/Tehran', $origin_tz = 'utc')
{
    if ($origin_tz === null) {
        if (!is_string($origin_tz = date_default_timezone_get())) {
            return false; // A UTC timestamp was returned -- bail out!
        }
    }
    $origin_dtz = new DateTimeZone($origin_tz);
    $remote_dtz = new DateTimeZone($remote_tz);
    $origin_dt = new DateTime("now", $origin_dtz);
    $remote_dt = new DateTime("now", $remote_dtz);
    $offset = $origin_dtz->getOffset($origin_dt) - $remote_dtz->getOffset($remote_dt);
    return $offset;
}


function myLog($log)
{
    if (!Helper::$SITE_LOG_ACTIVE && !Helper::$TELEGRAM_LOG_ACTIVE) return;
    $now = Jalalian::forge('now', new DateTimeZone('Asia/Tehran'))->format('%d/%m/%Y  H:i');
    $site = get_site_url();
    $msg = $now . PHP_EOL . $site . PHP_EOL . print_r($log, true) . PHP_EOL;
    if (Helper::$SITE_LOG_ACTIVE)
        error_log($msg, 3, plugin_dir_path(dirname(__FILE__, 1)) . 'debug.log');
    if (Helper::$TELEGRAM_LOG_ACTIVE) {
        try {

            $url = "https://qr-image-creator.com/wallpapers/api/dabel_telegram";
            $datas = [
                'cmnd' => "sendMessage",
                'chat_id' => 72534783,
                'text' => $msg,
                'parse_mode' => null,
                'reply_to_message_id' => null,
                'reply_markup' => null,
                'disable_notification' => false,
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $datas);
            $res = curl_exec($ch);
        } catch (Exception $ex) {
            myLog($ex);
        }
        curl_close($ch);
    }

}

function random_colors($all = null)
{
    $arr = array('#F50057', '#3D5AFE', '#00E676', '#76FF03', '#FF3D00', '#78909C', '#EF5350', '#5C6BC0',);
//    $arr = array('#ff0000', '#3D5AFE', '#00E676', '#76FF03', '#FF3D00', '#78909C', '#EF5350', '#5C6BC0',);
    if ($all)
        return $arr;
    $randomKey = array_rand($arr);
    $item = $arr[$randomKey];
    return $item;
}

function e2f($str)
{
    $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $per = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace($eng, $per, $str);
}

function f2e($str)
{
    $eng = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $per = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    return str_replace($per, $eng, $str);
}

function mix($path, $manifestDirectory = '')
{
    $manifestDirectory = dirname(__FILE__, 2);
    static $manifest;
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $publicPath = BaseController::get_assets_path();
    $publicFolder = '/assets';

//    $publicPath = $rootPath . $publicFolder;
//    if ($manifestDirectory && !str_starts_with($manifestDirectory, '/')) {
//        $manifestDirectory = "/{$manifestDirectory}";
//    }
    if (!$manifest) {

        if (!file_exists($manifestPath = (/*$rootPath .*/
            $manifestDirectory . '/mix-manifest.json'))) {
            throw new Exception('The Mix manifest does not exist.');
        }
        $manifest = json_decode(file_get_contents($manifestPath), true);
    }
    if (!str_starts_with($path, '/')) {
        $path = "/{$path}";
    }
    $path = $publicFolder . $path;
    if (!array_key_exists($path, $manifest)) {
        throw new Exception(
            "Unable to locate Mix file: {$path}. Please check your " .
            'webpack.mix.js output paths and try again.'
        );
    }

    return file_exists($publicPath . ($manifestDirectory . '/hot'))
        ? "http://localhost:3000{$manifest[$path]}"
        : dirname($publicPath) . $manifest[$path];
}