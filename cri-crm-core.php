<?php

/**
 * Plugin Name: CRI CRM Core
 * Description: Core CRM functionalities for CRI Venezia (Chat, Admin, Fundraising) - Replaces external Cloudflare Worker.
 * Version: 1.0.0
 * Author: CRI Venezia
 * Text Domain: cri-crm
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define Constants
define('CRI_CRM_PATH', plugin_dir_path(__FILE__));
define('CRI_CRM_URL', plugin_dir_url(__FILE__));

// Initialize Plugin
function cri_crm_init()
{
    // Load Includes
    require_once CRI_CRM_PATH . 'includes/class-db.php';
    require_once CRI_CRM_PATH . 'includes/class-api.php';
    require_once CRI_CRM_PATH . 'includes/class-assets.php';
    require_once CRI_CRM_PATH . 'includes/class-user-fields.php';
    require_once CRI_CRM_PATH . 'includes/elementor/class-elementor-init.php';
    require_once CRI_CRM_PATH . 'includes/class-roles.php';
    require_once CRI_CRM_PATH . 'includes/class-importer.php';

    // Admin Dashboard
    require_once CRI_CRM_PATH . 'includes/admin/class-dashboard.php';

    // Initialize Components
    $db = new CRI_CRM_DB();
    $api = new CRI_CRM_API();
    $assets = new CRI_CRM_Assets();
    $user_fields = new CRI_CRM_User_Fields();
    $importer = new CRI_CRM_Importer();
    $admin = new CRI_CRM_Admin();
    // Elementor is static init via hooks in its class

}
add_action('plugins_loaded', 'cri_crm_init');

// Activation Hooks
register_activation_hook(__FILE__, array('CRI_CRM_DB', 'install'));
register_activation_hook(__FILE__, array('CRI_CRM_Roles', 'create_roles'));
register_deactivation_hook(__FILE__, array('CRI_CRM_Roles', 'remove_roles'));
