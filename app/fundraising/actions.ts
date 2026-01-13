'use server'

import { GoogleGenerativeAI } from "@google/generative-ai"
import { readFile } from 'fs/promises'
import path from 'path'

const genAI = new GoogleGenerativeAI(process.env.GOOGLE_API_KEY!)
const model = genAI.getGenerativeModel({ model: "gemini-1.5-pro-latest" })

export async function generateCampaign(formData: FormData) {
    const title = formData.get('title') as string
    const goal = formData.get('goal') as string
    const target = formData.get('target') as string
    const tone = formData.get('tone') as string || "Emozionale"
    const details = formData.get('details') as string || ""

    // 1. Load the Elementor Template
    let templateJsonStr = "";
    try {
        const templatePath = path.join(process.cwd(), 'elementor_template.json');
        templateJsonStr = await readFile(templatePath, 'utf-8');
    } catch (e) {
        console.error("Template not found", e);
        return { copy: "Errore: Template elementor_template.json non trovato.", layout: "" };
    }

    const prompt = `
        Sei Erika, l'assistente specialista in Fundraising e Web Design della Croce Rossa Italiana.
        Il tuo compito è creare i contenuti per una campagna di raccolta fondi basata su un template Landing Page esistente.

        DATI INIZIATIVA:
        - Titolo: ${title}
        - Obiettivo: ${goal}
        - Target: ${target}
        - Tono: ${tone}
        - Dettagli: ${details}

        Genera una risposta in formato JSON STRETTO (senza markdown code block) con due oggetti principali:
        
        1. "copy": Un testo in Markdown che include:
           - Oggetto Email (accattivante)
           - Corpo Email (persuasivo, usando principi di Cialdini)
           - 2 Post Social (Facebook/Instagram) con emoji.

        2. "fields": Un oggetto JSON piatto con i seguenti campi, adattati specificamente per l'iniziativa:
           - "hero_title": Titolo principale (H1) breve e d'impatto (max 6 parole).
           - "hero_text": Sottotitolo persuasivo (2-3 frasi).
           - "about_title": Titolo per la sezione "Di cosa si tratta".
           - "about_text": Descrizione emozionale dell'iniziativa e perché donare (2 paragrafi).
           - "cta_title": Titolo per la Call to Action finale (es. "Aiutaci ora").
           - "cta_text": Breve testo che spinge alla donazione immediata.

        Rispondi SOLAMENTE con il JSON valido.
    `

    try {
        const result = await model.generateContent(prompt)
        const response = result.response
        let text = response.text()

        // Cleanup JSON manually
        text = text.replace(/```json/g, '').replace(/```/g, '').trim()

        const data = JSON.parse(text)

        // 3. Inject into Template (Simple find-and-replace for specific "known" placeholders matches the template logic?)
        // Since we can't rely on IDs which might change, we will traverse the JSON or simpler: 
        // We will try to replace specific "generic" placeholders if they existed, but here we have a filled template ("Bocolo").
        // Strategy: We will Parse the JSON and try to find the "Heading" and "Text Editor" widgets likely corresponding to Hero, About, CTA based on their order or simple heuristics.

        const templateObj = JSON.parse(templateJsonStr);
        const modifiedTemplate = populateTemplate(templateObj, data.fields);

        return {
            copy: data.copy,
            layout: JSON.stringify(modifiedTemplate) // Return the JSON string to be downloaded
        }

    } catch (error) {
        console.error("Generazione fallita:", error)
        return {
            copy: "Errore nella generazione.",
            layout: ""
        }
    }
}

// Helper to heuristically populate the specific template structure provided
function populateTemplate(template: any, fields: any) {
    // Deep clone to avoid mutating original if cached (though here it's fresh string parse)
    const newTemplate = JSON.parse(JSON.stringify(template));

    // We know the structure from the provided JSON file.
    // Hero Section is likely the first section.
    // Hero Title ID: "79cf40c5" (Heading "Bòcolo per la Croce Rossa...")
    // Hero Text ID: "194f3cef" (Text Editor "Regala un gesto...")

    // About Section (Second Section)
    // About Title ID: "6fb531d3" ("Il Bòcolo")
    // About Text ID: "4b70d573" ("Il tradizionale bocciolo...")

    // CTA Section (Fourth Section likely, ID "7ebef1c2" was about, "1bd717cc" is CTA)
    // CTA Title ID: "61d5b221" ("Fai sbocciare...")
    // CTA Text ID: "74d0e1d1" ("L'intero ricavato...")

    // Simplistic mapping by ID for this SPECIFIC template version. 
    // Ideally we would search by "widgetType" and order, but IDs are safer *if* the user uses the exact same template file.
    // Since the user provided the file, we can map IDs.

    const idMap: Record<string, string> = {
        "79cf40c5": fields.hero_title, // Heading
        "194f3cef": `<p>${fields.hero_text}</p>`, // Text Editor
        "6fb531d3": fields.about_title, // Heading
        "4b70d573": `<p>${fields.about_text}</p>`, // Text Editor
        "61d5b221": fields.cta_title, // Heading
        "74d0e1d1": `<p>${fields.cta_text}</p>` // Text Editor
    };

    // Recursive function to find and update elements
    function traverse(elements: any[]) {
        if (!elements) return;

        for (const el of elements) {
            if (idMap[el.id]) {
                if (el.widgetType === 'heading') {
                    el.settings.title = idMap[el.id];
                } else if (el.widgetType === 'text-editor') {
                    el.settings.editor = idMap[el.id];
                }
            }
            if (el.elements && el.elements.length > 0) {
                traverse(el.elements);
            }
        }
    }

    if (newTemplate.content) {
        traverse(newTemplate.content);
    }

    return newTemplate;
}
