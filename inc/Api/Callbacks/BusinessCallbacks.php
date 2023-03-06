<?php
/**
 * @package drclubs
 */

namespace DrClubs\Api\Callbacks;

use DrClubs\Base\BaseController;
use DrClubs\Base\BusinessController;
use function DrClubs\myLog;

class  BusinessCallbacks extends BaseController
{
    public function settingsValidate($input)
    {


        $valid = $input;

        $old_option = get_option(BusinessController::$admin_settings_option_group);
        $default_option = BusinessController::DEFAULT_OPTIONS;
        $emotes = $default_option['customers']['emotes'];
        $levels = $default_option['customers']['levels'];


        if (!isset($input['user_can_use_balance'])) {
            $valid['user_can_use_balance'] = false;
        }
        if (!isset($input['user_can_register'])) {
            $valid['user_can_register'] = false;
        }
        if (!isset($input['user_can_score_from_products'])) {
            $valid['user_can_score_from_products'] = false;
        }
        if (!isset($input['customers_ui']['active'])) {
            $valid['customers_ui']['active'] = false;
        }
        if (isset($input['customers']['emote_months']['1'])) {
            $valid['customers']['emote_months']['2'] = $input['customers']['emote_months']['1'];
            $input['customers']['emote_months']['2'] = $input['customers']['emote_months']['1'];
        }
        foreach ($emotes as $month => $name) {

            if (!isset($input['customers']['emote_months'][$month]) || !is_numeric($input['customers']['emote_months'][$month])) {
                add_settings_error(BusinessController::$admin_settings_option_group, "emote$month", "مقدار مشتری {$emotes[$month]} عددی باشد", 'error');
                $valid['customers']['emote_months'][$month] = isset($old_option['customers']['emote_months'][$month]) ? $old_option['customers']['emote_months'][$month] : $default_option['customers']['emote_months'][$month];
            }
        }

        foreach ($levels as $level => $amount) {

            if (!isset($input['customers']['level_amounts'][$level]) || !is_numeric($input['customers']['level_amounts'][$level])) {
                add_settings_error(BusinessController::$admin_settings_option_group, "level$level", "مقدار مشتری {$levels[$level]} عددی باشد", 'error');
                $valid['customers']['level_amounts'][$level] = isset($old_option['customers']['level_amounts'][$level]) ? $old_option['customers']['level_amounts'][$level] : $default_option['customers']['level_amounts'][$level];
            }
        }
        if ($input['customers']['emote_months']['1'] > $input['customers']['emote_months']['2']) {
            $level = 1;
            add_settings_error(BusinessController::$admin_settings_option_group, "emote$level", "مقدار مشتری {$emotes[$level]} کم تر یا برابر مقدار مشتری {$emotes[$level+1]} باشد", 'error');
            $valid['customers']['emote_months'][$level] = isset($old_option['customers']['emote_months'][$level]) ? $old_option['customers']['emote_months'][$level] : $default_option['customers']['emote_months'][$level];
            $valid['customers']['emote_months'][$level + 1] = isset($old_option['customers']['emote_months'][$level + 1]) ? $old_option['customers']['emote_months'][$level + 1] : $default_option['customers']['emote_months'][$level + 1];

        }
        if ($input['customers']['emote_months']['2'] >= $input['customers']['emote_months']['3']) {
            $level = 2;
            add_settings_error(BusinessController::$admin_settings_option_group, "emote$level", "مقدار مشتری {$emotes[$level]} کم تر از مقدار مشتری {$emotes[$level+1]} باشد", 'error');
            $valid['customers']['emote_months'][$level] = isset($old_option['customers']['emote_months'][$level]) ? $old_option['customers']['emote_months'][$level] : $default_option['customers']['emote_months'][$level];
            $valid['customers']['emote_months'][$level + 1] = isset($old_option['customers']['emote_months'][$level + 1]) ? $old_option['customers']['emote_months'][$level + 1] : $default_option['customers']['emote_months'][$level + 1];

        }
        if ($input['customers']['level_amounts']['1'] <= $input['customers']['level_amounts']['2']) {
            $level = 1;
            add_settings_error(BusinessController::$admin_settings_option_group, "level$level", "مجموع خرید سطح {$levels[$level]} بیشتر از مجموع خرید سطح {$levels[$level+1]} باشد", 'error');
            $valid['customers']['level_amounts'][$level] = isset($old_option['customers']['level_amounts'][$level]) ? $old_option['customers']['level_amounts'][$level] : $default_option['customers']['level_amounts'][$level];
            $valid['customers']['level_amounts'][$level + 1] = isset($old_option['customers']['level_amounts'][$level + 1]) ? $old_option['customers']['level_amounts'][$level + 1] : $default_option['customers']['level_amounts'][$level + 1];

        }
        if ($input['customers']['level_amounts']['2'] <= $input['customers']['level_amounts']['3']) {
            $level = 2;
            add_settings_error(BusinessController::$admin_settings_option_group, "level$level", "مجموع خرید سطح {$levels[$level]} بیشتر از مجموع خرید سطح {$levels[$level+1]} باشد", 'error');
            $valid['customers']['level_amounts'][$level] = isset($old_option['customers']['level_amounts'][$level]) ? $old_option['customers']['level_amounts'][$level] : $default_option['customers']['level_amounts'][$level];
            $valid['customers']['level_amounts'][$level + 1] = isset($old_option['customers']['level_amounts'][$level + 1]) ? $old_option['customers']['level_amounts'][$level + 1] : $default_option['customers']['level_amounts'][$level + 1];
        }


        return $valid;
    }

