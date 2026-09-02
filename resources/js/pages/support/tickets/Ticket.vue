<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

import {
    Search,
    Eye,
    Plus,
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
    tickets: any;
    categories: any[];
    priorities: any[];
    filters: {
        search: string;
        status: string;
        category: string;
        priority: string;
    };
}>();

/**
 * =========================================
 * FILTERS
 * =========================================
 */
const search = ref(props.filters.search || '');

const status = ref(props.filters.status || '');

const category = ref(props.filters.category || '');

const priority = ref(props.filters.priority || '');
/**
 * =========================================
 * WATCH FILTER
 * =========================================
 */
watch(
    [
        search,
        status,
        category,
        priority,
    ],
    () => {

        router.get(
            '/admin/tickets',
            {
                search: search.value,
                status: status.value,
                category: category.value,
                priority: priority.value,
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
 * STATUS COLOR
 * =========================================
 */
const statusColor = (
    status: string
) => {

    switch (status) {

        case 'open':
            return 'bg-red-100 text-red-700 border-red-200';

        case 'in_progress':
            return 'bg-blue-100 text-blue-700 border-blue-200';

        case 'waiting_client':
            return 'bg-yellow-100 text-yellow-700 border-yellow-200';

        case 'resolved':
            return 'bg-green-100 text-green-700 border-green-200';

        case 'closed':
            return 'bg-green-100 text-green-700 border-green-200';

        default:
            return 'bg-blue-100 text-blue-700 border-blue-200';
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tickets',
                href: '/admin/tickets',
            },
        ],
    },
});
</script>

<template>

    <Head :title="title" />

    <div class="flex flex-col gap-5 p-4">

        <!-- Header -->
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="flex flex-nowrap justify-between items-center">
                <div class="flex flex-col gap-2">
                    <div class="flex gap-2">
                        <h1 class="text-xl font-bold">{{ title }}</h1>
                        <span class="text-xs text-muted-foreground">({{ tickets.total }})</span>
                    </div>
                    <span class="text-xs">View and manage your tickets in one place</span>
                </div>
                <!-- <div class="flex gap-2">
                    <Link href="/admin/tickets/create"
                        class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50">
                        <Plus class="w-4 h-4 mr-2" />
                        Add a New Ticket
                    </Link>
                </div> -->
            </div>
        </div>

        <!-- Filters -->
        <div class="grid grid-cols-1 gap-3 md:grid-cols-12">

            <!-- Search -->
            <div class="relative md:col-span-6">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                <input v-model="search" type="text" placeholder="Search ticket..."
                    class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent" />
            </div>

            <!-- Status -->
            <div class="md:col-span-2">
                <select v-model="status" class="w-full rounded-xl border px-4 py-2.5 text-sm">
                    <option value="">All Status</option>
                    <option value="open">Open</option>
                    <option value="in_progress">In Progress</option>
                    <option value="waiting_client">Waiting Client</option>
                    <option value="resolved">Resolved</option>
                    <option value="closed">Closed</option>
                </select>
            </div>

            <!-- Category -->
            <div class="md:col-span-2">
                <select v-model="category" class="w-full rounded-xl border px-4 py-2.5 text-sm">
                    <option value="">All Categories</option>

                    <option v-for="item in categories" :key="item.id" :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>

            <!-- Priority -->
            <div class="md:col-span-2">
                <select v-model="priority" class="w-full rounded-xl border px-4 py-2.5 text-sm">
                    <option value="">All Priorities</option>

                    <option v-for="item in priorities" :key="item.id" :value="item.id">
                        {{ item.name }}
                    </option>
                </select>
            </div>

        </div>

        <!-- Table -->
        <div class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:bg-transparent">

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-muted/40">

                        <tr class="text-left text-sm">

                            <th class="px-5 py-3">
                                Date
                            </th>

                            <th class="px-5 py-3">
                                Code
                            </th>

                            <th class="px-5 py-3">
                                Subject
                            </th>

                            <th class="px-5 py-3">
                                Category
                            </th>

                            <th class="px-5 py-3">
                                Priority
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

                        <tr v-for="ticket in tickets.data" :key="ticket.id" class="border-t">

                            <!-- Date -->
                            <td class="px-5 py-4">
                                {{ ticket.created_at }}
                            </td>

                            <!-- Code -->
                            <td class="px-5 py-4">
                                {{ ticket.code }}
                            </td>

                            <!-- Client -->
                            <td class="px-5 py-4">

                                <div class="font-medium">
                                    {{ ticket.subject }}
                                </div>

                            </td>

                            <td class="px-5 py-4">
                                {{ ticket.category.name }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ ticket.priority.name }}
                                </td>

                                <!-- Status -->
                                <td class="px-5 py-4">

                                    <span class="inline-flex rounded-full border px-3 py-1 text-xs font-medium"
                                        :class="statusColor(ticket.status)">
                                        {{ ticket.status }}
                                    </span>

                                </td>

                                <!-- Actions -->
                                <td class="px-5 py-4">

                                    <Link :href="`/admin/tickets/${ticket.id}`"
                                        class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-muted">
                                        <Eye class="mr-2 h-4 w-4" />
                                        View
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
                {{ tickets.from }}
                to
                {{ tickets.to }}
                of
                {{ tickets.total }}
                results
            </div>

            <div class="flex gap-2">

                <Link v-if="tickets.prev_page_url" :href="tickets.prev_page_url"
                    class="rounded-lg border px-4 py-2 text-sm hover:bg-muted">
                    Previous
                </Link>

                <Link v-if="tickets.next_page_url" :href="tickets.next_page_url"
                    class="rounded-lg border px-4 py-2 text-sm hover:bg-muted">
                    Next
                </Link>

            </div>

        </div>

    </div>
</template>