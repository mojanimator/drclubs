<div class="wrap">

    <h1>تنظیمات نوع داده دکتر کلابز</h1>

    <?php settings_errors(); ?>
    <?php $editMode = isset($_POST["edit_post"]) ?>
    <ul class="nav nav-tabs">
        <li class="<?php echo($editMode ? '' : 'active') ?>"><a href="#tab-1">لیست انواع داده</a></li>
        <li class="<?php echo(!$editMode ? '' : 'active') ?>"><a href="#tab-2">ساخت/ویرایش نوع داده</a></li>
        <li class=""><a href="#tab-3">Import/Export</a></li>

    </ul>

    <div class="tab-content">
        <div id="tab-1" class="tab-pane <?php echo($editMode ? '' : ' active ') ?>">

            <?php

            $options = get_option(\DrClubs\Base\CustomPostTypeController::$option_name);
            echo "<table class='cpt-table'><tr><th>ID</th><th>نام</th><th>نام جمع</th><th>عمومی</th><th>آرشیو</th></tr>";
            if (is_array($options))
                foreach ($options as $option) {
                    $option['public'] = isset($option['public']) ? ($option['public'] ? '+' : '-') : '-';
                    $option['has_archive'] = isset($option['has_archive']) ? ($option['has_archive'] ? '+' : '-') : '-';
                    echo
                    "<tr>
                <td>{$option['post_type']}</td>
                <td>{$option['singular_name']}</td>
                <td>{$option['plural_name']}</td>
                <td>{$option['public']}</td>
                <td>{$option['has_archive']}</td>
                <td class='text-center'>";

                    echo '<form method="post" action="" class="inline-block">';
                    echo '<input type="hidden" name="edit_post" value="' . $option['post_type'] . '">';
                    submit_button('ویرایش', 'primary small', 'submit', false);
                    echo '</form>';

                    echo '  ';

                    echo '<form method="post" action="options.php" class="inline-block">';
                    settings_fields(\DrClubs\Base\CustomPostTypeController::$option_name . '_settings');
                    echo '<input type="hidden" name="remove" value="' . $option['post_type'] . '">';
                    submit_button('حذف', 'delete small', 'submit', false, [
                        'onclick' => 'return confirm("از حذف اطمینان دارید؟");'
                    ]);
                    echo '</form></td></tr>';
                }
            echo '</table>';
            ?>

        </div>
        <div id="tab-2" class="tab-pane  <?php echo(!$editMode ? '' : ' active ') ?>">
            <form method="post" action="options.php">
                <?php

                settings_fields('drclubs_plugin_cpt_settings');
                do_settings_sections(\DrClubs\Base\CustomPostTypeController::$option_name);
                submit_button();
                ?>
            </form>
        </div>
        <d
            <?php foreach ($options

            as $option) { ?>
                iv id="tab-3" class="tab-pane  ">
            <h3><?php echo $option['singular_name'] ?></h3>
            <pre class="prettyprint">
// Register Custom Post Type
function custom_post_type() {

	$labels = array(
		'name'                  => _x( '<?php echo $option['plural_name'] ?>', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( '<?php echo $option['singular_name'] ?>', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Post Types', 'text_domain' ),
		'name_admin_bar'        => __( 'Post Type', 'text_domain' ),
		'archives'              => __( 'Item Archives', 'text_domain' ),
		'attributes'            => __( 'Item Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
		'all_items'             => __( 'All Items', 'text_domain' ),
		'add_new_item'          => __( 'Add New Item', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Item', 'text_domain' ),
		'edit_item'             => __( 'Edit Item', 'text_domain' ),
		'update_item'           => __( 'Update Item', 'text_domain' ),
		'view_item'             => __( 'View Item', 'text_domain' ),
		'view_items'            => __( 'View Items', 'text_domain' ),
		'search_items'          => __( 'Search Item', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
		'items_list'            => __( 'Items list', 'text_domain' ),
		'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Post Type', 'text_domain' ),
		'description'           => __( 'Post Type Description', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => false,
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	);
	register_post_type( '<?php echo $option['post_type']; ?>', $args );

}
add_action( 'init', 'custom_post_type', 0 );
            </pre>

            <?php } ?>
    </div>
</div>


</div>