    public function admin_section($args)
    {
        echo 'نام کاربری و رمز عبور دکتر کلابز را وارد کنید و دکمه اتصال را بزنید';
        echo ' | <a style="text-decoration: none" href="https://drclubs.ir/insta">درخواست اکانت</a> ';

    }

    public function admin_option_section($args)
    {

//        echo '<h2 style="font-family: Tanha,serif">' . $args['title'] . '</h2>';
        echo 'جهت اعمال تغییرات، دکمه ثبت تنظیمات را بزنید';

    }

    public function checkboxField($args)
    {

        $name = $args['label_for'];
        $label = $args['label'];
        $description = isset($args['description']) ? $args['description'] : '';
        $classes = $args['classes'];
        $option_name = $args['option_name'];

        $checked = $args['value'];

        echo '<div  style="padding:1rem;' . ($description ? 'border: solid 1px rgba(0,0,0,.2);border-radius:.5rem;' : '') . '" >';
        echo '<label style="display:inline-block;" class="font-bold" for="' . $name . '">' . $label . '</label>';
        echo '<div style="display:inline;margin-left:1rem;margin-right:1rem" class="' . $classes . '"><input type="checkbox" id="' . $name . '" name="' . $option_name . '" value="1" ' . ($checked ? 'checked' : '') . ' /> <label for="' . $name . '"><div></div></label></div>';
        echo '<small style="display:inline-block; color:rgba(0,0,0,.6)" class=" m-1" for="' . $name . '">' . $description . '</small>';
        echo '</div>';
    }

    public function textField($args)
    {
        $label = $args['label'];
        $name = $args['label_for'];
        $value = $args['value'];
        $option_name = $args['option_name'];
        $styles = isset($args['styles']) ? $args['styles'] : '';
        $classes = isset($args['classes']) ? $args['classes'] : '';
        $description = isset($args['description']) ? $args['description'] : '';
        $type = isset($args['type']) ? $args['type'] : 'text';


        echo '<label style="' . $styles . '" class="font-bold px-1" for="' . $name . '">' . $label . '</label>';
        echo "<div style='font-size: small;color:dimgrey' class='px-1'>$description</div>";
        echo '<input  type="' . $type . '" class="regular-text m-1 block" id="' . $name . '" name="' . $option_name . '" value="' . $value . '"  >';

    }

