import { GoogleGenerativeAI, SchemaType } from "@google/generative-ai";
import { createClient } from "@supabase/supabase-js";
import { NextResponse } from "next/server";

// Definizioni dei Tools
const tools = [
    {
        functionDeclarations: [
            {
                name: "search_contacts",
                description: "Cerca contatti nell'anagrafica per nome, cognome, email o telefono.",
                parameters: {
                    type: SchemaType.OBJECT,
                    properties: {
                        query: {
                            type: SchemaType.STRING,
                            description: "Il termine di ricerca (nome, cognome, email, telefono)"
                        },
                    },
                    required: ["query"],
                },
            },
            {
                name: "create_contact",
                description: "Crea un nuovo contatto nell'anagrafica. ATTENZIONE: Richiede permessi di amministratore.",
                parameters: {
                    type: SchemaType.OBJECT,
                    properties: {
                        first_name: { type: SchemaType.STRING, description: "Nome del contatto" },
                        last_name: { type: SchemaType.STRING, description: "Cognome del contatto" },
                        email: { type: SchemaType.STRING, description: "Email del contatto" },
                        phone: { type: SchemaType.STRING, description: "Numero di telefono" },
                        type: {
                            type: SchemaType.STRING,
                            description: "Tipo di contatto",
                            enum: ["volontario", "donatore", "sostenitore"]
                        },
                        tags: { type: SchemaType.STRING, description: "Tag separati da virgola (es. 'autista')" },
                        notes: { type: SchemaType.STRING, description: "Note aggiuntive" },
                    },
                    required: ["first_name", "last_name", "type"],
                },
            },
        ],
    },
] as any;

export async function POST(req: Request) {
    try {
        const supabaseUrl = process.env.NEXT_PUBLIC_SUPABASE_URL!;
        const supabaseKey = process.env.SUPABASE_SERVICE_ROLE_KEY!;
        const supabase = createClient(supabaseUrl, supabaseKey);

        const apiKey = process.env.GOOGLE_API_KEY;
        if (!apiKey) {
            throw new Error("GOOGLE_API_KEY is not defined");
        }
        const genAI = new GoogleGenerativeAI(apiKey);

        const { message, history, userId } = await req.json();

        // 1. Recupera profilo utente per il ruolo (RBAC)
        const { data: userProfile } = await supabase
            .from('profiles')
            .select('*')
            .eq('id', userId)
            .single();

        const userName = userProfile?.full_name || "Collega";
        const userRole = userProfile?.role || 'volontario';

        // 2. Configura il modello con Tools
        const model = genAI.getGenerativeModel({
            model: "gemini-3-pro-preview", // Updated to latest preview
            tools: tools,
            systemInstruction: `
            Sei Erika, l'assistente virtuale del Comitato di Venezia della Croce Rossa Italiana.
            Parli con ${userName} (Ruolo: ${userRole}).
            
            HAI ACCESSO AGLI STRUMENTI ANAGRAFICA:
            - Puoi cercare contatti.
            - Puoi creare contatti (SOLO se l'utente è ADMIN).
            
            REGOLE:
            - Se l'utente chiede di creare un contatto, ESEGUI la funzione create_contact. Non dire solo "lo faccio", fallo.
            - Se l'utente non è admin, la funzione restituirà un errore di permessi: spiegalo gentilmente all'utente.
            - Sii precisa e professionale.
        `
        });

        const chat = model.startChat({
            history: history.map((msg: any) => ({
                role: msg.role === 'user' ? 'user' : 'model',
                parts: [{ text: msg.content }], // Simplified history for text
            })),
        });

        // 3. Invio messaggio iniziale
        let result = await chat.sendMessage(message);
        let call = result.response.functionCalls()?.[0];

        // 4. Gestione Loop Function Calling
        // Gestiamp solo una chiamata per turno per semplicità, o un loop se necessario.
        // Google GenAI SDK di solito richiede di inviare la risposta della funzione.
        if (call) {
            const funcName = call.name;
            const funcArgs = call.args as any;
            let funcResponse = {};

            console.log("Erika Function Call:", funcName, funcArgs);

            if (funcName === "search_contacts") {
                const { data, error } = await supabase
                    .from('contacts')
                    .select('*')
                    .or(`first_name.ilike.%${funcArgs.query}%,last_name.ilike.%${funcArgs.query}%,email.ilike.%${funcArgs.query}%`)
                    .limit(5);

                if (error) funcResponse = { error: error.message };
                else funcResponse = { results: data };
            }
            else if (funcName === "create_contact") {
                // RBAC CHECK
                if (userRole !== 'admin') {
                    funcResponse = { error: "PERMESSO NEGATO: Solo gli amministratori possono creare contatti." };
                } else {
                    const { data, error } = await supabase
                        .from('contacts')
                        .insert({
                            first_name: funcArgs.first_name,
                            last_name: funcArgs.last_name,
                            email: funcArgs.email,
                            phone: funcArgs.phone,
                            type: funcArgs.type,
                            tags: funcArgs.tags ? funcArgs.tags.split(',').map((t: string) => t.trim()) : [],
                            notes: funcArgs.notes
                        })
                        .select()
                        .single();

                    if (error) funcResponse = { error: error.message };
                    else funcResponse = { success: true, contact: data };
                }
            }

            // Invia il risultato della funzione al modello
            result = await chat.sendMessage([
                {
                    functionResponse: {
                        name: funcName,
                        response: funcResponse,
                    },
                },
            ]);
        }

        const responseText = result.response.text();

        // 5. Log
        await supabase.from('ai_chat_logs').insert({
            user_id: userId,
            message_input: message,
            message_output: responseText,
        });

        return NextResponse.json({ response: responseText });

    } catch (error) {
        console.error("Errore Erika:", error);
        return NextResponse.json({ error: "Errore interno di Erika" }, { status: 500 });
    }
}
