<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;

use DrClubs\Api\Callbacks\AdminCallbacks;
use DrClubs\Api\Callbacks\CptCallbacks;
use DrClubs\Api\Callbacks\DashboardCallbacks;
use  DrClubs\Base\BaseController;
use  DrClubs\Api\SettingsApi;
use DrClubs\Helper;

class  CustomPostTypeController extends BaseController
{
    public $settings;
    public $subPages = [];
    public $callbacks;
    public $cpt_callbacks;
    public $customPostTypes = [];
    public static $option_name = 'drclubs_plugin_cpt';

    public function register()
    {


        if (!$this->activated('cpt_manager')) return;

        $this->settings = new SettingsApi();
        $this->callbacks = new DashboardCallbacks();
        $this->cpt_callbacks = new CptCallbacks();
        $this->setSubPages();

        $this->setSettings();
        $this->setSections();
        $this->setFields();

        $this->settings->addSubPages($this->subPages)->register();

        $this->storeCustomPostTypes();

        if (!empty($this->customPostTypes))
            add_action('init', [$this, 'registerCustomPostType']);
    }

    public function setSettings()
    {
        $args = [
            [
                'option_group' => 'drclubs_plugin_cpt_settings',
                'option_name' => self::$option_name,
                'callback' => [$this->cpt_callbacks, 'cptSanitize']
            ]
        ];


        $this->settings->setSettings($args);
    }

    public function setSections()
    {
        $args = [
            [
                'id' => 'drclubs_cpt_index',
                'title' => 'بخش تنظیمات نوع داده',
                'callback' => [$this->cpt_callbacks, 'cptSectionManager'],
                'page' => self::$option_name   //same as option_name
            ]
        ];

        $this->settings->setSections($args);
    }

    public function setFields()
    {
        $args = [

            [
                'id' => 'post_type', //must be option_name of settings
                'title' => 'شناسه نوع داده',
                'callback' => [$this->cpt_callbacks, 'textField'],
                'page' => self::$option_name,
                'section' => 'drclubs_cpt_index',
                'args' => [
                    'option_name' => self::$option_name,
                    'label_for' => 'post_type',
                    'placeholder' => '...',

                ]
            ],
            [
                'id' => 'singular_name', //must be option_name of settings
                'title' => 'نام',
                'callback' => [$this->cpt_callbacks, 'textField'],
                'page' => self::$option_name,
                'section' => 'drclubs_cpt_index',
                'args' => [
                    'option_name' => self::$option_name,
                    'label_for' => 'singular_name',
                    'placeholder' => '...',

                ]
            ],
            [
                'id' => 'plural_name', //must be option_name of settings
                'title' => ' نام جمعی',
                'callback' => [$this->cpt_callbacks, 'textField'],
                'page' => self::$option_name,
                'section' => 'drclubs_cpt_index',
                'args' => [
                    'option_name' => self::$option_name,
                    'label_for' => 'plural_name',
                    'placeholder' => '...',

                ]
            ],
            [
                'id' => 'public', //must be option_name of settings
                'title' => 'عمومی',
                'callback' => [$this->cpt_callbacks, 'checkboxField'],
                'page' => self::$option_name,
                'section' => 'drclubs_cpt_index',
                'args' => [
                    'option_name' => self::$option_name,
                    'label_for' => 'public',
                    'classes' => 'ui-toggle',
                    'placeholder' => '...',
                ]
            ],
            [
                'id' => 'has_archive', //must be option_name of settings
                'title' => 'آرشیو',
                'callback' => [$this->cpt_callbacks, 'checkboxField'],
                'page' => self::$option_name,
                'section' => 'drclubs_cpt_index',
                'args' => [
                    'option_name' => self::$option_name,
                    'label_for' => 'has_archive',
                    'classes' => 'ui-toggle',
                    'placeholder' => '...',
                ]
            ],
        ];


        $this->settings->setFields($args);
    }

