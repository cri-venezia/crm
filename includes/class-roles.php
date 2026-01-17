<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_Roles
{

    public static function init()
    {
        // Prevent WooCommerce from blocking admin access for our custom roles
        add_filter('woocommerce_prevent_admin_access', '__return_false');
    }

    /**
     * Create custom roles on Plugin Activation
     */
    public static function create_roles()
    {
        // Volunteer Role
        add_role(
            'cri_volunteer',
            __('Volontario CRI', 'cri-crm'),
            array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
                'access_cricrm' => true, // Custom capability
            )
        );

        // Manager Role (Can manage users/groups)
        add_role(
            'cri_manager',
            __('CRI Manager', 'cri-crm'),
            array(
                'read' => true,
                'list_users' => true,
                'create_users' => true,
                'promote_users' => true,
                'edit_users' => true,
                'access_cricrm_admin' => true,
            )
        );

        // Add capabilities to Administrator
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('access_cricrm');
            $admin->add_cap('access_cricrm_admin');
        }
    }

    /**
     * Remove roles on Deactivation
     */
    public static function remove_roles()
    {
        remove_role('cri_volunteer');
        remove_role('cri_manager');

        // Clean admin caps? Ideally yes, but often kept to avoid breaking permissions if re-enabled.
    }
}
