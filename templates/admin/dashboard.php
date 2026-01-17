<!-- Wrappers to scope Tailwind and prevent bleeding -->
<div class="wrap cricrm-admin-wrapper">
    <div class="bg-white rounded-lg shadow-md p-6 max-w-7xl mx-auto mt-5">

        <!-- Header -->
        <div class="border-b border-gray-200 pb-5 mb-5 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-[#CC0000] flex items-center gap-2">
                    <span class="dashicons dashicons-heart text-4xl"></span>
                    CRI Venezia - CRM
                </h1>
                <p class="text-gray-500 mt-1">Pannello di Controllo Operativo</p>
            </div>
            <div class="bg-gray-100 px-4 py-2 rounded-md font-mono text-sm">
                Versione: 1.0.0
            </div>
        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Card 1: Users -->
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded shadow-sm">
                <h3 class="font-bold text-gray-700 text-lg mb-1">Volontari Attivi</h3>
                <p class="text-3xl font-bold text-blue-600">
                    <?php
                    $user_count = count_users();
                    echo $user_count['total_users'];
                    ?>
                </p>
                <a href="<?php echo admin_url('users.php'); ?>" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Gestisci Utenti &rarr;</a>
            </div>

            <!-- Card 2: Chat Logs -->
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm">
                <h3 class="font-bold text-gray-700 text-lg mb-1">Conversazioni AI</h3>
                <p class="text-3xl font-bold text-green-600">
                    <?php
                    global $wpdb;
                    echo $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cricrm_chat_logs");
                    ?>
                </p>
                <p class="text-xs text-gray-500">Messaggi scambiati con Erika</p>
            </div>

            <!-- Card 3: Quick Actions -->
            <div class="bg-red-50 border-l-4 border-[#CC0000] p-4 rounded shadow-sm">
                <h3 class="font-bold text-gray-700 text-lg mb-1">Azioni Rapide</h3>
                <div class="flex flex-col gap-2 mt-2">
                    <button class="bg-[#CC0000] text-white px-3 py-1 rounded hover:bg-[#8a0000] text-sm transition text-center">
                        Invia Newsletter
                    </button>
                    <button class="bg-white border border-[#CC0000] text-[#CC0000] px-3 py-1 rounded hover:bg-red-50 text-sm transition text-center">
                        Nuova Campagna
                    </button>
                    <!-- Data Rescue Tool -->
                    <a href="<?php echo admin_url('admin.php?page=cricrm-dashboard&cricrm_action=import_logs'); ?>" onclick="return confirm('Importare i log da JSON? Assicurati che il file backup/chat_logs.json esista.')" class="mt-2 text-xs text-gray-400 hover:text-gray-600 text-center underline">
                        ⚠️ Importa Log da Backup
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Logs Table -->
        <div class="bg-white border rounded-lg overflow-hidden">
            <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                <h3 class="font-bold text-gray-700">Ultime Attività Chat</h3>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-sm text-gray-500 border-b">
                        <th class="p-3 font-medium">Utente</th>
                        <th class="p-3 font-medium">Messaggio</th>
                        <th class="p-3 font-medium">Risposta AI</th>
                        <th class="p-3 font-medium">Data</th>
                    </tr>
                </thead>
                <tbody class="text-sm">
                    <?php
                    $db = new CRI_CRM_DB();
                    // Get last 5 logs (mocking function usage, ensure DB class supports generic fetch or use direct SQL here for dashboard optimization)
                    $logs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cricrm_chat_logs ORDER BY created_at DESC LIMIT 5");

                    if ($logs) {
                        foreach ($logs as $log) {
                            $user_info = get_userdata($log->user_id);
                            echo "<tr class='border-b hover:bg-gray-50'>";
                            echo "<td class='p-3 font-medium'>" . ($user_info ? esc_html($user_info->display_name) : 'Ospite') . "</td>";
                            echo "<td class='p-3 text-gray-600 truncate max-w-xs'>" . esc_html($log->message_input) . "</td>";
                            echo "<td class='p-3 text-gray-600 truncate max-w-xs'>" . esc_html(substr($log->message_output, 0, 50)) . "...</td>";
                            echo "<td class='p-3 text-gray-400'>" . date_i18n('d M H:i', strtotime($log->created_at)) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' class='p-4 text-center text-gray-500'>Nessuna attività recente.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</div>