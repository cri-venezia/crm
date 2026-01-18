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

        // Restrict Menu Items
        add_action('admin_menu', array(__CLASS__, 'restrict_menu_items'), 999);
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

        // Newsletter Role
        add_role(
            'cri_newsletter',
            __('CRI Newsletter', 'cri-crm'),
            array(
                'read' => true,
                'access_cricrm' => true, // Base access
                'access_cricrm_admin' => true,
                'manage_newsletter' => true,
            )
        );

        // Fundraising Role
        add_role(
            'cri_fundraiser',
            __('CRI Fundraising', 'cri-crm'),
            array(
                'read' => true,
                'access_cricrm' => true, // Base access
                'access_cricrm_admin' => true,
                'manage_fundraising' => true,
            )
        );

        // Add capabilities to Administrator
        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('access_cricrm');
            $admin->add_cap('access_cricrm_admin');
            $admin->add_cap('manage_newsletter');
            $admin->add_cap('manage_fundraising');
        }
    }

    public static function restrict_menu_items()
    {
        if (current_user_can('administrator')) {
            return;
        }

        $user = wp_get_current_user();
        $restricted_roles = ['cri_volunteer', 'cri_newsletter', 'cri_fundraiser', 'cri_manager'];

        $is_restricted = false;
        foreach ($restricted_roles as $role) {
            if (in_array($role, $user->roles)) {
                $is_restricted = true;
                break;
            }
        }

        if ($is_restricted) {
            // Remove Standard WP Menus
            remove_menu_page('index.php'); // Dashboard
            remove_menu_page('jet-dashboard'); // JetEngine/Elementor stuff usually
            remove_menu_page('edit.php'); // Posts
            remove_menu_page('upload.php'); // Media
            remove_menu_page('edit.php?post_type=page'); // Pages
            remove_menu_page('edit-comments.php'); // Comments
            remove_menu_page('themes.php'); // Appearance
            remove_menu_page('plugins.php'); // Plugins
            remove_menu_page('users.php'); // Users
            remove_menu_page('tools.php'); // Tools
            remove_menu_page('options-general.php'); // Settings

            // Note: 'cricrm-dashboard' and 'cri_crm_page' will remain visible if they have the right capability mapped
        }
    }

    /**
     * Remove roles on Deactivation
     */
    public static function remove_roles()
    {
        remove_role('cri_volunteer');
        remove_role('cri_manager');
        remove_role('cri_newsletter');
        remove_role('cri_fundraiser');

        // Clean admin caps? Ideally yes, but often kept to avoid breaking permissions if re-enabled.
    }
}
