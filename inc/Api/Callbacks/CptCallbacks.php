<?php
/**
 * @package drclubs
 */

namespace DrClubs\Api\Callbacks;


use DrClubs\Base\CustomPostTypeController;

class  CptCallbacks
{


    public function cptSection()
    {
        echo 'در این قسمت می توانید تنظیمات نوع داده را مدیریت کنید';
    }

    public function cptSectionManager()
    {
        echo 'در این قسمت می توانید انواع داده جدید بسازید و مدیریت کنید';
    }

    public function cptSanitize($input)
    {
        $output = get_option(CustomPostTypeController::$option_name);


        if (isset($_POST["remove"])) {
            unset($output[$_POST["remove"]]);
            return $output;
        }

        if (!is_array($output))
            return [$input['post_type'] => $input];

        foreach ($output as $key => $value) {
            if ($input['post_type'] === $key)
                $output[$key] = $input;
            else $output[$input['post_type']] = $input;
        }

        return $output;
    }

    public
    function textField($args)
    {
        $name = $args['label_for'];
        $placeholder = $args['placeholder'];
        $option_name = $args['option_name'];
        $value = '';
        if (isset($_POST["edit_post"])) {
            $input = get_option($option_name);
            $value = $input[$_POST["edit_post"]][$name];
        }

        echo '<input type="text" class="regular-text" id="' . $name . '" name="' . $option_name . '[' . $name . ']" value="' . $value . '" placeholder="' . $placeholder . '" required >';
    }


    public
    function checkboxField($args)
    {

        $name = $args['label_for'];

        $classes = $args['classes'];
        $option_name = $args['option_name'];
        $checked = false;

        if (isset($_POST["edit_post"])) {
            $checkbox = get_option($option_name);
            $checked = isset($checkbox[$_POST["edit_post"]][$name]) ?: false;
        }
        echo '<div class="' . $classes . '"><input type="checkbox" id="' . $name . '" name="' . $option_name . '[' . $name . ']' . '" value="1" ' . ($checked ? 'checked' : '') . ' /> <label for="' . $name . '"><div></div></label></div>';
    }
}