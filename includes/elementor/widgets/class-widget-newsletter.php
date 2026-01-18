<?php
if (!defined('ABSPATH')) exit;

class CRI_CRM_Widget_Newsletter extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'cri_newsletter_manager';
    }

    public function get_title()
    {
        return __('Gestore Newsletter', 'cri-crm');
    }

    public function get_icon()
    {
        return 'eicon-envelope';
    }

    public function get_categories()
    {
        return ['cri_category'];
    }

    public function get_script_depends()
    {
        return ['cri-newsletter-js'];
    }

    public function get_style_depends()
    {
        return ['cri-chat-css'];
    }

    protected function render()
    {
?>
        <div class="cri-crm-card p-6 bg-white rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-xl font-bold text-[#CC0000] mb-4 flex items-center gap-2">
                <span class="dashicons dashicons-email"></span> Invia Newsletter (Brevo)
            </h3>

            <form id="cri-newsletter-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Oggetto Email</label>
                    <input type="text" id="newsletter-subject" class="w-full p-2 border rounded focus:ring-red-500 focus:border-red-500" placeholder="News dalla Croce Rossa">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contenuto HTML</label>
                    <textarea id="newsletter-content" rows="10" class="w-full p-2 border rounded font-mono text-sm" placeholder="<p>Ciao Volontari...</p>"></textarea>
                    <p class="text-xs text-gray-500 mt-1">Puoi usare HTML di base.</p>
                </div>

                <div class="bg-yellow-50 p-3 rounded border border-yellow-200 text-sm text-yellow-800 flex gap-2">
                    <span class="dashicons dashicons-warning mt-0.5"></span>
                    <p>L'invio verrà gestito tramite le API di Brevo configurate nel CRM. Assicurati che la chiave API sia valida.</p>
                </div>

                <button type="submit" class="w-full bg-gray-800 text-white py-2 px-4 rounded hover:bg-black transition flex justify-center items-center gap-2">
                    <span class="dashicons dashicons-paperplane"></span> Invia Ora
                </button>
            </form>

            <div id="newsletter-status" class="mt-4 hidden p-3 rounded text-sm"></div>
        </div>
<?php
    }
}
