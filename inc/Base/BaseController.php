<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;

use DrClubs\Helper;

class  BaseController
{

    const VERSION_CODE = 1;
    const VERSION_NAME = '1.0.0';
    const PLUGIN_LINK = 'https://wordpress.org/plugins/drclubs';
    const SUPPORT_PORTAL_LINK = 'https://aftabtabangroup.ir/login';

    public $plugin_url;
    public $plugin;
    public $managers;
    public $main_menu_title = 'دکتر کلابز';
    public $main_menu_page_slug = 'drclubs_plugin';

    public function __construct()
    {
        $this->plugin_path = plugin_dir_path(dirname(__FILE__, 2)); //2 folder up
        $this->plugin_url = plugin_dir_url(dirname(__FILE__, 2));
        $this->plugin = plugin_basename(dirname(__FILE__, 3)) . '/' . plugin_basename(dirname(__FILE__, 3)) . '.php';
        $this->managers = [
            'cpt_manager' => 'فعال/غیر فعال CPT',
            'taxonomy_manager' => 'فعال/غیر فعال Taxonomy',
            'media_widget' => 'فعال/غیر فعال Widget',
            'gallery_manager' => 'فعال/غیر فعال Gallery',
            'testimonial_cpt_manager' => 'فعال/غیر فعال Testimonial',
            'templates_manager' => 'فعال/غیر فعال Templates',
            'login_manager' => 'فعال/غیر فعال Login',
            'membership_manager' => 'فعال/غیر فعال Membership',
            'chat_manager' => 'فعال/غیر فعال Chat',
        ];
    }

    public static function updateExists()
    {
        return false;
    }

    public function activated($key)
    {
        $option = get_option(Helper::$pageSlug);
        return isset($option[$key]) ? $option[$key] : false;
    }

    static function get_assets_path()
    {
        return plugin_dir_url(dirname(__FILE__, 2)) . 'assets/';
    }

    public static function getTemplatePath()
    {
        return (dirname(__FILE__, 3) . '/templates/');
    }

    public static function includeLoader($type = 'require')
    {
        if ($type == 'require')
            require_once(dirname(__FILE__, 3) . '/templates/loader.html');
        else
            return
                '<div class="loader-container  "  >
                <div class="loader  "  style="width: 50px;" >
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
                </div>';
    }

}