<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';


const props = defineProps<{
    title: string;
    clients: any;
    filters: {
        search: string;
    };
}>();

const search = ref(props.filters.search || '');

watch([search], () => {
    router.get(
        '/admin/clients',
        {
            search: search.value,
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
                title: 'Client Management',
                href: '/admin/clients',
            },
        ],
    },
});
</script>

<template>
    <Head :title="props.title" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
    <div class="flex flex-nowrap justify-between items-center">
        <div class="flex flex-col gap-2">
            <div class="flex gap-2">
                <h1 class="text-xl font-bold">{{ props.title }}</h1>
                <span class="text-xs text-muted-foreground">({{ props.clients.data.length }})</span>
            </div>
            <span class="text-xs">View and manage your clients in one place</span>
        </div>
        <div class="flex gap-2">
            <Link
                href="/admin/clients/create"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50"
            >
                <Plus class="w-4 h-4 mr-2" />
                Export Data
            </Link>
        </div>
    </div>

    <!-- filter -->
     <div class="flex items-center mt-5 mb-2 gap-2">
        <div class="relative w-full">
            <input
                v-model="search"
                type="text"
                placeholder="Search users..."
                class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent"
            />
            <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
        </div>
     </div>
    <!-- table -->
    <div class="flex w-full items-center rounded-md border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="text-left text-sm font-semibold text-muted-foreground">
                    <th class="px-4 py-2">Code</th>
                    <th class="px-4 py-2">Company Name</th>
                    <th class="px-4 py-2">PICs</th>
                    <th class="px-4 py-2">Contract Date</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="user in props.clients.data"
                    :key="user.id"
                    class="border-t hover:bg-muted/50"
                >
                    <td class="px-4 py-2">{{ user.company_code }}</td>
                    <td class="px-4 py-2">{{ user.company_name }}</td>
                    <td class="px-4 py-2">
                        <div class="flex items-center -space-x-2">
                            <template
                                v-for="(pic, index) in user.pics.slice(0, 3)"
                                :key="pic.id"
                            >
                                <img
                                    v-if="pic.user?.avatar"
                                    :src="pic.user.avatar"
                                    :alt="pic.user.name"
                                    class="w-8 h-8 rounded-full border-2 border-white object-cover"
                                />

                                <!-- fallback kalau tidak ada avatar -->
                                <div
                                    v-else
                                    class="w-8 h-8 rounded-full border-2 border-white bg-gray-300 flex items-center justify-center text-xs font-semibold text-gray-700"
                                >
                                    {{ pic.user?.name?.charAt(0) }}
                                </div>
                            </template>

                            <!-- jumlah sisa -->
                            <div
                                v-if="user.pics.length > 3"
                                class="w-8 h-8 rounded-full bg-muted border-2 border-white flex items-center justify-center text-xs font-semibold"
                            >
                                +{{ user.pics.length - 3 }}
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-2">{{ user.contract_date }}</td>
                    <td class="px-4 py-2 flex gap-2 flex-nowrap">
                        <Link
                            :href="`/admin/clients/${user.id}`"
                            class="text-blue-500 hover:underline mr-2"
                        >
                            <Eye class="w-4 h-4" />
                        </Link>
                        <Link
                            :href="`/admin/clients/${user.id}/edit`"
                            class="text-green-500 hover:underline mr-2"
                        >
                            <Pencil class="w-4 h-4" />
                        </Link>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- pagination -->
    <div class="flex items-center justify-between border-t py-3">
        <div class="text-sm text-muted-foreground">
            Showing <span class="font-medium">{{ props.clients.from }}</span> to <span class="font-medium">{{ props.clients.to }}</span> of <span class="font-medium">{{ props.clients.total }}</span> results
        </div>
        <div class="flex gap-2">
            <Link
                v-if="props.clients.prev_page_url"
                :href="props.clients.prev_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Previous
            </Link>
            <Link
                v-if="props.clients.next_page_url"
                :href="props.clients.next_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Next
            </Link>
        </div>
    </div>
    </div>
</template>
