<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import {
    ArrowLeft,
    Cable,
    CheckCircle2,
    ChevronDown,
    Loader2,
    Server,
    Network,
    FileText,
} from 'lucide-vue-next'

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Interconnections',
                href: '/client/interconnections',
            },
            {
                title: 'Create Request',
                href: '/client/interconnections/create',
            },
        ],
    },
})

interface Rack {
    id: number
    name?: string
    code?: string
}

interface Port {
    id: number
    rack_divice_id: number
    port_name: string
    port_number: number | string
    port_type?: string
    connector_type?: string
    status: string
}

interface Device {
    id: number
    client_id: number
    rack_id: number
    code: string
    divice_name: string
    model?: string
    status: string
    rack?: Rack
    ports?: Port[]
}

interface Client {
    id: number
    company_code?: string
    company_name: string
}

const props = defineProps<{
    devices: Device[]
    clients: Client[]
}>()

/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

const form = useForm({
    source_port_id: '',
    destination_port_id: '',
    
    destination_type: 'internal',
    
    external_client_name: '',
    external_rack_name: '',
    external_device_name: '',
    external_port_name: '',

    interconnection_type: 'cross_connect',

    cable_type: 'fiber_optic',

    connector_type: '',

    cable_length: 1,

    cable_length_unit: 'meter',

    description: '',

    notes: '',
})

/*
|--------------------------------------------------------------------------
| SOURCE
|--------------------------------------------------------------------------
*/

const selectedSourceRackId = ref<string>('')
const selectedSourceDeviceId = ref<string>('')

const uniqueSourceRacks = computed(() => {
    const racks = new Map<number, Rack>()
    props.devices.forEach(device => {
        if (device.rack) {
            racks.set(device.rack.id, device.rack)
        }
    })
    return Array.from(racks.values())
})

const filteredSourceDevices = computed(() => {
    if (!selectedSourceRackId.value) {
        return []
    }
    return props.devices.filter(
        device => String(device.rack_id) === String(selectedSourceRackId.value)
    )
})

const selectedSourceDevice = computed(() => {
    return filteredSourceDevices.value.find(
        device =>
            String(device.id) ===
            String(selectedSourceDeviceId.value)
    )
})

const sourcePorts = computed(() => {
    return selectedSourceDevice.value?.ports ?? []
})

/*
|--------------------------------------------------------------------------
| DESTINATION
|--------------------------------------------------------------------------
*/

const selectedDestinationClientId =
    ref<string>('')

const selectedDestinationRackId =
    ref<string>('')

const selectedDestinationDeviceId =
    ref<string>('')

const destinationDevices =
    ref<Device[]>([])

const destinationPorts =
    ref<Port[]>([])

const loadingDestinationDevices =
    ref(false)

const loadingDestinationPorts =
    ref(false)

/*
|--------------------------------------------------------------------------
| LOAD DESTINATION DEVICES
|--------------------------------------------------------------------------
*/

const loadDestinationDevices = async () => {
    selectedDestinationRackId.value = ''
    selectedDestinationDeviceId.value = ''
    destinationDevices.value = []

    form.destination_port_id = ''

    if (!selectedDestinationClientId.value) {
        return
    }

    loadingDestinationDevices.value = true

    try {
        const response = await axios.get(
            `/client/interconnections/destination-clients/${selectedDestinationClientId.value}/devices`
        )

        destinationDevices.value =
            response.data
    } catch (error) {
        console.error(
            'Failed loading destination devices:',
            error
        )
    } finally {
        loadingDestinationDevices.value = false
    }
}

/*
|--------------------------------------------------------------------------
| LOAD DESTINATION PORTS
|--------------------------------------------------------------------------
*/

const loadDestinationPorts = async () => {
    selectedDestinationDeviceId.value =
        String(
            selectedDestinationDeviceId.value
        )

    destinationPorts.value = []

    form.destination_port_id = ''

    if (!selectedDestinationDeviceId.value) {
        return
    }

    loadingDestinationPorts.value = true

    try {
        const response = await axios.get(
            `/client/interconnections/devices/${selectedDestinationDeviceId.value}/ports`
        )

        destinationPorts.value =
            response.data
    } catch (error) {
        console.error(
            'Failed loading destination ports:',
            error
        )
    } finally {
        loadingDestinationPorts.value = false
    }
}

