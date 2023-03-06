<?php
/**
 * @package drclubs
 */


if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}


//access database via sql
global $wpdb;
$prefix = $wpdb->prefix;

$table = $prefix . 'drclubs_logs';
$wpdb->query("DROP TABLE IF EXISTS $table");


delete_option('drclubs_admin_option_settings');
delete_option('drclubs_admin_connect_settings');
delete_option('drclubs_cache');
delete_option('drclubs_logs');

$wpdb->delete($wpdb->postmeta, array('meta_key' => 'drclubs_order_info'));
$wpdb->delete($wpdb->postmeta, array('meta_key' => 'drclubs_product_info'));

$wpdb->delete($wpdb->usermeta, array('meta_key' => '_drclubs_user_data'));
