<script setup lang="ts">
import { formatRupiah as formatMoneyRp } from '@/lib/money';
import { Link } from '@inertiajs/vue3';
import { Eye, Receipt } from 'lucide-vue-next';

const props = defineProps<{
    invoices: any;
}>();

const formatRupiah = (value: number) => {
    return formatMoneyRp(value || 0);
};

const statusColor = (status: string) => {
    switch (status) {
        case 'paid':
            return 'bg-green-100 dark:bg-green-950/40 text-green-700 dark:text-green-300 border-green-200 dark:border-green-900/60';
        case 'pending':
            return 'bg-yellow-100 dark:bg-yellow-950/40 text-yellow-700 dark:text-yellow-300 border-yellow-200 dark:border-yellow-900/60';
        case 'overdue':
            return 'bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-300 border-red-200 dark:border-red-900/60';
        case 'cancelled':
            return 'bg-muted text-foreground border-border';
        default:
            return 'bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 border-blue-200 dark:border-blue-900/60';
    }
};
</script>

<template>
    <div>
        <div v-if="!invoices?.data?.length" class="text-center py-12">
            <div class="text-5xl mb-3">🧾</div>
            <h3 class="font-semibold text-lg">No Invoices</h3>
            <p class="text-muted-foreground">This client does not have any invoices yet.</p>
        </div>

        <div v-else class="overflow-hidden rounded-2xl border bg-card shadow-sm dark:bg-transparent">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-muted/40">
                        <tr class="text-left text-sm">
                            <th class="px-5 py-3">Invoice</th>
                            <th class="px-5 py-3">Issue Date</th>
                            <th class="px-5 py-3">Due Date</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="invoice in invoices.data" :key="invoice.id" class="border-t hover:bg-muted">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                                        <Receipt class="h-5 w-5 text-primary" />
                                    </div>
                                    <div>
                                        <div class="font-semibold">{{ invoice.invoice_number }}</div>
                                        <div class="text-sm text-muted-foreground">{{ invoice.service?.name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4">{{ invoice.issue_date }}</td>
                            <td class="px-5 py-4">{{ invoice.due_date }}</td>
                            <td class="px-5 py-4 font-semibold">{{ formatRupiah(invoice.total) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium" :class="statusColor(invoice.status)">
                                    {{ invoice.status }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <Link :href="`/admin/invoices/${invoice.id}`" class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-muted">
                                    <Eye class="mr-2 h-4 w-4" />
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex items-center justify-between p-4 border-t">
                <div class="text-sm text-muted-foreground">
                    Showing {{ invoices.from }} to {{ invoices.to }} of {{ invoices.total }} results
                </div>
                <div class="flex gap-2">
                    <Link
                        v-if="invoices.prev_page_url"
                        :href="invoices.prev_page_url"
                        preserve-scroll
                        preserve-state
                        class="rounded-lg border px-4 py-2 text-sm hover:bg-muted"
                    >
                        Previous
                    </Link>
                    <Link
                        v-if="invoices.next_page_url"
                        :href="invoices.next_page_url"
                        preserve-scroll
                        preserve-state
                        class="rounded-lg border px-4 py-2 text-sm hover:bg-muted"
                    >
                        Next
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
