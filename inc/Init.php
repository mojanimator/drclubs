<?php
/**
 * @package drclubs
 */

namespace DrClubs;

use DrClubs\Base\AuthController;
use DrClubs\Base\ExcelController;
use DrClubs\Base\UserController;

final class Init
{
    /**
     * store all classes in array
     * @return array Full List Of Classes
     */
    public static function get_services()
    {
        return [
            Base\Enqueue::class,
            Base\SettingsLinks::class,
            Pages\Dashboard::class,
//            Base\CustomPostTypeController::class,
//            Base\CustomTaxonomyController::class,
//            Base\WidgetController::class,
//            Base\TestimonialController::class,
//            Base\TemplateController::class,
            Base\UserController::class,
//            Base\AuthController::class,
            Base\BusinessController::class,
            Base\WooCommerceController::class,
            Base\CronJobController::class,

        ];
    }

    /**
     * loop through classes and initialize them
     */
    public static function register_services()
    {
        foreach (self::get_services() as $class) {
            $service = self::instantiate($class);
            if (method_exists($service, 'register'))
                $service->register();
        }
    }

    /**
     * initialize class
     * @param class $class class from the services array
     * @return class instance
     */
    private static function instantiate($class)
    {
        return new $class();
    }
}