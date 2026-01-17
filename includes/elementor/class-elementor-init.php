<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_Elementor
{

    public static function init()
    {
        add_action('elementor/widgets/register', array(__CLASS__, 'register_widgets'));
        add_action('elementor/frontend/after_enqueue_styles', array(__CLASS__, 'enqueue_styles'));
        add_action('elementor/frontend/after_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
    }

    public static function register_widgets($widgets_manager)
    {
        require_once CRI_CRM_PATH . 'includes/elementor/widgets/class-widget-chat.php';
        $widgets_manager->register(new \CRICRM\Widgets\Widget_Chat());
    }

    public static function enqueue_styles()
    {
        wp_register_style('cricrm-chat-css', CRI_CRM_URL . 'assets/css/chat.css', [], '1.0.0');
        wp_enqueue_style('cricrm-chat-css');
    }

    public static function enqueue_scripts()
    {
        wp_register_script('cricrm-chat-js', CRI_CRM_URL . 'assets/js/chat.js', ['jquery'], '1.0.0', true);

        wp_localize_script('cricrm-chat-js', 'CRICrmConfig', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'currentUser' => get_current_user_id()
        ]);

        wp_enqueue_script('cricrm-chat-js');
    }
}

// Hook into Elementor
add_action('plugins_loaded', function () {
    if (did_action('elementor/loaded')) {
        CRI_CRM_Elementor::init();
    }
});
