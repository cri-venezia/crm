
import { GoogleGenerativeAI } from "@google/generative-ai";

export async function handleFundraising(request, env, corsHeaders) {
    if (request.method !== "POST") {
        return new Response("Method Not Allowed", { status: 405, headers: corsHeaders });
    }

    try {
        const { topic, goal, tone } = await request.json();

        const genAI = new GoogleGenerativeAI(env.GOOGLE_API_KEY);
        const model = genAI.getGenerativeModel({ model: "gemini-3-pro-preview" });

        const prompt = `
          Sei una esperta di fundraising per la Croce Rossa Italiana.
          Genera il contenuto per una landing page di una campagna di raccolta fondi.
          
          Argomento: ${topic}
          Obiettivo: ${goal}
          Tono: ${tone}

          Restituisci SOLO un oggetto JSON valido (senza markdown o backticks) con questa struttura esatta:
          {
            "title": "Titolo accattivante",
            "subtitle": "Sottotitolo persuasivo",
            "hero_text": "Testo principale per la sezione hero (max 200 caratteri)",
            "problem_section": "Descrizione del problema o dell'emergenza",
            "solution_section": "Come la CRI interviene e risolve il problema",
            "call_to_action": "Testo per il pulsante di donazione (es. Dona ora)",
            "impact_text": "Cosa faremo con i fondi raccolti"
          }
        `;

        const result = await model.generateContent(prompt);
        let responseText = result.response.text();
        responseText = responseText.replace(/```json/g, '').replace(/```/g, '').trim();

        return new Response(responseText, {
            headers: { ...corsHeaders, "Content-Type": "application/json" }
        });
    } catch (error) {
        return new Response(JSON.stringify({ error: error.message }), {
            status: 500,
            headers: { ...corsHeaders, "Content-Type": "application/json" }
        });
    }
}
