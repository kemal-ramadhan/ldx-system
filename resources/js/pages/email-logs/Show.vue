<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import { ArrowLeft, ExternalLink, FileText, Mail, RotateCw } from 'lucide-vue-next'
import {
    type EmailLog,
    STATUS_LABELS,
    fileSize,
    formatDateTime,
    reference,
    statusClass,
    typeClass,
} from '@/lib/emailLog'

/**
 * Admin: one e-mail, shown like in an inbox, with "Kirim Ulang".
 */
const props = defineProps<{
    title: string
    log: EmailLog & {
        resent_from?: { id: number; status: string; created_at: string; sent_at?: string | null } | null
        resends?: { id: number; status: string; created_at: string; sent_at?: string | null; error_message?: string | null; triggered_by?: { name: string } | null }[]
    }
    relatedUrl: string | null
    canResend: boolean
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Riwayat Email', href: '/admin/email-logs' },
            { title: 'Detail', href: '#' },
        ],
    },
})

const log = computed(() => props.log)
const resending = ref(false)

const resend = () => {
    const warn = log.value.display_status === 'sent'
        ? 'Email ini sudah terkirim. Kirim ulang ke penerima dengan data terbaru?'
        : 'Kirim ulang email ini dengan data terbaru?'

    if (!confirm(warn)) return

    router.post(`/admin/email-logs/${log.value.id}/resend`, {}, {
        preserveScroll: true,
        onStart: () => (resending.value = true),
        onFinish: () => (resending.value = false),
    })
}

const page = usePage()
const resendError = computed(() => (page.props.errors as Record<string, string> | undefined)?.resend)

const headerRows = computed(() => [
    { label: 'Dari', value: log.value.from ?? '-' },
    { label: 'Kepada', value: (log.value.to ?? []).join(', ') || '-' },
    ...(log.value.cc?.length ? [{ label: 'CC', value: log.value.cc.join(', ') }] : []),
    ...(log.value.bcc?.length ? [{ label: 'BCC', value: log.value.bcc.join(', ') }] : []),
])
</script>

