<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Label from '@/components/ui/Label.vue'

const router = useRouter()
const auth = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  
  const { error: err } = await auth.login(email.value, password.value)
  
  if (err) {
    error.value = err.message
    loading.value = false
  } else {
    router.push('/')
  }
}
</script>

<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-50 px-4">
    <Card class="w-full max-w-md">
      <div class="flex flex-col space-y-1.5 p-6">
        <h3 class="text-2xl font-bold text-center text-[#CC0000]">CRI Venezia</h3>
        <p class="text-sm text-muted-foreground text-center">Accedi al portale per utilizzare Erika</p>
      </div>
      
      <div class="p-6 pt-0 space-y-4">
        <form @submit.prevent="handleLogin" class="space-y-4">
          <div class="space-y-2">
            <Label for="email">Email</Label>
            <Input id="email" type="email" v-model="email" placeholder="m@example.com" required autocomplete="username" />
          </div>
          <div class="space-y-2">
            <Label for="password">Password</Label>
            <Input id="password" type="password" v-model="password" required autocomplete="current-password" />
          </div>

          <div v-if="error" class="text-red-500 text-sm font-medium">
            {{ error }}
          </div>
          
          <div class="flex flex-col gap-2 pt-4">
            <Button type="submit" class="w-full bg-[#CC0000] hover:bg-[#990000] text-white" :disabled="loading">
              {{ loading ? "Caricamento..." : "Accedi" }}
            </Button>
          </div>
          
          <div class="text-center text-sm mt-4">
            <router-link to="/signup" class="text-muted-foreground hover:text-red-600 underline">
              Non hai un account? Registrati
            </router-link>
          </div>
        </form>
      </div>
    </Card>
  </div>
</template>
