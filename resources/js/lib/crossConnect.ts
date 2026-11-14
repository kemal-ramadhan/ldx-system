/**
 * Display helpers for cross connects in the client portal.
 * Labels mirror the admin pages (resources/js/pages/cross-connects).
 */

export interface CCPort {
    id: number
    port_name?: string
    port_number?: number | string | null
    port_type?: string
    connector_type?: string | null
    status?: string
    device?: {
        id: number
        client_id?: number
        code?: string
        divice_name?: string
        client?: { id: number; company_code?: string; company_name?: string } | null
        rack?: {
            id: number
            code?: string
            name?: string
            room?: { id: number; name?: string; location_data_center?: { id: number; name?: string } | null } | null
        } | null
    } | null
}

export interface ClientCrossConnect {
    id: number
    cross_connect_number: string
    status: string
    destination_type: 'internal' | 'external'
    external_location?: string | null
    external_client_name?: string | null
    external_rack_name?: string | null
    external_device_name?: string | null
    external_port_name?: string | null
    cable_type?: string
    connector_type?: string | null
    cable_length?: number | string | null
    cable_length_unit?: string
    installed_at?: string | null
    terminated_at?: string | null
    terminate_requested_at?: string | null
    terminate_request_reason?: string | null
    terminate_requester?: { id: number; name: string } | null
    description?: string | null
    notes?: string | null
    my_side?: 'source' | 'destination' | null
    source_port?: CCPort | null
    destination_port?: CCPort | null
    interconnection_request?: { id: number; request_number: string; requester_client_id?: number } | null
}

export const statusLabel = (status: string) =>
    ({ active: 'Active', planned: 'Planned', terminated: 'Terminated', cancelled: 'Cancelled' })[status] ?? status

export const statusClass = (status: string) =>
    ({
        active: 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950/40 dark:text-emerald-300',
        planned: 'bg-amber-50 text-amber-700 ring-amber-600/20 dark:bg-amber-950/40 dark:text-amber-300',
        cancelled: 'bg-red-50 text-red-700 ring-red-600/20 dark:bg-red-950/40 dark:text-red-300',
    })[status] ?? 'bg-muted text-muted-foreground ring-border'

export const cableLabel = (type?: string) =>
    ({ fiber_optic: 'Fiber Optic', copper: 'Copper', coaxial: 'Coaxial' })[type ?? ''] ?? 'Other'

export const isExternal = (cc: ClientCrossConnect) => cc.destination_type === 'external'

export const formatDate = (date?: string | null, withTime = false) => {
    if (!date) return '-'

    return new Date(date).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        ...(withTime ? { hour: '2-digit', minute: '2-digit' } : {}),
    })
}

export const portLabel = (port?: CCPort | null) =>
    port ? (port.port_number ? `${port.port_name} — #${port.port_number}` : (port.port_name ?? '-')) : '-'

export const locationOf = (port?: CCPort | null) =>
    [
        port?.device?.rack?.room?.location_data_center?.name,
        port?.device?.rack?.room?.name,
        port?.device?.rack?.code ?? port?.device?.rack?.name,
    ]
        .filter(Boolean)
        .join(' · ') || '-'

/** One end of the connection, as a flat view model. */
export const endpoint = (cc: ClientCrossConnect, side: 'source' | 'destination') => {
    if (side === 'destination' && isExternal(cc)) {
        return {
            client: cc.external_client_name ?? 'MMR DC APJII',
            badge: cc.external_location ? `MMR · ${cc.external_location}` : 'MMR',
            location: cc.external_location ? `MMR DC APJII ${cc.external_location}` : 'MMR DC APJII',
            rack: cc.external_rack_name ?? '-',
            device: cc.external_device_name ?? '-',
            port: cc.external_port_name ?? '-',
        }
    }

    const port = side === 'source' ? cc.source_port : cc.destination_port

    return {
        client: port?.device?.client?.company_name ?? '-',
        badge: null as string | null,
        location: locationOf(port),
        rack: port?.device?.rack?.code ?? port?.device?.rack?.name ?? '-',
        device: port?.device?.divice_name ?? port?.device?.code ?? '-',
        port: portLabel(port),
    }
}
