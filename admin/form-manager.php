<?php
/**
 * Form Manager Functions
 *
 * @package External Dashboard Form
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get all saved forms
 * 
 * @return array Array of forms
 */
function edf_get_forms() {
    return get_option('external_dashboard_forms', array());
}

/**
 * Save a form to the database
 * 
 * @param string $name Form name
 * @param string $url  Form URL
 * @return bool|string Form ID on success, false on failure
 */
function edf_save_form($name, $url) {
    // Validate inputs
    if (empty($name) || empty($url)) {
        return false;
    }
    
    // Ensure URL is secure
    if (strpos($url, 'https://') !== 0) {
        return false;
    }
    
    // Get existing forms
    $forms = edf_get_forms();
    
    // Create unique ID
    $form_id = 'form_' . time();
    
    // Add new form
    $forms[$form_id] = array(
        'id'   => $form_id,
        'name' => sanitize_text_field($name),
        'url'  => esc_url_raw($url)
    );
    
    // Save updated forms
    if (update_option('external_dashboard_forms', $forms)) {
        return $form_id;
    }
    
    return false;
}

/**
 * Update an existing form
 * 
 * @param string $form_id Form ID
 * @param string $name    Form name
 * @param string $url     Form URL
 * @return bool Success or failure
 */
function edf_update_form($form_id, $name, $url) {
    // Validate inputs
    if (empty($form_id) || empty($name) || empty($url)) {
        return false;
    }
    
    // Ensure URL is secure
    if (strpos($url, 'https://') !== 0) {
        return false;
    }
    
    // Get existing forms
    $forms = edf_get_forms();
    
    // Check if form exists
    if (!isset($forms[$form_id])) {
        return false;
    }
    
    // Update form
    $forms[$form_id] = array(
        'id'   => $form_id,
        'name' => sanitize_text_field($name),
        'url'  => esc_url_raw($url)
    );
    
    // Save updated forms
    return update_option('external_dashboard_forms', $forms);
}

/**
 * Delete a form
 * 
 * @param string $form_id Form ID
 * @return bool Success or failure
 */
function edf_delete_form($form_id) {
    // Validate input
    if (empty($form_id)) {
        return false;
    }
    
    // Get existing forms
    $forms = edf_get_forms();
    
    // Check if form exists
    if (!isset($forms[$form_id])) {
        return false;
    }
    
    // Remove form
    unset($forms[$form_id]);
    
    // Save updated forms
    return update_option('external_dashboard_forms', $forms);
}

/**
 * Validate form URL is secure
 * 
 * @param string $url URL to validate
 * @return bool True if URL is secure, false otherwise
 */
function edf_validate_secure_url($url) {
    return (strpos($url, 'https://') === 0);
}

/**
 * Ajax callback for removing a form
 */
function edf_ajax_remove_form() {
    // Check nonce
    check_ajax_referer('edf_ajax_nonce', 'nonce');
    
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'external-dashboard-form')));
    }
    
    // Get form ID
    $form_id = isset($_POST['form_id']) ? sanitize_text_field($_POST['form_id']) : '';
    
    if (empty($form_id)) {
        wp_send_json_error(array('message' => __('Invalid form ID.', 'external-dashboard-form')));
    }
    
    // Remove form
    if (edf_delete_form($form_id)) {
        wp_send_json_success(array('message' => __('Form removed successfully.', 'external-dashboard-form')));
    } else {
        wp_send_json_error(array('message' => __('Failed to remove form.', 'external-dashboard-form')));
    }
}
add_action('wp_ajax_edf_remove_form', 'edf_ajax_remove_form');