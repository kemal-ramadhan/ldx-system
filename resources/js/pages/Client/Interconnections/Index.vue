<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    ArrowRight,
    Eye,
    Plus,
    Search,
    Server,
    Cable,
    CheckCircle2,
    Clock3,
    Loader2,
} from 'lucide-vue-next'

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Interconnections',
                href: '/client/interconnections',
            },
        ],
    },
})

interface Interconnection {
    id: number
    request_number: string
    status: string
    progress: number
    interconnection_type: string
    cable_type: string
    connector_type: string
    cable_length: number
    cable_length_unit: string
    description?: string
    requested_at?: string

    destination_type?: string
    external_client_name?: string
    external_rack_name?: string
    external_device_name?: string
    external_port_name?: string

    source_port?: {
        port_name: string
        port_number?: string
        device?: {
            divice_name: string
            client?: {
                company_name: string
            }
            rack?: {
                name: string
            }
        }
    }

    destination_port?: {
        port_name: string
        port_number?: string
        device?: {
            divice_name: string
            client?: {
                company_name: string
            }
            rack?: {
                name: string
            }
        }
    }
}

const props = defineProps<{
    interconnections: {
        data: Interconnection[]
        current_page: number
        last_page: number
        total: number
    }

    filters: {
        search?: string
        status?: string
    }
}>()

const search = ref(props.filters?.search ?? '')
const status = ref(props.filters?.status ?? '')

const statusOptions = [
    {
        value: '',
        label: 'All Status',
    },
    {
        value: 'waiting_destination_approval',
        label: 'Destination Approval',
    },
    {
        value: 'waiting_dc_approval',
        label: 'DC Approval',
    },
    {
        value: 'approved',
        label: 'Approved',
    },
    {
        value: 'in_progress',
        label: 'Installation',
    },
    {
        value: 'testing',
        label: 'Testing',
    },
    {
        value: 'completed',
        label: 'Completed',
    },
]

function submitFilter() {
    router.get(
        '/client/interconnections',
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        {
            preserveState: true,
            replace: true,
        }
    )
}

function statusLabel(value: string) {
    return (
        statusOptions.find(
            item => item.value === value
        )?.label ?? value
    )
}

function statusClass(value: string) {
    switch (value) {
        case 'completed':
            return 'bg-emerald-50 text-emerald-700 border-emerald-200'

        case 'approved':
            return 'bg-blue-50 text-blue-700 border-blue-200'

        case 'in_progress':
            return 'bg-indigo-50 text-indigo-700 border-indigo-200'

        case 'testing':
            return 'bg-purple-50 text-purple-700 border-purple-200'

        case 'waiting_destination_approval':
        case 'waiting_dc_approval':
            return 'bg-amber-50 text-amber-700 border-amber-200'

        default:
            return 'bg-gray-50 text-gray-700 border-gray-200'
    }
}

function statusIcon(value: string) {
    if (value === 'completed') return CheckCircle2
    if (value === 'in_progress') return Loader2
    if (value === 'testing') return Cable

    return Clock3
}

function formatDate(date?: string) {
    if (!date) return '-'

    return new Date(date).toLocaleDateString(
        'id-ID',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    )
}

function getDestination(item: Interconnection) {
    if (item.destination_type === 'external') {
        return item.external_client_name ?? 'Unknown'
    }
    return (
        item.destination_port?.device?.client?.company_name ??
        'Unknown'
    )
}

function getDestinationPort(item: Interconnection) {
    if (item.destination_type === 'external') {
        return item.external_port_name ?? 'Unknown Port'
    }
    return item.destination_port?.port_name ?? 'Unknown Port'
}
</script>

