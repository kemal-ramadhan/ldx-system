<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { CalendarPlus, Eye, Search, Users } from 'lucide-vue-next'
import { type Visit, formatDate, formatTime, stageClass } from '@/lib/visitRequest'

/**
 * Client portal: list of the company's visit requests.
 */
const props = defineProps<{
    visits: {
        data: Visit[]
        links: { url: string | null; label: string; active: boolean }[]
        from: number | null
        to: number | null
        total: number
    }
    filters: { status?: string; search?: string }
}>()

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Pengajuan Kunjungan', href: '/client/visit-requests' }],
    },
})

const search = ref(props.filters.search ?? '')
const status = ref(props.filters.status ?? 'all')

const statuses = [
    { key: 'all', label: 'Semua' },
    { key: 'pending', label: 'Menunggu' },
    { key: 'approved', label: 'Disetujui' },
    { key: 'rejected', label: 'Ditolak' },
    { key: 'cancelled', label: 'Dibatalkan' },
]

const apply = () => {
    router.get(
        '/client/visit-requests',
        { search: search.value || undefined, status: status.value !== 'all' ? status.value : undefined },
        { preserveState: true, preserveScroll: true, replace: true },
    )
}

const checkedIn = (visit: Visit) => (visit.members ?? []).filter((m) => m.checked_in_at).length
</script>

<template>
    <Head title="Pengajuan Kunjungan" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold">Pengajuan Kunjungan</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Ajukan kunjungan ke data center. Setelah disetujui, tamu cukup check-in dengan kode kunjungan.
                </p>
            </div>
            <Link href="/client/visit-requests/create"
                class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                <CalendarPlus class="h-4 w-4" /> Ajukan Kunjungan
            </Link>
        </div>

        <div class="flex flex-col gap-3 rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent md:flex-row md:items-center md:justify-between">
            <div class="flex flex-wrap gap-2">
                <button v-for="s in statuses" :key="s.key" type="button" @click="status = s.key; apply()" :class="[
                    'rounded-lg px-3 py-1.5 text-sm font-medium transition',
                    status === s.key ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:bg-muted',
                ]">{{ s.label }}</button>
            </div>
            <form class="relative md:w-72" @submit.prevent="apply">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="search" type="text" placeholder="Cari nomor / keperluan..."
                    class="w-full rounded-xl border border-input bg-background py-2.5 pl-9 pr-3 text-sm" />
            </form>
        </div>

        <div class="overflow-x-auto rounded-2xl border bg-card shadow-sm dark:bg-transparent">
            <table class="min-w-full text-sm">
                <thead class="border-b bg-muted/50 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3">Pengajuan</th>
                        <th class="px-4 py-3">Jadwal</th>
                        <th class="px-4 py-3">Keperluan</th>
                        <th class="px-4 py-3">Tamu</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="visit in visits.data" :key="visit.id" class="hover:bg-muted/50">
                        <td class="px-4 py-3 font-medium">{{ visit.request_number }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <div>{{ formatDate(visit.visit_date) }}</div>
                            <div class="text-xs text-muted-foreground">{{ formatTime(visit.start_time) }}–{{ formatTime(visit.end_time) }} WIB</div>
                        </td>
                        <td class="max-w-xs truncate px-4 py-3">{{ visit.purpose }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex items-center gap-1"><Users class="h-3.5 w-3.5" /> {{ visit.members_count }}</span>
                            <span v-if="checkedIn(visit)" class="ml-1 text-xs text-muted-foreground">({{ checkedIn(visit) }} check-in)</span>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="['whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold', stageClass(visit.stage)]">
                                {{ visit.stage_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="`/client/visit-requests/${visit.id}`"
                                class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium hover:bg-muted">
                                <Eye class="h-3.5 w-3.5" /> Detail
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="!visits.data.length">
                        <td colspan="6" class="px-4 py-14 text-center text-muted-foreground">
                            Belum ada pengajuan kunjungan.
                            <Link href="/client/visit-requests/create" class="font-medium text-primary hover:underline">Ajukan sekarang</Link>
                        </td>
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
