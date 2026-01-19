<div class="wrap cricrm-admin-wrapper">
    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 max-w-7xl mx-auto mt-5">
        <h1 class="text-3xl font-bold text-blue-800 mb-6 flex items-center gap-2">
            <span class="dashicons dashicons-megaphone"></span> Fundraising & Social
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Col 1: Bio -->
            <div class="col-span-1">
                <?php
                $current_user = wp_get_current_user();
                $avatar_url = get_avatar_url($current_user->ID, ['size' => 128]);
                ?>
                <div class="bg-white border rounded-lg shadow-sm p-6 text-center">
                    <img src="<?php echo esc_url($avatar_url); ?>" class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-blue-50">
                    <h2 class="text-xl font-bold text-gray-800"><?php echo esc_html($current_user->display_name); ?></h2>
                    <div class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide mt-2">
                        Fundraiser
                    </div>
                </div>
            </div>

            <!-- Col 2: Campaign Generator -->
            <div class="col-span-2">
                <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                    <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                        <h3 class="font-bold text-blue-800 text-lg">💡 Generatore Campagne</h3>
                        <p class="text-sm text-gray-600">Crea post social e campagne fundraising con Erika.</p>
                    </div>
                    <div class="p-6">
                        <form id="cricrm-campaign-form">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Argomento</label>
                                    <input type="text" id="cp-topic" class="w-full p-2 border rounded" placeholder="Es. Donazione Sangue, Panettoni...">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Piattaforma</label>
                                    <select id="cp-platform" class="w-full p-2 border rounded">
                                        <option value="Facebook">Facebook</option>
                                        <option value="Instagram">Instagram</option>
                                        <option value="LinkedIn">LinkedIn</option>
                                        <option value="Newsletter">Newsletter Intro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tono di Voce</label>
                                <select id="cp-tone" class="w-full p-2 border rounded">
                                    <option value="Emozionale e Urgente">Emozionale e Urgente</option>
                                    <option value="Informativo e Formale">Informativo e Formale</option>
                                    <option value="Amichevole e Coinvolgente">Amichevole e Coinvolgente</option>
                                </select>
                            </div>
                            <button type="button" id="cp-generate-btn" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition w-full">
                                ✨ Genera con Erika
                            </button>

                            <div id="cp-result-container" class="mt-6 hidden">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Risultato:</label>
                                <textarea id="cp-result" rows="8" class="w-full p-3 border rounded bg-gray-50 text-sm font-mono"></textarea>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                // Avoid double bind
                if ($('#cp-generate-btn').data('bound')) return;
                $('#cp-generate-btn').data('bound', true);

                $('#cp-generate-btn').on('click', function() {
                    const btn = $(this);
                    const topic = $('#cp-topic').val();
                    const platform = $('#cp-platform').val();
                    const tone = $('#cp-tone').val();

                    if (!topic) return alert('Inserisci un argomento.');

                    btn.prop('disabled', true).text('Generazione in corso (attendi 5-10s)...');

                    $.ajax({
                        url: '<?php echo esc_url_raw(rest_url('cricrm/v1/campaign')); ?>',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({
                            topic: topic,
                            platform: platform,
                            tone: tone
                        }),
                        success: function(res) {
                            if (res.text) {
                                $('#cp-result-container').removeClass('hidden');
                                $('#cp-result').val(res.text);
                                btn.prop('disabled', false).text('✨ Genera con Erika');
                            }
                        },
                        error: function(err) {
                            console.error(err);
                            alert('Errore generazione: ' + (err.responseJSON?.message || 'Errore sconosciuto'));
                            btn.prop('disabled', false).text('✨ Genera con Erika');
                        }
                    });
                });
            });
        </script>
    </div>
</div>