<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

import {
    DollarSign,
    FileText,
    AlertTriangle,
    RefreshCw,
    Users,
    Server,
    HardDrive,
    Headset,
    Thermometer,
    Droplets,
    Zap,
    Activity,
    CheckCircle2,
    XCircle,
    ArrowUpRight,
} from 'lucide-vue-next'

import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    Filler,
} from 'chart.js'

import { Bar, Pie, Line } from 'vue-chartjs'

ChartJS.register(
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    BarElement,
    PointElement,
    LineElement,
    Filler
)

interface RackMonitoringData {
    ok?: boolean
    error?: string
    server_time?: string
    jumlah?: number
    ambang?: any
    data?: Array<{
        rack: string
        slave: number
        status: string
        terakhir: string
        pln?: {
            ada?: boolean
            voltage?: number
            current?: number
            power?: number
            status?: string
        }
        ups?: {
            ada?: boolean
            voltage?: number
            current?: number
            power?: number
            status?: string
        }
        suhu?: {
            ada?: boolean
            temp?: number
            hum?: number
            status?: string
            status_hum?: string
        }
    }>
}

interface TemperatureHistoryItem {
    log_date: string
    avg_temp: number
    max_temp: number
    min_temp: number
    avg_hum: number
}

const props = defineProps<{
    title: string
    year: number
    monthlyRevenue: number
    yearlyRevenue: number
    invoiceStats: Record<string, number>
    overdueInvoices: any[]
    upcomingServices: any[]
    recentInvoices: any[]
    revenueChart: number[]
    totalClients: number
    activeServices: number
    totalDevices: number
    openTickets: number
    monitoringData?: RackMonitoringData
    temperatureHistory?: TemperatureHistoryItem[] | Record<string, TemperatureHistoryItem>
    telemetryLogs?: Array<{
        time: string
        temp: number
        max_temp: number
        hum: number
        recorded_at: string
    }>
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '#' }
        ],
    },
})

/**
 * =========================================
 * YEAR FILTER & REFRESH
 * =========================================
 */
const selectedYear = ref(props.year)
const isRefreshing = ref(false)

// Dashboard Chart Tab: 'per_rack' vs 'history_7days'
const activeChartTab = ref<'per_rack' | 'history_7days'>('history_7days')

const changeYear = () => {
    router.get('/admin/dashboard', {
        year: selectedYear.value
    }, {
        preserveState: true,
        replace: true
    })
}

const refreshDashboard = () => {
    isRefreshing.value = true
    router.reload({
        onFinish: () => {
            isRefreshing.value = false
        }
    })
}

/**
 * =========================================
 * MONITORING METRICS COMPUTED
 * =========================================
 */
const rackList = computed(() => props.monitoringData?.data || [])

const normalRacks = computed(() => {
    return rackList.value.filter(r => r.status?.toLowerCase() === 'normal').length
})

const warningRacks = computed(() => {
    return rackList.value.filter(
        r => r.status?.toLowerCase() === 'warning' || r.suhu?.status === 'warning' || r.suhu?.status_hum === 'warning'
    ).length
})

const offlineRacks = computed(() => {
    return rackList.value.filter(r => r.status?.toLowerCase() === 'offline').length
})

const avgTemp = computed(() => {
    const valid = rackList.value.filter(r => r.suhu?.ada && typeof r.suhu?.temp === 'number')
    if (!valid.length) return 0
    const sum = valid.reduce((acc, curr) => acc + (curr.suhu?.temp || 0), 0)
    return (sum / valid.length).toFixed(1)
})

const avgHumidity = computed(() => {
    const valid = rackList.value.filter(r => r.suhu?.ada && typeof r.suhu?.hum === 'number')
    if (!valid.length) return 0
    const sum = valid.reduce((acc, curr) => acc + (curr.suhu?.hum || 0), 0)
    return (sum / valid.length).toFixed(1)
})

// Top racks with highest temperature
const hottestRacks = computed(() => {
    return [...rackList.value]
        .filter(r => r.suhu?.ada && typeof r.suhu?.temp === 'number')
        .sort((a, b) => (b.suhu?.temp || 0) - (a.suhu?.temp || 0))
        .slice(0, 5)
})

