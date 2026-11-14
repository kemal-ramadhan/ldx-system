<script setup lang="ts">
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { CalendarCheck, Clock3, Eye, Search, UserCheck, XCircle } from 'lucide-vue-next'
import { type Visit, formatDate, formatDateTime, formatTime, stageClass, todayLocal } from '@/lib/visitRequest'

/**
 * Admin: visit requests from the client portal.
 */
const props = defineProps<{
    title: string
    visits: {
        data: Visit[]
        links: { url: string | null; label: string; active: boolean }[]
        from: number | null
        to: number | null
        total: number
    }
    summary: { pending: number; today: number; visiting: number; rejected: number }
    clients: { id: number; company_name: string }[]
    filters: Record<string, string | undefined>
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Visitor Management', href: '/admin/visitors' },
            { title: 'Pengajuan Kunjungan', href: '/admin/visit-requests' },
        ],
    },
})

const filters = reactive({
    status: props.filters.status ?? 'all',
    client_id: props.filters.client_id ?? '',
    date: props.filters.date ?? '',
    search: props.filters.search ?? '',
})

const apply = () => {
    const query = Object.fromEntries(Object.entries(filters).filter(([k, v]) => v && !(k === 'status' && v === 'all')))
    router.get('/admin/visit-requests', query, { preserveState: true, preserveScroll: true, replace: true })
}

const cards = [
    { key: 'pending', label: 'Menunggu Persetujuan', icon: Clock3, tone: 'text-amber-600 dark:text-amber-400', status: 'pending' },
    { key: 'today', label: 'Kunjungan Hari Ini', icon: CalendarCheck, tone: 'text-blue-600 dark:text-blue-400', status: 'approved' },
    { key: 'visiting', label: 'Sedang Berkunjung', icon: UserCheck, tone: 'text-emerald-600 dark:text-emerald-400', status: 'approved' },
    { key: 'rejected', label: 'Ditolak', icon: XCircle, tone: 'text-red-600 dark:text-red-400', status: 'rejected' },
] as const

const inputClass = 'rounded-xl border border-input bg-background px-3 py-2.5 text-sm'
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <div>
            <h1 class="text-xl font-semibold">Pengajuan Kunjungan</h1>
            <p class="mt-1 text-sm text-muted-foreground">Pengajuan dari portal client. Setujui agar tamu bisa check-in dengan kode kunjungan.</p>
        </div>

        <!-- SUMMARY -->
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <button v-for="card in cards" :key="card.key" type="button"
                @click="filters.status = card.status; filters.date = card.key === 'today' ? todayLocal() : ''; apply()"
                class="rounded-2xl border bg-card p-4 text-left shadow-sm transition hover:bg-muted/50 dark:bg-transparent">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted-foreground">{{ card.label }}</span>
                    <component :is="card.icon" :class="['h-4 w-4', card.tone]" />
                </div>
                <p :class="['mt-2 text-2xl font-bold', card.tone]">{{ summary[card.key] }}</p>
            </button>
        </div>

        <!-- FILTERS -->
        <form class="grid grid-cols-1 gap-3 rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent md:grid-cols-5" @submit.prevent="apply">
            <div class="relative md:col-span-2">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="filters.search" type="text" placeholder="No. / client / nama tamu / NIK" :class="[inputClass, 'w-full pl-9']" />
            </div>
            <select v-model="filters.status" :class="inputClass">
                <option value="all">Semua status</option>
                <option value="pending">Menunggu</option>
                <option value="approved">Disetujui</option>
                <option value="rejected">Ditolak</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
            <select v-model="filters.client_id" :class="inputClass">
                <option value="">Semua client</option>
                <option v-for="c in clients" :key="c.id" :value="String(c.id)">{{ c.company_name }}</option>
            </select>
            <div class="flex gap-2">
                <input v-model="filters.date" type="date" :class="[inputClass, 'min-w-0 flex-1']" />
                <button type="submit" class="rounded-xl bg-primary px-4 text-sm font-medium text-primary-foreground hover:bg-primary/90">Filter</button>
            </div>
        </form>

        <!-- TABLE -->
        <div class="overflow-x-auto rounded-2xl border bg-card shadow-sm dark:bg-transparent">
            <table class="min-w-full text-sm">
                <thead class="border-b bg-muted/50 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3">Pengajuan</th>
                        <th class="px-4 py-3">Client / Pengaju</th>
                        <th class="px-4 py-3">Jadwal</th>
                        <th class="px-4 py-3">Keperluan</th>
                        <th class="px-4 py-3">Tamu</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="visit in visits.data" :key="visit.id"
                        :class="['hover:bg-muted/50', visit.status === 'pending' ? 'bg-amber-50/60 dark:bg-amber-950/20' : '']">
                        <td class="whitespace-nowrap px-4 py-3">
                            <div class="font-medium">{{ visit.request_number }}</div>
                            <div class="text-xs text-muted-foreground">{{ formatDateTime(visit.created_at) }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ visit.client?.company_name }}</div>
                            <div class="text-xs text-muted-foreground">oleh {{ visit.requester?.name ?? '-' }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <div>{{ formatDate(visit.visit_date) }}</div>
                            <div class="text-xs text-muted-foreground">{{ formatTime(visit.start_time) }}–{{ formatTime(visit.end_time) }}</div>
                        </td>
                        <td class="max-w-xs px-4 py-3">
                            <div class="truncate">{{ visit.purpose }}</div>
                            <div class="text-xs text-muted-foreground">{{ visit.type_label }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            {{ visit.members_count }} orang
                            <div v-if="(visit.members ?? []).some((m) => m.checked_in_at)" class="text-xs text-muted-foreground">
                                {{ (visit.members ?? []).filter((m) => m.checked_in_at).length }} check-in
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="['whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold', stageClass(visit.stage)]">{{ visit.stage_label }}</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="`/admin/visit-requests/${visit.id}`" :class="[
                                'inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium',
                                visit.status === 'pending' ? 'bg-primary text-primary-foreground hover:bg-primary/90' : 'border hover:bg-muted',
                            ]">
                                <Eye class="h-3.5 w-3.5" /> {{ visit.status === 'pending' ? 'Tinjau' : 'Detail' }}
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="!visits.data.length">
                        <td colspan="7" class="px-4 py-14 text-center text-muted-foreground">Tidak ada pengajuan kunjungan.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="visits.links.length > 3" class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-muted-foreground">{{ visits.from ?? 0 }}–{{ visits.to ?? 0 }} dari {{ visits.total }}</p>
            <div class="flex flex-wrap gap-1">
                <template v-for="link in visits.links" :key="link.label">
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
