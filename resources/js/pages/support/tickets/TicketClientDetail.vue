<script setup lang="ts">
import { ref, computed, onMounted, nextTick, watch, onBeforeUnmount } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import {
    Paperclip,
    Send,
    Download,
    Clock,
    User,
    Tag,
    Calendar,
    CheckCircle,
    XCircle,
    FileText,
    Image,
    File,
    Upload,
    X,
    MessageSquare,
    UserCircle,
    Hash,
    FolderOpen,
    Activity,
    Zap,
    RefreshCw,
    ExternalLink,
    Info,
} from 'lucide-vue-next';
import echo from '@/echo';

// Props with default values
const props = defineProps<{
    title: string;
    ticket: {
        id: number;
        code: string;
        subject: string;
        status: string;
        priority: {
            id: number;
            name: string;
        };
        category: {
            id: number;
            name: string;
        };
        client: {
            id: number;
            name: string;
            email: string;
            avatar?: string;
        };
        assigned_technician?: {
            id: number;
            name: string;
            email: string;
            avatar?: string;
        };
        created_at: string;
        sla_due?: string;
        resolved_at?: string;
        closed_at?: string;
    };
    messages: Array<{
        id: number;
        message: string;
        is_internal: boolean;
        sender_type: 'client' | 'technician';
        sender_name: string;
        sender_email: string;
        sender_avatar?: string;
        role: string;
        attachments: Array<{
            id: number;
            filename: string;
            size: number;
            mime_type: string;
            url: string;
        }>;
        created_at: string;
    }>;
    all_attachments: Array<{
        id: number;
        filename: string;
        size: number;
        mime_type: string;
        url: string;
        uploaded_at: string;
    }>;
    timeline: Array<{
        id: number;
        type: string;
        description: string;
        icon: string;
        user_name: string;
        user_avatar?: string;
        created_at: string;
    }>;
}>();

// Form
const form = useForm({
    message: '',
    attachments: [] as File[],
});

const ALLOWED_TYPES = [
    'image/png',
    'image/jpeg',
    'image/jpg',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB

// Refs
const messagesContainer = ref<HTMLElement | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);

// Use reactive messages that can be updated in real-time
const messages = ref(props.messages || []);
const ticketData = ref(props.ticket);

// Real-time subscription
let subscription: any = null;

// Listen for new messages - REAL TIME
const listenForMessages = () => {
    if (subscription) {
        subscription.stopListening('TicketMessageSent');
        subscription.stopListening('TicketUpdated');
        subscription.unsubscribe();
    }

    subscription = echo.channel(`ticket.${props.ticket.id}`);
    
    // Listen for new messages
    subscription.listen('TicketMessageSent', (data: any) => {
        // Check if message is not internal (client only sees public messages)
        // For admin, they see both internal and public, so we keep all
        // For client, we filter internal messages
        if (!data.is_internal) {
            // Add new message to the list
            messages.value = [...messages.value, data];
            
            // Scroll to bottom
            scrollToBottom();
        }
    });

    // Listen for ticket updates
    subscription.listen('TicketUpdated', (data: any) => {
        // Update ticket data
        const updatedAssignedTechnician = ticketData.value.assigned_technician && data.assigned_to
            ? { ...ticketData.value.assigned_technician, name: data.assigned_to }
            : ticketData.value.assigned_technician;

        ticketData.value = {
            ...ticketData.value,
            status: data.status,
            priority: ticketData.value.priority,
            assigned_technician: updatedAssignedTechnician,
            resolved_at: data.resolved_at || ticketData.value.resolved_at,
            closed_at: data.closed_at || ticketData.value.closed_at,
        };
    });
};

// Computed with safe access - now using reactive messages
const filteredMessages = computed(() => {
    // Use messages.value instead of props.messages
    if (!messages.value || !Array.isArray(messages.value)) {
        return [];
    }
    return messages.value
        .filter(msg => !msg.is_internal)
        .sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime());
});

const hasAttachments = computed(() => form.attachments.length > 0);

const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        'open': 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
        'in_progress': 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        'waiting_customer': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        'waiting_technician': 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
        'resolved': 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'closed': 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400',
    };
    return colors[status] || colors['open'];
};

