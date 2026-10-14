<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import CardRack from '@/components/vanila/card/CardRack.vue';
import TabMenuRack from '@/components/vanila/menu/TabMenuRack.vue';
import {
    Pencil,
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
    Unlink,
    Info,
    SlidersHorizontal,
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
import { Line, Bar } from 'vue-chartjs';

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
    clients: any;
    invoices: any;
    interconnections: any;
    monitoringData?: MonitoringResponse | null;
    telemetryLogs?: any[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Rack Management',
                href: '/admin/racks',
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
            `/admin/racks/monitoring/data?rack=${encodeURIComponent(props.rack.api_suhu)}`
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
                        API Sensor: {{ props.rack.api_suhu }}
                    </span>
                </div>
                <span class="text-xs text-muted-foreground">
                    View and manage your racks in one place
                </span>
            </div>
        </div>
        <div class="flex gap-2">
            <Link :href="`/admin/racks/${props.rack.id}/edit`"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2">
                <Pencil class="w-4 h-4 mr-2" />
                Edit Rack
            </Link>
        </div>
    </div>

    <!-- MAIN RACK CARD -->
    <CardRack
        :rack="rack"
        :telemetry="currentData"
        :is-loading="isLoading"
    />

    <!-- TABS SECTION -->
    <TabMenuRack
        :rack="rack"
        :clients="clients"
        :invoices="invoices"
        :interconnections="interconnections"
        :telemetry="currentData"
        :telemetry-response="telemetryResponse"
        :is-loading="isLoading"
        :auto-refresh="autoRefresh"
        :seconds-until-next="secondsUntilNext"
        :selected-time-filter="selectedTimeFilter"
        :trend-chart-data="trendChartData"
        :trend-chart-options="trendChartOptions"
        @refresh="fetchTelemetry"
        @toggle-auto-refresh="toggleAutoRefresh"
        @change-time-filter="(val: any) => selectedTimeFilter = val"
    />
</template>