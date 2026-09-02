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
    Lock,
    Edit,
    Save,
    AlertTriangle,
    Archive,
    ArrowLeft,
} from 'lucide-vue-next';
import echo from '@/echo';

// Props
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
        } | null;
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
    technicians: Array<{
        id: number;
        name: string;
        email: string;
    }>;
    statuses: Array<{
        value: string;
        label: string;
    }>;
    priorities: Array<{
        value: string;
        label: string;
    }>;
}>();

// Reactive data untuk real-time
const messages = ref(props.messages || []);
const ticketData = ref(props.ticket);
const technicians = ref(props.technicians || []);

// Form untuk reply
const replyForm = useForm({
    message: '',
    attachments: [] as File[],
    is_internal: false,
});

// Form untuk update ticket
const updateForm = useForm({
    status: props.ticket?.status || 'open',
    priority: props.ticket?.priority?.name || 'medium',
    assigned_to: props.ticket?.assigned_technician?.id || null,
    category_id: props.ticket?.category?.id || null,
});

// Refs
const messagesContainer = ref<HTMLElement | null>(null);
const fileInputRef = ref<HTMLInputElement | null>(null);
const isDragging = ref(false);
const isEditing = ref(false);

// Subscription untuk real-time
let subscription: any = null;

// Constants
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const ALLOWED_TYPES = [
    'image/png',
    'image/jpeg',
    'image/jpg',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

// Computed - menggunakan messages.value untuk real-time
const allMessages = computed(() => {
    if (!messages.value || !Array.isArray(messages.value)) {
        return [];
    }
    return [...messages.value].sort((a, b) =>
        new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
    );
});

const publicMessages = computed(() => {
    return allMessages.value.filter(msg => !msg.is_internal);
});

const internalMessages = computed(() => {
    return allMessages.value.filter(msg => msg.is_internal);
});

const hasAttachments = computed(() => replyForm.attachments.length > 0);

const canResolve = computed(() => {
    return ticketData.value?.status !== 'resolved' && ticketData.value?.status !== 'closed';
});

const canClose = computed(() => {
    return ticketData.value?.status === 'resolved';
});

// Status & Priority Colors
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

const getStatusBadgeIcon = (status: string) => {
    const icons: Record<string, any> = {
        'open': AlertTriangle,
        'in_progress': Activity,
        'waiting_customer': Clock,
        'waiting_technician': Clock,
        'resolved': CheckCircle,
        'closed': XCircle,
    };
    return icons[status] || AlertTriangle;
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
        'internal_note': Lock,
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
        'internal_note': 'text-yellow-500 bg-yellow-100 dark:bg-yellow-900/30',
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

// ============================================
// REAL-TIME SUBSCRIPTION
// ============================================
const listenForMessages = () => {
    if (subscription) {
        subscription.stopListening('TicketMessageSent');
        subscription.stopListening('TicketUpdated');
        subscription.unsubscribe();
    }

    subscription = echo.channel(`ticket.${props.ticket.id}`);
    
    // Listen for new messages (admin sees ALL including internal)
    subscription.listen('TicketMessageSent', (data: any) => {
        messages.value = [...messages.value, data];
        scrollToBottom();
    });

    // Listen for ticket updates
    subscription.listen('TicketUpdated', (data: any) => {
        ticketData.value = {
            ...ticketData.value,
            status: data.status,
            priority: ticketData.value.priority,
            assigned_technician: data.assigned_to ? {
                ...ticketData.value.assigned_technician,
                // Ensure `id` is a number to satisfy the expected type.
                id: (ticketData.value.assigned_technician && ticketData.value.assigned_technician.id) || 0,
                // Ensure name is a string and required email field is always present (empty string if unknown)
                name: String(data.assigned_to),
                email: (ticketData.value.assigned_technician && ticketData.value.assigned_technician.email) || '',
                avatar: (ticketData.value.assigned_technician && ticketData.value.assigned_technician.avatar) || undefined,
            } : ticketData.value.assigned_technician,
            resolved_at: data.resolved_at || ticketData.value.resolved_at,
            closed_at: data.closed_at || ticketData.value.closed_at,
        };
        
        // Update form values
        updateForm.status = data.status;
    });
};

// ============================================
// FILE HANDLING
// ============================================
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
            replyForm.errors.attachments = `File "${file.name}" has an unsupported format.`;
        } else if (!isValidSize) {
            replyForm.errors.attachments = `File "${file.name}" exceeds the 10MB limit.`;
        }

        return isValidType && isValidSize;
    });

    if (validFiles.length) {
        replyForm.attachments = [...replyForm.attachments, ...validFiles];
        replyForm.errors.attachments = undefined;
    }
};

