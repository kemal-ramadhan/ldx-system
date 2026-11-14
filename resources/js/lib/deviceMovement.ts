/**
 * Keluar-masuk device: shared labels & formatting (admin only).
 */

export type MovementType = 'check_in' | 'check_out' | 'temp_out' | 'return_in' | 'relocate'
export type MovementAction = 'temp_out' | 'return_in' | 'relocate' | 'check_out'
export type Placement = 'in_rack' | 'out_temporary' | 'removed'

export interface Movement {
    id: number
    movement_number: string
    rack_divice_id: number
    type: MovementType
    type_label?: string
    occurred_at: string
    device_name: string
    device_code?: string | null
    serial_number?: string | null
    model?: string | null
    from_rack_id?: number | null
    from_start_unit?: number | null
    from_end_unit?: number | null
    to_rack_id?: number | null
    to_start_unit?: number | null
    to_end_unit?: number | null
    units_reserved?: boolean
    carrier_name?: string | null
    carrier_company?: string | null
    carrier_id_number?: string | null
    carrier_phone?: string | null
    reason?: string | null
    notes?: string | null
    attachments_count?: number
    attachments?: Attachment[]
    client?: { id: number; company_name: string } | null
    from_rack?: { id: number; code?: string; name?: string } | null
    to_rack?: { id: number; code?: string; name?: string } | null
    recorder?: { id: number; name: string } | null
}

export interface Attachment {
    id: number
    type: 'photo' | 'bast' | 'other'
    file_name: string
    file_type?: string
    file_size?: number
    url: string
    is_image: boolean
}

export const TYPE_LABELS: Record<MovementType, string> = {
    check_in: 'Masuk',
    check_out: 'Keluar Permanen',
    temp_out: 'Keluar Sementara',
    return_in: 'Kembali Masuk',
    relocate: 'Pindah',
}

export const typeClass = (type: string) =>
    ({
        check_in: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
        return_in: 'bg-teal-100 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300',
        temp_out: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
        relocate: 'bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300',
        check_out: 'bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-300',
    })[type] ?? 'bg-muted text-muted-foreground'

export const PLACEMENT_LABELS: Record<Placement, string> = {
    in_rack: 'Di Rack',
    out_temporary: 'Keluar Sementara',
    removed: 'Sudah Keluar',
}

export const placementClass = (placement?: string) =>
    ({
        in_rack: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300',
        out_temporary: 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300',
        removed: 'bg-muted text-muted-foreground',
    })[placement ?? 'in_rack'] ?? 'bg-muted text-muted-foreground'

export const rackLabel = (rack?: { code?: string; name?: string } | null) => rack?.code ?? rack?.name ?? '-'

export const unitRange = (start?: number | null, end?: number | null) =>
    start ? (end && end !== start ? `U${start}–U${end}` : `U${start}`) : ''

/** "R-01 · U6–U15" */
export const position = (rack?: { code?: string; name?: string } | null, start?: number | null, end?: number | null) => {
    if (!rack) return '-'
    const units = unitRange(start, end)
    return units ? `${rackLabel(rack)} · ${units}` : rackLabel(rack)
}

/** Server sends "YYYY-MM-DD HH:mm" (Asia/Jakarta). */
export const formatDateTime = (value?: string | null) => {
    if (!value) return '-'
    const [date, time] = value.replace('T', ' ').split(' ')
    const [y, m, d] = date.split('-').map(Number)
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des']
    return `${String(d).padStart(2, '0')} ${months[m - 1]} ${y}${time ? `, ${time.slice(0, 5)} WIB` : ''}`
}

export const nowLocal = () => {
    const d = new Date()
    const pad = (n: number) => String(n).padStart(2, '0')
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`
}

export const fileSize = (bytes?: number) =>
    !bytes ? '' : bytes > 1024 * 1024 ? `${(bytes / 1024 / 1024).toFixed(1)} MB` : `${Math.ceil(bytes / 1024)} KB`
