'use server'

import { createServerClient, type CookieOptions } from '@supabase/ssr'
import { cookies } from 'next/headers'
import { revalidatePath } from 'next/cache'

export type ContactType = 'volontario' | 'sostenitore' | 'donatore';

export type Contact = {
    id: string;
    first_name: string;
    last_name: string;
    email: string | null;
    phone: string | null;
    type: ContactType;
    tags: string[];
    notes: string | null;
    created_at: string;
    updated_at: string;
}

function createClient() {
    const cookieStore = cookies()
    return createServerClient(
        process.env.NEXT_PUBLIC_SUPABASE_URL!,
        process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY!,
        {
            cookies: {
                get(name: string) {
                    return cookieStore.get(name)?.value
                },
                set(name: string, value: string, options: CookieOptions) {
                    cookieStore.set({ name, value, ...options })
                },
                remove(name: string, options: CookieOptions) {
                    cookieStore.set({ name, value: '', ...options })
                },
            },
        }
    )
}

export async function getContacts(query?: string, type?: string) {
    const supabase = createClient()

    let builder = supabase
        .from('contacts')
        .select('*')
        .order('created_at', { ascending: false })

    if (type && type !== 'all') {
        builder = builder.eq('type', type)
    }

    if (query) {
        builder = builder.or(`first_name.ilike.%${query}%,last_name.ilike.%${query}%,email.ilike.%${query}%`)
    }

    const { data, error } = await builder

    if (error) {
        console.error('Error fetching contacts:', error)
        return []
    }

    return data as Contact[]
}

export async function createContact(data: Omit<Contact, 'id' | 'created_at' | 'updated_at'>) {
    const supabase = createClient()
    const { error } = await supabase.from('contacts').insert(data)

    if (error) {
        return { error: error.message }
    }

    revalidatePath('/contacts')
    return { success: true }
}

export async function updateContact(id: string, data: Partial<Omit<Contact, 'id' | 'created_at' | 'updated_at'>>) {
    const supabase = createClient()
    const { error } = await supabase.from('contacts').update({ ...data, updated_at: new Date().toISOString() }).eq('id', id)

    if (error) {
        return { error: error.message }
    }

    revalidatePath('/contacts')
    return { success: true }
}

export async function deleteContact(id: string) {
    const supabase = createClient()
    const { error } = await supabase.from('contacts').delete().eq('id', id)

    if (error) {
        return { error: error.message }
    }

    revalidatePath('/contacts')
    return { success: true }
}
