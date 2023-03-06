<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;

use DrClubs\Helper;

class  Activate
{


    public static function activate()
    {


        flush_rewrite_rules();

        $default = [];
        if (!get_option(Helper::$pageSlug))
            update_option(Helper::$pageSlug, $default);

//        if (!get_option(CustomPostTypeController::$option_name))
//            update_option(CustomPostTypeController::$option_name, $default);

        if (!get_option(BusinessController::$admin_connect_option_group))
            update_option(BusinessController::$admin_connect_option_group, []);
        if (!get_option(BusinessController::$admin_settings_option_group))
            update_option(BusinessController::$admin_settings_option_group, BusinessController::DEFAULT_OPTIONS);
        if (!get_option(BusinessController::$cache_option_group))
            update_option(BusinessController::$cache_option_group, BusinessController::DEFAULT_CACHES);
        if (!get_option(BusinessController::$logs_option_group))
            update_option(BusinessController::$logs_option_group, []);

        self::create_drclubs_logs_database_table();
    }

    static function create_drclubs_logs_database_table()
    {
        //logs lottery,comment
        global $wpdb;

        $table_name = $wpdb->prefix . BusinessController::LOGS_TABLE;

        $sql = "CREATE TABLE IF NOT EXISTS $table_name 
      (
       `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT , 
      `user_id` BIGINT UNSIGNED NOT NULL ,
       `type` VARCHAR(30) NOT NULL ,
        `value` VARCHAR(100) NOT NULL ,
         `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
          PRIMARY KEY (`id`), 
          INDEX `logs_type_index` (`type`),
           INDEX `user_id_index` (`user_id`)) 
           ENGINE = InnoDB CHARSET=utf8mb4 
           COLLATE utf8mb4_general_ci";
        require_once ABSPATH . '/wp-admin/includes/upgrade.php';
        dbDelta($sql);

    }
}