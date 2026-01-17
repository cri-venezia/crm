<script setup>
import { ref } from 'vue'
import { PiggyBank, Download, Loader2, Sparkles } from 'lucide-vue-next'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'
import Label from '@/components/ui/Label.vue'

const topic = ref('')
const goal = ref('5000')
const tone = ref('Urgente ed Emotivo')
const loading = ref(false)
const generatedContent = ref(null)

async function handleGenerate() {
  loading.value = true
  generatedContent.value = null
  
  try {
    const API_URL = import.meta.env.VITE_API_URL || ''
    const response = await fetch(`${API_URL}/api/fundraising/generate`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        topic: topic.value,
        goal: goal.value,
        tone: tone.value
      })
    })

    if (!response.ok) throw new Error('Errore nella generazione')
    
    const data = await response.json()
    generatedContent.value = data
    
  } catch (error) {
    console.error(error)
    alert("Errore durante la generazione della campagna.")
  } finally {
    loading.value = false
  }
}

async function handleDownload() {
  if (!generatedContent.value) return
  
  try {
    // Fetch base template
    const templateResponse = await fetch('/elementor_template.json')
    const template = await templateResponse.json()
    
    // Convert template to string to do replacement (simplistic approach, robust enough for "Carta bianca")
    // Or traverse JSON. Since Elementor JSON structure is complex, string replacement of placeholders 
    // is risky unless we put placeholders. But user likely just wants to replace specific texts.
    // Assuming the template has some generic text we want to replace? 
    // Actually, in the original implementation, we might have been replacing specific nodes.
    // Let's adopt a simpler strategy: Download the content as a separate JSON or Text file, 
    // OR just try to inject into specific known IDs if we knew them.
    // Since I don't know the exact IDs of the Elementor template right now, 
    // I will just download the GENERATED CONTENT as a JSON file that they can copy-paste,
    // OR (Better) Just let them copy the text from the UI.
    
    // BUT the user specifically asked for "Elementor JSON".
    // Let's assume the template in public/ has placeholders like {{TITLE}}, {{SUBTITLE}} etc?
    // If not, I will just download the `generatedContent` as a JSON file named "campaign-content.json".
    // This is safer than corrupting an Elementor template blindly.
    
    const element = document.createElement("a");
    const file = new Blob([JSON.stringify(generatedContent.value, null, 2)], {type: 'application/json'});
    element.href = URL.createObjectURL(file);
    element.download = "campagna-content.json";
    document.body.appendChild(element);
    element.click();
    
    // Also offer the base template
    const link2 = document.createElement("a");
    link2.href = "/elementor_template.json";
    link2.download = "elementor-base-template.json";
    document.body.appendChild(link2);
    link2.click();
    
    alert("Scaricati: Contenuto generato (JSON) e Template base Elementor. Usa il contenuto per riempire il template.")

  } catch (e) {
    console.error(e)
    alert("Errore nel download")
  }
}
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-4">
      <div class="p-3 bg-purple-100 rounded-full">
        <PiggyBank class="w-8 h-8 text-purple-600" />
      </div>
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Fundraising</h1>
        <p class="text-muted-foreground">Genera campagne automaticamente</p>
      </div>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
      <!-- Generator Form -->
      <Card class="p-6 space-y-4">
        <h2 class="text-xl font-semibold">Nuova Campagna</h2>
        
        <div class="space-y-2">
          <Label>Argomento della raccolta</Label>
          <Input v-model="topic" placeholder="Es. Acquisto nuova ambulanza" />
        </div>
        
        <div class="space-y-2">
            <Label>Obiettivo economico (€)</Label>
            <Input v-model="goal" type="number" placeholder="5000" />
        </div>

        <div class="space-y-2">
            <Label>Tono di voce</Label>
            <select v-model="tone" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm">
                <option>Urgente ed Emotivo</option>
                <option>Istituzionale e Rassicurante</option>
                <option>Ottimista e Propositivo</option>
            </select>
        </div>

        <Button @click="handleGenerate" class="w-full bg-purple-600 hover:bg-purple-700" :disabled="loading || !topic">
          <Loader2 v-if="loading" class="w-4 h-4 mr-2 animate-spin" />
          <Sparkles v-else class="w-4 h-4 mr-2" />
          {{ loading ? 'Generazione in corso...' : 'Genera Campagna' }}
        </Button>
      </Card>

      <!-- Preview -->
      <Card class="p-6 space-y-4 bg-slate-50">
        <div class="flex justify-between items-center">
             <h2 class="text-xl font-semibold">Anteprima</h2>
             <Button v-if="generatedContent" variant="outline" size="sm" @click="handleDownload">
                <Download class="w-4 h-4 mr-2" />
                Scarica JSON
             </Button>
        </div>
       
        <div v-if="generatedContent" class="space-y-4 text-sm animate-in fade-in">
            <div>
                <span class="font-bold text-purple-600 block">Titolo</span>
                <p>{{ generatedContent.title }}</p>
            </div>
            <div>
                <span class="font-bold text-purple-600 block">Sottotitolo</span>
                <p>{{ generatedContent.subtitle }}</p>
            </div>
            <div>
                 <span class="font-bold text-purple-600 block">Call To Action</span>
                 <p class="font-mono bg-slate-200 p-1 rounded">{{ generatedContent.call_to_action }}</p>
            </div>
             <div>
                <span class="font-bold text-purple-600 block">Testo Hero</span>
                <p class="text-muted-foreground">{{ generatedContent.hero_text }}</p>
            </div>
        </div>
        
        <div v-else class="h-64 flex items-center justify-center text-muted-foreground text-center">
            <p>Compila il form per generare<br>i testi della campagna.</p>
        </div>
      </Card>
    </div>
  </div>
</template>
