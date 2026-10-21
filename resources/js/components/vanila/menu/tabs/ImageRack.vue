<script setup lang="ts">
import { computed, onBeforeUnmount, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { Plus, Pencil, Trash2, Eye, ImageOff, Upload, ArrowRight } from 'lucide-vue-next'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Spinner } from '@/components/ui/spinner'
import { toast } from 'vue-sonner'
import ImageUploadPreview from '@/components/vanila/rack-image/ImageUploadPreview.vue'
import ImageLightbox from '@/components/vanila/rack-image/ImageLightbox.vue'

interface RackImage {
    id: number
    url: string
    file_name: string
    file_size?: number
    caption?: string | null
    created_at?: string
    uploader?: { id: number, name: string } | null
}

const props = defineProps<{ rack: any }>()

const images = computed<RackImage[]>(() => props.rack?.images || [])

const formatDate = (date?: string) => {
    if (!date) return '-'
    return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })
}

// =========================
// LIGHTBOX
// =========================
const lightboxOpen = ref(false)
const lightboxIndex = ref(0)

const openLightbox = (index: number) => {
    lightboxIndex.value = index
    lightboxOpen.value = true
}

// =========================
// ADD IMAGES
// =========================
const openAdd = ref(false)
const addForm = useForm({ images: [] as File[] })

const submitAdd = () => {
    if (!addForm.images.length) {
        toast.error('Pilih minimal 1 gambar')
        return
    }

    addForm.post(`/admin/racks/${props.rack.id}/images`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            openAdd.value = false
            addForm.reset()
        },
    })
}

// =========================
// EDIT IMAGE
// =========================
const openEdit = ref(false)
const editingImage = ref<RackImage | null>(null)
const editForm = useForm({
    image: null as File | null,
    caption: '',
    _method: 'put',
})
const editPreviewUrl = ref<string | null>(null)

const clearEditPreview = () => {
    if (editPreviewUrl.value) URL.revokeObjectURL(editPreviewUrl.value)
    editPreviewUrl.value = null
}

const startEdit = (image: RackImage) => {
    editingImage.value = image
    editForm.clearErrors()
    editForm.image = null
    editForm.caption = image.caption || ''
    clearEditPreview()
    openEdit.value = true
}

const handleEditFile = (e: Event) => {
    const target = e.target as HTMLInputElement
    const file = target.files?.[0]
    target.value = ''
    if (!file) return

    if (!['image/jpeg', 'image/png', 'image/jpg', 'image/webp'].includes(file.type)) {
        toast.error('Format harus JPG, PNG atau WEBP')
        return
    }
    if (file.size > 5 * 1024 * 1024) {
        toast.error('Ukuran maksimal 5MB')
        return
    }

    clearEditPreview()
    editForm.image = file
    editPreviewUrl.value = URL.createObjectURL(file)
}

const cancelNewFile = () => {
    editForm.image = null
    clearEditPreview()
}

