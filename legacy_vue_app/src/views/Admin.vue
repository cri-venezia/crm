<script setup>
import { ref, onMounted, computed } from 'vue'
import Card from '@/components/ui/Card.vue'
import Button from '@/components/ui/Button.vue'
import { Trash, Plus, Shield, User as UserIcon, Edit, MapPin, Users, Settings, Monitor, Heart, BookOpen } from 'lucide-vue-next'

const activeTab = ref('users') // 'users' | 'groups'

// --- USERS STATE ---
const users = ref([])
const groups = ref([]) // For dropdowns in User tab
const locations = ref([]) // For dropdowns in User tab
const loadingUsers = ref(false)
const showUserModal = ref(false)
const isEditingUser = ref(false)
const statusMessage = ref('In attesa di chiamata...')

const userFormData = ref({
    id: null,
    email: '',
    password: '',
    full_name: '',
    role: 'volontario',
    group_id: null,
    location_id: null
})

// --- GROUPS STATE ---
const groupsList = ref([]) // For the Groups tab list
const loadingGroups = ref(false)
const showGroupModal = ref(false)
const isEditingGroup = ref(false)

const groupFormData = ref({
    id: null,
    name: '',
    accessible_modules: [] // ['chat', 'contacts', 'fundraising', 'newsletter']
})

const availableModules = [
    { id: 'chat', label: 'Erika (Chat)', icon: Monitor },
    { id: 'contacts', label: 'Anagrafica', icon: Users },
    { id: 'fundraising', label: 'Fundraising', icon: Heart },
    { id: 'newsletter', label: 'Newsletter', icon: BookOpen }
]

const API_URL = import.meta.env.VITE_API_URL || ''

// --- INIT & FETCHING ---

async function fetchMetadata() {
    try {
        const res = await fetch(`${API_URL}/api/admin/metadata`)
        if (res.ok) {
            const data = await res.json()
            groups.value = data.groups || [] // Updates dropdowns
            locations.value = data.locations || []
        }
    } catch (e) {
        console.error("Error fetching metadata", e)
        statusMessage.value = `Errore Metadata: ${e.message} (URL: ${API_URL}/api/admin/metadata)`
    }
}

async function fetchUsers() {
    loadingUsers.value = true
    try {
        const res = await fetch(`${API_URL}/api/admin/users`)
        if (!res.ok) throw new Error('Errore caricamento utenti')
        users.value = await res.json()
    } catch (e) {
        alert(e.message) 
    } finally {
        loadingUsers.value = false
    }
}

async function fetchGroups() {
    loadingGroups.value = true
    try {
        const res = await fetch(`${API_URL}/api/admin/groups`)
        if (!res.ok) throw new Error('Errore caricamento gruppi')
        groupsList.value = await res.json() // Updates tab list
        groups.value = groupsList.value // Sync dropdowns too
    } catch (e) {
        alert(e.message)
    } finally {
        loadingGroups.value = false
    }
}

onMounted(() => {
    fetchMetadata()
    fetchUsers()
    fetchGroups()
})

// --- USER ACTIONS ---

function openCreateUser() {
    isEditingUser.value = false
    userFormData.value = {
        id: null,
        email: '',
        password: '',
        full_name: '',
        role: 'volontario',
        group_id: null,
        location_id: null
    }
    showUserModal.value = true
}

function openEditUser(user) {
    isEditingUser.value = true
    userFormData.value = {
        id: user.id,
        email: user.email,
        password: '',
        full_name: user.full_name,
        role: user.role,
        group_id: user.group_id,
        location_id: user.location_id
    }
    showUserModal.value = true
}