const getPriorityColor = (priority: string) => {
    const colors: Record<string, string> = {
        'low': 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
        'medium': 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
        'high': 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
        'critical': 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
    };
    return colors[priority] || colors['low'];
};

const getFileIconComponent = (mimeType: string) => {
    const iconMap: Record<string, any> = {
        'image/png': Image,
        'image/jpeg': Image,
        'image/jpg': Image,
        'application/pdf': FileText,
        'application/msword': FileText,
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': FileText,
    };
    return iconMap[mimeType] || File;
};

const getTimelineIcon = (type: string) => {
    const iconMap: Record<string, any> = {
        'created': Zap,
        'assigned': User,
        'technician_replied': MessageSquare,
        'client_replied': MessageSquare,
        'resolved': CheckCircle,
        'closed': XCircle,
    };
    return iconMap[type] || Clock;
};

const getTimelineColor = (type: string) => {
    const colorMap: Record<string, string> = {
        'created': 'text-blue-500 bg-blue-100 dark:bg-blue-900/30',
        'assigned': 'text-purple-500 bg-purple-100 dark:bg-purple-900/30',
        'technician_replied': 'text-green-500 bg-green-100 dark:bg-green-900/30',
        'client_replied': 'text-orange-500 bg-orange-100 dark:bg-orange-900/30',
        'resolved': 'text-green-500 bg-green-100 dark:bg-green-900/30',
        'closed': 'text-gray-500 bg-gray-100 dark:bg-gray-800',
    };
    return colorMap[type] || 'text-gray-500 bg-gray-100 dark:bg-gray-800';
};

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const formatDate = (date: string) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleString('en-US', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const getInitials = (name: string) => {
    if (!name) return '?';
    return name
        .split(' ')
        .map(word => word[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
};

// Methods
const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files?.length) {
        addFiles(Array.from(target.files));
    }
    target.value = '';
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = false;

    if (e.dataTransfer?.files.length) {
        addFiles(Array.from(e.dataTransfer.files));
    }
};

const addFiles = (files: File[]) => {
    const validFiles = files.filter(file => {
        const isValidType = ALLOWED_TYPES.includes(file.type);
        const isValidSize = file.size <= MAX_FILE_SIZE;

        if (!isValidType) {
            form.errors.attachments = `File "${file.name}" has an unsupported format.`;
        } else if (!isValidSize) {
            form.errors.attachments = `File "${file.name}" exceeds the 10MB limit.`;
        }

        return isValidType && isValidSize;
    });

    if (validFiles.length) {
        form.attachments = [...form.attachments, ...validFiles];
        form.errors.attachments = undefined;
    }
};

const removeFile = (index: number) => {
    form.attachments = form.attachments.filter((_, i) => i !== index);
    if (!form.attachments.length) {
        form.errors.attachments = undefined;
    }
};

const submitReply = () => {
    if (!form.message.trim() && !form.attachments.length) {
        form.errors.message = 'Please enter a message or attach a file.';
        return;
    }

    const data = new FormData();
    data.append('message', form.message);

    form.attachments.forEach((file, index) => {
        data.append(`attachments[${index}]`, file);
    });

    form.post(`/client/tickets/${props.ticket.id}/reply`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            form.attachments = [];
            scrollToBottom();
        },
    });
};

const scrollToBottom = () => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
        }
    });
};

const downloadAttachment = (url: string, filename: string) => {
    if (url) {
        window.open(url, '_blank');
    }
};

// Watch for new messages and scroll
watch(filteredMessages, () => {
    scrollToBottom();
});

// Lifecycle
onMounted(() => {
    scrollToBottom();
    listenForMessages();
});