/**
 * 7 Days Daily History / Timeline Line Chart (From rack_telemetry_logs)
 */
const history7DaysChartData = computed(() => {
    // 1. Jika ada telemetryLogs (waktu berkala seperti di detail rack), gunakan agar terbentuk garis tren kontinu
    const logs = props.telemetryLogs || []
    if (logs.length > 1) {
        return {
            labels: logs.map(l => l.time),
            datasets: [
                {
                    label: 'Suhu Rata-rata (°C)',
                    data: logs.map(l => l.temp !== null && l.temp !== undefined ? Number(l.temp) : null),
                    borderColor: '#ea580c', // orange
                    backgroundColor: 'rgba(234, 88, 12, 0.1)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: logs.length > 25 ? 2 : 4,
                    pointBackgroundColor: '#c2410c',
                    spanGaps: true,
                    yAxisID: 'yTemp',
                },
                {
                    label: 'Suhu Maksimum (°C)',
                    data: logs.map(l => l.max_temp !== null && l.max_temp !== undefined ? Number(l.max_temp) : null),
                    borderColor: '#ef4444', // red
                    backgroundColor: 'transparent',
                    borderDash: [5, 5],
                    tension: 0.35,
                    pointRadius: logs.length > 25 ? 2 : 3,
                    pointBackgroundColor: '#b91c1c',
                    spanGaps: true,
                    yAxisID: 'yTemp',
                },
                {
                    label: 'Kelembapan Rata-rata (%)',
                    data: logs.map(l => l.hum !== null && l.hum !== undefined ? Number(l.hum) : null),
                    borderColor: '#0284c7', // sky
                    backgroundColor: 'rgba(2, 132, 199, 0.08)',
                    tension: 0.35,
                    fill: false,
                    pointRadius: logs.length > 25 ? 2 : 4,
                    pointBackgroundColor: '#0369a1',
                    spanGaps: true,
                    yAxisID: 'yHum',
                }
            ]
        }
    }

    // 2. Fallback ke agregasi harian 7 hari
    const raw = props.temperatureHistory
    const history: TemperatureHistoryItem[] = Array.isArray(raw) ? raw : (raw && typeof raw === 'object' ? Object.values(raw) : [])
    
    if (!history.length) {
        return {
            labels: ['Hari Ini'],
            datasets: [
                {
                    label: 'Suhu Rata-rata (°C)',
                    data: [null],
                    borderColor: '#ea580c',
                    backgroundColor: 'rgba(234, 88, 12, 0.1)',
                }
            ]
        }
    }

    const labels = history.map(h => {
        if (!h.log_date) return ''
        const parts = h.log_date.split('-')
        return parts.length >= 3 ? `${parts[2]}/${parts[1]}` : h.log_date // format DD/MM
    })

    return {
        labels,
        datasets: [
            {
                label: 'Suhu Rata-rata (°C)',
                data: history.map(h => h.avg_temp !== null && h.avg_temp !== undefined ? Number(h.avg_temp) : null),
                borderColor: '#ea580c', // orange
                backgroundColor: 'rgba(234, 88, 12, 0.1)',
                tension: 0.35,
                fill: true,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#c2410c',
                spanGaps: true,
                yAxisID: 'yTemp',
            },
            {
                label: 'Suhu Maksimum (°C)',
                data: history.map(h => h.max_temp !== null && h.max_temp !== undefined ? Number(h.max_temp) : null),
                borderColor: '#ef4444', // red
                backgroundColor: 'transparent',
                borderDash: [5, 5],
                tension: 0.35,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#b91c1c',
                spanGaps: true,
                yAxisID: 'yTemp',
            },
            {
                label: 'Kelembapan Rata-rata (%)',
                data: history.map(h => h.avg_hum !== null && h.avg_hum !== undefined ? Number(h.avg_hum) : null),
                borderColor: '#0284c7', // sky
                backgroundColor: 'rgba(2, 132, 199, 0.08)',
                tension: 0.35,
                fill: false,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointBackgroundColor: '#0369a1',
                spanGaps: true,
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

/**
 * Temperature Bar Chart (Sample 12 representative racks across rows)
 */
const rackTempChartData = computed(() => {
    const racks = rackList.value.slice(0, 14)
    return {
        labels: racks.map(r => r.rack),
        datasets: [
            {
                label: 'Suhu (°C)',
                data: racks.map(r => r.suhu?.temp ?? null),
                backgroundColor: racks.map(r => {
                    const temp = r.suhu?.temp ?? 0
                    if (temp > 24) return 'rgba(245, 158, 11, 0.85)' // amber
                    if (temp <= 0) return 'rgba(156, 163, 175, 0.5)'
                    return 'rgba(16, 185, 129, 0.85)' // emerald
                }),
                borderColor: racks.map(r => {
                    const temp = r.suhu?.temp ?? 0
                    if (temp > 24) return '#d97706'
                    return '#059669'
                }),
                borderWidth: 1,
                borderRadius: 4,
            },
            {
                label: 'Kelembapan (%)',
                data: racks.map(r => r.suhu?.hum ?? null),
                backgroundColor: 'rgba(56, 189, 248, 0.45)', // cyan/sky
                borderColor: '#0284c7',
                borderWidth: 1,
                borderRadius: 4,
            }
        ]
    }
})

const rackChartOptions = {
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
        y: {
            beginAtZero: true,
            grid: { color: 'rgba(156, 163, 175, 0.15)' },
            ticks: { font: { size: 10 } }
        },
        x: {
            grid: { display: false },
            ticks: { font: { size: 10 } }
        }
    }
}

/**
 * =========================================
 * CHART DATA
 * =========================================
 */
const revenueChartData = {
    labels: [
        'Jan','Feb','Mar','Apr','May','Jun',
        'Jul','Aug','Sep','Oct','Nov','Dec'
    ],
    datasets: [
        {
            label: 'Revenue',
            data: props.revenueChart,
            borderColor: '#111827',
            backgroundColor: 'rgba(17,24,39,0.08)',
            tension: 0.4,
            fill: true,
        }
    ]
}

const invoiceChartData = {
    labels: Object.keys(props.invoiceStats),
    datasets: [
        {
            label: 'Invoices',
            data: Object.values(props.invoiceStats),
            backgroundColor: [
                '#111827',
                '#6b7280',
                '#9ca3af',
                '#d1d5db'
            ],
        }
    ]
}
</script>

<template>

    <Head :title="title" />

    <div class="p-6 space-y-6 bg-gray-50 dark:bg-black min-h-screen">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ title }}
                </h1>
                <p class="text-sm text-gray-500">
                    Billing • Revenue • Rack Environmental Telemetry Control Center
                </p>
            </div>

            <div class="flex items-center gap-3">

                <select
                    v-model="selectedYear"
                    @change="changeYear"
                    class="rounded-xl border px-3 py-2 text-sm bg-white dark:bg-gray-900">

                    <option :value="2026">2026</option>
                    <option :value="2025">2025</option>
                    <option :value="2024">2024</option>

                </select>

                <button
                    @click="refreshDashboard"
                    :disabled="isRefreshing"
                    class="flex items-center gap-2 rounded-xl bg-black text-white px-4 py-2 text-sm hover:bg-gray-800 disabled:opacity-50 transition-colors">
                    <RefreshCw :class="['h-4 w-4', isRefreshing ? 'animate-spin' : '']" />
                    Refresh
                </button>

            </div>

        </div>

        <!-- ================================================= -->
        <!-- SUHU & ENVIRONMENTAL MONITORING SUMMARY (RINGKAS) -->
        <!-- ================================================= -->
        <div class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-5">
            <!-- Section Title & Link to Full Page -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 pb-4 dark:border-slate-800">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-50 text-cyan-600 dark:bg-cyan-950/50 dark:text-cyan-400">
                        <Activity class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900 dark:text-white">
                            Monitoring Suhu & Telemetri Rack
                        </h2>
                        <p class="text-xs text-slate-500">
                            Ringkasan suhu, kelembapan & daya sensor Data Center (Realtime)
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span v-if="props.monitoringData?.server_time" class="text-xs text-slate-400 font-mono">
                        {{ props.monitoringData.server_time }}
                    </span>
                    <Link
                        href="/admin/racks/monitoring"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-100 dark:bg-blue-950/50 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors"
                    >
                        <span>Lihat Semua ({{ rackList.length }} Rack)</span>
                        <ArrowUpRight class="h-3.5 w-3.5" />
                    </Link>
                </div>
            </div>

            <!-- Mini Quick Status Grid (To the point) -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <!-- Rata-rata Suhu -->
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 dark:border-slate-800/60 dark:bg-slate-950/40">
                    <div class="flex items-center justify-between text-slate-500 text-xs">
                        <span>Rata Suhu</span>
                        <Thermometer class="h-4 w-4 text-orange-500" />
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1">
                        <span class="text-xl font-bold text-slate-900 dark:text-white">{{ avgTemp }}</span>
                        <span class="text-xs text-slate-400">°C</span>
                    </div>
                    <span class="text-[10px] text-slate-400">Normal: &lt; 24°C</span>
                </div>

                <!-- Rata-rata Kelembapan -->
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 dark:border-slate-800/60 dark:bg-slate-950/40">
                    <div class="flex items-center justify-between text-slate-500 text-xs">
                        <span>Rata Hum</span>
                        <Droplets class="h-4 w-4 text-cyan-500" />
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1">
                        <span class="text-xl font-bold text-slate-900 dark:text-white">{{ avgHumidity }}</span>
                        <span class="text-xs text-slate-400">%</span>
                    </div>
                    <span class="text-[10px] text-slate-400">Normal: 40 - 60%</span>
                </div>

                <!-- Rack Normal -->
                <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-3.5 dark:border-emerald-950/40 dark:bg-emerald-950/20">
                    <div class="flex items-center justify-between text-emerald-600 text-xs">
                        <span>Rack Normal</span>
                        <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1">
                        <span class="text-xl font-bold text-emerald-700 dark:text-emerald-400">{{ normalRacks }}</span>
                        <span class="text-xs text-emerald-600/70">/ {{ rackList.length }}</span>
                    </div>
                    <span class="text-[10px] text-emerald-600/80">Kondisi stabil</span>
                </div>

                <!-- Rack Warning -->
                <div class="rounded-xl border border-amber-100 bg-amber-50/40 p-3.5 dark:border-amber-950/40 dark:bg-amber-950/20">
                    <div class="flex items-center justify-between text-amber-600 text-xs">
                        <span>Warning</span>
                        <AlertTriangle class="h-4 w-4 text-amber-500" />
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1">
                        <span class="text-xl font-bold text-amber-700 dark:text-amber-400">{{ warningRacks }}</span>
                        <span class="text-xs text-amber-600/70">Rack</span>
                    </div>
                    <span class="text-[10px] text-amber-600/80">Perlu perhatian</span>
                </div>

                <!-- Rack Offline -->
                <div class="rounded-xl border border-rose-100 bg-rose-50/40 p-3.5 dark:border-rose-950/40 dark:bg-rose-950/20">
                    <div class="flex items-center justify-between text-rose-600 text-xs">
                        <span>Offline</span>
                        <XCircle class="h-4 w-4 text-rose-500" />
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1">
                        <span class="text-xl font-bold text-rose-700 dark:text-rose-400">{{ offlineRacks }}</span>
                        <span class="text-xs text-rose-600/70">Rack</span>
                    </div>
                    <span class="text-[10px] text-rose-600/80">Koneksi putus</span>
                </div>

                <!-- Total Monitored -->
                <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 dark:border-slate-800/60 dark:bg-slate-950/40">
                    <div class="flex items-center justify-between text-slate-500 text-xs">
                        <span>Total Rack</span>
                        <Server class="h-4 w-4 text-blue-500" />
                    </div>
                    <div class="mt-1.5 flex items-baseline gap-1">
                        <span class="text-xl font-bold text-slate-900 dark:text-white">{{ rackList.length }}</span>
                        <span class="text-xs text-slate-400">Unit</span>
                    </div>
                    <span class="text-[10px] text-slate-400">Data Center LDX</span>
                </div>
            </div>

            <!-- Chart & Top Highlights Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 pt-2">
                <!-- Chart: 7 Hari Terakhir / Per Rack -->
                <div class="lg:col-span-2 rounded-xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-950/30">
                    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                {{ activeChartTab === 'history_7days' ? 'Tren Suhu & Kelembapan (7 Hari Terakhir)' : 'Perbandingan Suhu per Rack (Realtime)' }}
                            </h3>
                            <p class="text-[11px] text-slate-400">
                                {{ activeChartTab === 'history_7days' ? 'Agregasi rata-rata dan suhu maksimum harian ruang server' : 'Data sensor realtime pada baris rack 1A, 1B, 2A, dll.' }}
                            </p>
                        </div>
                        <div class="flex items-center gap-1 rounded-lg bg-white p-1 border border-slate-200 dark:border-slate-800 dark:bg-slate-900 text-xs shadow-sm">
                            <button
                                type="button"
                                @click="activeChartTab = 'history_7days'"
                                :class="[
                                    'px-2.5 py-1 rounded-md text-xs font-medium transition-colors',
                                    activeChartTab === 'history_7days'
                                        ? 'bg-blue-600 text-white shadow-sm'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                ]"
                            >
                                Tren 7 Hari
                            </button>
                            <button
                                type="button"
                                @click="activeChartTab = 'per_rack'"
                                :class="[
                                    'px-2.5 py-1 rounded-md text-xs font-medium transition-colors',
                                    activeChartTab === 'per_rack'
                                        ? 'bg-blue-600 text-white shadow-sm'
                                        : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                ]"
                            >
                                Per Rack
                            </button>
                        </div>
                    </div>
                    <div class="h-60 w-full">
                        <Line
                            v-if="activeChartTab === 'history_7days'"
                            :data="history7DaysChartData"
                            :options="history7DaysChartOptions"
                        />
                        <Bar
                            v-else
                            :data="rackTempChartData"
                            :options="rackChartOptions"
                        />
                    </div>
                </div>

                <!-- Top Alert / Highest Temp Racks -->
                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-950/30 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                                Suhu Tertinggi Saat Ini
                            </h3>
                            <span class="text-[11px] text-slate-400">Top 5 Rack</span>
                        </div>

                        <div class="space-y-2.5">
                            <div
                                v-for="item in hottestRacks"
                                :key="item.rack"
                                class="flex items-center justify-between rounded-lg bg-white p-2.5 border border-slate-200/60 dark:border-slate-800 dark:bg-slate-900 text-xs"
                            >
                                <div class="flex items-center gap-2">
                                    <div class="flex h-7 w-7 items-center justify-center rounded bg-slate-100 font-bold text-slate-800 dark:bg-slate-800 dark:text-slate-200 text-[11px]">
                                        {{ item.rack }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900 dark:text-white">Rack {{ item.rack }}</p>
                                        <p class="text-[10px] text-slate-400">Slave #{{ item.slave }} • Hum {{ item.suhu?.hum }}%</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        :class="[
                                            'font-bold',
                                            (item.suhu?.temp || 0) > 24 ? 'text-amber-500' : 'text-emerald-500'
                                        ]"
                                    >
                                        {{ item.suhu?.temp }} °C
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-200/50 dark:border-slate-800 flex justify-between items-center text-xs text-slate-500">
                        <span>Lihat detail telemetri sensor</span>
                        <Link
                            href="/admin/racks/monitoring"
                            class="font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400"
                        >
                            Buka Suhu Monitoring →
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- OPERATIONAL KPI CARDS -->
        <div class="grid md:grid-cols-4 gap-4">

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs text-gray-500">Total Active Clients</p>
                <h2 class="text-xl font-bold mt-1">
                    {{ totalClients }}
                </h2>
                <Users class="h-5 w-5 text-indigo-500 mt-2" />
            </div>

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs text-gray-500">Active Services</p>
                <h2 class="text-xl font-bold mt-1">
                    {{ activeServices }}
                </h2>
                <Server class="h-5 w-5 text-emerald-500 mt-2" />
            </div>

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs text-gray-500">Total Devices in DC</p>
                <h2 class="text-xl font-bold mt-1">
                    {{ totalDevices }}
                </h2>
                <HardDrive class="h-5 w-5 text-blue-500 mt-2" />
            </div>

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs text-gray-500">Open Support Tickets</p>
                <h2 class="text-xl font-bold mt-1">
                    {{ openTickets }}
                </h2>
                <Headset class="h-5 w-5 text-orange-500 mt-2" />
            </div>

        </div>

        <!-- FINANCIAL KPI CARDS -->
        <div class="grid md:grid-cols-3 gap-4">

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs text-gray-500">Monthly Revenue</p>
                <h2 class="text-xl font-bold mt-1">
                    Rp {{ Number(monthlyRevenue).toLocaleString('id-ID') }}
                </h2>
                <DollarSign class="h-5 w-5 text-gray-400 mt-2" />
            </div>

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs text-gray-500">Yearly Revenue</p>
                <h2 class="text-xl font-bold mt-1">
                    Rp {{ Number(yearlyRevenue).toLocaleString('id-ID') }}
                </h2>
                <DollarSign class="h-5 w-5 text-gray-400 mt-2" />
            </div>

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <p class="text-xs text-gray-500">Total Invoices</p>
                <h2 class="text-xl font-bold mt-1">
                    {{ Object.values(invoiceStats).reduce((a,b)=>a+b,0) }}
                </h2>
                <FileText class="h-5 w-5 text-gray-400 mt-2" />
            </div>

        </div>

        <!-- CHART SECTION -->
        <div class="grid md:grid-cols-3 gap-6">

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm md:col-span-2">
                <h3 class="font-semibold mb-4 text-gray-900 dark:text-white">
                    Revenue Growth
                </h3>
                <Line :data="revenueChartData" />
            </div>

            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">
                <h3 class="font-semibold mb-4 text-gray-900 dark:text-white">
                    Invoice Status
                </h3>
                <Pie :data="invoiceChartData" />
            </div>

        </div>

        <!-- LOWER SECTION -->
        <div class="grid md:grid-cols-2 gap-6">

            <!-- OVERDUE -->
            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">

                <div class="flex items-center gap-2 text-red-600 font-semibold mb-4">
                    <AlertTriangle class="h-5 w-5" />
                    Overdue Invoices
                </div>

                <div v-if="overdueInvoices.length === 0" class="text-sm text-gray-500">
                    No overdue invoices
                </div>

                <div v-for="inv in overdueInvoices" :key="inv.id"
                    class="flex justify-between py-2 border-b">

                    <div>
                        <p class="font-semibold">{{ inv.invoice_number }}</p>
                        <p class="text-xs text-gray-500">
                            {{ inv.client?.company_name }}
                        </p>
                    </div>

                    <span class="text-red-600 font-bold">
                        Rp {{ Number(inv.total).toLocaleString('id-ID') }}
                    </span>

                </div>

            </div>

            <!-- UPCOMING -->
            <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">

                <h3 class="font-semibold mb-4 text-gray-900 dark:text-white">
                    Upcoming Renewals
                </h3>

                <div v-for="service in upcomingServices" :key="service.id"
                    class="flex justify-between py-2 border-b">

                    <div>
                        <p class="font-semibold">{{ service.name }}</p>
                        <p class="text-xs text-gray-500">
                            {{ service.client?.company_name }}
                        </p>
                    </div>

                    <span class="text-gray-600 text-sm">
                        {{ service.next_due_date }}
                    </span>

                </div>

            </div>

        </div>

        <!-- RECENT -->
        <div class="rounded-2xl border bg-white dark:bg-gray-900 p-5 shadow-sm">

            <h3 class="font-semibold mb-4 text-gray-900 dark:text-white">
                Recent Invoices
            </h3>

            <div v-for="inv in recentInvoices" :key="inv.id"
                class="flex justify-between py-2 border-b hover:bg-gray-50 dark:hover:bg-gray-800 px-2 rounded-lg">

                <div>
                    <p class="font-semibold">{{ inv.invoice_number }}</p>
                    <p class="text-xs text-gray-500">{{ inv.status }}</p>
                </div>

                <span class="font-bold">
                    Rp {{ Number(inv.total).toLocaleString('id-ID') }}
                </span>

            </div>

        </div>

    </div>

</template>