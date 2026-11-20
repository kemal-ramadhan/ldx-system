<script setup lang="ts">
import DataTablePagination from '@/components/DataTablePagination.vue';
import { formatNumber as formatMoney } from '@/lib/money';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Search,
    Plus,
    Pencil,
    Eye,
    Trash2,
    AlertTriangle,
} from 'lucide-vue-next';

import { ref, watch } from 'vue';
import DeleteServiceDialog from '@/components/vanila/service/DeleteServiceDialog.vue';

const props = defineProps<{
    title: string;
    services: any;
    filters: {
        search: string;
        status: string;
        attention?: boolean;
    };
    attentionCount?: number;
}>();

const deleteTarget = ref<any>(null);
const openDeleteDialog = ref(false);

const deleteService = (service: any) => {
    deleteTarget.value = service;
    openDeleteDialog.value = true;
};

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');
// "Perlu dicek": next due date already passed -> not invoiced automatically
const attention = ref(!!props.filters.attention);

watch([search, status, attention], () => {
    router.get(
        '/admin/services',
        {
            search: search.value,
            status: status.value,
            attention: attention.value ? 1 : undefined,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Services Management',
                href: '/admin/services',
            },
        ],
    },
});
</script>

<template>

    <Head :title="title" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">

        <!-- Header -->
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-nowrap sm:items-center sm:justify-between sm:gap-0">

            <div class="flex flex-col gap-2">
                <div class="flex gap-2">
                    <h1 class="text-xl font-bold">
                        {{ title }}
                    </h1>

                    <span class="text-xs text-muted-foreground">
                        ({{ services.total }})
                    </span>
                </div>

                <span class="text-xs">
                    View and manage client services
                </span>
            </div>

            <Link href="/admin/services/create"
                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 dark:text-gray-900">
                <Plus class="mr-2 h-4 w-4" />
                Create Service
            </Link>
        </div>

        <!-- Filters -->
        <!-- services whose next due date passed: not invoiced automatically -->
        <div v-if="attentionCount || attention"
            class="mt-5 flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-3">
                <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                <p>
                    <strong>{{ attentionCount }} service perlu dicek:</strong>
                    Next Due Date-nya sudah lewat, sehingga invoice <strong>tidak dibuat otomatis</strong> dan tidak ada email ke client.
                    Buka service-nya, lalu <em>Generate Invoice</em> jika periode itu memang belum ditagih, atau perbarui Next Due Date jika sudah.
                </p>
            </div>
            <button type="button" @click="attention = !attention"
                class="shrink-0 rounded-lg border border-amber-300 px-3 py-1.5 font-medium hover:bg-amber-100 dark:border-amber-800 dark:hover:bg-amber-900/40">
                {{ attention ? 'Tampilkan semua' : 'Tampilkan yang perlu dicek' }}
            </button>
        </div>

        <div class="flex flex-col mt-5 mb-2 gap-2 sm:flex-row sm:items-center">

            <!-- Search -->
            <div class="relative w-full">
                <input v-model="search" type="text" placeholder="Search service..."
                    class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent" />

                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            </div>

            <!-- Status -->
            <select v-model="status"
                class="w-full sm:w-auto rounded-xl border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="suspended">Suspended</option>
                <option value="terminated">Terminated</option>
            </select>
        </div>

        <!-- Table -->
        <div class="flex w-full items-center overflow-x-auto rounded-md border">

            <table class="min-w-full divide-y divide-gray-200">

                <thead>
                    <tr class="text-left text-sm font-semibold text-muted-foreground">
                        <th class="px-4 py-3">Code</th>
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Service</th>
                        <th class="px-4 py-3">Rack</th>
                        <th class="px-4 py-3">Monthly Total</th>
                        <th class="px-4 py-3">Next Due</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="service in services.data" :key="service.id" class="border-t hover:bg-muted/50">
                        <!-- Code -->
                        <td class="px-4 py-3">
                            {{ service.code }}
                        </td>

                        <!-- Client -->
                        <td class="px-4 py-3">
                            {{ service.client?.company_name }}
                        </td>

                        <!-- Service -->
                        <td class="px-4 py-3">
                            {{ service.name }}
                        </td>

                        <!-- Rack -->
                        <td class="px-4 py-3">
                            <span v-if="service.rack">{{ service.rack.name }}</span>
                            <span v-else
                                class="inline-flex rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground">
                                Tanpa Rack
                            </span>
                        </td>

                        <!-- Monthly -->
                        <td class="px-4 py-3">
                            Rp {{ formatMoney(Number(service.monthly_total)) }}
                        </td>

                        <!-- Due -->
                        <td class="px-4 py-3">
                            <div>{{ service.next_due_date || '-' }}</div>
                            <span v-if="service.schedule_overdue"
                                class="mt-1 inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                <AlertTriangle class="h-3 w-3" /> Perlu dicek
                            </span>
                            <div v-else-if="service.auto_invoice_date" class="mt-0.5 text-[11px] text-muted-foreground">
                                Invoice otomatis {{ service.auto_invoice_date }}
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium" :class="{
                                'bg-green-100 text-green-700':
                                    service.status === 'active',

                                'bg-yellow-100 text-yellow-700':
                                    service.status === 'pending',

                                'bg-orange-100 text-orange-700':
                                    service.status === 'suspended',

                                'bg-red-100 text-red-700':
                                    service.status === 'terminated',
                            }">
                                {{ service.status }}
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="flex flex-nowrap gap-2 px-4 py-3">
                            <div class="flex items-center gap-2">
                                <Link :href="`/admin/services/${service.id}`"
                                    class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-blue-600">
                                    <Eye class="h-4 w-4" />
                                    Detail
                                </Link>

                                <Link :href="`/admin/services/${service.id}/edit`"
                                    class="inline-flex items-center gap-2 rounded-lg bg-green-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-green-600">
                                    <Pencil class="h-4 w-4" />
                                    Edit
                                </Link>

                                <button @click="deleteService(service)"
                                    class="inline-flex items-center gap-2 rounded-lg bg-red-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-red-600">
                                    <Trash2 class="h-4 w-4" />
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <DataTablePagination :paginator="services" />
    </div>

    <DeleteServiceDialog v-model:open="openDeleteDialog" :service="deleteTarget" />
</template>