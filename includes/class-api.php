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

        register_rest_route('cricrm/v1', '/campaign', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_campaign'),
            'permission_callback' => array($this, 'check_permission')
        ));

        register_rest_route('cricrm/v1', '/newsletter', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_newsletter'),
            'permission_callback' => array($this, 'check_permission')
        ));

        register_rest_route('cricrm/v1', '/chat', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'clear_history'),
            'permission_callback' => array($this, 'check_permission')
        ));

        // User Management (Tags)
        register_rest_route('cricrm/v1', '/user/update-tags', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_update_tags'),
            'permission_callback' => array($this, 'check_admin_permission')
        ));

        // User Management (Roles)
        register_rest_route('cricrm/v1', '/user/toggle-role', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_toggle_role'),
            'permission_callback' => array($this, 'check_admin_permission')
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

        // Context Construction: The "Erika" Persona
        $user_location = get_user_meta($user->ID, 'sede', true);

        $context = "Sei Erika, l'assistente digitale operativa della Croce Rossa Italiana - Comitato di Venezia. ";
        $context .= "Stai parlando con {$user->display_name}";
        if ($user_location) {
            $context .= " della Sede di {$user_location}";
        }
        $context .= ". ";
        $context .= "Il tuo obiettivo è supportare i volontari nelle attività logistiche, sanitarie e amministrative. ";
        $context .= "Linee Guida: ";
        $context .= "1. Rispetta sempre i 7 Principi: Umanità, Imparzialità, Neutralità, Indipendenza, Volontarietà, Unità, Universalità. ";
        $context .= "2. Sii professionale, operativa e sintetica. Evita muri di testo. ";
        $context .= "3. Se non conosci una risposta operativa specifica (es. turni, codici), invita a contattare la Sala Operativa. ";
        $context .= "4. Non dare mai consigli medici diagnostici specifici, ma rimanda ai protocolli sanitari vigenti. ";
        $context .= "5. Tono: Formale ma empatico. Usa il 'Lei' se non specificato diversamente, o il 'Tu' tra colleghi (default: amichevole formale).";

        // Build Prompt
        $contents = [];
        $contents[] = ['role' => 'user', 'parts' => [['text' => $context]]]; // System Instruction workaround
        $contents[] = ['role' => 'model', 'parts' => [['text' => "Ricevuto. Sono Erika. Come posso aiutare?"]]];

        foreach ($history as $msg) {
            $role = ($msg['sender'] == 'user') ? 'user' : 'model';
            $contents[] = ['role' => $role, 'parts' => [['text' => $msg['text']]]];
        }

        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        return $this->execute_gemini_request($api_key, $contents);
    }

    public function handle_campaign($request)
    {
        $params = $request->get_json_params();
        $topic = sanitize_text_field($params['topic']);
        $platform = sanitize_text_field($params['platform']);
        $tone = sanitize_text_field($params['tone']);

        if (empty($topic)) {
            return new WP_Error('missing_topic', 'Topic is required', array('status' => 400));
        }

        $api_key = get_option('cri_crm_gemini_key');
        if (empty($api_key)) {
            return new WP_Error('config_error', 'Gemini API Key missing', array('status' => 500));
        }

        $prompt = "Agisci come Senior Social Media Manager della Croce Rossa Italiana (Venezia). ";
        $prompt .= "Obiettivo: Scrivere un post efficace per {$platform} sul tema: '{$topic}'. ";
        $prompt .= "Target: Cittadinanza locale e donatori. ";
        $prompt .= "Stile/Tono: {$tone}. ";
        $prompt .= "Struttura del post: ";
        $prompt .= "- Gancio iniziale (Hook) che attira l'attenzione. ";
        $prompt .= "- Corpo centrale emotivo o informativo. ";
        $prompt .= "- Call to Action (CTA) chiara finale. ";
        $prompt .= "- Uso strategico di emoji (non troppe, ma al posto giusto). ";
        $prompt .= "- Lista di 5-10 hashtag rilevanti (inclusi #CRIVenezia #CroceRossa #UnItaliaCheAiuta). ";
        $prompt .= "Output richiesto: Solo il testo del post, pronto per il copia-incolla.";

        $contents = [['role' => 'user', 'parts' => [['text' => $prompt]]]];

        $text = $this->execute_gemini_request($api_key, $contents);

        if (is_wp_error($text)) {
            return $text;
        }

        return rest_ensure_response(array('text' => $text));
    }

    public function handle_newsletter($request)
    {
        $params = $request->get_json_params();
        $subject = sanitize_text_field($params['subject']);
        // 'content' is legacy, now we support 'articles' array
        $articles = isset($params['articles']) ? $params['articles'] : [];

        // If no articles but 'content' is present, wrap it as a single article (Backward Compat)
        if (empty($articles) && !empty($params['content'])) {
            $articles[] = [
                'title' => 'Comunicazione',
                'image' => 'https://via.placeholder.com/600x300?text=CRI+Venezia',
                'content' => wp_kses_post($params['content']),
                'linkText' => 'Leggi',
                'linkUrl' => '#'
            ];
        }

        $api_key = get_option('cri_crm_brevo_key');
        if (empty($api_key)) {
            return new WP_Error('config_error', 'Brevo API Key missing', array('status' => 500));
        }

        // Generate HTML Email
        $html_content = $this->generate_newsletter_html($subject, $articles);

        $url = 'https://api.brevo.com/v3/smtp/email';
        $sender = array('name' => 'CRI Venezia', 'email' => 'newsletter@crivenezia.org');

        $body = array(
            'sender' => $sender,
            'to' => array(array('email' => 'test@crivenezia.org', 'name' => 'Volontari CRI')), // In Prod this would be a List ID
            'subject' => $subject,
            'htmlContent' => $html_content
        );

        $response = wp_remote_post($url, array(
            'body' => json_encode($body),
            'headers' => array(
                'api-key' => $api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            return $response;
        }

        return rest_ensure_response(array('status' => 'sent', 'details' => json_decode(wp_remote_retrieve_body($response))));
    }

    private function generate_newsletter_html($subject, $articles)
    {
        ob_start();
?>
        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f4f4f4;
                    margin: 0;
                    padding: 0;
                }

                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    background-color: #ffffff;
                }

                .header {
                    background-color: #cc0000;
                    color: #ffffff;
                    padding: 20px;
                    text-align: center;
                }

                .footer {
                    background-color: #333333;
                    color: #ffffff;
                    padding: 20px;
                    text-align: center;
                    font-size: 12px;
                }

                .article {
                    padding: 20px;
                    border-bottom: 1px solid #eeeeee;
                }

                .article img {
                    max-width: 100%;
                    height: auto;
                    border-radius: 4px;
                    margin-bottom: 15px;
                }

                .article h2 {
                    color: #cc0000;
                    margin-top: 0;
                }

                .btn {
                    display: inline-block;
                    background-color: #cc0000;
                    color: #ffffff;
                    text-decoration: none;
                    padding: 10px 20px;
                    border-radius: 4px;
                    margin-top: 10px;
                    font-weight: bold;
                }
            </style>
        </head>

        <body>
            <div class="container">
                <div class="header">
                    <h1>CRI Venezia - News</h1>
                    <p><?php echo esc_html($subject); ?></p>
                </div>

                <?php foreach ($articles as $article) : ?>
                    <div class="article">
                        <?php if (!empty($article['image'])) : ?>
                            <img src="<?php echo esc_url($article['image']); ?>" alt="Article Image">
                        <?php endif; ?>

                        <h2><?php echo esc_html($article['title']); ?></h2>
                        <p><?php echo nl2br(esc_html($article['content'])); ?></p>

                        <?php if (!empty($article['linkUrl']) && $article['linkUrl'] !== '#') : ?>
                            <a href="<?php echo esc_url($article['linkUrl']); ?>" class="btn">
                                <?php echo esc_html($article['linkText'] ?: 'Leggi tutto'); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <div class="footer">
                    <p>&copy; <?php echo date('Y'); ?> Croce Rossa Italiana - Comitato di Venezia</p>
                    <p>Via Nepal, 4 - 30126 Lido di Venezia (VE)</p>
                </div>
            </div>
        </body>

        </html>
<?php
        return ob_get_clean();
    }

    private function execute_gemini_request($api_key, $contents)
    {
        // Use Gemini 3.0 Pro Preview (User Mandate)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-pro-preview:generateContent?key=" . $api_key;

        $body = ['contents' => $contents];

        $response = wp_remote_post($url, array(
            'body' => json_encode($body),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 30
        ));

        if (is_wp_error($response)) {
            error_log('CRI CRM - Gemini Connection Error: ' . $response->get_error_message());
            return $response;
        }

        $body_str = wp_remote_retrieve_body($response);
        $data = json_decode($body_str, true);

        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        } else {
            error_log('CRI CRM - Gemini API Error Response: ' . $body_str);
            return new WP_Error('ai_error', 'Invalid AI response', array('status' => 502, 'data' => $data));
        }
    }
    public function check_admin_permission()
    {
        return current_user_can('manage_options') || current_user_can('cri_manager');
    }

    public function handle_update_tags($request)
    {
        $params = $request->get_json_params();
        $user_id = intval($params['user_id']);
        $tags = sanitize_text_field($params['tags']);

        if (!$user_id) return new WP_Error('no_user', 'User ID mismatch', array('status' => 400));

        update_user_meta($user_id, 'cri_user_tags', $tags);
        return rest_ensure_response(array('success' => true));
    }

    public function handle_toggle_role($request)
    {
        $params = $request->get_json_params();
        $user_id = intval($params['user_id']);
        $role = sanitize_text_field($params['role']);
        $active = (bool) $params['active'];

        if (!$user_id || !$role) return new WP_Error('missing_params', 'Params missing', array('status' => 400));

        $user = get_userdata($user_id);
        if (!$user) return new WP_Error('not_found', 'User not found', array('status' => 404));

        // Allowed roles to toggle
        $allowed = ['cri_newsletter', 'cri_fundraiser', 'cri_manager'];
        if (!in_array($role, $allowed)) return new WP_Error('forbidden_role', 'Role not managed here', array('status' => 403));

        if ($active) {
            $user->add_role($role);
        } else {
            $user->remove_role($role);
        }

        return rest_ensure_response(array('success' => true, 'roles' => $user->roles));
    }
}
