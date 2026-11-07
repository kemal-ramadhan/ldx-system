<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { ArrowRight, Eye, Cable } from 'lucide-vue-next'

defineProps<{
    interconnections?: any[]
}>()

const statusClass = (status: string) => {
    const classes: Record<string, string> = {
        draft: 'bg-muted text-foreground',
        submitted: 'bg-blue-100 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300',
        waiting_destination_approval: 'bg-yellow-100 dark:bg-yellow-950/40 text-yellow-700 dark:text-yellow-300',
        waiting_dc_approval: 'bg-orange-100 dark:bg-orange-950/40 text-orange-700 dark:text-orange-300',
        approved: 'bg-green-100 dark:bg-green-950/40 text-green-700 dark:text-green-300',
        in_progress: 'bg-indigo-100 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300',
        testing: 'bg-purple-100 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300',
        completed: 'bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300',
        rejected: 'bg-red-100 dark:bg-red-950/40 text-red-700 dark:text-red-300',
        cancelled: 'bg-muted text-muted-foreground',
    }
    return classes[status] ?? 'bg-muted text-foreground'
}

const formatStatus = (status: string) => {
    return status
        ?.replaceAll('_', ' ')
        ?.replace(/\b\w/g, (char) => char.toUpperCase())
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border">
        <table class="w-full text-sm divide-y divide-border">
            <thead class="bg-muted/50">
                <tr class="text-left text-xs font-semibold text-muted-foreground">
                    <th class="px-5 py-4">Request</th>
                    <th class="px-5 py-4">Connection</th>
                    <th class="px-5 py-4">Cable</th>
                    <th class="px-5 py-4">Status</th>
                    <th class="px-5 py-4">Progress</th>
                    <th class="px-5 py-4 text-right">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-border">
                <tr
                    v-for="item in (interconnections || [])"
                    :key="item.id"
                    class="hover:bg-muted/50"
                >
                    <!-- REQUEST -->
                    <td class="px-5 py-4">
                        <div class="flex flex-col">
                            <span class="font-medium">{{ item.request_number }}</span>
                            <span class="text-xs text-muted-foreground">{{ item.requester_client?.company_name || '-' }}</span>
                        </div>
                    </td>

                    <!-- CONNECTION -->
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium">{{ item.source_port?.device?.divice_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ item.source_port?.port_name }}</p>
                            </div>
                            <ArrowRight class="h-4 w-4 shrink-0 text-muted-foreground" />
                            <div class="min-w-0" v-if="item.destination_type === 'external'">
                                <p class="truncate text-sm font-medium">{{ item.external_device_name || 'External Device' }}</p>
                                <p class="text-xs text-muted-foreground">{{ item.external_port_name || '-' }}</p>
                                <p class="mt-1 flex flex-wrap items-center gap-1 text-xs text-muted-foreground">
                                    <span class="truncate">{{ item.external_client_name }} · Rack {{ item.external_rack_name || '-' }}</span>
                                    <span v-if="item.external_location" class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">MMR · {{ item.external_location }}</span>
                                </p>
                            </div>
                            <div class="min-w-0" v-else>
                                <p class="truncate text-sm font-medium">{{ item.destination_port?.device?.divice_name }}</p>
                                <p class="text-xs text-muted-foreground">{{ item.destination_port?.port_name }}</p>
                            </div>
                        </div>
                    </td>

                    <!-- CABLE -->
                    <td class="px-5 py-4">
                        <div class="flex flex-col">
                            <span class="text-sm capitalize">{{ item.cable_type?.replace('_', ' ') }}</span>
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
                                <span class="text-xs font-medium">{{ item.progress }}%</span>
                            </div>
                            <div class="h-1.5 overflow-hidden rounded-full bg-muted">
                                <div class="h-full rounded-full bg-primary transition-all" :style="{ width: `${item.progress}%` }"></div>
                            </div>
                        </div>
                    </td>

                    <!-- ACTION -->
                    <td class="px-5 py-4">
                        <div class="flex justify-end">
                            <Link :href="`/admin/interconnections/${item.id}`"
                                class="rounded-lg p-2 text-muted-foreground hover:bg-muted hover:text-foreground"
                                title="View">
                                <Eye class="h-4 w-4" />
                            </Link>
                        </div>
                    </td>
                </tr>

                <tr v-if="!(interconnections && interconnections.length)">
                    <td colspan="6" class="py-12 text-center">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                            <Cable class="h-6 w-6 text-muted-foreground" />
                        </div>
                        <h3 class="text-sm font-semibold">No interconnection requests</h3>
                        <p class="mt-1 text-xs text-muted-foreground">This client hasn't created any interconnection requests yet.</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
