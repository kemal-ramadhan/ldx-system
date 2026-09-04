<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import {
    ArrowLeft,
    Cable,
    Check,
    CheckCircle2,
    Clock3,
    ExternalLink,
    FileText,
    Loader2,
    Network,
    Server,
    XCircle,
} from 'lucide-vue-next'

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Interconnections',
                href: '/client/interconnections',
            },
            {
                title: 'Detail',
                href: '#',
            },
        ],
    },
})

interface Client {
    id: number
    company_code?: string
    company_name: string
}

interface Rack {
    id: number
    name?: string
    code?: string
}

interface Device {
    id: number
    code: string
    divice_name: string
    model?: string
    serial_number?: string
    rack?: Rack
    client?: Client
}

interface Port {
    id: number
    port_name: string
    port_number: number | string
    port_type?: string
    connector_type?: string
    status: string
    device?: Device
}

interface Approver {
    id: number
    name: string
}

interface CrossConnect {
    id: number
    status?: string
    connected_at?: string
}

interface Interconnection {
    id: number
    request_number: string

    requester_client_id: number

    source_port_id: number
    destination_port_id: number

    interconnection_type: string
    cable_type: string
    connector_type: string

    cable_length: string | number
    cable_length_unit: string

    status: string
    progress: number

    requested_at?: string
    started_at?: string
    completed_at?: string

    description?: string
    rejection_reason?: string
    notes?: string

    requester_client?: Client

    source_port?: Port
    destination_port?: Port

    destination_approver?: Approver
    dc_approver?: Approver

    cross_connect?: CrossConnect
}

const props = defineProps<{
    interconnection: Interconnection
}>()

/*
|--------------------------------------------------------------------------
| STATUS
|--------------------------------------------------------------------------
*/

const statusMap: Record<
    string,
    {
        label: string
        description: string
    }
> = {
    submitted: {
        label: 'Submitted',
        description: 'Request has been submitted.',
    },

    waiting_destination_approval: {
        label: 'Waiting Destination Approval',
        description:
            'Waiting for destination approval.',
    },

    waiting_dc_approval: {
        label: 'Waiting DC Approval',
        description:
            'Request is being reviewed by Data Center.',
    },

    approved: {
        label: 'Approved',
        description:
            'Request has been approved and is ready for installation.',
    },

    in_progress: {
        label: 'Installation',
        description:
            'Physical installation is in progress.',
    },

    testing: {
        label: 'Testing',
        description:
            'Connection is being tested.',
    },

    completed: {
        label: 'Completed',
        description:
            'Interconnection has been completed.',
    },

    rejected: {
        label: 'Rejected',
        description:
            'This request was rejected.',
    },

    cancelled: {
        label: 'Cancelled',
        description:
            'This request has been cancelled.',
    },
}

const statusInfo = computed(() => {
    return (
        statusMap[
        props.interconnection.status
        ] ?? {
            label: props.interconnection.status,
            description: '',
        }
    )
})

/*
|--------------------------------------------------------------------------
| STATUS CLASS
|--------------------------------------------------------------------------
*/

const statusClass = computed(() => {
    switch (
    props.interconnection.status
    ) {
        case 'completed':
            return 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20'

        case 'approved':
            return 'bg-blue-500/10 text-blue-600 border-blue-500/20'

        case 'in_progress':
        case 'testing':
            return 'bg-amber-500/10 text-amber-600 border-amber-500/20'

        case 'rejected':
        case 'cancelled':
            return 'bg-red-500/10 text-red-600 border-red-500/20'

        default:
            return 'bg-muted text-muted-foreground border-border'
    }
})

/*
|--------------------------------------------------------------------------
| PROGRESS
|--------------------------------------------------------------------------
*/

const steps = [
    {
        key: 'waiting_destination_approval',
        label: 'Destination Approval',
    },
    {
        key: 'waiting_dc_approval',
        label: 'DC Approval',
    },
    {
        key: 'approved',
        label: 'Approved',
    },
    {
        key: 'in_progress',
        label: 'Installation',
    },
    {
        key: 'testing',
        label: 'Testing',
    },
    {
        key: 'completed',
        label: 'Completed',
    },
]

const stepIndex = computed(() => {
    const status =
        props.interconnection.status

    if (status === 'submitted') {
        return 0
    }

    if (
        status ===
        'waiting_destination_approval'
    ) {
        return 0
    }

    if (
        status ===
        'waiting_dc_approval'
    ) {
        return 1
    }

    if (status === 'approved') {
        return 2
    }

    if (status === 'in_progress') {
        return 3
    }

    if (status === 'testing') {
        return 4
    }

    if (status === 'completed') {
        return 5
    }

    return -1
})

