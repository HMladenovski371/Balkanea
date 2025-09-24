<?php
/**
 * Plugin Name: Balkanea Ratehawk CRUD
 * Description: Custom CRUD operations for Ratehawk integration
 * Version: 1.0.0
 * Author: Hristijan Mladenovski
 * Text Domain: balkanea-ratehawk-crud
 */

if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('BALKANEA_RATEHAWK_CRUD_VERSION', '1.0.0');
define('BALKANEA_RATEHAWK_CRUD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BALKANEA_RATEHAWK_CRUD_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include admin class
require_once BALKANEA_RATEHAWK_CRUD_PLUGIN_DIR . 'Admin/ratehawkAdmin.php';

// Activation hook
register_activation_hook(__FILE__, 'balkanea_ratehawk_crud_activate');
function balkanea_ratehawk_crud_activate() {
    // Activation code here
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'balkanea_ratehawk_crud_deactivate');
function balkanea_ratehawk_crud_deactivate() {
    // Deactivation code here
}