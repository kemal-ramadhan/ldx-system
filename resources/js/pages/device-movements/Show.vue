<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { ArrowLeft, ArrowRight, Download, FileText, History } from 'lucide-vue-next'
import {
    type Movement,
    PLACEMENT_LABELS,
    TYPE_LABELS,
    fileSize,
    formatDateTime,
    placementClass,
    position,
    typeClass,
} from '@/lib/deviceMovement'

/**
 * Admin: detail of one device movement with photos / BAST.
 */
const props = defineProps<{
    title: string
    movement: Movement & {
        device?: { id: number; code?: string; divice_name?: string; placement_status?: string; deleted_at?: string | null } | null
        visitor?: { id: number; name: string; company_name?: string; nik?: string; phone?: string } | null
    }
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Keluar-Masuk Device', href: '/admin/device-movements' },
            { title: 'Detail', href: '#' },
        ],
    },
})

const m = computed(() => props.movement)
const photos = computed(() => (m.value.attachments ?? []).filter((a) => a.is_image))
const documents = computed(() => (m.value.attachments ?? []).filter((a) => !a.is_image))

const destination = computed(() =>
    m.value.type === 'check_out'
        ? 'Keluar dari data center'
        : m.value.type === 'temp_out'
            ? 'Keluar sementara'
            : position(m.value.to_rack, m.value.to_start_unit, m.value.to_end_unit),
)

const rows = computed(() => [
    { label: 'Client', value: m.value.client?.company_name ?? '-' },
    { label: 'Device', value: m.value.device_name },
    { label: 'Kode', value: m.value.device_code ?? '-' },
    { label: 'Model', value: m.value.model ?? '-' },
    { label: 'Serial Number', value: m.value.serial_number ?? '-' },
    { label: 'Dicatat oleh', value: m.value.recorder?.name ?? 'Sistem (data awal)' },
])
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <!-- HEADER -->
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <Link href="/admin/device-movements" class="rounded-xl border p-2 text-muted-foreground hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl font-semibold">{{ m.movement_number }}</h1>
                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', typeClass(m.type)]">{{ TYPE_LABELS[m.type] }}</span>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground">{{ formatDateTime(m.occurred_at) }}</p>
                </div>
            </div>

            <Link :href="`/admin/devices/${m.rack_divice_id}/history`"
                class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium hover:bg-muted">
                <History class="h-4 w-4" /> Riwayat device
            </Link>
        </div>

        <!-- ROUTE -->
        <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
            <div class="grid grid-cols-1 items-center gap-4 md:grid-cols-[1fr_auto_1fr]">
                <div class="rounded-xl bg-muted/50 p-4">
                    <p class="text-xs text-muted-foreground">Asal</p>
                    <p class="mt-1 font-semibold">
                        {{ m.type === 'check_in' ? 'Luar data center' : m.type === 'return_in' && !m.from_rack ? 'Luar data center' : position(m.from_rack, m.from_start_unit, m.from_end_unit) }}
                    </p>
                </div>
                <ArrowRight class="mx-auto h-5 w-5 rotate-90 text-muted-foreground md:rotate-0" />
                <div class="rounded-xl bg-muted/50 p-4">
                    <p class="text-xs text-muted-foreground">Tujuan</p>
                    <p class="mt-1 font-semibold">{{ destination }}</p>
                    <p v-if="m.type === 'temp_out'" class="mt-1 text-xs text-muted-foreground">
                        {{ m.units_reserved ? 'Posisi unit ditahan sampai device kembali' : 'Posisi unit dilepas' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid gap-5 lg:grid-cols-2">
            <!-- DEVICE -->
            <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-semibold">Data Device (saat kejadian)</h2>
                    <span v-if="m.device" :class="['rounded-full px-2 py-0.5 text-[10px] font-semibold', placementClass(m.device.placement_status)]">
                        Sekarang: {{ PLACEMENT_LABELS[(m.device.placement_status ?? 'in_rack') as keyof typeof PLACEMENT_LABELS] }}
                    </span>
                </div>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div v-for="row in rows" :key="row.label">
                        <dt class="text-xs text-muted-foreground">{{ row.label }}</dt>
                        <dd class="mt-1 font-medium">{{ row.value }}</dd>
                    </div>
                </dl>
            </div>

            <!-- CARRIER -->
            <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
                <h2 class="mb-4 font-semibold">Pembawa Barang</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-xs text-muted-foreground">Nama</dt>
                        <dd class="mt-1 font-medium">{{ m.carrier_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Perusahaan</dt>
                        <dd class="mt-1 font-medium">{{ m.carrier_company ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">No. Identitas</dt>
                        <dd class="mt-1 font-medium">{{ m.carrier_id_number ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Telepon</dt>
                        <dd class="mt-1 font-medium">{{ m.carrier_phone ?? '-' }}</dd>
                    </div>
                    <div v-if="movement.visitor" class="col-span-2">
                        <dt class="text-xs text-muted-foreground">Buku Tamu</dt>
                        <dd class="mt-1">
                            <Link :href="`/admin/visitors/${movement.visitor.id}`" class="font-medium text-primary hover:underline">
                                {{ movement.visitor.name }} — {{ movement.visitor.company_name }}
                            </Link>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- REASON / NOTES -->
        <div v-if="m.reason || m.notes" class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
            <div v-if="m.reason">
                <p class="text-xs text-muted-foreground">Alasan</p>
                <p class="mt-1 whitespace-pre-line text-sm">{{ m.reason }}</p>
            </div>
            <div v-if="m.notes" :class="m.reason ? 'mt-4' : ''">
                <p class="text-xs text-muted-foreground">Catatan</p>
                <p class="mt-1 whitespace-pre-line text-sm">{{ m.notes }}</p>
            </div>
        </div>

        <!-- ATTACHMENTS -->
        <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
            <h2 class="mb-4 font-semibold">Foto &amp; Dokumen</h2>

            <p v-if="!(m.attachments ?? []).length" class="text-sm text-muted-foreground">Tidak ada lampiran.</p>

            <div v-if="photos.length" class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                <a v-for="photo in photos" :key="photo.id" :href="photo.url" target="_blank" rel="noopener"
                    class="group overflow-hidden rounded-xl border">
                    <img :src="photo.url" :alt="photo.file_name" class="aspect-square w-full object-cover transition group-hover:scale-105" />
                </a>
            </div>

            <ul v-if="documents.length" class="mt-4 space-y-2">
                <li v-for="doc in documents" :key="doc.id">
                    <a :href="doc.url" target="_blank" rel="noopener"
                        class="flex items-center justify-between gap-3 rounded-xl border px-4 py-3 text-sm hover:bg-muted">
                        <span class="flex min-w-0 items-center gap-2">
                            <FileText class="h-4 w-4 shrink-0" />
                            <span class="truncate">{{ doc.file_name }}</span>
                            <span class="shrink-0 rounded bg-muted px-1.5 py-0.5 text-[10px] font-semibold uppercase">{{ doc.type }}</span>
                        </span>
                        <span class="flex shrink-0 items-center gap-2 text-xs text-muted-foreground">
                            {{ fileSize(doc.file_size) }} <Download class="h-4 w-4" />
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>
