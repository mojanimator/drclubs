<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;

use DrClubs\Api\Callbacks\AdminCallbacks;
use DrClubs\Api\Callbacks\CptCallbacks;
use DrClubs\Api\Callbacks\DashboardCallbacks;
use DrClubs\Api\Callbacks\TaxCallbacks;
use  DrClubs\Base\BaseController;
use  DrClubs\Api\SettingsApi;
use DrClubs\Helper;

class  CustomTaxonomyController extends BaseController
{
    public $settings;
    public $subPages = [];
    public $callbacks;
    public $tax_callbacks;
    public $customTaxonomies = [];
    public static $option_name = 'drclubs_plugin_taxonomy';

    public function register()
    {


        if (!$this->activated('taxonomy_manager')) return;

        $this->settings = new SettingsApi();
        $this->callbacks = new DashboardCallbacks();
        $this->tax_callbacks = new TaxCallbacks();
        $this->setSubPages();

        $this->setSettings();
        $this->setSections();
        $this->setFields();

        $this->settings->addSubPages($this->subPages)->register();

        $this->storeTaxonomies();

        if (!empty($this->customPostTypes))
            add_action('init', [$this, 'registerTaxonomy']);
    }

    public function setSettings()
    {
        $args = [
            [
                'option_group' => 'drclubs_plugin_taxonomy_settings',
                'option_name' => self::$option_name,
                'callback' => [$this->tax_callbacks, 'taxonomySanitize']
            ]
        ];


        $this->settings->setSettings($args);
    }

    public function setSections()
    {
        $args = [
            [
                'id' => 'drclubs_tax_index',
                'title' => 'بخش تنظیمات تکسونومی',
                'callback' => [$this->tax_callbacks, 'cptSectionManager'],
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
                'callback' => [$this->tax_callbacks, 'textField'],
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
                'callback' => [$this->tax_callbacks, 'textField'],
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
                'callback' => [$this->tax_callbacks, 'textField'],
                'page' => self::$option_name,
                'section' => 'drclubs_cpt_index',
                'args' => [
                    'option_name' => self::$option_name,
                    'label_for' => 'plural_name',
                    'placeholder' => '...',

                ]
            ],
            [
                'id' => 'hierarchical', //must be option_name of settings
                'title' => 'اتصال به نوع داده',
                'callback' => [$this->tax_callbacks, 'checkboxField'],
                'page' => self::$option_name,
                'section' => 'drclubs_tax_index',
                'args' => [
                    'option_name' => self::$option_name,
                    'label_for' => 'hierarchical',
                    'classes' => 'ui-toggle',
                    'placeholder' => '...',
                ]
            ],
            [
                'id' => 'objects', //must be option_name of settings
                'title' => 'اتصال به نوع داده',
                'callback' => [$this->tax_callbacks, 'checkboxPostTypesField'],
                'page' => self::$option_name,
                'section' => 'drclubs_cpt_index',
                'args' => [
                    'option_name' => self::$option_name,
                    'label_for' => 'objects',
                    'classes' => 'ui-toggle',
                    'placeholder' => '...',
                ]
            ],

        ];


        $this->settings->setFields($args);
    }

