<!-- Wrappers to scope Tailwind and prevent bleeding -->
<div class="wrap cricrm-admin-wrapper">
    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 max-w-7xl mx-auto mt-5">

        <?php
        $current_user = wp_get_current_user();
        $is_admin = current_user_can('manage_options');
        $is_newsletter = current_user_can('manage_newsletter');
        $is_fundraiser = current_user_can('manage_fundraising');

        // --- COMMON HEADER ---
        ?>
        <div class="border-b border-gray-200 pb-5 mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-[#CC0000] flex items-center gap-2">
                    <span class="dashicons dashicons-heart text-4xl"></span>
                    CRI Venezia - CRM
                </h1>
                <p class="text-gray-500 mt-1">
                    <?php
                    if ($is_admin) echo "Pannello di Controllo Operativo";
                    elseif ($is_newsletter) echo "Workspace Newsletter";
                    elseif ($is_fundraiser) echo "Workspace Fundraising";
                    else echo "Area Personale";
                    ?>
                </p>
            </div>
            <div class="flex flex-col items-end">
                <span class="bg-gray-100 px-3 py-1 rounded-full font-mono text-xs text-gray-600 mb-2">v<?php echo CRI_CRM_VERSION; ?></span>
                <span class="text-sm font-medium text-gray-700">Ciao, <?php echo esc_html($current_user->display_name); ?></span>
            </div>
        </div>

        <!-- === OVERVIEW CARD GRID === -->
        <div id="tab-overview" class="cricrm-tab-content">
            <!-- Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <!-- Card 1: Users -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded shadow-sm">
                    <h3 class="font-bold text-gray-700 text-lg mb-1">Volontari Attivi</h3>
                    <p class="text-3xl font-bold text-blue-600">
                        <?php
                        $user_count = count_users();
                        echo $user_count['total_users'];
                        ?>
                    </p>
                    <a href="<?php echo admin_url('admin.php?page=cricrm-registry'); ?>" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Gestisci Utenti &rarr;</a>
                </div>



                <!-- Card 3: Quick Actions -->
                <div class="bg-red-50 border-l-4 border-[#CC0000] p-4 rounded shadow-sm">
                    <h3 class="font-bold text-gray-700 text-lg mb-1">Azioni Rapide</h3>
                    <div class="flex flex-col gap-2 mt-2">
                        <?php
                        // Deprecated frontend links removed.
                        // Todo: Add new shortcuts if needed.
                        ?>
                        <div class="border-t border-red-200 my-1"></div>
                        <a href="<?php echo admin_url('admin.php?page=cricrm-dashboard&cricrm_action=import_logs'); ?>" onclick="return confirm('Importare i log da JSON? Assicurati che il file backup/chat_logs.json esista.')" class="text-xs text-gray-500 hover:text-red-600 text-center">
                            ♻️ Importa Backup Log
                        </a>
                    </div>
                </div>
            </div>


        </div>

        <!-- SETTINGS -->
        <?php if ($is_admin) : ?>
            <div class="mt-8 pt-8 border-t border-gray-200">
                <div class="bg-white border rounded-lg overflow-hidden shadow-sm">
                    <div class="bg-gray-50 px-4 py-3 border-b">
                        <h3 class="font-bold text-gray-700">Configurazione API (Admin)</h3>
                    </div>
                    <div class="p-6">
                        <form method="post" action="options.php">
                            <?php settings_fields('cricrm_options_group'); ?>
                            <?php do_settings_sections('cricrm_options_group'); ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="mb-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Chiave Attivazione Erika</label>
                                        <input type="password" name="cri_crm_gemini_key" value="<?php echo esc_attr(get_option('cri_crm_gemini_key')); ?>" class="w-full p-2 border rounded focus:ring-red-500 focus:border-red-500">
                                        <p class="text-xs text-gray-500 mt-1">Inserisci il codice di attivazione per abilitare l'intelligenza di Erika.</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Brevo API Key</label>
                                    <input type="password" name="cri_crm_brevo_key" value="<?php echo esc_attr(get_option('cri_crm_brevo_key')); ?>" class="w-full p-2 border rounded focus:ring-red-500 focus:border-red-500">
                                </div>
                            </div>

                            <div class="mt-4">
                                <?php submit_button('Salva Configurazioni'); ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>