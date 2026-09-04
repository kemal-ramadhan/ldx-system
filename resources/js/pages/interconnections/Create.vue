<script setup lang="ts">

import { computed, ref } from 'vue'

import {
    Head,
    Link,
    useForm,
} from '@inertiajs/vue3'

import {
    ArrowLeft,
    ArrowRight,
    Cable,
    Save,
} from 'lucide-vue-next'

import { toast } from 'vue-sonner'

const props = defineProps<{
    title: string
    clients: any[]
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Interconnections',
                href: '/admin/interconnections',
            },
            {
                title: 'Create',
                href: '/admin/interconnections/create',
            },
        ],
    },
})

/*
|--------------------------------------------------------------------------
| FORM
|--------------------------------------------------------------------------
*/

const form = useForm({

    requester_client_id: '',

    source_client_id: '',
    source_rack_id: '',
    source_device_id: '',
    source_port_id: '',

    destination_client_id: '',
    destination_rack_id: '',
    destination_device_id: '',
    destination_port_id: '',

    interconnection_type: 'internal_building',

    cable_type: 'fiber_optic',

    connector_type: '',

    cable_length: '',

    cable_length_unit: 'meter',

    description: '',

    notes: '',
})


/*
|--------------------------------------------------------------------------
| SOURCE
|--------------------------------------------------------------------------
*/

const sourceClient = computed(() => {

    return props.clients.find(
        client => String(client.id) === String(form.source_client_id)
    )

})

const sourceRacks = computed(() => {

    return sourceClient.value?.client_racks || []

})

const sourceRack = computed(() => {

    return sourceRacks.value.find(
        (item: any) => String(item.rack_id) === String(form.source_rack_id)
    )?.rack

})

const sourceDevices = computed(() => {

    return sourceRack.value?.rack_divices || []

})

const sourceDevice = computed(() => {

    return sourceDevices.value.find(
        (device: any) => String(device.id) === String(form.source_device_id)
    )

})

const sourcePorts = computed(() => {

    return sourceDevice.value?.ports?.filter(
        (port: any) => port.status === 'available'
    ) || []

})


/*
|--------------------------------------------------------------------------
| DESTINATION
|--------------------------------------------------------------------------
*/

const destinationClient = computed(() => {

    return props.clients.find(
        client => String(client.id) === String(form.destination_client_id)
    )

})

const destinationRacks = computed(() => {

    return destinationClient.value?.client_racks || []

})

const destinationRack = computed(() => {

    return destinationRacks.value.find(
        (item: any) => String(item.rack_id) === String(form.destination_rack_id)
    )?.rack

})

const destinationDevices = computed(() => {

    return destinationRack.value?.rack_divices || []

})

const destinationDevice = computed(() => {

    return destinationDevices.value.find(
        (device: any) => String(device.id) === String(form.destination_device_id)
    )

})

const destinationPorts = computed(() => {

    return destinationDevice.value?.ports?.filter(
        (port: any) => port.status === 'available'
    ) || []

})


/*
|--------------------------------------------------------------------------
| CHANGE HANDLERS
|--------------------------------------------------------------------------
*/

const changeSourceClient = () => {

    form.source_rack_id = ''
    form.source_device_id = ''
    form.source_port_id = ''

}

const changeSourceRack = () => {

    form.source_device_id = ''
    form.source_port_id = ''

}

const changeSourceDevice = () => {

    form.source_port_id = ''

}

const changeDestinationClient = () => {

    form.destination_rack_id = ''
    form.destination_device_id = ''
    form.destination_port_id = ''

}

const changeDestinationRack = () => {

    form.destination_device_id = ''
    form.destination_port_id = ''

}

const changeDestinationDevice = () => {

    form.destination_port_id = ''

}


/*
|--------------------------------------------------------------------------
| SUBMIT
|--------------------------------------------------------------------------
*/

const submit = () => {

    form.post(
        '/admin/interconnections',
        {
            preserveScroll: true,

            onSuccess: () => {

                toast.success(
                    'Interconnection request created successfully.'
                )

            },

            onError: (errors) => {

                Object.values(errors).forEach(
                    (message: any) => {
                        toast.error(String(message))
                    }
                )

            },
        }
    )

}

</script>


