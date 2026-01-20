<?php
if (!defined('ABSPATH')) {
    exit;
}

class CRI_CRM_Assets
{

    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend'));
    }

    public function enqueue_frontend()
    {
        // Only enqueue on pages using our widgets to avoid bloat (optional optimization)
        // For now, global as requested
        $this->load_tailwind();
    }

    public function enqueue_admin($hook)
    {
        // Initial check: only load on our plugin pages
        if (strpos($hook, 'cricrm') !== false) {
            $this->load_tailwind();
        }
    }

    private function load_tailwind()
    {
        // Tailwind CDN
        wp_register_script('tailwind-cdn', 'https://cdn.tailwindcss.com?plugins=typography', [], '3.4.0', false);
        wp_enqueue_script('tailwind-cdn');

        // Font Awesome removed in favor of Inline SVGs for reliability

        // Configure Tailwind Theme (CRI Red)
        wp_add_inline_script('tailwind-cdn', "
            tailwind.config = {
                theme: {
                    extend: {
                        colors: {
                            cri: {
                                red: '#CC0000',
                                dark: '#8a0000'
                            }
                        }
                    }
                }
            }
        ");
    }
}
