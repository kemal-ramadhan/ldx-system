<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { 
    Chart as ChartJS, 
    CategoryScale, 
    LinearScale, 
    PointElement, 
    LineElement, 
    Title, 
    Tooltip, 
    Legend,
    BarElement,
    ArcElement
} from 'chart.js'
import { Bar, Doughnut, Line } from 'vue-chartjs'
import {
    Thermometer,
    Droplets,
    Zap,
    Server,
    Activity,
    CheckCircle2,
    AlertTriangle,
    ArrowUpRight,
    RefreshCw
} from 'lucide-vue-next'

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    BarElement,
    ArcElement
)

interface TelemetryData {
    rack?: string
    slave?: number
    status?: string
    terakhir?: string
    pln?: {
        ada?: boolean
        voltage?: number
        current?: number
        power?: number
        volt?: number
        arus?: number
        daya?: number
        status?: string
        status_arus?: string
    }
    ups?: {
        ada?: boolean
        voltage?: number
        current?: number
        power?: number
        volt?: number
        arus?: number
        daya?: number
        status?: string
        status_arus?: string
    }
    suhu?: {
        ada?: boolean
        temp?: number
        hum?: number
        status?: string
        status_hum?: string
    }
}

interface ClientRackItem {
    id: number
    name: string
    code: string
    api_suhu?: string | null
    rented_units: number
    rental_start_date?: string | null
    rental_end_date?: string | null
    status: string
    room_name?: string | null
    location_name?: string | null
    telemetry?: TelemetryData | null
}

const props = defineProps<{
    title: string
    user: any
    stats: {
        racks: {
            total: number
            active: number
            total_units: number
        }
        devices: {
            total: number
            by_type: Array<{ divice_type: string, total: number }>
        }
        services: {
            total: number
            active: number
            by_status: Array<{ status: string, total: number }>
        }
        invoices: {
            total: number
            paid: number
            overdue: number
            waiting: number
            total_paid: number
            total_outstanding: number
            upcoming_due: number
        }
        tickets: {
            total: number
            open: number
            by_status: Array<{ status: string, total: number }>
        }
        trends: {
            monthly_racks: Array<{ month: number, total: number }>
        }
        recent: {
            tickets: Array<any>
            invoices: Array<any>
        }
    }
    clientRacks?: Array<ClientRackItem>
    temperatureHistory?: Array<{
        log_date: string
        avg_temp: number
        max_temp: number
        min_temp: number
        avg_hum: number
    }>
    telemetryLogs?: Array<any>
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '#' }
        ],
    },
})

// Selected rack tab for multi-rack clients
const selectedRackIndex = ref(0)
const activeChartTab = ref<'timeline' | 'history_7days' | 'multi_rack'>('timeline')

const clientRacksList = computed(() => props.clientRacks || [])
const activeRack = computed(() => {
    if (!clientRacksList.value.length) return null
    return clientRacksList.value[selectedRackIndex.value] || clientRacksList.value[0]
})

// Format date helper for labels
const formatPointDate = (dateStr: string) => {
    try {
        const d = new Date(dateStr)
        const day = d.getDate().toString().padStart(2, '0')
        const month = (d.getMonth() + 1).toString().padStart(2, '0')
        const hour = d.getHours().toString().padStart(2, '0')
        const min = d.getMinutes().toString().padStart(2, '0')
        return `${day}/${month} ${hour}:${min}`
    } catch {
        return dateStr
    }
}

