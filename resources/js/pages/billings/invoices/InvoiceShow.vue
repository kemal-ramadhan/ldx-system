```vue
<script setup lang="ts">
import { formatRupiah as formatMoneyRp } from '@/lib/money';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatItemTax } from '@/lib/tax';
import { onMounted, ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

import {
    ArrowLeft,
    Receipt,
    Building2,
    CalendarDays,
    CreditCard,
    Wallet,
    Mail,
    Download,
    CheckCircle2,
    Trash2,
    Pencil,
} from 'lucide-vue-next';
import DeleteInvoiceDialog from '@/components/vanila/invoice/DeleteInvoiceDialog.vue';

/**
 * =========================================
 * PROPS
 * =========================================
 */
const props = defineProps<{
    title: string;
    invoice: any;
    can_edit?: boolean;
}>();

/**
 * =========================================
 * FORMAT RUPIAH
 * =========================================
 */
const formatRupiah = (value: number) => {

    return formatMoneyRp(value || 0);
};

/**
 * =========================================
 * STATUS COLOR
 * =========================================
 */
const statusColor = (status: string) => {

    switch (status) {

        case 'paid':
            return 'bg-green-100 text-green-700 border-green-200';

        case 'pending':
            return 'bg-yellow-100 text-yellow-700 border-yellow-200';

        case 'overdue':
            return 'bg-red-100 text-red-700 border-red-200';

        case 'cancelled':
            return 'bg-gray-100 text-gray-700 border-gray-200';

        default:
            return 'bg-blue-100 text-blue-700 border-blue-200';
    }
};

/**
 * =========================================
 * GENERATED POPUP (from Service > Generate Invoice)
 * =========================================
 */
const showGeneratedDialog = ref(false);
const openDeleteDialog = ref(false);

/**
 * =========================================
 * EDITED POPUP (from Edit Invoice > Save)
 * =========================================
 * Ask whether the revised invoice should be resent to the client.
 */
const showEditedDialog = ref(false);
const resending = ref(false);

const resendRevisedInvoice = () => {
    router.post(
        `/admin/invoices/${props.invoice.id}/send`,
        {},
        {
            preserveScroll: true,
            onStart: () => (resending.value = true),
            onFinish: () => {
                resending.value = false;
                showEditedDialog.value = false;
            },
        },
    );
};

onMounted(() => {
    const url = new URL(window.location.href);

    if (url.searchParams.has('generated')) {
        showGeneratedDialog.value = true;
    }

    if (url.searchParams.has('edited')) {
        showEditedDialog.value = true;
    }

    // remove the flags so a refresh doesn't show the popup again
    if (url.searchParams.has('generated') || url.searchParams.has('edited')) {
        url.searchParams.delete('generated');
        url.searchParams.delete('edited');
        window.history.replaceState(window.history.state, '', url.toString());
    }
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Invoices',
                href: '/admin/invoices',
            },
            {
                title: 'Invoice Detail',
                href: '#',
            },
        ],
    },
});

/**
 * =========================================
 * SEND INVOICE EMAIL
 * =========================================
 */
const sendInvoice = () => {
    if (
        confirm(
            'Send invoice email to client?'
        )
    ) {

        router.post(
            `/admin/invoices/${props.invoice.id}/send`
        );
    }
};
</script>

<template>

    <Head :title="title" />

    <div class="flex flex-col gap-5 p-4">

        <!-- Header -->
        <div class="flex flex-col gap-4 rounded-2xl border bg-white p-6 shadow-sm dark:bg-transparent">

            <div class="flex flex-wrap items-start justify-between gap-4">

                <div class="flex items-center gap-3">

                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10">
                        <Receipt class="h-7 w-7 text-primary" />
                    </div>

                    <div>
                        <h1 class="text-2xl font-bold">
                            {{ invoice.invoice_number }}
                        </h1>

                        <p class="text-sm text-muted-foreground">
                            Invoice Detail
                        </p>
                    </div>
                </div>

                <div class="inline-flex items-center rounded-full border px-4 py-2 text-sm font-medium"
                    :class="statusColor(invoice.status)">
                    {{ invoice.status }}
                </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-wrap gap-2">

                <Link href="/admin/invoices"
                    class="inline-flex items-center rounded-lg border px-4 py-2 text-sm hover:bg-muted">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Back
                </Link>

                <Link 
                    :href="`/admin/invoices/${invoice.id}/payment`"
                    class="inline-flex items-center rounded-lg bg-primary px-4
                    py-2 text-sm text-white hover:bg-primary/90 dark:text-gray-900"
                >
                    <CreditCard class="mr-2 h-4 w-4" />

                    {{ ['waiting', 'processed', 'paid'].includes(invoice.status) 
                        ? 'See Payment' 
                        : 'Mark as Paid' 
                    }}
                </Link>

                <!-- Edit Invoice (not for paid / waiting) -->
                <Link
                    v-if="can_edit"
                    :href="`/admin/invoices/${invoice.id}/edit`"
                    class="inline-flex items-center rounded-lg border px-4 py-2 text-sm hover:bg-muted"
                >
                    <Pencil class="mr-2 h-4 w-4" />
                    Edit
                </Link>

                <!-- Send Invoice Email -->
                <button
                    v-if="['sent', 'pending', 'overdue'].includes(invoice.status)"
                    @click="sendInvoice"
                    class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-4 py-2 text-sm text-blue-700 hover:bg-blue-100"
                >
                    <Mail class="mr-2 h-4 w-4" />
                    Send Invoice
                </button>

                <a
                    :href="`/admin/invoices/${invoice.id}/download`"
                    class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm text-white hover:bg-green-700"
                >
                    <Download class="mr-2 h-4 w-4" />

                    Download Invoice
                </a>

                <!-- Delete Invoice (not for paid) -->
                <button
                    v-if="invoice.status !== 'paid'"
                    @click="openDeleteDialog = true"
                    class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700 hover:bg-red-100"
                >
                    <Trash2 class="mr-2 h-4 w-4" />
                    Delete
                </button>

            </div>
        </div>

        <!-- Info -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

            <!-- Client -->
            <div class="rounded-2xl border bg-white p-5 shadow-sm dark:bg-transparent">

                <div class="mb-4 flex items-center gap-2">
                    <Building2 class="h-5 w-5 text-primary" />
                    <h2 class="font-semibold">
                        Client Information
                    </h2>
                </div>

                <div class="space-y-3 text-sm">

                    <div>
                        <p class="text-muted-foreground">
                            Company Name
                        </p>

                        <p class="font-medium">
                            {{ invoice.client?.company_name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-muted-foreground">
                            Email
                        </p>

                        <p class="font-medium">
                            {{ invoice.client?.company_email }}
                        </p>
                    </div>

                    <div>
                        <p class="text-muted-foreground">
                            Phone
                        </p>

                        <p class="font-medium">
                            {{ invoice.client?.company_phone }}
                        </p>
                    </div>

                </div>
            </div>

            <!-- Invoice Info -->
            <div class="rounded-2xl border bg-white p-5 shadow-sm dark:bg-transparent">

                <div class="mb-4 flex items-center gap-2">
                    <CalendarDays class="h-5 w-5 text-primary" />
                    <h2 class="font-semibold">
                        Invoice Information
                    </h2>
                </div>

                <div class="space-y-3 text-sm">

                    <div>
                        <p class="text-muted-foreground">
                            Issue Date
                        </p>

                        <p class="font-medium">
                            {{ invoice.issue_date }}
                        </p>
                    </div>

                    <div>
                        <p class="text-muted-foreground">
                            Due Date
                        </p>

                        <p class="font-medium">
                            {{ invoice.due_date }}
                        </p>
                    </div>

                    <div>
                        <p class="text-muted-foreground">
                            Payment Method
                        </p>

                        <p class="font-medium">
                            {{ invoice.payment_method || '-' }}
                        </p>
                    </div>

                </div>
            </div>

            <!-- Summary -->
            <div class="rounded-2xl border bg-white p-5 shadow-sm dark:bg-transparent">

                <div class="mb-4 flex items-center gap-2">
                    <Wallet class="h-5 w-5 text-primary" />
                    <h2 class="font-semibold">
                        Billing Summary
                    </h2>
                </div>

                <div class="space-y-3 text-sm">

                    <div class="flex justify-between">
                        <span class="text-muted-foreground">
                            Subtotal
                        </span>

                        <span class="font-medium">
                            {{ formatRupiah(invoice.subtotal) }}
                        </span>
                    </div>

                    <div v-if="Number(invoice.ppn_amount) > 0" class="flex justify-between">
                        <span class="text-muted-foreground">
                            PPN
                        </span>

                        <span class="font-medium">
                            {{ formatRupiah(invoice.ppn_amount) }}
                        </span>
                    </div>

                    <div v-if="Number(invoice.pph23_amount) > 0" class="flex justify-between">
                        <span class="text-muted-foreground">
                            PPh 23
                        </span>

                        <span class="font-medium">
                            - {{ formatRupiah(invoice.pph23_amount) }}
                        </span>
                    </div>

                    <div class="border-t pt-3 flex justify-between">
                        <span class="font-semibold">
                            Total
                        </span>

                        <span class="font-bold text-primary">
                            {{ formatRupiah(invoice.total) }}
                        </span>
                    </div>

                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:bg-transparent">

            <div class="border-b px-5 py-4">
                <h2 class="font-semibold">
                    Invoice Items
                </h2>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-muted/40">

                        <tr class="text-left text-sm">

                            <th class="px-5 py-3">
                                Item
                            </th>

                            <th class="px-5 py-3">
                                Quantity
                            </th>

                            <th class="px-5 py-3">
                                Price
                            </th>

                            <th class="px-5 py-3">
                                PPN
                            </th>

                            <th class="px-5 py-3">
                                PPh 23
                            </th>

                            <th class="px-5 py-3">
                                Total
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr v-for="item in invoice.items" :key="item.id" class="border-t">

                            <td class="px-5 py-4">

                                <div class="font-medium">
                                    {{ item.name }}
                                </div>

                                <div class="text-sm text-muted-foreground">
                                    {{ item.description }}
                                </div>

                            </td>

                            <td class="px-5 py-4">
                                {{ item.quantity }}
                            </td>

                            <td class="px-5 py-4">
                                {{ formatRupiah(item.price) }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ formatItemTax(item.ppn_enabled, item.ppn_percentage, item.ppn_amount) }}
                            </td>

                            <td class="px-5 py-4 whitespace-nowrap">
                                {{ formatItemTax(item.pph23_enabled, item.pph23_percentage, item.pph23_amount) }}
                            </td>

                            <td class="px-5 py-4 font-semibold">
                                {{ formatRupiah(item.total) }}
                            </td>

                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <DeleteInvoiceDialog v-model:open="openDeleteDialog" :invoice="invoice" />

    <!-- INVOICE GENERATED POPUP -->
    <Dialog v-model:open="showGeneratedDialog">
        <DialogContent class="sm:max-w-md">
            <div class="flex flex-col items-center gap-3 pt-2 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 dark:bg-green-950/40">
                    <CheckCircle2 class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>
                <DialogHeader class="items-center space-y-2">
                    <DialogTitle class="text-center">
                        Invoice berhasil dibuat
                    </DialogTitle>
                    <DialogDescription class="text-center">
                        Invoice <strong>{{ invoice.invoice_number }}</strong> sudah berstatus <strong>Sent</strong>
                        dan sedang dikirim ke email client.
                    </DialogDescription>
                </DialogHeader>
            </div>

            <div class="grid gap-2 rounded-lg border p-4 text-sm dark:border-gray-700">
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Client</span>
                    <span class="font-medium text-right">{{ invoice.client?.company_name || '-' }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Email</span>
                    <span class="inline-flex items-center gap-1 font-medium text-right">
                        <Mail class="h-3.5 w-3.5 text-muted-foreground" />
                        {{ invoice.client?.company_email || '-' }}
                    </span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Due Date</span>
                    <span class="font-medium">{{ invoice.due_date || '-' }}</span>
                </div>
                <div class="flex justify-between gap-4 border-t pt-2 dark:border-gray-700">
                    <span class="text-muted-foreground">Total</span>
                    <span class="font-semibold">{{ formatRupiah(invoice.total) }}</span>
                </div>
            </div>

            <DialogFooter>
                <Button class="w-full bg-primary text-white hover:bg-primary/90 dark:text-gray-900"
                    @click="showGeneratedDialog = false">
                    OK
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- Edited: resend to client? -->
    <Dialog v-model:open="showEditedDialog">
        <DialogContent class="sm:max-w-md">
            <div class="flex flex-col items-center gap-3 pt-2 text-center">
                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 dark:bg-green-950/40">
                    <CheckCircle2 class="h-8 w-8 text-green-600 dark:text-green-400" />
                </div>
                <DialogHeader class="items-center space-y-2">
                    <DialogTitle class="text-center">
                        Invoice berhasil diperbarui
                    </DialogTitle>
                    <DialogDescription class="text-center">
                        Perubahan pada invoice <strong>{{ invoice.invoice_number }}</strong> sudah disimpan.
                        Kirim ulang invoice revisi ke client supaya client mengetahui perubahannya?
                    </DialogDescription>
                </DialogHeader>
            </div>

            <div class="grid gap-2 rounded-lg border p-4 text-sm">
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Client</span>
                    <span class="font-medium text-right">{{ invoice.client?.company_name || '-' }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Email</span>
                    <span class="inline-flex items-center gap-1 font-medium text-right">
                        <Mail class="h-3.5 w-3.5 text-muted-foreground" />
                        {{ invoice.client?.company_email || '-' }}
                    </span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Due Date</span>
                    <span class="font-medium">{{ invoice.due_date || '-' }}</span>
                </div>
                <div class="flex justify-between gap-4 border-t pt-2">
                    <span class="text-muted-foreground">Total Baru</span>
                    <span class="font-semibold">{{ formatRupiah(invoice.total) }}</span>
                </div>
            </div>

            <p v-if="!invoice.client?.company_email"
                class="rounded-lg bg-yellow-50 px-3 py-2 text-xs text-yellow-800 dark:bg-yellow-950/40 dark:text-yellow-300">
                Client belum memiliki email, invoice tidak bisa dikirim. Lengkapi email client terlebih dahulu.
            </p>

            <DialogFooter class="gap-2 sm:gap-2">
                <Button variant="outline" :disabled="resending" @click="showEditedDialog = false">
                    Nanti Saja
                </Button>
                <Button :disabled="resending || !invoice.client?.company_email" @click="resendRevisedInvoice">
                    <Mail class="mr-2 h-4 w-4" />
                    {{ resending ? 'Mengirim...' : 'Kirim Ulang ke Client' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>