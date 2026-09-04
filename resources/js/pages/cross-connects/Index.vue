<script setup lang="ts">
import { computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { Search, Cable, CheckCircle2, Clock3, Ban, XCircle } from 'lucide-vue-next'

interface Client {
    id: number
    company_code: string
    company_name: string
}

interface Rack {
    id: number
    code: string
}

interface Device {
    id: number
    code: string
    divice_name: string
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

interface InterconnectionRequest {
    id: number
    request_number: string
}

interface Installer {
    id: number
    name: string
}

interface CrossConnect {
    id: number
    cross_connect_number: string
    source_port?: DevicePort
    destination_port?: DevicePort
    interconnection_request?: InterconnectionRequest
    cable_type: string
    connector_type?: string | null
    cable_length?: string | number | null
    cable_length_unit: string
    status: 'planned' | 'active' | 'terminated' | 'cancelled'
    installed_at?: string | null
    terminated_at?: string | null
    installer?: Installer
    description?: string | null
    notes?: string | null
}

interface PaginationLink {
    url: string | null
    label: string
    active: boolean
}

interface Pagination<T> {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
    links: PaginationLink[]
}

interface Statistics {
    total: number
    active: number
    planned: number
    terminated: number
    cancelled: number
}

const props = defineProps<{
    title: string
    crossConnects: Pagination<CrossConnect>
    statistics: Statistics
    filters: {
        status: string
        search: string
    }
}>()

const search = ref(props.filters.search ?? '')
const selectedStatus = ref(props.filters.status ?? 'all')

const statuses = [
    {
        key: 'all',
        label: 'All',
    },
    {
        key: 'active',
        label: 'Active',
    },
    {
        key: 'planned',
        label: 'Planned',
    },
    {
        key: 'terminated',
        label: 'Terminated',
    },
    {
        key: 'cancelled',
        label: 'Cancelled',
    },
]

const submitFilter = () => {
    router.get(
        '/admin/cross-connects',
        {
            search: search.value || undefined,
            status:
                selectedStatus.value !== 'all'
                    ? selectedStatus.value
                    : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        }
    )
}

const changeStatus = (status: string) => {
    selectedStatus.value = status
    submitFilter()
}

const clearSearch = () => {
    search.value = ''
    submitFilter()
}

const goToPage = (url: string | null) => {
    if (!url) {
        return
    }

    router.get(
        url,
        {},
        {
            preserveState: true,
            preserveScroll: true,
        }
    )
}

const statusClass = (status: string) => {
    switch (status) {
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
}

const statusLabel = (status: string) => {
    switch (status) {
        case 'active':
            return 'Active'

        case 'planned':
            return 'Planned'

        case 'terminated':
            return 'Terminated'

        case 'cancelled':
            return 'Cancelled'

        default:
            return status
    }
}

const cableLabel = (type: string) => {
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

const formatDate = (date?: string | null) => {
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

const sourceClient = (crossConnect: CrossConnect) =>
    crossConnect.source_port?.device?.client?.company_name ?? '-'

const destinationClient = (crossConnect: CrossConnect) =>
    crossConnect.destination_port?.device?.client?.company_name ?? '-'

const sourceDevice = (crossConnect: CrossConnect) =>
    crossConnect.source_port?.device?.divice_name ??
    crossConnect.source_port?.device?.code ??
    '-'

const destinationDevice = (crossConnect: CrossConnect) =>
    crossConnect.destination_port?.device?.divice_name ??
    crossConnect.destination_port?.device?.code ??
    '-'

const sourcePort = (crossConnect: CrossConnect) =>
    crossConnect.source_port?.port_name ?? '-'

const destinationPort = (crossConnect: CrossConnect) =>
    crossConnect.destination_port?.port_name ?? '-'

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Cross Connects',
                href: '/admin/cross-connects',
            },
        ],
    },
})
</script>

