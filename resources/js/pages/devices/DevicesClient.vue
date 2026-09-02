<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';


const props = defineProps<{
    title: string;
    devices: any;
    filters: {
        search: string;
    };
}>();

const search = ref(props.filters.search || '');

watch([search, location], () => {
    router.get(
        '/client/devices',
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
                title: 'Device Management',
                href: '/client/devices',
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
                    <span class="text-xs text-muted-foreground">({{ props.devices.data.length }})</span>
                </div>
                <span class="text-xs">View and manage your devices in one place</span>
            </div>
            <!-- <div class="flex gap-2">
                <Link href="/client/devices/create"
                    class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50">
                    <Plus class="w-4 h-4 mr-2" />
                    Add Device
                </Link>
            </div> -->
        </div>

        <!-- filter -->
        <div class="flex items-center mt-5 mb-2 gap-2">
            <div class="relative w-full">
                <input v-model="search" type="text" placeholder="Search rooms..."
                    class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent" />
                <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
            </div>
        </div>
        <!-- table -->
        <div class="flex w-full items-center rounded-md border overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="text-left text-sm font-semibold text-muted-foreground">
                        <th class="px-4 py-2">Code</th>
                        <th class="px-4 py-2">Device</th>
                        <th class="px-4 py-2">Type</th>
                        <th class="px-4 py-2">Rack</th>
                        <th class="px-4 py-2">Unit</th>
                        <th class="px-4 py-2">Power</th>
                        <th class="px-4 py-2">IP Address</th>
                        <th class="px-4 py-2">Status</th>
                        <!-- <th class="px-4 py-2">Actions</th> -->
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="device in props.devices.data" :key="device.id" class="border-t hover:bg-muted/50">
                        <!-- CODE -->
                        <td class="px-4 py-2">
                            {{ device.code }}
                        </td>

                        <!-- DEVICE -->
                        <td class="px-4 py-2">
                            <div>
                                <div class="font-medium">
                                    {{ device.divice_name }}
                                </div>

                                <div class="text-xs text-muted-foreground">
                                    {{ device.model || '-' }}
                                </div>
                            </div>
                        </td>

                        <!-- TYPE -->
                        <td class="px-4 py-2">
                            {{ device.divice_type }}
                        </td>

                        <!-- RACK -->
                        <td class="px-4 py-2">
                            <div>
                                <div class="font-medium">
                                    {{ device.rack?.name }}
                                </div>

                                <div class="text-xs text-muted-foreground">
                                    {{ device.rack?.code }}
                                </div>
                            </div>
                        </td>

                        <!-- UNIT -->
                        <td class="px-4 py-2">
                            {{ device.total_unit }} U
                        </td>

                        <!-- POWER -->
                        <td class="px-4 py-2">
                            {{ device.power_usage }} W
                        </td>

                        <!-- IP -->
                        <td class="px-4 py-2">
                            {{ device.ip_address || '-' }}
                        </td>

                        <!-- STATUS -->
                        <td class="px-4 py-2">
                            <span :class="[
                                'inline-flex rounded-full px-2 py-1 text-xs font-medium',
                                device.status === 'active'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-red-100 text-red-700'
                            ]">
                                {{ device.status }}
                            </span>
                        </td>

                        <!-- ACTION -->
                        <!-- <td class="px-4 py-2">
                            <Link :href="`/client/my-devices/${device.id}`" class="text-primary hover:underline">
                                Detail
                            </Link>
                        </td> -->
                    </tr>
                </tbody>
            </table>
        </div>
        <!-- pagination -->
        <div class="flex items-center justify-between border-t py-3">
            <div class="text-sm text-muted-foreground">
                Showing <span class="font-medium">{{ props.devices.from }}</span> to <span class="font-medium">{{
                    props.devices.to }}</span> of <span class="font-medium">{{ props.devices.total }}</span> results
            </div>
            <div class="flex gap-2">
                <Link v-if="props.devices.prev_page_url" :href="props.devices.prev_page_url"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Previous
                </Link>
                <Link v-if="props.devices.next_page_url" :href="props.devices.next_page_url"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                    Next
                </Link>
            </div>
        </div>
    </div>
</template>
