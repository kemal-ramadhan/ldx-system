<script setup lang="ts">
import { ref, watch, nextTick, onMounted } from 'vue'
import { X, Minimize2, Maximize2, Send, Paperclip, MessageSquare } from 'lucide-vue-next'
import { useFloatingChat } from '@/composables/useFloatingChat'
import axios from 'axios'
import { toast } from 'vue-sonner'
import { router, usePage } from '@inertiajs/vue3'
import echo from '@/echo'

const chatState = useFloatingChat()
const isMinimized = ref(false)

const messages = ref<any[]>([])
const ticket = ref<any>(null)
const isLoading = ref(false)
const newMessage = ref('')

const messagesContainer = ref<HTMLElement | null>(null)
let subscription: any = null

const page = usePage()

let inertiaListener: any = null

onMounted(() => {
    inertiaListener = router.on('success', () => {
        if (chatState.isOpen.value && chatState.activeTicket.value) {
            // Re-setup echo after page transitions because TicketClientDetail might have unsubscribed
            setTimeout(() => {
                setupEcho()
            }, 300)
        }
    })
})

import { onBeforeUnmount } from 'vue'
onBeforeUnmount(() => {
    if (inertiaListener) inertiaListener()
    teardownEcho()
})

const toggleMinimize = () => {
    isMinimized.value = !isMinimized.value
}

const closeChat = () => {
    chatState.closeChat()
}

const fetchChatData = async () => {
    if (!chatState.activeTicket.value) return
    isLoading.value = true
    try {
        const response = await axios.get(`/client/tickets/${chatState.activeTicket.value.id}/chat-data`)
        ticket.value = response.data.ticket
        messages.value = response.data.messages || []
        scrollToBottom()
    } catch (error) {
        console.error('Failed to load chat data', error)
        toast.error('Failed to load chat data')
    } finally {
        isLoading.value = false
    }
}

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
        }
    })
}

const sendMessage = async () => {
    if (!newMessage.value.trim() || !ticket.value) return

    const messageText = newMessage.value
    newMessage.value = ''

    // Optimistic UI update could go here

    try {
        const formData = new FormData()
        formData.append('message', messageText)

        const response = await axios.post(`/client/tickets/${ticket.value.id}/reply`, formData, {
            headers: {
                'Accept': 'application/json'
            }
        })
        
        // Re-fetch chat data for the widget
        await fetchChatData() 

        // Reload current page props in the background (if on ticket page, this updates the main view)
        router.reload({ only: ['messages', 'ticket'] })

    } catch (error) {
        console.error('Failed to send message', error)
        toast.error('Failed to send message')
        newMessage.value = messageText // restore text
    }
}

watch(() => chatState.isOpen.value, (isOpen) => {
    if (isOpen) {
        isMinimized.value = false
        fetchChatData()
        setupEcho()
    } else {
        teardownEcho()
    }
})

const setupEcho = () => {
    teardownEcho()
    if (!chatState.activeTicket.value) return
    
    subscription = echo.channel(`ticket.${chatState.activeTicket.value.id}`)
    subscription.listen('TicketMessageSent', (data: any) => {
        // Just re-fetch to ensure data format is consistent and attachments are mapped
        // Skip fetching if the message is from the current user (we already handled it via sendMessage)
        if (data.user_id !== page.props.auth.user.id) {
            fetchChatData()
        }
    })
}

const teardownEcho = () => {
    if (subscription && chatState.activeTicket.value) {
        subscription.stopListening('TicketMessageSent')
        echo.leave(`ticket.${chatState.activeTicket.value.id}`)
        subscription = null
    }
}

watch(() => chatState.activeTicket.value, () => {
    if (chatState.isOpen.value) {
        fetchChatData()
    }
}, { deep: true })
</script>

<template>
    <div v-if="chatState.isOpen.value" 
        class="fixed bottom-4 right-4 z-[9999] w-80 sm:w-96 rounded-xl bg-white shadow-2xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 transition-all duration-300 flex flex-col"
        :class="isMinimized ? 'h-14' : 'h-[500px]'">
        
        <!-- Header -->
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800 bg-blue-600 rounded-t-xl text-white cursor-pointer" @click="toggleMinimize">
            <div class="flex items-center gap-2 overflow-hidden">
                <MessageSquare class="w-5 h-5 flex-shrink-0" />
                <span class="font-medium truncate text-sm">
                    {{ ticket?.subject || 'Ticket Chat' }}
                </span>
            </div>
            <div class="flex items-center gap-1 flex-shrink-0">
                <button @click.stop="toggleMinimize" class="p-1 hover:bg-white/20 rounded">
                    <Maximize2 v-if="isMinimized" class="w-4 h-4" />
                    <Minimize2 v-else class="w-4 h-4" />
                </button>
                <button @click.stop="closeChat" class="p-1 hover:bg-white/20 rounded">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Body -->
        <div v-show="!isMinimized" class="flex-1 overflow-y-auto p-4 flex flex-col gap-3 bg-gray-50 dark:bg-gray-900" ref="messagesContainer">
            <div v-if="isLoading && !messages.length" class="flex justify-center items-center h-full">
                <span class="text-sm text-gray-500">Loading...</span>
            </div>
            
            <template v-else>
                <div v-for="msg in messages" :key="msg.id" class="flex flex-col max-w-[85%]"
                    :class="msg.sender_type === 'client' ? 'self-end items-end' : 'self-start items-start'">
                    
                    <span class="text-[10px] text-gray-500 mb-1 px-1">{{ msg.sender_name }}</span>
                    
                    <div class="rounded-2xl px-3 py-2 text-sm"
                        :class="msg.sender_type === 'client' ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-tl-none'">
                        {{ msg.message }}
                    </div>

                    <div v-if="msg.attachments?.length" class="flex flex-col gap-1 mt-1">
                        <a v-for="att in msg.attachments" :key="att.id" :href="att.url" target="_blank"
                            class="text-xs flex items-center gap-1 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-2 py-1 rounded">
                            <Paperclip class="w-3 h-3" />
                            <span class="truncate max-w-[150px]">{{ att.filename }}</span>
                        </a>
                    </div>
                </div>
            </template>
        </div>

        <!-- Footer / Input -->
        <div v-show="!isMinimized" class="p-3 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700 rounded-b-xl">
            <form @submit.prevent="sendMessage" class="flex items-center gap-2">
                <input v-model="newMessage" type="text" placeholder="Type a message..." 
                    class="flex-1 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-900 border-transparent rounded-lg focus:ring-0 focus:border-transparent dark:text-white"
                    :disabled="isLoading" />
                <button type="submit" :disabled="!newMessage.trim() || isLoading"
                    class="p-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <Send class="w-4 h-4" />
                </button>
            </form>
        </div>
    </div>
</template>
