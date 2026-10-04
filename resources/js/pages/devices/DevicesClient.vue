<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2, Network, Cable, ArrowRight, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';

const openPorts = ref(false);
const selectedDevice = ref<any>(null);

const openViewPorts = (device: any) => {
    selectedDevice.value = device;
    openPorts.value = true;
};


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
                        <th class="px-4 py-2 text-right">Actions</th>
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

                        <!-- ACTIONS -->
                        <td class="px-4 py-2 text-right">
                            <button @click="openViewPorts(device)"
                                class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium hover:bg-gray-50 text-gray-700">
                                <Network class="h-3.5 w-3.5" />
                                View Ports
                            </button>
                        </td>
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

    <!-- Modal Ports -->
    <div v-if="openPorts && selectedDevice" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="openPorts = false"></div>
        <div class="relative w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
            <div class="flex items-center justify-between border-b px-6 py-5">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40">
                            <Network class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold">View Device Ports</h2>
                            <p class="mt-0.5 text-xs text-muted-foreground">{{ selectedDevice.divice_name }}</p>
                        </div>
                    </div>
                </div>
                <button type="button" @click="openPorts = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    <X class="h-5 w-5" />
                </button>
            </div>
            <div class="border-b bg-gray-50/70 px-6 py-4 dark:bg-gray-800/40">
                <div class="flex items-center gap-6">
                    <div>
                        <p class="text-xs text-muted-foreground">Total Ports</p>
                        <p class="mt-1 text-lg font-semibold">{{ selectedDevice.ports?.length || 0 }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Available</p>
                        <p class="mt-1 text-lg font-semibold text-green-600">
                            {{ selectedDevice.ports?.filter((port: any) => port.status === 'available').length || 0 }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">Connected</p>
                        <p class="mt-1 text-lg font-semibold text-blue-600">
                            {{ selectedDevice.ports?.filter((port: any) => port.status === 'connected').length || 0 }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="max-h-[60vh] overflow-y-auto px-6 py-5">
                <div v-if="!selectedDevice.ports?.length" class="flex flex-col items-center justify-center rounded-2xl border border-dashed py-16 text-center">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <Network class="h-6 w-6 text-gray-400" />
                    </div>
                    <h3 class="text-sm font-semibold">No ports configured</h3>
                </div>
                <div v-else class="overflow-hidden rounded-xl border">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">Port</th>
                                <th class="px-4 py-3 text-left font-medium">Type</th>
                                <th class="px-4 py-3 text-left font-medium">Connector</th>
                                <th class="px-4 py-3 text-left font-medium">Status</th>
                                <th class="px-4 py-3 text-left font-medium">Connection</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="port in selectedDevice.ports" :key="port.id" class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                            <Cable class="h-4 w-4" />
                                        </div>
                                        <div>
                                            <p class="font-medium">{{ port.port_name }}</p>
                                            <p v-if="port.port_number" class="text-xs text-muted-foreground">#{{ port.port_number }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3"><span class="capitalize">{{ port.port_type?.replace('_', ' ') }}</span></td>
                                <td class="px-4 py-3 text-muted-foreground">{{ port.connector_type || '-' }}</td>
                                <td class="px-4 py-3">
                                    <span v-if="port.status === 'available'" class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-950/30 dark:text-green-400">Available</span>
                                    <span v-else-if="port.status === 'connected'" class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/30 dark:text-blue-400">Connected</span>
                                    <span v-else-if="port.status === 'maintenance'" class="inline-flex rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400">Maintenance</span>
                                    <span v-else class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400 capitalize">{{ port.status }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div v-if="port.active_source_cross_connect" class="flex flex-col text-xs">
                                        <div class="flex items-center gap-1 text-gray-600">
                                            <ArrowRight class="w-3 h-3 text-blue-500" />
                                            <span class="font-medium truncate max-w-[150px]" :title="port.active_source_cross_connect.destination_port?.device?.divice_name">
                                                {{ port.active_source_cross_connect.destination_port?.device?.divice_name || 'Unknown Device' }}
                                            </span>
                                        </div>
                                        <div class="text-gray-400 ml-4">Port: {{ port.active_source_cross_connect.destination_port?.port_name || '?' }}</div>
                                    </div>
                                    <div v-else-if="port.active_destination_cross_connect" class="flex flex-col text-xs">
                                        <div class="flex items-center gap-1 text-gray-600">
                                            <ArrowRight class="w-3 h-3 text-green-500" />
                                            <span class="font-medium truncate max-w-[150px]" :title="port.active_destination_cross_connect.source_port?.device?.divice_name">
                                                {{ port.active_destination_cross_connect.source_port?.device?.divice_name || 'Unknown Device' }}
                                            </span>
                                        </div>
                                        <div class="text-gray-400 ml-4">Port: {{ port.active_destination_cross_connect.source_port?.port_name || '?' }}</div>
                                    </div>
                                    <div v-else class="text-gray-400 text-xs">-</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
