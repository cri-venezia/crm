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
                    <!-- Custom CRM Icon -->
                    <svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-[#CC0000]">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M15 11C15 10.4477 15.4477 10 16 10C16.5523 10 17 10.4477 17 11V12H19C20.6569 12 22 13.3431 22 15C22 16.6569 20.6569 18 19 18H17V19H21V21H17V24H21.5857L26.0624 19.5232C24.4384 17.0772 24.5152 13.8352 26.2927 11.4643C26.4906 11.2004 26.7096 10.9472 26.9496 10.7071C27.1897 10.467 27.4429 10.248 27.7069 10.0501L31.8995 14.2428L32.8494 13.293C33.2399 12.9025 33.8731 12.9025 34.2636 13.293C34.6541 13.6836 34.6541 14.3167 34.2636 14.7073L33.3137 15.657L37.5061 19.8494C37.3082 20.1133 37.0892 20.3665 36.8491 20.6066C36.6091 20.8467 36.3559 21.0657 36.0919 21.2636C34.0221 22.8153 31.2884 23.0709 29 22.0302V24H42V26H40V40H41C41.5523 40 42 40.4477 42 41C42 41.5523 41.5523 42 41 42H7C6.44772 42 6 41.5523 6 41C6 40.4477 6.44772 40 7 40H8V26H6V24H15V21H11V19H15V18H13C11.3431 18 10 16.6569 10 15C10 13.3431 11.3431 12 13 12H15V11ZM27 21.4141L24.4141 24H27V21.4141ZM31 40H36V32H31V40ZM13 14H15V16H13C12.4477 16 12 15.5523 12 15C12 14.4477 12.4477 14 13 14ZM19 14H17V16H19C19.5523 16 20 15.5523 20 15C20 14.4477 19.5523 14 19 14ZM34.6571 19.8288C32.7157 21.115 30.0743 20.9028 28.3638 19.1924C26.6534 17.4819 26.4413 14.8405 27.7275 12.8992L34.6571 19.8288ZM17 36V32H12V36H17ZM24 36V32H19V36H24Z" fill="currentColor" />
                    </svg>
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