async function handleUserSubmit() {
    try {
        const url = `${API_URL}/api/admin/users` + (isEditingUser.value ? `?id=${userFormData.value.id}` : '')
        const method = isEditingUser.value ? 'PUT' : 'POST'

        if (!isEditingUser.value && (!userFormData.value.email || !userFormData.value.password)) {
            return alert("Email e Password obbligatorie")
        }

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(userFormData.value)
        })
        
        const data = await res.json()
        if (!res.ok) throw new Error(data.error || 'Errore operazione')
        
        alert("Utente salvato!")
        showUserModal.value = false
        fetchUsers()
    } catch (e) {
        alert("Errore: " + e.message)
    }
}

async function deleteUser(id) {
    if (!confirm("Sei sicuro di voler eliminare questo utente?")) return
    try {
        const res = await fetch(`${API_URL}/api/admin/users?id=${id}`, { method: 'DELETE' })
        if (!res.ok) throw new Error('Errore eliminazione')
        fetchUsers()
    } catch (e) {
        alert(e.message)
    }
}

// --- GROUP ACTIONS ---

function openCreateGroup() {
    isEditingGroup.value = false
    groupFormData.value = { id: null, name: '', accessible_modules: [] }
    showGroupModal.value = true
}

function openEditGroup(group) {
    isEditingGroup.value = true
    groupFormData.value = { 
        id: group.id, 
        name: group.name, 
        accessible_modules: [...(group.accessible_modules || [])] 
    }
    showGroupModal.value = true
}

function toggleModule(moduleId) {
    const idx = groupFormData.value.accessible_modules.indexOf(moduleId)
    if (idx > -1) {
        groupFormData.value.accessible_modules.splice(idx, 1)
    } else {
        groupFormData.value.accessible_modules.push(moduleId)
    }
}

async function handleGroupSubmit() {
    try {
        const url = `${API_URL}/api/admin/groups` + (isEditingGroup.value ? `?id=${groupFormData.value.id}` : '')
        const method = isEditingGroup.value ? 'PUT' : 'POST'

        if (!groupFormData.value.name) return alert("Inserisci il nome del gruppo")

        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(groupFormData.value)
        })
        
        const data = await res.json()
        if (!res.ok) throw new Error(data.error || 'Errore salvataggio gruppo')
        
        alert("Gruppo salvato!")
        showGroupModal.value = false
        fetchGroups() // Refresh lists
    } catch (e) {
        alert("Errore: " + e.message)
    }
}

async function deleteGroup(id) {
    if (!confirm("Eliminare questo gruppo? Se ci sono utenti assegnati, l'operazione potrebbe fallire.")) return
    try {
        const res = await fetch(`${API_URL}/api/admin/groups?id=${id}`, { method: 'DELETE' })
        if (!res.ok) {
             const json = await res.json().catch(() => ({}))
             throw new Error(json.error || 'Errore eliminazione (Verifica che non ci siano utenti assegnati)')
        }
        fetchGroups()
    } catch (e) {
        alert(e.message)
    }
}

</script>

