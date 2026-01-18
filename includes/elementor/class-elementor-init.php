<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_Elementor
{

    public static function init()
    {
        add_action('elementor/elements/categories_registered', array(__CLASS__, 'register_categories'));
        add_action('elementor/widgets/register', array(__CLASS__, 'register_widgets'));
        add_action('elementor/frontend/after_enqueue_styles', array(__CLASS__, 'enqueue_styles'));
        add_action('elementor/frontend/after_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
    }

    public static function register_categories($elements_manager)
    {
        $elements_manager->add_category(
            'cri_category',
            [
                'title' => __('CRI Strumenti', 'cri-crm'),
                'icon' => 'fa fa-plus',
            ]
        );
    }

    public static function register_widgets($widgets_manager)
    {
        require_once CRI_CRM_PATH . 'includes/elementor/widgets/class-widget-chat.php';
        require_once CRI_CRM_PATH . 'includes/elementor/widgets/class-widget-campaign.php';
        require_once CRI_CRM_PATH . 'includes/elementor/widgets/class-widget-newsletter.php';
        require_once CRI_CRM_PATH . 'includes/elementor/widgets/class-widget-registry.php';

        $widgets_manager->register(new \CRICRM\Widgets\Widget_Chat());
        $widgets_manager->register(new \CRI_CRM_Widget_Campaign());
        $widgets_manager->register(new \CRI_CRM_Widget_Newsletter());
        $widgets_manager->register(new \CRI_CRM_Widget_Registry());
    }

    public static function enqueue_styles()
    {
        wp_register_style('cricrm-chat-css', CRI_CRM_URL . 'assets/css/chat.css', [], '1.0.0');
        wp_enqueue_style('cricrm-chat-css');
    }

    public static function enqueue_scripts()
    {
        // Chat Script
        wp_register_script('cricrm-chat-js', CRI_CRM_URL . 'assets/js/chat.js', ['jquery'], '1.0.0', true);
        wp_localize_script('cricrm-chat-js', 'CRICrmConfig', [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'currentUser' => get_current_user_id()
        ]);
        wp_enqueue_script('cricrm-chat-js');

        // Widgets Script (Campaign & Newsletter)
        wp_register_script('cri-crm-widgets-common', CRI_CRM_URL . 'assets/js/crm-widgets.js', ['jquery'], '1.0.0', true);

        // Register aliases for widget dependency
        wp_register_script('cri-campaign-js', CRI_CRM_URL . 'assets/js/crm-widgets.js', ['jquery'], '1.0.0', true);
        wp_register_script('cri-newsletter-js', CRI_CRM_URL . 'assets/js/crm-widgets.js', ['jquery'], '1.0.0', true);

        // Localize
        $config = [
            'root' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest')
        ];
        wp_localize_script('cri-crm-widgets-common', 'criCrmSettings', $config);
        wp_localize_script('cri-campaign-js', 'criCrmSettings', $config);
        wp_localize_script('cri-newsletter-js', 'criCrmSettings', $config);
    }
}

// Hook into Elementor correctly
add_action('elementor/init', array('CRI_CRM_Elementor', 'init'));
