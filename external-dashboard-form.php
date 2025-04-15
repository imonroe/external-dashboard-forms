<?php
/**
 * Plugin Name: External Dashboard Forms
 * Plugin URI:
 * Description: Allows administrators to embed custom external forms directly on the WordPress dashboard screen
 * Version: 1.0
 * Author: Monroe Digital
 * Author URI: https://www.monroedigitalconsulting.com/
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: external-dashboard-form
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('EDF_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('EDF_PLUGIN_URL', plugin_dir_url(__FILE__));
define('EDF_PLUGIN_VERSION', '1.0');

// Include admin functions
require_once EDF_PLUGIN_DIR . 'admin/form-manager.php';

/**
 * Initialize the plugin
 */
function edf_init() {
    // Register dashboard widgets for each saved form
    add_action('wp_dashboard_setup', 'edf_setup_dashboard_widgets');

    // Admin menu
    add_action('admin_menu', 'edf_admin_menu');

    // Add admin scripts and styles
    add_action('admin_enqueue_scripts', 'edf_admin_scripts');
}
add_action('plugins_loaded', 'edf_init');

/**
 * Register admin menu
 */
function edf_admin_menu() {
    // Add top level menu
    add_menu_page(
        __('External Forms', 'external-dashboard-form'),
        __('External Forms', 'external-dashboard-form'),
        'manage_options',
        'external-forms',
        'edf_forms_page',
        'dashicons-feedback',
        30
    );

    // Get saved forms
    $forms = get_option('external_dashboard_forms', array());

    // Add submenu items for each form
    if (!empty($forms)) {
        foreach ($forms as $form) {
            add_submenu_page(
                'external-forms',
                $form['name'],
                $form['name'],
                'manage_options',
                'index.php#' . sanitize_title($form['name']),
                ''
            );
        }
    }
}

/**
 * Admin page for managing forms
 */
function edf_forms_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Process form submission
    if (isset($_POST['edf_add_form_nonce']) && wp_verify_nonce($_POST['edf_add_form_nonce'], 'edf_add_form')) {
        // Process form submission
        edf_process_form_submission();
    }

    // Get existing forms
    $forms = get_option('external_dashboard_forms', array());

    // Display the form
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

        <h2><?php _e('Add New Form', 'external-dashboard-form'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('edf_add_form', 'edf_add_form_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="form_name"><?php _e('Form Name', 'external-dashboard-form'); ?></label></th>
                    <td>
                        <input name="form_name" type="text" id="form_name" value="" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="form_url"><?php _e('Form URL', 'external-dashboard-form'); ?></label></th>
                    <td>
                        <input name="form_url" type="url" id="form_url" value="" class="regular-text" required>
                        <p class="description"><?php _e('URL must begin with https://', 'external-dashboard-form'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Add Form', 'external-dashboard-form')); ?>
        </form>

        <?php if (!empty($forms)) : ?>
            <h2><?php _e('Existing Forms', 'external-dashboard-form'); ?></h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Form Name', 'external-dashboard-form'); ?></th>
                        <th><?php _e('Form URL', 'external-dashboard-form'); ?></th>
                        <th><?php _e('Actions', 'external-dashboard-form'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($forms as $form_id => $form) : ?>
                        <tr>
                            <td><?php echo esc_html($form['name']); ?></td>
                            <td><?php echo esc_url($form['url']); ?></td>
                            <td>
                                <a href="<?php echo esc_url(admin_url('index.php#' . sanitize_title($form['name']))); ?>"><?php _e('View on Dashboard', 'external-dashboard-form'); ?></a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Process form submission
 */
function edf_process_form_submission() {
    // Check for required fields
    if (!isset($_POST['form_name']) || !isset($_POST['form_url'])) {
        add_settings_error('external_dashboard_form', 'missing_fields', __('Form name and URL are required.', 'external-dashboard-form'), 'error');
        return;
    }

    $form_name = sanitize_text_field($_POST['form_name']);
    $form_url = esc_url_raw($_POST['form_url']);

    // Validate URL
    if (empty($form_name)) {
        add_settings_error('external_dashboard_form', 'invalid_name', __('Form name cannot be empty.', 'external-dashboard-form'), 'error');
        return;
    }

    // Validate URL starts with https://
    if (strpos($form_url, 'https://') !== 0) {
        add_settings_error('external_dashboard_form', 'invalid_url', __('Form URL must begin with https://.', 'external-dashboard-form'), 'error');
        return;
    }

    // Get existing forms
    $forms = get_option('external_dashboard_forms', array());

    // Generate unique ID
    $form_id = 'form_' . time();

    // Add new form
    $forms[$form_id] = array(
        'id' => $form_id,
        'name' => $form_name,
        'url' => $form_url
    );

    // Save to database
    update_option('external_dashboard_forms', $forms);

    add_settings_error('external_dashboard_form', 'form_added', __('Form added successfully.', 'external-dashboard-form'), 'success');
}

/**
 * Set up dashboard widgets
 */
function edf_setup_dashboard_widgets() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Get saved forms
    $forms = get_option('external_dashboard_forms', array());

    if (!empty($forms)) {
        foreach ($forms as $form_id => $form) {
            wp_add_dashboard_widget(
                sanitize_title($form['name']),
                $form['name'],
                'edf_render_dashboard_widget',
                'edf_dashboard_widget_control',
                array('form_id' => $form_id)
            );
        }
    }
}

/**
 * Render the dashboard widget
 */
function edf_render_dashboard_widget($post, $callback_args) {
    $form_id = $callback_args['args']['form_id'];
    $forms = get_option('external_dashboard_forms', array());

    if (isset($forms[$form_id])) {
        $form = $forms[$form_id];
        echo '<div class="edf-iframe-container">';
        echo '<iframe src="' . esc_url($form['url']) . '" width="100%" height="400" frameborder="0"></iframe>';
        echo '</div>';
    }
}

/**
 * Dashboard widget control callback
 */
function edf_dashboard_widget_control($widget_id, $callback_args) {
    $form_id = $callback_args['args']['form_id'];

    // Process removal if requested
    if (isset($_POST['edf_remove_form']) && $_POST['edf_remove_form'] == $form_id) {
        edf_remove_form($form_id);
        ?>
        <script>
            window.location.reload();
        </script>
        <?php
        return;
    }

    // Show remove form button
    ?>
    <form method="post">
        <input type="hidden" name="edf_remove_form" value="<?php echo esc_attr($form_id); ?>">
        <?php submit_button(__('Remove Form', 'external-dashboard-form'), 'delete', 'edf_remove_form_submit', false); ?>
    </form>
    <?php
}

/**
 * Remove a form from the database
 */
function edf_remove_form($form_id) {
    $forms = get_option('external_dashboard_forms', array());

    if (isset($forms[$form_id])) {
        unset($forms[$form_id]);
        update_option('external_dashboard_forms', $forms);
        return true;
    }

    return false;
}

/**
 * Enqueue admin scripts and styles
 */
function edf_admin_scripts($hook) {
    // Only on dashboard
    if ($hook != 'index.php' && $hook != 'toplevel_page_external-forms') {
        return;
    }

    // Add custom CSS
    wp_enqueue_style('edf-admin-style', EDF_PLUGIN_URL . 'assets/style.css', array(), EDF_PLUGIN_VERSION);
}
