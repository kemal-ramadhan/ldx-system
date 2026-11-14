<script setup lang="ts">
import { reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ArrowRight, CalendarClock, Eye, LogOut, PackageCheck, PackageX, Paperclip, Search } from 'lucide-vue-next'
import { type Movement, TYPE_LABELS, formatDateTime, position, typeClass } from '@/lib/deviceMovement'

/**
 * Admin: log of every device movement (keluar-masuk) in the data center.
 */
const props = defineProps<{
    title: string
    movements: {
        data: Movement[]
        links: { url: string | null; label: string; active: boolean }[]
        from: number | null
        to: number | null
        total: number
    }
    summary: { in_rack: number; out_temporary: number; removed: number; today: number }
    types: Record<string, string>
    racks: { id: number; code?: string; name?: string }[]
    clients: { id: number; company_name: string }[]
    filters: Record<string, string | undefined>
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Rack Management', href: '/admin/racks' },
            { title: 'Keluar-Masuk Device', href: '/admin/device-movements' },
        ],
    },
})

const filters = reactive({
    search: props.filters.search ?? '',
    type: props.filters.type ?? 'all',
    rack_id: props.filters.rack_id ?? '',
    client_id: props.filters.client_id ?? '',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
})

const apply = () => {
    const query = Object.fromEntries(Object.entries(filters).filter(([k, v]) => v && !(k === 'type' && v === 'all')))
    router.get('/admin/device-movements', query, { preserveState: true, preserveScroll: true, replace: true })
}

const reset = () => {
    Object.assign(filters, { search: '', type: 'all', rack_id: '', client_id: '', date_from: '', date_to: '' })
    apply()
}

const cards = [
    { key: 'in_rack', label: 'Device di Rack', icon: PackageCheck, tone: 'text-emerald-600 dark:text-emerald-400' },
    { key: 'out_temporary', label: 'Keluar Sementara', icon: LogOut, tone: 'text-amber-600 dark:text-amber-400' },
    { key: 'removed', label: 'Sudah Keluar', icon: PackageX, tone: 'text-muted-foreground' },
    { key: 'today', label: 'Pergerakan Hari Ini', icon: CalendarClock, tone: 'text-blue-600 dark:text-blue-400' },
] as const

const destination = (m: Movement) =>
    m.type === 'check_out' ? 'Keluar DC' : m.type === 'temp_out' ? 'Keluar sementara' : position(m.to_rack, m.to_start_unit, m.to_end_unit)

const inputClass = 'rounded-xl border border-input bg-background px-3 py-2.5 text-sm'
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <div>
            <h1 class="text-xl font-semibold">Keluar-Masuk Device</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Riwayat barang/device yang masuk, keluar, dan berpindah di data center. Aksi dilakukan dari tab Device di
                halaman rack.
            </p>
        </div>

        <!-- SUMMARY -->
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <div v-for="card in cards" :key="card.key" class="rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted-foreground">{{ card.label }}</span>
                    <component :is="card.icon" :class="['h-4 w-4', card.tone]" />
                </div>
                <p :class="['mt-2 text-2xl font-bold', card.tone]">{{ summary[card.key] }}</p>
            </div>
        </div>

        <!-- FILTERS -->
        <form class="grid grid-cols-1 gap-3 rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent md:grid-cols-6" @submit.prevent="apply">
            <div class="relative md:col-span-2">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="filters.search" type="text" placeholder="No. / device / serial / pembawa"
                    :class="[inputClass, 'w-full pl-9']" />
            </div>
            <select v-model="filters.type" :class="inputClass">
                <option value="all">Semua jenis</option>
                <option v-for="(label, key) in types" :key="key" :value="key">{{ label }}</option>
            </select>
            <select v-model="filters.rack_id" :class="inputClass">
                <option value="">Semua rack</option>
                <option v-for="rack in racks" :key="rack.id" :value="String(rack.id)">{{ rack.code ?? rack.name }}</option>
            </select>
            <select v-model="filters.client_id" :class="inputClass">
                <option value="">Semua client</option>
                <option v-for="client in clients" :key="client.id" :value="String(client.id)">{{ client.company_name }}</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 rounded-xl bg-primary px-3 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90">
                    Filter
                </button>
                <button type="button" @click="reset" class="rounded-xl border px-3 py-2.5 text-sm hover:bg-muted">Reset</button>
            </div>
            <div class="flex items-center gap-2 md:col-span-3">
                <span class="text-xs text-muted-foreground">Dari</span>
                <input v-model="filters.date_from" type="date" :class="[inputClass, 'flex-1']" />
                <span class="text-xs text-muted-foreground">s/d</span>
                <input v-model="filters.date_to" type="date" :class="[inputClass, 'flex-1']" />
            </div>
        </form>

        <!-- TABLE -->
        <div class="overflow-x-auto rounded-2xl border bg-card shadow-sm dark:bg-transparent">
            <table class="min-w-full text-sm">
                <thead class="border-b bg-muted/50 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Device</th>
                        <th class="px-4 py-3">Client</th>
                        <th class="px-4 py-3">Asal → Tujuan</th>
                        <th class="px-4 py-3">Pembawa</th>
                        <th class="px-4 py-3">Petugas</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="m in movements.data" :key="m.id" class="hover:bg-muted/50">
                        <td class="whitespace-nowrap px-4 py-3">
                            <div>{{ formatDateTime(m.occurred_at) }}</div>
                            <div class="text-xs text-muted-foreground">{{ m.movement_number }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="['whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold', typeClass(m.type)]">
                                {{ TYPE_LABELS[m.type] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <Link :href="`/admin/devices/${m.rack_divice_id}/history`" class="font-medium hover:underline">
                                {{ m.device_name }}
                            </Link>
                            <div class="text-xs text-muted-foreground">
                                {{ m.device_code }}<template v-if="m.serial_number"> · SN {{ m.serial_number }}</template>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ m.client?.company_name ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="text-muted-foreground">{{ position(m.from_rack, m.from_start_unit, m.from_end_unit) }}</span>
                            <ArrowRight class="mx-1 inline h-3.5 w-3.5 text-muted-foreground" />
                            <span>{{ destination(m) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ m.carrier_name ?? '-' }}</div>
                            <div v-if="m.carrier_company" class="text-xs text-muted-foreground">{{ m.carrier_company }}</div>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">{{ m.recorder?.name ?? 'Sistem' }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="`/admin/device-movements/${m.id}`"
                                class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-medium hover:bg-muted">
                                <Paperclip v-if="m.attachments_count" class="h-3.5 w-3.5" />
                                <Eye v-else class="h-3.5 w-3.5" />
                                Detail
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="!movements.data.length">
                        <td colspan="8" class="px-4 py-14 text-center text-muted-foreground">Belum ada pergerakan device.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        <div v-if="movements.links.length > 3" class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-muted-foreground">{{ movements.from ?? 0 }}–{{ movements.to ?? 0 }} dari {{ movements.total }}</p>
            <div class="flex flex-wrap gap-1">
                <template v-for="link in movements.links" :key="link.label">
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
