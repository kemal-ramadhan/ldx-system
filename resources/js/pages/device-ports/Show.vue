<script setup lang="ts">
import { computed } from 'vue'
import {
    ArrowLeft,
    Cable,
    CheckCircle2,
    CircleAlert,
    CircleOff,
    Network,
    Server,
    MapPin,
    Cpu,
    Plug,
    Wrench,
    ExternalLink,
} from 'lucide-vue-next'

interface Client {
    id: number
    company_code?: string
    company_name: string
    company_email?: string | null
}

interface LocationDataCenter {
    id: number
    name?: string
    code?: string
}

interface Room {
    id: number
    name?: string
    code?: string
    location_data_center?: LocationDataCenter
}

interface Rack {
    id: number
    code: string
    room?: Room
}

interface Device {
    id: number
    code: string
    divice_name: string
    divice_type?: string | null
    model?: string | null
    serial_number?: string | null
    client?: Client
    rack?: Rack
}

interface DevicePort {
    id: number
    rack_divice_id: number
    port_name: string
    port_number?: string | null
    port_type: string
    connector_type?: string | null
    status: 'available' | 'connected' | 'disabled' | 'maintenance'
    description?: string | null
    device?: Device
}

interface User {
    id: number
    name: string
    email?: string
}

interface CrossConnect {
    id: number
    cross_connect_number: string

    source_port_id: number
    destination_port_id: number

    source_port?: DevicePort
    destination_port?: DevicePort

    cable_type: string
    connector_type?: string | null
    cable_length?: string | number | null
    cable_length_unit: string

    status: string

    installed_at?: string | null
    terminated_at?: string | null

    installer?: User

    description?: string | null
    notes?: string | null
}

const props = defineProps<{
    title: string
    devicePort: DevicePort
    activeCrossConnect?: CrossConnect | null
    connectionSide?: 'source' | 'destination' | null
}>()

const port = computed(() => props.devicePort)

const device = computed(() => port.value.device)

const activeCrossConnect = computed(
    () => props.activeCrossConnect
)

const isConnected = computed(
    () => port.value.status === 'connected'
)

const statusLabel = computed(() => {
    switch (port.value.status) {
        case 'available':
            return 'Available'

        case 'connected':
            return 'Connected'

        case 'disabled':
            return 'Disabled'

        case 'maintenance':
            return 'Maintenance'

        default:
            return port.value.status
    }
})

const statusClass = computed(() => {
    switch (port.value.status) {
        case 'available':
            return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'

        case 'connected':
            return 'bg-blue-50 text-blue-700 ring-blue-600/20'

        case 'disabled':
            return 'bg-slate-100 text-slate-600 ring-slate-500/20'

        case 'maintenance':
            return 'bg-amber-50 text-amber-700 ring-amber-600/20'

        default:
            return 'bg-slate-100 text-slate-600 ring-slate-500/20'
    }
})

const cableLabel = (
    type?: string | null
) => {
    switch (type) {
        case 'fiber_optic':
            return 'Fiber Optic'

        case 'copper':
            return 'Copper'

        case 'coaxial':
            return 'Coaxial'

        default:
            return 'Other'
    }
}

const formatDateTime = (
    date?: string | null
) => {
    if (!date) {
        return '-'
    }

    return new Date(date).toLocaleString(
        'en-GB',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        }
    )
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Cross Connects',
                href: '/admin/cross-connects',
            },
            {
                title: 'Port Details',
                href: '#',
            },
        ],
    },
})
</script>

