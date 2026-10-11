<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import {
    Activity,
    Thermometer,
    Droplets,
    Zap,
    RefreshCw,
    AlertTriangle,
    CheckCircle2,
    XCircle,
    Search,
    SlidersHorizontal,
    Radio,
    Clock,
    Server,
    ShieldAlert,
    Gauge,
    Info,
    ChevronDown,
    ArrowUpDown,
} from 'lucide-vue-next';

interface MetricDetail {
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

interface RackMonitoringItem {
    rack: string;
    slave: number;
    status: 'normal' | 'warning' | 'offline' | string;
    terakhir: string;
    pln?: MetricDetail;
    ups?: MetricDetail;
    suhu?: MetricDetail;
}

interface AmbangItem {
    unit: string;
    normal_min: number;
    normal_max: number;
    critical_min: number;
    critical_max: number;
}

interface MonitoringResponse {
    ok: boolean;
    error?: string;
    server_time?: string;
    pemanggil?: string;
    ambang?: {
        pln?: AmbangItem;
        pln_arus?: AmbangItem;
        ups?: AmbangItem;
        ups_arus?: AmbangItem;
        temp?: AmbangItem;
        hum?: AmbangItem;
    };
    jumlah?: number;
    data?: RackMonitoringItem[];
}

const props = defineProps<{
    title: string;
    initialData: MonitoringResponse;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Rack Management',
                href: '/admin/racks',
            },
            {
                title: 'Suhu Monitoring',
                href: '/admin/racks/monitoring',
            },
        ],
    },
});

// State
const monitoringData = ref<MonitoringResponse>(props.initialData || { ok: false, data: [] });
const isLoading = ref(false);
const autoRefresh = ref(true);
const refreshIntervalSeconds = 45; // 45 seconds (recommended: API updates every 1.5 - 2 mins, call >= 30s)
const secondsUntilNextRefresh = ref(refreshIntervalSeconds);
let countdownTimer: any = null;
let pollTimer: any = null;

// Search & Filters
const searchQuery = ref('');
const statusFilter = ref<'all' | 'normal' | 'warning' | 'offline'>('all');
const sortBy = ref<'rack' | 'temp' | 'hum' | 'power_total' | 'terakhir'>('rack');
const sortDirection = ref<'asc' | 'desc'>('asc');
const showThresholdModal = ref(false);

// Active Tab / View
const selectedRackModal = ref<RackMonitoringItem | null>(null);

// Fetch fresh data
const fetchData = async () => {
    if (isLoading.value) return;
    isLoading.value = true;
    try {
        const response = await axios.get<MonitoringResponse>('/admin/racks/monitoring/data');
        if (response.data) {
            monitoringData.value = response.data;
        }
    } catch (err: any) {
        console.error('Failed to fetch monitoring data', err);
    } finally {
        isLoading.value = false;
        secondsUntilNextRefresh.value = refreshIntervalSeconds;
    }
};

const toggleAutoRefresh = () => {
    autoRefresh.value = !autoRefresh.value;
    if (autoRefresh.value) {
        secondsUntilNextRefresh.value = refreshIntervalSeconds;
    }
};

// Lifecycle
onMounted(() => {
    countdownTimer = setInterval(() => {
        if (!autoRefresh.value) return;
        if (secondsUntilNextRefresh.value > 1) {
            secondsUntilNextRefresh.value -= 1;
        } else {
            secondsUntilNextRefresh.value = refreshIntervalSeconds;
            fetchData();
        }
    }, 1000);
});

onUnmounted(() => {
    if (countdownTimer) clearInterval(countdownTimer);
    if (pollTimer) clearInterval(pollTimer);
});

// Computed statistics
const totalRacks = computed(() => monitoringData.value.data?.length || 0);

const normalCount = computed(() => {
    return monitoringData.value.data?.filter(
        (r) => r.status?.toLowerCase() === 'normal'
    ).length || 0;
});

