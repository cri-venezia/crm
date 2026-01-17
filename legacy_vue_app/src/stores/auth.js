
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { supabase } from '../lib/supabase'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const session = ref(null)
    const loading = ref(true)

    async function loadUser() {
        loading.value = true
        const { data } = await supabase.auth.getSession()
        if (data.session) {
            session.value = data.session
            const { data: profile } = await supabase
                .from('profiles')
                .select('role, user_groups(name, accessible_modules), locations(name)')
                .eq('id', data.session.user.id)
                .single()

            user.value = {
                ...data.session.user,
                role: profile?.role || 'volontario',
                group: profile?.user_groups || null,
                location: profile?.locations || null,
                modules: profile?.user_groups?.accessible_modules || []
            }
        }

        supabase.auth.onAuthStateChange(async (_event, _session) => {
            session.value = _session
            if (_session) {
                const { data: profile } = await supabase
                    .from('profiles')
                    .select('role, user_groups(name, accessible_modules), locations(name)')
                    .eq('id', _session.user.id)
                    .single()

                user.value = {
                    ..._session.user,
                    role: profile?.role || 'volontario',
                    group: profile?.user_groups || null,
                    location: profile?.locations || null,
                    modules: profile?.user_groups?.accessible_modules || []
                }
            } else {
                user.value = null
            }
            loading.value = false
        })
        loading.value = false
    }

    function canAccess(moduleName) {
        if (!user.value) return false
        if (user.value.role === 'admin') return true // Super admin
        if (!user.value.modules) return false
        return user.value.modules.includes(moduleName)
    }

    async function login(email, password) {
        return await supabase.auth.signInWithPassword({ email, password })
    }

    async function signup(data) {
        return await supabase.auth.signUp(data)
    }

    async function logout() {
        try {
            await supabase.auth.signOut()
        } catch (error) {
            console.error("Logout error (server)", error)
        } finally {
            user.value = null
            session.value = null
        }
    }

    return { user, session, loading, loadUser, login, signup, logout, canAccess }
})