<template>

    <Head title="Interconnections" />

    <div class="space-y-6 p-4">

        <!-- HEADER -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div>
                <h1 class="text-2xl font-semibold tracking-tight">
                    Interconnections
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Manage and monitor your interconnection requests.
                </p>
            </div>

            <Link href="/client/interconnections/create"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition hover:opacity-90">
                <Plus class="h-4 w-4" />
                New Request
            </Link>

        </div>


        <!-- FILTER -->
        <div class="rounded-2xl border bg-card p-4 shadow-sm">

            <div class="flex flex-col gap-3 md:flex-row">

                <div class="relative flex-1">

                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />

                    <input v-model="search" @keyup.enter="submitFilter" type="text"
                        placeholder="Search request number..."
                        class="h-10 w-full rounded-xl border bg-background pl-9 pr-4 text-sm outline-none focus:ring-2 focus:ring-primary/20" />

                </div>

                <select v-model="status" @change="submitFilter"
                    class="h-10 rounded-xl border bg-background px-4 text-sm outline-none focus:ring-2 focus:ring-primary/20">
                    <option v-for="item in statusOptions" :key="item.value" :value="item.value">
                        {{ item.label }}
                    </option>
                </select>

                <button @click="submitFilter"
                    class="h-10 rounded-xl border px-4 text-sm font-medium transition hover:bg-muted">
                    Search
                </button>

            </div>

        </div>


        <!-- DESKTOP TABLE -->
        <div class="hidden overflow-hidden rounded-2xl border bg-card shadow-sm md:block">

            <table class="w-full">

                <thead class="border-b bg-muted/30">

                    <tr class="text-left text-xs font-medium text-muted-foreground">

                        <th class="px-6 py-4">
                            Request
                        </th>

                        <th class="px-6 py-4">
                            Connection
                        </th>

                        <th class="px-6 py-4">
                            Type
                        </th>

                        <th class="px-6 py-4">
                            Status
                        </th>

                        <th class="px-6 py-4">
                            Progress
                        </th>

                        <th class="px-6 py-4 text-right">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y">

                    <tr v-for="item in interconnections.data" :key="item.id" class="transition hover:bg-muted/20">

                        <!-- REQUEST -->
                        <td class="px-6 py-4">

                            <div class="font-medium">
                                {{ item.request_number }}
                            </div>

                            <div class="mt-1 text-xs text-muted-foreground">
                                {{ formatDate(item.requested_at) }}
                            </div>

                        </td>


                        <!-- CONNECTION -->
                        <td class="px-6 py-4">

                            <div class="flex items-center gap-2 text-sm">

                                <div class="max-w-[150px] truncate">
                                    {{
                                        item.source_port?.device?.divice_name
                                    }}
                                </div>

                                <ArrowRight class="h-4 w-4 shrink-0 text-muted-foreground" />

                                <div class="max-w-[150px] truncate">
                                    {{ getDestination(item) }}
                                </div>

                            </div>

                            <div class="mt-1 text-xs text-muted-foreground">

                                {{
                                    item.source_port?.port_name
                                }}

                                →

                                {{
                                    getDestinationPort(item)
                                }}

                            </div>

                        </td>


                        <!-- TYPE -->
                        <td class="px-6 py-4">

                            <div class="text-sm font-medium">
                                {{ item.interconnection_type }}
                            </div>

                            <div class="mt-1 text-xs text-muted-foreground">
                                {{ item.cable_type }}
                                ·
                                {{ item.connector_type }}
                            </div>

                        </td>


                        <!-- STATUS -->
                        <td class="px-6 py-4">

                            <span
                                class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium"
                                :class="statusClass(item.status)">

                                <component :is="statusIcon(item.status)" class="h-3.5 w-3.5" />

                                {{ statusLabel(item.status) }}

                            </span>

                        </td>


                        <!-- PROGRESS -->
                        <td class="px-6 py-4">

                            <div class="w-28">

                                <div class="mb-1 flex justify-between text-xs">

                                    <span class="text-muted-foreground">
                                        Progress
                                    </span>

                                    <span class="font-medium">
                                        {{ item.progress }}%
                                    </span>

                                </div>

                                <div class="h-1.5 overflow-hidden rounded-full bg-muted">

                                    <div class="h-full rounded-full bg-primary transition-all" :style="{
                                        width: `${item.progress}%`
                                    }" />

                                </div>

                            </div>

                        </td>


                        <!-- ACTION -->
                        <td class="px-6 py-4 text-right">

                            <Link :href="`/client/interconnections/${item.id}`"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-medium hover:bg-muted">
                                <Eye class="h-4 w-4" />
                                View
                            </Link>

                        </td>

                    </tr>


                    <!-- EMPTY -->
                    <tr v-if="!interconnections.data.length">

                        <td colspan="6" class="px-6 py-16 text-center">

                            <Cable class="mx-auto h-10 w-10 text-muted-foreground/50" />

                            <div class="mt-3 font-medium">
                                No interconnection requests
                            </div>

                            <p class="mt-1 text-sm text-muted-foreground">
                                Create your first interconnection request.
                            </p>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>


        <!-- MOBILE -->
        <div class="space-y-3 md:hidden">

            <div v-for="item in interconnections.data" :key="item.id" class="rounded-2xl border bg-card p-4 shadow-sm">

                <div class="flex items-start justify-between gap-3">

                    <div>
                        <div class="font-medium">
                            {{ item.request_number }}
                        </div>

                        <div class="mt-1 text-xs text-muted-foreground">
                            {{ formatDate(item.requested_at) }}
                        </div>
                    </div>

                    <span class="rounded-full border px-2 py-1 text-xs" :class="statusClass(item.status)">
                        {{ statusLabel(item.status) }}
                    </span>

                </div>


                <div class="my-4 flex items-center gap-3">

                    <div class="flex-1 rounded-xl border p-3">

                        <div class="text-xs text-muted-foreground">
                            Source
                        </div>

                        <div class="mt-1 truncate text-sm font-medium">
                            {{ item.source_port?.device?.divice_name }}
                        </div>

                        <div class="mt-1 text-xs text-muted-foreground">
                            {{ item.source_port?.port_name }}
                        </div>

                    </div>

                    <ArrowRight class="h-4 w-4 shrink-0 text-muted-foreground" />

                    <div class="flex-1 rounded-xl border p-3">

                        <div class="text-xs text-muted-foreground">
                            Destination
                        </div>

                        <div class="mt-1 truncate text-sm font-medium">
                            {{ getDestination(item) }}
                        </div>

                        <div class="mt-1 text-xs text-muted-foreground">
                            {{ getDestinationPort(item) }}
                        </div>

                    </div>

                </div>


                <div>

                    <div class="mb-1 flex justify-between text-xs">

                        <span class="text-muted-foreground">
                            Progress
                        </span>

                        <span>
                            {{ item.progress }}%
                        </span>

                    </div>

                    <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                        <div class="h-full rounded-full bg-primary" :style="{
                            width: `${item.progress}%`
                        }" />
                    </div>

                </div>


                <Link :href="`/client/interconnections/${item.id}`"
                    class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border py-2.5 text-sm font-medium hover:bg-muted">
                    <Eye class="h-4 w-4" />
                    View Details
                </Link>

            </div>

        </div>

    </div>

</template>