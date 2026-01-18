<?php

namespace CRICRM\Widgets;

use Elementor\Widget_Base;

if (!defined('ABSPATH')) {
    exit;
}

class Widget_Bio extends Widget_Base
{

    public function get_name()
    {
        return 'cricrm_bio';
    }

    public function get_title()
    {
        return __('CRI Anagrafica Volontario', 'cri-crm');
    }

    public function get_icon()
    {
        return 'eicon-person';
    }

    public function get_categories()
    {
        return ['cri_category'];
    }

    protected function render()
    {
        $user_id = get_current_user_id();
        if (!$user_id) {
            echo '<p>' . __('Effettua il login per vedere i tuoi dati.', 'cri-crm') . '</p>';
            return;
        }

        $user_info = get_userdata($user_id);
        $sede = get_user_meta($user_id, 'sede', true) ?: 'N/A';
        $cf = get_user_meta($user_id, 'codice_fiscale', true) ?: 'N/A';

        // Get User Roles translated
        $roles = $user_info->roles;
        $role_labels = [];
        global $wp_roles;
        foreach ($roles as $role) {
            $role_labels[] = isset($wp_roles->roles[$role]['name']) ? translate_user_role($wp_roles->roles[$role]['name']) : $role;
        }
        $role_str = implode(', ', $role_labels);

        $avatar_url = get_avatar_url($user_id, ['size' => 96]);

?>
        <div class="cricrm-bio-card" style="background: white; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); overflow: hidden; max-width: 400px; font-family: 'Inter', sans-serif;">
            <div class="bio-header" style="background: #CC0000; height: 80px; position: relative;">
                <div class="bio-avatar" style="width: 80px; height: 80px; border-radius: 50%; border: 4px solid white; position: absolute; bottom: -40px; left: 20px; background-image: url('<?php echo esc_url($avatar_url); ?>'); background-size: cover; background-position: center;"></div>
            </div>
            <div class="bio-body" style="padding: 50px 20px 20px 20px;">
                <h3 style="margin: 0; font-size: 1.25rem; color: #333; font-weight: 700;"><?php echo esc_html($user_info->display_name); ?></h3>
                <p style="margin: 5px 0 15px 0; color: #666; font-size: 0.9rem;"><?php echo esc_html($user_info->user_email); ?></p>

                <div class="bio-details" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                    <div>
                        <span style="display: block; font-size: 0.75rem; text-transform: uppercase; color: #999; font-weight: 600;"><?php _e('Sede', 'cri-crm'); ?></span>
                        <span style="font-weight: 500; color: #333;"><?php echo esc_html($sede); ?></span>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.75rem; text-transform: uppercase; color: #999; font-weight: 600;"><?php _e('Ruolo', 'cri-crm'); ?></span>
                        <span style="font-weight: 500; color: #333; font-size: 0.85rem;"><?php echo esc_html($role_str); ?></span>
                    </div>
                </div>

                <div style="margin-top: 15px; background: #f9f9f9; padding: 10px; border-radius: 4px;">
                    <span style="display: block; font-size: 0.75rem; text-transform: uppercase; color: #999; font-weight: 600;">Codice Fiscale</span>
                    <span style="font-family: monospace; color: #555;"><?php echo esc_html($cf); ?></span>
                </div>

            </div>
        </div>
<?php
    }
}