/*
|--------------------------------------------------------------------------
| WATCHERS
|--------------------------------------------------------------------------
*/

watch(
    selectedSourceRackId,
    () => {
        selectedSourceDeviceId.value = ''
        form.source_port_id = ''
    }
)

watch(
    selectedDestinationClientId,
    () => {
        loadDestinationDevices()
    }
)

const uniqueDestinationRacks = computed(() => {
    const racks = new Map<number, Rack>()
    destinationDevices.value.forEach(device => {
        if (device.rack) {
            racks.set(device.rack.id, device.rack)
        }
    })
    return Array.from(racks.values())
})

const filteredDestinationDevices = computed(() => {
    if (!selectedDestinationRackId.value) {
        return []
    }
    return destinationDevices.value.filter(
        device => String(device.rack_id) === String(selectedDestinationRackId.value)
    )
})

watch(
    selectedDestinationRackId,
    () => {
        selectedDestinationDeviceId.value = ''
        form.destination_port_id = ''
    }
)

watch(
    selectedDestinationDeviceId,
    () => {
        loadDestinationPorts()
    }
)

/*
|--------------------------------------------------------------------------
| SUBMIT
|--------------------------------------------------------------------------
*/

const submit = () => {
    form.post(
        '/client/interconnections',
        {
            preserveScroll: true,
        }
    )
}

/*
|--------------------------------------------------------------------------
| CAN SUBMIT
|--------------------------------------------------------------------------
*/

const canSubmit = computed(() => {
    if (!form.source_port_id) return false
    
    if (form.destination_type === 'internal' && !form.destination_port_id) return false
    
    if (form.destination_type === 'external') {
        if (!form.external_client_name || !form.external_rack_name || !form.external_device_name || !form.external_port_name) {
            return false
        }
    }

    return (
        form.interconnection_type &&
        form.cable_type &&
        form.connector_type &&
        form.cable_length
    )
})
</script>

