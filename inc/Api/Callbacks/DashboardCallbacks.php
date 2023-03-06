<?php
/**
 * @package drclubs
 */

namespace DrClubs\Api\Callbacks;

use DrClubs\Base\BaseController;

class  DashboardCallbacks extends BaseController
{
    public function adminTax()
    {
        return require_once($this->plugin_path . '/templates/admin_tax.php');
    }

    public function adminDashboard()
    {
        return require_once($this->plugin_path . '/templates/dashboard.php');
    }

    public function adminCpt()
    {
        return require_once($this->plugin_path . '/templates/cpt.php');
    }

    public function drclubsOptionGroups($input)
    {
        return $input;
    }

    public function drclubsAdminSection()
    {
        echo 'Check This Section';
    }

    public function drclubsTextExample()
    {
        $value = esc_attr(get_option('text_example'));

        echo '<input type="text" class="regular-text" name="text_example" value="' . $value . '" placeholder="وارد کنید..."> ';
    }

    public function drclubsFirstName()
    {
        $value = esc_attr(get_option('first_name'));

        echo '<input type="text" class="regular-text" name="text_example" value="' . $value . '" placeholder="نام..."> ';
    }
}