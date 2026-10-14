<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import CardRack from '@/components/vanila/card/CardRack.vue';
import TabMenuRackClient from '@/components/vanila/menu/TabMenuRackClient.vue';
import {
    Activity,
    Thermometer,
    Droplets,
    Zap,
    RefreshCw,
    Radio,
    Clock,
    AlertTriangle,
    CheckCircle2,
    XCircle,
    Info,
} from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    BarElement,
} from 'chart.js';
import { Line } from 'vue-chartjs';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

interface TelemetryMetric {
    ada?: boolean;
    voltage?: number;
    current?: number;
    power?: number;
    temp?: number;
    hum?: number;
    status?: string;
    status_arus?: string;
    status_hum?: string;
    created_at?: string;
}

interface SingleRackData {
    rack: string;
    slave: number;
    status: string;
    terakhir: string;
    pln?: TelemetryMetric;
    ups?: TelemetryMetric;
    suhu?: TelemetryMetric;
}

interface MonitoringResponse {
    ok: boolean;
    server_time?: string;
    pemanggil?: string;
    ambang?: any;
    data?: SingleRackData;
    error?: string;
}

const props = defineProps<{
    title: string;
    rack: any;
    clients?: any;
    monitoringData?: MonitoringResponse | null;
    telemetryLogs?: any[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Rack Management',
                href: '/client/racks',
            },
        ],
    },
});

// Telemetry State
const telemetryResponse = ref<MonitoringResponse | null>(props.monitoringData || null);
const isLoading = ref(false);
const autoRefresh = ref(true);
const refreshIntervalSeconds = 120; // 2 Menit sekali (120 detik) sesuai instruksi
const secondsUntilNext = ref(refreshIntervalSeconds);
let countdownTimer: any = null;

// Time Filter for Chart: 'today' | '3days' | '7days' | 'session'
const selectedTimeFilter = ref<'session' | 'today' | '3days' | '7days'>('today');

// Format date helper for labels
const formatPointDate = (dateStr: string) => {
    try {
        const d = new Date(dateStr);
        const day = d.getDate().toString().padStart(2, '0');
        const month = (d.getMonth() + 1).toString().padStart(2, '0');
        const hour = d.getHours().toString().padStart(2, '0');
        const min = d.getMinutes().toString().padStart(2, '0');
        return `${day}/${month} ${hour}:${min}`;
    } catch {
        return dateStr;
    }
};

// Initialize history from database logs (up to 7 days)
const dbLogs = ref<any[]>(props.telemetryLogs || []);

// Session points buffer
const historyPoints = ref<
    Array<{
        time: string;
        temp: number | null;
        hum: number | null;
        plnPower: number | null;
        upsPower: number | null;
        rawDate?: string;
    }>
>([]);

const currentData = computed<SingleRackData | null>(() => {
    return telemetryResponse.value?.data || null;
});

const pushHistoryPoint = (data: SingleRackData | null) => {
    if (!data) return;
    const timeLabel = data.terakhir
        ? formatPointDate(data.terakhir)
        : new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

    // Prevent duplicate adjacent timestamp
    if (historyPoints.value.length > 0 && historyPoints.value[historyPoints.value.length - 1].time === timeLabel) {
        return;
    }

    historyPoints.value.push({
        time: timeLabel,
        temp: data.suhu?.ada ? (data.suhu.temp ?? null) : null,
        hum: data.suhu?.ada ? (data.suhu.hum ?? null) : null,
        plnPower: data.pln?.power ?? 0,
        upsPower: data.ups?.power ?? 0,
        rawDate: data.terakhir || new Date().toISOString(),
    });
};

const fetchTelemetry = async () => {
    if (!props.rack.api_suhu || isLoading.value) return;
    isLoading.value = true;
    try {
        const response = await axios.get<MonitoringResponse>(
            `/client/racks-monitoring/data?rack=${encodeURIComponent(props.rack.api_suhu)}`
        );
        if (response.data) {
            telemetryResponse.value = response.data;
            if (response.data.data) {
                pushHistoryPoint(response.data.data);
            }
        }
    } catch (err: any) {
        console.error('Failed to fetch rack telemetry', err);
    } finally {
        isLoading.value = false;
        secondsUntilNext.value = refreshIntervalSeconds;
    }
};

const toggleAutoRefresh = () => {
    autoRefresh.value = !autoRefresh.value;
    if (autoRefresh.value) {
        secondsUntilNext.value = refreshIntervalSeconds;
    }
};

