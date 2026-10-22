<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import {
    Plus,
    Search,
    Trash2,
    Pencil,
    X,
    Network,
    Cable,
    ArrowRight
} from 'lucide-vue-next'
import { route } from 'ziggy-js'

import { toast } from 'vue-sonner'

const page = usePage()

const props = defineProps<{
    rack: any
    clients: any[]
}>()

/**
 * SEARCH
 */
const search = ref('')

const openPorts = ref(false)
const selectedDevice = ref<any>(null)

const openViewPorts = (device: any) => {
    selectedDevice.value = device
    openPorts.value = true
}

const filteredDevices = computed(() => {

    if (!search.value) {
        return props.rack?.rack_divices || []
    }

    return props.rack?.rack_divices?.filter((item: any) => {

        return (
            item.divice_name?.toLowerCase().includes(search.value.toLowerCase()) ||
            item.code?.toLowerCase().includes(search.value.toLowerCase()) ||
            item.serial_number?.toLowerCase().includes(search.value.toLowerCase()) ||
            item.client?.company_name?.toLowerCase().includes(search.value.toLowerCase())
        )
    })
})


const currentPage = ref(1)
const perPage = 10

const paginatedDevices = computed(() => {
    const start = (currentPage.value - 1) * perPage
    const end = start + perPage

    return filteredDevices.value.slice(start, end)
})

const totalPages = computed(() => {
    return Math.ceil(filteredDevices.value.length / perPage)
})

const goToPage = (page: number) => {
    if (page < 1 || page > totalPages.value) return

    currentPage.value = page
}

watch(search, () => {
    currentPage.value = 1
})

</script>

