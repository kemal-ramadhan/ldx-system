<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Cable, Info, Save } from 'lucide-vue-next'
import { toast } from 'vue-sonner'

/**
 * Client portal: create interconnection request.
 * Same concept as the admin form (internal / MMR DC APJII S3-K1, cable data),
 * but the client can only use ITS OWN devices & ports. Connections to other
 * clients are requested through the LDX team.
 */
defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Interconnections', href: '/client/interconnections' },
            { title: 'Create Request', href: '/client/interconnections/create' },
        ],
    },
})

interface Port {
    id: number
    rack_divice_id: number
    port_name: string
    port_number: number | string | null
    port_type?: string
    status: string
}

interface Device {
    id: number
    rack_id: number
    code: string
    divice_name: string
    rack?: { id: number; code?: string; name?: string } | null
    ports?: Port[]
}

const props = defineProps<{
    devices: Device[]
    externalLocations: string[]
}>()

const form = useForm({
    // UI-only cascade helpers
    source_rack_id: '' as number | '',
    source_device_id: '' as number | '',
    destination_rack_id: '' as number | '',
    destination_device_id: '' as number | '',

    source_port_id: '' as number | '',
    destination_type: 'internal' as 'internal' | 'external',
    destination_port_id: '' as number | '',

    external_location: '',
    external_client_name: '',
    external_rack_name: '',
    external_device_name: '',
    external_port_name: '',

    interconnection_type: 'internal_building',
    cable_type: 'fiber_optic',
    connector_type: '',
    cable_length: '' as number | '',
    cable_length_unit: 'meter',
    description: '',
    notes: '',
})

/*
|--------------------------------------------------------------------------
| OWN RACK -> DEVICE -> PORT (shared by source & internal destination)
|--------------------------------------------------------------------------
*/
const racks = computed(() => {
    const map = new Map<number, { id: number; label: string }>()

    props.devices.forEach((device) => {
        if (device.rack_id && !map.has(device.rack_id)) {
            map.set(device.rack_id, {
                id: device.rack_id,
                label: device.rack?.code || device.rack?.name || `Rack #${device.rack_id}`,
            })
        }
    })

    return [...map.values()]
})

const devicesInRack = (rackId: number | '') =>
    rackId ? props.devices.filter((d) => d.rack_id === Number(rackId)) : []

const portsOfDevice = (deviceId: number | '', excludePortId: number | '' = '') => {
    if (!deviceId) return []

    const device = props.devices.find((d) => d.id === Number(deviceId))

    return (device?.ports ?? []).filter((p) => p.id !== Number(excludePortId))
}

const sourceDevices = computed(() => devicesInRack(form.source_rack_id))
const sourcePorts = computed(() => portsOfDevice(form.source_device_id))
const destinationDevices = computed(() => devicesInRack(form.destination_rack_id))
const destinationPorts = computed(() => portsOfDevice(form.destination_device_id, form.source_port_id))

const portLabel = (port: Port) => (port.port_number ? `${port.port_name} — #${port.port_number}` : port.port_name)

const changeSourceRack = () => {
    form.source_device_id = ''
    form.source_port_id = ''
}

const changeSourceDevice = () => {
    form.source_port_id = ''
}

const changeDestinationRack = () => {
    form.destination_device_id = ''
    form.destination_port_id = ''
}

const changeDestinationDevice = () => {
    form.destination_port_id = ''
}

// a port can't be both ends
watch(
    () => form.source_port_id,
    (id) => {
        if (id && form.destination_port_id === id) form.destination_port_id = ''
    },
)

/*
|--------------------------------------------------------------------------
| MMR DC APJII (same defaults as admin)
|--------------------------------------------------------------------------
*/
const MMR_DEFAULT_CLIENT_NAME = 'MMR DC APJII'
const externalRackOption = ref<'OTB' | 'other'>('OTB')

watch(
    () => form.destination_type,
    (type) => {
        if (type !== 'external') return

        if (!form.external_client_name) form.external_client_name = MMR_DEFAULT_CLIENT_NAME
        if (externalRackOption.value === 'OTB') form.external_rack_name = 'OTB'
    },
)

const changeExternalRackOption = () => {
    form.external_rack_name = externalRackOption.value === 'OTB' ? 'OTB' : ''
}

/*
|--------------------------------------------------------------------------
| SUBMIT
|--------------------------------------------------------------------------
*/
const hasPorts = computed(() => props.devices.some((d) => (d.ports ?? []).length > 0))

const submit = () => {
    form.post('/client/interconnections', {
        preserveScroll: true,
        onError: (errors) => {
            Object.values(errors).forEach((message) => toast.error(String(message)))
        },
    })
}

const inputClass = 'w-full rounded-xl border border-input bg-background px-4 py-3 text-sm'
const selectClass = `${inputClass} disabled:cursor-not-allowed disabled:bg-muted`
</script>

