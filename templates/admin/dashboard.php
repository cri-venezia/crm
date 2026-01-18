<!-- Wrappers to scope Tailwind and prevent bleeding -->
<div class="wrap cricrm-admin-wrapper">
    <div class="bg-white rounded-lg shadow-md p-6 max-w-7xl mx-auto mt-5">

        <?php
        $current_user = wp_get_current_user();
        $is_admin = current_user_can('manage_options');
        $is_newsletter = current_user_can('manage_newsletter');
        $is_fundraiser = current_user_can('manage_fundraising');

        // --- COMMON HEADER ---
        ?>
        <div class="border-b border-gray-200 pb-5 mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-[#CC0000] flex items-center gap-2">
                    <span class="dashicons dashicons-heart text-4xl"></span>
                    CRI Venezia - CRM
                </h1>
                <p class="text-gray-500 mt-1">
                    <?php
                    if ($is_admin) echo "Pannello di Controllo Operativo";
                    elseif ($is_newsletter) echo "Workspace Newsletter";
                    elseif ($is_fundraiser) echo "Workspace Fundraising";
                    else echo "Area Personale";
                    ?>
                </p>
            </div>
            <div class="flex flex-col items-end">
                <span class="bg-gray-100 px-3 py-1 rounded-full font-mono text-xs text-gray-600 mb-2">v<?php echo CRI_CRM_VERSION; ?></span>
                <span class="text-sm font-medium text-gray-700">Ciao, <?php echo esc_html($current_user->display_name); ?></span>
            </div>
        </div>

        <?php if ($is_admin) : ?>
            <!-- ADMIN TABS -->
            <div class="mb-6 border-b border-gray-200">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button type="button" class="cricrm-tab-btn border-[#CC0000] text-[#CC0000] whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-target="tab-overview">
                        📊 Overview
                    </button>
                    <button type="button" class="cricrm-tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-target="tab-newsletter">
                        📢 Newsletter
                    </button>
                    <button type="button" class="cricrm-tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-target="tab-fundraising">
                        💡 Fundraising
                    </button>
                    <button type="button" class="cricrm-tab-btn border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm" data-target="tab-settings">
                        ⚙️ Configurazioni
                    </button>
                </nav>
            </div>
        <?php endif; ?>

        <?php
        // --- LOGIC: If Admin, show all tabs hidden/shown via JS. If Specialist, show only specific content.
        // We render ALL sections for Admin, but only relevant one for others.
        ?>

        <!-- === TAB 1: OVERVIEW (Admin Only) === -->
        <?php if ($is_admin) : ?>
            <div id="tab-overview" class="cricrm-tab-content">
                <!-- Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Card 1: Users -->
                    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded shadow-sm">
                        <h3 class="font-bold text-gray-700 text-lg mb-1">Volontari Attivi</h3>
                        <p class="text-3xl font-bold text-blue-600">
                            <?php
                            $user_count = count_users();
                            echo $user_count['total_users'];
                            ?>
                        </p>
                        <a href="<?php echo admin_url('users.php'); ?>" class="text-sm text-blue-600 hover:underline mt-2 inline-block">Gestisci Utenti &rarr;</a>
                    </div>

                    <!-- Card 2: Chat Logs -->
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm">
                        <h3 class="font-bold text-gray-700 text-lg mb-1">Conversazioni AI</h3>
                        <p class="text-3xl font-bold text-green-600">
                            <?php
                            global $wpdb;
                            echo $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}cricrm_chat_logs");
                            ?>
                        </p>
                        <p class="text-xs text-gray-500">Messaggi totali salvati</p>
                    </div>

                    <!-- Card 3: Quick Actions -->
                    <div class="bg-red-50 border-l-4 border-[#CC0000] p-4 rounded shadow-sm">
                        <h3 class="font-bold text-gray-700 text-lg mb-1">Azioni Rapide</h3>
                        <div class="flex flex-col gap-2 mt-2">
                            <?php
                            $nl_page = get_option('cri_crm_newsletter_page_id');
                            $cp_page = get_option('cri_crm_campaign_page_id');
                            ?>
                            <a href="<?php echo $nl_page ? get_permalink($nl_page) : '#'; ?>" target="_blank" class="bg-white border border-[#CC0000] text-[#CC0000] px-3 py-1 rounded hover:bg-red-50 text-sm transition text-center flex items-center justify-center gap-2">
                                <span class="dashicons dashicons-external"></span> Newsletter (Frontend)
                            </a>
                            <a href="<?php echo $cp_page ? get_permalink($cp_page) : '#'; ?>" target="_blank" class="bg-white border border-[#CC0000] text-[#CC0000] px-3 py-1 rounded hover:bg-red-50 text-sm transition text-center flex items-center justify-center gap-2">
                                <span class="dashicons dashicons-external"></span> Campagna (Frontend)
                            </a>
                            <div class="border-t border-red-200 my-1"></div>
                            <a href="<?php echo admin_url('admin.php?page=cricrm-dashboard&cricrm_action=import_logs'); ?>" onclick="return confirm('Importare i log da JSON? Assicurati che il file backup/chat_logs.json esista.')" class="text-xs text-gray-500 hover:text-red-600 text-center">
                                ♻️ Importa Backup Log
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Logs Table -->
                <div class="bg-white border rounded-lg overflow-hidden mb-8">
                    <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                        <h3 class="font-bold text-gray-700">Ultime Attività Chat (Globali)</h3>
                    </div>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="text-sm text-gray-500 border-b">
                                <th class="p-3 font-medium">Utente</th>
                                <th class="p-3 font-medium">Messaggio</th>
                                <th class="p-3 font-medium">Risposta AI</th>
                                <th class="p-3 font-medium">Data</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <?php
                            $logs = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}cricrm_chat_logs ORDER BY created_at DESC LIMIT 5");
                            if ($logs) {
                                foreach ($logs as $log) {
                                    $u_info = get_userdata($log->user_id);
                                    echo "<tr class='border-b hover:bg-gray-50'>";
                                    echo "<td class='p-3 font-medium'>" . ($u_info ? esc_html($u_info->display_name) : 'Ospite') . "</td>";
                                    echo "<td class='p-3 text-gray-600 truncate max-w-xs'>" . esc_html($log->message_input) . "</td>";
                                    echo "<td class='p-3 text-gray-600 truncate max-w-xs'>" . esc_html(substr($log->message_output, 0, 50)) . "...</td>";
                                    echo "<td class='p-3 text-gray-400'>" . date_i18n('d M H:i', strtotime($log->created_at)) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='p-4 text-center text-gray-500'>Nessuna attività recente.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>


        <!-- === TAB 2: NEWSLETTER (Admin + Newsletter Role) === -->
        <?php if ($is_admin || $is_newsletter) : ?>
            <div id="tab-newsletter" class="cricrm-tab-content <?php echo $is_admin ? 'hidden' : ''; ?>">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Col 1: Bio (Only if not admin/pure newsletter view context, but let's keep it simple) -->
                    <div class="col-span-1">
                        <?php
                        // Avatar logic
                        $avatar_url = get_avatar_url($current_user->ID, ['size' => 128]);
                        $sede = get_user_meta($current_user->ID, 'sede', true) ?: 'N/A';
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
        <?php endif; ?>


        <!-- === TAB 3: FUNDRAISING (Admin + Fundraiser Role) === -->
        <?php if ($is_admin || $is_fundraiser) : ?>
            <div id="tab-fundraising" class="cricrm-tab-content <?php echo $is_admin ? 'hidden' : ''; ?>">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Col 1: Bio -->
                    <div class="col-span-1">
                        <div class="bg-white border rounded-lg shadow-sm p-6 text-center">
                            <h2 class="text-xl font-bold text-gray-800"><?php echo esc_html($current_user->display_name); ?></h2>
                            <div class="inline-block bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide mt-2">
                                Fundraiser / Admin
                            </div>
                        </div>
                    </div>

                    <!-- Col 2: Campaign Generator -->
                    <div class="col-span-2">
                        <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                            <div class="bg-blue-50 px-6 py-4 border-b border-blue-100">
                                <h3 class="font-bold text-blue-800 text-lg">💡 Generatore Campagne AI</h3>
                                <p class="text-sm text-gray-600">Crea post social e campagne fundraising con Gemini.</p>
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
                                        ✨ Genera con AI
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
                                        btn.prop('disabled', false).text('✨ Genera con AI');
                                    }
                                },
                                error: function(err) {
                                    console.error(err);
                                    alert('Errore generazione: ' + (err.responseJSON?.message || 'Errore sconosciuto'));
                                    btn.prop('disabled', false).text('✨ Genera con AI');
                                }
                            });
                        });
                    });
                </script>
            </div>
        <?php endif; ?>


        <!-- === TAB 4: SETTINGS (Admin Only) === -->
        <?php if ($is_admin) : ?>
            <div id="tab-settings" class="cricrm-tab-content hidden">
                <div class="bg-white border rounded-lg overflow-hidden shadow-sm">
                    <div class="bg-gray-50 px-4 py-3 border-b">
                        <h3 class="font-bold text-gray-700">Configurazione API (Admin)</h3>
                    </div>
                    <div class="p-6">
                        <form method="post" action="options.php">
                            <?php settings_fields('cricrm_options_group'); ?>
                            <?php do_settings_sections('cricrm_options_group'); ?>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gemini API Key</label>
                                    <input type="password" name="cri_crm_gemini_key" value="<?php echo esc_attr(get_option('cri_crm_gemini_key')); ?>" class="w-full p-2 border rounded focus:ring-red-500 focus:border-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Brevo API Key</label>
                                    <input type="password" name="cri_crm_brevo_key" value="<?php echo esc_attr(get_option('cri_crm_brevo_key')); ?>" class="w-full p-2 border rounded focus:ring-red-500 focus:border-red-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Pagina Newsletter (Frontend)</label>
                                    <input type="number" name="cri_crm_newsletter_page_id" value="<?php echo esc_attr(get_option('cri_crm_newsletter_page_id')); ?>" class="w-full p-2 border rounded focus:ring-red-500 focus:border-red-500" placeholder="Es. 42">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Pagina Campagne (Frontend)</label>
                                    <input type="number" name="cri_crm_campaign_page_id" value="<?php echo esc_attr(get_option('cri_crm_campaign_page_id')); ?>" class="w-full p-2 border rounded focus:ring-red-500 focus:border-red-500" placeholder="Es. 43">
                                </div>
                            </div>

                            <div class="mt-4">
                                <?php submit_button('Salva Configurazioni'); ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>


        <!-- Footer / Volunteer View Fallback -->
        <?php if (!$is_admin && !$is_newsletter && !$is_fundraiser) : ?>
            <div class="text-center py-12">
                <div class="bg-gray-100 rounded-full h-24 w-24 flex items-center justify-center mx-auto mb-4">
                    <span class="dashicons dashicons-id text-4xl text-gray-400"></span>
                </div>
                <h2 class="text-xl font-bold text-gray-700">Area Riservata Volontari</h2>
                <p class="text-gray-500 max-w-md mx-auto mt-2">
                    Benvenuto, <?php echo esc_html($current_user->display_name); ?>.
                    Utilizza i widget presenti nelle pagine del sito.
                </p>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- TABS LOGIC JS -->
<script>
    jQuery(document).ready(function($) {
        $('.cricrm-tab-btn').on('click', function() {
            // Remove active state from all buttons
            $('.cricrm-tab-btn').removeClass('border-[#CC0000] text-[#CC0000] font-medium').addClass('border-transparent text-gray-500 hover:text-gray-700 font-medium');

            // Add active state to clicked
            $(this).removeClass('border-transparent text-gray-500 hover:text-gray-700').addClass('border-[#CC0000] text-[#CC0000]');

            // Hide all tab contents
            $('.cricrm-tab-content').addClass('hidden');

            // Show target
            const target = $(this).data('target');
            $('#' + target).removeClass('hidden');
        });
    });
</script>