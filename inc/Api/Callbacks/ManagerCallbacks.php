<?php
/**
 * @package drclubs
 */

namespace DrClubs\Api\Callbacks;

use DrClubs\Base\BaseController;

class  ManagerCallbacks extends BaseController
{
    public function checkboxSanitize($input)
    {


//        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);

        $output = [];
        foreach ($this->managers as $id => $title) {
            $output[$id] = isset($input[$id]) ? true : false;
        }

        return $output;
    }

    public function adminSectionManager()
    {
        echo 'در این قسمت می توانید تنظیمات دکتر کلابز را مدیریت کنید';
    }

    public function checkboxField($args)
    {

        $name = $args['label_for'];

        $classes = $args['classes'];
        $option_name = $args['option_name'];
        $checkbox = get_option($option_name);
        $checked = isset($checkbox[$name]) ? ($checkbox[$name] ? true : false) : false;
        echo '<div class="' . $classes . '"><input type="checkbox" id="' . $name . '" name="' . $option_name . '[' . $name . ']' . '" value="1" ' . ($checked ? 'checked' : '') . ' /> <label for="' . $name . '"><div></div></label></div>';
    }
}