<?php

namespace CRICRM\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) {
    exit;
}

class Widget_Chat extends Widget_Base
{

    public function get_name()
    {
        return 'cricrm_chat';
    }

    public function get_title()
    {
        return __('CRI Erika Chat', 'cri-crm');
    }

    public function get_icon()
    {
        return 'eicon-comments';
    }

    public function get_categories()
    {
        return ['cri_category'];
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'cri-crm'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'welcome_message',
            [
                'label' => __('Welcome Message', 'cri-crm'),
                'type' => Controls_Manager::TEXTAREA,
                'default' => __('Ciao! Sono Erika. Come posso aiutarti?', 'cri-crm'),
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        // Security: Chat is reserved for Admins
        if (!current_user_can('administrator')) {
            return;
        }

        $settings = $this->get_settings_for_display();

        echo '<div class="cricrm-chat-wrapper">';
        echo '  <div class="cricrm-chat-header">';
        echo '      <div class="header-left" style="display: flex; align-items: center; gap: 10px;">';
        echo '          <div class="status-indicator online"></div>';
        echo '          <h3>' . esc_html($this->get_title()) . '</h3>';
        echo '      </div>';
        echo '      <button id="cricrm-chat-clear" title="' . __('Cancella Cronologia', 'cri-crm') . '" style="background:none; border:none; color:white; cursor:pointer;">';
        echo '          <i class="fa fa-trash" aria-hidden="true"></i>';
        echo '      </button>';
        echo '  </div>';

        echo '  <div id="cricrm-chat-history" class="cricrm-chat-history">';
        // Initial Welcome Message
        echo '<div class="cricrm-msg model">';
        echo '  <div class="cricrm-msg-content">' . esc_html($settings['welcome_message']) . '</div>';
        echo '</div>';
        echo '  </div>';

        echo '  <div class="cricrm-chat-input-area">';
        echo '      <input type="text" id="cricrm-chat-input" placeholder="' . __('Scrivi un messaggio...', 'cri-crm') . '" />';
        echo '      <button id="cricrm-chat-send" class="cricrm-btn-send">';
        echo '          <i class="fa fa-paper-plane" aria-hidden="true"></i>';
        echo '      </button>';
        echo '  </div>';
        echo '</div>';
    }
}
