<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import { useChatStore } from '@/stores/chat'
import { useAuthStore } from '@/stores/auth'
import MarkdownIt from 'markdown-it'
import { 
  Send, Bot, User, Loader2, Trash2, HeartPulse, Paperclip, Sparkles, MessageSquare 
} from 'lucide-vue-next'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Card from '@/components/ui/Card.vue'

const md = new MarkdownIt()
const chatStore = useChatStore()
const authStore = useAuthStore()

const input = ref('')
const attachedFile = ref(null)
const showQuickActions = ref(false)
const bottomRef = ref(null)
const fileInputRef = ref(null)

const quickActions = [
  { label: "📧 Bozza Newsletter", prompt: "Scrivi una bozza per la newsletter del mese su: " },
  { label: "💰 Idee Fundraising", prompt: "Dammi 3 idee originali per una campagna di raccolta fondi riguardante: " },
  { label: "🆘 Cerca Volontari", prompt: "Controlla se ci sono volontari disponibili per: " },
]

onMounted(async () => {
  // Wait for auth to be ready
  if (authStore.loading) {
    const unwatch = watch(() => authStore.loading, (newVal) => {
      if (!newVal && authStore.user) {
        initChat()
        unwatch()
      }
    })
  } else if (authStore.user) {
    initChat()
  }
})

function initChat() {
    chatStore.setUserId(authStore.user.id)
    chatStore.loadHistory()
    scrollToBottom()
}

watch(() => chatStore.messages.length, () => {
  scrollToBottom()
})

function scrollToBottom() {
  nextTick(() => {
    bottomRef.value?.scrollIntoView({ behavior: 'smooth' })
  })
}

function handleFileSelect(event) {
  const file = event.target.files[0]
  if (file) {
    if (file.type !== "text/plain") {
      alert("Per ora puoi caricare solo file di testo (.txt)")
      return
    }
    const reader = new FileReader()
    reader.onload = (e) => {
      attachedFile.value = { name: file.name, content: e.target.result }
    }
    reader.readAsText(file)
  }
}

function handleQuickAction(prompt) {
  input.value = prompt
  showQuickActions.value = false
}

async function handleClearChat() {
  if (confirm("Sei sicuro di voler cancellare tutta la cronologia?")) {
    await chatStore.clearHistory()
  }
}

async function handleSubmit() {
  if ((!input.value.trim() && !attachedFile.value) || chatStore.isLoading) return

  let finalInput = input.value
  if (attachedFile.value) {
    finalInput = `[CONTESTO DAL FILE: ${attachedFile.value.name}]\n${attachedFile.value.content} \n\n[MESSAGGIO UTENTE]: \n${input.value} `
  }
  
  const userMessage = { role: 'user', content: input.value || `(File inviato: ${attachedFile.value.name})` }
  chatStore.addMessage(userMessage)
  
  input.value = ''
  attachedFile.value = null
  if (fileInputRef.value) fileInputRef.value.value = ''
  
  chatStore.isLoading = true
  
  try {
    const API_URL = import.meta.env.VITE_API_URL || ''
    const response = await fetch(`${API_URL}/api/erika/chat`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        message: finalInput,
        history: chatStore.messages.slice(0, -1),
        userContext: {
            id: authStore.user.id,
            name: authStore.user.user_metadata?.full_name || authStore.user.email,
            role: authStore.user.role,
            group: authStore.user.group?.name,
            location: authStore.user.location?.name
        }
      })
    })

    if (!response.ok) {
        const errBody = await response.json().catch(() => ({}));
        console.error("Chat API Error Details:", errBody);
        throw new Error(errBody.details || errBody.error || 'Network response not ok');
    }
    
    const data = await response.json()
    // Map backend response { text: ... } to store
    const aiResponse = data.text || data.response // handle both potential formats just in case

    chatStore.addMessage({ role: 'model', content: aiResponse })
    
    
    // Save interaction to history - handled by backend now
    
  } catch (error) {
    console.error(error)
    chatStore.addMessage({ role: 'model', content: "Mi dispiace, si è verificato un errore. Riprova più tardi." })
  } finally {
    chatStore.isLoading = false
  }
}
</script>

