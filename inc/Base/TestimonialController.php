<?php
/**
 * @package drclubs
 */

namespace DrClubs\Base;

use DrClubs\Api\Callbacks\AdminCallbacks;
use DrClubs\Api\Callbacks\CptCallbacks;
use DrClubs\Api\Callbacks\TaxCallbacks;
use DrClubs\Api\Callbacks\TestimonialCallbacks;
use  DrClubs\Base\BaseController;
use  DrClubs\Api\SettingsApi;
use DrClubs\Helper;

class  TestimonialController extends BaseController
{

    public static $option_name = 'drclubs_plugin_testimonial';
    public $settings;
    public $callbacks;

    public function register()
    {


        if (!$this->activated('testimonial_cpt_manager')) return;

        $this->settings = new SettingsApi();
        $this->callbacks = new TestimonialCallbacks();

        add_action('init', [$this, 'testimonialCpt']);
        add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        add_action('save_post', [$this, 'saveMetaBox']);
        add_action('manage_testimonial_posts_columns', [$this, 'setCustomColumns']);
        add_action('manage_testimonial_posts_custom_column', [$this, 'setCustomColumnsData'], 10, 2);
        add_action('manage_edit-testimonial_sortable_columns', [$this, 'setCustomColumnsSortable']);

        $this->setShortcodePage();

        add_shortcode('testimonial-form', [$this, 'testimonialForm']);
        add_shortcode('testimonial-slideshow', [$this, 'testimonialSlideshow']);

        //form ajax
        add_action('wp_ajax_submit_testimonial', [$this, 'submitTestimonial']);
        add_action('wp_ajax_nopriv_submit_testimonial', [$this, 'submitTestimonial']); //[nopriv] every authenticated and unauthenticated can submit form
    }

    public function testimonialCpt()
    {
        register_post_type('testimonial', [

            'labels' => [
                'name' => 'تست ها',
                'singular_name' => 'تست'
            ],
            'public' => true,
            'has_archive' => true,
            'menu_icon' => 'dashicons-testimonial',
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'supports' => ['title', 'editor'],
            'show_in_rest' => true /* activate wordpress gutenberg  */
        ]);
    }

    public function addMetaBoxes()
    {
        add_meta_box('testimonial_author', 'نویسنده', [$this, 'renderFeaturesBox'], 'testimonial', 'side', 'default');
    }

    public function renderFeaturesBox($post)
    {
        wp_nonce_field('drclubs_testimonial', 'drclubs_testimonial_nonce');

        $data = get_post_meta($post->ID, '_drclubs_testimonial_key', true);

        $name = isset($data['name']) ? $data['name'] : '';
        $email = isset($data['email']) ? $data['email'] : '';
        $approved = isset($data['approved']) ? $data['approved'] : false;


        ?>
        <p>
            <label class="meta-label" for="drclubs_testimonial_author">نویسنده تست</label>
            <input class="widefat" type="text" id="drclubs_testimonial_author" name="drclubs_testimonial_author"
                   value="<?php echo esc_attr($name) ?>">
        </p>
        <p>
            <label class="meta-label" for="drclubs_testimonial_email"> ایمیل</label>
            <input class="widefat" type="text" id="drclubs_testimonial_email" name="drclubs_testimonial_email"
                   value="<?php echo esc_attr($email) ?>">
        </p>
        <div class="meta-container">
            <label class="meta-label w-50 text-left" for="approved">تایید شده</label>
            <div class="text-right w-50 inline">
                <div class="ui-toggle inline">
                    <input type="checkbox" id="drclubs_testimonial_approved" name="drclubs_testimonial_approved"
                           value="1" class="" <?php echo $approved ? 'checked' : '' ?>>
                    <label for="drclubs_testimonial_approved">
                        <div></div>
                    </label>
                </div>
            </div>
        </div>
        <?php
    }