const submitEdit = () => {
    if (!editingImage.value) return

    editForm.post(`/admin/racks/images/${editingImage.value.id}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            openEdit.value = false
            clearEditPreview()
        },
    })
}

// =========================
// DELETE IMAGE
// =========================
const deleteTarget = ref<RackImage | null>(null)
const openDelete = ref(false)
const isDeleting = ref(false)

const startDelete = (image: RackImage) => {
    deleteTarget.value = image
    openDelete.value = true
}

const confirmDelete = () => {
    if (!deleteTarget.value) return

    router.delete(`/admin/racks/images/${deleteTarget.value.id}`, {
        preserveScroll: true,
        onStart: () => (isDeleting.value = true),
        onFinish: () => {
            isDeleting.value = false
            openDelete.value = false
        },
    })
}

onBeforeUnmount(clearEditPreview)
</script>

<template>
    <div class="p-4">
        <div class="w-full">

            <div class="flex justify-between items-center">
                <!-- Header -->
                <div class="mb-8">
                    <h2 class="text-lg font-medium">
                        Gambar Rack ({{ images.length }})
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Kelola foto dan dokumentasi rack ini
                    </p>
                </div>
                <div class="flex gap-2">
                    <button
                        @click="openAdd = true"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90">
                        <Plus class="w-4 h-4 mr-2" />
                        Tambah Gambar
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="!images.length"
                class="flex flex-col items-center justify-center gap-3 rounded-xl border border-dashed py-14 text-center dark:border-gray-700"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400 dark:bg-gray-800">
                    <ImageOff class="h-6 w-6" />
                </div>
                <div>
                    <p class="text-sm font-medium">Belum ada gambar</p>
                    <p class="text-xs text-muted-foreground">Tambahkan foto rack untuk dokumentasi</p>
                </div>
                <Button variant="outline" size="sm" @click="openAdd = true">
                    <Plus class="w-4 h-4 mr-1" />
                    Tambah Gambar
                </Button>
            </div>

            <!-- Gallery -->
            <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                <div
                    v-for="(img, i) in images"
                    :key="img.id"
                    class="group overflow-hidden rounded-xl border bg-white transition-shadow hover:shadow-md dark:border-gray-700 dark:bg-gray-900"
                >
                    <div class="relative">
                        <button type="button" class="block w-full cursor-zoom-in" @click="openLightbox(i)">
                            <img :src="img.url" :alt="img.file_name" class="aspect-video w-full object-cover" />
                        </button>
                        <div class="absolute right-2 top-2 flex gap-1 opacity-100 transition-opacity md:opacity-0 md:group-hover:opacity-100">
                            <button
                                type="button"
                                @click="openLightbox(i)"
                                class="rounded-md bg-white/90 p-1.5 text-blue-500 shadow hover:bg-white cursor-pointer"
                                title="Lihat"
                            >
                                <Eye class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                @click="startEdit(img)"
                                class="rounded-md bg-white/90 p-1.5 text-green-600 shadow hover:bg-white cursor-pointer"
                                title="Edit"
                            >
                                <Pencil class="h-3.5 w-3.5" />
                            </button>
                            <button
                                type="button"
                                @click="startDelete(img)"
                                class="rounded-md bg-white/90 p-1.5 text-red-500 shadow hover:bg-white cursor-pointer"
                                title="Hapus"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>
                    </div>
                    <div class="px-3 py-2.5">
                        <p class="truncate text-sm font-medium" :title="img.caption || img.file_name">
                            {{ img.caption || img.file_name }}
                        </p>
                        <p class="mt-0.5 truncate text-xs text-muted-foreground">
                            {{ img.uploader?.name || '-' }} · {{ formatDate(img.created_at) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <ImageLightbox v-model:open="lightboxOpen" :images="images" :start-index="lightboxIndex" />

        <!-- ADD DIALOG -->
        <Dialog v-model:open="openAdd">
            <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Tambah Gambar Rack</DialogTitle>
                    <DialogDescription>
                        Pilih gambar lalu cek preview-nya sebelum disimpan
                    </DialogDescription>
                </DialogHeader>

                <ImageUploadPreview v-model="addForm.images" />

                <DialogFooter class="gap-2">
                    <Button variant="secondary" type="button" @click="openAdd = false">Batal</Button>
                    <Button
                        type="button"
                        :disabled="addForm.processing || !addForm.images.length"
                        @click="submitAdd"
                        class="bg-primary text-white hover:bg-primary/90 dark:text-gray-900"
                    >
                        <Spinner v-if="addForm.processing" class="mr-2 h-4 w-4" />
                        Simpan {{ addForm.images.length ? `(${addForm.images.length})` : '' }}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <!-- EDIT DIALOG -->
        <Dialog v-model:open="openEdit">
            <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>Edit Gambar Rack</DialogTitle>
                    <DialogDescription>
                        Ganti file gambar atau caption. Preview akan muncul sebelum disimpan.
                    </DialogDescription>
                </DialogHeader>

                <form v-if="editingImage" @submit.prevent="submitEdit" class="grid gap-4">
                    <div class="grid grid-cols-1 items-center gap-3 sm:grid-cols-[1fr_auto_1fr]">
                        <div class="grid gap-1.5">
                            <span class="text-xs font-medium text-muted-foreground">Gambar saat ini</span>
                            <img
                                :src="editingImage.url"
                                :alt="editingImage.file_name"
                                :class="['aspect-video w-full rounded-lg border object-cover dark:border-gray-700', editPreviewUrl ? 'opacity-50' : '']"
                            />
                        </div>

                        <ArrowRight class="hidden h-5 w-5 text-muted-foreground sm:block" />

                        <div class="grid gap-1.5">
                            <span class="text-xs font-medium text-muted-foreground">Gambar baru</span>
                            <div v-if="editPreviewUrl" class="relative">
                                <img :src="editPreviewUrl" alt="Preview" class="aspect-video w-full rounded-lg border-2 border-emerald-400 object-cover" />
                                <span class="absolute left-2 top-2 rounded-full bg-emerald-500 px-2 py-0.5 text-[10px] font-semibold text-white shadow">
                                    Preview
                                </span>
                                <button
                                    type="button"
                                    @click="cancelNewFile"
                                    class="absolute right-2 top-2 rounded-md bg-white/90 px-2 py-0.5 text-[11px] font-medium text-red-500 shadow hover:bg-white cursor-pointer"
                                >
                                    Batal
                                </button>
                            </div>
                            <label
                                v-else
                                class="flex aspect-video w-full cursor-pointer flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-gray-300 text-center hover:border-gray-400 dark:border-gray-600"
                            >
                                <Upload class="h-5 w-5 text-gray-400" />
                                <span class="text-xs text-gray-500">Pilih gambar pengganti</span>
                                <span class="text-[11px] text-gray-400">opsional · maks 5MB</span>
                                <input type="file" accept=".png,.jpg,.jpeg,.webp" class="hidden" @change="handleEditFile" />
                            </label>
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="caption">Caption</Label>
                        <Input id="caption" v-model="editForm.caption" placeholder="Contoh: Tampak depan rack" />
                        <p v-if="editForm.errors.caption" class="text-sm text-red-600">{{ editForm.errors.caption }}</p>
                        <p v-if="editForm.errors.image" class="text-sm text-red-600">{{ editForm.errors.image }}</p>
                    </div>

                    <DialogFooter class="gap-2">
                        <Button variant="secondary" type="button" @click="openEdit = false">Batal</Button>
                        <Button
                            type="submit"
                            :disabled="editForm.processing"
                            class="bg-primary text-white hover:bg-primary/90 dark:text-gray-900"
                        >
                            <Spinner v-if="editForm.processing" class="mr-2 h-4 w-4" />
                            Simpan Perubahan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- DELETE DIALOG -->
        <Dialog v-model:open="openDelete">
            <DialogContent>
                <DialogHeader class="space-y-3">
                    <DialogTitle>Hapus gambar ini?</DialogTitle>
                    <DialogDescription>
                        Gambar <strong>{{ deleteTarget?.caption || deleteTarget?.file_name }}</strong> akan dihapus permanen dari rack ini.
                    </DialogDescription>
                </DialogHeader>
                <img
                    v-if="deleteTarget"
                    :src="deleteTarget.url"
                    :alt="deleteTarget.file_name"
                    class="aspect-video w-full rounded-lg border object-cover dark:border-gray-700"
                />
                <DialogFooter class="gap-2">
                    <Button variant="secondary" @click="openDelete = false">Batal</Button>
                    <Button variant="destructive" :disabled="isDeleting" @click="confirmDelete">
                        <Spinner v-if="isDeleting" class="mr-2 h-4 w-4" />
                        Hapus
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </div>
</template>
