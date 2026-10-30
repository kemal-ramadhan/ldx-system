<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { AlertTriangle } from 'lucide-vue-next'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Spinner } from '@/components/ui/spinner'

const props = defineProps<{
    service: any | null
}>()

const open = defineModel<boolean>('open', { default: false })

const isDeleting = ref(false)

const confirmDelete = () => {
    if (!props.service) return

    router.delete(`/admin/services/${props.service.id}`, {
        preserveScroll: true,
        onStart: () => (isDeleting.value = true),
        onFinish: () => {
            isDeleting.value = false
            open.value = false
        },
    })
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => { if (!isDeleting) open = val }">
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle class="flex items-center gap-2">
                    <AlertTriangle class="h-5 w-5 text-red-500" />
                    Hapus service ini?
                </DialogTitle>
                <DialogDescription>
                    Service <strong>{{ service?.name }}</strong> ({{ service?.code }}) akan dihapus dari daftar service.
                </DialogDescription>
            </DialogHeader>

            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">
                <ul class="list-disc space-y-1 pl-5">
                    <li>Service tidak akan tampil lagi di halaman admin maupun client.</li>
                    <li>
                        <template v-if="service?.invoices_count">
                            <strong>{{ service.invoices_count }} invoice</strong> terkait tetap tersimpan dan tetap bisa dilihat untuk keperluan laporan.
                        </template>
                        <template v-else>
                            Service ini belum memiliki invoice.
                        </template>
                    </li>
                    <li>Invoice baru tidak bisa di-generate lagi untuk service ini.</li>
                </ul>
            </div>

            <div v-if="service" class="grid gap-2 rounded-lg border p-4 text-sm dark:border-gray-700">
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Client</span>
                    <span class="font-medium text-right">{{ service.client?.company_name || '-' }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Status</span>
                    <span class="font-medium capitalize">{{ service.status }}</span>
                </div>
            </div>

            <DialogFooter class="gap-2">
                <Button variant="secondary" :disabled="isDeleting" @click="open = false">Batal</Button>
                <Button variant="destructive" :disabled="isDeleting" @click="confirmDelete">
                    <Spinner v-if="isDeleting" class="mr-2 h-4 w-4" />
                    Hapus
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
