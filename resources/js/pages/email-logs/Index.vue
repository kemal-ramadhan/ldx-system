<script setup lang="ts">
import { reactive, ref } from 'vue'
import { Head, Link, router, useForm } from '@inertiajs/vue3'
import { AlertTriangle, Eye, Info, MailCheck, MailX, Paperclip, Plus, Save, Search, Send, Settings2, X } from 'lucide-vue-next'
import {
    type EmailLog,
    STATUS_LABELS,
    addressOnly,
    formatDateTime,
    reference,
    relatedUrl,
    statusClass,
    typeClass,
} from '@/lib/emailLog'

/**
 * Admin: every e-mail sent by the application.
 */
const props = defineProps<{
    title: string
    logs: {
        data: EmailLog[]
        links: { url: string | null; label: string; active: boolean }[]
        from: number | null
        to: number | null
        total: number
    }
    summary: { sent_today: number; sent: number; failed: number; failed_unresolved: number; pending: number }
    types: Record<string, string>
    filters: Record<string, string | undefined>
    archiveBcc: string[]
    archiveBccMeta?: { updated_at?: string; updater?: { name: string } | null } | null
}>()

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Riwayat Email', href: '/admin/email-logs' }],
    },
})

const filters = reactive({
    status: props.filters.status ?? '',
    type: props.filters.type ?? 'all',
    date_from: props.filters.date_from ?? '',
    date_to: props.filters.date_to ?? '',
    search: props.filters.search ?? '',
    related: props.filters.related ?? '',
})

const apply = () => {
    const query = Object.fromEntries(Object.entries(filters).filter(([k, v]) => v && !(k === 'type' && v === 'all')))
    router.get('/admin/email-logs', query, { preserveState: true, preserveScroll: true, replace: true })
}

const setStatus = (status: string) => {
    filters.status = filters.status === status ? '' : status
    apply()
}

const clearRelated = () => {
    filters.related = ''
    apply()
}

const cards = [
    { key: 'sent_today', status: 'sent', label: 'Terkirim Hari Ini', icon: Send, tone: 'text-blue-600 dark:text-blue-400' },
    { key: 'sent', status: 'sent', label: 'Total Terkirim', icon: MailCheck, tone: 'text-emerald-600 dark:text-emerald-400' },
    { key: 'failed_unresolved', status: 'failed_unresolved', label: 'Gagal (belum tertangani)', icon: MailX, tone: 'text-red-600 dark:text-red-400' },
    { key: 'pending', status: 'unconfirmed', label: 'Diproses / Tidak Terkonfirmasi', icon: AlertTriangle, tone: 'text-amber-600 dark:text-amber-400' },
] as const

const recipients = (log: EmailLog) => {
    const list = (log.to ?? []).map(addressOnly)
    return list.length > 1 ? `${list[0]} +${list.length - 1}` : (list[0] ?? '-')
}

/*
|--------------------------------------------------------------------------
| PENGATURAN BCC ARSIP (saved in the database, reused for every e-mail)
|--------------------------------------------------------------------------
*/
const showSettings = ref(false)
const newBcc = ref('')
const bccError = ref('')
const settingsForm = useForm({ archive_bcc: [...props.archiveBcc] as string[] })

const addBcc = () => {
    const email = newBcc.value.trim().toLowerCase()
    bccError.value = ''

    if (!email) return
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        bccError.value = 'Format email tidak valid.'
        return
    }
    if (settingsForm.archive_bcc.includes(email)) {
        bccError.value = 'Email sudah ada di daftar.'
        return
    }
    if (settingsForm.archive_bcc.length >= 10) {
        bccError.value = 'Maksimal 10 alamat.'
        return
    }

    settingsForm.archive_bcc.push(email)
    newBcc.value = ''
}

const removeBcc = (email: string) => {
    settingsForm.archive_bcc = settingsForm.archive_bcc.filter((e) => e !== email)
}

const saveSettings = () => {
    // an address typed but not added yet is included too
    if (newBcc.value.trim()) addBcc()
    if (bccError.value) return

    settingsForm.put('/admin/email-settings', {
        preserveScroll: true,
        onSuccess: () => (showSettings.value = false),
    })
}

const cancelSettings = () => {
    settingsForm.archive_bcc = [...props.archiveBcc]
    settingsForm.clearErrors()
    newBcc.value = ''
    bccError.value = ''
    showSettings.value = false
}

const settingsErrors = () =>
    Object.entries(settingsForm.errors as Record<string, string>)
        .filter(([key]) => key.startsWith('archive_bcc'))
        .map(([, message]) => message)