// Timeline Chart Data for the active selected rack (combines telemetryLogs & current realtime snapshot)
const activeRackTimelineChartData = computed(() => {
    const rack = activeRack.value
    if (!rack) {
        return { labels: ['Belum Ada Data'], datasets: [] }
    }

    // Filter logs for this specific rack or its api_suhu
    const logs = (props.telemetryLogs || []).filter((l: any) => {
        return (rack.id && l.rack_id === rack.id) || (rack.api_suhu && l.api_rack_name === rack.api_suhu)
    })

    const points: Array<{ time: string, temp: number | null, hum: number | null }> = []

    logs.forEach((l: any) => {
        points.push({
            time: formatPointDate(l.recorded_at),
            temp: l.temp !== null && l.temp !== undefined ? Number(l.temp) : null,
            hum: l.hum !== null && l.hum !== undefined ? Number(l.hum) : null,
        })
    })

    // If current realtime telemetry exists, append as latest point if not already present
    if (rack.telemetry?.suhu?.ada && typeof rack.telemetry.suhu.temp === 'number') {
        const lastLabel = rack.telemetry.terakhir
            ? formatPointDate(rack.telemetry.terakhir)
            : 'Saat Ini'
        if (!points.length || points[points.length - 1].time !== lastLabel) {
            points.push({
                time: lastLabel,
                temp: rack.telemetry.suhu.temp,
                hum: rack.telemetry.suhu.hum ?? null,
            })
        }
    }

    if (points.length === 0) {
        return {
            labels: ['Belum Ada Log'],
            datasets: [
                {
                    label: 'Suhu (°C)',
                    data: [rack.telemetry?.suhu?.temp ?? 0],
                    borderColor: '#f97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                }
            ]
        }
    }

    return {
        labels: points.map(p => p.time),
        datasets: [
            {
                label: `Suhu ${rack.code || rack.name} (°C)`,
                data: points.map(p => p.temp),
                borderColor: '#f97316',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                tension: 0.35,
                fill: true,
                pointRadius: points.length > 20 ? 2 : 4,
                pointBackgroundColor: '#ea580c',
                yAxisID: 'yTemp',
            },
            {
                label: `Kelembapan (%)`,
                data: points.map(p => p.hum),
                borderColor: '#0284c7',
                backgroundColor: 'rgba(2, 132, 199, 0.08)',
                tension: 0.35,
                fill: false,
                pointRadius: points.length > 20 ? 2 : 4,
                pointBackgroundColor: '#0369a1',
                yAxisID: 'yHum',
            }
        ]
    }
})

// 7-day temperature history line chart for client's rented racks (Daily Aggregation)
const history7DaysChartData = computed(() => {
    const history = props.temperatureHistory || []
    const labels = history.map(h => {
        const parts = h.log_date.split('-')
        return `${parts[2]}/${parts[1]}`
    })

    return {
        labels: labels.length ? labels : ['Hari Ini'],
        datasets: [
            {
                label: 'Suhu Rata-rata (°C)',
                data: history.map(h => Number(h.avg_temp)),
                borderColor: '#ea580c',
                backgroundColor: 'rgba(234, 88, 12, 0.1)',
                tension: 0.35,
                fill: true,
                pointRadius: 4,
                pointBackgroundColor: '#c2410c',
                yAxisID: 'yTemp',
            },
            {
                label: 'Suhu Maksimum (°C)',
                data: history.map(h => Number(h.max_temp)),
                borderColor: '#ef4444',
                backgroundColor: 'transparent',
                borderDash: [5, 5],
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: '#b91c1c',
                yAxisID: 'yTemp',
            },
            {
                label: 'Kelembapan Rata-rata (%)',
                data: history.map(h => Number(h.avg_hum)),
                borderColor: '#0284c7',
                backgroundColor: 'rgba(2, 132, 199, 0.08)',
                tension: 0.35,
                fill: false,
                pointRadius: 4,
                pointBackgroundColor: '#0369a1',
                yAxisID: 'yHum',
            }
        ]
    }
})

const history7DaysChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top' as const,
            labels: {
                boxWidth: 12,
                font: { size: 11 }
            }
        },
        tooltip: {
            mode: 'index' as const,
            intersect: false,
        }
    },
    scales: {
        yTemp: {
            type: 'linear' as const,
            position: 'left' as const,
            title: { display: true, text: 'Suhu (°C)', font: { size: 10 } },
            suggestedMin: 15,
            suggestedMax: 30,
            grid: { color: 'rgba(156, 163, 175, 0.12)' },
            ticks: { font: { size: 10 } }
        },
        yHum: {
            type: 'linear' as const,
            position: 'right' as const,
            title: { display: true, text: 'Kelembapan (%)', font: { size: 10 } },
            suggestedMin: 30,
            suggestedMax: 80,
            grid: { display: false },
            ticks: { font: { size: 10 } }
        },
        x: {
            grid: { display: false },
            ticks: { font: { size: 10 } }
        }
    }
}

