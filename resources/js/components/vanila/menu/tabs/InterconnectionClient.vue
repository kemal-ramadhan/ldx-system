<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ArrowRight, Eye, Cable } from 'lucide-vue-next'

defineProps<{
    interconnections?: any[]
}>()

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
    <div class="overflow-hidden rounded-xl border dark:border-gray-700">
        <table class="w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr class="text-left text-xs font-semibold text-muted-foreground dark:text-gray-300">
                    <th class="px-5 py-4">Request</th>
                    <th class="px-5 py-4">Connection</th>
                    <th class="px-5 py-4">Cable</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Progress</th>
                    <th class="px-5 py-4 text-right">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                <tr
                    v-for="item in (interconnections || [])"
                    :key="item.id"
                    class="hover:bg-gray-50/70 dark:hover:bg-gray-800/50"
                >
                    <!-- REQUEST -->
                    <td class="px-5 py-4">
                        <div class="flex flex-col">
                            <span class="font-medium dark:text-gray-100">{{ item.request_number }}</span>
                            <span class="text-xs text-muted-foreground">{{ item.requester_client?.company_name || '-' }}</span>
                        </div>
                    </td>

                    <!-- CONNECTION -->
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium dark:text-gray-100">{{ item.source_port?.device?.divice_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ item.source_port?.port_name }}</p>
                            </div>
                            <ArrowRight class="h-4 w-4 shrink-0 text-gray-400" />
                            <div class="min-w-0" v-if="item.destination_type === 'external'">
                                <p class="truncate text-sm font-medium dark:text-gray-100">{{ item.external_device_name || 'External Device' }}</p>
                                <p class="text-xs text-muted-foreground">{{ item.external_port_name || '-' }}</p>
                            </div>
                            <div class="min-w-0" v-else>
                                <p class="truncate text-sm font-medium dark:text-gray-100">{{ item.destination_port?.device?.divice_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ item.destination_port?.port_name }}</p>
                            </div>
                        </div>
                    </td>

                    <!-- CABLE -->
                    <td class="px-5 py-4">
                        <div class="flex flex-col">
                            <span class="text-sm capitalize dark:text-gray-100">{{ item.cable_type?.replace('_', ' ') }}</span>
                            <span class="text-xs text-muted-foreground">{{ item.connector_type || '-' }}</span>
                        </div>
                    </td>

                    <!-- STATUS -->
                    <td class="px-5 py-4">
                        <span :class="['inline-flex rounded-full px-3 py-1 text-xs font-medium', statusClass(item.status)]">
                            {{ formatStatus(item.status) }}
                        </span>
                    </td>

                    <!-- PROGRESS -->
                    <td class="px-5 py-4">
                        <div class="w-28">
                            <div class="mb-1 flex justify-between">
                                <span class="text-xs text-muted-foreground">Progress</span>
                                <span class="text-xs font-medium dark:text-gray-100">{{ item.progress }}%</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-700">
                                <div class="h-full rounded-full bg-primary transition-all" :style="{ width: `${item.progress}%` }"></div>
                            </div>
                        </div>
                    </td>

                    <!-- ACTION -->
                    <td class="px-5 py-4">
                        <div class="flex justify-end">
                            <Link :href="`/admin/interconnections/${item.id}`"
                                class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-800 dark:hover:bg-gray-700 dark:hover:text-gray-100"
                                title="View">
                                <Eye class="h-4 w-4" />
                            </Link>
                        </div>
                    </td>
                </tr>

                <tr v-if="!(interconnections && interconnections.length)">
                    <td colspan="6" class="py-12 text-center">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-800">
                            <Cable class="h-6 w-6 text-gray-400" />
                        </div>
                        <h3 class="text-sm font-semibold dark:text-gray-100">No interconnection requests</h3>
                        <p class="mt-1 text-xs text-muted-foreground">This client hasn't created any interconnection requests yet.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