    public function textFieldGroup($array)
    {
        echo '<div  style="padding:1rem;margin:1rem 0;' . 'border: solid 1px rgba(0,0,0,.2);border-radius:.5rem;' . '" >';
        echo '<h3 style="font-family: Tanha,serif">' . $array[0]['title'] . '</h3>';

        $hint = isset($array[0]['hint']) ? $array[0]['hint'] : '';

        echo "<div style='font-size: small;color:dimgrey' class='px-1'>$hint</div>";

        foreach ($array as $args) {


            $label = $args['label'];
            $name = $args['label_for'];
            $value = $args['value'];
            $values = isset($args['values']) ? $args['values'] : [];
            $type = isset($args['type']) ? $args['type'] : 'text';
            $option_name = $args['option_name'];
            $labelStyle = isset($args['labelStyle']) ? $args['labelStyle'] : '';
            $inputStyle = isset($args['inputStyle']) ? $args['inputStyle'] : '';
            $wrapperStyle = isset($args['wrapperStyle']) ? $args['wrapperStyle'] : '';
            $classes = isset($args['classes']) ? $args['classes'] : '';
            $description = isset($args['description']) ? $args['description'] : '';

            if ($type == 'checkbox') {
                echo "<div style='' class='p-2 '> ";
                echo "<div style='' class='p-2 font-bold d-inline'>$label</div>";
                echo '<div class=" ui-toggle d-inline"> ';
                echo '<input style=""   type="' . $type . '" class="regular-text   " id="' . $name . '" name="' . $option_name . '" value="1" ' . ($value == true ? ' checked ' : ' ') . '  />';
                echo ' <label for="' . $name . '">
                            <div></div>
                            </label>';

                echo '</div>';
                echo "<div style='font-size: small;color:dimgrey' class='p-1'>$description</div>";
                echo '</div>';


                continue;
            }
            if ($type == 'radio') {
                echo "<div   class='p-2 '> ";
                echo "<div style='' class='p-2 font-bold'>$label</div>";
                echo "<div style='font-size: small;color:dimgrey' class='px-1'>$description</div>";
                echo '<div class="  p-1">';
                foreach ($values as $val) {
                    echo '<span class="p-1"> ';
                    echo '<input style=""   type="' . $type . '" class="regular-text   " id="' . $option_name . $val . '" name="' . $option_name . '" value="' . $val . '" ' . ($val == $value ? ' checked ' : ' ') . '  />';
                    echo '<label  style=" " class="  font-bold  " for="' . $option_name . $val . '">' . $val . '</label>';
                    echo '</span>';
                }
                echo '</div>';
                echo '</div>';

                continue;
            }

            echo '<div style="' . $wrapperStyle . '">';
            echo '<label style="' . $labelStyle . '" class="font-bold px-1" for="' . $name . '">' . $label . '</label>';
            echo "<div style='font-size: small;color:dimgrey' class='px-1'>$description</div>";
            echo '<input style="' . $inputStyle . '"   type="' . $type . '" class="regular-text m-1   " id="' . $name . '" name="' . $option_name . '" value="' . $value . '"  >';
            echo '</div>';
        }

        echo '</div>';
    }

