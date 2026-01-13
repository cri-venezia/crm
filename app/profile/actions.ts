'use server'

import { createServerClient, type CookieOptions } from '@supabase/ssr'
import { cookies } from 'next/headers'
import { revalidatePath } from 'next/cache'

export async function updateProfile(formData: FormData) {
    const firstName = formData.get('firstName') as string
    const lastName = formData.get('lastName') as string
    const fullName = `${firstName} ${lastName}`.trim()

    const cookieStore = cookies()

    const supabase = createServerClient(
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

    const { data: { user } } = await supabase.auth.getUser()
    if (!user) return { error: 'Non autenticato' }

    // 1. Update Public Profile
    const { error: profileError } = await supabase
        .from('profiles')
        .update({ full_name: fullName })
        .eq('id', user.id)

    if (profileError) return { error: `Errore profilo: ${profileError.message}` }

    // 2. Update Auth Metadata (Best effort)
    const { error: authError } = await supabase.auth.updateUser({
        data: {
            full_name: fullName,
            first_name: firstName,
            last_name: lastName
        }
    })

    if (authError) return { error: `Errore metadata: ${authError.message}` }

    revalidatePath('/', 'layout')
    return { message: 'Profilo aggiornato con successo' }
}

export async function updatePassword(formData: FormData) {
    const password = formData.get('password') as string
    const confirmPassword = formData.get('confirmPassword') as string

    if (!password || password.length < 6) {
        return { error: 'La password deve essere di almeno 6 caratteri' }
    }

    if (password !== confirmPassword) {
        return { error: 'Le password non coincidono' }
    }

    const cookieStore = cookies()
    const supabase = createServerClient(
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

    const { error } = await supabase.auth.updateUser({
        password: password
    })

    if (error) return { error: error.message }

    return { message: 'Password aggiornata. Esegui nuovamente il login.' }
}