const inputClass = 'rounded-xl border border-input bg-background px-3 py-2.5 text-sm'
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <div>
            <h1 class="text-xl font-semibold">Riwayat Email</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Semua email yang dikirim sistem (invoice, reminder, pembayaran, pengajuan kunjungan) beserta statusnya.
            </p>
        </div>

        <!-- PENGATURAN BCC ARSIP -->
        <div class="rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="flex min-w-0 flex-1 items-start gap-3">
                    <Info class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground" />
                    <div class="min-w-0 text-sm">
                        <p class="font-semibold">BCC Arsip</p>
                        <p class="mt-0.5 text-muted-foreground">
                            Email sistem dikirim lewat SMTP sehingga tidak tersimpan di folder <em>Sent</em> mailbox hosting.
                            Salinan setiap email dikirim (BCC) ke alamat berikut:
                        </p>
                        <div v-if="!showSettings" class="mt-2 flex flex-wrap gap-1.5">
                            <span v-for="email in archiveBcc" :key="email"
                                class="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">{{ email }}</span>
                            <span v-if="!archiveBcc.length" class="text-xs text-amber-600 dark:text-amber-400">
                                Belum diatur — salinan email tidak dikirim ke mana pun.
                            </span>
                        </div>
                        <p v-if="!showSettings && archiveBccMeta?.updated_at" class="mt-1 text-xs text-muted-foreground">
                            Diubah {{ formatDateTime(archiveBccMeta.updated_at) }}{{ archiveBccMeta.updater ? ` oleh ${archiveBccMeta.updater.name}` : '' }}
                        </p>
                    </div>
                </div>
                <button v-if="!showSettings" type="button" @click="showSettings = true"
                    class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium hover:bg-muted">
                    <Settings2 class="h-4 w-4" /> Atur BCC
                </button>
            </div>

            <!-- form -->
            <div v-if="showSettings" class="mt-4 space-y-3 border-t pt-4">
                <div class="flex flex-wrap gap-2">
                    <span v-for="email in settingsForm.archive_bcc" :key="email"
                        class="inline-flex items-center gap-1.5 rounded-full bg-primary/10 py-1 pl-3 pr-1.5 text-sm text-primary">
                        {{ email }}
                        <button type="button" @click="removeBcc(email)" class="rounded-full p-0.5 hover:bg-primary/20" :title="`Hapus ${email}`">
                            <X class="h-3.5 w-3.5" />
                        </button>
                    </span>
                    <span v-if="!settingsForm.archive_bcc.length" class="text-sm text-muted-foreground">Belum ada alamat.</span>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    <input v-model="newBcc" type="email" placeholder="contoh: arsip@ldxdc.id" @keydown.enter.prevent="addBcc"
                        :class="[inputClass, 'flex-1']" />
                    <button type="button" @click="addBcc"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border px-4 py-2.5 text-sm font-medium hover:bg-muted">
                        <Plus class="h-4 w-4" /> Tambah
                    </button>
                </div>
                <p class="text-xs text-muted-foreground">
                    Tekan Enter atau Tambah untuk memasukkan beberapa email (maks. 10). Kosongkan daftar untuk menonaktifkan BCC.
                    Berlaku untuk semua email berikutnya.
                </p>
                <p v-if="bccError" class="text-xs text-red-500">{{ bccError }}</p>
                <p v-for="message in settingsErrors()" :key="message" class="text-xs text-red-500">{{ message }}</p>

                <div class="flex justify-end gap-2">
                    <button type="button" @click="cancelSettings" class="rounded-xl border px-4 py-2 text-sm font-medium hover:bg-muted">Batal</button>
                    <button type="button" :disabled="settingsForm.processing" @click="saveSettings"
                        class="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                        <Save class="h-4 w-4" /> {{ settingsForm.processing ? 'Menyimpan...' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- SUMMARY -->
        <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
            <button v-for="card in cards" :key="card.key" type="button" @click="setStatus(card.status)" :class="[
                'rounded-2xl border bg-card p-4 text-left shadow-sm transition hover:bg-muted/50 dark:bg-transparent',
                filters.status === card.status ? 'ring-2 ring-primary' : '',
            ]">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-muted-foreground">{{ card.label }}</span>
                    <component :is="card.icon" :class="['h-4 w-4', card.tone]" />
                </div>
                <p :class="['mt-2 text-2xl font-bold', card.tone]">{{ summary[card.key] }}</p>
            </button>
        </div>

        <!-- FILTERS -->
        <form class="grid grid-cols-1 gap-3 rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent md:grid-cols-6" @submit.prevent="apply">
            <div class="relative md:col-span-2">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="filters.search" type="text" placeholder="Penerima / subjek / no. invoice" :class="[inputClass, 'w-full pl-9']" />
            </div>
            <select v-model="filters.status" :class="inputClass">
                <option value="">Semua status</option>
                <option v-for="(label, key) in STATUS_LABELS" :key="key" :value="key">{{ label }}</option>
                <option value="failed_unresolved">Gagal (belum tertangani)</option>
            </select>
            <select v-model="filters.type" :class="inputClass">
                <option value="all">Semua jenis</option>
                <option v-for="(label, key) in types" :key="key" :value="key">{{ label }}</option>
            </select>
            <div class="flex items-center gap-2 md:col-span-2">
                <input v-model="filters.date_from" type="date" :class="[inputClass, 'min-w-0 flex-1']" />
                <span class="text-xs text-muted-foreground">s/d</span>
                <input v-model="filters.date_to" type="date" :class="[inputClass, 'min-w-0 flex-1']" />
                <button type="submit" class="rounded-xl bg-primary px-4 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90">Filter</button>
            </div>
        </form>

        <div v-if="filters.related" class="flex items-center gap-2 text-sm">
            <span class="rounded-full bg-muted px-3 py-1">Filter: {{ filters.related.replace(':', ' #') }}</span>
            <button type="button" class="rounded-full p-1 text-muted-foreground hover:bg-muted" @click="clearRelated"><X class="h-3.5 w-3.5" /></button>
        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto rounded-2xl border bg-card shadow-sm dark:bg-transparent">
            <table class="min-w-full text-sm">
                <thead class="border-b bg-muted/50 text-left text-xs uppercase tracking-wide text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3">Waktu</th>
                        <th class="px-4 py-3">Jenis</th>
                        <th class="px-4 py-3">Subjek</th>
                        <th class="px-4 py-3">Penerima</th>
                        <th class="px-4 py-3">Terkait</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Oleh</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <tr v-for="log in logs.data" :key="log.id" class="cursor-pointer hover:bg-muted/50"
                        @click="router.visit(`/admin/email-logs/${log.id}`)">
                        <td class="whitespace-nowrap px-4 py-3">{{ formatDateTime(log.sent_at ?? log.created_at) }}</td>
                        <td class="px-4 py-3">
                            <span :class="['whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold', typeClass(log.type)]">{{ log.type_label }}</span>
                        </td>
                        <td class="max-w-sm px-4 py-3">
                            <div class="flex items-center gap-1.5">
                                <span class="truncate font-medium">{{ log.subject ?? '(tanpa subjek)' }}</span>
                                <Paperclip v-if="log.attachments?.length" class="h-3.5 w-3.5 shrink-0 text-muted-foreground" />
                            </div>
                            <div v-if="log.resent_from_id" class="text-xs text-muted-foreground">Kirim ulang</div>
                            <div v-if="log.display_status === 'failed'" class="truncate text-xs text-red-600 dark:text-red-400">{{ log.error_message }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div>{{ recipients(log) }}</div>
                            <div class="text-xs text-muted-foreground">{{ log.client?.company_name }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3" @click.stop>
                            <Link v-if="relatedUrl(log)" :href="relatedUrl(log)!" class="text-primary hover:underline">{{ reference(log) }}</Link>
                            <span v-else class="text-muted-foreground">-</span>
                        </td>
                        <td class="px-4 py-3">
                            <span :class="['whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-semibold', statusClass(log.display_status)]">
                                {{ STATUS_LABELS[log.display_status] }}
                            </span>
                            <div v-if="log.resends_count" class="mt-1 text-xs text-muted-foreground">{{ log.resends_count }}x dikirim ulang</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-muted-foreground">{{ log.triggered_by?.name ?? 'Sistem' }}</td>
                        <td class="px-4 py-3 text-right" @click.stop>
                            <Link :href="`/admin/email-logs/${log.id}`"
                                class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium hover:bg-muted">
                                <Eye class="h-3.5 w-3.5" /> Detail
                            </Link>
                        </td>
                    </tr>
                    <tr v-if="!logs.data.length">
                        <td colspan="8" class="px-4 py-14 text-center text-muted-foreground">Belum ada email yang tercatat.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="logs.links.length > 3" class="flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-muted-foreground">{{ logs.from ?? 0 }}–{{ logs.to ?? 0 }} dari {{ logs.total }}</p>
            <div class="flex flex-wrap gap-1">
                <template v-for="link in logs.links" :key="link.label">
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
