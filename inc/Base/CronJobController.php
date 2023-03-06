<?php
/**
 * Created by PhpStorm.
 * User: MSI GS72
 * Date: 31/08/2022
 * Time: 11:14 PM
 */

namespace DrClubs\Base;


use DrClubs\Api\DrClubsApi;
use function DrClubs\myLog;

class CronJobController
{
    const TASK_NAME = 'drclubs_failed_tasks_cronjob';

    public function __construct()
    {

    }

    public function register()
    {
        add_filter('cron_schedules', [$this, 'custom_schedules']);
        $this->setCronJobs();
    }

    public function custom_schedules($schedules)
    {

        if (!isset($schedules['30sec'])) {
            $schedules["30sec"] = array(
                'interval' => 30,
                'display' => __('Once every 30 seconds'));
        }
        return $schedules;


    }

    public function setCronJobs()
    {

        if (!wp_next_scheduled(self::TASK_NAME)) {

            wp_schedule_event(time(), 'daily', self::TASK_NAME);

        }
        add_action(self::TASK_NAME, [$this, 'retry_failed_jobs']);

    }

    public static function retry_failed_jobs()
    {

        try {
            $api = new DrClubsApi();
            $option = get_option(BusinessController::$cache_option_group, BusinessController::DEFAULT_CACHES);

            //        key=register,buy

            if (isset($option['register'])) {
                foreach ($option['register'] as $id => $user) {
                    $user = json_decode($user, true);
                    if (!$user)
                        unset($option['register'][$id]);
                    $user['cache'] = true;
                    $res = $api->registerUser($user);

                }
            }
            if (isset($option['buy'])) {
                foreach ($option['buy'] as $order_id => $data) {
                    $data = json_decode($data, true);
                    $order = wc_get_order($order_id);
                    if (!$data || !isset($data['CustomerId']) || !$order) {
                        unset($option['buy'][$order_id]);
                        continue;
                    }

                    $res = $api->buy($order, ['cache' => true, 'drclubs_id' => $data['CustomerId']]);

                }
            }
        } catch (\Exception $e) {
        }

    }
}