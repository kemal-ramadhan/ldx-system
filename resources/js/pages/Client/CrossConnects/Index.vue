<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ArrowRight, Ban, Cable, CheckCircle2, Clock3, Eye, Search, XCircle } from 'lucide-vue-next'
import {
    type ClientCrossConnect,
    endpoint,
    formatDate,
    statusClass,
    statusLabel,
} from '@/lib/crossConnect'

/**
 * Client portal: cross connects that use at least one of the client's ports.
 */
defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Cross Connect', href: '/client/cross-connects' }],
    },
})

const props = defineProps<{
    crossConnects: {
        data: ClientCrossConnect[]
        links: { url: string | null; label: string; active: boolean }[]
        from: number | null
        to: number | null
        total: number
    }
    statistics: { total: number; active: number; planned: number; terminated: number; cancelled: number }
    filters: { status: string; search: string }
}>()

const search = ref(props.filters.search ?? '')
const selectedStatus = ref(props.filters.status ?? 'all')

const statuses = [
    { key: 'all', label: 'All' },
    { key: 'active', label: 'Active' },
    { key: 'planned', label: 'Planned' },
    { key: 'terminated', label: 'Terminated' },
    { key: 'cancelled', label: 'Cancelled' },
]

const submitFilter = () => {
    router.get(
        '/client/cross-connects',
        {
            search: search.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    )
}

const selectStatus = (key: string) => {
    selectedStatus.value = key
    submitFilter()
}

const stats = [
    { key: 'total', label: 'Total', icon: Cable, tone: 'text-foreground' },
    { key: 'active', label: 'Active', icon: CheckCircle2, tone: 'text-emerald-600 dark:text-emerald-400' },
    { key: 'planned', label: 'Planned', icon: Clock3, tone: 'text-amber-600 dark:text-amber-400' },
    { key: 'terminated', label: 'Terminated', icon: Ban, tone: 'text-muted-foreground' },
    { key: 'cancelled', label: 'Cancelled', icon: XCircle, tone: 'text-red-600 dark:text-red-400' },
] as const
</script>

<template>
    <Head title="Cross Connect" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <!-- HEADER -->
        <div>
            <h1 class="text-xl font-semibold">Cross Connect</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Koneksi fisik yang menggunakan port milik perusahaan Anda.
            </p>
        </div>

        <!-- STATS -->
        <div class="grid grid-cols-2 gap-3 md:grid-cols-5">
            <div v-for="stat in stats" :key="stat.key" class="rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted-foreground">{{ stat.label }}</span>
                    <component :is="stat.icon" :class="['h-4 w-4', stat.tone]" />
                </div>
                <p :class="['mt-2 text-2xl font-bold', stat.tone]">{{ statistics[stat.key] }}</p>
            </div>
        </div>

        <!-- FILTERS -->
        <div class="flex flex-col gap-3 rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap gap-2">
                <button v-for="status in statuses" :key="status.key" type="button" @click="selectStatus(status.key)" :class="[
                    'rounded-lg px-3 py-1.5 text-sm font-medium transition',
                    selectedStatus === status.key
                        ? 'bg-primary text-primary-foreground'
                        : 'text-muted-foreground hover:bg-muted',
                ]">
                    {{ status.label }}
                </button>
            </div>

            <form class="relative md:w-80" @submit.prevent="submitFilter">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="search" type="text" placeholder="Cari nomor CC / request..."
                    class="w-full rounded-xl border border-input bg-background py-2.5 pl-9 pr-3 text-sm" />
            </form>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto rounded-2xl border bg-card shadow-sm dark:bg-transparent">
            <table class="min-w-full text-sm">
                <thead class="border-b bg-muted/50 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-5 py-3">Cross Connect</th>
                        <th class="px-5 py-3">Source</th>
                        <th class="px-5 py-3"></th>
                        <th class="px-5 py-3">Destination</th>
                        <th class="px-5 py-3">Installed</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border">
                    <tr v-for="cc in crossConnects.data" :key="cc.id" class="hover:bg-muted/50">
                        <td class="px-5 py-4">
                            <div class="font-medium">{{ cc.cross_connect_number }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ cc.interconnection_request?.request_number ?? '-' }}
                            </div>
                            <span v-if="cc.terminate_requested_at"
                                class="mt-1 inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-700 dark:bg-amber-950/40 dark:text-amber-300">
                                Terminate diajukan
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            <div class="font-medium">{{ endpoint(cc, 'source').client }}</div>
                            <div class="text-xs text-muted-foreground">
                                {{ endpoint(cc, 'source').device }} · {{ endpoint(cc, 'source').port }}
                            </div>
                            <span v-if="cc.my_side === 'source'" class="text-[10px] font-semibold text-primary">Milik Anda</span>
                        </td>

                        <td class="px-2 py-4">
                            <ArrowRight class="h-4 w-4 text-muted-foreground" />
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2 font-medium">
                                {{ endpoint(cc, 'destination').client }}
                                <span v-if="endpoint(cc, 'destination').badge"
                                    class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                                    {{ endpoint(cc, 'destination').badge }}
                                </span>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                {{ endpoint(cc, 'destination').device }} · {{ endpoint(cc, 'destination').port }}
                            </div>
                            <span v-if="cc.my_side === 'destination'" class="text-[10px] font-semibold text-primary">Milik Anda</span>
                        </td>

                        <td class="whitespace-nowrap px-5 py-4 text-muted-foreground">{{ formatDate(cc.installed_at) }}</td>

                        <td class="px-5 py-4">
                            <span :class="['inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset', statusClass(cc.status)]">
                                {{ statusLabel(cc.status) }}
                            </span>
                        </td>

                        <td class="px-5 py-4 text-right">
                            <Link :href="`/client/cross-connects/${cc.id}`"
                                class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium hover:bg-muted">
                                <Eye class="h-3.5 w-3.5" />
                                Detail
                            </Link>
                        </td>
                    </tr>

                    <tr v-if="!crossConnects.data.length">
                        <td colspan="7" class="px-5 py-14 text-center text-sm text-muted-foreground">
                            Belum ada cross connect yang menggunakan port Anda.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div v-if="crossConnects.links.length > 3" class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-muted-foreground">
                {{ crossConnects.from ?? 0 }}–{{ crossConnects.to ?? 0 }} dari {{ crossConnects.total }}
            </p>
            <div class="flex flex-wrap gap-1">
                <template v-for="link in crossConnects.links" :key="link.label">
                    <Link v-if="link.url" :href="link.url" preserve-scroll :class="[
                        'rounded-lg border px-3 py-1.5 text-sm',
                        link.active ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted',
                    ]"><span v-html="link.label" /></Link>
                    <span v-else class="rounded-lg border px-3 py-1.5 text-sm text-muted-foreground opacity-50" v-html="link.label" />
                </template>
            </div>
        </div>
    </div>
</template>