    public function registerCustomPostType()
    {

        foreach ($this->customPostTypes as $cpt) {


            register_post_type($cpt['post_type'],
                [
                    'labels' => [
                        'name' => $cpt['plural_name'],
                        'singular_name' => $cpt['singular_name'],
                        'menu_name' => isset($cpt['menu_name']) ? $cpt['menu_name'] : $cpt['plural_name'],
                        'name_admin_bar' => isset($cpt['name_admin_bar']) ? $cpt['name_admin_bar'] : $cpt['singular_name'],
                        'archives' => isset($cpt['archives']) ? $cpt['archives'] : ' آرشیو ' . $cpt['singular_name'],
                        'attributes' => isset($cpt['attributes']) ? $cpt['attributes'] : ' مشخصات ' . $cpt['singular_name'],
                        'parent_item_colon' => isset($cpt['parent_item_colon']) ? $cpt['parent_item_colon'] : ' والد ' . $cpt['singular_name'] . ':',
                        'all_items' => isset($cpt['all_items']) ? $cpt['all_items'] : ' همه ' . $cpt['plural_name'],
                        'add_new_item' => isset($cpt['add_new_item']) ? $cpt['add_new_item'] : ' افزودن ' . $cpt['singular_name'],
                        'add_new' => isset($cpt['add_new']) ? $cpt['add_new'] : ' افزودن ',
                        'new_item' => isset($cpt['new_item']) ? $cpt['new_item'] : $cpt['singular_name'] . ' جدید ',
                        'edit_item' => isset($cpt['edit_item']) ? $cpt['edit_item'] : ' ویرایش ' . $cpt['singular_name'],
                        'update_item' => isset($cpt['update_item']) ? $cpt['update_item'] : ' بروزرسانی ' . $cpt['singular_name'],
                        'view_item' => isset($cpt['view_item']) ? $cpt['view_item'] : ' مشاهده ' . $cpt['singular_name'],
                        'view_items' => isset($cpt['view_items']) ? $cpt['view_items'] : ' مشاهده ' . $cpt['plural_name'],
                        'search_items' => isset($cpt['search_items']) ? $cpt['search_items'] : ' جست و جوی ' . $cpt['singular_name'],
                        'not_found' => isset($cpt['not_found']) ? $cpt['not_found'] : $cpt['singular_name'] . ' یافت نشد ',
                        'not_found_in_trash' => isset($cpt['not_found_in_trash']) ? $cpt['not_found_in_trash'] : $cpt['singular_name'] . ' در سطل بازیافت پیدا نشد ',
                        'featured_image' => isset($cpt['featured_image']) ? $cpt['featured_image'] : 'تصویر',
                        'set_featured_image' => isset($cpt['set_featured_image']) ? $cpt['set_featured_image'] : 'تنظیم تصویر',
                        'remove_featured_image' => isset($cpt['remove_featured_image']) ? $cpt['remove_featured_image'] : 'حذف تصویر',
                        'use_featured_image' => isset($cpt['use_featured_image']) ? $cpt['use_featured_image'] : 'استفاده از تصویر',
                        'insert_into_item' => isset($cpt['insert_into_item']) ? $cpt['insert_into_item'] : ' افزودن به ' . $cpt['plural_name'],
                        'uploaded_to_this_item' => isset($cpt['uploaded_to_this_item']) ? $cpt['uploaded_to_this_item'] : ' بارگذاری به این ' . $cpt['singular_name'],
                        'items_list' => isset($cpt['items_list']) ? $cpt['items_list'] : ' لیست ' . $cpt['plural_name'],
                        'items_list_navigation' => isset($cpt['items_list_navigation']) ? $cpt['items_list_navigation'] : ' پیمایش لیست ' . $cpt['plural_name'],
                        'filter_items_list' => isset($cpt['filter_items_list']) ? $cpt['filter_items_list'] : ' فیلتر لیست ' . $cpt['plural_name'],
                    ],
                    'public' => isset($cpt['public']) ? $cpt['public'] : false,
                    'has_archive' => isset($cpt['has_archive']) ? $cpt['has_archive'] : false,
                    'label' => isset($cpt['label']) ? $cpt['label'] : $cpt['singular_name'],
                    'description' => isset($cpt['description']) ? $cpt['description'] : ' نوع داده ' . $cpt['singular_name'],
                    'supports' => isset($cpt['supports']) ? $cpt['supports'] : ['title', 'editor', 'thumbnail'],
                    'show_in_rest' => isset($cpt['show_in_rest']) ? $cpt['show_in_rest'] : true,
                    'taxonomies' => isset($cpt['taxonomies']) ? $cpt['taxonomies'] : ['category', 'post_tag'],
                    'hierarchical' => isset($cpt['hierarchical']) ? $cpt['hierarchical'] : false,
                    'show_ui' => isset($cpt['show_ui']) ? $cpt['show_ui'] : true,
                    'show_in_menu' => isset($cpt['show_in_menu']) ? $cpt['show_in_menu'] : true,
                    'menu_position' => isset($cpt['menu_position']) ? $cpt['menu_position'] : 5,
                    'show_in_admin_bar' => isset($cpt['show_in_admin_bar']) ? $cpt['show_in_admin_bar'] : true,
                    'show_in_nav_menus' => isset($cpt['show_in_nav_menus']) ? $cpt['show_in_nav_menus'] : true,
                    'can_export' => isset($cpt['can_export']) ? $cpt['can_export'] : true,
                    'exclude_from_search' => isset($cpt['exclude_from_search']) ? $cpt['exclude_from_search'] : false,
                    'publicly_queryable' => isset($cpt['publicly_queryable']) ? $cpt['publicly_queryable'] : true,
                    'capability_type' => isset($cpt['capability_type']) ? $cpt['capability_type'] : 'post',
                ]);
        }


    }


    private function storeCustomPostTypes()
    {
        $options = get_option(self::$option_name);
        if (!$options) return;

        foreach ($options as $option)

            if (is_array($option))
                $this->customPostTypes[] = [
                    'post_type' => isset($option['post_type']) ? $option['post_type'] : null,
                    'plural_name' => isset($option['plural_name']) ? $option['plural_name'] : null,
                    'singular_name' => isset($option['singular_name']) ? $option['singular_name'] : null,
                    'public' => isset($option['public']) ? $option['public'] : null,
                    'has_archive' => isset($option['has_archive']) ? $option['has_archive'] : null,

                ];


    }

    private function setSubPages()
    {
        $this->subPages = [
            [
                'parent_slug' => Helper::$pageSlug,
                'page_title' => 'انواع داده',
                'menu_title' => 'منوی انواع داده',
                'capability' => Helper::$adminCapability,
                'menu_slug' => self::$option_name,
                'callback' => [$this->callbacks, 'adminCpt']

            ]
        ];
    }
}