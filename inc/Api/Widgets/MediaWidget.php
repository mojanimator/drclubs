<?php
/**
 * @package drclubs
 */

namespace DrClubs\Api\Widgets;


use WP_Widget;

class  MediaWidget extends WP_Widget
{

    public $widget_ID;
    public $widget_name;
    public $widget_options = [];
    public $control_options = [];

    public function __construct()
    {


        $this->widget_ID = 'drclubs_media_widget';
        $this->widget_name = 'ویجت دکتر کلابز';
        $this->widget_options = [
            'classname' => $this->widget_ID,
            'description' => $this->widget_name,
            'customize_selective_refresh' => true, //only widget refresh on edit
        ];

        $this->control_options = [
            'width' => 400,
            'height' => 350,
        ];
        parent::__construct($this->widget_ID, $this->widget_name, $this->widget_options, $this->control_options);
    }

    public function register()
    {


        add_action('widgets_init', [$this, 'widgetInit']);
    }

    public function widgetInit()
    {

        register_sidebar(array(
            'name' => __('دکتر کلابز', 'customtheme'),
            'id' => 'title',
            'description' => __('Add widgets here to appear in your sidebar.', 'customtheme'),
            'before_widget' => '<section id="%1$s" class="widget %2$s">',
            'after_widget' => '</section>',
            'before_title' => '<h2 class="widget-title">',
            'after_title' => '</h2>',
        ));


        register_widget($this);
    }

    public function form($instance)
    {


        $title = !empty($instance['title']) ? $instance['title'] : esc_html__('متن پیشفرض ویجت');
        $image = !empty($instance['image']) ? $instance['image'] : '';
        $titleID = esc_attr($this->get_field_id('title'));
        $imageID = esc_attr($this->get_field_id('image'));
        ?>
        <p>
            <label for="<?php echo $titleID ?>">عنوان</label>
            <input type="text" class="widefat" id="<?php echo $titleID ?>"
                   name="<?php esc_attr($this->get_field_name('title')); ?>"
                   value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $titleID ?>"><?php esc_attr_e('تصویر', 'awps') ?></label>
            <input type="text" class="widefat image-upload" id="<?php echo $imageID ?>"
                   name="<?php esc_attr($this->get_field_name('image')); ?>"
                   value="<?php echo esc_url($image); ?>">
            <button type="submit" class="button button-primary js-image-upload">انتخاب تصویر</button>
        </p>
        <?php
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title') . $args['after_title'];
        }
        if (!empty($instance['image'])) {
            echo '<img src="' . esc_url($instance['image']) . '" >';
        }
        echo $args['after_widget'];
    }


    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['image'] = !empty($new_instance['image']) ? $new_instance['image'] : '';

        return $instance;

    }
}