<template>

    <Head :title="title" />

    <div class="p-4">

        <!-- HEADER -->

        <div class="mb-6 flex items-center gap-3">

            <Link href="/admin/interconnections" class="rounded-xl border p-2 text-gray-500 hover:bg-gray-50">

                <ArrowLeft class="h-4 w-4" />

            </Link>

            <div>

                <h1 class="text-lg font-semibold">
                    Create Interconnection Request
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Create a connection request between two device ports.
                </p>

            </div>

        </div>


        <!-- REQUESTER -->

        <div class="mb-5 rounded-2xl border bg-white p-6 shadow-sm dark:bg-transparent">

            <div class="mb-5">

                <h2 class="text-sm font-semibold">
                    Request Information
                </h2>

                <p class="mt-1 text-xs text-muted-foreground">
                    Select the client requesting this interconnection.
                </p>

            </div>

            <div class="max-w-xl">

                <label class="mb-2 block text-sm font-medium">
                    Requester Client
                </label>

                <select v-model="form.requester_client_id"
                    class="w-full rounded-xl border bg-background px-4 py-3 text-sm">

                    <option value="">
                        Select Client
                    </option>

                    <option v-for="client in props.clients" :key="client.id" :value="client.id">
                        {{ client.company_name }}
                    </option>

                </select>

                <p v-if="form.errors.requester_client_id" class="mt-1 text-xs text-red-500">
                    {{ form.errors.requester_client_id }}
                </p>

            </div>

        </div>


        <!-- ENDPOINTS -->

        <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">


            <!-- SOURCE -->

            <div class="rounded-2xl border bg-white p-6 shadow-sm dark:bg-transparent">

                <div class="mb-6 flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">

                        <Cable class="h-5 w-5" />

                    </div>

                    <div>

                        <h2 class="text-sm font-semibold">
                            Source
                        </h2>

                        <p class="text-xs text-muted-foreground">
                            Starting endpoint
                        </p>

                    </div>

                </div>


                <div class="space-y-5">


                    <!-- CLIENT -->

                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Client
                        </label>

                        <select v-model="form.source_client_id" @change="changeSourceClient"
                            class="w-full rounded-xl border bg-background px-4 py-3 text-sm">

                            <option value="">
                                Select Client
                            </option>

                            <option v-for="client in props.clients" :key="client.id" :value="client.id">
                                {{ client.company_name }}
                            </option>

                        </select>

                    </div>


                    <!-- RACK -->

                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Rack
                        </label>

                        <select v-model="form.source_rack_id" @change="changeSourceRack"
                            :disabled="!form.source_client_id"
                            class="w-full rounded-xl border bg-background px-4 py-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-50">

                            <option value="">
                                Select Rack
                            </option>

                            <option v-for="item in sourceRacks" :key="item.rack_id" :value="item.rack_id">
                                {{ item.rack?.code }}
                            </option>

                        </select>

                    </div>


                    <!-- DEVICE -->

                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Device
                        </label>

                        <select v-model="form.source_device_id" @change="changeSourceDevice"
                            :disabled="!form.source_rack_id"
                            class="w-full rounded-xl border bg-background px-4 py-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-50">

                            <option value="">
                                Select Device
                            </option>

                            <option v-for="device in sourceDevices" :key="device.id" :value="device.id">
                                {{ device.divice_name }}
                            </option>

                        </select>

                    </div>


                    <!-- PORT -->

                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Port
                        </label>

                        <select v-model="form.source_port_id" :disabled="!form.source_device_id"
                            class="w-full rounded-xl border bg-background px-4 py-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-50">

                            <option value="">
                                Select Available Port
                            </option>

                            <option v-for="port in sourcePorts" :key="port.id" :value="port.id">
                                {{ port.port_name }}
                                <template v-if="port.port_number">
                                    — #{{ port.port_number }}
                                </template>
                            </option>

                        </select>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Only available ports are displayed.
                        </p>

                    </div>

                </div>

            </div>


            <!-- DESTINATION -->

            <div class="rounded-2xl border bg-white p-6 shadow-sm dark:bg-transparent">

                <div class="mb-6 flex items-center gap-3">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600">

                        <Cable class="h-5 w-5" />

                    </div>

                    <div>

                        <h2 class="text-sm font-semibold">
                            Destination
                        </h2>

                        <p class="text-xs text-muted-foreground">
                            Destination endpoint
                        </p>

                    </div>

                </div>


                <div class="space-y-5">


                    <!-- CLIENT -->

                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Client
                        </label>

                        <select v-model="form.destination_client_id" @change="changeDestinationClient"
                            class="w-full rounded-xl border bg-background px-4 py-3 text-sm">

                            <option value="">
                                Select Client
                            </option>

                            <option v-for="client in props.clients" :key="client.id" :value="client.id">
                                {{ client.company_name }}
                            </option>

                        </select>

                    </div>


                    <!-- RACK -->

                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Rack
                        </label>

                        <select v-model="form.destination_rack_id" @change="changeDestinationRack"
                            :disabled="!form.destination_client_id"
                            class="w-full rounded-xl border bg-background px-4 py-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-50">

                            <option value="">
                                Select Rack
                            </option>

                            <option v-for="item in destinationRacks" :key="item.rack_id" :value="item.rack_id">
                                {{ item.rack?.code }}
                            </option>

                        </select>

                    </div>


                    <!-- DEVICE -->

                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Device
                        </label>

                        <select v-model="form.destination_device_id" @change="changeDestinationDevice"
                            :disabled="!form.destination_rack_id"
                            class="w-full rounded-xl border bg-background px-4 py-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-50">

                            <option value="">
                                Select Device
                            </option>

                            <option v-for="device in destinationDevices" :key="device.id" :value="device.id">
                                {{ device.divice_name }}
                            </option>

                        </select>

                    </div>


                    <!-- PORT -->

                    <div>

                        <label class="mb-2 block text-sm font-medium">
                            Port
                        </label>

                        <select v-model="form.destination_port_id" :disabled="!form.destination_device_id"
                            class="w-full rounded-xl border bg-background px-4 py-3 text-sm disabled:cursor-not-allowed disabled:bg-gray-50">

                            <option value="">
                                Select Available Port
                            </option>

                            <option v-for="port in destinationPorts" :key="port.id" :value="port.id">
                                {{ port.port_name }}

                                <template v-if="port.port_number">
                                    — #{{ port.port_number }}
                                </template>

                            </option>

                        </select>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Only available ports are displayed.
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <!-- CONNECTION DETAILS -->

        <div class="mt-5 rounded-2xl border bg-white p-6 shadow-sm dark:bg-transparent">

            <div class="mb-6">

                <h2 class="text-sm font-semibold">
                    Connection Details
                </h2>

                <p class="mt-1 text-xs text-muted-foreground">
                    Specify the physical connection requirements.
                </p>

            </div>


            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">


                <!-- TYPE -->

                <div>

                    <label class="mb-2 block text-sm font-medium">
                        Interconnection Type
                    </label>

                    <select v-model="form.interconnection_type"
                        class="w-full rounded-xl border bg-background px-4 py-3 text-sm">

                        <option value="internal_building">
                            Internal Building
                        </option>

                        <option value="cross_connect">
                            Cross Connect
                        </option>

                        <option value="other">
                            Other
                        </option>

                    </select>

                </div>


                <!-- CABLE -->

                <div>

                    <label class="mb-2 block text-sm font-medium">
                        Cable Type
                    </label>

                    <select v-model="form.cable_type" class="w-full rounded-xl border bg-background px-4 py-3 text-sm">

                        <option value="fiber_optic">
                            Fiber Optic
                        </option>

                        <option value="copper">
                            Copper
                        </option>

                        <option value="coaxial">
                            Coaxial
                        </option>

                        <option value="other">
                            Other
                        </option>

                    </select>

                </div>


                <!-- CONNECTOR -->

                <div>

                    <label class="mb-2 block text-sm font-medium">
                        Connector Type
                    </label>

                    <input v-model="form.connector_type" type="text" placeholder="Example: LC-LC Singlemode"
                        class="w-full rounded-xl border px-4 py-3 text-sm" />

                </div>


                <!-- LENGTH -->

                <div>

                    <label class="mb-2 block text-sm font-medium">
                        Cable Length
                    </label>

                    <div class="flex gap-2">

                        <input v-model="form.cable_length" type="number" min="0" step="0.01" placeholder="25"
                            class="w-full rounded-xl border px-4 py-3 text-sm" />

                        <select v-model="form.cable_length_unit"
                            class="w-32 rounded-xl border bg-background px-3 py-3 text-sm">

                            <option value="meter">
                                Meter
                            </option>

                            <option value="cm">
                                CM
                            </option>

                            <option value="km">
                                KM
                            </option>

                        </select>

                    </div>

                </div>


                <!-- DESCRIPTION -->

                <div class="md:col-span-2">

                    <label class="mb-2 block text-sm font-medium">
                        Description
                    </label>

                    <textarea v-model="form.description" rows="4"
                        placeholder="Describe the purpose of this interconnection..."
                        class="w-full rounded-xl border px-4 py-3 text-sm"></textarea>

                </div>


                <!-- NOTES -->

                <div class="md:col-span-2">

                    <label class="mb-2 block text-sm font-medium">
                        Notes
                    </label>

                    <textarea v-model="form.notes" rows="3" placeholder="Additional notes..."
                        class="w-full rounded-xl border px-4 py-3 text-sm"></textarea>

                </div>

            </div>

        </div>


        <!-- FOOTER -->

        <div class="mt-5 flex justify-end gap-3">

            <Link href="/admin/interconnections"
                class="inline-flex items-center rounded-xl border px-5 py-2.5 text-sm font-medium hover:bg-gray-50">
                Cancel
            </Link>

            <button type="button" @click="submit" :disabled="form.processing"
                class="inline-flex items-center rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-primary/90 disabled:opacity-50">

                <Save class="mr-2 h-4 w-4" />

                {{
                    form.processing
                        ? 'Submitting...'
                        : 'Submit Request'
                }}

            </button>

        </div>

    </div>

</template>