<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold tracking-tight">Admin Dashboard <span class="text-sm font-normal text-gray-500">(Live v2)</span></h1>
        <p class="text-muted-foreground">Pannello di controllo completo</p>
        <div class="mt-2 p-2 bg-gray-100 text-xs font-mono rounded">
           API: {{ API_URL }} <br>
           Status: {{ statusMessage }}
        </div>
      </div>
    </div>

    <!-- TABS -->
    <div class="border-b border-gray-200">
      <div class="flex space-x-4">
        <button 
            @click="activeTab = 'users'"
            :class="[
                activeTab === 'users' ? 'bg-[#CC0000] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300',
                'py-2 px-4 rounded-md font-medium text-sm flex items-center transition-all'
            ]"
        >
          <Users class="w-4 h-4 mr-2" />
          Gestione Utenti
        </button>
        <button 
            @click="activeTab = 'groups'"
            :class="[
                activeTab === 'groups' ? 'bg-[#CC0000] text-white shadow-md' : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-300',
                'py-2 px-4 rounded-md font-medium text-sm flex items-center transition-all'
            ]"
        >
          <Settings class="w-4 h-4 mr-2" />
          Gestione Gruppi
        </button>
      </div>
    </div>

    <!-- USERS TAB CONTENT -->
    <div v-if="activeTab === 'users'" class="space-y-6">
         <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Elenco Utenti</h2>
            <Button @click="openCreateUser" class="bg-[#CC0000] hover:bg-[#990000] text-white">
                <Plus class="w-4 h-4 mr-2" /> Nuovo Utente
            </Button>
         </div>

         <!-- Users Table -->
        <Card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 font-medium border-b">
                        <tr>
                            <th class="px-6 py-3">Utente</th>
                            <th class="px-6 py-3">Ruolo / Gruppo</th>
                            <th class="px-6 py-3">Sede</th>
                            <th class="px-6 py-3 text-right">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loadingUsers">
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Caricamento...</td>
                        </tr>
                        <tr v-else-if="users.length === 0">
                            <td colspan="4" class="px-6 py-4 text-center text-gray-500">Nessun utente trovato</td>
                        </tr>
                        <tr v-else v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900">{{ user.full_name || 'N/A' }}</div>
                                <div class="text-gray-500 text-xs">{{ user.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-1 items-start">
                                    <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${user.role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}`">
                                        <Shield v-if="user.role === 'admin'" class="w-3 h-3 mr-1" />
                                        {{ user.role === 'admin' ? 'Super Admin' : 'Volontario' }}
                                    </span>
                                    <span v-if="user.group_name !== 'N/A'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                        <Users class="w-3 h-3 mr-1" /> {{ user.group_name }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center text-gray-600" v-if="user.location_name !== 'N/A'">
                                    <MapPin class="w-3 h-3 mr-1" />
                                    {{ user.location_name }}
                                </div>
                                <span v-else class="text-gray-400 text-xs">-</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditUser(user)" class="text-gray-400 hover:text-blue-600 transition-colors mr-3" title="Modifica">
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button @click="deleteUser(user.id)" class="text-gray-400 hover:text-[#CC0000] transition-colors" title="Elimina">
                                    <Trash class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>

    <!-- GROUPS TAB CONTENT -->
    <div v-if="activeTab === 'groups'" class="space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Gruppi e Permessi</h2>
            <Button @click="openCreateGroup" class="bg-[#CC0000] hover:bg-[#990000] text-white">
                <Plus class="w-4 h-4 mr-2" /> Nuovo Gruppo
            </Button>
         </div>

        <Card class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 font-medium border-b">
                        <tr>
                            <th class="px-6 py-3">Nome Gruppo</th>
                            <th class="px-6 py-3">Moduli Abilitati</th>
                            <th class="px-6 py-3 text-right">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                         <tr v-if="loadingGroups">
                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">Caricamento...</td>
                        </tr>
                        <tr v-else v-for="group in groupsList" :key="group.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ group.name }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    <span v-for="mod in group.accessible_modules" :key="mod" 
                                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                        {{ mod }}
                                    </span>
                                    <span v-if="!group.accessible_modules?.length" class="text-gray-400 text-xs italic">Nessun modulo</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditGroup(group)" class="text-gray-400 hover:text-blue-600 transition-colors mr-3" title="Modifica">
                                    <Edit class="w-4 h-4" />
                                </button>
                                <button @click="deleteGroup(group.id)" class="text-gray-400 hover:text-[#CC0000] transition-colors" title="Elimina">
                                    <Trash class="w-4 h-4" />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Card>
    </div>

    <!-- MODAL USER -->
    <div v-if="showUserModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <!-- (Same User Form as before, slightly adjusted layout) -->
         <Card class="w-full max-w-md p-6 space-y-4 bg-white shadow-xl relative animate-in fade-in zoom-in-95 duration-200">
            <h2 class="text-xl font-bold">{{ isEditingUser ? 'Modifica Utente' : 'Nuovo Utente' }}</h2>
            <form @submit.prevent="handleUserSubmit" class="space-y-4">
                 <!-- Common User Inputs -->
                <div class="space-y-2">
                    <label class="text-sm font-medium">Nome Completo</label>
                    <input v-model="userFormData.full_name" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
                </div>
                <div class="space-y-2">
                    <label class="text-sm font-medium">Email</label>
                    <input v-model="userFormData.email" :disabled="isEditingUser" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm disabled:opacity-50" />
                </div>
                <div class="space-y-2" v-if="!isEditingUser">
                    <label class="text-sm font-medium">Password</label>
                    <input v-model="userFormData.password" type="password" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" />
                </div>
                
                 <!-- Security Context -->
                <div class="p-3 bg-gray-50 rounded-md space-y-3 border">
                     <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Livello Accesso</label>
                        <select v-model="userFormData.role" class="flex h-9 w-full rounded-md border border-gray-200 bg-white px-3 py-1 text-sm">
                            <option value="volontario">Volontario</option>
                            <option value="admin">Super Admin</option>
                        </select>
                    </div>
                     <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Gruppo Operativo</label>
                        <select v-model="userFormData.group_id" class="flex h-9 w-full rounded-md border border-gray-200 bg-white px-3 py-1 text-sm">
                            <option :value="null">-- Nessun Gruppo --</option>
                            <option v-for="g in groups" :key="g.id" :value="g.id">{{ g.name }}</option>
                        </select>
                    </div>
                     <div class="space-y-1">
                        <label class="text-xs font-semibold text-gray-500 uppercase">Sede di Appartenenza</label>
                        <select v-model="userFormData.location_id" class="flex h-9 w-full rounded-md border border-gray-200 bg-white px-3 py-1 text-sm">
                            <option :value="null">-- Nessuna Sede --</option>
                            <option v-for="l in locations" :key="l.id" :value="l.id">{{ l.name }}</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <Button type="button" variant="ghost" @click="showUserModal = false">Annulla</Button>
                    <Button type="submit" class="bg-[#CC0000] hover:bg-[#990000] text-white">Salva</Button>
                </div>
            </form>
         </Card>
    </div>

    <!-- MODAL GROUP -->
    <div v-if="showGroupModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <Card class="w-full max-w-md p-6 space-y-4 bg-white shadow-xl relative animate-in fade-in zoom-in-95 duration-200">
            <h2 class="text-xl font-bold">{{ isEditingGroup ? 'Modifica Gruppo' : 'Nuovo Gruppo' }}</h2>
             <form @submit.prevent="handleGroupSubmit" class="space-y-4">
                 <div class="space-y-2">
                    <label class="text-sm font-medium">Nome Gruppo</label>
                    <input v-model="groupFormData.name" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm" placeholder="es. Segreteria" />
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium">Moduli Accessibili</label>
                    <div class="grid grid-cols-1 gap-2 border rounded-md p-3 bg-gray-50 max-h-48 overflow-y-auto">
                        <label v-for="module in availableModules" :key="module.id" 
                               class="flex items-center space-x-3 p-2 bg-white rounded border cursor-pointer hover:bg-gray-50">
                            <input type="checkbox" 
                                   :checked="groupFormData.accessible_modules.includes(module.id)"
                                   @change="toggleModule(module.id)"
                                   class="h-4 w-4 rounded border-gray-300 text-[#CC0000] focus:ring-[#CC0000]">
                            <component :is="module.icon" class="w-4 h-4 text-gray-500" />
                            <span class="text-sm font-medium text-gray-700">{{ module.label }}</span>
                        </label>
                    </div>
                    <p class="text-xs text-muted-foreground">Seleziona quali aree del CRM possono vedere i membri di questo gruppo.</p>
                </div>

                 <div class="flex justify-end gap-2 pt-2">
                    <Button type="button" variant="ghost" @click="showGroupModal = false">Annulla</Button>
                    <Button type="submit" class="bg-[#CC0000] hover:bg-[#990000] text-white">Salva Gruppo</Button>
                </div>
             </form>
         </Card>
    </div>

  </div>
</template>
