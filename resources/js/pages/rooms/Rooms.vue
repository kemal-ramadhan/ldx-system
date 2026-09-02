<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';


const props = defineProps<{
    title: string;
    rooms: any;
    locations: any[];
    filters: {
        search: string;
        location: string;
    };
}>();

const search = ref(props.filters.search || '');
const location = ref(props.filters.location || '');

watch([search, location], () => {
    router.get(
        '/admin/rooms',
        {
            search: search.value,
            location: location.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const deleteRoom = (id: number) => {
    if (confirm('Yakin ingin menghapus ruangan ini?')) {
        router.delete(`/admin/rooms/${id}`);
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Room Management',
                href: '/admin/rooms',
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
                <span class="text-xs text-muted-foreground">({{ props.rooms.data.length }})</span>
            </div>
            <span class="text-xs">View and manage your rooms in one place</span>
        </div>
        <div class="flex gap-2">
            <Link
                href="/admin/rooms/create"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50"
            >
                <Plus class="w-4 h-4 mr-2" />
                Add Room
            </Link>
        </div>
    </div>

    <!-- filter -->
     <div class="flex items-center mt-5 mb-2 gap-2">
        <div class="relative w-full">
            <input
                v-model="search"
                type="text"
                placeholder="Search rooms..."
                class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent"
            />
            <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
        </div>
        <select
            v-model="location"
            class="rounded-xl border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent"
        >
            <option value="">All Locations</option>

            <option
                v-for="item in locations"
                :key="item.id"
                :value="item.id"
            >
                {{ item.name }}
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
                    <th class="px-4 py-2">Location</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="room in props.rooms.data"
                    :key="room.id"
                    class="border-t hover:bg-muted/50"
                >
                    <td class="px-4 py-2">{{ room.code }}</td>
                    <td class="px-4 py-2">{{ room.name }}</td>
                    <td class="px-4 py-2">{{ room.location_data_center?.name }}</td>
                    <td class="px-4 py-2">{{ room.description }}</td>
                    <td class="px-4 py-2 flex gap-2 flex-nowrap">
                        <Link
                            :href="`/admin/rooms/${room.id}/edit`"
                            class="text-green-500 hover:underline mr-2"
                        >
                            <Pencil class="w-4 h-4" />
                        </Link>
                        <button
                            @click="deleteRoom(room.id)"
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
            Showing <span class="font-medium">{{ props.rooms.from }}</span> to <span class="font-medium">{{ props.rooms.to }}</span> of <span class="font-medium">{{ props.rooms.total }}</span> results
        </div>
        <div class="flex gap-2">
            <Link
                v-if="props.rooms.prev_page_url"
                :href="props.rooms.prev_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Previous
            </Link>
            <Link
                v-if="props.rooms.next_page_url"
                :href="props.rooms.next_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Next
            </Link>
        </div>
    </div>
    </div>
</template>
