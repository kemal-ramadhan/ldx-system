<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';


const props = defineProps<{
    title: string;
    products: any;
    categories: any[];
    filters: {
        search: string;
        category: string;
    };
}>();

const search = ref(props.filters.search || '');
const category = ref(props.filters.category || '');

watch([search, location], () => {
    router.get(
        '/admin/products',
        {
            search: search.value,
            category: category.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const deleteproduct = (id: number) => {
    if (confirm('Yakin ingin menghapus ruangan ini?')) {
        router.delete(`/admin/products/${id}`);
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Products Management',
                href: '/admin/products',
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
                <span class="text-xs text-muted-foreground">({{ props.products.data.length }})</span>
            </div>
            <span class="text-xs">View and manage your products in one place</span>
        </div>
        <div class="flex gap-2">
            <Link
                href="/admin/products/create"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50"
            >
                <Plus class="w-4 h-4 mr-2" />
                Add Products
            </Link>
        </div>
    </div>

    <!-- filter -->
     <div class="flex items-center mt-5 mb-2 gap-2">
        <div class="relative w-full">
            <input
                v-model="search"
                type="text"
                placeholder="Search products..."
                class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent"
            />
            <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
        </div>
        <select
            v-model="category"
            class="rounded-xl border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent"
        >
            <option value="">All Categories</option>

            <option
                v-for="item in categories"
                :key="item.id"
                :value="item.id"
            >
                {{ item.categori }}
            </option>
        </select>
     </div>
    <!-- table -->
    <div class="flex w-full items-center rounded-md border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="text-left text-sm font-semibold text-muted-foreground">
                    <th class="px-4 py-2">Code</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Unit</th>
                    <th class="px-4 py-2">Base Price</th>
                    <th class="px-4 py-2">Billing Type</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="product in props.products.data"
                    :key="product.id"
                    class="border-t hover:bg-muted/50"
                >
                    <td class="px-4 py-2">{{ product.code }}</td>
                    <td class="px-4 py-2">{{ product.name }}</td>
                    <td class="px-4 py-2">{{ product.categories?.categori }}</td>
                    <td class="px-4 py-2">{{ product.unit }}</td>
                    <td class="px-4 py-2">{{ product.base_price }}</td>
                    <td class="px-4 py-2">{{ product.billing_type }}</td>
                    <td class="px-4 py-2">{{ product.description }}</td>
                    <td class="px-4 py-2">{{ product.status }}</td>
                    <td class="px-4 py-2 flex gap-2 flex-nowrap">
                        <Link
                            :href="`/admin/products/${product.id}`"
                            class="text-blue-500 hover:underline mr-2"
                        >
                            <Eye class="w-4 h-4" />
                        </Link>
                        <Link
                            :href="`/admin/products/${product.id}/edit`"
                            class="text-green-500 hover:underline mr-2"
                        >
                            <Pencil class="w-4 h-4" />
                        </Link>
                        <button
                            @click="deleteproduct(product.id)"
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
            Showing <span class="font-medium">{{ props.products.from }}</span> to <span class="font-medium">{{ props.products.to }}</span> of <span class="font-medium">{{ props.products.total }}</span> results
        </div>
        <div class="flex gap-2">
            <Link
                v-if="props.products.prev_page_url"
                :href="props.products.prev_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Previous
            </Link>
            <Link
                v-if="props.products.next_page_url"
                :href="props.products.next_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Next
            </Link>
        </div>
    </div>
    </div>
</template>
