<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_User_Fields
{

    public function __construct()
    {
        add_action('show_user_profile', array($this, 'render_fields'));
        add_action('edit_user_profile', array($this, 'render_fields'));
        add_action('personal_options_update', array($this, 'save_fields'));
        add_action('edit_user_profile_update', array($this, 'save_fields'));
    }

    public function render_fields($user)
    {
        $sede = get_user_meta($user->ID, 'sede', true);
        $cf = get_user_meta($user->ID, 'codice_fiscale', true);
?>
        <h3><?php _e('CRI CRM Extra Info', 'cri-crm'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="sede"><?php _e('Sede (Comitato)', 'cri-crm'); ?></label></th>
                <td>
                    <input type="text" name="sede" id="sede" value="<?php echo esc_attr($sede); ?>" class="regular-text" />
                    <p class="description"><?php _e('Es: Venezia, Mestre, Lido...', 'cri-crm'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="codice_fiscale"><?php _e('Codice Fiscale', 'cri-crm'); ?></label></th>
                <td>
                    <input type="text" name="codice_fiscale" id="codice_fiscale" value="<?php echo esc_attr($cf); ?>" class="regular-text" />
                </td>
            </tr>
            <tr>
                <th><label for="cri_is_volunteer"><?php _e('Anagrafica Volontari', 'cri-crm'); ?></label></th>
                <td>
                    <label>
                        <input type="checkbox" name="cri_is_volunteer" id="cri_is_volunteer" value="1" <?php checked(get_user_meta($user->ID, 'cri_is_volunteer', true), 1); ?> />
                        <?php _e('Mostra questo utente nell\'elenco pubblico dei volontari', 'cri-crm'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <th><label for="cri_user_tags"><?php _e('Qualifiche / Tag', 'cri-crm'); ?></label></th>
                <td>
                    <input type="text" name="cri_user_tags" id="cri_user_tags" value="<?php echo esc_attr(get_user_meta($user->ID, 'cri_user_tags', true)); ?>" class="regular-text" />
                    <p class="description"><?php _e('Separati da virgola. Es: Autista, Soccorritore, Istruttore, TSSA', 'cri-crm'); ?></p>
                </td>
            </tr>
        </table>
<?php
    }

    public function save_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }

        if (isset($_POST['sede'])) {
            update_user_meta($user_id, 'sede', sanitize_text_field($_POST['sede']));
        }

        if (isset($_POST['codice_fiscale'])) {
            update_user_meta($user_id, 'codice_fiscale', strtoupper(sanitize_text_field($_POST['codice_fiscale'])));
        }

        if (isset($_POST['cri_user_tags'])) {
            update_user_meta($user_id, 'cri_user_tags', sanitize_text_field($_POST['cri_user_tags']));
        }

        // Checkbox handling: if not set, delete the meta (false)
        if (isset($_POST['cri_is_volunteer'])) {
            update_user_meta($user_id, 'cri_is_volunteer', 1);
        } else {
            delete_user_meta($user_id, 'cri_is_volunteer');
        }
    }
}
