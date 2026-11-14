<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { ArrowLeft, Building2, CheckCircle2, LogIn, LogOut, User, X, XCircle } from 'lucide-vue-next'
import { type Visit, formatDate, formatDateTime, formatTime, rackLocation, stageClass } from '@/lib/visitRequest'

/**
 * Admin: review a visit request (approve / reject) and front-desk check-in/out.
 */
const props = defineProps<{
    title: string
    visit: Visit
    canCheckIn: boolean
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pengajuan Kunjungan', href: '/admin/visit-requests' },
            { title: 'Detail', href: '#' },
        ],
    },
})

const v = computed(() => props.visit)

/* approve / reject */
const modal = ref<'approve' | 'reject' | null>(null)
const approveForm = useForm({ admin_notes: '' })
const rejectForm = useForm({ rejection_reason: '' })

const approve = () =>
    approveForm.patch(`/admin/visit-requests/${v.value.id}/approve`, {
        preserveScroll: true,
        onSuccess: () => (modal.value = null),
    })

const reject = () =>
    rejectForm.patch(`/admin/visit-requests/${v.value.id}/reject`, {
        preserveScroll: true,
        onSuccess: () => (modal.value = null),
    })

/* front-desk check-in / check-out */
const busyMember = ref<number | null>(null)

const memberAction = (memberId: number, action: 'check-in' | 'check-out', name: string) => {
    if (!confirm(`${action === 'check-in' ? 'Check-in' : 'Check-out'} ${name}?`)) return

    router.post(`/admin/visit-requests/${v.value.id}/members/${memberId}/${action}`, {}, {
        preserveScroll: true,
        onStart: () => (busyMember.value = memberId),
        onFinish: () => (busyMember.value = null),
    })
}

