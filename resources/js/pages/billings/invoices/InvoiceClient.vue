<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

import {
    Search,
    Eye,
    Receipt,
} from 'lucide-vue-next';

import {
    ref,
    watch,
} from 'vue';

/**
 * =========================================
 * PROPS
 * =========================================
 */
const props = defineProps<{
    title: string;
    invoices: any;
    filters: {
        search: string;
        status: string;
    };
}>();

/**
 * =========================================
 * FILTERS
 * =========================================
 */
const search = ref(
    props.filters.search || ''
);

const status = ref(
    props.filters.status || ''
);

/**
 * =========================================
 * WATCH FILTER
 * =========================================
 */
watch(
    [search, status],
    () => {

        router.get(
            '/client/invoices',
            {

                search: search.value,

                status: status.value,
            },
            {

                preserveState: true,

                replace: true,
            }
        );
    }
);

/**
 * =========================================
 * FORMAT RUPIAH
 * =========================================
 */
const formatRupiah = (
    value: number
) => {

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
const statusColor = (
    status: string
) => {

    switch (status) {

        case 'paid':
            return 'bg-green-100 text-green-700 border-green-200';

        case 'pending':
            return 'bg-yellow-100 text-yellow-700 border-yellow-200';

        case 'overdue':
            return 'bg-red-100 text-red-700 border-red-200';

        case 'sent':
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
                href: '/client/invoices',
            },
        ],
    },
});
</script>

<template>

    <Head :title="title" />

    <div class="flex flex-col gap-5 p-4">

        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">

            <div>

                <div class="flex items-center gap-2">

                    <h1 class="text-2xl font-bold">
                        {{ title }}
                    </h1>

                    <span class="rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary">
                        {{ invoices.total }}
                    </span>

                </div>

                <p class="text-sm text-muted-foreground">
                    Manage and monitor all invoices
                </p>

            </div>
        </div>

        <!-- Filters -->
        <div class="flex flex-col gap-3 md:flex-row">

            <!-- Search -->
            <div class="relative w-full">

                <input v-model="search" type="text" placeholder="Search invoice..."
                    class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent" />

                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

            </div>

            <!-- Status -->
            <select v-model="status"
                class="rounded-xl border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent">

                <option value="">
                    All Status
                </option>

                <option value="sent">
                    Await Payment
                </option>

                <option value="paid">
                    Paid
                </option>

                <option value="overdue">
                    Overdue
                </option>

                <option value="cancelled">
                    Cancelled
                </option>

            </select>

        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:bg-transparent">

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-muted/40">

                        <tr class="text-left text-sm">

                            <th class="px-5 py-3">
                                Invoice
                            </th>

                            <th class="px-5 py-3">
                                Client
                            </th>

                            <th class="px-5 py-3">
                                Issue Date
                            </th>

                            <th class="px-5 py-3">
                                Due Date
                            </th>

                            <th class="px-5 py-3">
                                Total
                            </th>

                            <th class="px-5 py-3">
                                Status
                            </th>

                            <th class="px-5 py-3">
                                Actions
                            </th>

                        </tr>
                    </thead>

                    <tbody>

                        <tr v-for="invoice in invoices.data" :key="invoice.id" class="border-t">

                            <!-- Invoice -->
                            <td class="px-5 py-4">

                                <div class="flex items-center gap-3">

                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                        <Receipt class="h-5 w-5 text-primary" />
                                    </div>

                                    <div>

                                        <div class="font-semibold">
                                            {{ invoice.invoice_number }}
                                        </div>

                                        <div class="text-sm text-muted-foreground">
                                            {{ invoice.service?.name }}
                                        </div>

                                    </div>

                                </div>

                            </td>

                            <!-- Client -->
                            <td class="px-5 py-4">

                                <div class="font-medium">
                                    {{ invoice.client?.company_name }}
                                </div>

                            </td>

                            <!-- Issue -->
                            <td class="px-5 py-4">
                                {{ invoice.issue_date }}
                            </td>

                            <!-- Due -->
                            <td class="px-5 py-4">
                                {{ invoice.due_date }}
                            </td>

                            <!-- Total -->
                            <td class="px-5 py-4 font-semibold">
                                {{ formatRupiah(invoice.total) }}
                            </td>

                            <!-- Status -->
                            <td class="px-5 py-4">

                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium"
                                    :class="statusColor(invoice.status)">
                                    {{ invoice.status === 'sent' ? 'Awaiting Payment' : invoice.status }}
                                </span>

                            </td>

                            <!-- Actions -->
                            <td class="px-5 py-4">

                                <Link :href="`/client/invoices/${invoice.id}`"
                                    class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-muted">
                                    <Eye class="mr-2 h-4 w-4" />
                                    {{ invoice.status === 'sent' ? 'Payment' : 'View' }}
                                </Link>

                            </td>

                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between">

            <div class="text-sm text-muted-foreground">
                Showing
                {{ invoices.from }}
                to
                {{ invoices.to }}
                of
                {{ invoices.total }}
                results
            </div>

            <div class="flex gap-2">

                <Link v-if="invoices.prev_page_url" :href="invoices.prev_page_url"
                    class="rounded-lg border px-4 py-2 text-sm hover:bg-muted">
                    Previous
                </Link>

                <Link v-if="invoices.next_page_url" :href="invoices.next_page_url"
                    class="rounded-lg border px-4 py-2 text-sm hover:bg-muted">
                    Next
                </Link>

            </div>

        </div>

    </div>
</template>