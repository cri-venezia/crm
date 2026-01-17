
export async function handleNewsletter(request, env, corsHeaders) {
    if (request.method !== "POST") {
        return new Response("Method Not Allowed", { status: 405, headers: corsHeaders });
    }

    try {
        const { title, articles, templateId, testEmail } = await request.json();

        if (!env.BREVO_API_KEY) throw new Error("Missing BREVO_API_KEY");
        if (!templateId) throw new Error("Missing templateId");

        const response = await fetch("https://api.brevo.com/v3/smtp/email", {
            method: "POST",
            headers: {
                "accept": "application/json",
                "api-key": env.BREVO_API_KEY,
                "content-type": "application/json"
            },
            body: JSON.stringify({
                sender: { name: "CRI Venezia", email: "newsletter@crivenezia.org" },
                to: testEmail ? [{ email: testEmail }] : [{ email: "newsletter-list@crivenezia.org" }],
                templateId: Number(templateId),
                params: {
                    title: title,
                    articles: articles // Array of { title, link, body, imageUrl }
                }
            })
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Brevo API Error: ${errorText}`);
        }

        return new Response(JSON.stringify({ success: true }), {
            headers: { ...corsHeaders, "Content-Type": "application/json" }
        });
    } catch (error) {
        return new Response(JSON.stringify({ error: error.message }), {
            status: 500,
            headers: { ...corsHeaders, "Content-Type": "application/json" }
        });
    }
}
