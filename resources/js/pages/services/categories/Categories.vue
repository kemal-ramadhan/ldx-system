<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';


const props = defineProps<{
    title: string;
    categories: any;
    filters: {
        search: string;
    };
}>();

const search = ref(props.filters.search || '');


const deleteCategory = (id: number) => {
    if (confirm('Yakin ingin menghapus data ini?')) {
        router.delete(`/admin/categories/${id}`);
    }
};

watch([search], () => {
    router.get(
        '/admin/categories',
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
                title: 'Categories Management',
                href: '/admin/categories',
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
                <span class="text-xs text-muted-foreground">({{ props.categories.data.length }})</span>
            </div>
            <span class="text-xs">View and manage your categories in one place</span>
        </div>
        <div class="flex gap-2">
            <Link
                href="/admin/categories/create"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50"
            >
                <Plus class="w-4 h-4 mr-2" />
                Add categories
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
                    <th class="px-4 py-2">Categories</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="category in props.categories.data"
                    :key="category.id"
                    class="border-t hover:bg-muted/50"
                >
                    <td class="px-4 py-2">{{ category.categori }}</td>
                    <td class="px-4 py-2">{{ category.description }}</td>
                    <td class="px-4 py-2 flex gap-2 flex-nowrap">
                        <Link
                            :href="`/admin/categories/${category.id}/edit`"
                            class="text-green-500 hover:underline mr-2"
                        >
                            <Pencil class="w-4 h-4" />
                        </Link>
                        <button
                            @click="deleteCategory(category.id)"
                            class="text-red-500 hover:underline"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- pagination -->
    <div class="flex items-center justify-between border-t py-3">
        <div class="text-sm text-muted-foreground">
            Showing <span class="font-medium">{{ props.categories.from }}</span> to <span class="font-medium">{{ props.categories.to }}</span> of <span class="font-medium">{{ props.categories.total }}</span> results
        </div>
        <div class="flex gap-2">
            <Link
                v-if="props.categories.prev_page_url"
                :href="props.categories.prev_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Previous
            </Link>
            <Link
                v-if="props.categories.next_page_url"
                :href="props.categories.next_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Next
            </Link>
        </div>
    </div>
    </div>
</template>