    public function uiGroup($array)
    {
        echo '<div  style="padding:1rem;margin:1rem 0;' . 'border: solid 1px rgba(0,0,0,.2);border-radius:.5rem;' . '" >';
        echo '<h3 style="font-family: Tanha,serif">' . $array['title'] . '</h3>';


        $active = isset($array['options']['active']) && $array['options']['active'];
        $option_name = $array['option_name'] . "[active]";
        echo '<h4 style="font-family: Tanha,serif;margin-bottom:.5rem;">' . 'فعال سازی' . '</h4>';
        echo "<span style='font-size: small;color:dimgrey;' class='px-1' >نمایش دکمه باشگاه مشتریان در سایت</span>";
        echo '<div style="display:inline;margin-left:1rem;margin-right:1rem" class=" ui-toggle">
            <input type="checkbox" id="ui-active" name="' . $option_name . '" value="' . (1) . '" ' . ($active ? 'checked' : '') . ' />
             <label for="ui-active">
             <div></div>
            </label>
        </div>';

//

        echo '<h4 style="font-family: Tanha,serif;margin-bottom:.5rem;">' . 'محل قرارگیری' . '</h4>';
        echo "<div style='font-size: small;color:dimgrey;' class='px-1'>کاربران دکمه باشگاه مشتریان را در این قسمت از سایت خواهند دید</div>";

        $positions = isset($array['options']['positions']) && count($array['options']['positions']) > 1 ? $array['options']['positions'] : $array['default_options']['positions'];
        $position_selected = $positions['selected'];

        foreach ($positions as $idx => $pos) {
            if ($idx == 'selected') continue;

            $option_name = $array['option_name'] . '[positions][selected]';

            $option_hidden_values = isset($array['options']['positions'][$idx]) ? $array['options']['positions'][$idx] : $array['default_options']['positions'][$idx];
            foreach ($option_hidden_values as $key => $option_hidden_value) {
                $option_hidden_name = $array['option_name'] . "[positions][$idx][$key]";
                echo '<input type="hidden" name="' . $option_hidden_name . '" value="' . $option_hidden_value . '" />';
            }


            $value = $idx;

            echo '<div style="display:inline-block;>';
            echo '<label style="display:inline-block;" class="font-bold px-1" for="' . $value . '">';
            echo '<input style="display:inline-block;"   type="radio" class="regular-text m-1   " id="' . $value . '" name="' . $option_name . '" value="' . $value . '" ' . ($value == $position_selected ? 'checked' : '') . '/>' . $value;
            echo '</label>';
            echo '</div>';
        }
        echo '<hr style="border-bottom: 1px solid lightgrey">';
        echo '<h4 style="font-family: Tanha,serif;margin-bottom:.5rem;">' . 'حاشیه (پیکسل)' . '</h4>';
        echo "<div style='font-size: small;color:dimgrey;' class='px-1'> فاصله دکمه از اطراف را تنظیم می کند</div>";

        $margins = isset($array['options']['margins']) ? $array['options']['margins'] : $array['default_options']['margins'];


        foreach ($margins as $idx => $margin) {

            $option_hidden_values = isset($array['options']['margins'][$idx]) ? $array['options']['margins'][$idx] : $array['default_options']['margins'][$idx];

            foreach ($option_hidden_values as $key => $option_hidden_value) {
                $option_hidden_name = $array['option_name'] . "[margins][$idx][$key]";
                echo '<input type="hidden" name="' . $option_hidden_name . '" value="' . $option_hidden_value . '" />';
            }

            $option_name = $array['option_name'] . "[margins][$idx][value]";
            $value = isset($array['options']['margins'][$idx]['value']) ? $array['options']['margins'][$idx]['value'] : $array['default_options']['margins'][$idx]['value'];


            echo '<div style="display:inline-block;>';
            echo '<label style="display:inline-block;" class="font-bold px-1" for="' . $idx . '">' . $idx;
            echo '<input style="display:inline-block;max-width:5rem;"   type="number" class="regular-text m-1   " id="' . $idx . '" name="' . $option_name . '" value="' . $value . '" ' . ($value == $position_selected ? 'checked' : '') . '/>';
            echo '</label>';

            echo '</div>';
        }
        echo '</div>';

    }