const warningCount = computed(() => {
    return monitoringData.value.data?.filter(
        (r) => r.status?.toLowerCase() === 'warning' || r.suhu?.status === 'warning' || r.suhu?.status_hum === 'warning'
    ).length || 0;
});

const offlineCount = computed(() => {
    return monitoringData.value.data?.filter(
        (r) => r.status?.toLowerCase() === 'offline'
    ).length || 0;
});

const avgTemp = computed(() => {
    const list = (monitoringData.value.data || []).filter(
        (r) => r.suhu?.ada && typeof r.suhu?.temp === 'number'
    );
    if (!list.length) return 0;
    const sum = list.reduce((acc, curr) => acc + (curr.suhu?.temp || 0), 0);
    return (sum / list.length).toFixed(1);
});

const avgHumidity = computed(() => {
    const list = (monitoringData.value.data || []).filter(
        (r) => r.suhu?.ada && typeof r.suhu?.hum === 'number'
    );
    if (!list.length) return 0;
    const sum = list.reduce((acc, curr) => acc + (curr.suhu?.hum || 0), 0);
    return (sum / list.length).toFixed(1);
});

// Filtered and Sorted Racks
const filteredRacks = computed(() => {
    let list = [...(monitoringData.value.data || [])];

    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        list = list.filter((item) =>
            item.rack.toLowerCase().includes(query) ||
            item.slave?.toString().includes(query)
        );
    }

    if (statusFilter.value !== 'all') {
        if (statusFilter.value === 'warning') {
            list = list.filter(
                (item) => item.status === 'warning' || item.suhu?.status === 'warning' || item.suhu?.status_hum === 'warning'
            );
        } else {
            list = list.filter((item) => item.status?.toLowerCase() === statusFilter.value);
        }
    }

    list.sort((a, b) => {
        let valA: any = 0;
        let valB: any = 0;

        if (sortBy.value === 'rack') {
            valA = a.rack;
            valB = b.rack;
            return sortDirection.value === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
        } else if (sortBy.value === 'temp') {
            valA = a.suhu?.temp ?? -999;
            valB = b.suhu?.temp ?? -999;
        } else if (sortBy.value === 'hum') {
            valA = a.suhu?.hum ?? -999;
            valB = b.suhu?.hum ?? -999;
        } else if (sortBy.value === 'power_total') {
            valA = (a.pln?.power ?? 0) + (a.ups?.power ?? 0);
            valB = (b.pln?.power ?? 0) + (b.ups?.power ?? 0);
        } else if (sortBy.value === 'terakhir') {
            valA = a.terakhir ? new Date(a.terakhir).getTime() : 0;
            valB = b.terakhir ? new Date(b.terakhir).getTime() : 0;
        }

        if (valA < valB) return sortDirection.value === 'asc' ? -1 : 1;
        if (valA > valB) return sortDirection.value === 'asc' ? 1 : -1;
        return 0;
    });

    return list;
});

