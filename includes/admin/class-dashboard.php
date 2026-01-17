<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_Admin
{

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_pages'));
    }

    public function add_menu_pages()
    {
        add_menu_page(
            __('CRI CRM', 'cri-crm'),
            __('CRI CRM', 'cri-crm'),
            'manage_options',
            'cricrm-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-heart',
            2
        );

        add_submenu_page(
            'cricrm-dashboard',
            __('Dashboard', 'cri-crm'),
            __('Dashboard', 'cri-crm'),
            'manage_options',
            'cricrm-dashboard',
            array($this, 'render_dashboard')
        );
    }

    public function render_dashboard()
    {
        include CRI_CRM_PATH . 'templates/admin/dashboard.php';
    }
}
