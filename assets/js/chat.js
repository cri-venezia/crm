document.addEventListener('DOMContentLoaded', function () {
    const chatInput = document.getElementById('cricrm-chat-input');
    const sendBtn = document.getElementById('cricrm-chat-send');
    const clearBtn = document.getElementById('cricrm-chat-clear');
    const chatHistory = document.getElementById('cricrm-chat-history');

    if (!chatInput || !sendBtn) return; // Not on a page with the widget

    // Auto-scroll to bottom
    const scrollToBottom = () => {
        chatHistory.scrollTop = chatHistory.scrollHeight;
    };

    // Add Message to UI
    const appendMessage = (text, type) => {
        const msgDiv = document.createElement('div');
        msgDiv.className = `cricrm-msg ${type}`;
        msgDiv.innerHTML = `<div class="cricrm-msg-content">${text}</div>`;
        chatHistory.appendChild(msgDiv);
        scrollToBottom();
    };

    // Load History
    const loadHistory = async () => {
        if (!CRICrmConfig.currentUser) return;

        try {
            const res = await fetch(`${CRICrmConfig.root}cricrm/v1/history`, {
                headers: { 'X-WP-Nonce': CRICrmConfig.nonce }
            });
            const data = await res.json();

            if (Array.isArray(data)) {
                data.forEach(msg => {
                    // map role 'user' -> 'user', 'assistant' -> 'model' for UI class
                    const type = msg.role === 'user' ? 'user' : 'model';
                    appendMessage(msg.content, type);
                });
            }
        } catch (e) {
            console.error("Failed to load history", e);
        }
    };

    // Initial Load
    // Initial Load
    loadHistory();

    // Send Message Handler
    const handleSend = async () => {
        const text = chatInput.value.trim();
        if (!text) return;

        // UI Updates
        appendMessage(text, 'user');
        chatInput.value = '';
        chatInput.disabled = true;

        // Show Typing Indicator (simple version)
        const typingId = 'cricrm-typing-' + Date.now();
        const typingDiv = document.createElement('div');
        typingDiv.className = 'cricrm-msg model typing';
        typingDiv.id = typingId;
        typingDiv.innerHTML = `<div class="cricrm-msg-content">...</div>`;
        chatHistory.appendChild(typingDiv);
        scrollToBottom();

        try {
            const res = await fetch(`${CRICrmConfig.root}cricrm/v1/chat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': CRICrmConfig.nonce
                },
                body: JSON.stringify({ message: text })
            });

            const data = await res.json();

            // Remove typing indicator
            document.getElementById(typingId)?.remove();

            if (data.text) {
                appendMessage(data.text, 'model');
            } else {
                appendMessage("Errore: Impossibile contattare Erika.", 'model error');
            }

        } catch (e) {
            document.getElementById(typingId)?.remove();
            appendMessage("Errore di connessione.", 'model error');
            console.error(e);
        } finally {
            chatInput.disabled = false;
            chatInput.focus();
        }
    };

    sendBtn.addEventListener('click', handleSend);
    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') handleSend();
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', async () => {
            if (!confirm('Sei sicuro di voler cancellare tutta la cronologia?')) return;

            try {
                const res = await fetch(`${CRICrmConfig.root}cricrm/v1/chat`, {
                    method: 'DELETE',
                    headers: { 'X-WP-Nonce': CRICrmConfig.nonce }
                });

                if (res.ok) {
                    chatHistory.innerHTML = '';
                    // Re-append welcome message (optional, but nice)
                    // Currently hard to re-fetch settings without reloading, so we just leave empty or reload.
                    // Let's just create a system message
                    appendMessage('Cronologia cancellata.', 'model');
                } else {
                    alert('Errore durante la cancellazione.');
                }
            } catch (e) {
                console.error(e);
                alert('Errore di connessione.');
            }
        });
    }
});
