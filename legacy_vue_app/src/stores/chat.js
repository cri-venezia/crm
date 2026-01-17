
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { supabase } from '../lib/supabase'

export const useChatStore = defineStore('chat', () => {
    const messages = ref([])
    const isLoading = ref(false)
    const userId = ref(null)

    function addMessage(msg) {
        messages.value.push(msg)
    }

    function setMessages(msgs) {
        messages.value = msgs
    }

    function setUserId(id) {
        userId.value = id
    }

    async function loadHistory() {
        if (!userId.value) return
        const { data } = await supabase
            .from('ai_chat_logs')
            .select('message_input, message_output, created_at')
            .eq('user_id', userId.value)
            .order('created_at', { ascending: true })

        if (data) {
            messages.value = data.flatMap(log => [
                { role: 'user', content: log.message_input },
                { role: 'model', content: log.message_output }
            ])
        }
    }

    async function clearHistory() {
        if (!userId.value) return
        await supabase.from('ai_chat_logs').delete().eq('user_id', userId.value)
        messages.value = []
    }

    return {
        messages,
        addMessage,
        setMessages,
        isLoading,
        loadHistory,
        clearHistory,
        userId,
        setUserId
    }
})
