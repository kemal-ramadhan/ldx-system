```vue
<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

import {
    ArrowLeft,
    Receipt,
    Building2,
    CalendarDays,
    CreditCard,
    Wallet,
    Mail,
    Download
} from 'lucide-vue-next';

/**
 * =========================================
 * PROPS
 * =========================================
 */
const props = defineProps<{
    title: string;
    invoice: any;
}>();

/**
 * =========================================
 * FORMAT RUPIAH
 * =========================================
 */
const formatRupiah = (value: number) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
        }
    ).format(value || 0);
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

                <!-- Send Invoice Email -->
                <button
                    v-if="['sent', 'pending'].includes(invoice.status)"
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

                    <div class="flex justify-between">
                        <span class="text-muted-foreground">
                            PPn ({{ invoice.ppn_percentage }}%)
                        </span>

                        <span class="font-medium">
                            {{ formatRupiah(invoice.ppn_amount) }}
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">
                            PPh23 ({{ invoice.pph23_percentage }}%)
                        </span>

                        <span class="font-medium">
                            {{ formatRupiah(invoice.pph23_amount) }}
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

                            <td class="px-5 py-4 font-semibold">
                                {{ formatRupiah(item.total) }}
                            </td>

                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

    </div>
</template>