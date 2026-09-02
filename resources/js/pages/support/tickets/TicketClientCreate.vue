<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { 
    X, 
    Upload, 
    Paperclip, 
    AlertCircle,
    FileText,
    Image,
    File
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    title: string;
    categories: any[];
    priorities: any[];
}>();

const form = useForm({
    subject: '',
    category_id: '',
    priority_id: '',
    description: '',
    attachments: [] as File[],
});

const isDragging = ref(false);
const fileInputRef = ref<HTMLInputElement | null>(null);

const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const ALLOWED_TYPES = [
    'image/png', 
    'image/jpeg', 
    'image/jpg',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
];

const createTicket = () => {
    form.post('/client/tickets', {
        forceFormData: true,
    });
};

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

const getFileIcon = (file: File) => {
    if (file.type.startsWith('image/')) return Image;
    if (file.type === 'application/pdf') return FileText;
    return File;
};

const formatFileSize = (bytes: number) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const hasErrors = computed(() => {
    return Object.keys(form.errors).length > 0;
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Add a new ticket',
                href: '/client/tickets',
            },
        ],
    },
});
</script>

<template>
    <Head :title="title" />
    
    <div class="p-6">
        <!-- Header -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                Create Ticket
            </h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Describe your problem clearly so our support team can help you faster.
            </p>
        </div>

        <form @submit.prevent="createTicket" class="space-y-6">
            <!-- Main Form Card -->
            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="space-y-6 p-8">
                    <!-- Subject -->
                    <div>
                        <Label for="subject" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Subject <span class="text-red-500">*</span>
                        </Label>
                        <Input
                            id="subject"
                            v-model="form.subject"
                            type="text"
                            placeholder="Example: Cannot login to dashboard"
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.subject }"
                        />
                        <p v-if="form.errors.subject" class="mt-2 flex items-center gap-1 text-sm text-red-500">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.subject }}
                        </p>
                    </div>

                    <!-- Category & Priority -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <!-- Category -->
                        <div>
                            <Label for="category" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Category <span class="text-red-500">*</span>
                            </Label>
                            <select
                                id="category"
                                v-model="form.category_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                :class="{ 'border-red-500': form.errors.category_id }"
                            >
                                <option value="">Select Category</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.category_id" class="mt-2 flex items-center gap-1 text-sm text-red-500">
                                <AlertCircle class="h-4 w-4" />
                                {{ form.errors.category_id }}
                            </p>
                        </div>

                        <!-- Priority -->
                        <div>
                            <Label for="priority" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Priority <span class="text-red-500">*</span>
                            </Label>
                            <select
                                id="priority"
                                v-model="form.priority_id"
                                class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                                :class="{ 'border-red-500': form.errors.priority_id }"
                            >
                                <option value="">Select Priority</option>
                                <option v-for="priority in priorities" :key="priority.id" :value="priority.id">
                                    {{ priority.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.priority_id" class="mt-2 flex items-center gap-1 text-sm text-red-500">
                                <AlertCircle class="h-4 w-4" />
                                {{ form.errors.priority_id }}
                            </p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <Label for="description" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Description <span class="text-red-500">*</span>
                        </Label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="8"
                            placeholder="Please explain your problem in detail..."
                            class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-primary focus:ring-2 focus:ring-primary/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                            :class="{ 'border-red-500': form.errors.description }"
                        />
                        <p v-if="form.errors.description" class="mt-2 flex items-center gap-1 text-sm text-red-500">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.description }}
                        </p>
                    </div>

                    <!-- Attachments -->
                    <div>
                        <Label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Attachments
                            <span class="ml-1 text-xs font-normal text-gray-500">
                                (Optional, max 10MB each)
                            </span>
                        </Label>

                        <!-- Drop Zone -->
                        <div
                            @dragover.prevent="isDragging = true"
                            @dragleave.prevent="isDragging = false"
                            @drop="handleDrop"
                            class="relative rounded-xl border-2 border-dashed p-8 text-center transition-colors"
                            :class="[
                                isDragging 
                                    ? 'border-primary bg-primary/5' 
                                    : 'border-gray-300 hover:border-gray-400 dark:border-gray-600',
                                form.errors.attachments ? 'border-red-500' : ''
                            ]"
                        >
                            <input
                                ref="fileInputRef"
                                type="file"
                                multiple
                                @change="handleFileSelect"
                                class="absolute inset-0 cursor-pointer opacity-0"
                                accept=".png,.jpg,.jpeg,.pdf,.doc,.docx"
                            />
                            
                            <div class="flex flex-col items-center gap-2">
                                <Upload class="h-10 w-10 text-gray-400 dark:text-gray-500" />
                                <div>
                                    <p class="font-medium text-gray-700 dark:text-gray-300">
                                        Drop files here or <span class="text-primary">browse</span>
                                    </p>
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        PNG, JPG, PDF, DOC, DOCX (Max 10 MB)
                                    </p>
                                </div>
                            </div>
                        </div>

                        <p v-if="form.errors.attachments" class="mt-2 flex items-center gap-1 text-sm text-red-500">
                            <AlertCircle class="h-4 w-4" />
                            {{ form.errors.attachments }}
                        </p>

                        <!-- File List -->
                        <div v-if="form.attachments.length" class="mt-4 space-y-2">
                            <div
                                v-for="(file, index) in form.attachments"
                                :key="index"
                                class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-800"
                            >
                                <div class="flex items-center gap-3">
                                    <component 
                                        :is="getFileIcon(file)" 
                                        class="h-5 w-5 text-gray-500 dark:text-gray-400"
                                    />
                                    <div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {{ file.name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ formatFileSize(file.size) }}
                                        </p>
                                    </div>
                                </div>
                                
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    class="h-8 w-8 p-0 hover:bg-red-100 hover:text-red-600 dark:hover:bg-red-900/20"
                                    @click="removeFile(index)"
                                >
                                    <X class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 bg-gray-50 px-8 py-5 dark:border-gray-700 dark:bg-gray-800/50">
                    <Button
                        type="button"
                        variant="outline"
                        @click="$inertia.visit('/client/tickets')"
                    >
                        Cancel
                    </Button>
                    
                    <Button
                        type="submit"
                        :disabled="form.processing"
                        class="min-w-[140px]"
                    >
                        <span v-if="form.processing" class="flex items-center gap-2">
                            <span class="inline-block h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                            Submitting...
                        </span>
                        <span v-else>
                            Submit Ticket
                        </span>
                    </Button>
                </div>
            </div>
        </form>
    </div>
</template>