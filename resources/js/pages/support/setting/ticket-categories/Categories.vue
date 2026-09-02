<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';


const props = defineProps<{
    title: string;
    ticketCategories: any;
    filters: {
        search: string;
    };
}>();

const search = ref(props.filters.search || '');

watch([search], () => {
    router.get(
        '/admin/tickets-category',
        {
            search: search.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const toggleActive = (category: any) => {
    router.patch(
        `/admin/tickets-category/${category.id}/toggle-active`,
        {
            is_active: !category.is_active,
        },
        {
            preserveScroll: true,
        }
    );
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Ticket Categories',
                href: '/admin/tickets-category',
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
                    <span class="text-xs text-muted-foreground">({{ props.ticketCategories.data.length }})</span>
                </div>
                <span class="text-xs">View and manage your Ticket Categories in one place</span>
            </div>
            <div class="flex gap-2">
                <Link href="/admin/tickets-category/create"
                    class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Category
                </Link>
            </div>
        </div>

        <!-- filter -->
        <div class="flex items-center mt-5 mb-2 gap-2">
            <div class="relative w-full">
                <input v-model="search" type="text" placeholder="Search Category ..."
                    class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent" />
                <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
            </div>
        </div>
        <!-- table -->
        <div class="flex w-full items-center rounded-md border overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="text-left text-sm font-semibold text-muted-foreground">
                        <th class="px-4 py-2">Name Categories</th>
                        <th class="px-4 py-2">Color</th>
                        <th class="px-4 py-2">Description</th>
                        <th class="px-4 py-2">Active</th>
                        <th class="px-4 py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="category in props.ticketCategories.data" :key="category.id"
                        class="border-t hover:bg-muted/50">
                        <td class="px-4 py-2">{{ category.name }}</td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-2">
                                <div class="h-6 w-6 rounded border border-gray-300"
                                    :style="{ backgroundColor: category.color }"></div>

                                <span class="rounded-md border bg-gray-50 px-2 py-1 font-mono text-xs dark:bg-gray-800">
                                    {{ category.color }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-2">{{ category.description }}</td>
                        <td class="px-4 py-2">
                            <button type="button" @click="toggleActive(category)"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition"
                                :class="category.is_active ? 'bg-green-500' : 'bg-gray-300'">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition"
                                    :class="category.is_active ? 'translate-x-6' : 'translate-x-1'" />
                            </button>
                        </td>
                        <td class="px-4 py-2 flex gap-2 flex-nowrap">
                            <Link :href="`/admin/tickets-category/${category.id}/edit`"
                                class="text-green-500 hover:underline mr-2">
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
                Showing <span class="font-medium">{{ props.ticketCategories.from }}</span> to <span
                    class="font-medium">{{ props.ticketCategories.to }}</span> of <span class="font-medium">{{
                        props.ticketCategories.total }}</span> results
            </div>
            <div class="flex gap-2">
                <Link v-if="props.ticketCategories.prev_page_url" :href="props.ticketCategories.prev_page_url"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Previous
                </Link>
                <Link v-if="props.ticketCategories.next_page_url" :href="props.ticketCategories.next_page_url"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Next
                </Link>
            </div>
        </div>
    </div>
</template>
