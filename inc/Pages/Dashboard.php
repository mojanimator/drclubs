<?php
/**
 * @package drclubs
 */

namespace DrClubs\Pages;

use DrClubs\Api\Callbacks\AdminCallbacks;
use DrClubs\Api\Callbacks\DashboardCallbacks;
use DrClubs\Api\Callbacks\ManagerCallbacks;
use DrClubs\Base\BusinessController;
use DrClubs\Helper;
use DrClubs\Api\SettingsApi;
use DrClubs\Base\BaseController;

class Dashboard extends BaseController
{
    public $callbacks;
    public $callbacks_mngr;
    public $settings;
    public $pages = [];
    public $businessController;

//    public $subPages = [];


    public function register()
    {
//        add_action('admin_menu', [$this, 'add_admin_pages']);
        $this->settings = new SettingsApi();
        $this->callbacks_mngr = new ManagerCallbacks();
        $this->callbacks = new DashboardCallbacks();


        $this->setPages();

//        $this->businessController = new BusinessController();
//        $this->businessController->register();
//        $this->setSubPages();
//        $this->setSettings();
//        $this->setSections();
//        $this->setFields();


        $this->settings->addPages($this->pages)->withSubPage('داشبورد')/*->addSubPages($this->subPages)*/
        ->register();
    }


    public function admin_index()
    {
        require_once $this->plugin_path . 'templates/dashboard.php';

    }

    private function setPages()
    {
        $this->pages = [
            [
                'page_title' => $this->main_menu_title,
                'menu_title' => $this->main_menu_title,
                'capability' => 'manage_options',
                'menu_slug' => BusinessController::$main_settings_page_slug,
                'callback' => [$this->callbacks, 'adminDashboard'],
                'icon_url' => /*'dashicons-store'*/
                    $this->plugin_url . 'assets/img/logo.svg',
                'position' => 110,
            ]
        ];
    }

//    private function setSubPages()
//    {
//        $this->subPages = [
//            [
//                'parent_slug' => Helper::$pageSlug,
//                'page_title' => 'انواع داده',
//                'menu_title' => 'منوی انواع داده',
//                'capability' => Helper::$adminCapability,
//                'menu_slug' => 'drclubs_cpt',
//                'callback' => function () {
//                    echo '<h1>مدیریت داده </h1>';
//                },
//
//            ], [
//                'parent_slug' => Helper::$menuTitle,
//                'page_title' => 'ویجت ها',
//                'menu_title' => 'منوی ویجت ها',
//                'capability' => Helper::$adminCapability,
//                'menu_slug' => 'drclubs_widgets',
//                'callback' => function () {
//                    echo '<h1>مدیریت ویجت ها </h1>';
//                },
//
//            ],
//        ];
//    }

    public function setSettings()
    {
        $args = [
            [
                'option_group' => 'drclubs_plugin_settings',
                'option_name' => Helper::$pageSlug,
                'callback' => [$this->callbacks_mngr, 'checkboxSanitize']
            ]
        ];
//        foreach ($this->managers as $id => $title) {
//            $args[] = [
//                'option_group' => 'drclubs_plugin_settings',
//                'option_name' => $id,
//                'callback' => [$this->callbacks_mngr, 'checkboxSanitize']
//            ];
//        }


        $this->settings->setSettings($args);
    }

    public function setSections()
    {
        $args = [
            [
                'id' => 'drclubs_admin_index',
                'title' => 'بخش تنظیمات',
                'callback' => [$this->callbacks_mngr, 'adminSectionManager'],
                'page' => Helper::$pageSlug
            ]
        ];

        $this->settings->setSections($args);
    }

    public function setFields()
    {
        $args = [];
        foreach ($this->managers as $id => $title) {
            $args[] = [
                'id' => $id, //must be option_name of settings
                'title' => $title,
                'callback' => [$this->callbacks_mngr, 'checkboxField'],
                'page' => Helper::$pageSlug,
                'section' => 'drclubs_admin_index',
                'args' => [
                    'option_name' => Helper::$pageSlug,
                    'label_for' => $id,
                    'classes' => 'ui-toggle',
                ]
            ];
        }


        $this->settings->setFields($args);
    }
}