<template>
  <div class="h-full flex flex-col gap-4 max-w-4xl mx-auto">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <h1 class="text-3xl font-bold tracking-tight text-red-600">Erika</h1>
        <span class="text-muted-foreground">- Assistente Operativo</span>
      </div>
    </div>

    <Card class="flex-1 flex flex-col shadow-xl border-t-4 border-t-red-600 overflow-hidden min-h-[500px]">
      <!-- Header -->
      <div class="border-b p-4 bg-slate-50 flex justify-between items-center">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-red-100 rounded-full">
            <MessageSquare class="w-6 h-6 text-red-600" />
          </div>
          <div>
            <h3 class="font-semibold text-lg">Parla con Erika</h3>
            <p class="text-xs text-muted-foreground">Assistente Operativa CRI Venezia</p>
          </div>
        </div>
        <Button v-if="chatStore.userId && chatStore.messages.length > 0" variant="ghost" size="icon" @click="handleClearChat" title="Cancella cronologia">
          <Trash2 class="w-4 h-4" />
        </Button>
      </div>

      <!-- Messages Area -->
      <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-white">
        <div v-if="chatStore.messages.length === 0" class="text-center text-muted-foreground py-10 space-y-4">
          <p class="text-lg font-medium text-gray-900">Ciao! Sono Erika.</p>
          <p class="text-sm">Come posso aiutarti oggi?</p>
        </div>

        <div v-for="(msg, idx) in chatStore.messages" :key="idx" class="flex w-full" :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
          <div class="flex max-w-[80%] gap-2 p-3 rounded-lg text-sm" 
            :class="msg.role === 'user' ? 'bg-red-600 text-white rounded-br-none' : 'bg-slate-100 text-slate-800 rounded-bl-none'">
            
            <Bot v-if="msg.role === 'model'" class="w-4 h-4 mt-0.5 shrink-0" />
            
            <div class="prose prose-sm max-w-none break-words" 
              :class="msg.role === 'user' ? 'prose-invert prose-p:text-white prose-headings:text-white' : ''"
              v-html="md.render(msg.content)">
            </div>
            
            <User v-if="msg.role === 'user'" class="w-4 h-4 mt-0.5 shrink-0" />
          </div>
        </div>

        <div v-if="chatStore.isLoading" class="flex w-full justify-start">
          <div class="flex max-w-[80%] gap-2 p-3 rounded-lg bg-slate-100 rounded-bl-none">
            <HeartPulse class="w-4 h-4 mt-0.5 animate-pulse" />
            <div class="flex items-center gap-1">
              <span class="text-xs text-muted-foreground">Sto scrivendo...</span>
            </div>
          </div>
        </div>
        
        <div ref="bottomRef"></div>
      </div>

      <!-- Footer / Input -->
      <div class="p-4 border-t bg-white flex flex-col gap-3">
        <!-- Attached File Preview -->
        <div v-if="attachedFile" class="px-4 py-2 bg-gray-50 flex items-center gap-2 rounded border">
          <span class="truncate max-w-[200px] font-medium text-xs">{{ attachedFile.name }}</span>
          <button @click="attachedFile = null" class="text-gray-400 hover:text-red-500">
            <Trash2 class="w-3 h-3" />
          </button>
        </div>

        <!-- Quick Actions -->
        <div v-if="showQuickActions" class="flex gap-2 overflow-x-auto pb-1">
           <Button v-for="(action, idx) in quickActions" :key="idx" variant="outline" size="sm" 
            class="whitespace-nowrap text-xs rounded-full border-red-200 text-red-600 hover:bg-red-50"
            @click="handleQuickAction(action.prompt)">
             {{ action.label }}
           </Button>
        </div>

        <form @submit.prevent="handleSubmit" class="flex gap-2">
          <input type="file" ref="fileInputRef" class="hidden" accept=".txt" @change="handleFileSelect">
          
          <Button type="button" variant="ghost" size="icon" @click="fileInputRef.click()" title="Allega file">
            <Paperclip class="w-5 h-5" />
          </Button>
          
          <Button type="button" :variant="showQuickActions ? 'secondary' : 'ghost'" size="icon" @click="showQuickActions = !showQuickActions" title="Suggerimenti">
            <Sparkles class="w-5 h-5" :class="showQuickActions ? 'text-red-600' : ''" />
          </Button>

          <Input 
            v-model="input" 
            :placeholder="attachedFile ? 'Scrivi cosa fare con il file...' : 'Scrivi un messaggio a Erika...'" 
            :disabled="chatStore.isLoading"
            class="flex-1"
          />
          
          <Button type="submit" size="icon" :disabled="chatStore.isLoading || (!input.trim() && !attachedFile)">
            <Send class="w-4 h-4" />
          </Button>
        </form>
      </div>

    </Card>
  </div>
</template>
