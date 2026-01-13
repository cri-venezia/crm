import { create } from 'zustand'

export type Message = {
    role: 'user' | 'model'
    content: string
}

interface ChatState {
    messages: Message[]
    isLoading: boolean
    userId: string | null
    setUserId: (id: string | null) => void
    setIsLoading: (loading: boolean) => void
    addMessage: (message: Message) => void
    setMessages: (messages: Message[]) => void
    loadHistory: () => Promise<void>
    clearHistory: () => Promise<void>
}

export const useChatStore = create<ChatState>((set, get) => ({
    messages: [],
    isLoading: false,
    userId: null,
    setUserId: (id) => set({ userId: id }),
    setIsLoading: (loading) => set({ isLoading: loading }),
    addMessage: (message) => set((state) => ({ messages: [...state.messages, message] })),
    setMessages: (messages) => set({ messages }),
    loadHistory: async () => {
        const { userId } = get()
        if (!userId) return

        set({ isLoading: true })
        try {
            const response = await fetch('/api/erika/history')
            if (response.ok) {
                const data = await response.json()
                set({ messages: data.messages })
            }
        } catch (error) {
            console.error('Failed to load history:', error)
        } finally {
            set({ isLoading: false })
        }
    },
    clearHistory: async () => {
        set({ isLoading: true })
        try {
            const response = await fetch('/api/erika/history', { method: 'DELETE' })
            if (response.ok) {
                set({ messages: [] })
            }
        } catch (error) {
            console.error('Failed to clear history:', error)
        } finally {
            set({ isLoading: false })
        }
    }
}))
