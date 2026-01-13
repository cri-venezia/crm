'use server'

import * as Brevo from '@getbrevo/brevo'
import { createServerClient, type CookieOptions } from '@supabase/ssr'
import { cookies } from 'next/headers'

// Initialize Brevo
const apiInstance = new Brevo.TransactionalEmailsApi()
const apiKey = process.env.BREVO_API_KEY
if (apiKey) {
    apiInstance.setApiKey(Brevo.TransactionalEmailsApiApiKeys.apiKey, apiKey)
}

export async function sendNewsletter(formData: FormData) {
    const templateId = parseInt(formData.get('templateId') as string)
    const recipientType = formData.get('recipientType') as string

    if (!templateId || isNaN(templateId)) {
        return { error: "ID Template non valido." }
    }

    const cookieStore = cookies()
    const supabase = createServerClient(
        process.env.NEXT_PUBLIC_SUPABASE_URL!,
        process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!,
        {
            cookies: {
                get(name: string) { return cookieStore.get(name)?.value },
                set(name: string, value: string, options: CookieOptions) { cookieStore.set({ name, value, ...options }) },
                remove(name: string, options: CookieOptions) { cookieStore.set({ name, value: '', ...options }) },
            },
        }
    )

    // 1. Check Auth & Permissions
    const { data: { user } } = await supabase.auth.getUser()
    if (!user) return { error: "Non autenticato" }

    const { data: profile } = await supabase.from('profiles').select('role').eq('id', user.id).single()
    if (!profile || (profile.role !== 'admin' && profile.role !== 'newsletter')) {
        return { error: "Permesso negato. Richiesto ruolo Admin o Newsletter." }
    }

    // 2. Fetch Recipients
    let query = supabase.from('contacts').select('email, first_name')
    if (recipientType !== 'all') {
        query = query.eq('type', recipientType)
    }

    const { data: contacts, error } = await query

    if (error || !contacts || contacts.length === 0) {
        return { error: "Nessun destinatario trovato." }
    }

    // 3. Send Emails via Template
    let successCount = 0
    let failureCount = 0

    // Using basic loop for MVP. 
    for (const contact of contacts) {
        if (!contact.email) continue

        const sendSmtpEmail = new Brevo.SendSmtpEmail()
        sendSmtpEmail.templateId = templateId
        // We pass the email; name matches Supabase data to Brevo params if template uses {{contact.FIRSTNAME}} etc.
        // Standard Brevo param structure depends on template. 
        // We'll pass basic params just in case.
        sendSmtpEmail.params = {
            NOME: contact.first_name,
            EMAIL: contact.email
        }
        sendSmtpEmail.to = [{ email: contact.email, name: contact.first_name }]

        try {
            await apiInstance.sendTransacEmail(sendSmtpEmail)
            successCount++
        } catch (e) {
            console.error(`Failed to send to ${contact.email}`, e)
            failureCount++
        }
    }

    return {
        message: `Campagna inviata (Template ${templateId})! Successi: ${successCount}, Errori: ${failureCount}`,
        stats: { success: successCount, failure: failureCount }
    }
}