    public function levelsGroup($args)
    {
        $assetLink = BaseController::get_assets_path() . 'img/';
        $emotes = isset($args['options']['emotes']) ? $args['options']['emotes'] : $args['default_options']['emotes'];
        $levels = isset($args['options']['levels']) ? $args['options']['levels'] : $args['default_options']['levels'];
        $emote_months = isset($args['options']['emote_months']) ? $args['options']['emote_months'] : $args['default_options']['emote_months'];
        $level_amounts = isset($args['options']['level_amounts']) ? $args['options']['level_amounts'] : $args['default_options']['level_amounts'];
        $option_name = $args['option_name'];

        echo '   <div style="padding:1rem;margin:1rem 0;' . 'border: solid 1px rgba(0,0,0,.2);border-radius:.5rem;' . '"> 
           
         <div class="row   ">
 ';

        echo
            '
          <div class="row col-12 col-xl-6 align-content-between  " style="max-width: 55rem;" >
                    <h4 class="m-1 col-12" style="font-family: Tanha,serif">دسته بندی مشتریان ( بر اساس زمان خرید )</h4>
          <div   style="font-size: small;color:dimgrey;" class="col-12 px-1 mx-2 mb-4">شناسایی وفاداری مشتریان بر اساس تداوم خرید</div>

            <div class="col-4 "  >
                <div class="row m-sm-1 h-100 p-1 border border-success rounded-3 " >
                    <img class=""    src="' . "{$assetLink}emote1.png" . '" alt="">
                    <div class="p-1  text-center text-success font-weight-bold">' . $emotes['1'] . '</div>
                    <small class="  small  text-center text-success  ">' . "خرید در {$emote_months['1'] } ماه اخیر" . '</small>
                </div>
            </div>
            <div class="col-4 "  >
                <div class="row m-sm-1 h-100 p-1 border border-warning rounded-3 " >
                    <img class="" height=" " src="' . "{$assetLink}emote2.png" . '" alt="">
                    <div class=" p-1 text-center text-warning font-weight-bold">' . $emotes['2'] . '</div>
                    <small class="  small  text-center text-warning  ">خرید در محدوده وفادار و بی خیال</small>

                </div>
            </div>
            <div class="col-4 "  >
                <div class="row m-sm-1 h-100 p-1 border border-danger rounded-3 " >
                    <img class="" height=" " src="' . "{$assetLink}emote3.png" . '" alt="">
                    <div class="p-1 text-center text-danger font-weight-bold">' . $emotes['3'] . '</div>
                    <small class="  small  text-center text-danger  ">' . "عدم خرید در {$emote_months['3'] } ماه اخیر" . '</small>

                </div>
            </div>

        
        <div class="table-responsive m-1  text-center"> 
        <table class="table table-striped table-sm">
            
            <thead>
                <th></th>
                <th></th>
               
            </thead>
            <tbody>
                <tr>
                    <td>
                        <h5 style="font-family: Tanha,serif" class="d-inline text-success"   >مشتری وفادار</h5>  
                    </td>

                    <td>
                    <label class="small" for="emote1">
             خرید در
                    <input id="emote1" style="width:3rem;" type="number" min="1" name="' . $option_name . '[emote_months][1]" value="' . $emote_months['1'] . '"> 
              ماه اخیر 
            </label>
            </td>
                   
                </tr>
                <tr> 
                <td>             <h5 style="font-family: Tanha,serif" class="d-inline text-warning"   >مشتری از دست رفته</h5>  
</td>
                <td> <label class="small" for="emote2">خرید در محدوده وفادار و بی خیال
 
            </label>
                </td>
                </tr>
                <tr>
                <td> 
                             <h5 style="font-family: Tanha,serif" class="d-inline text-danger"   >مشتری بی خیال</h5>  
                </td>
                <td>
                 <label class="small" for="emote3">
           عدم خرید در
              <input id="emote3" style="width:3rem;" type="number" min="1" name="' . $option_name . '[emote_months][3]" value="' . $emote_months['3'] . '"> 
              ماه اخیر 
            </label>
                </td>
                </tr>
                
            </tbody>
        </table>
        </div>
        <div class="col-12 ">
        ' . $this->showErrorFields(['emote1', 'emote2', 'emote3'], BusinessController::$admin_settings_option_group) . '
        </div>
        </div>
         
        
        
        ';


        echo
            '
        

          <div class="row  col-12 col-xl-6 align-content-between" style="max-width: 55rem;" >
             <h4 class="m-1 col-12" style="font-family: Tanha,serif">دسته بندی مشتریان ( بر اساس مجموع خرید )</h4>
          <div style="font-size: small;color:dimgrey;" class="col-12 px-1 mx-2 mb-4">دسته بندی مشتریان بر اساس مجموع مبلغ خرید</div>

            <div class="col-4 "  >
                <div class="row m-sm-1 h-100 p-1  border border-success rounded-3 " >
                    <img class="" height="  " width=" " src="' . "{$assetLink}level1.png" . '" alt="">
                    <div class="p-1  text-center text-success font-weight-bold">' . $levels['1'] . '</div>
                    <small class="  small  text-center text-success  ">' . "مجموع خرید بالای {$level_amounts['1'] } تومان" . '</small>
                </div>
            </div>
            <div class="col-4 "  >
                <div class="row m-sm-1 h-100 p-1   border border-warning rounded-3 " >
                    <img class="" height=" " src="' . "{$assetLink}level2.png" . '" alt="">
                    <div class=" p-1 text-center text-warning font-weight-bold">' . $levels['2'] . '</div>
                    <small class="  small  text-center text-warning  ">' . "مجموع خرید بالای {$level_amounts['2'] } تومان" . '</small>

                </div>
            </div>
            <div class="col-4 "  >
                <div class="row m-sm-1 h-100 p-1  border border-danger rounded-3 " >
                    <img class="" height=" " src="' . "{$assetLink}level3.png" . '" alt="">
                    <div class="p-1 text-center text-danger font-weight-bold">' . $levels['3'] . '</div>
                    <small class="  small  text-center text-danger  ">' . "مجموع خرید بالای {$level_amounts['3'] } تومان" . '</small>

                </div>
            </div>

        
        <div class="table-responsive m-1  text-center"> 
        <table class="table table-striped table-sm">
            
            <thead>
                <th></th>
                <th></th>
               
            </thead>
            <tbody>
                <tr>
                    <td>
                        <h5 style="font-family: Tanha,serif" class="d-inline text-success"   >سطح طلایی</h5>  
                    </td>

                    <td>
                    <label class="small" for="level1">
             مجموع خرید بالای
                    <input id="level1" style="width:8rem;" type="number" min="0" name="' . $option_name . '[level_amounts][1]" value="' . $level_amounts['1'] . '"> 
              تومان 
            </label>
            </td>
                   
                </tr>
                <tr> 
                <td>             <h5 style="font-family: Tanha,serif" class="d-inline text-warning"   >سطح نقره ای</h5>  
</td>
                <td> <label class="small" for="level2">
             مجموع خرید بالای
              <input id="level2" style="width:8rem;" type="number" min="0" name="' . $option_name . '[level_amounts][2]" value="' . $level_amounts['2'] . '"> 
              تومان 
            </label>
                </td>
                </tr>
                <tr>
                <td> 
                             <h5 style="font-family: Tanha,serif" class="d-inline text-danger"   >سطح برنزی</h5>  
                </td>
                <td>
                 <label class="small" for="level3">
           مجموع خرید بالای 0 
              <input id="level3" style="width:8rem;" type="hidden" min="0" name="' . $option_name . '[level_amounts][3]" value="' . $level_amounts['3'] . '"> 
              تومان 
            </label>
                </td>
                </tr>
                
            </tbody>
        </table>
        </div>
          <div class="col-12">
        ' . $this->showErrorFields(['level1', 'level2', 'level3'], BusinessController::$admin_settings_option_group) . '
        </div>
      </div>
        
        ';


        echo ' </div>';
        echo ' </div>';

    }

    private function showErrorFields($name, $admin_settings_option_group)
    {
        if (!is_array($name))
            $name = [$name];

        foreach (get_settings_errors($admin_settings_option_group) as $error) {
            if (isset($error['code']) && in_array($error['code'], $name))
                echo '
                <div class="  widefat" style="font-family: Tanha,serif">
        <div id="setting-error-' . $error['code'] . '" class="notice notice-error settings-error is-dismissible"> 
            <p><strong class="text-danger">' . $error['message'] . ' </strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">رد کردن این اخطار</span></button></div> 
        </div>
                ';
        }
    }
}