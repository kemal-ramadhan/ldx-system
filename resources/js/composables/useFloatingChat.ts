import { ref } from 'vue'

const isOpen = ref(false)
const activeTicket = ref(null)

export function useFloatingChat() {
    const openChat = (ticket: any) => {
        activeTicket.value = ticket
        isOpen.value = true
    }

    const closeChat = () => {
        isOpen.value = false
        activeTicket.value = null
    }

    const toggleChat = () => {
        isOpen.value = !isOpen.value
    }

    return {
        isOpen,
        activeTicket,
        openChat,
        closeChat,
        toggleChat
    }
}
