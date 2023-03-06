<?php
/**
 * @package drclubs
 */

namespace DrClubs\Api\Callbacks;


use DrClubs\Base\BaseController;

class  TestimonialCallbacks extends BaseController
{


    public function shortcodePage()
    {


        return require_once("$this->plugin_path/templates/testimonial.php");
    }


}