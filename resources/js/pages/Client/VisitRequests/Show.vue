<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ArrowLeft, CheckCircle2, Clock3, Copy, Pencil, QrCode, XCircle } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { type Visit, formatDate, formatDateTime, formatTime, rackLocation, stageClass } from '@/lib/visitRequest'

/**
 * Client portal: visit request detail, check-in code + QR once approved.
 */
const props = defineProps<{
    visit: Visit
    qr: { svg: string; url: string } | null
    canEdit: boolean
    canCancel: boolean
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pengajuan Kunjungan', href: '/client/visit-requests' },
            { title: 'Detail', href: '#' },
        ],
    },
})

const v = computed(() => props.visit)
const cancelling = ref(false)

const copyCode = async () => {
    try {
        await navigator.clipboard.writeText(v.value.checkin_code ?? '')
        toast.success('Kode kunjungan disalin.')
    } catch {
        toast.error('Gagal menyalin kode.')
    }
}

const cancel = () => {
    if (!confirm('Batalkan pengajuan kunjungan ini?')) return

    router.patch(`/client/visit-requests/${v.value.id}/cancel`, {}, {
        preserveScroll: true,
        onStart: () => (cancelling.value = true),
        onFinish: () => (cancelling.value = false),
    })
}
</script>

<template>
    <Head :title="v.request_number" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <!-- HEADER -->
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <Link href="/client/visit-requests" class="rounded-xl border p-2 text-muted-foreground hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl font-semibold">{{ v.request_number }}</h1>
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', stageClass(v.stage)]">{{ v.stage_label }}</span>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Diajukan {{ formatDateTime(v.created_at) }} oleh {{ v.requester?.name ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="flex gap-2">
                <Link v-if="canEdit" :href="`/client/visit-requests/${v.id}/edit`"
                    class="inline-flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-medium hover:bg-muted">
                    <Pencil class="h-4 w-4" /> Ubah
                </Link>
                <button v-if="canCancel" type="button" :disabled="cancelling" @click="cancel"
                    class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100 disabled:opacity-50 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                    <XCircle class="h-4 w-4" /> Batalkan
                </button>
            </div>
        </div>

        <!-- STATUS BANNERS -->
        <div v-if="v.status === 'pending'"
            class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300">
            <Clock3 class="mt-0.5 h-4 w-4 shrink-0" />
            Pengajuan sedang ditinjau tim LDX. Anda akan menerima email setelah disetujui atau ditolak.
        </div>

        <div v-if="v.status === 'rejected'"
            class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            <p class="font-semibold">Pengajuan ditolak</p>
            <p class="mt-1 whitespace-pre-line">{{ v.rejection_reason }}</p>
        </div>

        <!-- CHECK-IN CODE -->
        <div v-if="qr && v.checkin_code" class="rounded-2xl border border-primary/30 bg-card p-6 shadow-sm dark:bg-transparent">
            <div class="grid items-center gap-6 md:grid-cols-[auto_1fr]">
                <div class="mx-auto rounded-xl bg-white p-3" v-html="qr.svg" />
                <div>
                    <p class="flex items-center gap-2 text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                        <CheckCircle2 class="h-4 w-4" /> Disetujui — siap check-in
                    </p>
                    <p class="mt-3 text-xs uppercase tracking-wider text-muted-foreground">Kode Kunjungan</p>
                    <div class="mt-1 flex items-center gap-3">
                        <span class="font-mono text-3xl font-bold tracking-[0.3em]">{{ v.checkin_code }}</span>
                        <button type="button" @click="copyCode" class="rounded-lg border p-2 hover:bg-muted" title="Salin kode">
                            <Copy class="h-4 w-4" />
                        </button>
                    </div>
                    <ol class="mt-4 list-decimal space-y-1 pl-5 text-sm text-muted-foreground">
                        <li>Pada <strong class="text-foreground">{{ formatDate(v.visit_date) }}</strong>, buka Buku Tamu di lobi
                            atau scan QR ini.</li>
                        <li>Pilih <strong class="text-foreground">"Sudah Punya Kode Kunjungan"</strong> dan masukkan kode.</li>
                        <li>Setiap tamu memilih namanya, memasukkan 4 digit terakhir NIK, konfirmasi, lalu foto.</li>
                    </ol>
                    <p v-if="v.admin_notes" class="mt-3 rounded-lg bg-muted px-3 py-2 text-sm">
                        <strong>Catatan LDX:</strong> {{ v.admin_notes }}
                    </p>
                    <a :href="qr.url" target="_blank" rel="noopener" class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-primary hover:underline">
                        <QrCode class="h-3.5 w-3.5" /> {{ qr.url }}
                    </a>
                </div>
            </div>
        </div>

        <!-- DETAIL -->
        <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
            <h2 class="mb-4 font-semibold">Detail Kunjungan</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                <div>
                    <dt class="text-xs text-muted-foreground">Tanggal</dt>
                    <dd class="mt-1 font-medium">{{ formatDate(v.visit_date) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Jam</dt>
                    <dd class="mt-1 font-medium">{{ formatTime(v.start_time) }}–{{ formatTime(v.end_time) }} WIB</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Jenis</dt>
                    <dd class="mt-1 font-medium">{{ v.type_label }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Ditinjau</dt>
                    <dd class="mt-1 font-medium">{{ v.reviewer ? `${v.reviewer.name}, ${formatDateTime(v.reviewed_at)}` : '-' }}</dd>
                </div>
                <div class="col-span-2 md:col-span-4">
                    <dt class="text-xs text-muted-foreground">Keperluan</dt>
                    <dd class="mt-1">{{ v.purpose }}</dd>
                </div>
                <div v-if="v.racks?.length" class="col-span-2 md:col-span-4">
                    <dt class="text-xs text-muted-foreground">Rack</dt>
                    <dd class="mt-1 flex flex-wrap gap-2">
                        <span v-for="rack in v.racks" :key="rack.id" class="rounded-lg bg-muted px-2.5 py-1 text-xs">{{ rackLocation(rack) }}</span>
                    </dd>
                </div>
                <div v-if="v.notes" class="col-span-2 md:col-span-4">
                    <dt class="text-xs text-muted-foreground">Catatan</dt>
                    <dd class="mt-1 whitespace-pre-line">{{ v.notes }}</dd>
                </div>
            </dl>
        </div>

        <!-- MEMBERS -->
        <div class="overflow-x-auto rounded-2xl border bg-card shadow-sm dark:bg-transparent">
            <div class="border-b px-5 py-4 font-semibold">Tamu ({{ v.members?.length ?? 0 }})</div>
            <table class="min-w-full text-sm">
                <thead class="bg-muted/50 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-5 py-3">Nama</th>
                        <th class="px-5 py-3">Perusahaan / Jabatan</th>
                        <th class="px-5 py-3">Kontak</th>
                        <th class="px-5 py-3">Check-in</th>
                        <th class="px-5 py-3">Check-out</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="member in v.members" :key="member.id">
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ member.name }}</div>
                            <div class="text-xs text-muted-foreground">NIK {{ member.nik }}</div>
                        </td>
                        <td class="px-5 py-3">{{ member.company_name }}<div class="text-xs text-muted-foreground">{{ member.position_in_company }}</div></td>
                        <td class="px-5 py-3">{{ member.phone }}<div class="text-xs text-muted-foreground">{{ member.email }}</div></td>
                        <td class="whitespace-nowrap px-5 py-3">
                            <span v-if="member.checked_in_at" class="text-emerald-600 dark:text-emerald-400">{{ formatDateTime(member.checked_in_at) }}</span>
                            <span v-else class="text-muted-foreground">-</span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-muted-foreground">{{ formatDateTime(member.checked_out_at) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
