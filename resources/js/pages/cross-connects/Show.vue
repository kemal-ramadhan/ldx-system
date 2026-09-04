<script setup lang="ts">
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import {
    ArrowLeft,
    Cable,
    CheckCircle2,
    CircleAlert,
    Clock3,
    Server,
    Network,
    User,
    CalendarDays,
    MapPin,
    ShieldCheck,
    XCircle,
    Ban,
    ExternalLink,
} from 'lucide-vue-next'
import { toast } from 'vue-sonner'

interface Client {
    id: number
    company_code: string
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
    port_name: string
    port_number?: string | null
    port_type: string
    connector_type?: string | null
    status: string
    device?: Device
}

interface User {
    id: number
    name: string
    email?: string
}

interface InterconnectionRequest {
    id: number
    request_number: string
    requester_client?: Client
    destination_approver?: User
    dc_approver?: User
    status: string
    progress: number
    requested_at?: string | null
    started_at?: string | null
    completed_at?: string | null
}

interface CrossConnect {
    id: number
    cross_connect_number: string
    interconnection_request_id?: number | null

    source_port_id: number
    destination_port_id: number

    source_port?: DevicePort
    destination_port?: DevicePort

    interconnection_request?: InterconnectionRequest

    cable_type: string
    connector_type?: string | null
    cable_length?: string | number | null
    cable_length_unit: string

    status: 'planned' | 'active' | 'terminated' | 'cancelled'

    installed_by?: number | null
    installed_at?: string | null
    terminated_at?: string | null

    installer?: User

    description?: string | null
    notes?: string | null
}

const props = defineProps<{
    title: string
    crossConnect: CrossConnect
}>()

const item = computed(() => props.crossConnect)

const source = computed(
    () => item.value.source_port
)

const destination = computed(
    () => item.value.destination_port
)

const request = computed(
    () => item.value.interconnection_request
)

const terminateModal = ref(false)

const terminateForm = useForm({
    reason: '',
})

/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

const statusLabel = computed(() => {
    switch (item.value.status) {
        case 'active':
            return 'Active'

        case 'planned':
            return 'Planned'

        case 'terminated':
            return 'Terminated'

        case 'cancelled':
            return 'Cancelled'

        default:
            return item.value.status
    }
})

const statusClass = computed(() => {
    switch (item.value.status) {
        case 'active':
            return 'bg-emerald-50 text-emerald-700 ring-emerald-600/20'

        case 'planned':
            return 'bg-amber-50 text-amber-700 ring-amber-600/20'

        case 'terminated':
            return 'bg-slate-100 text-slate-600 ring-slate-500/20'

        case 'cancelled':
            return 'bg-red-50 text-red-700 ring-red-600/20'

        default:
            return 'bg-slate-100 text-slate-600 ring-slate-500/20'
    }
})

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

