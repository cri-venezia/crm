<script setup>
import { ref, onMounted } from 'vue'
import { Mail, Send, FileText, Plus, Trash, Image as ImageIcon } from 'lucide-vue-next'
import { supabase } from '@/lib/supabase'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Label from '@/components/ui/Label.vue'

const subject = ref('') 
const testEmail = ref('')
const loading = ref(false)
const templates = ref([])
const selectedTemplate = ref(null)

// Multi-article state
const articles = ref([
    { title: '', link: '', body: '', imageUrl: '', uploading: false }
])

onMounted(async () => {
    const { data, error } = await supabase.from('newsletter_templates').select('*')
    if (data) templates.value = data
    if (data && data.length > 0) selectedTemplate.value = data[0].brevo_id
})

function addArticle() {
    articles.value.push({ title: '', link: '', body: '', imageUrl: '', uploading: false })
}

function removeArticle(index) {
    articles.value.splice(index, 1)
}

async function handleImageUpload(event, index) {
    const file = event.target.files[0]
    if (!file) return

    articles.value[index].uploading = true
    const fileName = `${Date.now()}-${file.name}`
    
    // Upload to 'newsletter-images' bucket
    const { data, error } = await supabase.storage
        .from('newsletter-images')
        .upload(fileName, file)

    articles.value[index].uploading = false

    if (error) {
        alert('Errore caricamento immagine: ' + error.message)
        return
    }

    // Get public URL
    const { data: { publicUrl } } = supabase.storage
        .from('newsletter-images')
        .getPublicUrl(fileName)
    
    articles.value[index].imageUrl = publicUrl
}

async function handleSend(isTest = false) {
  if (!subject.value) return alert("Compila l'oggetto")
  if (!selectedTemplate.value) return alert("Seleziona un template")
  if (isTest && !testEmail.value) return alert("Inserisci email di test")
  if (articles.value.length === 0) return alert("Aggiungi almeno un articolo")

  loading.value = true
  
  try {
    const API_URL = import.meta.env.VITE_API_URL || ''
    const response = await fetch(`${API_URL}/api/newsletter/send`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        title: subject.value,
        articles: articles.value, // Send array of articles
        templateId: selectedTemplate.value,
        testEmail: isTest ? testEmail.value : null
      })
    })

    const data = await response.json()
    if (!response.ok) throw new Error(data.error || 'Errore invio')
    
    alert(`Inviato con successo ${isTest ? 'al test' : 'alla lista'}!`)
    if (!isTest) {
      subject.value = ''
      articles.value = [{ title: '', link: '', body: '', imageUrl: '', uploading: false }] // Reset
    }
  } catch (error) {
    alert("Errore: " + error.message)
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-5xl mx-auto space-y-6 pb-20">
    <div class="flex items-center gap-4">
      <div class="p-3 bg-red-100 rounded-full">
        <Mail class="w-8 h-8 text-[#CC0000]" />
      </div>
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Newsletter</h1>
        <p class="text-muted-foreground">Editor Multi-Articolo (4-6 news)</p>
      </div>
    </div>

    <Card class="p-6 space-y-6">
      <!-- Global Settings -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
            <Label>Seleziona Template</Label>
            <select v-model="selectedTemplate" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                <option v-for="t in templates" :key="t.id" :value="t.brevo_id">
                    {{ t.name }}
                </option>
            </select>
        </div>
        <div class="space-y-2">
            <Label>Oggetto Newsletter</Label>
            <Input v-model="subject" placeholder="Es. Aggiornamenti Febbraio" />
        </div>
      </div>

      <div class="border-t border-gray-200 my-4"></div>

      <!-- Articles List -->
      <div class="space-y-6">
        <div v-for="(article, index) in articles" :key="index" class="p-4 border rounded-lg bg-gray-50 relative group">
            <button @click="removeArticle(index)" class="absolute top-2 right-2 p-1 text-gray-400 hover:text-[#CC0000] transition-colors" title="Rimuovi Articolo">
                <Trash class="w-5 h-5" />
            </button>
            
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                <!-- Image Upload (Left) -->
                <div class="md:col-span-3">
                    <Label class="mb-2 block">Immagine</Label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg h-32 flex flex-col items-center justify-center bg-white hover:bg-gray-50 transition-colors cursor-pointer relative overflow-hidden">
                        <input type="file" accept="image/*" @change="(e) => handleImageUpload(e, index)" class="absolute inset-0 opacity-0 cursor-pointer" />
                        
                        <img v-if="article.imageUrl" :src="article.imageUrl" class="absolute inset-0 w-full h-full object-cover" />
                        
                        <div v-else class="text-center p-2">
                            <ImageIcon v-if="!article.uploading" class="w-8 h-8 text-gray-400 mx-auto mb-1" />
                            <span v-if="article.uploading" class="text-xs text-indigo-600 font-medium">Caricamento...</span>
                            <span v-else class="text-xs text-gray-500">Clicca per caricare</span>
                        </div>
                    </div>
                </div>

                <!-- Content (Right) -->
                <div class="md:col-span-9 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <Label>Titolo Articolo</Label>
                            <Input v-model="article.title" placeholder="Es. Nuovo corso B." />
                        </div>
                        <div class="space-y-2">
                            <Label>Link (News Sito)</Label>
                            <Input v-model="article.link" placeholder="https://..." />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <Label>Breve Testo</Label>
                        <textarea 
                            v-model="article.body" 
                            class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            placeholder="Descrizione breve..."
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>

        <Button variant="outline" @click="addArticle" class="w-full border-dashed">
            <Plus class="w-4 h-4 mr-2" /> Aggiungi Articolo
        </Button>
      </div>

      <!-- Footer Actions -->
      <div class="pt-4 border-t flex flex-col md:flex-row gap-4 justify-between items-end sticky bottom-0 bg-white p-4 shadow-top z-10">
        <div class="flex items-end gap-2 w-full md:w-auto">
           <div class="space-y-2 flex-1">
             <Label>Email di Test</Label>
             <Input v-model="testEmail" placeholder="tua@email.com" />
           </div>
           <Button variant="outline" @click="handleSend(true)" :disabled="loading">Test Invio</Button>
        </div>
        
        <Button @click="handleSend(false)" class="bg-[#CC0000] hover:bg-[#990000] text-white w-full md:w-auto" :disabled="loading">
          <Send class="w-4 h-4 mr-2" />
          {{ loading ? 'Invio...' : 'Invia a TUTTI' }}
        </Button>
      </div>
    </Card>
  </div>
</template>
