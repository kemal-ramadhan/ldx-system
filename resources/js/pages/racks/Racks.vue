<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next';
import { ref, watch, computed } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';


const props = defineProps<{
    title: string;
    racks: any;
    rooms: any[];
    filters: {
        search: string;
        room: string;
    };
}>();

const search = ref(props.filters.search || '');
const room = ref(props.filters.room || '');

watch([search, room], () => {
    router.get(
        '/admin/racks',
        {
            search: search.value,
            room: room.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const deleteConfirmation = ref<{ isOpen: boolean, type: 'single' | 'bulk', rack: any | null }>({
    isOpen: false,
    type: 'single',
    rack: null,
});

const deleteRack = (rack: any) => {
    deleteConfirmation.value = {
        isOpen: true,
        type: 'single',
        rack: rack,
    };
};

const selectedIds = ref<number[]>([]);

const isAllSelected = computed(() => {
    return props.racks.data.length > 0 && selectedIds.value.length === props.racks.data.length;
});

const toggleAll = (event: Event) => {
    const isChecked = (event.target as HTMLInputElement).checked;
    if (isChecked) {
        selectedIds.value = props.racks.data.map((rack: any) => rack.id);
    } else {
        selectedIds.value = [];
    }
};

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    deleteConfirmation.value = {
        isOpen: true,
        type: 'bulk',
        rack: null,
    };
};

const confirmDelete = () => {
    if (deleteConfirmation.value.type === 'single' && deleteConfirmation.value.rack) {
        router.delete(`/admin/racks/${deleteConfirmation.value.rack.id}`, {
            onSuccess: () => {
                deleteConfirmation.value.isOpen = false;
            }
        });
    } else if (deleteConfirmation.value.type === 'bulk') {
        router.delete('/admin/racks/bulk-destroy', {
            data: { ids: selectedIds.value },
            onSuccess: () => {
                selectedIds.value = [];
                deleteConfirmation.value.isOpen = false;
            },
        });
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Rack Management',
                href: '/admin/racks',
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
                <span class="text-xs text-muted-foreground">({{ props.racks.data.length }})</span>
            </div>
            <span class="text-xs">View and manage your racks in one place</span>
        </div>
        <div class="flex gap-2">
            <button
                v-if="selectedIds.length > 0"
                @click="bulkDelete"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
            >
                <Trash2 class="w-4 h-4 mr-2" />
                Delete Selected ({{ selectedIds.length }})
            </button>
            <Link
                href="/admin/racks/create"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50"
            >
                <Plus class="w-4 h-4 mr-2" />
                Add Rack
            </Link>
        </div>
    </div>

    <!-- filter -->
     <div class="flex items-center mt-5 mb-2 gap-2">
        <div class="relative w-full">
            <input
                v-model="search"
                type="text"
                placeholder="Search racks..."
                class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent"
            />
            <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
        </div>
        <select
            v-model="room"
            class="rounded-xl border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent"
        >
            <option value="">All Rooms</option>

            <option
                v-for="item in props.rooms"
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
                    <th class="px-4 py-2 w-10">
                        <input
                            type="checkbox"
                            :checked="isAllSelected"
                            @change="toggleAll"
                            class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                        />
                    </th>
                    <th class="px-4 py-2">Code</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Unit</th>
                    <th class="px-4 py-2">Power Capacity</th>
                    <th class="px-4 py-2">Weight Capacity</th>
                    <th class="px-4 py-2">Location</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="rack in props.racks.data"
                    :key="rack.id"
                    class="border-t hover:bg-muted/50"
                >
                    <td class="px-4 py-2">
                        <input
                            type="checkbox"
                            v-model="selectedIds"
                            :value="rack.id"
                            class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                        />
                    </td>
                    <td class="px-4 py-2">{{ rack.code }}</td>
                    <td class="px-4 py-2">{{ rack.name }}</td>
                    <td class="px-4 py-2">{{ rack.total_units }}</td>
                    <td class="px-4 py-2">{{ rack.power_capacity }} (A)</td>
                    <td class="px-4 py-2">{{ rack.weight_capacity }} (kg)</td>
                    <td class="px-4 py-2">{{ rack.room?.location_data_center?.name }} - {{ rack.room?.name }}</td>
                    <td class="px-4 py-2">{{ rack.description }}</td>
                    <td class="px-4 py-2">
                        <span
                            :class="`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${
                                rack.status === 'active' ? 'bg-green-100 text-green-800' :
                                rack.status === 'inactive' ? 'bg-gray-100 text-gray-800' :
                                rack.status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' :
                                'bg-red-100 text-red-800'
                            }`"
                        >
                            {{ rack.status }}
                        </span>
                    </td>
                    <td class="px-4 py-2 flex gap-2 flex-nowrap">
                        <Link
                            :href="`/admin/racks/${rack.id}`"
                            class="text-blue-500 hover:underline mr-2"
                        >
                            <Eye class="w-4 h-4" />
                        </Link>
                        <Link
                            :href="`/admin/racks/${rack.id}/edit`"
                            class="text-green-500 hover:underline mr-2"
                        >
                            <Pencil class="w-4 h-4" />
                        </Link>
                        <button
                            @click="deleteRack(rack)"
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
            Showing <span class="font-medium">{{ props.racks.from }}</span> to <span class="font-medium">{{ props.racks.to }}</span> of <span class="font-medium">{{ props.racks.total }}</span> results
        </div>
        <div class="flex gap-2">
            <Link
                v-if="props.racks.prev_page_url"
                :href="props.racks.prev_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Previous
            </Link>
            <Link
                v-if="props.racks.next_page_url"
                :href="props.racks.next_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Next
            </Link>
        </div>
    </div>
    </div>

    <Dialog :open="deleteConfirmation.isOpen" @update:open="(val) => deleteConfirmation.isOpen = val">
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>
                    {{ deleteConfirmation.type === 'single' ? 'Are you sure you want to delete this rack?' : 'Are you sure you want to delete selected racks?' }}
                </DialogTitle>
                <DialogDescription>
                    <template v-if="deleteConfirmation.type === 'single'">
                        You are about to delete rack <strong>{{ deleteConfirmation.rack?.name }}</strong>. 
                        This rack currently has <strong>{{ deleteConfirmation.rack?.rack_divices_count || 0 }}</strong> devices and <strong>{{ deleteConfirmation.rack?.client_racks_count || 0 }}</strong> owners.
                        <br><br>
                        Deleting this rack will also terminate all active cross connects associated with its devices.
                    </template>
                    <template v-else>
                        You are about to delete <strong>{{ selectedIds.length }}</strong> racks.
                        <br><br>
                        Deleting these racks will also terminate all active cross connects associated with their devices.
                    </template>
                    <br><br>
                    This action will hide the data but keep it in the history.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button variant="secondary" @click="deleteConfirmation.isOpen = false">Cancel</Button>
                <Button variant="destructive" @click="confirmDelete">Delete</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
