<?php

/**
 * Plugin Name: CRI CRM Core
 * Description: Core CRM functionalities for CRI Venezia (Chat, Admin, Fundraising) - Replaces external Cloudflare Worker.
 * Version: 1.1.1
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
    require_once CRI_CRM_PATH . 'includes/class-cpt.php';

    // Admin Dashboard
    require_once CRI_CRM_PATH . 'includes/admin/class-dashboard.php';

    // Initialize Components
    $db = new CRI_CRM_DB();
    $api = new CRI_CRM_API();
    $assets = new CRI_CRM_Assets();
    $user_fields = new CRI_CRM_User_Fields();
    $importer = new CRI_CRM_Importer();
    $cpt = new CRI_CRM_CPT();
    $admin = new CRI_CRM_Admin();
    // Elementor is static init via hooks in its class

}
add_action('plugins_loaded', 'cri_crm_init');

// Activation Hooks
function cri_crm_activate()
{
    // Ensure classes are loaded before calling them
    require_once CRI_CRM_PATH . 'includes/class-db.php';
    require_once CRI_CRM_PATH . 'includes/class-roles.php';

    CRI_CRM_DB::install();
    CRI_CRM_Roles::create_roles();
}
register_activation_hook(__FILE__, 'cri_crm_activate');

function cri_crm_deactivate()
{
    require_once CRI_CRM_PATH . 'includes/class-roles.php';
    CRI_CRM_Roles::remove_roles();
}
register_deactivation_hook(__FILE__, 'cri_crm_deactivate');
