<script setup>
import { useAuthStore } from '@/stores/auth'
import { MessageSquare, Users, PiggyBank, Mail, ArrowRight } from 'lucide-vue-next'
import Card from '@/components/ui/Card.vue'

const auth = useAuthStore()

const modules = [
  { 
    title: 'Erika', 
    desc: 'L\'assistente virtuale per la tua operatività.', 
    icon: MessageSquare, 
    path: '/chat',
    color: 'text-red-600',
    bg: 'bg-red-100'
  },
  { 
    title: 'Anagrafica', 
    desc: 'Gestione completa dei volontari e contatti.', 
    icon: Users, 
    path: '/contacts',
    color: 'text-blue-600',
    bg: 'bg-blue-100'
  },
  { 
    title: 'Fundraising', 
    desc: 'Genera campagne di raccolta fondi in pochi istanti.', 
    icon: PiggyBank, 
    path: '/fundraising',
    color: 'text-purple-600',
    bg: 'bg-purple-100'
  },
  { 
    title: 'Newsletter', 
    desc: 'Invia comunicazioni a tutti i soci.', 
    icon: Mail, 
    path: '/newsletter',
    color: 'text-red-600',
    bg: 'bg-red-100'
  }
]
</script>

<template>
  <div class="max-w-5xl mx-auto space-y-8">
    <div>
      <h1 class="text-3xl font-bold tracking-tight text-gray-900">Dashboard</h1>
      <p class="text-muted-foreground">Benvenuto in CRI Venezia CRM.</p>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-2 gap-6">
      <router-link 
        v-for="mod in modules" 
        :key="mod.path" 
        :to="mod.path"
        class="block group"
      >
        <Card class="h-full p-6 transition-all hover:shadow-lg border-l-4" :class="`border-l-${mod.color.split('-')[1]}-500`">
          <div class="flex items-start justify-between">
            <div class="flex gap-4">
              <div class="p-3 rounded-xl transition-colors group-hover:bg-opacity-80" :class="mod.bg">
                <component :is="mod.icon" class="w-8 h-8" :class="mod.color" />
              </div>
              <div class="space-y-1">
                <h3 class="font-semibold text-xl text-gray-900 group-hover:text-primary transition-colors">
                  {{ mod.title }}
                </h3>
                <p class="text-sm text-muted-foreground leading-relaxed">
                  {{ mod.desc }}
                </p>
              </div>
            </div>
            <ArrowRight class="w-5 h-5 text-gray-300 group-hover:text-primary transition-colors" />
          </div>
        </Card>
      </router-link>
    </div>
  </div>
</template>