<template>
    <Head :title="log.subject ?? 'Email'" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <!-- TOP BAR -->
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="flex items-start gap-3">
                <Link href="/admin/email-logs" class="rounded-xl border p-2 text-muted-foreground hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div class="flex flex-wrap items-center gap-2">
                    <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', statusClass(log.display_status)]">{{ STATUS_LABELS[log.display_status] }}</span>
                    <span :class="['rounded-full px-2.5 py-1 text-xs font-semibold', typeClass(log.type)]">{{ log.type_label }}</span>
                    <Link v-if="relatedUrl" :href="relatedUrl" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:underline">
                        {{ reference(log) }} <ExternalLink class="h-3.5 w-3.5" />
                    </Link>
                </div>
            </div>

            <button v-if="canResend" type="button" :disabled="resending" @click="resend"
                class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                <RotateCw :class="['h-4 w-4', resending ? 'animate-spin' : '']" />
                {{ resending ? 'Mengirim...' : 'Kirim Ulang' }}
            </button>
        </div>

        <p v-if="resendError" class="rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-950/40 dark:text-red-300">{{ resendError }}</p>

        <!-- FAILURE -->
        <div v-if="log.display_status === 'failed'"
            class="rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
            <p class="font-semibold">Gagal dikirim {{ formatDateTime(log.failed_at, true) }}</p>
            <p class="mt-1 break-words font-mono text-xs">{{ log.error_message }}</p>
        </div>
        <div v-if="log.display_status === 'unconfirmed'"
            class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300">
            Pengiriman tidak terkonfirmasi (proses berhenti sebelum server email menjawab). Status sebenarnya tidak diketahui —
            kirim ulang jika penerima belum menerima.
        </div>

        <!-- INBOX-LIKE VIEW -->
        <div class="overflow-hidden rounded-2xl border bg-card shadow-sm dark:bg-transparent">
            <div class="border-b p-5">
                <h1 class="text-xl font-semibold">{{ log.subject ?? '(tanpa subjek)' }}</h1>

                <div class="mt-4 flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                        <Mail class="h-5 w-5 text-primary" />
                    </div>
                    <div class="min-w-0 flex-1 text-sm">
                        <dl class="space-y-0.5">
                            <div v-for="row in headerRows" :key="row.label" class="flex gap-2">
                                <dt class="w-14 shrink-0 text-muted-foreground">{{ row.label }}</dt>
                                <dd class="min-w-0 break-words">{{ row.value }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="shrink-0 text-right text-xs text-muted-foreground">
                        <div>{{ formatDateTime(log.sent_at ?? log.created_at, true) }}</div>
                        <div>oleh {{ log.triggered_by?.name ?? 'Sistem' }}{{ log.source === 'console' ? ' (terjadwal)' : '' }}</div>
                    </div>
                </div>
            </div>

            <!-- attachments -->
            <div v-if="log.attachments?.length" class="flex flex-wrap gap-2 border-b px-5 py-3">
                <span v-for="file in log.attachments" :key="file.name"
                    class="inline-flex items-center gap-2 rounded-lg border bg-muted/40 px-3 py-1.5 text-xs">
                    <FileText class="h-4 w-4 text-red-500" />
                    <span class="font-medium">{{ file.name }}</span>
                    <span class="text-muted-foreground">{{ fileSize(file.size) }}</span>
                </span>
            </div>

            <!-- body: sandboxed, no scripts -->
            <div class="bg-white">
                <iframe :src="`/admin/email-logs/${log.id}/preview`" sandbox="allow-popups allow-popups-to-escape-sandbox"
                    referrerpolicy="no-referrer" title="Isi email" class="block h-[75vh] w-full border-0" />
            </div>
        </div>

        <!-- TECHNICAL + RESEND CHAIN -->
        <div class="grid gap-5 lg:grid-cols-2">
            <div class="rounded-2xl border bg-card p-5 text-sm shadow-sm dark:bg-transparent">
                <h2 class="mb-3 font-semibold">Detail Teknis</h2>
                <dl class="space-y-2">
                    <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted-foreground">Dicatat</dt><dd>{{ formatDateTime(log.created_at, true) }}</dd></div>
                    <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted-foreground">Terkirim</dt><dd>{{ formatDateTime(log.sent_at, true) }}</dd></div>
                    <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted-foreground">Message-ID</dt><dd class="break-all font-mono text-xs">{{ log.message_id ?? '-' }}</dd></div>
                    <div class="flex gap-2"><dt class="w-28 shrink-0 text-muted-foreground">Template</dt><dd class="break-all font-mono text-xs">{{ log.mailable ?? '-' }}</dd></div>
                    <div v-if="log.client" class="flex gap-2"><dt class="w-28 shrink-0 text-muted-foreground">Client</dt><dd>{{ log.client.company_name }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border bg-card p-5 text-sm shadow-sm dark:bg-transparent">
                <h2 class="mb-3 font-semibold">Riwayat Kirim Ulang</h2>
                <p v-if="log.resent_from" class="mb-3">
                    Kiriman ulang dari
                    <Link :href="`/admin/email-logs/${log.resent_from.id}`" class="font-medium text-primary hover:underline">email #{{ log.resent_from.id }}</Link>
                    ({{ formatDateTime(log.resent_from.created_at) }})
                </p>
                <ul v-if="log.resends?.length" class="space-y-2">
                    <li v-for="item in log.resends" :key="item.id" class="flex items-center justify-between gap-3 rounded-lg border px-3 py-2">
                        <Link :href="`/admin/email-logs/${item.id}`" class="font-medium hover:underline">#{{ item.id }} · {{ formatDateTime(item.sent_at ?? item.created_at) }}</Link>
                        <span class="text-xs text-muted-foreground">{{ item.triggered_by?.name ?? 'Sistem' }}</span>
                        <span :class="['rounded-full px-2 py-0.5 text-xs font-semibold', statusClass(item.status)]">
                            {{ STATUS_LABELS[item.status as keyof typeof STATUS_LABELS] ?? item.status }}
                        </span>
                    </li>
                </ul>
                <p v-else-if="!log.resent_from" class="text-muted-foreground">Belum pernah dikirim ulang.</p>
            </div>
        </div>
    </div>
</template>
