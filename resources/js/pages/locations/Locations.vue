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
    locations: any;
    filters: {
        search: string;
    };
}>();

const search = ref(props.filters.search || '');

watch([search], () => {
    router.get(
        '/admin/locations',
        {
            search: search.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const deleteConfirmation = ref<{ isOpen: boolean, type: 'single' | 'bulk', location: any | null }>({
    isOpen: false,
    type: 'single',
    location: null,
});

const deleteLocation = (loc: any) => {
    deleteConfirmation.value = {
        isOpen: true,
        type: 'single',
        location: loc,
    };
};

const selectedIds = ref<number[]>([]);

const isAllSelected = computed(() => {
    return props.locations.data.length > 0 && selectedIds.value.length === props.locations.data.length;
});

const toggleAll = (event: Event) => {
    const isChecked = (event.target as HTMLInputElement).checked;
    if (isChecked) {
        selectedIds.value = props.locations.data.map((loc: any) => loc.id);
    } else {
        selectedIds.value = [];
    }
};

const bulkDelete = () => {
    if (selectedIds.value.length === 0) return;
    deleteConfirmation.value = {
        isOpen: true,
        type: 'bulk',
        location: null,
    };
};

const confirmDelete = () => {
    if (deleteConfirmation.value.type === 'single' && deleteConfirmation.value.location) {
        router.delete(`/admin/locations/${deleteConfirmation.value.location.id}`, {
            onSuccess: () => {
                deleteConfirmation.value.isOpen = false;
            }
        });
    } else if (deleteConfirmation.value.type === 'bulk') {
        router.delete('/admin/locations/bulk-destroy', {
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
                title: 'Location Management',
                href: '/admin/locations',
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
                <span class="text-xs text-muted-foreground">({{ props.locations.data.length }})</span>
            </div>
            <span class="text-xs">View and manage your locations in one place</span>
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
                href="/admin/locations/create"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50"
            >
                <Plus class="w-4 h-4 mr-2" />
                Add Location
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
                    <th class="px-4 py-2 w-10">
                        <input
                            type="checkbox"
                            :checked="isAllSelected"
                            @change="toggleAll"
                            class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                        />
                    </th>
                    <th class="px-4 py-2">Code</th>
                    <th class="px-4 py-2">Name Location</th>
                    <th class="px-4 py-2">Address</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="location in props.locations.data"
                    :key="location.id"
                    class="border-t hover:bg-muted/50"
                >
                    <td class="px-4 py-2">
                        <input
                            type="checkbox"
                            v-model="selectedIds"
                            :value="location.id"
                            class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4"
                        />
                    </td>
                    <td class="px-4 py-2">{{ location.code }}</td>
                    <td class="px-4 py-2">{{ location.name }}</td>
                    <td class="px-4 py-2">{{ location.address }}</td>
                    <td class="px-4 py-2 flex gap-2 flex-nowrap">
                        <Link
                            :href="`/admin/locations/${location.id}`"
                            class="text-blue-500 hover:underline mr-2"
                        >
                            <Eye class="w-4 h-4" />
                        </Link>
                        <Link
                            :href="`/admin/locations/${location.id}/edit`"
                            class="text-green-500 hover:underline mr-2"
                        >
                            <Pencil class="w-4 h-4" />
                        </Link>
                        <button
                            @click="deleteLocation(location)"
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
            Showing <span class="font-medium">{{ props.locations.from }}</span> to <span class="font-medium">{{ props.locations.to }}</span> of <span class="font-medium">{{ props.locations.total }}</span> results
        </div>
        <div class="flex gap-2">
            <Link
                v-if="props.locations.prev_page_url"
                :href="props.locations.prev_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Previous
            </Link>
            <Link
                v-if="props.locations.next_page_url"
                :href="props.locations.next_page_url"
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
                    {{ deleteConfirmation.type === 'single' ? 'Are you sure you want to delete this location?' : 'Are you sure you want to delete selected locations?' }}
                </DialogTitle>
                <DialogDescription>
                    <template v-if="deleteConfirmation.type === 'single'">
                        You are about to delete location <strong>{{ deleteConfirmation.location?.name }}</strong>. 
                    </template>
                    <template v-else>
                        You are about to delete <strong>{{ selectedIds.length }}</strong> locations.
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