    public function registerTaxonomy()
    {

        foreach ($this->customTaxonomies as $tax) {


            register_taxonomy($tax['rewrite']['slug'], $tax['objects'],
                ['labels' => [
                    'name' => $tax['plural_name'],
                    'singular_name' => $tax['singular_name'],
                    'menu_name' => isset($tax['menu_name']) ? $tax['menu_name'] : $tax['plural_name'],
                    'name_admin_bar' => isset($tax['name_admin_bar']) ? $tax['name_admin_bar'] : $tax['singular_name'],
                    'archives' => isset($tax['archives']) ? $tax['archives'] : ' آرشیو ' . $tax['singular_name'],
                    'parent_item_colon' => isset($tax['parent_item_colon']) ? $tax['parent_item_colon'] : ' والد ' . $tax['singular_name'] . ':',
                    'all_items' => isset($tax['all_items']) ? $tax['all_items'] : ' همه ' . $tax['plural_name'],
                    'add_new_item' => isset($tax['add_new_item']) ? $tax['add_new_item'] : ' افزودن ' . $tax['singular_name'],
                    'add_new' => isset($tax['add_new']) ? $tax['add_new'] : ' افزودن ',
                    'new_item' => isset($tax['new_item']) ? $tax['new_item'] : $tax['singular_name'] . ' جدید ',
                    'edit_item' => isset($tax['edit_item']) ? $tax['edit_item'] : ' ویرایش ' . $tax['singular_name'],
                    'update_item' => isset($tax['update_item']) ? $tax['update_item'] : ' بروزرسانی ' . $tax['singular_name'],
                    'view_item' => isset($tax['view_item']) ? $tax['view_item'] : ' مشاهده ' . $tax['singular_name'],
                    'view_items' => isset($tax['view_items']) ? $tax['view_items'] : ' مشاهده ' . $tax['plural_name'],
                    'search_items' => isset($tax['search_items']) ? $tax['search_items'] : ' جست و جوی ' . $tax['singular_name'],
                ],
                    'show_in_rest' => isset($tax['show_in_rest']) && is_bool($tax['show_in_rest']) ? $tax['show_in_rest'] : true,
                    'hierarchical' => isset($tax['hierarchical']) && is_bool($tax['hierarchical']) ? $tax['hierarchical'] : true,
                    'show_ui' => isset($tax['show_ui']) && is_bool($tax['show_ui']) ? $tax['show_ui'] : true,
                    'show_admin_column' => isset($tax['show_admin_column']) && is_bool($tax['show_admin_column']) ? $tax['show_admin_column'] : true,
                    'query_var' => isset($tax['query_var']) && is_bool($tax['query_var']) ? $tax['query_var'] : true,
                    'rewrite' => isset($tax['rewrite']) ? $tax['rewrite'] : ['slug' => isset($tax['singular_name']) ? $tax['singular_name'] : ''],
                    'objects' => isset($tax['objects']) ? $tax['objects'] : [],

                ]);
        }


    }


    private function storeTaxonomies()
    {
        $options = get_option(self::$option_name);
        if (!$options) return;

        foreach ($options as $option)

            if (is_array($option))
                $this->taxonomies = [
                    'hierarchical' => isset($option['hierarchical']) && is_bool($option['hierarchical']) ? $option['hierarchical'] : null,
                    'show_ui' => isset($option['show_ui']) && is_bool($option['show_ui']) ? $option['show_ui'] : null,
                    'show_admin_column' => isset($option['show_admin_column']) && is_bool($option['show_admin_column']) ? $option['show_admin_column'] : null,
                    'query_var' => isset($option['query_var']) && is_bool($option['query_var']) ? $option['query_var'] : null,
                    'rewrite' => ['slug' => isset($option['singular_name']) ? $option['singular_name'] : null],

                    'post_type' => isset($option['post_type']) ? $option['post_type'] : null,
                    'plural_name' => isset($option['plural_name']) ? $option['plural_name'] : null,
                    'singular_name' => isset($option['singular_name']) ? $option['singular_name'] : null,
                    'has_archive' => isset($option['has_archive']) ? $option['has_archive'] : null,
                    'objects' => isset($option['objects']) && is_array($option['objects']) ? array_keys($option['objects']) : null,

                ];


    }

    private function setSubPages()
    {
        $this->subPages = [
            [
                'parent_slug' => Helper::$pageSlug,
                'page_title' => 'انواع تکسونومی',
                'menu_title' => 'منوی تکسونومی',
                'capability' => Helper::$adminCapability,
                'menu_slug' => self::$option_name,
                'callback' => [$this->callbacks, 'adminTax']

            ]
        ];
    }
}