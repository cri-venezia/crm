<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_DB
{

    private static $table_chat_logs = 'cricrm_chat_logs';

    public static function install()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_chat_logs;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            message_input mediumtext NOT NULL,
            message_output mediumtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Add version option to avoid running heavy updates every time
        add_option('cri_crm_db_version', '1.0.0');
    }

    /**
     * Insert a new chat log.
     */
    public function log_chat($user_id, $input, $output)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_chat_logs;
        return $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'message_input' => $input,
                'message_output' => $output,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s')
        );
    }

    /**
     * Retrieve logs for a specific user.
     */
    public function get_user_logs($user_id, $limit = 50)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_chat_logs;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at ASC LIMIT %d",
            $user_id,
            $limit
        ));
    }

    /**
     * Delete all logs for a specific user.
     */
    public function delete_user_logs($user_id)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . self::$table_chat_logs;
        return $wpdb->delete(
            $table_name,
            array('user_id' => $user_id),
            array('%d')
        );
    }
}
