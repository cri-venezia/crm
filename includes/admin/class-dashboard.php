<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_Admin
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_init', array($this, 'fix_woocommerce_admin_access'), 1);
    }

    public function fix_woocommerce_admin_access()
    {
        add_filter('woocommerce_prevent_admin_access', '__return_false', 100);
    }

    public function register_settings()
    {
        register_setting('cricrm_options_group', 'cri_crm_gemini_key');
        register_setting('cricrm_options_group', 'cri_crm_brevo_key');
    }

    public function add_menu_pages()
    {
        // Main Menu (Dashboard)
        // We use 'read' so everyone sees the menu item, but 'render_dashboard' handles the redirection.
        add_menu_page(
            __('CRI CRM', 'cri-crm'),
            __('CRI CRM', 'cri-crm'),
            'read',
            'cricrm-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-heart',
            2
        );

        // Dashboard (Submenu)
        add_submenu_page(
            'cricrm-dashboard',
            __('Dashboard', 'cri-crm'),
            __('Dashboard', 'cri-crm'),
            'read',
            'cricrm-dashboard',
            array($this, 'render_dashboard')
        );

        // Newsletter (Submenu)
        add_submenu_page(
            'cricrm-dashboard',
            __('Newsletter', 'cri-crm'),
            __('Newsletter', 'cri-crm'),
            'manage_newsletter',
            'cricrm-newsletter',
            array($this, 'render_newsletter')
        );

        // Fundraising (Submenu)
        add_submenu_page(
            'cricrm-dashboard',
            __('Fundraising', 'cri-crm'),
            __('Fundraising', 'cri-crm'),
            'manage_fundraising',
            'cricrm-fundraising',
            array($this, 'render_fundraising')
        );

        // Registry / Anagrafica (Submenu)
        add_submenu_page(
            'cricrm-dashboard',
            __('Anagrafica', 'cri-crm'),
            __('Anagrafica', 'cri-crm'),
            'list_users', // Usually managers have this
            'cricrm-registry',
            array($this, 'render_registry')
        );

        // Erika Chat (Submenu)
        add_submenu_page(
            'cricrm-dashboard',
            __('Erika', 'cri-crm'),
            __('Erika', 'cri-crm'),
            'access_cricrm_admin', // Admins/Managers
            'cricrm-chat',
            array($this, 'render_chat')
        );
    }

    public function render_dashboard()
    {
        // SMART REDIRECT
        // If user is Admin, they see the full Dashboard.
        // If user is NOT Admin, they might need to go to their specific workspace.
        // Priority: Newsletter > Fundraising > Registry.

        if (!current_user_can('manage_options')) {
            // Priority 1: Newsletter
            if (current_user_can('manage_newsletter')) {
                wp_redirect(admin_url('admin.php?page=cricrm-newsletter'));
                exit;
            }
            // Priority 2: Fundraising
            if (current_user_can('manage_fundraising')) {
                wp_redirect(admin_url('admin.php?page=cricrm-fundraising'));
                exit;
            }
            // Priority 3: Manager/Registry
            if (current_user_can('list_users')) {
                wp_redirect(admin_url('admin.php?page=cricrm-registry'));
                exit;
            }
        }

        // Fallback: Show Generic Dashboard (for Admins or Basic Volunteers)
        include CRI_CRM_PATH . 'templates/admin/dashboard.php';
    }

    public function render_newsletter()
    {
        include CRI_CRM_PATH . 'templates/admin/newsletter.php';
    }

    public function render_fundraising()
    {
        include CRI_CRM_PATH . 'templates/admin/fundraising.php';
    }

    public function render_registry()
    {
        include CRI_CRM_PATH . 'templates/admin/registry.php';
    }

    public function render_chat()
    {
        include CRI_CRM_PATH . 'templates/admin/chat.php';
    }
}
