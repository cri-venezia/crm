<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_Importer
{

    public function __construct()
    {
        add_action('admin_init', array($this, 'handle_import'));
    }

    public function handle_import()
    {
        if (isset($_GET['cricrm_action']) && $_GET['cricrm_action'] === 'import_logs' && current_user_can('manage_options')) {
            $this->run_import();
        }
    }

    private function run_import()
    {
        global $wpdb;
        $json_file = CRI_CRM_PATH . '../backup/chat_logs.json';

        if (!file_exists($json_file)) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>File backup/chat_logs.json non trovato!</p></div>';
            });
            return;
        }

        $json_data = file_get_contents($json_file);
        $logs = json_decode($json_data, true);

        if (!$logs) {
            add_action('admin_notices', function () {
                echo '<div class="notice notice-error"><p>Errore nel parsing del JSON.</p></div>';
            });
            return;
        }

        $count = 0;
        $db = new CRI_CRM_DB();
        $current_user_id = get_current_user_id(); // Assign all imported logs to current admin for safety, or we could map via email if available

        foreach ($logs as $log) {
            // Check duplications (optional) or just insert
            // Mapping: 
            // Supabase: user_id (UUID), message (json/text), response (text), created_at
            // WP: user_id (INT), message_input, message_output, created_at

            // Note: Old 'ai_chat_logs' structure needs to be known. Assuming:
            // input: $log['message']
            // output: $log['response']
            // created_at: $log['created_at']

            $input = isset($log['message']) ? $log['message'] : '[Nessun Testo]';
            $output = isset($log['response']) ? $log['response'] : '[Nessuna Risposta]';
            $created_at = isset($log['created_at']) ? $log['created_at'] : current_time('mysql');

            $db->log_chat($current_user_id, $input, $output); // Using method that sets created_at to NOW, we might want to manually override timestamp via SQL if strict history needed.

            // Manual SQL for timestamp accuracy if needed:
            // $wpdb->insert(...)

            $count++;
        }

        add_action('admin_notices', function () use ($count) {
            echo '<div class="notice notice-success"><p>Importazione completata! ' . $count . ' messaggi importati.</p></div>';
        });
    }
}
