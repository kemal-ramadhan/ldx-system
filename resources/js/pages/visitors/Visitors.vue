<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2, EyeIcon } from 'lucide-vue-next';
import { ref, watch } from 'vue';


const props = defineProps<{
    title: string;
    visitors: any;
    filters: {
        search: string;
        status: string;
    };
}>();

const search = ref(props.filters.search || '');
const status = ref(props.filters.status || '');

watch([search, location], () => {
    router.get(
        '/admin/visitors',
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

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Visitors Management',
                href: '/admin/visitors',
            },
        ],
    },
});
</script>

<template>

    <Head :title="props.title" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex flex-nowrap justify-between items-center">
            <div class="flex flex-col gap-2">
                <div class="flex gap-2">
                    <h1 class="text-xl font-bold">{{ props.title }}</h1>
                    <span class="text-xs text-muted-foreground">({{ props.visitors.data.length }})</span>
                </div>
                <span class="text-xs">View and manage your visitors in one place</span>
            </div>
        </div>

        <!-- filter -->
        <div class="flex items-center mt-5 mb-2 gap-2">
            <div class="relative w-full">
                <input v-model="search" type="text" placeholder="Search visitors..."
                    class="w-full rounded-md border border-gray-300 bg-white py-2 pl-10 pr-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:text-gray-900" />
                <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
            </div>
            <select v-model="status"
                class="rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:text-gray-900 dark:bg-white">
                <option value="">All status</option>

                <option value="checkin">
                    checkin
                </option>
                <option value="checkout">
                    checkout
                </option>
            </select>
        </div>
        <!-- table -->
        <div class="flex w-full items-center rounded-md border overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="text-left text-sm font-semibold text-muted-foreground">
                        <th class="px-4 py-2">Date Of Visit</th>
                        <th class="px-4 py-2">Name</th>
                        <th class="px-4 py-2">Company Name</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Phone</th>
                        <th class="px-4 py-2">Visit Perpose</th>
                        <th class="px-4 py-2">Status</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="visitor in props.visitors.data" :key="visitor.id" class="border-t hover:bg-muted/50">
                        <td class="px-4 py-2">{{ new Date(visitor.visit_date).toLocaleDateString('id-ID') }}</td>
                        <td class="px-4 py-2">{{ visitor.name }}</td>
                        <td class="px-4 py-2">{{ visitor.company_name }}</td>
                        <td class="px-4 py-2">{{ visitor.email }}</td>
                        <td class="px-4 py-2">{{ visitor.phone }}</td>
                        <td class="px-4 py-2">{{ visitor.visit_purpose }}</td>
                        <td class="px-4 py-2">{{ visitor.status }}</td>
                        <td class="px-4 py-2 flex gap-2 flex-nowrap">
                            <Link :href="`/admin/visitors/${visitor.id}`"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-blue-600">
                                <Eye class="h-4 w-4" />
                                Detail
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- pagination -->
        <div class="flex items-center justify-between border-t py-3">
            <div class="text-sm text-muted-foreground">
                Showing <span class="font-medium">{{ props.visitors.from }}</span> to <span class="font-medium">{{
                    props.visitors.to }}</span> of <span class="font-medium">{{ props.visitors.total }}</span> results
            </div>
            <div class="flex gap-2">
                <Link v-if="props.visitors.prev_page_url" :href="props.visitors.prev_page_url"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Previous
                </Link>
                <Link v-if="props.visitors.next_page_url" :href="props.visitors.next_page_url"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Next
                </Link>
            </div>
        </div>
    </div>
</template>
