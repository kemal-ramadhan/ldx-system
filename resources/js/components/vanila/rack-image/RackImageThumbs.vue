<script setup lang="ts">
import { computed, ref } from 'vue'
import { ImageOff } from 'lucide-vue-next'
import ImageLightbox from './ImageLightbox.vue'

const props = defineProps<{
    images?: { id: number, url: string, file_name?: string, caption?: string | null }[]
}>()

const MAX_VISIBLE = 3

const list = computed(() => props.images || [])
const visible = computed(() => list.value.slice(0, MAX_VISIBLE))
const remaining = computed(() => list.value.length - MAX_VISIBLE)

const lightboxOpen = ref(false)
const lightboxIndex = ref(0)

const openAt = (index: number) => {
    lightboxIndex.value = index
    lightboxOpen.value = true
}
</script>

<template>
    <div>
        <span v-if="!list.length" class="inline-flex items-center gap-1 text-xs text-muted-foreground italic">
            <ImageOff class="h-3.5 w-3.5" /> -
        </span>

        <button
            v-else-if="list.length === 1"
            type="button"
            @click="openAt(0)"
            class="block h-10 w-10 overflow-hidden rounded-md border border-gray-200 cursor-zoom-in hover:ring-2 hover:ring-primary/40 dark:border-gray-700"
        >
            <img :src="list[0].url" :alt="list[0].file_name" class="h-full w-full object-cover" />
        </button>

        <div v-else class="flex items-center -space-x-2">
            <button
                v-for="(img, i) in visible"
                :key="img.id"
                type="button"
                @click="openAt(i)"
                class="relative h-9 w-9 overflow-hidden rounded-md ring-2 ring-white transition-transform hover:z-10 hover:scale-110 cursor-zoom-in dark:ring-gray-900"
            >
                <img :src="img.url" :alt="img.file_name" class="h-full w-full object-cover" />
            </button>
            <button
                v-if="remaining > 0"
                type="button"
                @click="openAt(MAX_VISIBLE)"
                class="relative flex h-9 w-9 items-center justify-center rounded-md bg-gray-900 text-xs font-semibold text-white ring-2 ring-white cursor-pointer dark:bg-gray-700 dark:ring-gray-900"
            >
                +{{ remaining }}
            </button>
        </div>

        <ImageLightbox v-model:open="lightboxOpen" :images="list" :start-index="lightboxIndex" />
    </div>
</template>