// Per-rack bar chart comparison if client rents multiple racks
const multiRackBarChartData = computed(() => {
    const racks = clientRacksList.value
    return {
        labels: racks.map(r => r.code || r.name),
        datasets: [
            {
                label: 'Suhu Saat Ini (°C)',
                data: racks.map(r => r.telemetry?.suhu?.temp ?? null),
                backgroundColor: racks.map(r => {
                    const temp = r.telemetry?.suhu?.temp ?? 0
                    if (temp > 24) return 'rgba(245, 158, 11, 0.85)'
                    if (temp <= 0) return 'rgba(156, 163, 175, 0.5)'
                    return 'rgba(16, 185, 129, 0.85)'
                }),
                borderRadius: 6,
            },
            {
                label: 'Kelembapan (%)',
                data: racks.map(r => r.telemetry?.suhu?.hum ?? null),
                backgroundColor: 'rgba(2, 132, 199, 0.6)',
                borderRadius: 6,
            }
        ]
    }
})

const multiRackBarChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top' as const,
            labels: { boxWidth: 12, font: { size: 11 } }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            suggestedMax: 100,
            grid: { color: 'rgba(156, 163, 175, 0.12)' },
            ticks: { font: { size: 10 } }
        },
        x: {
            grid: { display: false },
            ticks: { font: { size: 10 } }
        }
    }
}

// Chart configurations
const deviceTypeChartData = computed(() => {
    const labels = props.stats.devices.by_type.map(item => item.divice_type || 'Unknown')
    const data = props.stats.devices.by_type.map(item => item.total)
    return {
        labels,
        datasets: [{
            data,
            backgroundColor: ['#3b82f6', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ef4444'],
            borderWidth: 2,
            borderColor: '#fff'
        }]
    }
})

const ticketStatusChartData = computed(() => {
    const labels = props.stats.tickets.by_status.map(item => item.status)
    const data = props.stats.tickets.by_status.map(item => item.total)
    const colors = {
        open: '#ef4444',
        on_progress: '#f59e0b',
        waiting_customer: '#8b5cf6',
        waiting_technician: '#06b6d4',
        waiting_vendor: '#3b82f6',
        resolved: '#10b981',
        cancelled: '#6b7280',
        closed: '#6b7280'
    }
    return {
        labels,
        datasets: [{
            data,
            backgroundColor: labels.map(label => colors[label as keyof typeof colors] || '#6b7280'),
            borderWidth: 2,
            borderColor: '#fff'
        }]
    }
})

const monthlyRackTrendData = computed(() => {
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']
    const labels = props.stats.trends.monthly_racks.map(item => monthNames[item.month - 1])
    const data = props.stats.trends.monthly_racks.map(item => item.total)
    return {
        labels,
        datasets: [{
            label: 'Racks Added',
            data,
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: true,
            tension: 0.4
        }]
    }
})

// Helper to format currency
const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount)
}