onBeforeUnmount(() => {
    if (subscription) {
        subscription.stopListening('TicketMessageSent');
        subscription.stopListening('TicketUpdated');
        subscription.unsubscribe();
    }
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tickets',
                href: '/client/tickets',
            },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ ticket?.subject || 'Ticket Detail' }}
                        </h1>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <span
                                class="rounded-md border border-gray-200 px-2 py-1 font-mono text-sm dark:border-gray-700">
                                #{{ ticket?.code || 'N/A' }}
                            </span>
                            <span
                                :class="[getStatusColor(ticket?.status || 'open'), 'rounded-full px-3 py-1 text-xs font-medium capitalize']">
                                {{ ticket?.status?.replace('_', ' ') || 'Open' }}
                            </span>
                            <span
                                :class="[getPriorityColor(ticket?.priority?.name || 'low'), 'rounded-full px-3 py-1 text-xs font-medium capitalize']">
                                {{ ticket?.priority?.name || 'Low' }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ ticket?.category?.name || 'Uncategorized' }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Created {{ formatDate(ticket?.created_at) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <RefreshCw class="mr-2 h-4 w-4" />
                            Refresh
                        </button>
                        <button
                            class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <ExternalLink class="mr-2 h-4 w-4" />
                            View in Portal
                        </button>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Left Column - Communication -->
                <div class="lg:col-span-8 space-y-6">
                    <!-- Conversation Card -->
                    <div
                        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <!-- Card Header -->
                        <div
                            class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/50">
                            <div class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                <MessageSquare class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                Conversation
                                <span class="ml-auto text-sm font-normal text-gray-500 dark:text-gray-400">
                                    {{ filteredMessages.length }} messages
                                </span>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-0">
                            <!-- Messages Container -->
                            <div ref="messagesContainer" class="h-[500px] overflow-y-auto p-6"
                                style="scrollbar-width: thin;">
                                <!-- Empty State -->
                                <div v-if="!filteredMessages.length"
                                    class="flex h-full flex-col items-center justify-center text-center">
                                    <div class="rounded-full bg-gray-100 p-4 dark:bg-gray-800">
                                        <MessageSquare class="h-8 w-8 text-gray-400 dark:text-gray-600" />
                                    </div>
                                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">
                                        No messages yet
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        Start the conversation by replying below.
                                    </p>
                                </div>

                                <!-- Messages -->
                                <div v-else class="space-y-6">
                                    <div v-for="message in filteredMessages" :key="message.id" class="flex gap-3"
                                        :class="message.sender_type === 'client' ? 'flex-row-reverse' : ''">
                                        <!-- Avatar -->
                                        <div
                                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
                                            {{ getInitials(message.sender_name) }}
                                        </div>

                                        <!-- Message Bubble -->
                                        <div class="max-w-[80%] space-y-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                                    {{ message.sender_name }}
                                                </span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    {{ message.role }}
                                                </span>
                                                <span class="text-xs text-gray-400 dark:text-gray-500">
                                                    {{ formatDate(message.created_at) }}
                                                </span>
                                            </div>

                                            <div class="rounded-2xl px-4 py-3" :class="[
                                                message.sender_type === 'client'
                                                    ? 'bg-blue-600 text-white dark:bg-blue-600'
                                                    : 'bg-gray-100 text-gray-900 dark:bg-gray-800 dark:text-white'
                                            ]">
                                                <div class="whitespace-pre-wrap text-sm">
                                                    {{ message.message }}
                                                </div>

                                                <!-- Attachments -->
                                                <div v-if="message.attachments && message.attachments.length" class="mt-3 space-y-2">
                                                    <div v-for="attachment in message.attachments" :key="attachment.id"
                                                        class="flex items-center gap-2 rounded-lg p-2" :class="[
                                                            message.sender_type === 'client'
                                                                ? 'bg-blue-700/30'
                                                                : 'bg-gray-200 dark:bg-gray-700'
                                                        ]">
                                                        <component :is="getFileIconComponent(attachment.mime_type)"
                                                            class="h-4 w-4" :class="[
                                                                message.sender_type === 'client'
                                                                    ? 'text-white/80'
                                                                    : 'text-gray-500 dark:text-gray-400'
                                                            ]" />
                                                        <span class="flex-1 text-sm">
                                                            {{ attachment.filename }}
                                                            <span class="text-xs opacity-70">
                                                                ({{ formatFileSize(attachment.size) }})
                                                            </span>
                                                        </span>
                                                        <button
                                                            @click="downloadAttachment(attachment.url, attachment.filename)"
                                                            class="rounded p-1 transition-colors hover:bg-white/20">
                                                            <Download class="h-4 w-4" />
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Reply Box -->
                            <div
                                class="border-t border-gray-100 bg-gray-50/50 p-6 dark:border-gray-800 dark:bg-gray-900/50">
                                <form @submit.prevent="submitReply" class="space-y-4">
                                    <!-- Textarea -->
                                    <div>
                                        <textarea v-model="form.message" rows="3" placeholder="Write your reply..."
                                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500/20': form.errors.message }" />
                                        <p v-if="form.errors.message" class="mt-2 text-sm text-red-500">
                                            {{ form.errors.message }}
                                        </p>
                                    </div>

                                    <!-- File Upload Area -->
                                    <div>
                                        <!-- Drop Zone -->
                                        <div @dragover.prevent="isDragging = true"
                                            @dragleave.prevent="isDragging = false" @drop="handleDrop"
                                            class="relative rounded-xl border-2 border-dashed p-4 text-center transition-colors"
                                            :class="[
                                                isDragging
                                                    ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20'
                                                    : 'border-gray-300 hover:border-gray-400 dark:border-gray-600',
                                                form.errors.attachments ? 'border-red-500' : ''
                                            ]">
                                            <input ref="fileInputRef" type="file" multiple @change="handleFileSelect"
                                                class="absolute inset-0 cursor-pointer opacity-0"
                                                accept=".png,.jpg,.jpeg,.pdf,.doc,.docx" />

                                            <div class="flex flex-col items-center gap-1">
                                                <Upload class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                                                <div>
                                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                                        Drop files here or <span
                                                            class="text-blue-600 dark:text-blue-400">browse</span>
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        PNG, JPG, PDF, DOC, DOCX (Max 10 MB each)
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <p v-if="form.errors.attachments" class="mt-2 text-sm text-red-500">
                                            {{ form.errors.attachments }}
                                        </p>

                                        <!-- File List -->
                                        <div v-if="hasAttachments" class="mt-3 space-y-2">
                                            <div v-for="(file, index) in form.attachments" :key="index"
                                                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                                <div class="flex items-center gap-2">
                                                    <component :is="getFileIconComponent(file.type)"
                                                        class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                                    <div>
                                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                            {{ file.name }}
                                                        </p>
                                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                                            {{ formatFileSize(file.size) }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <button type="button" @click="removeFile(index)"
                                                    class="rounded p-1 transition-colors hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/20">
                                                    <X class="h-4 w-4" />
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="fileInputRef?.click()"
                                                class="inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
                                                <Paperclip class="mr-2 h-4 w-4" />
                                                Attach Files
                                            </button>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ form.attachments.length }} file(s) attached
                                            </span>
                                        </div>
                                        <button type="submit" :disabled="form.processing"
                                            class="inline-flex min-w-[120px] items-center justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-60 dark:bg-blue-600 dark:hover:bg-blue-700">
                                            <span v-if="form.processing" class="flex items-center gap-2">
                                                <span
                                                    class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                                Sending...
                                            </span>
                                            <span v-else class="flex items-center gap-2">
                                                <Send class="h-4 w-4" />
                                                Send Reply
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Sidebar -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Ticket Information -->
                    <div
                        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <div
                            class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/50">
                            <div class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                <Info class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                Ticket Information
                            </div>
                        </div>
                        <div class="space-y-4 p-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</p>
                                    <span
                                        :class="[getStatusColor(ticket?.status || 'open'), 'mt-1 inline-block rounded-full px-3 py-1 text-xs font-medium capitalize']">
                                        {{ ticket?.status?.replace('_', ' ') || 'Open' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Priority</p>
                                    <span
                                        :class="[getPriorityColor(ticket?.priority?.name || 'low'), 'mt-1 inline-block rounded-full px-3 py-1 text-xs font-medium capitalize']">
                                        {{ ticket?.priority?.name || 'Low' }}
                                    </span>
                                </div>
                            </div>

                            <hr class="border-gray-200 dark:border-gray-700" />

                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <Hash class="h-4 w-4 text-gray-400" />
                                    <span class="font-mono text-sm text-gray-600 dark:text-gray-300">
                                        {{ ticket?.code || 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Tag class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ ticket?.category?.name || 'Uncategorized' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <User class="h-4 w-4 text-gray-400" />
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ ticket?.client?.name || 'Unknown' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ ticket?.client?.email || '' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserCircle class="h-4 w-4 text-gray-400" />
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ ticket?.assigned_technician?.name || 'Unassigned' }}
                                        </p>
                                        <p v-if="ticket?.assigned_technician"
                                            class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ ticket.assigned_technician.email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ formatDate(ticket?.created_at) }}
                                    </span>
                                </div>
                                <div v-if="ticket?.sla_due" class="flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        SLA Due: {{ formatDate(ticket.sla_due) }}
                                    </span>
                                </div>
                                <div v-if="ticket?.resolved_at" class="flex items-center gap-2">
                                    <CheckCircle class="h-4 w-4 text-green-500" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        Resolved: {{ formatDate(ticket.resolved_at) }}
                                    </span>
                                </div>
                                <div v-if="ticket?.closed_at" class="flex items-center gap-2">
                                    <XCircle class="h-4 w-4 text-gray-500" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        Closed: {{ formatDate(ticket.closed_at) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Attachments -->
                    <div
                        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <div
                            class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/50">
                            <div class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                <Paperclip class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                Attachments
                                <span class="ml-auto text-xs font-normal text-gray-500 dark:text-gray-400">
                                    {{ all_attachments?.length || 0 }}
                                </span>
                            </div>
                        </div>
                        <div class="p-4">
                            <div v-if="!all_attachments || !all_attachments.length" class="py-6 text-center">
                                <FolderOpen class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    No attachments
                                </p>
                            </div>
                            <div v-else class="space-y-2">
                                <div v-for="attachment in all_attachments" :key="attachment.id"
                                    class="flex items-center gap-2 rounded-lg border border-gray-100 p-2 transition-colors hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/50">
                                    <component :is="getFileIconComponent(attachment.mime_type)"
                                        class="h-5 w-5 text-gray-400" />
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ attachment.filename }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ formatFileSize(attachment.size) }}
                                        </p>
                                    </div>
                                    <button @click="downloadAttachment(attachment.url, attachment.filename)"
                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <Download class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div
                        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <div
                            class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/50">
                            <div class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                <Clock class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                Timeline
                            </div>
                        </div>
                        <div class="max-h-[400px] overflow-y-auto p-4">
                            <div v-if="!timeline || !timeline.length" class="py-6 text-center">
                                <Activity class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600" />
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                    No timeline events
                                </p>
                            </div>
                            <div v-else class="relative space-y-4">
                                <div v-for="(event, index) in timeline" :key="event.id" class="relative pl-8"
                                    :class="index < timeline.length - 1 ? 'pb-4' : ''">
                                    <!-- Timeline Line -->
                                    <div v-if="index < timeline.length - 1"
                                        class="absolute left-3 top-6 h-full w-px bg-gray-200 dark:bg-gray-700" />

                                    <!-- Timeline Dot -->
                                    <div class="absolute left-0 top-1 flex h-6 w-6 items-center justify-center rounded-full"
                                        :class="getTimelineColor(event.type)">
                                        <component :is="getTimelineIcon(event.type)" class="h-3 w-3" />
                                    </div>

                                    <!-- Timeline Content -->
                                    <div>
                                        <p class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ event.description }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                by {{ event.user_name }}
                                            </span>
                                            <span class="text-xs text-gray-400 dark:text-gray-500">
                                                {{ formatDate(event.created_at) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Custom scrollbar styles */
.h-\[500px\]::-webkit-scrollbar {
    width: 6px;
}

.h-\[500px\]::-webkit-scrollbar-track {
    background: transparent;
}

.h-\[500px\]::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 9999px;
}

.dark .h-\[500px\]::-webkit-scrollbar-thumb {
    background: #374151;
}

.h-\[500px\]::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.dark .h-\[500px\]::-webkit-scrollbar-thumb:hover {
    background: #4b5563;
}

.max-h-\[400px\]::-webkit-scrollbar {
    width: 6px;
}

.max-h-\[400px\]::-webkit-scrollbar-track {
    background: transparent;
}

.max-h-\[400px\]::-webkit-scrollbar-thumb {
    background: #d1d5db;
    border-radius: 9999px;
}

.dark .max-h-\[400px\]::-webkit-scrollbar-thumb {
    background: #374151;
}

.max-h-\[400px\]::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

.dark .max-h-\[400px\]::-webkit-scrollbar-thumb:hover {
    background: #4b5563;
}
</style>