<template>

    <Head title="Create Interconnection Request" />

    <div class="space-y-6 p-4">

        <!-- HEADER -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <Link href="/client/interconnections"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-border bg-background transition hover:bg-muted">
                        <ArrowLeft class="h-4 w-4" />
                    </Link>

                    <div>
                        <h1 class="text-xl font-semibold tracking-tight">
                            Create Interconnection
                        </h1>

                        <p class="text-sm text-muted-foreground">
                            Submit a new cross-connect request.
                        </p>
                    </div>
                </div>
            </div>
        </div>


        <!-- FORM -->
        <form @submit.prevent="submit" class="space-y-6">

            <!-- CONNECTION -->
            <div class="rounded-2xl border border-border bg-card shadow-sm">

                <div class="flex items-center gap-3 border-b border-border px-6 py-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                        <Network class="h-5 w-5 text-primary" />
                    </div>

                    <div>
                        <h2 class="font-semibold">
                            Connection
                        </h2>

                        <p class="text-sm text-muted-foreground">
                            Select source and destination ports.
                        </p>
                    </div>
                </div>


                <div class="grid gap-6 p-6 lg:grid-cols-2">

                    <!-- SOURCE -->
                    <div class="space-y-4">

                        <div>
                            <h3 class="text-sm font-semibold">
                                Source
                            </h3>

                            <p class="text-xs text-muted-foreground">
                                Your device and port.
                            </p>
                        </div>


                        <!-- SOURCE RACK -->
                        <div class="space-y-2">

                            <label class="text-sm font-medium">
                                Rack
                            </label>

                            <div class="relative">

                                <Server
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                                <select v-model="selectedSourceRackId"
                                    class="h-11 w-full appearance-none rounded-xl border border-input bg-background pl-10 pr-10 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10">
                                    <option value="">
                                        Select source rack
                                    </option>

                                    <option v-for="rack in uniqueSourceRacks" :key="rack.id" :value="rack.id">
                                        {{ rack.name ?? rack.code }}
                                    </option>
                                </select>

                                <ChevronDown
                                    class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                            </div>

                        </div>

                        <!-- SOURCE DEVICE -->
                        <div class="space-y-2">

                            <label class="text-sm font-medium">
                                Device
                            </label>

                            <div class="relative">

                                <Server
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                                <select v-model="selectedSourceDeviceId"
                                    :disabled="!selectedSourceRackId"
                                    class="h-11 w-full appearance-none rounded-xl border border-input bg-background pl-10 pr-10 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 disabled:cursor-not-allowed disabled:opacity-50">
                                    <option value="">
                                        Select source device
                                    </option>

                                    <option v-for="device in filteredSourceDevices" :key="device.id" :value="device.id">
                                        {{ device.code }}
                                        —
                                        {{ device.divice_name }}
                                    </option>
                                </select>

                                <ChevronDown
                                    class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                            </div>

                        </div>

                        <!-- SOURCE PORT -->
                        <div class="space-y-2">

                            <label class="text-sm font-medium">
                                Port
                            </label>

                            <select v-model="form.source_port_id" :disabled="!selectedSourceDeviceId"
                                class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 disabled:cursor-not-allowed disabled:opacity-50">

                                <option value="">
                                    Select source port
                                </option>

                                <option v-for="port in sourcePorts" :key="port.id" :value="port.id">
                                    {{ port.port_name }}
                                    — Port {{ port.port_number }}
                                    — {{ port.connector_type }}
                                </option>

                            </select>

                            <p v-if="form.errors.source_port_id" class="text-xs text-destructive">
                                {{ form.errors.source_port_id }}
                            </p>

                        </div>

                    </div>


                    <!-- DESTINATION -->
                    <div class="space-y-4">

                        <div>
                            <h3 class="text-sm font-semibold">
                                Destination
                            </h3>

                            <p class="text-xs text-muted-foreground">
                                Select the destination tenant and port.
                            </p>
                        </div>


                        <!-- TYPE SELECTION -->
                        <div class="mb-5 flex gap-4">
                            <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                                <input type="radio" v-model="form.destination_type" value="internal" class="h-4 w-4 text-primary" />
                                Internal Data Center
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium cursor-pointer">
                                <input type="radio" v-model="form.destination_type" value="external" class="h-4 w-4 text-primary" />
                                External Data Center
                            </label>
                        </div>
                        
                        <div v-if="form.destination_type === 'internal'" class="space-y-4">
                            <!-- DESTINATION CLIENT -->
                        <div class="space-y-2">

                            <label class="text-sm font-medium">
                                Destination Client
                            </label>

                            <select v-model="selectedDestinationClientId"
                                class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10">

                                <option value="">
                                    Select destination client
                                </option>

                                <option v-for="client in props.clients" :key="client.id" :value="client.id">
                                    {{ client.company_code }}
                                    —
                                    {{ client.company_name }}
                                </option>

                            </select>

                        </div>

                        <!-- DESTINATION RACK -->
                        <div class="space-y-2">

                            <label class="text-sm font-medium">
                                Rack
                            </label>

                            <div class="relative">

                                <Server
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                                <select v-model="selectedDestinationRackId"
                                    :disabled="!selectedDestinationClientId || loadingDestinationDevices"
                                    class="h-11 w-full appearance-none rounded-xl border border-input bg-background pl-10 pr-10 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 disabled:cursor-not-allowed disabled:opacity-50">
                                    
                                    <option value="" disabled selected v-if="loadingDestinationDevices">
                                        Loading racks...
                                    </option>

                                    <option value="" v-else>
                                        Select destination rack
                                    </option>

                                    <option v-for="rack in uniqueDestinationRacks" :key="rack.id" :value="rack.id">
                                        {{ rack.name ?? rack.code }}
                                    </option>

                                </select>

                                <Loader2 v-if="loadingDestinationDevices"
                                    class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-muted-foreground" />
                                
                                <ChevronDown v-else
                                    class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                            </div>

                        </div>


                        <!-- DESTINATION DEVICE -->
                        <div class="space-y-2">

                            <label class="text-sm font-medium">
                                Device
                            </label>

                            <div class="relative">

                                <Server
                                    class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                                <select v-model="selectedDestinationDeviceId"
                                    :disabled="!selectedDestinationRackId || loadingDestinationDevices"
                                    class="h-11 w-full appearance-none rounded-xl border border-input bg-background pl-10 pr-10 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 disabled:cursor-not-allowed disabled:opacity-50">

                                    <option value="" disabled selected v-if="loadingDestinationDevices">
                                        Loading devices...
                                    </option>

                                    <option value="" v-else>
                                        Select destination device
                                    </option>

                                    <option v-for="device in filteredDestinationDevices" :key="device.id"
                                        :value="device.id">
                                        {{ device.code }}
                                        —
                                        {{ device.divice_name }}
                                    </option>

                                </select>

                                <Loader2 v-if="loadingDestinationDevices"
                                    class="absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 animate-spin text-muted-foreground" />

                                <ChevronDown v-else
                                    class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                            </div>

                        </div>


                        <!-- DESTINATION PORT -->
                        <div class="space-y-2">

                            <label class="text-sm font-medium">
                                Port
                            </label>

                            <select v-model="form.destination_port_id" :disabled="!selectedDestinationDeviceId ||
                                loadingDestinationPorts
                                "
                                class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10 disabled:cursor-not-allowed disabled:opacity-50">

                                <option value="">
                                    {{
                                        loadingDestinationPorts
                                            ? 'Loading ports...'
                                            : 'Select destination port'
                                    }}
                                </option>

                                <option v-for="port in destinationPorts" :key="port.id" :value="port.id">
                                    {{ port.port_name }}
                                    — Port {{ port.port_number }}
                                    — {{ port.connector_type }}
                                </option>

                            </select>

                            <p v-if="form.errors.destination_port_id" class="text-xs text-destructive">
                                {{ form.errors.destination_port_id }}
                            </p>

                        </div>
                        
                        </div>
                        
                        <div v-if="form.destination_type === 'external'" class="space-y-4">
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Client Name</label>
                                <input v-model="form.external_client_name" type="text" placeholder="e.g. PT Telekomunikasi"
                                    class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10" />
                                <p v-if="form.errors.external_client_name" class="mt-1 text-xs text-destructive">
                                    {{ form.errors.external_client_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Rack Name</label>
                                <input v-model="form.external_rack_name" type="text" placeholder="e.g. Rack A1"
                                    class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10" />
                                <p v-if="form.errors.external_rack_name" class="mt-1 text-xs text-destructive">
                                    {{ form.errors.external_rack_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Device Name</label>
                                <input v-model="form.external_device_name" type="text" placeholder="e.g. Switch Core 1"
                                    class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10" />
                                <p v-if="form.errors.external_device_name" class="mt-1 text-xs text-destructive">
                                    {{ form.errors.external_device_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <label class="text-sm font-medium">Port Name</label>
                                <input v-model="form.external_port_name" type="text" placeholder="e.g. eth0/1"
                                    class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10" />
                                <p v-if="form.errors.external_port_name" class="mt-1 text-xs text-destructive">
                                    {{ form.errors.external_port_name }}
                                </p>
                            </div>
                        </div>

                    </div>

                </div>

            </div>


            <!-- CABLE -->
            <div class="rounded-2xl border border-border bg-card shadow-sm">

                <div class="flex items-center gap-3 border-b border-border px-6 py-5">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                        <Cable class="h-5 w-5 text-primary" />
                    </div>

                    <div>
                        <h2 class="font-semibold">
                            Cable Information
                        </h2>

                        <p class="text-sm text-muted-foreground">
                            Specify the physical cable requirements.
                        </p>
                    </div>

                </div>


                <div class="grid gap-6 p-6 md:grid-cols-2">

                    <!-- TYPE -->
                    <div class="space-y-2">

                        <label class="text-sm font-medium">
                            Interconnection Type
                        </label>

                        <select v-model="form.interconnection_type"
                            class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">
                            <option value="cross_connect">
                                Cross Connect
                            </option>

                            <option value="patch">
                                Patch Connection
                            </option>
                        </select>

                    </div>


                    <!-- CABLE -->
                    <div class="space-y-2">

                        <label class="text-sm font-medium">
                            Cable Type
                        </label>

                        <select v-model="form.cable_type"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="fiber_optic">Fiber Optic</option>
                            <option value="copper">Copper</option>
                            <option value="coaxial">Coaxial</option>
                            <option value="other">Other</option>
                        </select>

                    </div>


                    <!-- CONNECTOR -->
                    <div class="space-y-2">

                        <label class="text-sm font-medium">
                            Connector Type
                        </label>

                        <input v-model="form.connector_type" type="text" placeholder="Example: LC-LC"
                            class="h-11 w-full rounded-xl border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" />

                        <p v-if="form.errors.connector_type" class="text-xs text-destructive">
                            {{ form.errors.connector_type }}
                        </p>

                    </div>


                    <!-- LENGTH -->
                    <div class="space-y-2">

                        <label class="text-sm font-medium">
                            Cable Length
                        </label>

                        <div class="flex gap-2">

                            <input v-model="form.cable_length" type="number" min="0.1" step="0.1"
                                class="h-11 min-w-0 flex-1 rounded-xl border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10" />

                            <select v-model="form.cable_length_unit"
                                class="h-11 w-32 rounded-xl border border-input bg-background px-3 text-sm outline-none focus:border-primary focus:ring-2 focus:ring-primary/10">
                                <option value="meter">
                                    Meter
                                </option>

                                <option value="cm">
                                    CM
                                </option>
                            </select>

                        </div>

                    </div>

                </div>

            </div>


            <!-- DESCRIPTION -->
            <div class="rounded-2xl border border-border bg-card shadow-sm">

                <div class="flex items-center gap-3 border-b border-border px-6 py-5">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                        <FileText class="h-5 w-5 text-primary" />
                    </div>

                    <div>
                        <h2 class="font-semibold">
                            Request Details
                        </h2>

                        <p class="text-sm text-muted-foreground">
                            Add additional information for the Data Center team.
                        </p>
                    </div>

                </div>


                <div class="space-y-5 p-6">

                    <div class="space-y-2">

                        <label class="text-sm font-medium">
                            Description
                        </label>

                        <textarea v-model="form.description" rows="4"
                            placeholder="Describe the purpose of this interconnection..."
                            class="w-full resize-none rounded-xl border border-input bg-background px-3 py-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10" />

                        <p v-if="form.errors.description" class="text-xs text-destructive">
                            {{ form.errors.description }}
                        </p>

                    </div>


                    <div class="space-y-2">

                        <label class="text-sm font-medium">
                            Notes
                        </label>

                        <textarea v-model="form.notes" rows="3" placeholder="Additional notes..."
                            class="w-full resize-none rounded-xl border border-input bg-background px-3 py-3 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10" />

                    </div>

                </div>

            </div>


            <!-- ACTION -->
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">

                <Link href="/client/interconnections"
                    class="inline-flex h-11 items-center justify-center rounded-xl border border-border px-5 text-sm font-medium transition hover:bg-muted">
                    Cancel
                </Link>

                <button type="submit" :disabled="!canSubmit ||
                    form.processing
                    "
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-primary px-6 text-sm font-medium text-primary-foreground shadow-sm transition hover:bg-primary/90 disabled:pointer-events-none disabled:opacity-50">

                    <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />

                    <CheckCircle2 v-else class="h-4 w-4" />

                    {{
                        form.processing
                            ? 'Submitting...'
                            : 'Submit Request'
                    }}

                </button>

            </div>

        </form>

    </div>

</template>