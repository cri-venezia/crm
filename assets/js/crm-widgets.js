document.addEventListener('DOMContentLoaded', function () {

    // --- Campaign Generator Logic ---
    const campaignForm = document.getElementById('cri-campaign-form');
    if (campaignForm) {
        campaignForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = campaignForm.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Generazione...';
            btn.disabled = true;

            const topic = document.getElementById('campaign-topic').value;
            const platform = document.getElementById('campaign-platform').value;
            const tone = document.getElementById('campaign-tone').value;

            try {
                const response = await fetch('/wp-json/cricrm/v1/campaign', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': criCrmSettings.nonce
                    },
                    body: JSON.stringify({ topic, platform, tone })
                });

                const data = await response.json();

                if (response.ok) {
                    document.getElementById('campaign-result').classList.remove('hidden');
                    document.getElementById('campaign-output').value = data.text;
                } else {
                    alert('Errore: ' + (data.message || 'Errore sconosciuto'));
                }
            } catch (error) {
                alert('Errore di connessione');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }

    // --- Newsletter Sender Logic ---
    const newsletterForm = document.getElementById('cri-newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!confirm('Sei sicuro di voler inviare questa newsletter?')) return;

            const btn = newsletterForm.querySelector('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<span class="dashicons dashicons-update spin"></span> Invio in corso...';
            btn.disabled = true;

            const subject = document.getElementById('newsletter-subject').value;
            const content = document.getElementById('newsletter-content').value;

            try {
                const response = await fetch('/wp-json/cricrm/v1/newsletter', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': criCrmSettings.nonce
                    },
                    body: JSON.stringify({ subject, content })
                });

                const data = await response.json();
                const statusDiv = document.getElementById('newsletter-status');
                statusDiv.classList.remove('hidden');

                if (response.ok) {
                    statusDiv.className = 'mt-4 p-3 rounded text-sm bg-green-100 text-green-800 border border-green-200';
                    statusDiv.innerHTML = '✅ Newsletter inviata con successo!';
                    newsletterForm.reset();
                } else {
                    statusDiv.className = 'mt-4 p-3 rounded text-sm bg-red-100 text-red-800 border border-red-200';
                    statusDiv.innerHTML = '❌ Errore: ' + (data.message || 'Errore sconosciuto');
                }
            } catch (error) {
                alert('Errore di connessione');
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }
});
