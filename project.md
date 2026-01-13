# PROGETTO: CRM Croce Rossa Italiana - Comitato di Venezia

## 1. Contesto Generale
Stiamo sviluppando un mini-CRM su misura per il Comitato di Venezia della Croce Rossa Italiana.
L'utente principale è un professionista sanitario del 118 / SUEM (Servizio di Urgenza ed Emergenza Medica) di Venezia.
L'obiettivo è gestire Anagrafica (Volontari/Donatori), Newsletter, Fundraising e includere un assistente AI avanzato chiamato "Erika".

## 2. Tech Stack
- **Frontend:** Next.js 14 (App Router), TypeScript, Tailwind CSS.
- **UI Library:** Shadcn/ui (basata su Radix UI).
- **Backend/DB:** Supabase (PostgreSQL, Auth, Row Level Security).
- **AI:** Google Gemini Pro (tramite SDK `@google/generative-ai`).
- **Email:** Brevo (ex Sendinblue) API.
- **Deploy:** Cloudflare Pages (adattatore `@cloudflare/next-on-pages`).

## 3. Database Schema (Supabase)
- **profiles:** Estensione di `auth.users`. Campi: `role` ('admin', 'fundraising', 'newsletter', 'volontario').
- **contacts:** Gestione anagrafica unificata. Campi: `type` ('volontario', 'sostenitore', 'donatore'), `tags` (array), `notes`.
- **ai_chat_logs:** Storico conversazioni con Erika.

## 4. Persona AI: "ERIKA"
Erika è l'assistente virtuale integrata. Deve essere estremamente competente, formale ma empatica.

### Knowledge Base Operativa (Venezia & SUEM 118)
Erika DEVE conoscere e usare correttamente i seguenti acronimi e concetti, specifici del contesto Veneto:

**Ambito Sanitario / SUEM:**
- **SUEM 118:** Servizio di Urgenza ed Emergenza Medica (Sistema regionale Veneto che coordina i soccorsi).
- **CO:** Centrale Operativa.
- **TSSA:** Trasporto Sanitario e Soccorso in Ambulanza (Corso base per volontari soccorritori).
- **TS:** Trasporto Sanitario (secondario, dimissioni, visite).
- **MSB:** Mezzo di Soccorso di Base (Ambulanza con soli soccorritori).
- **MSA:** Mezzo di Soccorso Avanzato (Ambulanza con Medico/Infermiere).
- **Idroambulanza:** Mezzo di soccorso nautico (specifico laguna di Venezia).
- **Target:** Codici di gravità (Rosso, Giallo, Verde, Bianco, Nero).
- **Valutazione:** ABCDE (Airway, Breathing, Circulation, Disability, Exposure), OPQRST (dolore), SAMPLE (anamnesi).

**Ambito CRI (Croce Rossa):**
- **7 Principi:** Umanità, Imparzialità, Neutralità, Indipendenza, Volontarietà, Unità, Universalità.
- **OPSA:** Operatori Polivalenti Salvataggio in Acqua.
- **SMTS:** Soccorsi con Mezzi e Tecniche Speciali.
- **TLC:** Telecomunicazioni.
- **DIU:** Diritto Internazionale Umanitario.
- **SeLeMa:** Servizio Restoring Family Links.

### Funzioni di Erika
1.  **Fundraising & Copywriting:** Scrivere email e testi per campagne donazioni usando un tono che ispiri fiducia e urgenza etica.
2.  **Supporto Operativo:** Se l'utente chiede "Prepara una checklist per turno in idroambulanza", Erika deve sapere cosa serve (Dae, ossigeno, aspiratore, spinale, cime d'ormeggio, parabordi).
3.  **Newsletter:** Generare bozze HTML o testo per Brevo.

## 5. Regole di Coding
- Usa sempre **TypeScript** rigoroso.
- Usa **Server Components** di default in Next.js. Usa `'use client'` solo quando necessario (gestione stato, hook).
- Per le chiamate al DB, usa `@supabase/ssr` nelle Server Actions o API Routes.
- Non esporre mai chiavi private (Service Role) lato client.
- Usa `lucide-react` per le icone standard.

## 6. Prompt Engineering "In-Code"
Quando generi codice per le API di Gemini, includi sempre nel `systemInstruction` il riferimento al ruolo dell'utente (Sanitario SUEM 118) per calibrare il livello tecnico delle risposte.

## 7. UI & Icone Custom
Per le icone specifiche (es. Idroambulanza, Simboli CRI, Loghi SUEM) che non esistono in Lucide:
- Creare i file in `components/icons/`.
- Usare la funzione `createLucideIcon` da `lucide-react` per mantenere la compatibilità delle props (`size`, `className`, ecc.).
- **Specifiche Grafiche:**
    - Grid: 24x24 px.
    - Stroke Width: 2px.
    - Style: `stroke="currentColor"`, `fill="none"`, `strokeLinecap="round"`, `strokeLinejoin="round"`.