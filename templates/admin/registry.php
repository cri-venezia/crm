<div class="wrap cricrm-admin-wrapper">
    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 max-w-7xl mx-auto mt-5">
        <h1 class="text-3xl font-bold text-gray-800 mb-6 flex items-center gap-2">
            <span class="dashicons dashicons-groups"></span> Gestione Volontari
        </h1>

        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b flex flex-col md:flex-row justify-between items-center">
                <h3 class="font-bold text-gray-700 text-lg">👥 Elenco Volontari</h3>
                <input type="text" id="reg-search" class="text-sm border rounded p-2 w-full md:w-64 mt-2 md:mt-0" placeholder="Cerca volontario...">
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-xs text-gray-500 uppercase border-b">
                            <th class="p-4 font-bold">Volontari</th>
                            <th class="p-4 font-bold">Tags / Qualifiche</th>
                            <th class="p-4 font-bold">Accesso Moduli</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100" id="reg-table-body">
                        <?php
                        $users = get_users(['orderby' => 'display_name']); // Get all users
                        foreach ($users as $u) :
                            $tags = get_user_meta($u->ID, 'cri_user_tags', true);
                            $sede = get_user_meta($u->ID, 'sede', true);
                            $has_nl = in_array('cri_newsletter', $u->roles);
                            $has_fr = in_array('cri_fundraiser', $u->roles);
                            // Manager check slightly complex if secondary role, but let's assume direct check
                            $has_mgr = in_array('cri_manager', $u->roles);
                        ?>
                            <tr class="hover:bg-gray-50 reg-row transition" data-search="<?php echo esc_attr(strtolower($u->display_name . ' ' . $u->user_email . ' ' . $tags)); ?>">
                                <td class="p-4 flex items-center gap-3">
                                    <img src="<?php echo get_avatar_url($u->ID, ['size' => 40]); ?>" class="rounded-full w-10 h-10 border border-gray-200">
                                    <div>
                                        <div class="font-bold text-gray-800"><?php echo esc_html($u->display_name); ?></div>
                                        <div class="text-xs text-gray-500"><?php echo esc_html($u->user_email); ?></div>
                                        <?php if ($sede) : ?>
                                            <div class="text-[10px] text-gray-400 mt-1 uppercase tracking-wide">📍 <?php echo esc_html($sede); ?></div>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex items-center gap-2">
                                        <input type="text"
                                            class="reg-tags-input text-sm border-gray-300 rounded focus:ring-red-500 focus:border-red-500 w-full max-w-xs"
                                            value="<?php echo esc_attr($tags); ?>"
                                            placeholder="Es. Autista, TSSA..."
                                            data-uid="<?php echo $u->ID; ?>">
                                        <button type="button" class="reg-save-tags text-gray-400 hover:text-green-600 transition" title="Salva Tags">
                                            <span class="dashicons dashicons-saved"></span>
                                        </button>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <div class="flex flex-col gap-2">
                                        <label class="inline-flex items-center text-xs text-gray-700 cursor-pointer">
                                            <input type="checkbox" class="reg-role-toggle rounded text-red-600 focus:ring-red-500 mr-2"
                                                data-uid="<?php echo $u->ID; ?>" data-role="cri_newsletter"
                                                <?php checked($has_nl, true); ?>>
                                            Newsletter
                                        </label>
                                        <label class="inline-flex items-center text-xs text-gray-700 cursor-pointer">
                                            <input type="checkbox" class="reg-role-toggle rounded text-blue-600 focus:ring-blue-500 mr-2"
                                                data-uid="<?php echo $u->ID; ?>" data-role="cri_fundraiser"
                                                <?php checked($has_fr, true); ?>>
                                            Fundraising
                                        </label>
                                        <label class="inline-flex items-center text-xs text-gray-700 cursor-pointer">
                                            <input type="checkbox" class="reg-role-toggle rounded text-purple-600 focus:ring-purple-500 mr-2"
                                                data-uid="<?php echo $u->ID; ?>" data-role="cri_manager"
                                                <?php checked($has_mgr, true); ?>>
                                            Manager
                                        </label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                // Search Filter
                $('#reg-search').on('input', function() {
                    const val = $(this).val().toLowerCase();
                    $('#reg-table-body .reg-row').each(function() {
                        const txt = $(this).data('search');
                        $(this).toggle(txt.indexOf(val) > -1);
                    });
                });

                // Save Tags
                $('.reg-tags-input').on('change', function() {
                    const input = $(this);
                    const uid = input.data('uid');
                    const tags = input.val();
                    const btn = input.next('.reg-save-tags');

                    // Visual indication of saving
                    input.addClass('bg-yellow-50');

                    $.ajax({
                        url: '<?php echo esc_url_raw(rest_url('cricrm/v1/user/update-tags')); ?>',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({
                            user_id: uid,
                            tags: tags
                        }),
                        success: function() {
                            input.removeClass('bg-yellow-50').addClass('bg-green-50');
                            setTimeout(() => input.removeClass('bg-green-50'), 1000);
                        },
                        error: function() {
                            alert('Errore salvataggio tags');
                            input.removeClass('bg-yellow-50').addClass('bg-red-50');
                        }
                    });
                });

                // Toggle Role
                $('.reg-role-toggle').on('change', function() {
                    const cb = $(this);
                    const uid = cb.data('uid');
                    const role = cb.data('role');
                    const active = cb.is(':checked');

                    $.ajax({
                        url: '<?php echo esc_url_raw(rest_url('cricrm/v1/user/toggle-role')); ?>',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({
                            user_id: uid,
                            role: role,
                            active: active
                        }),
                        success: function() {
                            // Success cosmetic
                        },
                        error: function(err) {
                            alert('Errore modifica ruolo: ' + (err.responseJSON?.message || 'Server error'));
                            cb.prop('checked', !active); // Revert
                        }
                    });
                });
            });
        </script>
    </div>
</div>