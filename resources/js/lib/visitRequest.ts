/**
 * Pengajuan kunjungan: shared labels & formatting (client + admin pages).
 */

export type VisitStage = 'pending' | 'approved' | 'in_progress' | 'completed' | 'expired' | 'rejected' | 'cancelled'

export interface VisitMember {
    id?: number
    name: string
    nik: string
    phone: string
    email: string
    address: string
    company_name: string
    position_in_company: string
    visitor_id?: number | null
    checked_in_at?: string | null
    checked_out_at?: string | null
    checked_in_by?: { id: number; name: string } | null
}

export interface VisitRack {
    id: number
    code?: string
    name?: string
    room?: { id: number; name?: string; location_data_center?: { id: number; name?: string } | null } | null
}

export interface Visit {
    id: number
    request_number: string
    visit_date: string
    start_time: string
    end_time: string
    type_of_visit: string
    type_label?: string
    purpose: string
    notes?: string | null
    status: 'pending' | 'approved' | 'rejected' | 'cancelled'
    stage: VisitStage
    stage_label: string
    checkin_code?: string | null
    rejection_reason?: string | null
    admin_notes?: string | null
    reviewed_at?: string | null
    cancelled_at?: string | null
    created_at?: string
    members_count?: number
    members?: VisitMember[]
    racks?: VisitRack[]
    client?: { id: number; company_name: string; company_code?: string; company_email?: string; company_phone?: string } | null
    requester?: { id: number; name: string; email?: string; phone?: string; client_pic?: { position?: string } | null } | null
    reviewer?: { id: number; name: string } | null
}

export const stageClass = (stage?: string) =>
    ({
        pending: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
        approved: 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
        in_progress: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300',
        completed: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
        expired: 'bg-muted text-muted-foreground',
        rejected: 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300',
        cancelled: 'bg-muted text-muted-foreground',
    })[stage ?? ''] ?? 'bg-muted text-muted-foreground'

const MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
const DAYS = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']

/** "2026-11-20" -> "Jum, 20 Nov 2026" (timezone-safe: no Date parsing shift) */
export const formatDate = (value?: string | null, withDay = true) => {
    if (!value) return '-'
    const [y, m, d] = value.slice(0, 10).split('-').map(Number)
    const day = DAYS[new Date(y, m - 1, d).getDay()]
    return `${withDay ? `${day}, ` : ''}${String(d).padStart(2, '0')} ${MONTHS[m - 1]} ${y}`
}

/** "09:00:00" -> "09:00" */
export const formatTime = (value?: string | null) => (value ? value.slice(0, 5) : '-')

/** "2026-11-20 09:15" -> "20 Nov 2026, 09:15" */
export const formatDateTime = (value?: string | null) =>
    value ? `${formatDate(value, false)}, ${value.replace('T', ' ').slice(11, 16)}` : '-'

export const rackLocation = (rack: VisitRack) =>
    [rack.room?.location_data_center?.name, rack.room?.name, rack.code ?? rack.name].filter(Boolean).join(' · ')

export const todayLocal = () => {
    const d = new Date()
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}
