<div class="wrap cricrm-admin-wrapper">
    <div class="bg-white rounded-lg shadow-md p-4 md:p-6 w-full mx-auto mt-5 flex flex-col">
        <h1 class="text-3xl font-bold text-[#CC0000] mb-4 flex items-center gap-2">
            <!-- Venus Icon SVG -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 fill-current" viewBox="0 0 320 512">
                <path d="M160 0a160 160 0 1 1 0 320 160 160 0 1 1 0-320zM128 352v64H80c-17.7 0-32 14.3-32 32s14.3 32 32 32h48v32c0 17.7 14.3 32 32 32s32-14.3 32-32V480h48c17.7 0 32-14.3 32-32s-14.3-32-32-32H192V352H128z" />
            </svg>
            Erika
        </h1>
        <p class="text-sm text-gray-500 mb-4">
            Utilizza questa interfaccia per interagire con Erika senza dover usare il widget frontend.
            Questa chat è collegata allo stesso account utente.
        </p>

        <!-- Chat Container -->
        <div id="cri-chat-history" class="flex-1 overflow-y-auto border border-gray-200 rounded-lg p-4 bg-gray-50 mb-4 space-y-3">
            <!-- Messages injected here -->
            <div class="text-center text-gray-400 text-sm mt-10">Caricamento cronologia...</div>
        </div>

        <!-- Input Area -->
        <div class="flex gap-2">
            <input type="text" id="cri-chat-input" class="flex-1 p-3 border rounded-lg focus:ring-red-500 focus:border-red-500 shadow-sm" placeholder="Scrivi un messaggio a Erika..." onkeydown="if(event.key === 'Enter') document.getElementById('cri-chat-send').click()">
            <button id="cri-chat-send" class="bg-[#CC0000] text-white px-6 py-3 rounded-lg font-bold hover:bg-[#8a0000] transition flex items-center gap-2">
                <!-- Paper Plane Icon SVG -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 fill-current" viewBox="0 0 512 512">
                    <path d="M498.1 5.6c10.1 7 15.4 19.1 13.5 31.2l-64 416c-1.5 9.7-7.4 18.2-16 23s-18.9 5.4-28 1.6L284 427.7l-68.5 74.1c-8.9 9.7-22.9 12.9-35.2 8.1S160 493.2 160 480V396.4c0-4 1.5-7.8 4.2-10.7l167.6-182.9c5.8-6.3 5.6-16-.4-22s-15.7-6.4-22-.7L106 360.8 17.7 316.6C7.1 311.3 .3 300.7 0 288.9s5.9-22.8 16.1-28.7l448-256c10.7-6.1 23.9-5.5 34 1.4z" />
                </svg>
                Invia
            </button>
        </div>

        <!-- Markdown Support -->
        <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/dompurify/dist/purify.min.js"></script>

        <script>
            jQuery(document).ready(function($) {
                const historyDiv = $('#cri-chat-history');
                const chatInput = $('#cri-chat-input');
                const sendBtn = $('#cri-chat-send');
                let chatHistory = [];

                function scrollToBottom() {
                    setTimeout(() => {
                        $('html, body').animate({
                            scrollTop: $(document).height()
                        }, 100);
                    }, 100);
                }

                function renderMessage(role, text) {
                    const align = role === 'user' ? 'justify-end' : 'justify-start';
                    const bg = role === 'user' ? 'bg-red-100 text-gray-800' : 'bg-white border border-gray-200 text-gray-700';
                    const name = role === 'user' ? 'Tu' : 'Erika';

                    // Parse Markdown (only for model? No, user might type MD too)
                    let htmlContent = text;
                    if (typeof marked !== 'undefined') {
                        // Configure marked to handle line breaks correctly
                        marked.use({
                            breaks: true
                        });
                        const rawHtml = marked.parse(text);
                        htmlContent = DOMPurify.sanitize(rawHtml);
                    }

                    const html = `
                    <div class="flex ${align}">
                        <div class="max-w-[80%] ${bg} rounded-lg px-4 py-2 shadow-sm">
                            <div class="text-xs font-bold mb-1 opacity-50">${name}</div>
                            <!-- Added 'prose' for proper Markdown styling -->
                            <div class="prose prose-sm max-w-none leading-normal ${role === 'user' ? 'prose-p:text-gray-800' : 'prose-p:text-gray-700'}">
                                ${htmlContent}
                            </div>
                        </div>
                    </div>
                `;
                    historyDiv.append(html);
                    scrollToBottom();
                }

                function showTypingIndicator() {
                    const html = `
                    <div id="cri-chat-typing" class="flex justify-start">
                        <div class="bg-white border border-gray-200 rounded-lg px-4 py-3 shadow-sm flex items-center gap-2">
                            <div class="text-xs font-bold mr-1 opacity-50">Erika</div>
                            <div class="flex space-x-1">
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                                <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                            </div>
                            <span class="text-xs text-gray-400 ml-2">sta scrivendo...</span>
                        </div>
                    </div>`;
                    historyDiv.append(html);
                    scrollToBottom();
                }

                function hideTypingIndicator() {
                    $('#cri-chat-typing').remove();
                }

                // Load History
                $.ajax({
                    url: '<?php echo esc_url_raw(rest_url('cricrm/v1/history')); ?>',
                    method: 'GET',
                    beforeSend: function(xhr) {
                        xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                    },
                    success: function(data) {
                        historyDiv.empty();
                        if (data && data.length) {
                            data.forEach(msg => {
                                renderMessage(msg.role, msg.content);
                                chatHistory.push({
                                    sender: msg.role === 'user' ? 'user' : 'model',
                                    text: msg.content
                                });
                            });
                        } else {
                            historyDiv.html('<div class="text-center text-gray-400 text-sm mt-10">Nessuna conversazione precedente. Inizia a scrivere!</div>');
                        }
                    },
                    error: function() {
                        historyDiv.html('<div class="text-center text-red-500">Errore caricamento chat.</div>');
                    }
                });

                // Send Message
                sendBtn.on('click', function() {
                    const msg = chatInput.val().trim();
                    if (!msg) return;

                    // Optimistic Render
                    renderMessage('user', msg);
                    if (historyDiv.find('.text-center').length) historyDiv.find('.text-center').remove();
                    chatInput.val('');
                    chatInput.prop('disabled', true);

                    // Show typing indicator
                    showTypingIndicator();

                    // Add to history BEFORE sending to API
                    chatHistory.push({
                        sender: 'user',
                        text: msg
                    });

                    $.ajax({
                        url: '<?php echo esc_url_raw(rest_url('cricrm/v1/chat')); ?>',
                        method: 'POST',
                        beforeSend: function(xhr) {
                            xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce('wp_rest'); ?>');
                        },
                        contentType: 'application/json',
                        data: JSON.stringify({
                            message: msg,
                            history: chatHistory // Send updated history
                        }),
                        success: function(res) {
                            hideTypingIndicator(); // Hide typing
                            chatInput.prop('disabled', false).focus();
                            if (res.text) {
                                renderMessage('model', res.text);
                                chatHistory.push({
                                    sender: 'model',
                                    text: res.text
                                });
                            }
                        },
                        error: function(err) {
                            hideTypingIndicator(); // Hide typing
                            chatInput.prop('disabled', false);
                            renderMessage('model', '⚠️ Errore di connessione: ' + (err.responseJSON?.message || 'Server error'));
                        }
                    });
                });
            });
        </script>
    </div>
</div>