/*
|--------------------------------------------------------------------------
| DATE FORMAT
|--------------------------------------------------------------------------
*/

const formatDate = (
    value?: string
) => {
    if (!value) {
        return '-'
    }

    return new Date(value).toLocaleString(
        'id-ID',
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
| DEVICE / PORT
|--------------------------------------------------------------------------
*/

const sourceDevice = computed(
    () =>
        props.interconnection
            .source_port?.device
)

const destinationDevice =
    computed(
        () =>
            props.interconnection
                .destination_port?.device
    )

const sourceClient =
    computed(
        () =>
            sourceDevice.value
                ?.client
    )

const destinationClient =
    computed(
        () =>
            destinationDevice.value
                ?.client
    )
</script>

<template>

    <Head :title="interconnection.request_number" />

    <div class="space-y-6 p-4">

        <!-- HEADER -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

            <div class="flex items-start gap-3">

                <Link href="/client/interconnections"
                    class="mt-1 inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-border bg-background transition hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>

                <div>

                    <div class="flex flex-wrap items-center gap-2">

                        <h1 class="text-xl font-semibold tracking-tight">
                            {{
                                interconnection.request_number
                            }}
                        </h1>

                        <span class="rounded-full border px-3 py-1 text-xs font-medium" :class="statusClass">
                            {{
                                statusInfo.label
                            }}
                        </span>

                    </div>

                    <p class="mt-1 text-sm text-muted-foreground">
                        {{
                            statusInfo.description
                        }}
                    </p>

                </div>

            </div>

        </div>


        <!-- REJECTION -->
        <div v-if="
            interconnection.status === 'rejected'
        " class="rounded-2xl border border-red-500/20 bg-red-500/5 p-5">

            <div class="flex gap-3">

                <XCircle class="mt-0.5 h-5 w-5 shrink-0 text-red-600" />

                <div>

                    <h3 class="font-semibold text-red-700 dark:text-red-400">
                        Request Rejected
                    </h3>

                    <p class="mt-1 text-sm text-muted-foreground">
                        {{
                            interconnection.rejection_reason ||
                            'No rejection reason provided.'
                        }}
                    </p>

                </div>

            </div>

        </div>


        <!-- PROGRESS -->
        <div class="rounded-2xl border border-border bg-card p-6 shadow-sm">

            <div class="mb-6 flex items-center justify-between">

                <div>
                    <h2 class="font-semibold">
                        Request Progress
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Track the progress of your interconnection request.
                    </p>
                </div>

                <div class="text-right">

                    <div class="text-2xl font-semibold">
                        {{
                            interconnection.progress
                        }}%
                    </div>

                    <div class="text-xs text-muted-foreground">
                        Overall Progress
                    </div>

                </div>

            </div>


            <!-- PROGRESS BAR -->
            <div class="mb-8 h-2 overflow-hidden rounded-full bg-muted">

                <div class="h-full rounded-full bg-primary transition-all duration-500" :style="{
                    width:
                        `${interconnection.progress}%`,
                }" />

            </div>


            <!-- STEPS -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-6">

                <div v-for="(
step,
                            index
                    ) in steps" :key="step.key" class="relative">

                    <div class="flex items-center gap-3">

                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border" :class="index <= stepIndex
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'border-border bg-background text-muted-foreground'
                            ">

                            <Check v-if="
                                index <
                                stepIndex
                            " class="h-4 w-4" />

                            <Loader2 v-else-if="
                                index ===
                                stepIndex &&
                                ![
                                    'completed',
                                ].includes(
                                    interconnection.status
                                )
                            " class="h-4 w-4 animate-spin" />

                            <span v-else class="text-xs font-semibold">
                                {{
                                    index + 1
                                }}
                            </span>

                        </div>

                        <div>

                            <p class="text-sm font-medium" :class="index <= stepIndex
                                    ? 'text-foreground'
                                    : 'text-muted-foreground'
                                ">
                                {{
                                    step.label
                                }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- CONNECTION -->
        <div class="grid gap-6 lg:grid-cols-2">

            <!-- SOURCE -->
            <div class="rounded-2xl border border-border bg-card shadow-sm">

                <div class="flex items-center gap-3 border-b border-border p-5">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                        <Server class="h-5 w-5 text-primary" />
                    </div>

                    <div>

                        <h2 class="font-semibold">
                            Source
                        </h2>

                        <p class="text-xs text-muted-foreground">
                            Your equipment
                        </p>

                    </div>

                </div>


                <div class="space-y-5 p-5">

                    <div>

                        <p class="text-xs text-muted-foreground">
                            Client
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{
                                sourceClient?.company_name ??
                                interconnection.requester_client?.company_name ??
                                '-'
                            }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs text-muted-foreground">
                            Device
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{
                                sourceDevice?.divice_name ??
                                '-'
                            }}
                        </p>

                        <p class="text-xs text-muted-foreground">
                            {{
                                sourceDevice?.code ??
                                '-'
                            }}
                        </p>

                    </div>


                    <div class="grid grid-cols-2 gap-4">

                        <div>

                            <p class="text-xs text-muted-foreground">
                                Port
                            </p>

                            <p class="mt-1 text-sm font-medium">
                                {{
                                    interconnection.source_port?.port_name ??
                                    '-'
                                }}
                            </p>

                        </div>

                        <div>

                            <p class="text-xs text-muted-foreground">
                                Connector
                            </p>

                            <p class="mt-1 text-sm font-medium">
                                {{
                                    interconnection.source_port?.connector_type ??
                                    '-'
                                }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- DESTINATION -->
            <div class="rounded-2xl border border-border bg-card shadow-sm">

                <div class="flex items-center gap-3 border-b border-border p-5">

                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                        <Network class="h-5 w-5 text-primary" />
                    </div>

                    <div>

                        <h2 class="font-semibold">
                            Destination
                        </h2>

                        <p class="text-xs text-muted-foreground">
                            Connected tenant
                        </p>

                    </div>

                </div>


                <div class="space-y-5 p-5">

                    <div>

                        <p class="text-xs text-muted-foreground">
                            Client
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{
                                destinationClient?.company_name ??
                                '-'
                            }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs text-muted-foreground">
                            Device
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{
                                destinationDevice?.divice_name ??
                                '-'
                            }}
                        </p>

                        <p class="text-xs text-muted-foreground">
                            {{
                                destinationDevice?.code ??
                                '-'
                            }}
                        </p>

                    </div>


                    <div class="grid grid-cols-2 gap-4">

                        <div>

                            <p class="text-xs text-muted-foreground">
                                Port
                            </p>

                            <p class="mt-1 text-sm font-medium">
                                {{
                                    interconnection.destination_port?.port_name ??
                                    '-'
                                }}
                            </p>

                        </div>

                        <div>

                            <p class="text-xs text-muted-foreground">
                                Connector
                            </p>

                            <p class="mt-1 text-sm font-medium">
                                {{
                                    interconnection.destination_port?.connector_type ??
                                    '-'
                                }}
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- CABLE -->
        <div class="rounded-2xl border border-border bg-card shadow-sm">

            <div class="flex items-center gap-3 border-b border-border p-5">

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                    <Cable class="h-5 w-5 text-primary" />
                </div>

                <div>

                    <h2 class="font-semibold">
                        Cable Information
                    </h2>

                    <p class="text-xs text-muted-foreground">
                        Physical connection specification.
                    </p>

                </div>

            </div>


            <div class="grid gap-6 p-5 sm:grid-cols-2 lg:grid-cols-4">

                <div>

                    <p class="text-xs text-muted-foreground">
                        Type
                    </p>

                    <p class="mt-1 text-sm font-medium">
                        {{
                            interconnection.interconnection_type
                        }}
                    </p>

                </div>


                <div>

                    <p class="text-xs text-muted-foreground">
                        Cable
                    </p>

                    <p class="mt-1 text-sm font-medium">
                        {{
                            interconnection.cable_type
                        }}
                    </p>

                </div>


                <div>

                    <p class="text-xs text-muted-foreground">
                        Connector
                    </p>

                    <p class="mt-1 text-sm font-medium">
                        {{
                            interconnection.connector_type
                        }}
                    </p>

                </div>


                <div>

                    <p class="text-xs text-muted-foreground">
                        Length
                    </p>

                    <p class="mt-1 text-sm font-medium">
                        {{
                            interconnection.cable_length
                        }}
                        {{
                            interconnection.cable_length_unit
                        }}
                    </p>

                </div>

            </div>

        </div>


        <!-- REQUEST INFORMATION -->
        <div class="grid gap-6 lg:grid-cols-2">

            <!-- INFORMATION -->
            <div class="rounded-2xl border border-border bg-card shadow-sm">

                <div class="flex items-center gap-3 border-b border-border p-5">

                    <FileText class="h-5 w-5 text-muted-foreground" />

                    <h2 class="font-semibold">
                        Request Information
                    </h2>

                </div>


                <div class="space-y-5 p-5">

                    <div>

                        <p class="text-xs text-muted-foreground">
                            Request Number
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{
                                interconnection.request_number
                            }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs text-muted-foreground">
                            Requested At
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{
                                formatDate(
                                    interconnection.requested_at
                                )
                            }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs text-muted-foreground">
                            Installation Started
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{
                                formatDate(
                                    interconnection.started_at
                                )
                            }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs text-muted-foreground">
                            Completed At
                        </p>

                        <p class="mt-1 text-sm font-medium">
                            {{
                                formatDate(
                                    interconnection.completed_at
                                )
                            }}
                        </p>

                    </div>

                </div>

            </div>


            <!-- DESCRIPTION -->
            <div class="rounded-2xl border border-border bg-card shadow-sm">

                <div class="flex items-center gap-3 border-b border-border p-5">

                    <FileText class="h-5 w-5 text-muted-foreground" />

                    <h2 class="font-semibold">
                        Description & Notes
                    </h2>

                </div>


                <div class="space-y-5 p-5">

                    <div>

                        <p class="text-xs text-muted-foreground">
                            Description
                        </p>

                        <p class="mt-2 whitespace-pre-line text-sm leading-6">
                            {{
                                interconnection.description ||
                                'No description provided.'
                            }}
                        </p>

                    </div>


                    <div>

                        <p class="text-xs text-muted-foreground">
                            Notes
                        </p>

                        <p class="mt-2 whitespace-pre-line text-sm leading-6">
                            {{
                                interconnection.notes ||
                                'No notes provided.'
                            }}
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <!-- APPROVAL INFORMATION -->
        <div v-if="
            interconnection.destination_approver ||
            interconnection.dc_approver
        " class="rounded-2xl border border-border bg-card shadow-sm">

            <div class="flex items-center gap-3 border-b border-border p-5">

                <CheckCircle2 class="h-5 w-5 text-muted-foreground" />

                <h2 class="font-semibold">
                    Approval Information
                </h2>

            </div>


            <div class="grid gap-6 p-5 sm:grid-cols-2">

                <div v-if="
                    interconnection.destination_approver
                ">

                    <p class="text-xs text-muted-foreground">
                        Destination Approval
                    </p>

                    <p class="mt-1 text-sm font-medium">
                        {{
                            interconnection.destination_approver.name
                        }}
                    </p>

                    <p class="text-xs text-muted-foreground">
                        Approved
                    </p>

                </div>


                <div v-if="
                    interconnection.dc_approver
                ">

                    <p class="text-xs text-muted-foreground">
                        Data Center Approval
                    </p>

                    <p class="mt-1 text-sm font-medium">
                        {{
                            interconnection.dc_approver.name
                        }}
                    </p>

                    <p class="text-xs text-muted-foreground">
                        Approved
                    </p>

                </div>

            </div>

        </div>


        <!-- CROSS CONNECT -->
        <div v-if="interconnection.cross_connect" class="rounded-2xl border border-border bg-card shadow-sm">

            <div class="flex items-center gap-3 border-b border-border p-5">

                <CheckCircle2 class="h-5 w-5 text-emerald-600" />

                <div>

                    <h2 class="font-semibold">
                        Cross Connect
                    </h2>

                    <p class="text-xs text-muted-foreground">
                        Physical connection record.
                    </p>

                </div>

            </div>


            <div class="grid gap-6 p-5 sm:grid-cols-2">

                <div>

                    <p class="text-xs text-muted-foreground">
                        Status
                    </p>

                    <p class="mt-1 text-sm font-medium capitalize">
                        {{
                            interconnection.cross_connect.status ??
                            '-'
                        }}
                    </p>

                </div>

                <div>

                    <p class="text-xs text-muted-foreground">
                        Connected At
                    </p>

                    <p class="mt-1 text-sm font-medium">
                        {{
                            formatDate(
                                interconnection.cross_connect.connected_at
                            )
                        }}
                    </p>

                </div>

            </div>

        </div>

    </div>

</template>