onMounted(() => {
    // 1. Masukkan riwayat dari log database (maks 7 hari)
    if (dbLogs.value.length > 0) {
        dbLogs.value.forEach((log: any) => {
            const timeLabel = formatPointDate(log.recorded_at);
            historyPoints.value.push({
                time: timeLabel,
                temp: log.temp,
                hum: log.hum,
                plnPower: log.pln_power ?? 0,
                upsPower: log.ups_power ?? 0,
                rawDate: log.recorded_at,
            });
        });
    }

    // 2. Masukkan snapshot data terkini jika ada
    if (telemetryResponse.value?.data) {
        pushHistoryPoint(telemetryResponse.value.data);
    } else if (props.rack.api_suhu) {
        fetchTelemetry();
    }

    countdownTimer = setInterval(() => {
        if (!autoRefresh.value || !props.rack.api_suhu) return;
        if (secondsUntilNext.value > 1) {
            secondsUntilNext.value -= 1;
        } else {
            secondsUntilNext.value = refreshIntervalSeconds;
            fetchTelemetry();
        }
    }, 1000);
});

onUnmounted(() => {
    if (countdownTimer) clearInterval(countdownTimer);
});

// Chart Data (Filtered based on selectedTimeFilter: 7days, 3days, today, session)
const trendChartData = computed(() => {
    let points = [...historyPoints.value];

    const now = new Date().getTime();
    if (selectedTimeFilter.value === 'today') {
        const oneDayAgo = now - 24 * 60 * 60 * 1000;
        points = points.filter((p) => !p.rawDate || new Date(p.rawDate).getTime() >= oneDayAgo);
    } else if (selectedTimeFilter.value === '3days') {
        const threeDaysAgo = now - 3 * 24 * 60 * 60 * 1000;
        points = points.filter((p) => !p.rawDate || new Date(p.rawDate).getTime() >= threeDaysAgo);
    } else if (selectedTimeFilter.value === 'session') {
        points = points.slice(-10);
    } // '7days' displays all records within the retained 7 days window

    if (points.length === 0 && currentData.value) {
        points = [
            {
                time: formatPointDate(currentData.value.terakhir || new Date().toISOString()),
                temp: currentData.value?.suhu?.temp ?? null,
                hum: currentData.value?.suhu?.hum ?? null,
                plnPower: currentData.value?.pln?.power ?? 0,
                upsPower: currentData.value?.ups?.power ?? 0,
            }
        ];
    }

    return {
        labels: points.map((p) => p.time),
        datasets: [
            {
                label: 'Suhu (°C)',
                data: points.map((p) => p.temp),
                borderColor: '#f97316', // orange
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                tension: 0.35,
                fill: true,
                pointRadius: points.length > 25 ? 2 : 4,
                pointBackgroundColor: '#ea580c',
                yAxisID: 'yTemp',
            },
            {
                label: 'Kelembapan (%)',
                data: points.map((p) => p.hum),
                borderColor: '#0284c7', // sky
                backgroundColor: 'rgba(2, 132, 199, 0.08)',
                tension: 0.35,
                fill: true,
                pointRadius: points.length > 25 ? 2 : 4,
                pointBackgroundColor: '#0369a1',
                yAxisID: 'yHum',
            },
        ],
    };
});

const trendChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top' as const,
            labels: {
                boxWidth: 12,
                font: { size: 11 },
            },
        },
        tooltip: {
            mode: 'index' as const,
            intersect: false,
        },
    },
    scales: {
        yTemp: {
            type: 'linear' as const,
            position: 'left' as const,
            title: { display: true, text: 'Suhu (°C)', font: { size: 10 } },
            suggestedMin: 15,
            suggestedMax: 30,
            grid: { color: 'rgba(156, 163, 175, 0.12)' },
            ticks: { font: { size: 10 } },
        },
        yHum: {
            type: 'linear' as const,
            position: 'right' as const,
            title: { display: true, text: 'Hum (%)', font: { size: 10 } },
            suggestedMin: 30,
            suggestedMax: 80,
            grid: { display: false },
            ticks: { font: { size: 10 } },
        },
        x: {
            grid: { display: false },
            ticks: { font: { size: 10 } },
        },
    },
};

const getStatusBadge = (status?: string) => {
    switch (status?.toLowerCase()) {
        case 'normal':
            return {
                bg: 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                dot: 'bg-emerald-500',
                text: 'Normal',
            };
        case 'warning':
            return {
                bg: 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                dot: 'bg-amber-500 animate-pulse',
                text: 'Warning',
            };
        case 'offline':
            return {
                bg: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                dot: 'bg-rose-500',
                text: 'Offline',
            };
        default:
            return {
                bg: 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20',
                dot: 'bg-slate-500',
                text: status || 'Unknown',
            };
    }
};

