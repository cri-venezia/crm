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
                <span class="dashicons dashicons-email"></span> Invia Newsletter (Magazine)
            </h3>

            <!-- JS driven form -->
            <form id="cri-newsletter-widget-form">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Oggetto Email</label>
                    <input type="text" id="nl-widget-subject" class="w-full p-2 border rounded focus:ring-red-500 focus:border-red-500" placeholder="Es. Notiziario Luglio">
                </div>

                <div id="nl-widget-articles-container" class="space-y-4 mb-4">
                    <!-- Articles Injected Here -->
                </div>

                <button type="button" id="nl-widget-add-btn" class="text-sm text-[#CC0000] border border-[#CC0000] px-3 py-1 rounded hover:bg-red-50 flex items-center gap-1 mb-6">
                    <span class="dashicons dashicons-plus-alt2"></span> Aggiungi Articolo
                </button>

                <div class="bg-yellow-50 p-3 rounded border border-yellow-200 text-sm text-yellow-800 flex gap-2 mb-4">
                    <span class="dashicons dashicons-warning mt-0.5"></span>
                    <p>L'invio verrà gestito tramite le API di Brevo. Controlla l'anteprima prima di inviare.</p>
                </div>

                <button type="button" id="nl-widget-send-btn" class="w-full bg-[#CC0000] text-white py-3 px-4 rounded hover:bg-[#8a0000] transition flex justify-center items-center gap-2 font-bold text-lg">
                    <span class="dashicons dashicons-paperplane"></span> Invia Newsletter
                </button>
            </form>

            <div id="nl-widget-status" class="mt-4 hidden p-3 rounded text-sm font-medium"></div>
        </div>

        <!-- Template for Article Item (Widget Version) -->
        <template id="nl-widget-article-template">
            <div class="nl-widget-item bg-gray-50 p-3 rounded border border-gray-200 relative text-sm">
                <button type="button" class="nl-widget-remove absolute top-2 right-2 text-gray-400 hover:text-red-600" title="Rimuovi">
                    <span class="dashicons dashicons-trash"></span>
                </button>
                <div class="grid grid-cols-1 gap-2">
                    <input type="text" class="nl-w-title w-full p-2 border rounded" placeholder="Titolo Articolo">
                    <input type="text" class="nl-w-image w-full p-2 border rounded" placeholder="URL Immagine">
                    <textarea class="nl-w-content w-full p-2 border rounded" rows="2" placeholder="Testo breve..."></textarea>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" class="nl-w-btn-text w-full p-2 border rounded" value="Leggi">
                        <input type="text" class="nl-w-btn-url w-full p-2 border rounded" placeholder="URL Link">
                    </div>
                </div>
            </div>
        </template>

        <script>
            jQuery(document).ready(function($) {
                const maxArticles = 6;
                const container = $('#nl-widget-articles-container');
                const template = $('#nl-widget-article-template').html();

                // Add
                $('#nl-widget-add-btn').on('click', function() {
                    if ($('.nl-widget-item').length >= maxArticles) return alert('Massimo ' + maxArticles + ' articoli.');
                    container.append(template);
                });

                // Remove
                $(document).on('click', '.nl-widget-remove', function() {
                    $(this).closest('.nl-widget-item').remove();
                });

                // Init
                if ($('.nl-widget-item').length === 0) $('#nl-widget-add-btn').click();

                // Send
                $('#nl-widget-send-btn').on('click', function() {
                    const btn = $(this);
                    const status = $('#nl-widget-status');
                    const subject = $('#nl-widget-subject').val();

                    const articles = [];
                    $('.nl-widget-item').each(function() {
                        const t = $(this).find('.nl-w-title').val();
                        if (t) {
                            articles.push({
                                title: t,
                                image: $(this).find('.nl-w-image').val() || 'https://via.placeholder.com/600x300?text=CRI',
                                content: $(this).find('.nl-w-content').val(),
                                linkText: $(this).find('.nl-w-btn-text').val(),
                                linkUrl: $(this).find('.nl-w-btn-url').val() || '#'
                            });
                        }
                    });

                    if (!subject || articles.length === 0) {
                        alert('Oggetto e almeno 1 articolo richiesti.');
                        return;
                    }

                    if (!confirm('Confermi l\'invio massivo?')) return;

                    btn.prop('disabled', true).text('Invio...');
                    status.removeClass('hidden text-green-600 text-red-600').text('');

                    $.ajax({
                        url: '<?php echo esc_url_raw(rest_url('cricrm/v1/newsletter')); ?>',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({
                            subject: subject,
                            articles: articles
                        }),
                        success: function(res) {
                            status.addClass('text-green-600').text('✅ Inviata!');
                            btn.prop('disabled', false).text('Invia Newsletter');
                        },
                        error: function(err) {
                            status.addClass('text-red-600').html('❌ Errore: ' + (err.responseJSON?.message || 'Errore'));
                            btn.prop('disabled', false).text('Invia Newsletter');
                        }
                    });
                });
            });
        </script>
<?php
    }
}
