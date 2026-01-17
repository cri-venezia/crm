
import { createClient } from '@supabase/supabase-js';

export async function handleChat(request, env, corsHeaders) {
    if (request.method !== "POST") {
        return new Response("Method Not Allowed", { status: 405, headers: corsHeaders });
    }

    try {
        const body = await request.json();
        const { message, history, userContext } = body;
        // userContext = { id, name, role, group, location }

        // 1. Convert history to internal format
        const rawMessages = (history || []).map(msg => ({
            role: msg.role === 'model' ? 'model' : 'user',
            parts: [{ text: msg.content }]
        }));

        if (message) {
            rawMessages.push({
                role: 'user',
                parts: [{ text: message }]
            });
        }

        // 2. Contextual System Prompt
        let systemPrompt = "Sei Erika, l'assistente operativa della Croce Rossa di Venezia. Rispondi in italiano.";

        if (userContext) {
            const { name, role, group, location } = userContext;
            systemPrompt += `\nInterloquisci con: ${name || 'Volontario'}.`;
            if (role === 'admin') systemPrompt += "\nRuolo: Super Amministratore (può fare tutto).";
            if (group) systemPrompt += `\nGruppo: ${group}.`;
            if (location) systemPrompt += `\nSede Operativa: ${location}.`;
        }

        // 3. Call Gemini API
        const apiKey = env.GOOGLE_AI_KEY;
        if (!apiKey) throw new Error('CONFIG ERROR: GOOGLE_AI_KEY missing.');

        const model = 'gemini-3-pro-preview';
        const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;

        const payload = {
            system_instruction: { parts: [{ text: systemPrompt }] },
            contents: rawMessages || []
        };

        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            const errText = await response.text();
            throw new Error(`Gemini API Error: ${errText}`);
        }

        const data = await response.json();
        const aiText = data.candidates?.[0]?.content?.parts?.[0]?.text || "Non ho capito.";

        // 4. Server-Side Persistence (Fire and Forget or Await)
        if (userContext && userContext.id) {
            const supabase = createClient(env.SUPABASE_URL, env.SUPABASE_SERVICE_ROLE_KEY);
            await supabase.from('ai_chat_logs').insert({
                user_id: userContext.id,
                message_input: message,
                message_output: aiText
            });
        }

        return new Response(JSON.stringify({ text: aiText }), {
            headers: { ...corsHeaders, 'Content-Type': 'application/json' },
        });

    } catch (error) {
        return new Response(JSON.stringify({ error: error.message }), {
            headers: { ...corsHeaders, 'Content-Type': 'application/json' },
            status: 500,
        });
    }
}