// Helper to get status color
const getStatusColor = (status: string) => {
    const colors: Record<string, string> = {
        paid: 'bg-emerald-100 text-emerald-800',
        overdue: 'bg-red-100 text-red-800',
        waiting: 'bg-yellow-100 text-yellow-800',
        sent: 'bg-blue-100 text-blue-800',
        cancelled: 'bg-gray-100 text-gray-800',
        processed: 'bg-purple-100 text-purple-800',
        rejected: 'bg-red-100 text-red-800',
        active: 'bg-emerald-100 text-emerald-800',
        inactive: 'bg-gray-100 text-gray-800',
        pending: 'bg-yellow-100 text-yellow-800',
        suspended: 'bg-orange-100 text-orange-800',
        terminated: 'bg-red-100 text-red-800',
        open: 'bg-red-100 text-red-800',
        on_progress: 'bg-yellow-100 text-yellow-800',
        waiting_customer: 'bg-purple-100 text-purple-800',
        waiting_technician: 'bg-blue-100 text-blue-800',
        waiting_vendor: 'bg-indigo-100 text-indigo-800',
        resolved: 'bg-emerald-100 text-emerald-800',
        closed: 'bg-gray-100 text-gray-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}
</script>

<template>
    <Head :title="title" />

    <div class="p-6 space-y-6 dark:bg-black">
        <!-- Welcome Section -->
        <div class="grid">
            <div class="col-span-12">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Hallo {{ user.name }}</h1>
                <label class="text-sm text-gray-500 dark:text-gray-400">Welcome to your dashboard, {{ user.name }}!</label>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Racks Card -->
            <Link href="/client/racks" 
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Rack
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.racks.total }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 dark:bg-blue-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <div class="flex items-center gap-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            {{ stats.racks.active }} Active
                        </span>
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ stats.racks.total_units }} Units
                    </span>
                </div>
            </Link>

            <!-- Devices Card -->
            <Link href="/client/devices" 
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Devices
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.devices.total }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-50 dark:bg-purple-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ stats.devices.by_type.length }} types
                    </span>
                </div>
            </Link>

            <!-- Services Card -->
            <Link href="/client/services" 
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Active Services
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.services.active }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Total: {{ stats.services.total }}
                    </span>
                </div>
            </Link>

            <!-- Invoices Card -->
            <Link href="/client/invoices" 
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Invoices
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.invoices.total }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-50 dark:bg-orange-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-3">
                    <div class="flex items-center gap-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 px-3 py-1.5">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                        </svg>
                        <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">
                            {{ stats.invoices.paid }} Paid
                        </span>
                    </div>
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ stats.invoices.overdue }} Overdue
                    </span>
                </div>
            </Link>
        </div>

        <!-- Second Row Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Paid Amount -->
            <Link href="/client/invoices" 
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Paid Amount
                        </h3>
                        <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ formatCurrency(stats.invoices.total_paid) }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 dark:bg-emerald-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </Link>

            <!-- Outstanding Amount -->
            <Link href="/client/invoices" 
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Outstanding Amount
                        </h3>
                        <h2 class="mt-2 text-xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ formatCurrency(stats.invoices.total_outstanding) }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        {{ stats.invoices.upcoming_due }} due in 7 days
                    </span>
                </div>
            </Link>

            <!-- Open Tickets -->
            <Link href="/client/tickets" 
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Open Tickets
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.tickets.open }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-50 dark:bg-red-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Total: {{ stats.tickets.total }}
                    </span>
                </div>
            </Link>

            <!-- Total Racks Card -->
            <Link href="/client/racks" 
                class="group relative overflow-hidden rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl block">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-xs font-medium text-slate-500 dark:text-slate-400">
                            Total Rented Units
                        </h3>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ stats.racks.total_units }}
                        </h2>
                    </div>
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-indigo-50 dark:bg-indigo-900/20">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        From {{ stats.racks.total }} racks
                    </span>
                </div>
            </Link>
        </div>

        <!-- Rented Racks Telemetry & Monitoring Section (Suhu & Power) -->
        <div v-if="clientRacksList.length > 0" class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 space-y-6 shadow-sm">
            <!-- Header with Rack Selector & Status -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4 dark:border-slate-700/60">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-2.5 w-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            Monitoring Suhu & Power Rack Sewa
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Telemetri sensor dan riwayat performa khusus unit rack yang Anda sewa
                    </p>
                </div>

                <!-- Multi-rack Selector Tabs (if client rents > 1 rack) -->
                <div v-if="clientRacksList.length > 1" class="flex flex-wrap items-center gap-1.5 p-1 rounded-xl bg-slate-100 dark:bg-slate-900 text-xs">
                    <button
                        v-for="(r, idx) in clientRacksList"
                        :key="r.id"
                        type="button"
                        @click="selectedRackIndex = idx"
                        :class="[
                            'px-3 py-1.5 rounded-lg font-medium transition-all flex items-center gap-1.5',
                            selectedRackIndex === idx
                                ? 'bg-white dark:bg-slate-800 text-blue-600 dark:text-blue-400 shadow-sm'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'
                        ]"
                    >
                        <Server class="h-3.5 w-3.5" />
                        <span>{{ r.code || r.name }}</span>
                        <span
                            v-if="r.telemetry?.suhu?.temp"
                            :class="[
                                'text-[10px] px-1.5 py-0.2 rounded-full font-bold',
                                (r.telemetry.suhu.temp > 24) ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                            ]"
                        >
                            {{ r.telemetry.suhu.temp }}°C
                        </span>
                    </button>
                </div>
            </div>

            <!-- Active Rack Summary & Telemetry Cards -->
            <div v-if="activeRack" class="space-y-4">
                <!-- Rack Info Banner -->
                <div class="flex flex-wrap items-center justify-between gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800 text-xs">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400 font-bold text-sm">
                            {{ activeRack.code?.slice(-3) || activeRack.name?.slice(0, 3) || 'RCK' }}
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-900 dark:text-white text-sm">
                                    {{ activeRack.name }} ({{ activeRack.code }})
                                </h3>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    Sewa: {{ activeRack.rented_units }} Unit
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                Lokasi: {{ activeRack.location_name || '-' }} • Ruang: {{ activeRack.room_name || '-' }}
                                <span v-if="activeRack.api_suhu" class="ml-1 text-slate-400">
                                    (Sensor: {{ activeRack.api_suhu }})
                                </span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <span class="text-[11px] text-slate-400 block">Status Sensor</span>
                            <span
                                v-if="activeRack.telemetry?.status === 'normal' || (!activeRack.telemetry?.status && activeRack.api_suhu)"
                                class="inline-flex items-center gap-1 font-semibold text-emerald-600 dark:text-emerald-400 text-xs"
                            >
                                <CheckCircle2 class="h-3.5 w-3.5" /> Online
                            </span>
                            <span
                                v-else-if="activeRack.telemetry?.status === 'warning'"
                                class="inline-flex items-center gap-1 font-semibold text-amber-600 dark:text-amber-400 text-xs"
                            >
                                <AlertTriangle class="h-3.5 w-3.5" /> Warning
                            </span>
                            <span
                                v-else
                                class="inline-flex items-center gap-1 font-semibold text-slate-400 text-xs"
                            >
                                {{ activeRack.api_suhu ? 'Standby' : 'Sensor Belum Dipetakan' }}
                            </span>
                        </div>

                        <Link
                            :href="`/client/racks/${activeRack.id}`"
                            class="flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-600 text-white font-medium text-xs hover:bg-blue-700 transition-colors shadow-sm"
                        >
                            <span>Detail Rack</span>
                            <ArrowUpRight class="h-3.5 w-3.5" />
                        </Link>
                    </div>
                </div>

                <!-- 4 KPI Telemetry Cards: Temp, Hum, PLN, UPS -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
                    <!-- Suhu Realtime -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 dark:border-slate-800/60 dark:bg-slate-900/40">
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span class="font-medium">Suhu Udara</span>
                            <Thermometer class="h-4 w-4 text-orange-500" />
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span
                                :class="[
                                    'text-2xl font-bold',
                                    (activeRack.telemetry?.suhu?.temp ?? 0) > 24
                                        ? 'text-amber-600 dark:text-amber-400'
                                        : 'text-slate-900 dark:text-white'
                                ]"
                            >
                                {{ activeRack.telemetry?.suhu?.temp ?? '--' }}
                            </span>
                            <span class="text-xs text-slate-400">°C</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px]">
                            <span class="text-slate-400">Ambang: 18 - 24°C</span>
                            <span
                                :class="[
                                    'font-medium text-[10px] px-1.5 py-0.5 rounded',
                                    (activeRack.telemetry?.suhu?.temp ?? 0) > 24
                                        ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300'
                                        : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300'
                                ]"
                            >
                                {{ (activeRack.telemetry?.suhu?.temp ?? 0) > 24 ? 'Hangat' : 'Optimal' }}
                            </span>
                        </div>
                    </div>

                    <!-- Kelembapan Realtime -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 dark:border-slate-800/60 dark:bg-slate-900/40">
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span class="font-medium">Kelembapan</span>
                            <Droplets class="h-4 w-4 text-cyan-500" />
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ activeRack.telemetry?.suhu?.hum ?? '--' }}
                            </span>
                            <span class="text-xs text-slate-400">%</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px]">
                            <span class="text-slate-400">Ambang: 40 - 70%</span>
                            <span class="font-medium text-[10px] px-1.5 py-0.5 rounded bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300">
                                Normal
                            </span>
                        </div>
                    </div>

                    <!-- Daya PLN Realtime -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 dark:border-slate-800/60 dark:bg-slate-900/40">
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span class="font-medium">Listrik PLN</span>
                            <Zap class="h-4 w-4 text-amber-500" />
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ activeRack.telemetry?.pln?.voltage ?? activeRack.telemetry?.pln?.volt ?? '--' }}
                            </span>
                            <span class="text-xs text-slate-400">V</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Arus: {{ activeRack.telemetry?.pln?.current ?? activeRack.telemetry?.pln?.arus ?? 0 }} A</span>
                            <span class="font-semibold text-slate-600 dark:text-slate-300">
                                {{ activeRack.telemetry?.pln?.power ?? activeRack.telemetry?.pln?.daya ?? 0 }} W
                            </span>
                        </div>
                    </div>

                    <!-- Daya UPS Realtime -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-4 dark:border-slate-800/60 dark:bg-slate-900/40">
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span class="font-medium">Backup UPS</span>
                            <Activity class="h-4 w-4 text-emerald-500" />
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ activeRack.telemetry?.ups?.voltage ?? activeRack.telemetry?.ups?.volt ?? '--' }}
                            </span>
                            <span class="text-xs text-slate-400">V</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Arus: {{ activeRack.telemetry?.ups?.current ?? activeRack.telemetry?.ups?.arus ?? 0 }} A</span>
                            <span class="font-semibold text-slate-600 dark:text-slate-300">
                                {{ activeRack.telemetry?.ups?.power ?? activeRack.telemetry?.ups?.daya ?? 0 }} W
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Grafik Section: Timeline, 7 Hari Terakhir & Per-Rack Comparison -->
                <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-900/30">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                <span v-if="activeChartTab === 'timeline'">Grafik Suhu & Kelembapan Rack {{ activeRack.code || activeRack.name }}</span>
                                <span v-else-if="activeChartTab === 'history_7days'">Riwayat Tren Harian (7 Hari Terakhir)</span>
                                <span v-else>Perbandingan Suhu Antar Rack Anda</span>
                            </h3>
                            <p class="text-[11px] text-slate-400">
                                <span v-if="activeChartTab === 'timeline'">Riwayat pencatatan sensor dan telemetri realtime pada unit rack ini</span>
                                <span v-else-if="activeChartTab === 'history_7days'">Tren harian rata-rata, suhu maksimum, dan kelembapan unit rack Anda</span>
                                <span v-else>Perbandingan suhu dan kelembapan realtime seluruh rack yang Anda sewa</span>
                            </p>
                        </div>
                        <div class="flex items-center gap-1 rounded-xl bg-white p-1 border border-slate-200 dark:border-slate-800 dark:bg-slate-900 text-xs shadow-sm">
                            <button
                                type="button"
                                @click="activeChartTab = 'timeline'"
                                :class="[
                                    'px-2.5 py-1 rounded-lg text-xs font-medium transition-colors',
                                    activeChartTab === 'timeline'
                                        ? 'bg-blue-600 text-white shadow-sm'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                ]"
                            >
                                Timeline Log
                            </button>
                            <button
                                type="button"
                                @click="activeChartTab = 'history_7days'"
                                :class="[
                                    'px-2.5 py-1 rounded-lg text-xs font-medium transition-colors',
                                    activeChartTab === 'history_7days'
                                        ? 'bg-blue-600 text-white shadow-sm'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                ]"
                            >
                                Tren 7 Hari
                            </button>
                            <button
                                v-if="clientRacksList.length > 1"
                                type="button"
                                @click="activeChartTab = 'multi_rack'"
                                :class="[
                                    'px-2.5 py-1 rounded-lg text-xs font-medium transition-colors',
                                    activeChartTab === 'multi_rack'
                                        ? 'bg-blue-600 text-white shadow-sm'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                ]"
                            >
                                Perbandingan Rack
                            </button>
                        </div>
                    </div>

                    <div class="h-60 w-full">
                        <Line
                            v-if="activeChartTab === 'timeline'"
                            :data="activeRackTimelineChartData"
                            :options="history7DaysChartOptions"
                        />
                        <Line
                            v-else-if="activeChartTab === 'history_7days'"
                            :data="history7DaysChartData"
                            :options="history7DaysChartOptions"
                        />
                        <Bar
                            v-else-if="clientRacksList.length > 1"
                            :data="multiRackBarChartData"
                            :options="multiRackBarChartOptions"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Device Type Distribution -->
            <div
                class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Device Type Distribution</h3>
                <div class="h-64 flex items-center justify-center">
                    <Doughnut 
                        v-if="stats.devices.by_type.length > 0" 
                        :data="deviceTypeChartData" 
                        :options="{ responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }"
                    />
                    <p v-else class="text-slate-500 dark:text-slate-400">No device data available</p>
                </div>
            </div>

            <!-- Ticket Status Distribution -->
            <Link href="/client/tickets"
                class="block rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6 hover:shadow-md transition-shadow">
                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Ticket Status Distribution</h3>
                <div class="h-64 flex items-center justify-center">
                    <Doughnut 
                        v-if="stats.tickets.by_status.length > 0" 
                        :data="ticketStatusChartData" 
                        :options="{ responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }"
                    />
                    <p v-else class="text-slate-500 dark:text-slate-400">No ticket data available</p>
                </div>
            </Link>
        </div>

        <!-- Monthly Trend -->
        <div
            class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
            <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">Monthly Rack Addition Trend</h3>
            <div class="h-64">
                <Line 
                    v-if="stats.trends.monthly_racks.length > 0" 
                    :data="monthlyRackTrendData" 
                    :options="{ 
                        responsive: true, 
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { 
                            y: { beginAtZero: true }
                        }
                    }"
                />
                <p v-else class="text-slate-500 dark:text-slate-400 text-center py-8">No trend data available</p>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Tickets -->
            <div
                class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Recent Tickets</h3>
                    <Link href="/client/tickets" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        View All
                    </Link>
                </div>
                <div class="space-y-3">
                    <Link v-for="ticket in stats.recent.tickets" :key="ticket.id" :href="`/client/tickets/${ticket.id}`"
                         class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ ticket.subject }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ ticket.code }} • {{ new Date(ticket.created_at).toLocaleDateString() }}
                            </p>
                        </div>
                        <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStatusColor(ticket.status)]">
                            {{ ticket.status.replace('_', ' ') }}
                        </span>
                    </Link>
                    <p v-if="stats.recent.tickets.length === 0" class="text-slate-500 dark:text-slate-400 text-center py-4">
                        No recent tickets
                    </p>
                </div>
            </div>

            <!-- Recent Invoices -->
            <div
                class="rounded-[32px] border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white">Recent Invoices</h3>
                    <Link href="/client/invoices" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">
                        View All
                    </Link>
                </div>
                <div class="space-y-3">
                    <Link v-for="invoice in stats.recent.invoices" :key="invoice.id" :href="`/client/invoices/${invoice.id}`"
                         class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-slate-700/50 hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-white">{{ invoice.invoice_number }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ formatCurrency(invoice.total) }} • {{ new Date(invoice.issue_date).toLocaleDateString() }}
                            </p>
                        </div>
                        <span :class="['px-2.5 py-1 rounded-full text-xs font-medium', getStatusColor(invoice.status)]">
                            {{ invoice.status }}
                        </span>
                    </Link>
                    <p v-if="stats.recent.invoices.length === 0" class="text-slate-500 dark:text-slate-400 text-center py-4">
                        No recent invoices                    </p>
                </div>
            </div>
        </div>
    </div>
</template>