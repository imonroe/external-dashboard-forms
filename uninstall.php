<?php
/**
 * Uninstall External Dashboard Form
 *
 * @package External Dashboard Form
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete plugin options
delete_option('external_dashboard_forms');