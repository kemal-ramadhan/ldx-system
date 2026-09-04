<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm, usePage, router } from '@inertiajs/vue3'
import {
    Plus,
    Search,
    Trash2,
    Pencil,
    X,
    Network,
    Settings2,
    Cable,
    MoreHorizontal,
} from 'lucide-vue-next'
import { route } from 'ziggy-js'
import { Input } from '@/components/ui/input';
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

const openPorts = ref(false)

const selectedDevice = ref<any>(null)

const openManagePorts = (device: any) => {
    selectedDevice.value = device
    openPorts.value = true
}

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

const openGeneratePorts = ref(false)

const generatePortForm = useForm({
    total_ports: '',
    port_type: 'ethernet',
    connector_type: '',
})

const openAddPort = ref(false)

const portForm = useForm({
    port_name: '',
    port_number: '',
    port_type: 'ethernet',
    connector_type: '',
    status: 'available',
    description: '',
})

const openEditPort = ref(false)

const editPortForm = useForm({
    id: null as number | null,
    port_name: '',
    port_number: '',
    port_type: 'ethernet',
    connector_type: '',
    status: 'available',
    description: '',
})

const editPort = (port: any) => {
    editPortForm.id = port.id
    editPortForm.port_name = port.port_name
    editPortForm.port_number = port.port_number ?? ''
    editPortForm.port_type = port.port_type
    editPortForm.connector_type = port.connector_type ?? ''
    editPortForm.status = port.status
    editPortForm.description = port.description ?? ''

    openEditPort.value = true
}

const openGenerateModal = () => {
    generatePortForm.reset()

    generatePortForm.port_type = 'ethernet'
    generatePortForm.connector_type = ''

    openGeneratePorts.value = true
}

const submitGeneratePorts = () => {
    if (!selectedDevice.value) return

    generatePortForm.post(
        `/admin/racks/devices/${selectedDevice.value.id}/ports/generate`,
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success('Device ports generated successfully.')
                openGeneratePorts.value = false
                generatePortForm.reset()

                router.reload({
                    only: ['rack'],
                })
            },

            onError: (errors) => {
                Object.values(errors).forEach((message: any) => {
                    toast.error(String(message))
                })
            },
        }
    )
}

const submitPort = () => {
    if (!selectedDevice.value) return

    portForm.post(
        `/admin/racks/devices/${selectedDevice.value.id}/ports`,
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success('Device port added successfully.')
                openAddPort.value = false
                portForm.reset()

                router.reload({
                    only: ['rack'],
                })
            },

            onError: (errors) => {
                Object.values(errors).forEach((message: any) => {
                    toast.error(String(message))
                })
            },
        }
    )
}

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

