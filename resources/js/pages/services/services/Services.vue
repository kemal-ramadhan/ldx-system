<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Search,
    Plus,
    Pencil,
    Eye,
    Trash2,
} from 'lucide-vue-next';

import { ref, watch } from 'vue';

const props = defineProps<{
    title: string;
    services: any;
    filters: {
        search: string;
        status: string;
    };
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

watch([search, status], () => {
    router.get(
        '/admin/services',
        {
            search: search.value,
            status: status.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const deleteService = (id: number) => {
    if (confirm('Yakin ingin menghapus service ini?')) {
        router.delete(`/admin/services/${id}`);
    }
};

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
        <div class="flex flex-nowrap items-center justify-between">

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
        <div class="mt-5 mb-2 flex items-center gap-2">

            <!-- Search -->
            <div class="relative w-full">
                <input v-model="search" type="text" placeholder="Search service..."
                    class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent" />

                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            </div>

            <!-- Status -->
            <select v-model="status"
                class="rounded-xl border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent">
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
                            {{ service.rack?.name || '-' }}
                        </td>

                        <!-- Monthly -->
                        <td class="px-4 py-3">
                            Rp {{ Number(service.monthly_total).toLocaleString('id-ID') }}
                        </td>

                        <!-- Due -->
                        <td class="px-4 py-3">
                            {{ service.next_due_date || '-' }}
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
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-between border-t py-3">

            <div class="text-sm text-muted-foreground">
                Showing
                <span class="font-medium">
                    {{ services.from }}
                </span>

                to

                <span class="font-medium">
                    {{ services.to }}
                </span>

                of

                <span class="font-medium">
                    {{ services.total }}
                </span>

                results
            </div>

            <div class="flex gap-2">

                <Link v-if="services.prev_page_url" :href="services.prev_page_url"
                    class="inline-flex items-center rounded-md border bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Previous
                </Link>

                <Link v-if="services.next_page_url" :href="services.next_page_url"
                    class="inline-flex items-center rounded-md border bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                    Next
                </Link>
            </div>
        </div>
    </div>
</template>