// Helpers
const getStatusBadge = (status: string) => {
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

const getTempColor = (temp?: number, status?: string) => {
    if (temp === undefined || temp === null) return 'text-slate-400';
    if (status === 'warning' || temp > 24) return 'text-amber-500 dark:text-amber-400 font-semibold';
    if (temp > 30) return 'text-rose-500 dark:text-rose-400 font-bold';
    return 'text-emerald-600 dark:text-emerald-400 font-semibold';
};

const getHumColor = (hum?: number, status?: string) => {
    if (hum === undefined || hum === null) return 'text-slate-400';
    if (status === 'warning' || hum < 40 || hum > 60) return 'text-amber-500 dark:text-amber-400 font-semibold';
    if (hum < 20 || hum > 80) return 'text-rose-500 dark:text-rose-400 font-bold';
    return 'text-sky-600 dark:text-sky-400 font-semibold';
};

const openRackDetail = (item: RackMonitoringItem) => {
    selectedRackModal.value = item;
};
</script>

<template>
    <Head :title="props.title" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 sm:p-6 lg:p-8">
        <!-- Header Banner with Status & Realtime Auto-refresh -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-tr from-cyan-600 to-blue-600 text-white shadow-lg shadow-cyan-500/20">
                        <Activity class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                            {{ props.title }}
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Realtime Data Center Environmental, Power & Rack Telemetry
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Toolbar -->
            <div class="flex flex-wrap items-center gap-2.5">
                <!-- Ambang Info Button -->
                <button
                    @click="showThresholdModal = true"
                    class="inline-flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-medium text-slate-700 shadow-sm transition-colors hover:bg-slate-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800"
                >
                    <SlidersHorizontal class="h-4 w-4 text-slate-500" />
                    <span>Ambang Batas</span>
                </button>

                <!-- Auto-refresh Toggle -->
                <button
                    @click="toggleAutoRefresh"
                    :class="[
                        'inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-xs font-medium transition-all shadow-sm',
                        autoRefresh
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-400'
                            : 'border-slate-200 bg-white text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400'
                    ]"
                >
                    <Radio :class="['h-3.5 w-3.5', autoRefresh ? 'animate-pulse text-emerald-500' : 'text-slate-400']" />
                    <span>Auto ({{ secondsUntilNextRefresh }}s)</span>
                </button>

                <!-- Refresh Button -->
                <button
                    @click="fetchData"
                    :disabled="isLoading"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-xs font-semibold text-white shadow-md shadow-blue-500/20 transition-all hover:bg-blue-700 active:scale-95 disabled:opacity-50"
                >
                    <RefreshCw :class="['h-3.5 w-3.5', isLoading ? 'animate-spin' : '']" />
                    <span>{{ isLoading ? 'Refreshing...' : 'Refresh' }}</span>
                </button>
            </div>
        </div>

        <!-- Alert if Error or Offline API -->
        <div
            v-if="!monitoringData.ok && monitoringData.error"
            class="flex items-center gap-3 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900/50 dark:bg-rose-950/30 dark:text-rose-300"
        >
            <AlertTriangle class="h-5 w-5 shrink-0 text-rose-600 dark:text-rose-400" />
            <div class="flex-1">
                <p class="font-medium">Gagal memuat telemetry sensor!</p>
                <p class="text-xs text-rose-600/90 dark:text-rose-400/90">{{ monitoringData.error }}</p>
            </div>
            <button
                @click="fetchData"
                class="rounded-lg bg-rose-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-rose-700"
            >
                Coba Lagi
            </button>
        </div>

        <!-- Metric KPI Cards -->
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
            <!-- Total Racks -->
            <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                    <span class="text-xs font-medium uppercase tracking-wider">Total Rack</span>
                    <Server class="h-4 w-4 text-blue-500" />
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ totalRacks }}</span>
                    <span class="text-xs text-slate-400">Unit</span>
                </div>
            </div>

            <!-- Normal Racks -->
            <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                    <span class="text-xs font-medium uppercase tracking-wider">Normal</span>
                    <CheckCircle2 class="h-4 w-4 text-emerald-500" />
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ normalCount }}</span>
                    <span class="text-xs text-emerald-600/70">Aman</span>
                </div>
            </div>

            <!-- Warning Racks -->
            <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                    <span class="text-xs font-medium uppercase tracking-wider">Warning</span>
                    <AlertTriangle class="h-4 w-4 text-amber-500" />
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ warningCount }}</span>
                    <span class="text-xs text-amber-600/70">Waspada</span>
                </div>
            </div>

            <!-- Offline Racks -->
            <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                    <span class="text-xs font-medium uppercase tracking-wider">Offline</span>
                    <XCircle class="h-4 w-4 text-rose-500" />
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ offlineCount }}</span>
                    <span class="text-xs text-rose-600/70">Terputus</span>
                </div>
            </div>

            <!-- Avg Temp -->
            <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                    <span class="text-xs font-medium uppercase tracking-wider">Rata-rata Suhu</span>
                    <Thermometer class="h-4 w-4 text-orange-500" />
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ avgTemp }}</span>
                    <span class="text-xs text-slate-400">°C</span>
                </div>
            </div>

            <!-- Avg Humidity -->
            <div class="relative overflow-hidden rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition-all hover:shadow dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between text-slate-500 dark:text-slate-400">
                    <span class="text-xs font-medium uppercase tracking-wider">Rata-rata Hum</span>
                    <Droplets class="h-4 w-4 text-cyan-500" />
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-slate-900 dark:text-white">{{ avgHumidity }}</span>
                    <span class="text-xs text-slate-400">%</span>
                </div>
            </div>
        </div>

        <!-- Filter Bar & Search -->
        <div class="flex flex-col gap-3 rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-1 flex-wrap items-center gap-3">
                <!-- Search Input -->
                <div class="relative w-full max-w-xs">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari Rack atau Slave ID..."
                        class="w-full rounded-lg border border-slate-200 bg-slate-50/50 py-2 pl-9 pr-3 text-xs text-slate-900 placeholder-slate-400 focus:border-blue-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-800 dark:bg-slate-950 dark:text-white dark:placeholder-slate-500"
                    />
                </div>

                <!-- Status Filter Pills -->
                <div class="flex flex-wrap items-center gap-1 rounded-lg border border-slate-200 p-1 dark:border-slate-800">
                    <button
                        @click="statusFilter = 'all'"
                        :class="[
                            'rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                            statusFilter === 'all'
                                ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                        ]"
                    >
                        Semua ({{ totalRacks }})
                    </button>
                    <button
                        @click="statusFilter = 'normal'"
                        :class="[
                            'rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                            statusFilter === 'normal'
                                ? 'bg-emerald-600 text-white'
                                : 'text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-950/30'
                        ]"
                    >
                        Normal ({{ normalCount }})
                    </button>
                    <button
                        @click="statusFilter = 'warning'"
                        :class="[
                            'rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                            statusFilter === 'warning'
                                ? 'bg-amber-600 text-white'
                                : 'text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-950/30'
                        ]"
                    >
                        Warning ({{ warningCount }})
                    </button>
                    <button
                        @click="statusFilter = 'offline'"
                        :class="[
                            'rounded-md px-2.5 py-1 text-xs font-medium transition-colors',
                            statusFilter === 'offline'
                                ? 'bg-rose-600 text-white'
                                : 'text-rose-700 hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/30'
                        ]"
                    >
                        Offline ({{ offlineCount }})
                    </button>
                </div>
            </div>

            <!-- Sort By Selector -->
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500">Urutkan:</span>
                <select
                    v-model="sortBy"
                    class="rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-xs text-slate-700 focus:border-blue-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                >
                    <option value="rack">Nama Rack</option>
                    <option value="temp">Suhu</option>
                    <option value="hum">Kelembapan</option>
                    <option value="power_total">Total Power</option>
                    <option value="terakhir">Waktu Terakhir</option>
                </select>
                <button
                    @click="sortDirection = sortDirection === 'asc' ? 'desc' : 'asc'"
                    class="rounded-lg border border-slate-200 p-1.5 text-slate-600 hover:bg-slate-50 dark:border-slate-800 dark:text-slate-400 dark:hover:bg-slate-800"
                    :title="sortDirection === 'asc' ? 'Ascending' : 'Descending'"
                >
                    <ArrowUpDown class="h-3.5 w-3.5" />
                </button>
            </div>
        </div>

        <!-- Rack Grid Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div
                v-for="item in filteredRacks"
                :key="item.rack + item.slave"
                @click="openRackDetail(item)"
                class="group relative flex cursor-pointer flex-col justify-between overflow-hidden rounded-xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-blue-400 hover:shadow-md dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-600"
            >
                <!-- Card Header -->
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-base font-bold text-slate-900 group-hover:text-blue-600 dark:text-white dark:group-hover:text-blue-400">
                                {{ item.rack }}
                            </span>
                            <span class="rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-mono font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                Slave #{{ item.slave }}
                            </span>
                        </div>
                        <div class="mt-1 flex items-center gap-1.5 text-[11px] text-slate-400">
                            <Clock class="h-3 w-3" />
                            <span>{{ item.terakhir }}</span>
                        </div>
                    </div>

                    <!-- Status Pill -->
                    <span
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize',
                            getStatusBadge(item.status).bg
                        ]"
                    >
                        <span :class="['h-1.5 w-1.5 rounded-full', getStatusBadge(item.status).dot]"></span>
                        {{ getStatusBadge(item.status).text }}
                    </span>
                </div>

                <!-- Environmental Sensor (Suhu & Kelembapan) -->
                <div class="mt-4 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 dark:border-slate-800/60 dark:bg-slate-950/40">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                        <span class="flex items-center gap-1 font-medium">
                            <Thermometer class="h-3.5 w-3.5 text-orange-500" />
                            Sensor Suhu & Hum
                        </span>
                        <span v-if="!item.suhu?.ada" class="text-[10px] italic text-slate-400">
                            (Belum terpasang)
                        </span>
                    </div>

                    <div class="mt-2.5 grid grid-cols-2 gap-2">
                        <!-- Temp -->
                        <div class="flex items-baseline justify-between rounded-lg bg-white p-2 border border-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                            <span class="text-[11px] text-slate-400">Suhu</span>
                            <span :class="['text-sm', getTempColor(item.suhu?.temp, item.suhu?.status)]">
                                {{ item.suhu?.ada && item.suhu?.temp !== undefined ? item.suhu.temp + ' °C' : 'N/A' }}
                            </span>
                        </div>

                        <!-- Hum -->
                        <div class="flex items-baseline justify-between rounded-lg bg-white p-2 border border-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                            <span class="text-[11px] text-slate-400">Hum</span>
                            <span :class="['text-sm', getHumColor(item.suhu?.hum, item.suhu?.status_hum)]">
                                {{ item.suhu?.ada && item.suhu?.hum !== undefined ? item.suhu.hum + ' %' : 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Power Feeds (PLN & UPS) -->
                <div class="mt-3 grid grid-cols-2 gap-2 text-xs">
                    <!-- PLN Feed -->
                    <div class="rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800/50 dark:bg-slate-950/30">
                        <div class="flex items-center justify-between text-[11px] font-medium text-slate-600 dark:text-slate-300">
                            <span class="flex items-center gap-1">
                                <Zap class="h-3 w-3 text-amber-500" />
                                PLN
                            </span>
                            <span
                                :class="[
                                    'text-[9px] font-bold uppercase',
                                    item.pln?.status === 'offline' ? 'text-rose-500' : 'text-emerald-500'
                                ]"
                            >
                                {{ item.pln?.status || 'N/A' }}
                            </span>
                        </div>
                        <div class="mt-1.5 flex flex-col gap-0.5 text-[11px]">
                            <div class="flex justify-between text-slate-500">
                                <span>Volt:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ item.pln?.voltage ?? '-' }} V</span>
                            </div>
                            <div class="flex justify-between text-slate-500">
                                <span>Power:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ item.pln?.power ?? '-' }} W</span>
                            </div>
                        </div>
                    </div>

                    <!-- UPS Feed -->
                    <div class="rounded-lg border border-slate-100 bg-slate-50/50 p-2.5 dark:border-slate-800/50 dark:bg-slate-950/30">
                        <div class="flex items-center justify-between text-[11px] font-medium text-slate-600 dark:text-slate-300">
                            <span class="flex items-center gap-1">
                                <Zap class="h-3 w-3 text-cyan-500" />
                                UPS
                            </span>
                            <span
                                :class="[
                                    'text-[9px] font-bold uppercase',
                                    item.ups?.status === 'offline' ? 'text-rose-500' : 'text-emerald-500'
                                ]"
                            >
                                {{ item.ups?.status || 'N/A' }}
                            </span>
                        </div>
                        <div class="mt-1.5 flex flex-col gap-0.5 text-[11px]">
                            <div class="flex justify-between text-slate-500">
                                <span>Volt:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ item.ups?.voltage ?? '-' }} V</span>
                            </div>
                            <div class="flex justify-between text-slate-500">
                                <span>Power:</span>
                                <span class="font-medium text-slate-800 dark:text-slate-200">{{ item.ups?.power ?? '-' }} W</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer info / click prompt -->
                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2 text-[11px] text-slate-400 dark:border-slate-800">
                    <span>Klik untuk detail lengkap</span>
                    <span class="font-semibold text-blue-600 group-hover:translate-x-0.5 transition-transform dark:text-blue-400">→</span>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div
            v-if="filteredRacks.length === 0"
            class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white p-12 text-center dark:border-slate-800 dark:bg-slate-900"
        >
            <Server class="h-12 w-12 text-slate-300 dark:text-slate-700" />
            <h3 class="mt-3 text-sm font-semibold text-slate-900 dark:text-white">Tidak ada data rack yang cocok</h3>
            <p class="mt-1 text-xs text-slate-500">Coba ubah kata kunci pencarian atau filter status yang dipilih.</p>
        </div>

        <!-- Threshold Reference Modal -->
        <div
            v-if="showThresholdModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            @click.self="showThresholdModal = false"
        >
            <div class="w-full max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <SlidersHorizontal class="h-5 w-5 text-blue-600" />
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Standar Ambang Batas Sensor (Thresholds)</h3>
                    </div>
                    <button
                        @click="showThresholdModal = false"
                        class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                    >
                        ✕
                    </button>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 dark:border-slate-800">
                                <th class="pb-2 font-medium">Parameter</th>
                                <th class="pb-2 font-medium">Unit</th>
                                <th class="pb-2 font-medium">Normal Min</th>
                                <th class="pb-2 font-medium">Normal Max</th>
                                <th class="pb-2 font-medium">Critical Min</th>
                                <th class="pb-2 font-medium">Critical Max</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="(val, key) in monitoringData.ambang" :key="key" class="text-slate-700 dark:text-slate-300">
                                <td class="py-2.5 font-semibold uppercase">{{ key }}</td>
                                <td class="py-2.5 font-mono">{{ val?.unit ?? '-' }}</td>
                                <td class="py-2.5 text-emerald-600 dark:text-emerald-400">{{ val?.normal_min ?? '-' }}</td>
                                <td class="py-2.5 text-emerald-600 dark:text-emerald-400">{{ val?.normal_max ?? '-' }}</td>
                                <td class="py-2.5 text-rose-600 dark:text-rose-400">{{ val?.critical_min ?? '-' }}</td>
                                <td class="py-2.5 text-rose-600 dark:text-rose-400">{{ val?.critical_max ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 rounded-lg bg-blue-50 p-3 text-[11px] text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                    <div class="flex items-center gap-1.5 font-semibold">
                        <Info class="h-3.5 w-3.5" />
                        Catatan Interval & Sensor
                    </div>
                    <p class="mt-1">
                        Data telemetry diperbarui dari mikrokontroler per rack kira-kira setiap 1.5 - 2 menit.
                        Status offline menandakan sensor pernah mengirim namun saat ini sambungan terputus.
                    </p>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        @click="showThresholdModal = false"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-medium text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

        <!-- Detail Modal for Single Rack -->
        <div
            v-if="selectedRackModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            @click.self="selectedRackModal = null"
        >
            <div class="w-full max-w-xl rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start justify-between border-b border-slate-100 pb-4 dark:border-slate-800">
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-xl font-bold text-slate-900 dark:text-white">
                                Rack {{ selectedRackModal.rack }}
                            </h3>
                            <span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-mono font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                Slave ID: {{ selectedRackModal.slave }}
                            </span>
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Waktu pembaruan terakhir: {{ selectedRackModal.terakhir }}
                        </p>
                    </div>

                    <span
                        :class="[
                            'inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold capitalize',
                            getStatusBadge(selectedRackModal.status).bg
                        ]"
                    >
                        <span :class="['h-2 w-2 rounded-full', getStatusBadge(selectedRackModal.status).dot]"></span>
                        {{ getStatusBadge(selectedRackModal.status).text }}
                    </span>
                </div>

                <!-- Modal Body -->
                <div class="mt-4 space-y-4">
                    <!-- Environmental Section -->
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                <Thermometer class="h-4 w-4 text-orange-500" />
                                Environmental Sensor
                            </span>
                            <span class="text-[11px] text-slate-400">
                                Updated: {{ selectedRackModal.suhu?.created_at || '-' }}
                            </span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-white p-3 border border-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                                <span class="text-xs text-slate-400">Suhu (Temperature)</span>
                                <div class="mt-1 text-xl font-bold" :class="getTempColor(selectedRackModal.suhu?.temp, selectedRackModal.suhu?.status)">
                                    {{ selectedRackModal.suhu?.temp ?? '-' }} °C
                                </div>
                                <span class="text-[10px] uppercase font-semibold text-slate-400">
                                    Status: {{ selectedRackModal.suhu?.status ?? 'N/A' }}
                                </span>
                            </div>

                            <div class="rounded-lg bg-white p-3 border border-slate-200/60 dark:border-slate-800 dark:bg-slate-900">
                                <span class="text-xs text-slate-400">Kelembapan (Humidity)</span>
                                <div class="mt-1 text-xl font-bold" :class="getHumColor(selectedRackModal.suhu?.hum, selectedRackModal.suhu?.status_hum)">
                                    {{ selectedRackModal.suhu?.hum ?? '-' }} %
                                </div>
                                <span class="text-[10px] uppercase font-semibold text-slate-400">
                                    Status: {{ selectedRackModal.suhu?.status_hum ?? 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Power Section: PLN vs UPS -->
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <!-- PLN Card -->
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <Zap class="h-4 w-4 text-amber-500" />
                                    PLN Feed
                                </span>
                                <span :class="['text-[10px] font-bold uppercase', selectedRackModal.pln?.status === 'offline' ? 'text-rose-500' : 'text-emerald-500']">
                                    {{ selectedRackModal.pln?.status || 'N/A' }}
                                </span>
                            </div>
                            <div class="mt-3 space-y-2 text-xs">
                                <div class="flex justify-between border-b border-slate-200/50 pb-1 text-slate-500 dark:border-slate-800">
                                    <span>Tegangan (Voltage):</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ selectedRackModal.pln?.voltage ?? '-' }} V</span>
                                </div>
                                <div class="flex justify-between border-b border-slate-200/50 pb-1 text-slate-500 dark:border-slate-800">
                                    <span>Arus (Current):</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ selectedRackModal.pln?.current ?? '-' }} A</span>
                                </div>
                                <div class="flex justify-between text-slate-500">
                                    <span>Daya (Power):</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ selectedRackModal.pln?.power ?? '-' }} W</span>
                                </div>
                            </div>
                        </div>

                        <!-- UPS Card -->
                        <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <Zap class="h-4 w-4 text-cyan-500" />
                                    UPS Feed
                                </span>
                                <span :class="['text-[10px] font-bold uppercase', selectedRackModal.ups?.status === 'offline' ? 'text-rose-500' : 'text-emerald-500']">
                                    {{ selectedRackModal.ups?.status || 'N/A' }}
                                </span>
                            </div>
                            <div class="mt-3 space-y-2 text-xs">
                                <div class="flex justify-between border-b border-slate-200/50 pb-1 text-slate-500 dark:border-slate-800">
                                    <span>Tegangan (Voltage):</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ selectedRackModal.ups?.voltage ?? '-' }} V</span>
                                </div>
                                <div class="flex justify-between border-b border-slate-200/50 pb-1 text-slate-500 dark:border-slate-800">
                                    <span>Arus (Current):</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ selectedRackModal.ups?.current ?? '-' }} A</span>
                                </div>
                                <div class="flex justify-between text-slate-500">
                                    <span>Daya (Power):</span>
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ selectedRackModal.ups?.power ?? '-' }} W</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button
                        @click="selectedRackModal = null"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-xs font-medium text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
