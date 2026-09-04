<script setup lang="ts">

import { Head, Link } from '@inertiajs/vue3'
import {
    Plus,
    ArrowRight,
    Cable,
    Eye,
} from 'lucide-vue-next'

const props = defineProps<{
    title: string
    interconnections: any
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Interconnections',
                href: '/admin/interconnections',
            },
        ],
    },
})

const statusClass = (status: string) => {

    const classes: Record<string, string> = {
        draft: 'bg-gray-100 text-gray-700',
        submitted: 'bg-blue-100 text-blue-700',
        waiting_destination_approval: 'bg-yellow-100 text-yellow-700',
        waiting_dc_approval: 'bg-orange-100 text-orange-700',
        approved: 'bg-green-100 text-green-700',
        in_progress: 'bg-indigo-100 text-indigo-700',
        testing: 'bg-purple-100 text-purple-700',
        completed: 'bg-emerald-100 text-emerald-700',
        rejected: 'bg-red-100 text-red-700',
        cancelled: 'bg-gray-100 text-gray-500',
    }

    return classes[status] ?? 'bg-gray-100 text-gray-700'
}

const formatStatus = (status: string) => {

    return status
        ?.replaceAll('_', ' ')
        ?.replace(/\b\w/g, (char) => char.toUpperCase())
}

</script>

<template>

    <Head :title="title" />

    <div class="p-4">

        <!-- HEADER -->

        <div class="mb-6 flex items-center justify-between">

            <div>

                <h1 class="text-lg font-semibold">
                    Interconnections
                </h1>

                <p class="mt-1 text-sm text-muted-foreground">
                    Manage interconnection requests between clients and devices.
                </p>

            </div>

            <Link href="/admin/interconnections/create"
                class="inline-flex items-center rounded-xl bg-primary px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90">

                <Plus class="mr-2 h-4 w-4" />

                New Interconnection

            </Link>

        </div>


        <!-- EMPTY -->

        <div v-if="props.interconnections.data.length === 0" class="rounded-2xl border border-dashed p-12 text-center">

            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">

                <Cable class="h-6 w-6 text-gray-400" />

            </div>

            <h3 class="text-sm font-semibold">
                No interconnection requests
            </h3>

            <p class="mt-1 text-xs text-muted-foreground">
                Create your first interconnection request.
            </p>

        </div>


        <!-- TABLE -->

        <div v-else class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:bg-transparent">

            <table class="min-w-full divide-y divide-gray-200">

                <thead class="bg-gray-50">

                    <tr class="text-left text-xs font-semibold text-muted-foreground">

                        <th class="px-5 py-4">
                            Request
                        </th>

                        <th class="px-5 py-4">
                            Connection
                        </th>

                        <th class="px-5 py-4">
                            Cable
                        </th>

                        <th class="px-5 py-4">
                            Status
                        </th>

                        <th class="px-5 py-4">
                            Progress
                        </th>

                        <th class="px-5 py-4 text-right">
                            Action
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-100">

                    <tr v-for="item in props.interconnections.data" :key="item.id" class="hover:bg-gray-50/70">

                        <!-- REQUEST -->

                        <td class="px-5 py-4">

                            <div class="flex flex-col">

                                <span class="font-medium">
                                    {{ item.request_number }}
                                </span>

                                <span class="text-xs text-muted-foreground">
                                    {{ item.requester_client?.company_name || '-' }}
                                </span>

                            </div>

                        </td>


                        <!-- CONNECTION -->

                        <td class="px-5 py-4">

                            <div class="flex items-center gap-3">

                                <div class="min-w-0">

                                    <p class="truncate text-sm font-medium">
                                        {{ item.source_port?.device?.divice_name }}
                                    </p>

                                    <p class="text-xs text-muted-foreground">
                                        {{ item.source_port?.port_name }}
                                    </p>

                                </div>

                                <ArrowRight class="h-4 w-4 shrink-0 text-gray-400" />

                                <div class="min-w-0">

                                    <p class="truncate text-sm font-medium">
                                        {{ item.destination_port?.device?.divice_name }}
                                    </p>

                                    <p class="text-xs text-muted-foreground">
                                        {{ item.destination_port?.port_name }}
                                    </p>

                                </div>

                            </div>

                        </td>


                        <!-- CABLE -->

                        <td class="px-5 py-4">

                            <div class="flex flex-col">

                                <span class="text-sm capitalize">
                                    {{ item.cable_type?.replace('_', ' ') }}
                                </span>

                                <span class="text-xs text-muted-foreground">
                                    {{ item.connector_type || '-' }}
                                </span>

                            </div>

                        </td>


                        <!-- STATUS -->

                        <td class="px-5 py-4">

                            <span :class="[
                                'inline-flex rounded-full px-3 py-1 text-xs font-medium',
                                statusClass(item.status)
                            ]">
                                {{ formatStatus(item.status) }}
                            </span>

                        </td>


                        <!-- PROGRESS -->

                        <td class="px-5 py-4">

                            <div class="w-28">

                                <div class="mb-1 flex justify-between">

                                    <span class="text-xs text-muted-foreground">
                                        Progress
                                    </span>

                                    <span class="text-xs font-medium">
                                        {{ item.progress }}%
                                    </span>

                                </div>

                                <div class="h-1.5 overflow-hidden rounded-full bg-gray-100">

                                    <div class="h-full rounded-full bg-primary transition-all" :style="{
                                        width: `${item.progress}%`
                                    }"></div>

                                </div>

                            </div>

                        </td>


                        <!-- ACTION -->

                        <td class="px-5 py-4">

                            <div class="flex justify-end">

                                <Link :href="`/admin/interconnections/${item.id}`"
                                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800"
                                    title="View">

                                    <Eye class="h-4 w-4" />

                                </Link>

                            </div>

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</template>