    public function saveMetaBox($post_id)
    {


        if (!isset($_POST['drclubs_testimonial_nonce']))
            return $post_id;

        $nonce = $_POST['drclubs_testimonial_nonce'];
        if (!wp_verify_nonce($nonce, 'drclubs_testimonial'))
            return $post_id;

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return $post_id;

        if (!current_user_can('edit_post', $post_id))
            return $post_id;

        $data = [
            'name' => sanitize_text_field($_POST['drclubs_testimonial_author']),
            'email' => sanitize_email($_POST['drclubs_testimonial_email']),
            'approved' => isset($_POST['drclubs_testimonial_approved']) ? 1 : 0,
        ];

        update_post_meta($post_id, '_drclubs_testimonial_key', $data);
        return $post_id;
    }

    public function setCustomColumns($columns)
    {
        $title = $columns['title'];
        $date = $columns['date'];
        unset($columns['title'], $columns['date']);

        $columns['name'] = 'نام';
        $columns['title'] = 'عنوان';
        $columns['approved'] = 'تایید';
        $columns['date'] = $date;
        return $columns;
    }

    public function setCustomColumnsData($column, $post_id)
    {
        $data = get_post_meta($post_id, '_drclubs_testimonial_key', true);

        $name = isset($data['name']) ? $data['name'] : '';
        $email = isset($data['email']) ? $data['email'] : '';
        $approved = isset($data['approved']) && $data['approved'] == 1 ? '<strong>تایید</strong>' : '-';

        switch ($column) {
            case 'name':
                echo '<strong>' . $name . '</strong><br/><a href="mailto:' . $email . '">' . $email . '</a>';
                break;
            case 'approved':
                echo $approved;
                break;
        }

    }

    public function setCustomColumnsSortable($columns)
    {

        $columns['name'] = 'name';
        $columns['approved'] = 'approved';

        return $columns;
    }

    public function setShortcodePage()
    {
        $subPage = [
            [
                'parent_slug' => 'edit.php?post_type=testimonial',
                'page_title' => 'شورت کد',
                'menu_title' => 'شورت کد',
                'capability' => 'manage_options',
                'menu_slug' => 'drclubs_testimonial_shortcode',
                'callback' => [$this->callbacks, 'shortcodePage']


            ]
        ];

        $this->settings->addSubPages($subPage)->register();
    }

    public function testimonialForm()
    {
        ob_start();
        echo "<link rel=\"stylesheet\" href=\"$this->plugin_url/assets/form.css\" type=\"text/css\" media=\"all\" />";

        require_once("$this->plugin_path/templates/contact-form.php");
        echo "<script src=\"$this->plugin_url/assets/form.js\" ></script>";
        return ob_get_clean();


    }

    public function submitTestimonial()
    {
        if (!DOING_AJAX || !check_ajax_referer('testimonial-nonce', 'nonce')) {
            wp_send_json(['status' => 'error']);
            wp_die();
        }


        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $message = sanitize_textarea_field($_POST['message']);
        $data = [
            'name' => $name,
            'email' => $email,
            'approved' => 0,
            'featured' => 0
        ];

        $args = [
            'post_title' => ' پیام از طرف ' . $name,
            'post_content' => $message,
            'post_author' => 1,
            'post_status' => 'publish',
            'post_type' => 'testimonial',
            'meta_input' => [
                '_drclubs_testimonial_key' => $data
            ]
        ];

        $postID = wp_insert_post($args);
        //success
        if ($postID) {
            $return = [
                'status' => 'success',
                'ID' => $postID
            ];

            wp_send_json($return);
            wp_die();
        }

        //error

        wp_send_json([
            'status' => 'error'
        ]);
        wp_die();
    }


    public function testimonialSlideshow()
    {
        ob_start();
        echo "<link rel=\"stylesheet\" href=\"$this->plugin_url/assets/slider.css\" type=\"text/css\" media=\"all\" />";

        require_once("$this->plugin_path/templates/slider.php");
        echo "<script src=\"$this->plugin_url/assets/slider.js\" ></script>";
        return ob_get_clean();


    }
}