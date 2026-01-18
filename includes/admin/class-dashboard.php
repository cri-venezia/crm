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
    }

    public function register_settings()
    {
        register_setting('cricrm_options_group', 'cri_crm_gemini_key');
        register_setting('cricrm_options_group', 'cri_crm_brevo_key');
        register_setting('cricrm_options_group', 'cri_crm_newsletter_page_id');
        register_setting('cricrm_options_group', 'cri_crm_campaign_page_id');
    }

    public function add_menu_pages()
    {
        add_menu_page(
            __('CRI CRM', 'cri-crm'),
            __('CRI CRM', 'cri-crm'),
            'access_cricrm_admin', // Security: Role-based check
            'cricrm-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-heart',
            2
        );

        add_submenu_page(
            'cricrm-dashboard',
            __('Dashboard', 'cri-crm'),
            __('Dashboard', 'cri-crm'),
            'access_cricrm_admin', // Security: Role-based check
            'cricrm-dashboard',
            array($this, 'render_dashboard')
        );
    }

    public function render_dashboard()
    {
        include CRI_CRM_PATH . 'templates/admin/dashboard.php';
    }
}
