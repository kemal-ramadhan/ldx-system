/**
 * Riwayat Email: shared labels & formatting.
 */

export type DisplayStatus = 'sent' | 'failed' | 'sending' | 'unconfirmed'

export interface EmailLog {
    id: number
    mailable?: string | null
    type: string
    type_label: string
    context?: Record<string, string> | null
    related_type?: string | null
    related_id?: number | null
    subject?: string | null
    from?: string | null
    to?: string[] | null
    cc?: string[] | null
    bcc?: string[] | null
    attachments?: { name: string; type: string; size: number }[] | null
    status: 'sending' | 'sent' | 'failed'
    display_status: DisplayStatus
    error_message?: string | null
    message_id?: string | null
    sent_at?: string | null
    failed_at?: string | null
    created_at: string
    source?: string
    resent_from_id?: number | null
    resends_count?: number
    client?: { id: number; company_name: string } | null
    triggered_by?: { id: number; name: string } | null
}

export const STATUS_LABELS: Record<DisplayStatus, string> = {
    sent: 'Terkirim',
    failed: 'Gagal',
    sending: 'Diproses',
    unconfirmed: 'Tidak Terkonfirmasi',
}

export const statusClass = (status?: string) =>
    ({
        sent: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
        failed: 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300',
        sending: 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
        unconfirmed: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
    })[status ?? ''] ?? 'bg-muted text-muted-foreground'

export const typeClass = (type?: string) =>
    ({
        invoice: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300',
        invoice_reminder: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
        payment_received: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
        visit_request: 'bg-sky-100 text-sky-700 dark:bg-sky-950/40 dark:text-sky-300',
    })[type ?? ''] ?? 'bg-muted text-muted-foreground'

export const REMINDER_LABELS: Record<string, string> = {
    upcoming: 'H-7',
    due_today: 'Jatuh tempo',
    overdue: 'Overdue',
}

/** short reference shown in lists: invoice / visit number (+ reminder kind) */
export const reference = (log: EmailLog) => {
    const ctx = log.context ?? {}
    const number = ctx.invoice_number ?? ctx.request_number
    const reminder = ctx.reminder_type ? ` · ${REMINDER_LABELS[ctx.reminder_type] ?? ctx.reminder_type}` : ''
    return number ? `${number}${reminder}` : '-'
}

export const relatedUrl = (log: EmailLog) =>
    log.related_type?.endsWith('Invoice')
        ? `/admin/invoices/${log.related_id}`
        : log.related_type?.endsWith('VisitRequest')
            ? `/admin/visit-requests/${log.related_id}`
            : null

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']

/** "2026-11-14 16:47:03" (Asia/Jakarta) -> "14 Nov 2026, 16:47" */
export const formatDateTime = (value?: string | null, seconds = false) => {
    if (!value) return '-'
    const [date, time = ''] = value.replace('T', ' ').split(' ')
    const [y, m, d] = date.split('-').map(Number)
    return `${String(d).padStart(2, '0')} ${MONTHS[m - 1]} ${y}${time ? `, ${time.slice(0, seconds ? 8 : 5)}` : ''}`
}

export const fileSize = (bytes?: number) =>
    !bytes ? '' : bytes > 1024 * 1024 ? `${(bytes / 1024 / 1024).toFixed(1)} MB` : `${Math.ceil(bytes / 1024)} KB`

/** "Name <a@b.c>" -> "a@b.c" for compact lists */
export const addressOnly = (value: string) => value.match(/<([^>]+)>/)?.[1] ?? value