const cableLabel = (type?: string | null) => {
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

const formatDate = (
    date?: string | null
) => {
    if (!date) {
        return '-'
    }

    return new Date(date).toLocaleDateString(
        'en-GB',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    )
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

/*
|--------------------------------------------------------------------------
| Terminate
|--------------------------------------------------------------------------
*/

const openTerminateModal = () => {
    terminateForm.reset()
    terminateForm.clearErrors()

    terminateModal.value = true
}

const closeTerminateModal = () => {
    if (terminateForm.processing) {
        return
    }

    terminateModal.value = false
}

const terminateCrossConnect = () => {
    if (!terminateForm.reason.trim()) {
        terminateForm.setError(
            'reason',
            'Termination reason is required.'
        )

        return
    }

    terminateForm.patch(
        `/admin/cross-connects/${item.value.id}/terminate`,
        {
            preserveScroll: true,

            onSuccess: () => {
                terminateModal.value = false

                toast.success(
                    'Cross connect terminated successfully.'
                )
            },

            onError: (errors) => {
                const firstError =
                    Object.values(errors)[0]

                toast.error(
                    firstError ??
                    'Failed to terminate cross connect.'
                )
            },
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
                title: 'Detail',
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
                <a href="/admin/cross-connects"
                    class="mb-3 inline-flex items-center gap-1.5 text-sm text-muted-foreground transition hover:text-foreground">
                    <ArrowLeft class="h-4 w-4" />
                    Back to Cross Connects
                </a>

                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary/10">
                        <Cable class="h-5 w-5 text-primary" />
                    </div>

                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                                {{
                                    item.cross_connect_number
                                }}
                            </h1>

                            <span
                                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                :class="statusClass">
                                {{ statusLabel }}
                            </span>
                        </div>

                        <p class="mt-1 text-sm text-muted-foreground">
                            Cross connect connection details and operational information.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ACTION -->
            <div v-if="item.status === 'active'" class="flex items-center gap-2">
                <button type="button"
                    class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 transition hover:bg-red-100"
                    @click="openTerminateModal">
                    <Ban class="h-4 w-4" />
                    Terminate
                </button>
            </div>
        </div>


        <!-- ================================================= -->
        <!-- STATUS BANNER -->
        <!-- ================================================= -->

        <div v-if="item.status === 'active'"
            class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
            <CheckCircle2 class="mt-0.5 h-5 w-5 shrink-0 text-emerald-600" />

            <div>
                <p class="text-sm font-semibold text-emerald-900">
                    Cross Connect is active
                </p>

                <p class="mt-1 text-sm text-emerald-700">
                    This connection is currently active and both ports are reserved as connected.
                </p>
            </div>
        </div>

        <div v-if="item.status === 'terminated'"
            class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
            <CircleAlert class="mt-0.5 h-5 w-5 shrink-0 text-slate-600" />

            <div>
                <p class="text-sm font-semibold text-slate-900">
                    Cross Connect has been terminated
                </p>

                <p class="mt-1 text-sm text-slate-600">
                    The connection is no longer active and the associated ports have been released.
                </p>
            </div>
        </div>


        <!-- ================================================= -->
        <!-- CONNECTION -->
        <!-- ================================================= -->

        <div class="rounded-xl border border-border bg-card shadow-sm">
            <div class="border-b border-border px-5 py-4">
                <div class="flex items-center gap-2">
                    <Network class="h-4 w-4 text-primary" />

                    <h2 class="font-semibold text-foreground">
                        Connection
                    </h2>
                </div>
            </div>

            <div class="p-5">

                <div class="grid gap-6 lg:grid-cols-[1fr_auto_1fr]">

                    <!-- SOURCE -->
                    <div class="rounded-xl border border-border bg-background p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                                    <Server class="h-4 w-4 text-primary" />
                                </div>

                                <span class="text-sm font-semibold text-foreground">
                                    Source
                                </span>
                            </div>

                            <span class="text-xs text-muted-foreground">
                                Port
                                {{
                                    source?.id ?? '-'
                                }}
                            </span>
                        </div>

                        <div class="space-y-3">

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Client
                                </p>

                                <p class="mt-1 font-medium text-foreground">
                                    {{
                                        source?.device?.client?.company_name ??
                                        '-'
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Device
                                </p>

                                <p class="mt-1 font-medium text-foreground">
                                    {{
                                        source?.device?.divice_name ??
                                        source?.device?.code ??
                                        '-'
                                    }}
                                </p>

                                <p v-if="source?.device?.code" class="mt-1 text-xs text-muted-foreground">
                                    {{
                                        source.device.code
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Rack
                                </p>

                                <p class="mt-1 font-medium text-foreground">
                                    {{
                                        source?.device?.rack?.code ??
                                        '-'
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Port
                                </p>

                                <a v-if="source" :href="`/admin/device-ports/${source.id}`"
                                    class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline">
                                    {{ source.port_name }}

                                    <ExternalLink class="h-3.5 w-3.5" />
                                </a>

                                <span v-else class="mt-1 text-sm font-medium text-foreground">
                                    -
                                </span>

                                <p v-if="source?.port_number" class="mt-1 text-xs text-muted-foreground">
                                    Port Number:
                                    {{
                                        source.port_number
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Port Status
                                </p>

                                <span
                                    class="mt-1 inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">
                                    {{
                                        source?.status ??
                                        '-'
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>


                    <!-- CENTER -->
                    <div class="flex items-center justify-center">
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-full border border-border bg-muted">
                            <Cable class="h-5 w-5 text-muted-foreground" />
                        </div>
                    </div>


                    <!-- DESTINATION -->
                    <div class="rounded-xl border border-border bg-background p-5">
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10">
                                    <Server class="h-4 w-4 text-primary" />
                                </div>

                                <span class="text-sm font-semibold text-foreground">
                                    Destination
                                </span>
                            </div>

                            <span class="text-xs text-muted-foreground">
                                Port
                                {{
                                    destination?.id ?? '-'
                                }}
                            </span>
                        </div>

                        <div class="space-y-3">

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Client
                                </p>

                                <p class="mt-1 font-medium text-foreground">
                                    {{
                                        destination?.device?.client?.company_name ??
                                        '-'
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Device
                                </p>

                                <p class="mt-1 font-medium text-foreground">
                                    {{
                                        destination?.device?.divice_name ??
                                        destination?.device?.code ??
                                        '-'
                                    }}
                                </p>

                                <p v-if="destination?.device?.code" class="mt-1 text-xs text-muted-foreground">
                                    {{
                                        destination.device.code
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Rack
                                </p>

                                <p class="mt-1 font-medium text-foreground">
                                    {{
                                        destination?.device?.rack?.code ??
                                        '-'
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Port
                                </p>

                                <a v-if="destination" :href="`/admin/device-ports/${destination.id}`"
                                    class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium text-primary hover:underline">
                                    {{ destination.port_name }}

                                    <ExternalLink class="h-3.5 w-3.5" />
                                </a>

                                <span v-else class="mt-1 text-sm font-medium text-foreground">
                                    -
                                </span>

                                <p v-if="destination?.port_number" class="mt-1 text-xs text-muted-foreground">
                                    Port Number:
                                    {{
                                        destination.port_number
                                    }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-muted-foreground">
                                    Port Status
                                </p>

                                <span
                                    class="mt-1 inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">
                                    {{
                                        destination?.status ??
                                        '-'
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>


        <!-- ================================================= -->
        <!-- CONNECTION INFORMATION -->
        <!-- ================================================= -->

        <div class="grid gap-6 lg:grid-cols-2">

            <!-- CABLE -->
            <div class="rounded-xl border border-border bg-card shadow-sm">
                <div class="border-b border-border px-5 py-4">
                    <div class="flex items-center gap-2">
                        <Cable class="h-4 w-4 text-primary" />

                        <h2 class="font-semibold text-foreground">
                            Cable Information
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
                                        item.cable_type
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
                                    item.connector_type ??
                                    '-'
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Cable Length
                            </dt>

                            <dd class="mt-1 text-sm font-medium text-foreground">
                                <template v-if="item.cable_length">
                                    {{
                                        item.cable_length
                                    }}
                                    {{
                                        item.cable_length_unit
                                    }}
                                </template>

                                <template v-else>
                                    -
                                </template>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Status
                            </dt>

                            <dd class="mt-1">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="statusClass">
                                    {{ statusLabel }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>


            <!-- INSTALLATION -->
            <div class="rounded-xl border border-border bg-card shadow-sm">
                <div class="border-b border-border px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ShieldCheck class="h-4 w-4 text-primary" />

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

                            <dd class="mt-1 flex items-center gap-2 text-sm font-medium text-foreground">
                                <User class="h-4 w-4 text-muted-foreground" />

                                {{
                                    item.installer?.name ??
                                    '-'
                                }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-xs text-muted-foreground">
                                Installed At
                            </dt>

                            <dd class="mt-1 flex items-center gap-2 text-sm font-medium text-foreground">
                                <CalendarDays class="h-4 w-4 text-muted-foreground" />

                                {{
                                    formatDateTime(
                                        item.installed_at
                                    )
                                }}
                            </dd>
                        </div>

                        <div v-if="item.terminated_at">
                            <dt class="text-xs text-muted-foreground">
                                Terminated At
                            </dt>

                            <dd class="mt-1 flex items-center gap-2 text-sm font-medium text-foreground">
                                <Clock3 class="h-4 w-4 text-muted-foreground" />

                                {{
                                    formatDateTime(
                                        item.terminated_at
                                    )
                                }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>


        <!-- ================================================= -->
        <!-- ORIGINAL REQUEST -->
        <!-- ================================================= -->

        <div v-if="request" class="rounded-xl border border-border bg-card shadow-sm">
            <div
                class="flex flex-col gap-3 border-b border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs text-muted-foreground">
                        Original Interconnection Request
                    </p>

                    <h2 class="mt-1 font-semibold text-foreground">
                        {{
                            request.request_number
                        }}
                    </h2>
                </div>

                <a :href="`/admin/interconnections/${request.id}`"
                    class="inline-flex items-center justify-center rounded-lg border border-border px-3 py-1.5 text-sm font-medium transition hover:bg-muted">
                    View Request
                </a>
            </div>

            <div class="p-5">
                <dl class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <dt class="text-xs text-muted-foreground">
                            Requester
                        </dt>

                        <dd class="mt-1 text-sm font-medium text-foreground">
                            {{
                                request.requester_client
                                    ?.company_name ??
                                '-'
                            }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-muted-foreground">
                            Requested At
                        </dt>

                        <dd class="mt-1 text-sm font-medium text-foreground">
                            {{
                                formatDateTime(
                                    request.requested_at
                                )
                            }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-muted-foreground">
                            Completed At
                        </dt>

                        <dd class="mt-1 text-sm font-medium text-foreground">
                            {{
                                formatDateTime(
                                    request.completed_at
                                )
                            }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-xs text-muted-foreground">
                            Request Status
                        </dt>

                        <dd class="mt-1">
                            <span
                                class="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700">
                                {{
                                    request.status
                                }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>


        <!-- ================================================= -->
        <!-- DESCRIPTION / NOTES -->
        <!-- ================================================= -->

        <div v-if="item.description || item.notes" class="rounded-xl border border-border bg-card shadow-sm">
            <div class="border-b border-border px-5 py-4">
                <h2 class="font-semibold text-foreground">
                    Notes
                </h2>
            </div>

            <div class="space-y-5 p-5">

                <div v-if="item.description">
                    <p class="text-xs font-medium text-muted-foreground">
                        Description
                    </p>

                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-foreground">
                        {{
                            item.description
                        }}
                    </p>
                </div>

                <div v-if="item.notes">
                    <p class="text-xs font-medium text-muted-foreground">
                        Notes
                    </p>

                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-foreground">
                        {{
                            item.notes
                        }}
                    </p>
                </div>
            </div>
        </div>


        <!-- ================================================= -->
        <!-- TERMINATE MODAL -->
        <!-- ================================================= -->

        <div v-if="terminateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="closeTerminateModal">
            <div class="w-full max-w-lg rounded-xl border border-border bg-background shadow-xl">

                <div class="flex items-start justify-between border-b border-border px-5 py-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-red-50">
                                <Ban class="h-4 w-4 text-red-600" />
                            </div>

                            <div>
                                <h3 class="font-semibold text-foreground">
                                    Terminate Cross Connect
                                </h3>

                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    {{
                                        item.cross_connect_number
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <button type="button"
                        class="rounded-md p-1.5 text-muted-foreground transition hover:bg-muted hover:text-foreground"
                        @click="closeTerminateModal">
                        <XCircle class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-5 px-5 py-5">

                    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                        <p class="text-sm font-medium text-amber-900">
                            This action will release both ports.
                        </p>

                        <p class="mt-1 text-sm text-amber-700">
                            The source and destination ports will become available again after termination.
                        </p>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-foreground">
                            Termination Reason
                        </label>

                        <textarea v-model="terminateForm.reason" rows="4"
                            placeholder="Enter reason for terminating this cross connect..."
                            class="mt-2 w-full rounded-lg border border-input bg-background px-3 py-2 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                            :disabled="terminateForm.processing"></textarea>

                        <p v-if="terminateForm.errors.reason" class="mt-1 text-xs text-red-600">
                            {{
                                terminateForm.errors.reason
                            }}
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-border px-5 py-4">
                    <button type="button"
                        class="rounded-lg border border-border px-4 py-2 text-sm font-medium transition hover:bg-muted"
                        :disabled="terminateForm.processing" @click="closeTerminateModal">
                        Cancel
                    </button>

                    <button type="button"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="terminateForm.processing ||
                            !terminateForm.reason.trim()
                            " @click="terminateCrossConnect">
                        <span v-if="terminateForm.processing">
                            Terminating...
                        </span>

                        <span v-else>
                            Terminate Cross Connect
                        </span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</template>