<template>
    <div class="p-4">

        <!-- HEADER -->
        <div class="flex items-center justify-between mb-6">

            <div>
                <h2 class="text-lg font-semibold">
                    Rack Devices
                </h2>

                <p class="text-sm text-muted-foreground">
                    Manage all devices inside this rack
                </p>
            </div>

        </div>

        <!-- SEARCH -->
        <div class="relative mb-6">

            <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />

            <input v-model="search" type="text" placeholder="Search device..."
                class="w-full rounded-xl border px-10 py-3 text-sm" />
        </div>

        <!-- EMPTY -->
        <div v-if="filteredDevices.length === 0" class="rounded-2xl border border-dashed p-10 text-center">
            <h3 class="text-lg font-medium">
                No devices found
            </h3>

            <p class="mt-2 text-sm text-muted-foreground">
                Add your first device to this rack
            </p>
        </div>

        <!-- TABLE -->
        <div class="rounded-2xl border bg-white shadow-sm overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">

                <!-- HEAD -->
                <thead class="bg-gray-50">
                    <tr class="text-left text-sm font-semibold text-muted-foreground">

                        <th class="px-4 py-3">
                            Device
                        </th>

                        <th class="px-4 py-3">
                            Client
                        </th>

                        <th class="px-4 py-3">
                            Unit
                        </th>

                        <th class="px-4 py-3">
                            Position
                        </th>

                        <th class="px-4 py-3">
                            Power
                        </th>

                        <th class="px-4 py-3">
                            Weight
                        </th>

                        <th class="px-4 py-3">
                            Status
                        </th>

                        <th class="px-4 py-3 text-right">
                            Action
                        </th>

                    </tr>
                </thead>

                <!-- BODY -->
                <tbody class="divide-y divide-gray-100">

                    <tr v-for="device in paginatedDevices" :key="device.id" class="hover:bg-gray-50">

                        <!-- DEVICE -->
                        <td class="px-4 py-3">

                            <div class="flex flex-col">

                                <span class="font-medium">
                                    {{ device.divice_name }}
                                </span>

                                <span class="text-xs text-muted-foreground">
                                    {{ device.code }}
                                </span>

                            </div>

                        </td>

                        <!-- CLIENT -->
                        <td class="px-4 py-3">

                            {{ device.client?.company_name || '-' }}

                        </td>

                        <!-- TOTAL UNIT -->
                        <td class="px-4 py-3">

                            {{ device.total_unit }}U

                        </td>

                        <!-- POSITION -->
                        <td class="px-4 py-3">

                            <span v-if="device.start_unit && device.end_unit">
                                U{{ device.start_unit }}
                                -
                                U{{ device.end_unit }}
                            </span>

                            <span v-else>
                                -
                            </span>

                        </td>

                        <!-- POWER -->
                        <td class="px-4 py-3">

                            {{ device.power_usage || 0 }} A

                        </td>

                        <!-- WEIGHT -->
                        <td class="px-4 py-3">

                            {{ device.weight_usage || 0 }} Kg

                        </td>

                        <!-- STATUS -->
                        <td class="px-4 py-3">

                            <span :class="[
                                'inline-flex rounded-full px-3 py-1 text-xs font-medium',
                                device.status === 'active'
                                    ? 'bg-green-100 text-green-700'
                                    : device.status === 'maintenance'
                                        ? 'bg-yellow-100 text-yellow-700'
                                        : 'bg-gray-100 text-gray-700'
                            ]">
                                {{ device.status }}
                            </span>

                        </td>

                        <!-- ACTIONS -->
                        <td class="px-4 py-3 text-right">
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

        <!-- PAGINATION -->
        <div class="flex items-center justify-between mt-4">
            <div class="text-sm text-muted-foreground">
                Showing
                {{ (currentPage - 1) * perPage + 1 }}
                -
                {{ Math.min(currentPage * perPage, filteredDevices.length) }}
                of
                {{ filteredDevices.length }}
                devices
            </div>
            <div>
                <div v-if="totalPages > 1" class="flex items-center gap-2">
                    <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                        class="rounded-lg border px-3 py-2 text-sm disabled:opacity-50">
                        Previous
                    </button>

                    <button v-for="page in totalPages" :key="page" @click="goToPage(page)" :class="[
                        'rounded-lg px-3 py-2 text-sm border',
                        currentPage === page
                            ? 'bg-black text-white'
                            : 'bg-white'
                    ]">
                        {{ page }}
                    </button>

                    <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                        class="rounded-lg border px-3 py-2 text-sm disabled:opacity-50">
                        Next
                    </button>
                </div>

            </div>
        </div>

    </div>

    <!-- Modal Ports -->
    <div v-if="openPorts && selectedDevice" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="openPorts = false"></div>

        <!-- Modal -->
        <div class="relative w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">
            <!-- Header -->
            <div class="flex items-center justify-between border-b px-6 py-5">
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40">
                            <Network class="h-5 w-5" />
                        </div>
                        <div>
                            <h2 class="text-base font-semibold">
                                View Device Ports
                            </h2>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ selectedDevice.divice_name }}
                            </p>
                        </div>
                    </div>
                </div>
                <button type="button" @click="openPorts = false" class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Summary -->
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

            <!-- Port List -->
            <div class="max-h-[60vh] overflow-y-auto px-6 py-5">
                <!-- Empty -->
                <div v-if="!selectedDevice.ports?.length" class="flex flex-col items-center justify-center rounded-2xl border border-dashed py-16 text-center">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <Network class="h-6 w-6 text-gray-400" />
                    </div>
                    <h3 class="text-sm font-semibold">No ports configured</h3>
                </div>

                <!-- Table -->
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
                                <!-- Port -->
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
                                <!-- Type -->
                                <td class="px-4 py-3"><span class="capitalize">{{ port.port_type?.replace('_', ' ') }}</span></td>
                                <!-- Connector -->
                                <td class="px-4 py-3 text-muted-foreground">{{ port.connector_type || '-' }}</td>
                                <!-- Status -->
                                <td class="px-4 py-3">
                                    <span v-if="port.status === 'available'" class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-950/30 dark:text-green-400">Available</span>
                                    <span v-else-if="port.status === 'connected'" class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/30 dark:text-blue-400">Connected</span>
                                    <span v-else-if="port.status === 'maintenance'" class="inline-flex rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400">Maintenance</span>
                                    <span v-else class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-400 capitalize">{{ port.status }}</span>
                                </td>
                                <!-- Connection Detail -->
                                <td class="px-4 py-3">
                                    <div v-if="port.active_source_cross_connect" class="flex flex-col text-xs">
                                        <div class="flex items-center gap-1 text-gray-600">
                                            <ArrowRight class="w-3 h-3 text-blue-500" />
                                            <span class="font-medium truncate max-w-[150px]" :title="port.active_source_cross_connect.destination_port?.device?.divice_name">
                                                {{ port.active_source_cross_connect.destination_port?.device?.divice_name || port.active_source_cross_connect.external_device_name || 'Unknown Device' }}<span v-if="port.active_source_cross_connect.external_location" class="ml-1 text-[10px] text-blue-600">(MMR {{ port.active_source_cross_connect.external_location }})</span>
                                            </span>
                                        </div>
                                        <div class="text-gray-400 ml-4">
                                            Port: {{ port.active_source_cross_connect.destination_port?.port_name || port.active_source_cross_connect.external_port_name || '?' }}
                                        </div>
                                    </div>
                                    <div v-else-if="port.active_destination_cross_connect" class="flex flex-col text-xs">
                                        <div class="flex items-center gap-1 text-gray-600">
                                            <ArrowRight class="w-3 h-3 text-green-500" />
                                            <span class="font-medium truncate max-w-[150px]" :title="port.active_destination_cross_connect.source_port?.device?.divice_name">
                                                {{ port.active_destination_cross_connect.source_port?.device?.divice_name || 'Unknown Device' }}
                                            </span>
                                        </div>
                                        <div class="text-gray-400 ml-4">
                                            Port: {{ port.active_destination_cross_connect.source_port?.port_name || '?' }}
                                        </div>
                                    </div>
                                    <div v-else class="text-gray-400 text-xs">
                                        -
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</template>