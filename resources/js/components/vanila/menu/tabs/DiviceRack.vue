<script setup lang="ts">
import { ref, computed } from 'vue'
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

/**
 * MODAL
 */
const openAddDevice = ref(false)

/**
 * FORM
 */
const deviceForm = useForm({
    client_id: '',
    divice_name: '',
    divice_type: '',
    model: '',
    serial_number: '',
    description: '',
    power_usage: '',
    weight_usage: '',
    ip_address: '',
    total_unit: '',
    status: 'active',
})

/**
 * SUBMIT
 */
const submitDevice = () => {

    deviceForm.post(`/admin/racks/${props.rack.id}/devices`, {

        preserveScroll: true,

        onSuccess: () => {

            openAddDevice.value = false

            deviceForm.reset()
        }
    })
}

const openEdit = ref(false)

const editForm = useForm({
    id: null,

    divice_name: '',
    divice_type: '',

    model: '',
    serial_number: '',

    description: '',

    power_usage: '',
    weight_usage: '',

    ip_address: '',

    status: 'active',
})

const openEditModal = (device: any) => {

    editForm.id = device.id

    editForm.divice_name = device.divice_name
    editForm.divice_type = device.divice_type

    editForm.model = device.model
    editForm.serial_number = device.serial_number

    editForm.description = device.description

    editForm.power_usage = device.power_usage
    editForm.weight_usage = device.weight_usage

    editForm.ip_address = device.ip_address

    editForm.status = device.status

    openEdit.value = true
}

const submitEdit = () => {

    editForm.put(
        `/admin/racks/devices/${editForm.id}`,
        {
            preserveScroll: true,

            onSuccess: () => {

                toast.success('Device updated successfully.')

                openEdit.value = false
            },

            onError: (errors) => {

                Object.values(errors).forEach((message: any) => {

                    toast.error(String(message))
                })
            }
        }
    )
}

const form = useForm<{ user_id: number | null }>({
    user_id: null
})

