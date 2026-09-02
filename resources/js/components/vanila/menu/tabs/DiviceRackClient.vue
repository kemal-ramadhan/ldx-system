<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import {
    Plus,
    Search,
    Trash2,
    Pencil,
    X
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

</template>