<template>

    <Head :title="title" />
    <div class="space-y-6 p-4">


        <!-- ================================================= -->
        <!-- HEADER -->
        <!-- ================================================= -->

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a :href="device
                        ? `/admin/rack-divices/${device.id}`
                        : '/admin'
                    "
                    class="mb-3 inline-flex items-center gap-1.5 text-sm text-muted-foreground transition hover:text-foreground">
                    <ArrowLeft class="h-4 w-4" />

                    Back to Device
                </a>

                <div class="flex items-center gap-3">

                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10">
                        <Network class="h-5 w-5 text-primary" />
                    </div>

                    <div>

                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                                {{ port.port_name }}
                            </h1>

                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                :class="statusClass">
                                {{ statusLabel }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Device port and connection information.
                        </p>

                    </div>
                </div>
            </div>
        </div>


        <!-- ================================================= -->
        <!-- CONNECTION STATUS -->
        <!-- ================================================= -->

        <div v-if="isConnected && activeCrossConnect"
            class="flex items-start gap-3 rounded-xl border border-blue-200 bg-blue-50 p-4">
            <CheckCircle2 class="mt-0.5 h-5 w-5 shrink-0 text-blue-600" />

            <div class="min-w-0">

                <p class="text-sm font-semibold text-blue-900">
                    Port is currently connected
                </p>

                <p class="mt-1 text-sm text-blue-700">
                    This port is currently being used by an active cross connect.
                </p>

                <a :href="`/admin/cross-connects/${activeCrossConnect.id}`
                    "
                    class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-blue-700 hover:text-blue-900">
                    {{
                        activeCrossConnect.cross_connect_number
                    }}

                    <ExternalLink class="h-3.5 w-3.5" />
                </a>

            </div>
        </div>


        <div v-else-if="port.status === 'available'"
            class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <CheckCircle2 class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />

            <div>
                <p class="text-sm font-semibold text-emerald-900">
                    Port is available
                </p>

                <p class="mt-1 text-sm text-emerald-700">
                    This port is not currently assigned to an active cross connect.
                </p>
            </div>
        </div>


        <div v-else-if="port.status === 'maintenance'"
            class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4">
            <Wrench class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />

            <div>
                <p class="text-sm font-semibold text-amber-900">
                    Port is under maintenance
                </p>

                <p class="mt-1 text-sm text-amber-700">
                    This port should not be used for new connections.
                </p>
            </div>
        </div>


        <div v-else-if="port.status === 'disabled'"
            class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <CircleOff class="mt-0.5 h-5 w-5 shrink-0 text-slate-600" />

            <div>
                <p class="text-sm font-semibold text-slate-900">
                    Port is disabled
                </p>

                <p class="mt-1 text-sm text-slate-600">
                    This port is currently disabled and cannot be used.
                </p>
            </div>
        </div>


        <!-- ================================================= -->
        <!-- PORT INFORMATION -->
        <!-- ================================================= -->

        <div class="grid gap-6 lg:grid-cols-2">

            <!-- PORT -->
            <div class="rounded-xl border border-border bg-card shadow-sm">
                <div class="border-b border-border px-5 py-4">
                    <div class="flex items-center gap-2">
                        <Plug class="h-4 w-4 text-primary" />

                        <h2 class="font-semibold text-foreground">
                            Port Information
                        </h2>
                    </div>
                </div>

                <div class="p-5">

                    <dl class="grid gap-5 sm:grid-cols-2">

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Port Name
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    port.port_name
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Port Number
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    port.port_number ??
                                    '-'
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Port Type
                            </dt>

                            <dd class="mt-1 text-sm font-medium capitalize text-foreground">
                                {{
                                    port.port_type
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Connector
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    port.connector_type ??
                                    '-'
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Status
                            </dt>

                            <dd class="mt-1">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="statusClass">
                                    {{ statusLabel }}
                                </span>
                            </dd>
                        </div>

                    </dl>

                    <div v-if="port.description" class="mt-6 border-t border-border pt-5">
                        <p class="text-xs text-muted-foreground">
                            Description
                        </p>

                        <p class="mt-2 whitespace-pre-line text-sm leading-6 text-foreground">
                            {{
                                port.description
                            }}
                        </p>
                    </div>

                </div>
            </div>


            <!-- DEVICE -->
            <div class="rounded-xl border border-border bg-card shadow-sm">
                <div class="border-b border-border px-5 py-4">
                    <div class="flex items-center gap-2">
                        <Server class="h-4 w-4 text-primary" />

                        <h2 class="font-semibold text-foreground">
                            Device
                        </h2>
                    </div>
                </div>

                <div class="p-5">

                    <dl class="space-y-5">

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Device Name
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    device?.divice_name ??
                                    '-'
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Device Code
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    device?.code ??
                                    '-'
                                }}
                            </dd>
                        </div>

                        <div v-if="device?.model">
                            <dt class="text-xs text-muted-foreground">
                                Model
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    device.model
                                }}
                            </dd>
                        </div>

                        <div v-if="device?.serial_number">
                            <dt class="text-xs text-muted-foreground">
                                Serial Number
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    device.serial_number
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Client
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    device?.client?.company_name ??
                                    '-'
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Rack
                            </dt>

                            <dd class="mt-1 flex items-center gap-2 text-sm font-medium text-foreground">
                                <MapPin class="h-4 w-4 text-muted-foreground" />

                                {{
                                    device?.rack?.code ??
                                    '-'
                                }}
                            </dd>
                        </div>

                    </dl>

                </div>
            </div>
        </div>


        <!-- ================================================= -->
        <!-- ACTIVE CROSS CONNECT -->
        <!-- ================================================= -->

        <div v-if="activeCrossConnect" class="rounded-xl border border-border bg-card shadow-sm">

            <div
                class="flex flex-col gap-3 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

                <div>

                    <p class="text-xs text-muted-foreground">
                        Active Connection
                    </p>

                    <h2 class="mt-1 font-semibold text-foreground">
                        {{
                            activeCrossConnect.cross_connect_number
                        }}
                    </h2>

                </div>

                <a :href="`/admin/cross-connects/${activeCrossConnect.id}`
                    "
                    class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-border px-3 py-1.5 text-sm font-medium transition hover:bg-muted">
                    View Cross Connect

                    <ExternalLink class="h-3.5 w-3.5" />
                </a>

            </div>


            <div class="p-5">

                <div class="grid gap-6 lg:grid-cols-[1fr_auto_1fr]">

                    <!-- CURRENT PORT -->
                    <div class="rounded-xl border border-primary/20 bg-primary/5 p-5">

                        <div class="mb-4 flex items-center gap-2">
                            <Network class="h-4 w-4 text-primary" />

                            <span class="text-sm font-semibold text-foreground">
                                Current Port
                            </span>
                        </div>

                        <p class="font-medium text-foreground">
                            {{
                                port.device?.client?.company_name ??
                                '-'
                            }}
                        </p>

                        <p class="mt-1 text-sm text-muted-foreground">
                            {{
                                port.device?.divice_name ??
                                '-'
                            }}
                        </p>

                        <p class="mt-1 text-sm font-medium text-foreground">
                            {{
                                port.port_name
                            }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            Rack
                            {{
                                port.device?.rack?.code ??
                                '-'
                            }}
                        </p>

                    </div>


                    <!-- CONNECTION -->
                    <div class="flex items-center justify-center">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-full border border-border bg-muted">
                            <Cable class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </div>


                    <!-- REMOTE PORT -->
                    <div class="rounded-xl border border-border bg-background p-5">

                        <div class="mb-4 flex items-center gap-2">
                            <Server class="h-4 w-4 text-primary" />

                            <span class="text-sm font-semibold text-foreground">
                                Connected To
                            </span>
                        </div>

                        <template v-if="
                            props.connectionSide ===
                            'source'
                        ">

                            <p class="font-medium text-foreground">
                                {{
                                    activeCrossConnect
                                        .destination_port
                                        ?.device
                                        ?.client
                                        ?.company_name ??
                                    '-'
                                }}
                            </p>

                            <p class="mt-1 text-sm text-muted-foreground">
                                {{
                                    activeCrossConnect
                                        .destination_port
                                        ?.device
                                        ?.divice_name ??
                                    '-'
                                }}
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    activeCrossConnect
                                        .destination_port
                                        ?.port_name ??
                                    '-'
                                }}
                            </p>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Rack
                                {{
                                    activeCrossConnect
                                        .destination_port
                                        ?.device
                                        ?.rack
                                        ?.code ??
                                    '-'
                                }}
                            </p>

                        </template>

                        <template v-else>

                            <p class="font-medium text-foreground">
                                {{
                                    activeCrossConnect
                                        .source_port
                                        ?.device
                                        ?.client
                                        ?.company_name ??
                                    '-'
                                }}
                            </p>

                            <p class="mt-1 text-sm text-muted-foreground">
                                {{
                                    activeCrossConnect
                                        .source_port
                                        ?.device
                                        ?.divice_name ??
                                    '-'
                                }}
                            </p>

                            <p class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    activeCrossConnect
                                        .source_port
                                        ?.port_name ??
                                    '-'
                                }}
                            </p>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Rack
                                {{
                                    activeCrossConnect
                                        .source_port
                                        ?.device
                                        ?.rack
                                        ?.code ??
                                    '-'
                                }}
                            </p>

                        </template>

                    </div>

                </div>
            </div>
        </div>


        <!-- ================================================= -->
        <!-- NO CONNECTION -->
        <!-- ================================================= -->

        <div v-else class="rounded-xl border border-dashed border-border bg-card p-8 text-center">

            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-muted">
                <Cable class="h-6 w-6 text-muted-foreground" />
            </div>

            <h3 class="mt-4 text-sm font-semibold text-foreground">
                No Active Cross Connect
            </h3>

            <p class="mx-auto mt-1 max-w-md text-sm text-muted-foreground">
                This port is currently not associated with an active cross connect.
            </p>

        </div>


        <!-- ================================================= -->
        <!-- CONNECTION INFORMATION -->
        <!-- ================================================= -->

        <div v-if="activeCrossConnect" class="grid gap-6 lg:grid-cols-2">

            <!-- CABLE -->
            <div class="rounded-xl border border-border bg-card shadow-sm">

                <div class="border-b border-border px-5 py-4">
                    <div class="flex items-center gap-2">
                        <Cable class="h-4 w-4 text-primary" />

                        <h2 class="font-semibold text-foreground">
                            Connection Information
                        </h2>
                    </div>
                </div>

                <div class="p-5">

                    <dl class="grid gap-5 sm:grid-cols-2">

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Cable Type
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    cableLabel(
                                        activeCrossConnect.cable_type
                                    )
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Connector
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    activeCrossConnect.connector_type ??
                                    '-'
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Cable Length
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    activeCrossConnect.cable_length ??
                                    '-'
                                }}

                                {{
                                    activeCrossConnect.cable_length
                                        ? activeCrossConnect.cable_length_unit
                                        : ''
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Connection Side
                            </dt>

                            <dd class="mt-1 text-sm font-medium capitalize text-foreground">
                                {{
                                    props.connectionSide ??
                                    '-'
                                }}
                            </dd>
                        </div>

                    </dl>

                </div>
            </div>


            <!-- INSTALLATION -->
            <div class="rounded-xl border border-border bg-card shadow-sm">

                <div class="border-b border-border px-5 py-4">
                    <div class="flex items-center gap-2">
                        <Cpu class="h-4 w-4 text-primary" />

                        <h2 class="font-semibold text-foreground">
                            Installation
                        </h2>
                    </div>
                </div>

                <div class="p-5">

                    <dl class="space-y-5">

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Installed By
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    activeCrossConnect
                                        .installer
                                        ?.name ??
                                    '-'
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Installed At
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                {{
                                    formatDateTime(
                                        activeCrossConnect
                                            .installed_at
                                    )
                                }}
                            </dd>
                        </div>

                    </dl>

                </div>
            </div>

        </div>

    </div>
</template>