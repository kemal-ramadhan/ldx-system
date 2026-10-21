<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { ChevronLeft, ChevronRight, ImageOff } from 'lucide-vue-next'
import {
    Dialog,
    DialogContent,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog'

interface LightboxImage {
    id?: number
    url: string
    file_name?: string
    caption?: string | null
}

const props = withDefaults(defineProps<{
    images: LightboxImage[]
    startIndex?: number
}>(), {
    startIndex: 0,
})

const open = defineModel<boolean>('open', { default: false })

const index = ref(0)

watch(open, (val) => {
    if (val) index.value = Math.min(props.startIndex, Math.max(props.images.length - 1, 0))
})

const current = computed(() => props.images[index.value])
const hasMany = computed(() => props.images.length > 1)

const prev = () => {
    index.value = (index.value - 1 + props.images.length) % props.images.length
}

const next = () => {
    index.value = (index.value + 1) % props.images.length
}

const onKeydown = (e: KeyboardEvent) => {
    if (!hasMany.value) return
    if (e.key === 'ArrowLeft') prev()
    if (e.key === 'ArrowRight') next()
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogContent class="sm:max-w-4xl p-0 overflow-hidden gap-0" @keydown="onKeydown">
            <div class="flex items-center justify-between gap-3 border-b px-5 py-3 pr-12 dark:border-gray-800">
                <div class="min-w-0">
                    <DialogTitle class="truncate text-sm font-semibold">
                        {{ current?.caption || current?.file_name || 'Gambar Rack' }}
                    </DialogTitle>
                    <DialogDescription class="truncate text-xs">
                        {{ current?.caption ? current?.file_name : 'Preview gambar rack' }}
                    </DialogDescription>
                </div>
                <span v-if="hasMany" class="shrink-0 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ index + 1 }} / {{ images.length }}
                </span>
            </div>

            <div class="relative flex items-center justify-center bg-gray-950 min-h-[300px] max-h-[70vh]">
                <img
                    v-if="current"
                    :key="current.url"
                    :src="current.url"
                    :alt="current.file_name"
                    class="max-h-[70vh] w-auto max-w-full object-contain"
                />
                <div v-else class="flex flex-col items-center gap-2 py-16 text-gray-400">
                    <ImageOff class="h-8 w-8" />
                    <span class="text-xs">Gambar tidak tersedia</span>
                </div>

                <template v-if="hasMany">
                    <button
                        type="button"
                        @click="prev"
                        class="absolute left-3 top-1/2 -translate-y-1/2 rounded-full bg-white/80 p-2 text-gray-800 shadow hover:bg-white cursor-pointer"
                    >
                        <ChevronLeft class="h-5 w-5" />
                    </button>
                    <button
                        type="button"
                        @click="next"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full bg-white/80 p-2 text-gray-800 shadow hover:bg-white cursor-pointer"
                    >
                        <ChevronRight class="h-5 w-5" />
                    </button>
                </template>
            </div>

            <div v-if="hasMany" class="flex gap-2 overflow-x-auto border-t p-3 dark:border-gray-800">
                <button
                    v-for="(img, i) in images"
                    :key="img.id ?? img.url"
                    type="button"
                    @click="index = i"
                    :class="[
                        'h-14 w-20 shrink-0 overflow-hidden rounded-md border-2 transition-all cursor-pointer',
                        i === index ? 'border-primary' : 'border-transparent opacity-60 hover:opacity-100'
                    ]"
                >
                    <img :src="img.url" :alt="img.file_name" class="h-full w-full object-cover" />
                </button>
            </div>
        </DialogContent>
    </Dialog>
</template>