const deleteDevice = (id: number) => {

    if (!confirm('Delete this device?')) {
        return
    }

    form.delete(`/admin/racks/devices/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Device deleted successfully.')
        }
    })
}

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

            <button @click="openAddDevice = true"
                class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90">
                <Plus class="w-4 h-4 mr-2" />
                Add Device
            </button>
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
        <div class="rounded-2xl border bg-white dark:bg-transparent shadow-sm overflow-x-auto">

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
                            Actions
                        </th>

                    </tr>
                </thead>

                <!-- BODY -->
                <tbody class="divide-y divide-gray-100">

                    <tr v-for="device in filteredDevices" :key="device.id" class="hover:bg-gray-50">

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

                        <!-- ACTION -->
                        <td class="px-4 py-3">

                            <div class="flex justify-end gap-2">

                                <button @click="openEditModal(device)" class="rounded-lg p-2 hover:bg-gray-100">
                                    <Pencil class="w-4 h-4" />
                                </button>

                                <button
    type="button"
    @click="deleteDevice(device.id)"
    class="rounded-lg p-2 text-red-500 hover:bg-red-50"
>
    <Trash2 class="w-4 h-4" />
</button>

                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

        <!-- MODAL -->
        <div v-if="openAddDevice"
            class="absolute md:fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

            <div class="w-full max-w-3xl rounded-2xl bg-white dark:bg-gray-900 shadow-xl">

                <!-- HEADER -->
                <div class="flex items-center justify-between border-b p-5">

                    <div>
                        <h2 class="text-lg font-semibold">
                            Add Device
                        </h2>

                        <p class="text-sm text-muted-foreground">
                            Add new device into rack
                        </p>
                    </div>

                    <button @click="openAddDevice = false" class="rounded-lg p-2 hover:bg-gray-100">
                        <X class="w-5 h-5" />
                    </button>

                </div>

                <!-- BODY -->
                <form @submit.prevent="submitDevice" class="grid grid-cols-1 gap-4 p-5 md:grid-cols-2">

                    <!-- CLIENT -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Client
                        </label>

                        <select v-model="deviceForm.client_id" class="w-full rounded-xl border px-4 py-3 text-sm"
                            required>
                            <option value="">
                                Select Client
                            </option>

                            <option v-for="item in props.rack.client_racks" :key="item.client.id"
                                :value="item.client.id">
                                {{ item.client.company_name }}
                                ({{ item.rented_units }}U)
                            </option>
                        </select>
                    </div>

                    <!-- DEVICE NAME -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Device Name <span class="text-red-500">*</span>
                        </label>

                        <input v-model="deviceForm.divice_name" type="text"
                            class="w-full rounded-xl border px-4 py-3 text-sm" placeholder="Device name" required />
                    </div>

                    <!-- TYPE -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Device Type <span class="text-red-500">*</span>
                        </label>

                        <input v-model="deviceForm.divice_type" type="text" placeholder="Server / Router / Switch"
                            class="w-full rounded-xl border px-4 py-3 text-sm" />
                    </div>

                    <!-- MODEL -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Model
                        </label>

                        <input v-model="deviceForm.model" type="text" class="w-full rounded-xl border px-4 py-3 text-sm"
                            placeholder="Model Divice" />
                    </div>

                    <!-- SERIAL -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Serial Number
                        </label>

                        <input v-model="deviceForm.serial_number" type="text"
                            class="w-full rounded-xl border px-4 py-3 text-sm" placeholder="Serial Number" />
                    </div>

                    <!-- POWER -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Power Usage (A)
                        </label>

                        <input v-model="deviceForm.power_usage" type="number"
                            class="w-full rounded-xl border px-4 py-3 text-sm" placeholder="100 Ampere (A)" />
                    </div>

                    <!-- WEIGHT -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            Weight Usage (Kg)
                        </label>

                        <input v-model="deviceForm.weight_usage" type="number"
                            class="w-full rounded-xl border px-4 py-3 text-sm" placeholder="2 Kg (Kilogram)" />
                    </div>

                    <!-- IP -->
                    <div>
                        <label class="mb-2 block text-sm font-medium">
                            IP Address
                        </label>

                        <input v-model="deviceForm.ip_address" type="text"
                            class="w-full rounded-xl border px-4 py-3 text-sm" placeholder="162.000.000.00" />
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">
                            Description
                        </label>

                        <textarea v-model="deviceForm.description" rows="4"
                            class="w-full rounded-xl border px-4 py-3 text-sm" />
                    </div>

                    <!-- TOTAL UNIT -->
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-medium">
                            Total Unit (U) <span class="text-red-500">*</span>
                        </label>

                        <input v-model="deviceForm.total_unit" type="number"
                            class="w-full rounded-xl border px-4 py-3 text-sm" required placeholder="2" />
                    </div>

                    <!-- FOOTER -->
                    <div class="md:col-span-2 flex justify-end gap-3">

                        <button type="button" @click="openAddDevice = false"
                            class="rounded-xl border px-4 py-2 text-sm">
                            Cancel
                        </button>

                        <button type="submit" :disabled="deviceForm.processing"
                            class="rounded-xl bg-primary px-5 py-2 text-sm text-white">
                            {{
                                deviceForm.processing
                                    ? 'Saving...'
                                    : 'Save Device'
                            }}
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- UPDATE MODAL -->
    <div v-if="openEdit" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

        <div class="w-full max-w-2xl rounded-2xl bg-white p-6 shadow-xl">

            <!-- HEADER -->
            <div class="mb-6 flex items-center justify-between">

                <div>
                    <h2 class="text-lg font-semibold">
                        Update Device
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Update rack device information
                    </p>
                </div>

                <button @click="openEdit = false" class="rounded-lg p-2 hover:bg-gray-100">
                    ✕
                </button>

            </div>

            <!-- FORM -->
            <form @submit.prevent="submitEdit" class="space-y-4">

                <div class="grid grid-cols-2 gap-3">

                    <!-- DEVICE NAME -->
                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Device Name
                        </label>

                        <input v-model="editForm.divice_name" type="text"
                            class="w-full rounded-xl border px-4 py-3 text-sm" required />

                    </div>

                    <!-- TYPE -->
                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Device Type
                        </label>

                        <input v-model="editForm.divice_type" type="text"
                            class="w-full rounded-xl border px-4 py-3 text-sm" />

                    </div>

                    <!-- MODEL -->
                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Model
                        </label>

                        <input v-model="editForm.model" type="text"
                            class="w-full rounded-xl border px-4 py-3 text-sm" />

                    </div>

                    <!-- SERIAL -->
                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Serial Number
                        </label>

                        <input v-model="editForm.serial_number" type="text"
                            class="w-full rounded-xl border px-4 py-3 text-sm" />

                    </div>

                    <!-- POWER -->
                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Power Usage
                        </label>

                        <input v-model="editForm.power_usage" type="number"
                            class="w-full rounded-xl border px-4 py-3 text-sm" />

                    </div>

                    <!-- WEIGHT -->
                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Weight Usage
                        </label>

                        <input v-model="editForm.weight_usage" type="number"
                            class="w-full rounded-xl border px-4 py-3 text-sm" />

                    </div>

                    <!-- IP -->
                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            IP Address
                        </label>

                        <input v-model="editForm.ip_address" type="text"
                            class="w-full rounded-xl border px-4 py-3 text-sm" />

                    </div>

                    <!-- STATUS -->
                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Status
                        </label>

                        <select v-model="editForm.status" class="w-full rounded-xl border px-4 py-3 text-sm">
                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                            <option value="maintenance">
                                Maintenance
                            </option>
                        </select>

                    </div>
                </div>

                <!-- ACTION -->
                <div class="flex justify-end gap-3 pt-4">

                    <button type="button" @click="openEdit = false" class="rounded-xl border px-4 py-2 text-sm">
                        Cancel
                    </button>

                    <button type="submit" class="rounded-xl bg-black px-4 py-2 text-sm text-white">
                        Update Device
                    </button>

                </div>

            </form>

        </div>

    </div>
</template>