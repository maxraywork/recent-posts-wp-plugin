<?php
/*
Plugin Name:  Recent posts Max Ray
Plugin URI:   https://www.test.com
Description:  blank
Version:      1.0
Author:       Max Ray
Author URI:   https://github.com/maxraywork
License:      Test
License URI:  https://www.test.com
Text Domain:  Test
Domain Path:  /.
*/

//rp - Recent Posts
function rp_settings_init() {
    register_setting('rp', 'rp_options');

    add_settings_field(
        'rp_field_number_posts',
        __('Number of Posts', 'rp'),
        'rp_field_number_posts_cb',
        'rp',
        'default',
        array(
            'label_for' => 'rp_field_number_posts',
        )
    );
}
add_action('admin_init', 'rp_settings_init');

function rp_field_number_posts_cb($args) {
    $options = get_option('rp_options');
    ?>
    <input type="number" id="<?php echo esc_attr($args['label_for']); ?>" 
           name="rp_options[<?php echo esc_attr($args['label_for']); ?>]" 
           value="<?php echo isset($options[$args['label_for']]) ? esc_attr($options[$args['label_for']]) : '10'; ?>" />
    <?php
}

function rp_options_page() {
    add_menu_page(
        'Recent Posts Settings',
        'Recent Posts',
        'manage_options',
        'rp-settings',
        'rp_options_page_html'
    );
}
add_action('admin_menu', 'rp_options_page');

function rp_options_page_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    if (isset($_GET['settings-updated'])) {
        add_settings_error('rp_messages', 'rp_message', __('Settings Saved', 'rp'), 'updated');
    }

    settings_errors('rp_messages');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('rp');
            do_settings_fields('rp', 'default');
            submit_button(__('Save Settings', 'rp'));
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_menu', 'rp_options_page');

function rp_show_posts($content) {
    $options = get_option('rp_options');
    $numberposts = isset($options['rp_field_number_posts']) ? intval($options['rp_field_number_posts']) : 10;

    $args = array(
        'numberposts' => $numberposts
    );
    $my_posts = get_posts($args);

    if (!empty($my_posts)) {
        $result = '<ul>';
        foreach ($my_posts as $post) {
            setup_postdata($post);
            $post_permalink = esc_url(get_permalink($post->ID));
            $post_title = esc_html($post->post_title);
            $post_content = esc_html(wp_trim_words($post->post_content, 100));

            $result .= '<li><a href="' . $post_permalink . '">'
                . $post_title . '</a><br><div><p>' . $post_content . '</p></div></li>';
        }
        wp_reset_postdata();
        $result .= '</ul>';
    }

    return $result ?? '<div>Sorry, no posts were found :(</div>';
}

function rp_shortcode_init() {
    add_shortcode('recent-posts', 'rp_show_posts');
}
add_action('init', 'rp_shortcode_init');
?>
