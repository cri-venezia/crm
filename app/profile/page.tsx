import { createServerClient, type CookieOptions } from '@supabase/ssr'
import { cookies } from 'next/headers'
import { redirect } from 'next/navigation'
import { ProfileForm } from "@/components/profile/profile-form"

export default async function ProfilePage() {
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

    if (!user) {
        redirect('/login')
    }

    return (
        <main className="container mx-auto p-8 pt-10 min-h-screen max-w-2xl">
            <div className="mb-8 space-y-2">
                <h1 className="text-3xl font-bold tracking-tight">Il Mio Profilo</h1>
                <p className="text-muted-foreground">
                    Gestisci le tue informazioni e le impostazioni di sicurezza.
                </p>
            </div>

            <ProfileForm user={user} />
        </main>
    )
}
