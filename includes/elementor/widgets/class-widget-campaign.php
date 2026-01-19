<?php
if (!defined('ABSPATH')) exit;

class CRI_CRM_Widget_Campaign extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'cri_campaign_generator';
    }

    public function get_title()
    {
        return __('Generatore Campagne', 'cri-crm');
    }

    public function get_icon()
    {
        return 'eicon-edit';
    }

    public function get_categories()
    {
        return ['cri_category'];
    }

    public function get_script_depends()
    {
        return ['cri-campaign-js'];
    }

    public function get_style_depends()
    {
        return ['cri-chat-css']; // Reusing chat styles for consistent UI
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Content', 'cri-crm'),
            ]
        );

        $this->add_control(
            'platform',
            [
                'label' => __('Piattaforma', 'cri-crm'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'facebook',
                'options' => [
                    'facebook' => 'Facebook',
                    'instagram' => 'Instagram',
                    'linkedin' => 'LinkedIn',
                    'newsletter' => 'Email/Newsletter',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        $settings = $this->get_settings_for_display();
?>
        <div class="cri-crm-card p-6 bg-white rounded-lg shadow-sm border border-gray-200">
            <h3 class="text-xl font-bold text-[#CC0000] mb-4 flex items-center gap-2">
                <span class="dashicons dashicons-megaphone"></span> Generatore Campagne
            </h3>

            <form id="cri-campaign-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Argomento</label>
                    <input type="text" id="campaign-topic" class="w-full p-2 border rounded focus:ring-red-500 focus:border-red-500" placeholder="Es. Raccolta fondi per ambulanza">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Piattaforma</label>
                        <select id="campaign-platform" class="w-full p-2 border rounded bg-white">
                            <option value="Facebook">Facebook</option>
                            <option value="Instagram">Instagram</option>
                            <option value="LinkedIn">LinkedIn</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tono</label>
                        <select id="campaign-tone" class="w-full p-2 border rounded bg-white">
                            <option value="Emozionale">Emozionale</option>
                            <option value="Urgente">Urgente</option>
                            <option value="Informativo">Informativo</option>
                            <option value="Istituzionale">Istituzionale</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-[#CC0000] text-white py-2 px-4 rounded hover:bg-[#aa0000] transition flex justify-center items-center gap-2">
                    <span class="dashicons dashicons-edit"></span> Genera Testo con IA
                </button>
            </form>

            <div id="campaign-result" class="mt-6 hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">Risultato:</label>
                <div class="relative">
                    <textarea id="campaign-output" rows="8" class="w-full p-3 border rounded bg-gray-50 text-sm font-mono"></textarea>
                    <button onclick="navigator.clipboard.writeText(document.getElementById('campaign-output').value)" class="absolute top-2 right-2 text-gray-400 hover:text-gray-600">
                        <span class="dashicons dashicons-clipboard"></span>
                    </button>
                </div>
            </div>
        </div>
<?php
    }
}
