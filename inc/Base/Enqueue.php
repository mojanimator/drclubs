<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;

use DrClubs\Base\BaseController;

class Enqueue extends BaseController
{

    public function register()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
    }

    function enqueue()
    {
        wp_enqueue_script('media-upload');
        wp_enqueue_style('dashicons');
        wp_enqueue_media();

        wp_enqueue_style('drclubs-mystyle', $this->plugin_url . 'assets/css/style.css', __FILE__);
        wp_enqueue_script('drclubs-myscript', $this->plugin_url . 'assets/js/script.js', __FILE__);

    }
}