const removeFile = (index: number) => {
    replyForm.attachments = replyForm.attachments.filter((_, i) => i !== index);
    if (!replyForm.attachments.length) {
        replyForm.errors.attachments = undefined;
    }
};

// ============================================
// REPLY METHODS
// ============================================
const submitReply = () => {
    if (!replyForm.message.trim() && !replyForm.attachments.length) {
        replyForm.errors.message = 'Please enter a message or attach a file.';
        return;
    }

    replyForm.errors.message = undefined;
    replyForm.errors.attachments = undefined;

    const data = new FormData();
    data.append('message', replyForm.message);
    data.append('is_internal', replyForm.is_internal ? '1' : '0');

    replyForm.attachments.forEach((file, index) => {
        data.append(`attachments[${index}]`, file);
    });

    router.post(`/admin/tickets/${props.ticket.id}/reply`, data, {
        forceFormData: true,
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            replyForm.reset();
            replyForm.attachments = [];
            scrollToBottom();
        },
        onError: (errors) => {
            if (errors.message) {
                replyForm.errors.message = errors.message;
            }
            if (errors.attachments) {
                replyForm.errors.attachments = errors.attachments;
            }
        },
    });
};

// ============================================
// TICKET ACTION METHODS
// ============================================
const updateTicket = () => {
    updateForm.put(`/admin/tickets/${props.ticket.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};

const resolveTicket = () => {
    if (confirm('Are you sure you want to resolve this ticket?')) {
        router.put(`/admin/tickets/${props.ticket.id}/resolve`, {}, {
            preserveScroll: true,
        });
    }
};

const closeTicket = () => {
    if (confirm('Are you sure you want to close this ticket?')) {
        router.put(`/admin/tickets/${props.ticket.id}/close`, {}, {
            preserveScroll: true,
        });
    }
};

const reopenTicket = () => {
    if (confirm('Are you sure you want to reopen this ticket?')) {
        router.put(`/admin/tickets/${props.ticket.id}/reopen`, {}, {
            preserveScroll: true,
        });
    }
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

// ============================================
// LIFECYCLE
// ============================================
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

watch(publicMessages, () => {
    scrollToBottom();
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
                        <div class="flex items-center gap-3">
                            <button @click="$inertia.visit('/admin/tickets')"
                                class="inline-flex items-center rounded-lg px-3 py-2 text-sm text-gray-600 transition-colors hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Back to Tickets
                            </button>
                        </div>
                        <h1 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ ticketData?.subject || 'Ticket Detail' }}
                        </h1>
                        <div class="mt-2 flex flex-wrap items-center gap-3">
                            <span class="rounded-md border border-gray-200 px-2 py-1 font-mono text-sm dark:border-gray-700">
                                #{{ ticketData?.code || 'N/A' }}
                            </span>
                            <span :class="[getStatusColor(ticketData?.status || 'open'), 'rounded-full px-3 py-1 text-xs font-medium capitalize flex items-center gap-1']">
                                <component :is="getStatusBadgeIcon(ticketData?.status || 'open')" class="h-3 w-3" />
                                {{ ticketData?.status?.replace('_', ' ') || 'Open' }}
                            </span>
                            <span :class="[getPriorityColor(ticketData?.priority?.name || 'low'), 'rounded-full px-3 py-1 text-xs font-medium capitalize']">
                                {{ ticketData?.priority?.name || 'Low' }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ ticketData?.category?.name || 'Uncategorized' }}
                            </span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                Created {{ formatDate(ticketData?.created_at) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Action Buttons -->
                        <button v-if="canResolve" @click="resolveTicket"
                            class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-green-700">
                            <CheckCircle class="mr-2 h-4 w-4" />
                            Resolve
                        </button>
                        <button v-if="canClose" @click="closeTicket"
                            class="inline-flex items-center rounded-lg bg-gray-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700">
                            <Archive class="mr-2 h-4 w-4" />
                            Close
                        </button>
                        <button v-if="ticketData?.status === 'closed' || ticketData?.status === 'resolved'" @click="reopenTicket"
                            class="inline-flex items-center rounded-lg bg-yellow-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-yellow-700">
                            <RefreshCw class="mr-2 h-4 w-4" />
                            Reopen
                        </button>
                        <!-- <button @click="isEditing = !isEditing"
                            class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </button> -->
                        <button
                            class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                            <RefreshCw class="mr-2 h-4 w-4" />
                            Refresh
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div v-if="isEditing"
                class="mb-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-lg dark:border-gray-700 dark:bg-gray-900">
                <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Edit Ticket</h3>
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select v-model="updateForm.status"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option v-for="status in statuses" :key="status.value" :value="status.value">
                                {{ status.label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Priority</label>
                        <select v-model="updateForm.priority"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option v-for="priority in priorities" :key="priority.value" :value="priority.value">
                                {{ priority.label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Assign To</label>
                        <select v-model="updateForm.assigned_to"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                            <option :value="null">Unassigned</option>
                            <option v-for="tech in technicians" :key="tech.id" :value="tech.id">
                                {{ tech.name }}
                            </option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button @click="updateTicket" :disabled="updateForm.processing"
                            class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-60">
                            <Save class="mr-2 h-4 w-4" />
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid gap-6 lg:grid-cols-12">
                <!-- Left Column - Communication -->
                <div class="lg:col-span-8 space-y-6">
                    <!-- Conversation Card -->
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <!-- Card Header -->
                        <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4 dark:border-gray-800 dark:bg-gray-900/50">
                            <div class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">
                                <MessageSquare class="h-5 w-5 text-gray-500 dark:text-gray-400" />
                                Conversation
                                <span class="ml-auto text-sm font-normal text-gray-500 dark:text-gray-400">
                                    {{ publicMessages.length }} messages
                                    <span v-if="internalMessages.length" class="ml-2 text-yellow-600 dark:text-yellow-400">
                                        ({{ internalMessages.length }} internal)
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- Card Content -->
                        <div class="p-0">
                            <!-- Messages Container -->
                            <div ref="messagesContainer" class="h-[500px] overflow-y-auto p-6" style="scrollbar-width: thin;">
                                <!-- Empty State -->
                                <div v-if="!allMessages.length" class="flex h-full flex-col items-center justify-center text-center">
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
                                    <div v-for="message in allMessages" :key="message.id" class="flex gap-3" :class="[
                                        message.sender_type === 'client' ? 'flex-row-reverse' : '',
                                        message.is_internal ? 'opacity-75' : ''
                                    ]">
                                        <!-- Avatar -->
                                        <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300">
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
                                                <span v-if="message.is_internal" class="rounded bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">
                                                    Internal
                                                </span>
                                            </div>

                                            <div class="rounded-2xl px-4 py-3" :class="[
                                                message.is_internal
                                                    ? 'bg-yellow-50 text-gray-900 dark:bg-yellow-900/20 dark:text-white border border-yellow-200 dark:border-yellow-800'
                                                    : message.sender_type === 'client'
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
                                                            message.is_internal
                                                                ? 'bg-yellow-200/50 dark:bg-yellow-800/30'
                                                                : message.sender_type === 'client'
                                                                    ? 'bg-blue-700/30'
                                                                    : 'bg-gray-200 dark:bg-gray-700'
                                                        ]">
                                                        <component :is="getFileIconComponent(attachment.mime_type)"
                                                            class="h-4 w-4" :class="[
                                                                message.sender_type === 'client' && !message.is_internal
                                                                    ? 'text-white/80'
                                                                    : 'text-gray-500 dark:text-gray-400'
                                                            ]" />
                                                        <span class="flex-1 text-sm">
                                                            {{ attachment.filename }}
                                                            <span class="text-xs opacity-70">
                                                                ({{ formatFileSize(attachment.size) }})
                                                            </span>
                                                        </span>
                                                        <button @click="downloadAttachment(attachment.url, attachment.filename)"
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

                            <!-- Reply Box - Sama seperti sebelumnya -->
                            <div class="border-t border-gray-100 bg-gray-50/50 p-6 dark:border-gray-800 dark:bg-gray-900/50">
                                <form @submit.prevent="submitReply" class="space-y-4">
                                    <!-- Internal Note Toggle -->
                                    <div class="flex items-center gap-3">
                                        <label class="flex cursor-pointer items-center gap-2">
                                            <input v-model="replyForm.is_internal" type="checkbox"
                                                class="h-4 w-4 rounded border-gray-300 text-yellow-600 focus:ring-yellow-500 dark:border-gray-600 dark:bg-gray-700" />
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <Lock class="mr-1 inline h-3 w-3" />
                                                Internal Note
                                            </span>
                                        </label>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            (Visible only to technicians)
                                        </span>
                                    </div>

                                    <!-- Textarea -->
                                    <div>
                                        <textarea v-model="replyForm.message" rows="3"
                                            :placeholder="replyForm.is_internal ? 'Write an internal note...' : 'Write your reply...'"
                                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                                            :class="{ 'border-yellow-500 focus:border-yellow-500 focus:ring-yellow-500/20': replyForm.is_internal, 'border-red-500 focus:border-red-500 focus:ring-red-500/20': replyForm.errors.message }" />
                                        <p v-if="replyForm.errors.message" class="mt-2 text-sm text-red-500">
                                            {{ replyForm.errors.message }}
                                        </p>
                                    </div>

                                    <!-- File Upload Area -->
                                    <div>
                                        <!-- Drop Zone -->
                                        <div @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false" @drop="handleDrop"
                                            class="relative rounded-xl border-2 border-dashed p-4 text-center transition-colors"
                                            :class="[
                                                isDragging ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20' : 'border-gray-300 hover:border-gray-400 dark:border-gray-600',
                                                replyForm.errors.attachments ? 'border-red-500' : ''
                                            ]">
                                            <input ref="fileInputRef" type="file" multiple @change="handleFileSelect"
                                                class="absolute inset-0 cursor-pointer opacity-0"
                                                accept=".png,.jpg,.jpeg,.pdf,.doc,.docx" />

                                            <div class="flex flex-col items-center gap-1">
                                                <Upload class="h-6 w-6 text-gray-400 dark:text-gray-500" />
                                                <div>
                                                    <p class="text-sm text-gray-600 dark:text-gray-300">
                                                        Drop files here or <span class="text-blue-600 dark:text-blue-400">browse</span>
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                        PNG, JPG, PDF, DOC, DOCX (Max 10 MB each)
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <p v-if="replyForm.errors.attachments" class="mt-2 text-sm text-red-500">
                                            {{ replyForm.errors.attachments }}
                                        </p>

                                        <!-- File List -->
                                        <div v-if="hasAttachments" class="mt-3 space-y-2">
                                            <div v-for="(file, index) in replyForm.attachments" :key="index"
                                                class="flex items-center justify-between rounded-lg border border-gray-200 bg-white px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                                                <div class="flex items-center gap-2">
                                                    <component :is="getFileIconComponent(file.type)" class="h-5 w-5 text-gray-500 dark:text-gray-400" />
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
                                                {{ replyForm.attachments.length }} file(s) attached
                                            </span>
                                        </div>
                                        <button type="submit" :disabled="replyForm.processing"
                                            class="inline-flex min-w-[120px] items-center justify-center rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 disabled:opacity-60 dark:bg-blue-600 dark:hover:bg-blue-700"
                                            :class="{ 'bg-yellow-600 hover:bg-yellow-700 dark:bg-yellow-600 dark:hover:bg-yellow-700': replyForm.is_internal }">
                                            <span v-if="replyForm.processing" class="flex items-center gap-2">
                                                <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                                Sending...
                                            </span>
                                            <span v-else class="flex items-center gap-2">
                                                <Send class="h-4 w-4" />
                                                {{ replyForm.is_internal ? 'Add Note' : 'Send Reply' }}
                                            </span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Sidebar - Gunakan ticketData -->
                <div class="lg:col-span-4 space-y-6">
                    <!-- Ticket Information -->
                    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-900">
                        <div class="border-b border-gray-100 bg-gray-50/50 px-4 py-3 dark:border-gray-800 dark:bg-gray-900/50">
                            <div class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-white">
                                <Info class="h-4 w-4 text-gray-500 dark:text-gray-400" />
                                Ticket Information
                            </div>
                        </div>
                        <div class="space-y-4 p-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</p>
                                    <span :class="[getStatusColor(ticketData?.status || 'open'), 'mt-1 inline-block rounded-full px-3 py-1 text-xs font-medium capitalize flex items-center gap-1']">
                                        <component :is="getStatusBadgeIcon(ticketData?.status || 'open')" class="h-3 w-3" />
                                        {{ ticketData?.status?.replace('_', ' ') || 'Open' }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Priority</p>
                                    <span :class="[getPriorityColor(ticketData?.priority?.name || 'low'), 'mt-1 inline-block rounded-full px-3 py-1 text-xs font-medium capitalize']">
                                        {{ ticketData?.priority?.name || 'Low' }}
                                    </span>
                                </div>
                            </div>

                            <hr class="border-gray-200 dark:border-gray-700" />

                            <div class="space-y-3">
                                <div class="flex items-center gap-2">
                                    <Hash class="h-4 w-4 text-gray-400" />
                                    <span class="font-mono text-sm text-gray-600 dark:text-gray-300">
                                        {{ ticketData?.code || 'N/A' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Tag class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ ticketData?.category?.name || 'Uncategorized' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <User class="h-4 w-4 text-gray-400" />
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ ticketData?.client?.name || 'Unknown' }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ ticketData?.client?.email || '' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <UserCircle class="h-4 w-4 text-gray-400" />
                                    <div>
                                        <p class="text-sm text-gray-600 dark:text-gray-300">
                                            {{ ticketData?.assigned_technician?.name || 'Unassigned' }}
                                        </p>
                                        <p v-if="ticketData?.assigned_technician" class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ ticketData.assigned_technician.email }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Calendar class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        {{ formatDate(ticketData?.created_at) }}
                                    </span>
                                </div>
                                <div v-if="ticketData?.sla_due" class="flex items-center gap-2">
                                    <Clock class="h-4 w-4 text-gray-400" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        SLA Due: {{ formatDate(ticketData.sla_due) }}
                                    </span>
                                </div>
                                <div v-if="ticketData?.resolved_at" class="flex items-center gap-2">
                                    <CheckCircle class="h-4 w-4 text-green-500" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        Resolved: {{ formatDate(ticketData.resolved_at) }}
                                    </span>
                                </div>
                                <div v-if="ticketData?.closed_at" class="flex items-center gap-2">
                                    <XCircle class="h-4 w-4 text-gray-500" />
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        Closed: {{ formatDate(ticketData.closed_at) }}
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