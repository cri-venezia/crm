<script setup>
import { ref, onMounted } from 'vue'
import { supabase } from '@/lib/supabase'
import { Users, Plus, Search, Trash2, Edit } from 'lucide-vue-next'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import Input from '@/components/ui/Input.vue'

const contacts = ref([])
const loading = ref(true)
const searchQuery = ref('')
const showForm = ref(false)
const editingContact = ref(null)

// Form fields
const form = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  tags: ''
})

onMounted(() => {
  fetchContacts()
})

async function fetchContacts() {
  loading.value = true
  const { data, error } = await supabase
    .from('contacts')
    .select('*')
    .order('created_at', { ascending: false })
  
  if (error) console.error(error)
  else contacts.value = data || []
  loading.value = false
}

async function handleDelete(id) {
  if (!confirm("Eliminare questo contatto?")) return
  
  const { error } = await supabase.from('contacts').delete().eq('id', id)
  if (error) alert("Errore eliminazione")
  else fetchContacts()
}

function openAdd() {
  editingContact.value = null
  form.value = { first_name: '', last_name: '', email: '', phone: '', tags: '' }
  showForm.value = true
}

function openEdit(contact) {
  editingContact.value = contact
  form.value = { ...contact }
  if (Array.isArray(form.value.tags)) form.value.tags = form.value.tags.join(', ')
  showForm.value = true
}

async function handleSubmit() {
  const contactData = {
    first_name: form.value.first_name,
    last_name: form.value.last_name,
    email: form.value.email,
    phone: form.value.phone,
    tags: form.value.tags.split(',').map(t => t.trim()).filter(Boolean)
  }

  if (editingContact.value) {
    const { error } = await supabase
      .from('contacts')
      .update(contactData)
      .eq('id', editingContact.value.id)
      
    if (error) alert("Errore aggiornamento: " + error.message)
    else {
      showForm.value = false
      fetchContacts()
    }
  } else {
    const { error } = await supabase.from('contacts').insert([contactData])
    if (error) alert("Errore creazione: " + error.message)
    else {
      showForm.value = false
      fetchContacts()
    }
  }
}
</script>

<template>
  <div class="max-w-6xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <div class="p-3 bg-blue-100 rounded-full">
          <Users class="w-8 h-8 text-blue-600" />
        </div>
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Anagrafica</h1>
          <p class="text-muted-foreground">Gestione volontari e contatti</p>
        </div>
      </div>
      <Button @click="openAdd">
        <Plus class="w-4 h-4 mr-2" />
        Nuovo Contatto
      </Button>
    </div>

    <!-- Search / Toolbar -->
    <div class="flex items-center gap-4">
      <div class="relative flex-1 max-w-sm">
        <Search class="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
        <Input v-model="searchQuery" placeholder="Cerca nome, email..." class="pl-8" />
      </div>
    </div>

    <!-- List -->
    <Card class="overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="px-4 py-3 text-left font-medium">Nome</th>
              <th class="px-4 py-3 text-left font-medium">Email</th>
              <th class="px-4 py-3 text-left font-medium">Telefono</th>
              <th class="px-4 py-3 text-left font-medium">Tags</th>
              <th class="px-4 py-3 text-right font-medium">Azioni</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <tr v-if="loading">
              <td colspan="5" class="p-8 text-center text-muted-foreground">Caricamento...</td>
            </tr>
            <tr v-else-if="contacts.length === 0">
              <td colspan="5" class="p-8 text-center text-muted-foreground">Nessun contatto trovato.</td>
            </tr>
            <tr v-for="contact in contacts" :key="contact.id" class="hover:bg-gray-50/50">
              <td class="px-4 py-3 font-medium">{{ contact.first_name }} {{ contact.last_name }}</td>
              <td class="px-4 py-3">{{ contact.email }}</td>
              <td class="px-4 py-3">{{ contact.phone }}</td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap gap-1">
                  <span v-for="tag in contact.tags" :key="tag" class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-800 text-xs">
                    {{ tag }}
                  </span>
                </div>
              </td>
              <td class="px-4 py-3 text-right flex justify-end gap-2">
                <Button variant="ghost" size="icon" @click="openEdit(contact)">
                   <Edit class="w-4 h-4 text-gray-500" />
                </Button>
                <Button variant="ghost" size="icon" @click="handleDelete(contact.id)">
                   <Trash2 class="w-4 h-4 text-red-500" />
                </Button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </Card>

    <!-- Modal (Simple overlay for speed) -->
    <div v-if="showForm" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <Card class="w-full max-w-lg p-6 space-y-4 bg-background">
        <h2 class="text-xl font-bold">{{ editingContact ? 'Modifica Contatto' : 'Nuovo Contatto' }}</h2>
        
        <form @submit.prevent="handleSubmit" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <label class="text-sm font-medium">Nome</label>
              <Input v-model="form.first_name" required />
            </div>
            <div class="space-y-2">
              <label class="text-sm font-medium">Cognome</label>
              <Input v-model="form.last_name" required />
            </div>
          </div>
          
          <div class="space-y-2">
              <label class="text-sm font-medium">Email</label>
              <Input v-model="form.email" type="email" />
          </div>
          
          <div class="space-y-2">
              <label class="text-sm font-medium">Telefono</label>
              <Input v-model="form.phone" />
          </div>

          <div class="space-y-2">
              <label class="text-sm font-medium">Tags (separati da virgola)</label>
              <Input v-model="form.tags" placeholder="volontario, autista, ..." />
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <Button type="button" variant="ghost" @click="showForm = false">Annulla</Button>
            <Button type="submit">{{ editingContact ? 'Salva Modifiche' : 'Crea Contatto' }}</Button>
          </div>
        </form>
      </Card>
    </div>

  </div>
</template>
