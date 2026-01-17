<script setup>
import { ref } from 'vue'
import { 
  LayoutDashboard, 
  MessageSquare, 
  Users, 
  PiggyBank, 
  Mail,
  LogOut,
  Menu,
  X,
  ShieldAlert,
  User
} from 'lucide-vue-next'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { cn } from '@/lib/utils'
import Button from '@/components/ui/Button.vue'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()
const isOpen = ref(false)

async function handleLogout() {
  await auth.logout()
  router.push('/login')
}

const links = [
  { name: 'Dashboard', path: '/', icon: LayoutDashboard },
  { name: 'Erika', path: '/chat', icon: MessageSquare }, // Shortened for Navbar
  { name: 'Anagrafica', path: '/contacts', icon: Users },
  { name: 'Fundraising', path: '/fundraising', icon: PiggyBank },
  { name: 'Newsletter', path: '/newsletter', icon: Mail },
]
</script>

<template>
  <nav class="w-full bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16">
        <!-- Logo & Brand -->
        <div class="flex items-center">
            <img src="/logo-cri.png" alt="CRI Venezia" class="h-10 w-auto" />
        </div>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center space-x-1">
          <router-link 
            v-for="link in links" 
            :key="link.path" 
            :to="link.path"
            :class="cn(
              'flex items-center gap-2 px-3 py-2 rounded-md transition-colors text-sm font-medium',
              route.path === link.path 
                ? 'bg-red-50 text-red-600' 
                : 'text-gray-500 hover:text-red-600 hover:bg-gray-50'
            )"
          >
            <component :is="link.icon" class="w-4 h-4" />
            {{ link.name }}
          </router-link>

          <!-- Admin Link -->
          <router-link 
            v-if="auth.user?.role === 'admin'"
            to="/admin"
            :class="cn(
              'flex items-center gap-2 px-3 py-2 rounded-md transition-colors text-sm font-medium',
              route.path === '/admin' 
                ? 'bg-red-50 text-red-600' 
                : 'text-gray-500 hover:text-red-600 hover:bg-gray-50'
            )"
          >
            <ShieldAlert class="w-4 h-4" />
            Admin
          </router-link>
        </div>

        <!-- User / Logout (Desktop) -->
        <div class="hidden md:flex items-center gap-2">
          <router-link to="/profile">
              <Button variant="ghost" size="sm" class="text-gray-500 hover:text-[#CC0000] gap-2">
                <User class="w-4 h-4" />
                <span>Profilo</span>
              </Button>
          </router-link>

          <Button 
            variant="ghost" 
            size="sm"
            @click="handleLogout"
            class="text-gray-500 hover:text-red-600 gap-2"
          >
            <LogOut class="w-4 h-4" />
            <span>Esci</span>
          </Button>
        </div>

        <!-- Mobile Menu Button -->
        <div class="flex items-center md:hidden">
          <button @click="isOpen = !isOpen" class="text-gray-500 hover:text-red-600 focus:outline-none">
            <Menu v-if="!isOpen" class="w-6 h-6" />
            <X v-else class="w-6 h-6" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="isOpen" class="md:hidden border-t border-gray-100">
      <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-white shadow-lg">
        <router-link 
          v-for="link in links" 
          :key="link.path" 
          :to="link.path"
          @click="isOpen = false"
          :class="cn(
            'flex items-center gap-3 px-3 py-3 rounded-md text-base font-medium transition-colors',
            route.path === link.path 
              ? 'bg-red-50 text-red-600' 
              : 'text-gray-600 hover:bg-gray-50 hover:text-red-600'
          )"
        >
          <component :is="link.icon" class="w-5 h-5" />
          {{ link.name }}
        </router-link>

        <router-link 
          v-if="auth.user?.role === 'admin'"
          to="/admin"
          @click="isOpen = false"
          :class="cn(
            'flex items-center gap-3 px-3 py-3 rounded-md text-base font-medium transition-colors',
            route.path === '/admin' 
              ? 'bg-red-50 text-red-600' 
              : 'text-gray-600 hover:bg-gray-50 hover:text-red-600'
          )"
        >
          <ShieldAlert class="w-5 h-5" />
          Admin Panel
        </router-link>
        
        <div class="border-t border-gray-100 my-2 pt-2 space-y-1">
            <router-link 
              to="/profile"
              @click="isOpen = false"
              class="flex w-full items-center gap-3 px-3 py-3 rounded-md text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-[#CC0000] transition-colors"
            >
              <User class="w-5 h-5" />
              Profilo
            </router-link>

            <button 
                @click="handleLogout"
                class="flex w-full items-center gap-3 px-3 py-3 rounded-md text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-red-600 transition-colors"
            >
                <LogOut class="w-5 h-5" />
                Esci
            </button>
        </div>
      </div>
    </div>
  </nav>
</template>