const formatSeconds = (sec: number) => {
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return `${m}:${s < 10 ? '0' : ''}${s}`;
};
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-nowrap justify-between items-center p-4">
        <div class="flex items-start gap-3">
            <div class="flex flex-col">
                <div class="flex items-center gap-2">
                    <h1 class="text-lg font-bold">
                        {{ props.rack.code }}
                    </h1>
                    <span v-if="props.rack.api_suhu" class="rounded bg-cyan-100 px-2 py-0.5 text-xs font-mono font-bold text-cyan-800 dark:bg-cyan-950/60 dark:text-cyan-300">
                        Sensor Suhu: {{ props.rack.api_suhu }}
                    </span>
                </div>
                <span class="text-xs text-muted-foreground">
                    View and manage your racks in one place
                </span>
            </div>
        </div>
    </div>

    <!-- MAIN RACK CARD -->
    <CardRack
        :rack="rack"
        :telemetry="currentData"
        :is-loading="isLoading"
    />

    <!-- ==================================================================== -->
    <!-- TELEMETRI & SUHU MONITORING (DI BAWAH CARD RACK, TERLIHAT DI SEMUA TAB) -->
    <!-- ==================================================================== -->
    <div class="p-4 pt-0">
        <div class="rounded-3xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-6">
            <!-- Header Telemetri & Realtime Controls -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-5 dark:border-slate-800">
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white shadow-md shadow-cyan-500/20">
                        <Activity class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white">
                                Realtime Sensor Suhu & Telemetri Rack
                            </h2>
                            <span
                                v-if="currentData"
                                :class="[
                                    'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize',
                                    getStatusBadge(currentData.status).bg
                                ]"
                            >
                                <span :class="['h-1.5 w-1.5 rounded-full', getStatusBadge(currentData.status).dot]"></span>
                                {{ getStatusBadge(currentData.status).text }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            Sensor mikrokontroler aktual dari unit rack <span class="font-mono text-cyan-700 dark:text-cyan-400">{{ props.rack.api_suhu || '-' }}</span>
                        </p>
                    </div>
                </div>

                <!-- Action Controls (2 Menit Auto-refresh) -->
                <div v-if="props.rack.api_suhu" class="flex flex-wrap items-center gap-2.5">
                    <span v-if="currentData?.terakhir" class="text-xs font-mono text-slate-400 flex items-center gap-1">
                        <Clock class="h-3.5 w-3.5" />
                        {{ currentData.terakhir }}
                    </span>

                    <button
                        @click="toggleAutoRefresh"
                        :class="[
                            'inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium transition-all shadow-sm',
                            autoRefresh
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-400'
                                : 'border-slate-200 bg-white text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400'
                        ]"
                    >
                        <Radio :class="['h-3.5 w-3.5', autoRefresh ? 'animate-pulse text-emerald-500' : 'text-slate-400']" />
                        <span>Auto-refresh ({{ formatSeconds(secondsUntilNext) }})</span>
                    </button>

                    <button
                        @click="fetchTelemetry"
                        :disabled="isLoading"
                        class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 transition-colors"
                    >
                        <RefreshCw :class="['h-3.5 w-3.5', isLoading ? 'animate-spin' : '']" />
                        <span>{{ isLoading ? 'Refreshing...' : 'Refresh' }}</span>
                    </button>
                </div>
            </div>

            <!-- JIKA RACK BELUM TERHUBUNG KE API SUHU -->
            <div
                v-if="!props.rack.api_suhu"
                class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center dark:border-slate-800 dark:bg-slate-950/30"
            >
                <Thermometer class="h-10 w-10 text-slate-300 dark:text-slate-700 mb-2" />
                <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                    Sensor Suhu Belum Dikoneksikan
                </h3>
                <p class="mt-1 text-xs text-slate-500 max-w-md">
                    Rack ini belum dikaitkan dengan sensor telemetri suhu & kelistrikan. Hubungi admin data center untuk mengaktifkannya.
                </p>
            </div>

            <!-- JIKA RACK SUDAH TERHUBUNG KE API SUHU -->
            <div v-else class="space-y-6">
                <!-- KPI Telemetry Cards Grid -->
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                    <!-- Suhu (Temperature) -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 dark:border-slate-800/60 dark:bg-slate-950/40">
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span class="font-medium">Suhu Udara</span>
                            <Thermometer class="h-4 w-4 text-orange-500" />
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span
                                :class="[
                                    'text-2xl font-bold',
                                    (currentData?.suhu?.temp ?? 0) > 24 ? 'text-amber-500' : 'text-emerald-600 dark:text-emerald-400'
                                ]"
                            >
                                {{ currentData?.suhu?.ada && currentData.suhu.temp !== undefined ? currentData.suhu.temp : '-' }}
                            </span>
                            <span class="text-xs text-slate-400">°C</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[10px] text-slate-400">
                            <span>Status: {{ currentData?.suhu?.status || 'N/A' }}</span>
                            <span>Max: 24°C</span>
                        </div>
                    </div>

                    <!-- Kelembapan (Humidity) -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 dark:border-slate-800/60 dark:bg-slate-950/40">
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span class="font-medium">Kelembapan</span>
                            <Droplets class="h-4 w-4 text-cyan-500" />
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span
                                :class="[
                                    'text-2xl font-bold',
                                    (currentData?.suhu?.hum ?? 0) < 40 || (currentData?.suhu?.hum ?? 0) > 60
                                        ? 'text-amber-500'
                                        : 'text-sky-600 dark:text-sky-400'
                                ]"
                            >
                                {{ currentData?.suhu?.ada && currentData.suhu.hum !== undefined ? currentData.suhu.hum : '-' }}
                            </span>
                            <span class="text-xs text-slate-400">%</span>
                        </div>
                        <div class="mt-1 flex items-center justify-between text-[10px] text-slate-400">
                            <span>Status: {{ currentData?.suhu?.status_hum || 'N/A' }}</span>
                            <span>40 - 60%</span>
                        </div>
                    </div>

                    <!-- PLN Voltage -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 dark:border-slate-800/60 dark:bg-slate-950/40">
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span class="font-medium">Tegangan PLN</span>
                            <Zap class="h-4 w-4 text-amber-500" />
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ currentData?.pln?.voltage ?? '-' }}
                            </span>
                            <span class="text-xs text-slate-400">V</span>
                        </div>
                        <div class="mt-1 text-[10px] text-slate-400">
                            Arus: {{ currentData?.pln?.current ?? '-' }} A
                        </div>
                    </div>

                    <!-- PLN Power -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 dark:border-slate-800/60 dark:bg-slate-950/40">
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span class="font-medium">Daya PLN</span>
                            <Zap class="h-4 w-4 text-amber-500" />
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ currentData?.pln?.power ?? '-' }}
                            </span>
                            <span class="text-xs text-slate-400">W</span>
                        </div>
                        <div class="mt-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold uppercase">
                            {{ currentData?.pln?.status || 'N/A' }}
                        </div>
                    </div>

                    <!-- UPS Voltage -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 dark:border-slate-800/60 dark:bg-slate-950/40">
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span class="font-medium">Tegangan UPS</span>
                            <Zap class="h-4 w-4 text-cyan-500" />
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ currentData?.ups?.voltage ?? '-' }}
                            </span>
                            <span class="text-xs text-slate-400">V</span>
                        </div>
                        <div class="mt-1 text-[10px] text-slate-400">
                            Arus: {{ currentData?.ups?.current ?? '-' }} A
                        </div>
                    </div>

                    <!-- UPS Power -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/60 p-4 dark:border-slate-800/60 dark:bg-slate-950/40">
                        <div class="flex items-center justify-between text-slate-500 text-xs">
                            <span class="font-medium">Daya UPS</span>
                            <Zap class="h-4 w-4 text-cyan-500" />
                        </div>
                        <div class="mt-2 flex items-baseline gap-1">
                            <span class="text-2xl font-bold text-slate-900 dark:text-white">
                                {{ currentData?.ups?.power ?? '-' }}
                            </span>
                            <span class="text-xs text-slate-400">W</span>
                        </div>
                        <div class="mt-1 text-[10px] font-semibold uppercase" :class="currentData?.ups?.status === 'offline' ? 'text-rose-500' : 'text-emerald-500'">
                            {{ currentData?.ups?.status || 'N/A' }}
                        </div>
                    </div>
                </div>

                <!-- Grafik Garis Tren Suhu & Kelembapan + Status Listrik -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                    <!-- Line Chart Tren Suhu & Hum -->
                    <div class="lg:col-span-2 rounded-2xl border border-slate-100 bg-slate-50/50 p-5 dark:border-slate-800 dark:bg-slate-950/30">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-3">
                            <div>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Grafik Tren Suhu & Kelembapan Rack {{ props.rack.api_suhu }}
                                </h3>
                                <p class="text-[11px] text-slate-400">
                                    Riwayat telemetri (Retensi database tersimpan 7 hari terakhir)
                                </p>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <!-- Time Filter Pills -->
                                <div class="flex items-center rounded-lg border border-slate-200 bg-white p-0.5 text-[11px] font-medium dark:border-slate-800 dark:bg-slate-900">
                                    <button
                                        type="button"
                                        @click="selectedTimeFilter = 'session'"
                                        :class="[
                                            'rounded px-2 py-0.5 transition-colors',
                                            selectedTimeFilter === 'session'
                                                ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                        ]"
                                    >
                                        Live
                                    </button>
                                    <button
                                        type="button"
                                        @click="selectedTimeFilter = 'today'"
                                        :class="[
                                            'rounded px-2 py-0.5 transition-colors',
                                            selectedTimeFilter === 'today'
                                                ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                        ]"
                                    >
                                        24 Jam
                                    </button>
                                    <button
                                        type="button"
                                        @click="selectedTimeFilter = '3days'"
                                        :class="[
                                            'rounded px-2 py-0.5 transition-colors',
                                            selectedTimeFilter === '3days'
                                                ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                        ]"
                                    >
                                        3 Hari
                                    </button>
                                    <button
                                        type="button"
                                        @click="selectedTimeFilter = '7days'"
                                        :class="[
                                            'rounded px-2 py-0.5 transition-colors',
                                            selectedTimeFilter === '7days'
                                                ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                                : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                        ]"
                                    >
                                        7 Hari
                                    </button>
                                </div>

                                <span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-mono text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                    Slave #{{ currentData?.slave ?? '-' }}
                                </span>
                            </div>
                        </div>

                        <div class="h-64 w-full">
                            <Line :data="trendChartData" :options="trendChartOptions" />
                        </div>
                    </div>

                    <!-- Detail Feed Daya Listrik (PLN & UPS) -->
                    <div class="rounded-2xl border border-slate-100 bg-slate-50/50 p-5 dark:border-slate-800 dark:bg-slate-950/30 flex flex-col justify-between">
                        <div>
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">
                                Rincian Feed Kelistrikan Dual-Source
                            </h3>

                            <div class="space-y-3 text-xs">
                                <!-- PLN Box -->
                                <div class="rounded-xl border border-slate-200/80 bg-white p-3.5 dark:border-slate-800 dark:bg-slate-900">
                                    <div class="flex items-center justify-between font-semibold text-slate-800 dark:text-slate-200 mb-2">
                                        <span class="flex items-center gap-1.5">
                                            <Zap class="h-4 w-4 text-amber-500" />
                                            Source A: PLN
                                        </span>
                                        <span :class="['text-[10px] font-bold uppercase', currentData?.pln?.status === 'offline' ? 'text-rose-500' : 'text-emerald-500']">
                                            {{ currentData?.pln?.status || 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-center pt-1 border-t border-slate-100 dark:border-slate-800">
                                        <div>
                                            <span class="text-[10px] text-slate-400">Volt</span>
                                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ currentData?.pln?.voltage ?? '-' }} V</p>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400">Arus</span>
                                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ currentData?.pln?.current ?? '-' }} A</p>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400">Daya</span>
                                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ currentData?.pln?.power ?? '-' }} W</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- UPS Box -->
                                <div class="rounded-xl border border-slate-200/80 bg-white p-3.5 dark:border-slate-800 dark:bg-slate-900">
                                    <div class="flex items-center justify-between font-semibold text-slate-800 dark:text-slate-200 mb-2">
                                        <span class="flex items-center gap-1.5">
                                            <Zap class="h-4 w-4 text-cyan-500" />
                                            Source B: UPS
                                        </span>
                                        <span :class="['text-[10px] font-bold uppercase', currentData?.ups?.status === 'offline' ? 'text-rose-500' : 'text-emerald-500']">
                                            {{ currentData?.ups?.status || 'N/A' }}
                                        </span>
                                    </div>
                                    <div class="grid grid-cols-3 gap-2 text-center pt-1 border-t border-slate-100 dark:border-slate-800">
                                        <div>
                                            <span class="text-[10px] text-slate-400">Volt</span>
                                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ currentData?.ups?.voltage ?? '-' }} V</p>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400">Arus</span>
                                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ currentData?.ups?.current ?? '-' }} A</p>
                                        </div>
                                        <div>
                                            <span class="text-[10px] text-slate-400">Daya</span>
                                            <p class="font-bold text-slate-800 dark:text-slate-200">{{ currentData?.ups?.power ?? '-' }} W</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Total Daya (PLN + UPS):</span>
                            <span class="font-bold text-slate-900 dark:text-white">
                                {{ ((currentData?.pln?.power ?? 0) + (currentData?.ups?.power ?? 0)).toFixed(1) }} Watt
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB MENU RACK CLIENT -->
    <TabMenuRackClient :rack="rack" />
</template>