<template>
    <Head title="Create Interconnection Request" />

    <div class="p-4 text-foreground dark:scheme-dark">
        <!-- HEADER -->
        <div class="mb-6 flex items-center gap-3">
            <Link href="/client/interconnections" class="rounded-xl border p-2 text-muted-foreground hover:bg-muted">
                <ArrowLeft class="h-4 w-4" />
            </Link>

            <div>
                <h1 class="text-lg font-semibold">Create Interconnection Request</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Ajukan koneksi antar port milik perusahaan Anda, atau ke MMR DC APJII.
                </p>
            </div>
        </div>

        <div v-if="!hasPorts"
            class="mb-5 flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300">
            <Info class="mt-0.5 h-4 w-4 shrink-0" />
            Belum ada port tersedia pada device aktif milik Anda. Hubungi tim LDX untuk menambahkan port.
        </div>

        <!-- ENDPOINTS -->
        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
            <!-- SOURCE -->
            <div class="rounded-2xl border bg-card p-6 shadow-sm dark:bg-transparent">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-950/40 dark:text-blue-300">
                        <Cable class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold">Source</h2>
                        <p class="text-xs text-muted-foreground">Port milik Anda</p>
                    </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Rack</label>
                        <select v-model="form.source_rack_id" @change="changeSourceRack" :class="selectClass">
                            <option value="">Select Rack</option>
                            <option v-for="rack in racks" :key="rack.id" :value="rack.id">{{ rack.label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Device</label>
                        <select v-model="form.source_device_id" @change="changeSourceDevice" :disabled="!form.source_rack_id"
                            :class="selectClass">
                            <option value="">Select Device</option>
                            <option v-for="device in sourceDevices" :key="device.id" :value="device.id">
                                {{ device.divice_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Port <span class="text-red-500">*</span></label>
                        <select v-model="form.source_port_id" :disabled="!form.source_device_id" :class="selectClass">
                            <option value="">Select Available Port</option>
                            <option v-for="port in sourcePorts" :key="port.id" :value="port.id">{{ portLabel(port) }}</option>
                        </select>
                        <p class="mt-1 text-xs text-muted-foreground">Only available ports are displayed.</p>
                        <p v-if="form.errors.source_port_id" class="mt-1 text-xs text-red-500">{{ form.errors.source_port_id }}</p>
                    </div>
                </div>
            </div>

            <!-- DESTINATION -->
            <div class="rounded-2xl border bg-card p-6 shadow-sm dark:bg-transparent">
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600 dark:bg-green-950/40 dark:text-green-300">
                        <Cable class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold">Destination</h2>
                        <p class="text-xs text-muted-foreground">Destination endpoint</p>
                    </div>
                </div>

                <div class="mb-5 flex flex-wrap gap-4">
                    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium">
                        <input type="radio" v-model="form.destination_type" value="internal" class="h-4 w-4 accent-primary" />
                        Internal Data Center
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium">
                        <input type="radio" v-model="form.destination_type" value="external" class="h-4 w-4 accent-primary" />
                        MMR DC APJII
                    </label>
                </div>

                <!-- INTERNAL: own ports only -->
                <div v-if="form.destination_type === 'internal'" class="space-y-5">
                    <p class="flex items-start gap-2 rounded-xl bg-muted px-3 py-2 text-xs text-muted-foreground">
                        <Info class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                        Tujuan internal adalah port lain milik perusahaan Anda. Interconnection ke client lain diajukan
                        melalui tim LDX.
                    </p>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Rack</label>
                        <select v-model="form.destination_rack_id" @change="changeDestinationRack" :class="selectClass">
                            <option value="">Select Rack</option>
                            <option v-for="rack in racks" :key="rack.id" :value="rack.id">{{ rack.label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Device</label>
                        <select v-model="form.destination_device_id" @change="changeDestinationDevice"
                            :disabled="!form.destination_rack_id" :class="selectClass">
                            <option value="">Select Device</option>
                            <option v-for="device in destinationDevices" :key="device.id" :value="device.id">
                                {{ device.divice_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Port <span class="text-red-500">*</span></label>
                        <select v-model="form.destination_port_id" :disabled="!form.destination_device_id" :class="selectClass">
                            <option value="">Select Available Port</option>
                            <option v-for="port in destinationPorts" :key="port.id" :value="port.id">{{ portLabel(port) }}</option>
                        </select>
                        <p class="mt-1 text-xs text-muted-foreground">Port sumber tidak ditampilkan di sini.</p>
                        <p v-if="form.errors.destination_port_id" class="mt-1 text-xs text-red-500">
                            {{ form.errors.destination_port_id }}
                        </p>
                    </div>
                </div>

                <!-- EXTERNAL: MMR DC APJII -->
                <div v-else class="space-y-5">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Lokasi MMR <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2">
                            <button v-for="location in externalLocations" :key="location" type="button"
                                @click="form.external_location = location" :class="[
                                    'rounded-xl border px-4 py-3 text-sm font-medium transition-all',
                                    form.external_location === location
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'border-input bg-background hover:bg-muted dark:bg-transparent',
                                ]">
                                {{ location }}
                            </button>
                        </div>
                        <p v-if="form.errors.external_location" class="mt-1 text-xs text-red-500">
                            {{ form.errors.external_location }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Client Name</label>
                        <input v-model="form.external_client_name" type="text" placeholder="e.g. PT Telekomunikasi"
                            :class="inputClass" />
                        <p class="mt-1 text-xs text-muted-foreground">Default: {{ MMR_DEFAULT_CLIENT_NAME }}</p>
                        <p v-if="form.errors.external_client_name" class="mt-1 text-xs text-red-500">
                            {{ form.errors.external_client_name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Rack</label>
                        <select v-model="externalRackOption" @change="changeExternalRackOption" :class="selectClass">
                            <option value="OTB">OTB</option>
                            <option value="other">Lainnya</option>
                        </select>
                        <input v-if="externalRackOption === 'other'" v-model="form.external_rack_name" type="text"
                            placeholder="Masukkan nama rack" :class="[inputClass, 'mt-2']" />
                        <p v-if="form.errors.external_rack_name" class="mt-1 text-xs text-red-500">
                            {{ form.errors.external_rack_name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Device Name</label>
                        <input v-model="form.external_device_name" type="text" placeholder="e.g. Switch Core 1"
                            :class="inputClass" />
                        <p v-if="form.errors.external_device_name" class="mt-1 text-xs text-red-500">
                            {{ form.errors.external_device_name }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium">Port Name</label>
                        <input v-model="form.external_port_name" type="text" placeholder="e.g. eth0/1" :class="inputClass" />
                        <p v-if="form.errors.external_port_name" class="mt-1 text-xs text-red-500">
                            {{ form.errors.external_port_name }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CONNECTION DETAILS (same options as admin) -->
        <div class="mt-5 rounded-2xl border bg-card p-6 shadow-sm dark:bg-transparent">
            <div class="mb-6">
                <h2 class="text-sm font-semibold">Connection Details</h2>
                <p class="mt-1 text-xs text-muted-foreground">Specify the physical connection requirements.</p>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-medium">Interconnection Type</label>
                    <select v-model="form.interconnection_type" :class="selectClass">
                        <option value="internal_building">Internal Building</option>
                        <option value="cross_connect">Cross Connect</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">Cable Type</label>
                    <select v-model="form.cable_type" :class="selectClass">
                        <option value="fiber_optic">Fiber Optic</option>
                        <option value="copper">Copper</option>
                        <option value="coaxial">Coaxial</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">Connector Type</label>
                    <input v-model="form.connector_type" type="text" placeholder="Example: LC-LC Singlemode" :class="inputClass" />
                    <p v-if="form.errors.connector_type" class="mt-1 text-xs text-red-500">{{ form.errors.connector_type }}</p>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">Cable Length</label>
                    <div class="flex gap-2">
                        <input v-model="form.cable_length" type="number" min="0" step="0.01" placeholder="25" :class="inputClass" />
                        <select v-model="form.cable_length_unit" class="w-32 rounded-xl border border-input bg-background px-3 py-3 text-sm">
                            <option value="meter">Meter</option>
                            <option value="cm">CM</option>
                            <option value="km">KM</option>
                        </select>
                    </div>
                    <p v-if="form.errors.cable_length" class="mt-1 text-xs text-red-500">{{ form.errors.cable_length }}</p>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium">Description</label>
                    <textarea v-model="form.description" rows="4" placeholder="Describe the purpose of this interconnection..."
                        :class="inputClass"></textarea>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-sm font-medium">Notes</label>
                    <textarea v-model="form.notes" rows="3" placeholder="Additional notes..." :class="inputClass"></textarea>
                </div>
            </div>
        </div>

        <!-- FOOTER -->
        <div class="mt-5 flex justify-end gap-3">
            <Link href="/client/interconnections"
                class="inline-flex items-center rounded-xl border px-5 py-2.5 text-sm font-medium hover:bg-muted">
                Cancel
            </Link>

            <button type="button" @click="submit" :disabled="form.processing || !hasPorts"
                class="inline-flex items-center rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground shadow-sm hover:bg-primary/90 disabled:opacity-50">
                <Save class="mr-2 h-4 w-4" />
                {{ form.processing ? 'Submitting...' : 'Submit Request' }}
            </button>
        </div>
    </div>
</template>