const statusError = computed(() => (approveForm.errors as Record<string, string>).status ?? (rejectForm.errors as Record<string, string>).status)
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <!-- HEADER -->
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <Link href="/admin/visit-requests" class="rounded-xl border p-2 text-muted-foreground hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl font-semibold">{{ v.request_number }}</h1>
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', stageClass(v.stage)]">{{ v.stage_label }}</span>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground">Diajukan {{ formatDateTime(v.created_at) }}</p>
                </div>
            </div>

            <div v-if="v.status === 'pending' || v.status === 'approved'" class="flex gap-2">
                <button v-if="v.status === 'pending'" type="button" @click="modal = 'approve'"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                    <CheckCircle2 class="h-4 w-4" /> Setujui
                </button>
                <button v-if="v.stage === 'pending' || v.stage === 'approved'" type="button" @click="modal = 'reject'"
                    class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                    <XCircle class="h-4 w-4" /> Tolak
                </button>
            </div>
        </div>

        <p v-if="statusError" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-950/40 dark:text-red-300">{{ statusError }}</p>

        <div v-if="v.status === 'rejected'"
            class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            <p class="font-semibold">Ditolak oleh {{ v.reviewer?.name ?? '-' }}, {{ formatDateTime(v.reviewed_at) }}</p>
            <p class="mt-1 whitespace-pre-line">{{ v.rejection_reason }}</p>
        </div>

        <div v-if="v.status === 'approved'" class="flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-primary/30 bg-card p-4 dark:bg-transparent">
            <div class="text-sm">
                <p class="text-xs uppercase tracking-wider text-muted-foreground">Kode Kunjungan</p>
                <p class="font-mono text-2xl font-bold tracking-[0.3em]">{{ v.checkin_code }}</p>
            </div>
            <p class="text-sm text-muted-foreground">
                Disetujui {{ v.reviewer?.name ?? '-' }}, {{ formatDateTime(v.reviewed_at) }}
                <span v-if="v.admin_notes" class="block">Catatan: {{ v.admin_notes }}</span>
            </p>
        </div>

        <div class="grid gap-5 lg:grid-cols-3">
            <!-- REQUESTER -->
            <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
                <h2 class="mb-4 flex items-center gap-2 font-semibold"><User class="h-4 w-4" /> Diajukan oleh</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-muted-foreground">Nama</dt>
                        <dd class="font-medium">{{ v.requester?.name ?? '-' }}</dd>
                    </div>
                    <div v-if="v.requester?.client_pic?.position">
                        <dt class="text-xs text-muted-foreground">Jabatan (PIC)</dt>
                        <dd class="font-medium">{{ v.requester.client_pic.position }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Email</dt>
                        <dd class="font-medium break-all">{{ v.requester?.email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Telepon</dt>
                        <dd class="font-medium">{{ v.requester?.phone ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- CLIENT -->
            <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
                <h2 class="mb-4 flex items-center gap-2 font-semibold"><Building2 class="h-4 w-4" /> Client</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-muted-foreground">Perusahaan</dt>
                        <dd class="font-medium">{{ v.client?.company_name }} <span class="text-muted-foreground">({{ v.client?.company_code }})</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Email</dt>
                        <dd class="font-medium break-all">{{ v.client?.company_email ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Telepon</dt>
                        <dd class="font-medium">{{ v.client?.company_phone ?? '-' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- VISIT -->
            <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
                <h2 class="mb-4 font-semibold">Kunjungan</h2>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs text-muted-foreground">Jadwal</dt>
                        <dd class="font-medium">{{ formatDate(v.visit_date) }}, {{ formatTime(v.start_time) }}–{{ formatTime(v.end_time) }} WIB</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Jenis / Keperluan</dt>
                        <dd class="font-medium">{{ v.type_label }} — {{ v.purpose }}</dd>
                    </div>
                    <div v-if="v.racks?.length">
                        <dt class="text-xs text-muted-foreground">Rack</dt>
                        <dd class="mt-1 flex flex-wrap gap-1.5">
                            <span v-for="rack in v.racks" :key="rack.id" class="rounded-lg bg-muted px-2 py-0.5 text-xs">{{ rackLocation(rack) }}</span>
                        </dd>
                    </div>
                    <div v-if="v.notes">
                        <dt class="text-xs text-muted-foreground">Catatan client</dt>
                        <dd class="whitespace-pre-line">{{ v.notes }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- MEMBERS -->
        <div class="overflow-x-auto rounded-2xl border bg-card shadow-sm dark:bg-transparent">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b px-5 py-4">
                <span class="font-semibold">Tamu ({{ v.members?.length ?? 0 }})</span>
                <span v-if="v.status === 'approved' && !canCheckIn" class="text-xs text-muted-foreground">
                    Check-in tersedia pada {{ formatDate(v.visit_date) }}
                </span>
            </div>
            <table class="min-w-full text-sm">
                <thead class="bg-muted/50 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-5 py-3">Nama / NIK</th>
                        <th class="px-5 py-3">Perusahaan / Jabatan</th>
                        <th class="px-5 py-3">Kontak</th>
                        <th class="px-5 py-3">Alamat</th>
                        <th class="px-5 py-3">Check-in</th>
                        <th class="px-5 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="member in v.members" :key="member.id">
                        <td class="px-5 py-3">
                            <div class="font-medium">{{ member.name }}</div>
                            <div class="font-mono text-xs text-muted-foreground">{{ member.nik }}</div>
                        </td>
                        <td class="px-5 py-3">{{ member.company_name }}<div class="text-xs text-muted-foreground">{{ member.position_in_company }}</div></td>
                        <td class="px-5 py-3">{{ member.phone }}<div class="text-xs text-muted-foreground">{{ member.email }}</div></td>
                        <td class="max-w-[220px] px-5 py-3 text-xs text-muted-foreground">{{ member.address }}</td>
                        <td class="whitespace-nowrap px-5 py-3 text-xs">
                            <template v-if="member.checked_in_at">
                                <div class="text-emerald-600 dark:text-emerald-400">Masuk {{ formatDateTime(member.checked_in_at) }}</div>
                                <div class="text-muted-foreground">{{ member.checked_in_by ? `oleh ${member.checked_in_by.name}` : 'mandiri (kode)' }}</div>
                                <div v-if="member.checked_out_at" class="text-muted-foreground">Keluar {{ formatDateTime(member.checked_out_at) }}</div>
                                <Link v-if="member.visitor_id" :href="`/admin/visitors/${member.visitor_id}`" class="text-primary hover:underline">Data visitor</Link>
                            </template>
                            <span v-else class="text-muted-foreground">-</span>
                        </td>
                        <td class="whitespace-nowrap px-5 py-3 text-right">
                            <button v-if="canCheckIn && !member.checked_in_at" type="button" :disabled="busyMember === member.id"
                                @click="memberAction(member.id!, 'check-in', member.name)"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-primary px-3 py-1.5 text-xs font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                                <LogIn class="h-3.5 w-3.5" /> Check-in
                            </button>
                            <button v-else-if="member.checked_in_at && !member.checked_out_at" type="button" :disabled="busyMember === member.id"
                                @click="memberAction(member.id!, 'check-out', member.name)"
                                class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium hover:bg-muted disabled:opacity-50">
                                <LogOut class="h-3.5 w-3.5" /> Check-out
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- APPROVE / REJECT MODAL -->
    <div v-if="modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 text-foreground dark:scheme-dark"
        @click.self="modal = null">
        <div class="w-full max-w-lg rounded-2xl border bg-card shadow-xl">
            <div class="flex items-start justify-between border-b px-6 py-5">
                <div>
                    <h3 class="text-lg font-semibold">{{ modal === 'approve' ? 'Setujui Pengajuan' : 'Tolak Pengajuan' }}</h3>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ modal === 'approve'
                            ? 'Kode kunjungan + QR akan dikirim ke email client.'
                            : 'Client akan menerima email berisi alasan penolakan.' }}
                    </p>
                </div>
                <button type="button" class="rounded-lg p-1 text-muted-foreground hover:bg-muted" @click="modal = null"><X class="h-4 w-4" /></button>
            </div>

            <div class="px-6 py-5">
                <template v-if="modal === 'approve'">
                    <label class="mb-2 block text-sm font-medium">Catatan untuk client (opsional)</label>
                    <textarea v-model="approveForm.admin_notes" rows="4" maxlength="2000" placeholder="Contoh: Harap membawa KTP asli, parkir di basement B1"
                        class="w-full rounded-xl border border-input bg-background px-4 py-3 text-sm" />
                </template>
                <template v-else>
                    <label class="mb-2 block text-sm font-medium">Alasan penolakan <span class="text-red-500">*</span></label>
                    <textarea v-model="rejectForm.rejection_reason" rows="4" maxlength="2000"
                        class="w-full rounded-xl border border-input bg-background px-4 py-3 text-sm" />
                    <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-xs text-red-500">{{ rejectForm.errors.rejection_reason }}</p>
                </template>
            </div>

            <div class="flex justify-end gap-2 border-t px-6 py-4">
                <button type="button" class="rounded-lg border px-4 py-2.5 text-sm font-medium hover:bg-muted" @click="modal = null">Batal</button>
                <button v-if="modal === 'approve'" type="button" :disabled="approveForm.processing" @click="approve"
                    class="rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                    {{ approveForm.processing ? 'Menyimpan...' : 'Setujui & Kirim Kode' }}
                </button>
                <button v-else type="button" :disabled="rejectForm.processing || !rejectForm.rejection_reason.trim()" @click="reject"
                    class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:opacity-50">
                    {{ rejectForm.processing ? 'Menyimpan...' : 'Tolak Pengajuan' }}
                </button>
            </div>
        </div>
    </div>
</template>
