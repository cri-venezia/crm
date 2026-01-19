<div class="wrap cricrm-admin-wrapper">
    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 max-w-7xl mx-auto mt-5">
        <h1 class="text-3xl font-bold text-[#CC0000] mb-6 flex items-center gap-2">
            <span class="dashicons dashicons-email-alt"></span> Newsletter CRI
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Col 1: Bio -->
            <div class="col-span-1">
                <?php
                $current_user = wp_get_current_user();
                $avatar_url = get_avatar_url($current_user->ID, ['size' => 128]);
                ?>
                <div class="bg-white border rounded-lg shadow-sm p-6 text-center">
                    <img src="<?php echo esc_url($avatar_url); ?>" class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-red-50">
                    <h2 class="text-xl font-bold text-gray-800"><?php echo esc_html($current_user->display_name); ?></h2>
                    <p class="text-gray-500 text-sm mb-4"><?php echo esc_html($current_user->user_email); ?></p>
                    <div class="inline-block bg-red-100 text-[#CC0000] px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
                        Operatore Newsletter
                    </div>
                </div>
            </div>

            <!-- Col 2: Newsletter Tool -->
            <div class="col-span-2">
                <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-red-50 px-6 py-4 border-b border-red-100">
                        <h3 class="font-bold text-[#CC0000] text-lg">📢 Invia Comunicazione</h3>
                        <p class="text-sm text-gray-600">Invia mail massive ai volontari via Brevo.</p>
                    </div>
                    <div class="p-6">
                        <form id="cricrm-newsletter-form">
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Oggetto Email</label>
                                <input type="text" id="nl-subject" class="w-full p-2 border rounded focus:ring-red-500 focus:border-red-500" placeholder="Es. Notiziario CRI Venezia - Gennaio">
                            </div>

                            <h4 class="font-bold text-gray-700 mb-3 border-b pb-1">Articoli (Max 6)</h4>
                            <div id="nl-articles-container" class="space-y-6">
                                <!-- Articles will be injected here -->
                            </div>

                            <button type="button" id="nl-add-article-btn" class="mt-4 text-sm text-[#CC0000] border border-[#CC0000] px-3 py-1 rounded hover:bg-red-50 flex items-center gap-1">
                                <span class="dashicons dashicons-plus-alt2"></span> Aggiungi Articolo
                            </button>

                            <div class="mt-8 border-t pt-4">
                                <button type="button" id="nl-send-btn" class="bg-[#CC0000] text-white px-6 py-3 rounded text-lg font-bold hover:bg-[#8a0000] transition w-full md:w-auto flex items-center justify-center gap-2">
                                    <span class="dashicons dashicons-email"></span> Invia Newsletter
                                </button>
                            </div>
                            <div id="nl-status" class="mt-3 text-sm hidden font-medium"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Template for an Article Item -->
        <template id="nl-article-template">
            <div class="nl-article-item bg-gray-50 p-4 rounded border border-gray-200 relative">
                <button type="button" class="nl-remove-article absolute top-2 right-2 text-gray-400 hover:text-red-600" title="Rimuovi">
                    <span class="dashicons dashicons-trash"></span>
                </button>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Titolo Articolo</label>
                        <input type="text" class="nl-input-title w-full p-2 border rounded text-sm" placeholder="Titolo...">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">URL Immagine</label>
                        <input type="text" class="nl-input-image w-full p-2 border rounded text-sm" placeholder="https://...">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Contenuto Breve</label>
                        <textarea class="nl-input-content w-full p-2 border rounded text-sm" rows="3" placeholder="Riassunto..."></textarea>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Testo Bottone</label>
                        <input type="text" class="nl-input-btn-text w-full p-2 border rounded text-sm" value="Leggi tutto">
                    </div>
                    <div class="col-span-1">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">URL Bottone</label>
                        <input type="text" class="nl-input-btn-url w-full p-2 border rounded text-sm" placeholder="https://...">
                    </div>
                </div>
            </div>
        </template>

        <script>
            jQuery(document).ready(function($) {
                const maxArticles = 6;
                // Avoid double binding if included multiple times (unlikely with this structure but safe)
                if ($('#nl-add-article-btn').data('bound')) return;
                $('#nl-add-article-btn').data('bound', true);

                // Add Article
                $('#nl-add-article-btn').on('click', function() {
                    const count = $('.nl-article-item').length;
                    if (count >= maxArticles) return alert('Massimo ' + maxArticles + ' articoli.');
                    const template = $('#nl-article-template').html();
                    $('#nl-articles-container').append(template);
                });

                // Remove Article
                $(document).on('click', '.nl-remove-article', function() {
                    $(this).closest('.nl-article-item').remove();
                });

                // Init
                if ($('.nl-article-item').length === 0) $('#nl-add-article-btn').click();

                // Send Newsletter
                $('#nl-send-btn').on('click', function() {
                    const btn = $(this);
                    const status = $('#nl-status');
                    const subject = $('#nl-subject').val();
                    const articles = [];
                    $('.nl-article-item').each(function() {
                        const title = $(this).find('.nl-input-title').val();
                        if (title) {
                            articles.push({
                                title: title,
                                image: $(this).find('.nl-input-image').val() || 'https://via.placeholder.com/600x300?text=CRI+Venezia',
                                content: $(this).find('.nl-input-content').val(),
                                linkText: $(this).find('.nl-input-btn-text').val() || 'Leggi di più',
                                linkUrl: $(this).find('.nl-input-btn-url').val() || '#'
                            });
                        }
                    });

                    if (!subject) return alert('Inserisci l\'oggetto.');
                    if (articles.length === 0) return alert('Inserisci almeno un articolo.');
                    if (!confirm('Stai per inviare la newsletter a TUTTI i volontari. Confermi?')) return;

                    btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Invio in corso...');
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
                            status.addClass('text-green-600').html('<span class="dashicons dashicons-yes"></span> Newsletter inviata con successo!');
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-email"></span> Invia Newsletter');
                        },
                        error: function(err) {
                            status.addClass('text-red-600').html('<span class="dashicons dashicons-warning"></span> Errore: ' + (err.responseJSON?.message || 'Errore sconosciuto'));
                            btn.prop('disabled', false).html('<span class="dashicons dashicons-email"></span> Invia Newsletter');
                        }
                    });
                });
            });
        </script>
    </div>
</div>