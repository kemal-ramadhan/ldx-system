<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue'
import { Upload, X } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import ImageLightbox from './ImageLightbox.vue'

const props = withDefaults(defineProps<{
    multiple?: boolean
    maxFiles?: number
}>(), {
    multiple: true,
    maxFiles: 10,
})

const files = defineModel<File[]>({ default: () => [] })

const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp']
const MAX_FILE_SIZE = 5 * 1024 * 1024

const isDragging = ref(false)
const fileInputRef = ref<HTMLInputElement | null>(null)
const previews = ref<{ file: File, url: string }[]>([])

const lightboxOpen = ref(false)
const lightboxIndex = ref(0)

// keep previews in sync with the model (e.g. when the parent resets the form)
watch(files, (list) => {
    previews.value
        .filter(p => !list.includes(p.file))
        .forEach(p => URL.revokeObjectURL(p.url))

    previews.value = list.map(file =>
        previews.value.find(p => p.file === file) ?? { file, url: URL.createObjectURL(file) }
    )
}, { immediate: true })

onBeforeUnmount(() => {
    previews.value.forEach(p => URL.revokeObjectURL(p.url))
})

const formatSize = (bytes: number) => {
    if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`
    return `${(bytes / 1024 / 1024).toFixed(1)} MB`
}

const addFiles = (incoming: File[]) => {
    const valid = incoming.filter(f => {
        if (!ALLOWED_TYPES.includes(f.type)) {
            toast.error(`${f.name}: format harus JPG, PNG atau WEBP`)
            return false
        }
        if (f.size > MAX_FILE_SIZE) {
            toast.error(`${f.name}: ukuran maksimal 5MB`)
            return false
        }
        return true
    })

    if (!props.multiple) {
        files.value = valid.slice(0, 1)
        return
    }

    const merged = [...files.value, ...valid]
    if (merged.length > props.maxFiles) {
        toast.error(`Maksimal ${props.maxFiles} gambar sekali upload`)
    }
    files.value = merged.slice(0, props.maxFiles)
}

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement
    addFiles(Array.from(target.files || []))
    target.value = ''
}

const handleDrop = (e: DragEvent) => {
    e.preventDefault()
    isDragging.value = false
    addFiles(Array.from(e.dataTransfer?.files || []))
}

const removeFile = (index: number) => {
    files.value = files.value.filter((_, i) => i !== index)
}

const openPreview = (index: number) => {
    lightboxIndex.value = index
    lightboxOpen.value = true
}
</script>

<template>
    <div class="flex flex-col gap-3">
        <div
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop="handleDrop"
            class="relative rounded-xl border-2 border-dashed p-5 text-center transition-colors"
            :class="[
                isDragging
                    ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-900/20'
                    : 'border-gray-300 hover:border-gray-400 dark:border-gray-600'
            ]"
        >
            <input
                ref="fileInputRef"
                type="file"
                :multiple="multiple"
                @change="handleFileSelect"
                class="absolute inset-0 cursor-pointer opacity-0"
                accept=".png,.jpg,.jpeg,.webp"
            />
            <div class="flex flex-col items-center gap-1">
                <Upload class="h-6 w-6 text-gray-400" />
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Drop gambar di sini atau <span class="text-blue-600 dark:text-blue-400">browse</span>
                </p>
                <p class="text-xs text-gray-400">
                    JPG, PNG, WEBP · maks 5MB<span v-if="multiple"> · maks {{ maxFiles }} gambar</span>
                </p>
            </div>
        </div>

        <div v-if="previews.length" class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4">
            <div
                v-for="(p, i) in previews"
                :key="p.url"
                class="group relative overflow-hidden rounded-xl border bg-gray-50 dark:border-gray-700 dark:bg-gray-800"
            >
                <button type="button" class="block w-full cursor-zoom-in" @click="openPreview(i)">
                    <img :src="p.url" :alt="p.file.name" class="aspect-video w-full object-cover" />
                </button>
                <span class="absolute left-2 top-2 rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-semibold text-white shadow">
                    Baru
                </span>
                <button
                    type="button"
                    @click="removeFile(i)"
                    class="absolute right-2 top-2 rounded-full bg-white/90 p-1 text-red-500 shadow hover:bg-white cursor-pointer"
                    title="Hapus dari daftar"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
                <div class="px-2.5 py-1.5">
                    <p class="truncate text-xs font-medium text-gray-700 dark:text-gray-200">{{ p.file.name }}</p>
                    <p class="text-[11px] text-gray-400">{{ formatSize(p.file.size) }}</p>
                </div>
            </div>
        </div>

        <ImageLightbox
            v-model:open="lightboxOpen"
            :images="previews.map(p => ({ url: p.url, file_name: p.file.name }))"
            :start-index="lightboxIndex"
        />
    </div>
</template>