const submitEditPort = () => {
    if (!editPortForm.id) return

    editPortForm.put(
        `/admin/racks/devices/ports/${editPortForm.id}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success('Device port updated successfully.')
                openEditPort.value = false

                router.reload({
                    only: ['rack'],

                    onSuccess: () => {
                        refreshSelectedDevice()
                    },
                })
            },

            onError: (errors) => {
                Object.values(errors).forEach((message: any) => {
                    toast.error(String(message))
                })
            },
        }
    )
}

const deletePort = (port: any) => {
    if (!confirm(`Delete ${port.port_name}?`)) return

    router.delete(
        `/admin/racks/devices/ports/${port.id}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success('Device port deleted successfully.')

                router.reload({
                    only: ['rack'],
                    onSuccess: () => {
                        refreshSelectedDevice()
                    },
                })
            },

            onError: (errors) => {
                Object.values(errors).forEach((message: any) => {
                    toast.error(String(message))
                })
            },
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

const refreshSelectedDevice = () => {
    if (!selectedDevice.value) return

    const updatedDevice = props.rack?.rack_divices?.find(
        (device: any) => device.id === selectedDevice.value.id
    )

    if (updatedDevice) {
        selectedDevice.value = updatedDevice
    }
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

                                <button type="button" @click="openManagePorts(device)"
                                    class="rounded-lg p-2 text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-950/30"
                                    title="Manage Ports">
                                    <Network class="w-4 h-4" />
                                </button>

                                <button @click="openEditModal(device)" class="rounded-lg p-2 hover:bg-gray-100">
                                    <Pencil class="w-4 h-4" />
                                </button>

                                <button type="button" @click="deleteDevice(device.id)"
                                    class="rounded-lg p-2 text-red-500 hover:bg-red-50">
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

    <!-- =========================================================
     MANAGE DEVICE PORTS
========================================================= -->

    <div v-if="openPorts && selectedDevice" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="openPorts = false"></div>

        <!-- Modal -->
        <div class="relative w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

            <!-- Header -->
            <div class="flex items-center justify-between border-b px-6 py-5">
                <div>
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40">
                            <Network class="h-5 w-5" />
                        </div>

                        <div>
                            <h2 class="text-base font-semibold">
                                Manage Device Ports
                            </h2>

                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ selectedDevice.divice_name }}
                            </p>
                        </div>
                    </div>
                </div>

                <button type="button" @click="openPorts = false"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <!-- Summary -->
            <div class="border-b bg-gray-50/70 px-6 py-4 dark:bg-gray-800/40">
                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-6">

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Total Ports
                            </p>

                            <p class="mt-1 text-lg font-semibold">
                                {{ selectedDevice.ports?.length || 0 }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Available
                            </p>

                            <p class="mt-1 text-lg font-semibold text-green-600">
                                {{
                                    selectedDevice.ports?.filter(
                                        (port: any) =>
                                            port.status === 'available'
                                    ).length || 0
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-muted-foreground">
                                Connected
                            </p>

                            <p class="mt-1 text-lg font-semibold text-blue-600">
                                {{
                                    selectedDevice.ports?.filter(
                                        (port: any) =>
                                            port.status === 'connected'
                                    ).length || 0
                                }}
                            </p>
                        </div>

                    </div>

                    <div class="flex items-center gap-2">

                        <button type="button" @click="openGenerateModal"
                            class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium hover:bg-white dark:hover:bg-gray-800">
                            <Settings2 class="h-4 w-4" />

                            Generate
                        </button>

                        <button type="button" @click="openAddPort = true"
                            class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90">
                            <Plus class="h-4 w-4" />

                            Add Port
                        </button>

                    </div>
                </div>
            </div>

            <!-- Port List -->
            <div class="max-h-[60vh] overflow-y-auto px-6 py-5">

                <!-- Empty -->
                <div v-if="!selectedDevice.ports?.length"
                    class="flex flex-col items-center justify-center rounded-2xl border border-dashed py-16 text-center">
                    <div
                        class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                        <Network class="h-6 w-6 text-gray-400" />
                    </div>

                    <h3 class="text-sm font-semibold">
                        No ports configured
                    </h3>

                    <p class="mt-1 max-w-sm text-xs text-muted-foreground">
                        Add ports manually or generate ports automatically
                        for this device.
                    </p>

                    <button type="button" @click="openGenerateModal"
                        class="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white">
                        <Settings2 class="h-4 w-4" />
                        Generate Ports
                    </button>
                </div>

                <!-- Table -->
                <div v-else class="overflow-hidden rounded-xl border">
                    <table class="w-full text-sm">

                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium">
                                    Port
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Type
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Connector
                                </th>

                                <th class="px-4 py-3 text-left font-medium">
                                    Status
                                </th>

                                <th class="px-4 py-3 text-right font-medium">
                                    Action
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y">

                            <tr v-for="port in selectedDevice.ports" :key="port.id"
                                class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50">

                                <!-- Port -->
                                <td class="px-4 py-3">

                                    <div class="flex items-center gap-3">

                                        <div
                                            class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                            <Cable class="h-4 w-4" />
                                        </div>

                                        <div>
                                            <p class="font-medium">
                                                {{ port.port_name }}
                                            </p>

                                            <p v-if="port.port_number" class="text-xs text-muted-foreground">
                                                #{{ port.port_number }}
                                            </p>
                                        </div>

                                    </div>

                                </td>

                                <!-- Type -->
                                <td class="px-4 py-3">
                                    <span class="capitalize">
                                        {{ port.port_type?.replace('_', ' ') }}
                                    </span>
                                </td>

                                <!-- Connector -->
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ port.connector_type || '-' }}
                                </td>

                                <!-- Status -->
                                <td class="px-4 py-3">

                                    <span v-if="port.status === 'available'"
                                        class="inline-flex rounded-full bg-green-50 px-2.5 py-1 text-xs font-medium text-green-700 dark:bg-green-950/30 dark:text-green-400">
                                        Available
                                    </span>

                                    <span v-else-if="port.status === 'connected'"
                                        class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 dark:bg-blue-950/30 dark:text-blue-400">
                                        Connected
                                    </span>

                                    <span v-else-if="port.status === 'maintenance'"
                                        class="inline-flex rounded-full bg-yellow-50 px-2.5 py-1 text-xs font-medium text-yellow-700 dark:bg-yellow-950/30 dark:text-yellow-400">
                                        Maintenance
                                    </span>

                                    <span v-else
                                        class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        Disabled
                                    </span>

                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-3">

                                    <div class="flex justify-end gap-1">

                                        <button type="button" @click="editPort(port)"
                                            class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800">
                                            <Pencil class="h-4 w-4" />
                                        </button>

                                        <button type="button" @click="deletePort(port)"
                                            class="rounded-lg p-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-950/30">
                                            <Trash2 class="h-4 w-4" />
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>
                </div>

            </div>

        </div>
    </div>

    <!-- =========================================================
     GENERATE PORTS
========================================================= -->

    <div v-if="openGeneratePorts" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="openGeneratePorts = false"></div>

        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

            <div class="flex items-center justify-between border-b px-6 py-5">

                <div>
                    <h2 class="text-base font-semibold">
                        Generate Ports
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Automatically create ports for this device.
                    </p>
                </div>

                <button type="button" @click="openGeneratePorts = false"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">
                    <X class="h-5 w-5" />
                </button>

            </div>

            <form @submit.prevent="submitGeneratePorts" class="space-y-5 p-6">

                <!-- Total -->
                <div class="space-y-2">

                    <Label>
                        Number of Ports
                    </Label>

                    <Input v-model="generatePortForm.total_ports" class="" type="number" min="1" max="1024"
                        placeholder="Example: 48" />

                    <p v-if="generatePortForm.errors.total_ports" class="text-xs text-red-500">
                        {{ generatePortForm.errors.total_ports }}
                    </p>

                </div>

                <!-- Type -->
                <div class="space-y-2">

                    <Label>
                        Port Type
                    </Label>

                    <select v-model="generatePortForm.port_type"
                        class="w-full rounded-xl border bg-background px-4 py-3 text-sm">
                        <option value="ethernet">
                            Ethernet
                        </option>

                        <option value="fiber">
                            Fiber
                        </option>

                        <option value="management">
                            Management
                        </option>

                        <option value="power">
                            Power
                        </option>

                        <option value="console">
                            Console
                        </option>

                        <option value="other">
                            Other
                        </option>
                    </select>

                </div>

                <!-- Connector -->
                <div class="space-y-2">

                    <Label>
                        Connector Type
                    </Label>

                    <Input v-model="generatePortForm.connector_type" placeholder="Example: LC, RJ45, SC" />

                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2 pt-2">

                    <button type="button" @click="openGeneratePorts = false"
                        class="rounded-xl border px-4 py-2 text-sm">
                        Cancel
                    </button>

                    <button type="submit" :disabled="generatePortForm.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                        <Spinner v-if="generatePortForm.processing" class="h-4 w-4" />

                        Generate
                    </button>

                </div>

            </form>

        </div>
    </div>

    <!-- =========================================================
     ADD PORT
========================================================= -->

    <div v-if="openAddPort" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="openAddPort = false"></div>

        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

            <div class="flex items-center justify-between border-b px-6 py-5">

                <div>
                    <h2 class="text-base font-semibold">
                        Add Device Port
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Add a port manually.
                    </p>
                </div>

                <button type="button" @click="openAddPort = false"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">
                    <X class="h-5 w-5" />
                </button>

            </div>

            <form @submit.prevent="submitPort" class="space-y-5 p-6">

                <!-- Port Name -->
                <div class="space-y-2">

                    <Label>
                        Port Name
                    </Label>

                    <Input v-model="portForm.port_name" placeholder="Example: Port 1" />

                    <p v-if="portForm.errors.port_name" class="text-xs text-red-500">
                        {{ portForm.errors.port_name }}
                    </p>

                </div>

                <!-- Port Number -->
                <div class="space-y-2">

                    <Label>
                        Port Number
                    </Label>

                    <Input v-model="portForm.port_number" placeholder="Example: 1" />

                </div>

                <!-- Type -->
                <div class="space-y-2">

                    <Label>
                        Port Type
                    </Label>

                    <select v-model="portForm.port_type"
                        class="w-full rounded-xl border bg-background px-4 py-3 text-sm">
                        <option value="ethernet">
                            Ethernet
                        </option>

                        <option value="fiber">
                            Fiber
                        </option>

                        <option value="management">
                            Management
                        </option>

                        <option value="power">
                            Power
                        </option>

                        <option value="console">
                            Console
                        </option>

                        <option value="other">
                            Other
                        </option>
                    </select>

                </div>

                <!-- Connector -->
                <div class="space-y-2">

                    <Label>
                        Connector Type
                    </Label>

                    <Input v-model="portForm.connector_type" placeholder="Example: LC / RJ45" />

                </div>

                <!-- Description -->
                <div class="space-y-2">

                    <Label>
                        Description
                    </Label>

                    <textarea v-model="portForm.description" rows="3"
                        class="w-full rounded-xl border bg-background px-4 py-3 text-sm"
                        placeholder="Optional description..."></textarea>

                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2 pt-2">

                    <button type="button" @click="openAddPort = false" class="rounded-xl border px-4 py-2 text-sm">
                        Cancel
                    </button>

                    <button type="submit" :disabled="portForm.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                        <Spinner v-if="portForm.processing" class="h-4 w-4" />

                        Save Port
                    </button>

                </div>

            </form>

        </div>
    </div>

    <!-- =========================================================
     EDIT PORT
========================================================= -->

    <div v-if="openEditPort" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="openEditPort = false"></div>

        <div class="relative w-full max-w-md rounded-2xl bg-white shadow-2xl dark:bg-gray-900">

            <div class="flex items-center justify-between border-b px-6 py-5">

                <div>
                    <h2 class="text-base font-semibold">
                        Edit Device Port
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Update port information.
                    </p>
                </div>

                <button type="button" @click="openEditPort = false"
                    class="rounded-lg p-2 text-gray-400 hover:bg-gray-100">
                    <X class="h-5 w-5" />
                </button>

            </div>

            <form @submit.prevent="submitEditPort" class="space-y-5 p-6">

                <div class="space-y-2">

                    <Label>
                        Port Name
                    </Label>

                    <Input v-model="editPortForm.port_name" />

                </div>

                <div class="space-y-2">

                    <Label>
                        Port Number
                    </Label>

                    <Input v-model="editPortForm.port_number" />

                </div>

                <div class="space-y-2">

                    <Label>
                        Port Type
                    </Label>

                    <select v-model="editPortForm.port_type"
                        class="w-full rounded-xl border bg-background px-4 py-3 text-sm">
                        <option value="ethernet">
                            Ethernet
                        </option>

                        <option value="fiber">
                            Fiber
                        </option>

                        <option value="management">
                            Management
                        </option>

                        <option value="power">
                            Power
                        </option>

                        <option value="console">
                            Console
                        </option>

                        <option value="other">
                            Other
                        </option>
                    </select>

                </div>

                <div class="space-y-2">

                    <Label>
                        Connector Type
                    </Label>

                    <Input v-model="editPortForm.connector_type" />

                </div>

                <div class="space-y-2">

                    <Label>
                        Status
                    </Label>

                    <select v-model="editPortForm.status"
                        class="w-full rounded-xl border bg-background px-4 py-3 text-sm">
                        <option value="available">
                            Available
                        </option>

                        <option value="connected">
                            Connected
                        </option>

                        <option value="disabled">
                            Disabled
                        </option>

                        <option value="maintenance">
                            Maintenance
                        </option>
                    </select>

                </div>

                <div class="flex justify-end gap-2 pt-2">

                    <button type="button" @click="openEditPort = false" class="rounded-xl border px-4 py-2 text-sm">
                        Cancel
                    </button>

                    <button type="submit" :disabled="editPortForm.processing"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white disabled:opacity-50">
                        <Spinner v-if="editPortForm.processing" class="h-4 w-4" />

                        Update Port
                    </button>

                </div>

            </form>

        </div>
    </div>


</template>