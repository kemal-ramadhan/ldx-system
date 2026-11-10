<script setup lang="ts">
import { formatRupiah as formatMoneyRp } from '@/lib/money';
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
    invoice: any | null
}>()

const open = defineModel<boolean>('open', { default: false })

const isDeleting = ref(false)

const formatRupiah = (value: number) =>
    formatMoneyRp(value || 0)

const confirmDelete = () => {
    if (!props.invoice) return

    router.delete(`/admin/invoices/${props.invoice.id}`, {
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
                    Hapus invoice ini?
                </DialogTitle>
                <DialogDescription>
                    Invoice <strong>{{ invoice?.invoice_number }}</strong> akan dihapus permanen.
                </DialogDescription>
            </DialogHeader>

            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-300">
                <p class="font-medium">Perhatian:</p>
                <ul class="mt-1 list-disc space-y-1 pl-5">
                    <li>Invoice juga akan <strong>hilang dari halaman client</strong>.</li>
                    <li>Item invoice dan data pembayaran (termasuk bukti transfer) ikut terhapus.</li>
                    <li>Tindakan ini tidak dapat dibatalkan.</li>
                </ul>
            </div>

            <div v-if="invoice" class="grid gap-2 rounded-lg border p-4 text-sm dark:border-gray-700">
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Client</span>
                    <span class="font-medium text-right">{{ invoice.client?.company_name || '-' }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Status</span>
                    <span class="font-medium capitalize">{{ invoice.status }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Total</span>
                    <span class="font-semibold">{{ formatRupiah(invoice.total) }}</span>
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