<template>

    <Head :title="title" />

    <div class="space-y-4 p-4">

        <!-- HEADER -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                        <Cable class="h-5 w-5 text-primary" />
                    </div>

                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-foreground">
                            Cross Connects
                        </h1>

                        <p class="text-sm text-muted-foreground">
                            Manage active and historical data center connections.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="/admin/interconnections"
                    class="inline-flex items-center rounded-lg border border-border bg-background px-4 py-2 text-sm font-medium transition hover:bg-muted">
                    Interconnection Requests
                </a>
            </div>
        </div>

        <!-- STATISTICS -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

            <!-- TOTAL -->
            <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Total
                        </p>

                        <p class="mt-2 text-2xl font-semibold text-foreground">
                            {{ statistics.total }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">
                        <Cable class="h-5 w-5 text-slate-600" />
                    </div>
                </div>
            </div>

            <!-- ACTIVE -->
            <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Active
                        </p>

                        <p class="mt-2 text-2xl font-semibold text-foreground">
                            {{ statistics.active }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50">
                        <CheckCircle2 class="h-5 w-5 text-emerald-600" />
                    </div>
                </div>
            </div>

            <!-- PLANNED -->
            <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Planned
                        </p>

                        <p class="mt-2 text-2xl font-semibold text-foreground">
                            {{ statistics.planned }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50">
                        <Clock3 class="h-5 w-5 text-amber-600" />
                    </div>
                </div>
            </div>

            <!-- TERMINATED -->
            <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Terminated
                        </p>

                        <p class="mt-2 text-2xl font-semibold text-foreground">
                            {{ statistics.terminated }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100">
                        <Ban class="h-5 w-5 text-slate-600" />
                    </div>
                </div>
            </div>

            <!-- CANCELLED -->
            <div class="rounded-xl border border-border bg-card p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Cancelled
                        </p>

                        <p class="mt-2 text-2xl font-semibold text-foreground">
                            {{ statistics.cancelled }}
                        </p>
                    </div>

                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-red-50">
                        <XCircle class="h-5 w-5 text-red-600" />
                    </div>
                </div>
            </div>
        </div>

        <!-- MAIN CARD -->
        <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">

            <!-- FILTER -->
            <div class="flex flex-col gap-4 border-b border-border p-5 lg:flex-row lg:items-center lg:justify-between">

                <!-- SEARCH -->
                <div class="relative w-full lg:max-w-md">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                    <input v-model="search" type="text" placeholder="Search cross connect, request, or client..."
                        class="h-10 w-full rounded-lg border border-input bg-background pl-9 pr-20 text-sm outline-none transition focus:border-primary focus:ring-2 focus:ring-primary/10"
                        @keyup.enter="submitFilter" />

                    <button v-if="search" type="button"
                        class="absolute right-2 top-1/2 -translate-y-1/2 rounded-md px-2 py-1 text-xs text-muted-foreground hover:bg-muted"
                        @click="clearSearch">
                        Clear
                    </button>
                </div>

                <!-- STATUS -->
                <div class="flex flex-wrap items-center gap-1 rounded-lg bg-muted p-1">
                    <button v-for="status in statuses" :key="status.key" type="button"
                        class="rounded-md px-3 py-1.5 text-sm font-medium transition" :class="selectedStatus === status.key
                                ? 'bg-background text-foreground shadow-sm'
                                : 'text-muted-foreground hover:text-foreground'
                            " @click="changeStatus(status.key)">
                        {{ status.label }}
                    </button>
                </div>
            </div>

            <!-- TABLE -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-muted/30">
                        <tr>
                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Cross Connect
                            </th>

                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Source
                            </th>

                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Destination
                            </th>

                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Connection
                            </th>

                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Status
                            </th>

                            <th class="px-5 py-3 font-medium text-muted-foreground">
                                Installed
                            </th>

                            <th class="px-5 py-3 text-right font-medium text-muted-foreground">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody v-if="crossConnects.data.length" class="divide-y divide-border">
                        <tr v-for="crossConnect in crossConnects.data" :key="crossConnect.id"
                            class="transition hover:bg-muted/30">

                            <!-- CROSS CONNECT -->
                            <td class="px-5 py-4">
                                <div>
                                    <p class="font-medium text-foreground">
                                        {{
                                            crossConnect.cross_connect_number
                                        }}
                                    </p>

                                    <p v-if="
                                        crossConnect.interconnection_request
                                    " class="mt-1 text-xs text-muted-foreground">
                                        {{
                                            crossConnect
                                                .interconnection_request
                                                .request_number
                                        }}
                                    </p>
                                </div>
                            </td>

                            <!-- SOURCE -->
                            <td class="px-5 py-4">
                                <div class="min-w-[190px]">
                                    <p class="font-medium text-foreground">
                                        {{
                                            sourceClient(crossConnect)
                                        }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{
                                            sourceDevice(crossConnect)
                                        }}
                                    </p>

                                    <p class="mt-1 text-xs font-medium text-foreground">
                                        {{ sourcePort(crossConnect) }}
                                    </p>

                                    <p v-if="
                                        crossConnect.source_port?.device?.rack
                                    " class="mt-1 text-xs text-muted-foreground">
                                        Rack
                                        {{
                                            crossConnect.source_port.device.rack.code
                                        }}
                                    </p>
                                </div>
                            </td>

                            <!-- DESTINATION -->
                            <td class="px-5 py-4">
                                <div class="min-w-[190px]">
                                    <p class="font-medium text-foreground">
                                        {{
                                            destinationClient(
                                                crossConnect
                                            )
                                        }}
                                    </p>

                                    <p class="mt-1 text-xs text-muted-foreground">
                                        {{
                                            destinationDevice(
                                                crossConnect
                                            )
                                        }}
                                    </p>

                                    <p class="mt-1 text-xs font-medium text-foreground">
                                        {{
                                            destinationPort(
                                                crossConnect
                                            )
                                        }}
                                    </p>

                                    <p v-if="
                                        crossConnect.destination_port?.device?.rack
                                    " class="mt-1 text-xs text-muted-foreground">
                                        Rack
                                        {{
                                            crossConnect.destination_port.device.rack.code
                                        }}
                                    </p>
                                </div>
                            </td>

                            <!-- CONNECTION -->
                            <td class="px-5 py-4">
                                <div class="min-w-[130px]">
                                    <p class="font-medium text-foreground">
                                        {{
                                            cableLabel(
                                                crossConnect.cable_type
                                            )
                                        }}
                                    </p>

                                    <p v-if="crossConnect.connector_type" class="mt-1 text-xs text-muted-foreground">
                                        {{
                                            crossConnect.connector_type
                                        }}
                                    </p>

                                    <p v-if="crossConnect.cable_length" class="mt-1 text-xs text-muted-foreground">
                                        {{
                                            crossConnect.cable_length
                                        }}
                                        {{
                                            crossConnect.cable_length_unit
                                        }}
                                    </p>
                                </div>
                            </td>

                            <!-- STATUS -->
                            <td class="px-5 py-4">
                                <span
                                    class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                                    :class="statusClass(
                                        crossConnect.status
                                    )
                                        ">
                                    {{
                                        statusLabel(
                                            crossConnect.status
                                        )
                                    }}
                                </span>
                            </td>

                            <!-- INSTALLED -->
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div>
                                    <p class="text-sm text-foreground">
                                        {{
                                            formatDate(
                                                crossConnect.installed_at
                                            )
                                        }}
                                    </p>

                                    <p v-if="crossConnect.installer" class="mt-1 text-xs text-muted-foreground">
                                        {{
                                            crossConnect.installer.name
                                        }}
                                    </p>
                                </div>
                            </td>

                            <!-- ACTION -->
                            <td class="px-5 py-4 text-right">
                                <a :href="`/admin/cross-connects/${crossConnect.id}`"
                                    class="inline-flex items-center rounded-lg border border-border px-3 py-1.5 text-sm font-medium transition hover:bg-muted">
                                    View
                                </a>
                            </td>
                        </tr>
                    </tbody>

                    <!-- EMPTY -->
                    <tbody v-else>
                        <tr>
                            <td colspan="7" class="px-5 py-16 text-center">
                                <div class="mx-auto flex max-w-sm flex-col items-center">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-muted">
                                        <Cable class="h-6 w-6 text-muted-foreground" />
                                    </div>

                                    <h3 class="mt-4 text-sm font-semibold text-foreground">
                                        No cross connects found
                                    </h3>

                                    <p class="mt-1 text-sm text-muted-foreground">
                                        Cross connects will appear here
                                        after an interconnection request
                                        is completed.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div v-if="crossConnects.last_page > 1"
                class="flex flex-col gap-3 border-t border-border px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-muted-foreground">
                    Showing
                    <span class="font-medium text-foreground">
                        {{ crossConnects.from }}
                    </span>
                    to
                    <span class="font-medium text-foreground">
                        {{ crossConnects.to }}
                    </span>
                    of
                    <span class="font-medium text-foreground">
                        {{ crossConnects.total }}
                    </span>
                    results
                </p>

                <div class="flex items-center gap-1">
                    <button v-for="(link, index) in crossConnects.links" :key="index" type="button"
                        :disabled="!link.url" class="min-w-9 rounded-lg px-3 py-1.5 text-sm transition" :class="link.active
                                ? 'bg-primary text-primary-foreground'
                                : link.url
                                    ? 'border border-border hover:bg-muted'
                                    : 'cursor-not-allowed text-muted-foreground/50'
                            " @click="goToPage(link.url)" v-html="link.label" />
                </div>
            </div>
        </div>
    </div>
</template>