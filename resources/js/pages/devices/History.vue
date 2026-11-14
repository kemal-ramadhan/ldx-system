<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { ArrowLeft, ArrowRight, Paperclip } from 'lucide-vue-next'
import {
    type Movement,
    PLACEMENT_LABELS,
    TYPE_LABELS,
    formatDateTime,
    placementClass,
    position,
    typeClass,
} from '@/lib/deviceMovement'

/**
 * Admin: full keluar-masuk timeline of one device (also after it left the DC).
 */
const props = defineProps<{
    title: string
    device: any
    movements: Movement[]
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Keluar-Masuk Device', href: '/admin/device-movements' },
            { title: 'Riwayat Device', href: '#' },
        ],
    },
})

const d = computed(() => props.device)

const target = (m: Movement) =>
    m.type === 'check_out' ? 'Keluar dari DC' : m.type === 'temp_out' ? 'Keluar sementara' : position(m.to_rack, m.to_start_unit, m.to_end_unit)

const source = (m: Movement) =>
    m.type === 'check_in' || (m.type === 'return_in' && !m.from_rack) ? 'Luar DC' : position(m.from_rack, m.from_start_unit, m.from_end_unit)

const dotClass = (type: string) =>
    ({
        check_in: 'bg-emerald-500',
        return_in: 'bg-teal-500',
        temp_out: 'bg-amber-500',
        relocate: 'bg-blue-500',
        check_out: 'bg-red-500',
    })[type] ?? 'bg-muted-foreground'
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <!-- HEADER -->
        <div class="flex items-start gap-3">
            <Link :href="d.rack ? `/admin/racks/${d.rack.id}` : '/admin/device-movements'" class="rounded-xl border p-2 text-muted-foreground hover:bg-muted">
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-xl font-semibold">{{ d.divice_name }}</h1>
                    <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', placementClass(d.placement_status)]">
                        {{ PLACEMENT_LABELS[(d.placement_status ?? 'in_rack') as keyof typeof PLACEMENT_LABELS] }}
                    </span>
                    <span v-if="d.deleted_at" class="rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold text-muted-foreground">Dihapus</span>
                </div>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ d.code }}<template v-if="d.serial_number"> · SN {{ d.serial_number }}</template>
                    · {{ d.client?.company_name ?? '-' }}
                </p>
            </div>
        </div>

        <!-- CURRENT -->
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <div class="rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent">
                <p class="text-xs text-muted-foreground">Posisi sekarang</p>
                <p class="mt-1 font-semibold">
                    {{ d.placement_status === 'removed' ? 'Sudah keluar' : position(d.rack, d.start_unit, d.end_unit) }}
                </p>
            </div>
            <div class="rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent">
                <p class="text-xs text-muted-foreground">Ukuran</p>
                <p class="mt-1 font-semibold">{{ d.total_unit }}U</p>
            </div>
            <div class="rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent">
                <p class="text-xs text-muted-foreground">Tanggal pasang</p>
                <p class="mt-1 font-semibold">{{ formatDateTime(d.installed_at) }}</p>
            </div>
            <div class="rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent">
                <p class="text-xs text-muted-foreground">Tanggal keluar</p>
                <p class="mt-1 font-semibold">{{ formatDateTime(d.removed_at) }}</p>
            </div>
        </div>

        <!-- TIMELINE -->
        <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
            <h2 class="mb-5 font-semibold">Riwayat Keluar-Masuk ({{ movements.length }})</h2>

            <p v-if="!movements.length" class="text-sm text-muted-foreground">Belum ada riwayat.</p>

            <ol class="relative ml-2 space-y-6 border-l border-border pl-6">
                <li v-for="m in movements" :key="m.id" class="relative">
                    <span :class="['absolute -left-[31px] top-1 h-3 w-3 rounded-full ring-4 ring-card', dotClass(m.type)]" />

                    <div class="flex flex-wrap items-center gap-2">
                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', typeClass(m.type)]">{{ TYPE_LABELS[m.type] }}</span>
                        <span class="text-sm text-muted-foreground">{{ formatDateTime(m.occurred_at) }}</span>
                    </div>

                    <p class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                        <span class="text-muted-foreground">{{ source(m) }}</span>
                        <ArrowRight class="h-3.5 w-3.5 text-muted-foreground" />
                        <span class="font-medium">{{ target(m) }}</span>
                        <span v-if="m.type === 'temp_out'" class="text-xs text-muted-foreground">
                            ({{ m.units_reserved ? 'posisi ditahan' : 'posisi dilepas' }})
                        </span>
                    </p>

                    <p v-if="m.reason" class="mt-1 text-sm">{{ m.reason }}</p>

                    <p class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
                        <span v-if="m.carrier_name">Pembawa: {{ m.carrier_name }}{{ m.carrier_company ? ` (${m.carrier_company})` : '' }}</span>
                        <span>Dicatat: {{ m.recorder?.name ?? 'Sistem' }}</span>
                        <span v-if="m.attachments?.length" class="inline-flex items-center gap-1">
                            <Paperclip class="h-3 w-3" /> {{ m.attachments.length }} lampiran
                        </span>
                        <Link :href="`/admin/device-movements/${m.id}`" class="font-medium text-primary hover:underline">{{ m.movement_number }}</Link>
                    </p>
                </li>
            </ol>
        </div>
    </div>
</template>
