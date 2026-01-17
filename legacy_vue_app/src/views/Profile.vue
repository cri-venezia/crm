<script setup>
import { ref } from 'vue'
import { User, Lock } from 'lucide-vue-next'
import { supabase } from '@/lib/supabase'
import { useAuthStore } from '@/stores/auth'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Label from '@/components/ui/Label.vue'

const auth = useAuthStore()
const password = ref('')
const confirmPassword = ref('')
const loading = ref(false)

async function handleUpdatePassword() {
    if (password.value !== confirmPassword.value) {
        return alert("Le password non coincidono")
    }
    if (password.value.length < 6) {
        return alert("La password deve essere di almeno 6 caratteri")
    }

    loading.value = true
    const { error } = await supabase.auth.updateUser({ password: password.value })

    if (error) {
        alert("Errore aggiornamento: " + error.message)
    } else {
        alert("Password aggiornata con successo!")
        password.value = ''
        confirmPassword.value = ''
    }
    loading.value = false
}
</script>

<template>
  <div class="max-w-xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
      <div class="p-3 bg-red-100 rounded-full">
        <User class="w-8 h-8 text-[#CC0000]" />
      </div>
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Profilo Utente</h1>
        <p class="text-muted-foreground">{{ auth.user?.email }}</p>
      </div>
    </div>

    <Card class="p-6">
       <h2 class="text-xl font-semibold mb-4 flex items-center gap-2">
           <Lock class="w-5 h-5 text-gray-500" />
           Cambio Password
       </h2>
       
       <div class="space-y-4">
           <div class="space-y-2">
               <Label>Nuova Password</Label>
               <Input v-model="password" type="password" placeholder="••••••••" />
           </div>
           <div class="space-y-2">
               <Label>Conferma Password</Label>
               <Input v-model="confirmPassword" type="password" placeholder="••••••••" />
           </div>

           <Button @click="handleUpdatePassword" class="w-full bg-[#CC0000] hover:bg-[#990000] text-white" :disabled="loading">
               {{ loading ? 'Aggiornamento...' : 'Aggiorna Password' }}
           </Button>
       </div>
    </Card>
  </div>
</template>
