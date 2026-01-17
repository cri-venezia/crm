<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_API
{

    public function __construct()
    {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    public function register_routes()
    {
        register_rest_route('cricrm/v1', '/chat', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_chat'),
            'permission_callback' => array($this, 'check_permission')
        ));

        register_rest_route('cricrm/v1', '/history', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_history'),
            'permission_callback' => array($this, 'check_permission')
        ));
    }

    public function check_permission()
    {
        return is_user_logged_in();
    }

    public function handle_chat($request)
    {
        $params = $request->get_json_params();
        $message = sanitize_text_field($params['message']);
        $history = isset($params['history']) ? $params['history'] : [];

        $user_id = get_current_user_id();
        $user_info = get_userdata($user_id);

        if (empty($message)) {
            return new WP_Error('no_message', 'Message is required', array('status' => 400));
        }

        // Call Gemini API
        $response_text = $this->call_gemini($message, $history, $user_info);

        if (is_wp_error($response_text)) {
            return $response_text;
        }

        // Log to DB
        $db = new CRI_CRM_DB();
        $db->log_chat($user_id, $message, $response_text);

        return rest_ensure_response(array(
            'text' => $response_text
        ));
    }

    public function get_history($request)
    {
        $user_id = get_current_user_id();
        $db = new CRI_CRM_DB();
        $logs = $db->get_user_logs($user_id);

        // Format for frontend
        $formatted = array();
        foreach ($logs as $log) {
            $formatted[] = array('role' => 'user', 'content' => $log->message_input);
            $formatted[] = array('role' => 'assistant', 'content' => $log->message_output);
        }

        return rest_ensure_response($formatted);
    }

    private function call_gemini($message, $history, $user)
    {
        $api_key = get_option('cri_crm_gemini_key');

        if (empty($api_key)) {
            return "Errore Configurazione: Manca la chiave API di Gemini nelle impostazioni del plugin.";
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . $api_key;

        // Context Construction
        $context = "Sei Erika, l'assistente AI della Croce Rossa di Venezia. ";
        $context .= "Parli con {$user->display_name}. ";
        $context .= "Rispondi in modo professionale ma empatico. Sii concisa.";

        // Build Prompt
        $contents = [];
        $contents[] = ['role' => 'user', 'parts' => [['text' => $context]]]; // System Instruction workaround
        $contents[] = ['role' => 'model', 'parts' => [['text' => "Ricevuto. Sono Erika. Come posso aiutare?"]]];

        foreach ($history as $msg) {
            $role = ($msg['sender'] === 'user') ? 'user' : 'model';
            $contents[] = ['role' => $role, 'parts' => [['text' => $msg['text']]]];
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        $body = [
            'contents' => $contents
        ];

        $response = wp_remote_post($url, array(
            'body' => json_encode($body),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        } else {
            return "Errore AI: Non ho ricevuto una risposta valida.";
        }
    }
}
