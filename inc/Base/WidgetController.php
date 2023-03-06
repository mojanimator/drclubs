<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;

use DrClubs\Api\Callbacks\AdminCallbacks;
use DrClubs\Api\Callbacks\CptCallbacks;
use DrClubs\Api\Widgets\MediaWidget;
use  DrClubs\Base\BaseController;
use  DrClubs\Api\SettingsApi;
use DrClubs\Helper;

class  WidgetController extends BaseController
{

    public static $option_name = 'drclubs_plugin_widget';

    public function register()
    {


        if (!$this->activated('media_widget')) return;

        $media_widget = new MediaWidget();
        $media_widget->register();

    }


}