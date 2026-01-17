
import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from './stores/auth'

import MainLayout from './components/layout/MainLayout.vue'
import Home from './views/Home.vue'
import Login from './views/Login.vue'
import Signup from './views/Signup.vue'
import Chat from './views/Chat.vue'
import Contacts from './views/Contacts.vue'
import Fundraising from './views/Fundraising.vue'
import Newsletter from './views/Newsletter.vue'
import Admin from './views/Admin.vue'
import Profile from './views/Profile.vue'

const routes = [
    {
        path: '/',
        component: MainLayout,
        meta: { requiresAuth: true },
        children: [
            { path: '', component: Home },
            { path: 'chat', component: Chat },
            { path: 'contacts', component: Contacts },
            { path: 'fundraising', component: Fundraising },
            { path: 'newsletter', component: Newsletter },
            {
                path: 'admin',
                component: Admin,
                meta: { requiresAdmin: true }
            },
            { path: 'profile', component: Profile },
        ]
    },
    { path: '/login', component: Login },
    { path: '/signup', component: Signup },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

router.beforeEach(async (to, from, next) => {
    const auth = useAuthStore()

    // Ensure user load is attempted
    if (auth.loading) await auth.loadUser()

    if (to.meta.requiresAuth && !auth.user) {
        next('/login')
    } else if (to.meta.requiresAdmin && auth.user?.role !== 'admin') {
        alert("Accesso Negato: Richiesto ruolo Admin")
        next('/')
    } else if ((to.path === '/login' || to.path === '/signup') && auth.user) {
        next('/')
    } else {
        next()
    }
})

export default router
