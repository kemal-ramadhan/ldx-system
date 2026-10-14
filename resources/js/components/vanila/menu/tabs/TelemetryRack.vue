<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Activity,
    Thermometer,
    Droplets,
    Zap,
    RefreshCw,
    Radio,
    Clock,
    Pencil,
} from 'lucide-vue-next';
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

const props = defineProps<{
    rack: any;
    telemetry?: SingleRackData | null;
    telemetryResponse?: any;
    isLoading?: boolean;
    autoRefresh?: boolean;
    secondsUntilNext?: number;
    selectedTimeFilter?: 'session' | 'today' | '3days' | '7days';
    trendChartData?: any;
    trendChartOptions?: any;
}>();

const emit = defineEmits<{
    (e: 'refresh'): void;
    (e: 'toggleAutoRefresh'): void;
    (e: 'changeTimeFilter', filter: 'session' | 'today' | '3days' | '7days'): void;
}>();

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
        case 'critical':
            return {
                bg: 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                dot: 'bg-rose-500 animate-pulse',
                text: 'Critical',
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

const formatSeconds = (sec: number = 0) => {
    const m = Math.floor(sec / 60);
    const s = sec % 60;
    return `${m}:${s < 10 ? '0' : ''}${s}`;
};
</script>

<template>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 space-y-6">
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
                            v-if="props.telemetry"
                            :class="[
                                'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize',
                                getStatusBadge(props.telemetry.status).bg
                            ]"
                        >
                            <span :class="['h-1.5 w-1.5 rounded-full', getStatusBadge(props.telemetry.status).dot]"></span>
                            {{ getStatusBadge(props.telemetry.status).text }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        Data mikrokontroler aktual dari endpoint <span class="font-mono text-cyan-700 dark:text-cyan-400">/api/data.php?rack={{ props.rack.api_suhu || '-' }}</span>
                    </p>
                </div>
            </div>

            <!-- Action Controls (Auto-refresh & Refresh Button) -->
            <div v-if="props.rack.api_suhu" class="flex flex-wrap items-center gap-2.5">
                <span v-if="props.telemetry?.terakhir" class="text-xs font-mono text-slate-400 flex items-center gap-1">
                    <Clock class="h-3.5 w-3.5" />
                    {{ props.telemetry.terakhir }}
                </span>

                <button
                    type="button"
                    @click="emit('toggleAutoRefresh')"
                    :class="[
                        'inline-flex items-center gap-2 rounded-lg border px-3 py-1.5 text-xs font-medium transition-all shadow-sm cursor-pointer',
                        props.autoRefresh
                            ? 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-400'
                            : 'border-slate-200 bg-white text-slate-600 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-400'
                    ]"
                >
                    <Radio :class="['h-3.5 w-3.5', props.autoRefresh ? 'animate-pulse text-emerald-500' : 'text-slate-400']" />
                    <span>Auto-refresh ({{ formatSeconds(props.secondsUntilNext ?? 0) }})</span>
                </button>

                <button
                    type="button"
                    @click="emit('refresh')"
                    :disabled="props.isLoading"
                    class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-3.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 disabled:opacity-50 transition-colors cursor-pointer"
                >
                    <RefreshCw :class="['h-3.5 w-3.5', props.isLoading ? 'animate-spin' : '']" />
                    <span>{{ props.isLoading ? 'Refreshing...' : 'Refresh' }}</span>
                </button>
            </div>

            <div v-else class="flex items-center gap-2">
                <Link
                    :href="`/admin/racks/${props.rack.id}/edit`"
                    class="inline-flex items-center gap-2 rounded-lg bg-cyan-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-cyan-700 transition-colors shadow-sm"
                >
                    <Activity class="h-3.5 w-3.5" />
                    Hubungkan ke API Suhu
                </Link>
            </div>
        </div>

        <!-- JIKA RACK BELUM TERHUBUNG KE API SUHU -->
        <div
            v-if="!props.rack.api_suhu"
            class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-8 text-center dark:border-slate-800 dark:bg-slate-950/30"
        >
            <Thermometer class="h-10 w-10 text-slate-300 dark:text-slate-700 mb-2" />
            <h3 class="text-sm font-semibold text-slate-800 dark:text-slate-200">
                Rack ini belum dikoneksikan ke API Suhu Monitoring
            </h3>
            <p class="mt-1 text-xs text-slate-500 max-w-md">
                Anda dapat menghubungkan rack ini dengan ID sensor telemetri melalui halaman edit rack dengan menekan tombol "Connect To API Suhu".
            </p>
            <Link
                :href="`/admin/racks/${props.rack.id}/edit`"
                class="mt-4 inline-flex items-center gap-2 rounded-lg bg-slate-900 px-4 py-2 text-xs font-semibold text-white hover:bg-slate-800 dark:bg-slate-100 dark:text-slate-900"
            >
                <Pencil class="h-3.5 w-3.5" />
                Buka Edit Rack untuk Hubungkan
            </Link>
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
                                (props.telemetry?.suhu?.temp ?? 0) > 24 ? 'text-amber-500' : 'text-emerald-600 dark:text-emerald-400'
                            ]"
                        >
                            {{ props.telemetry?.suhu?.ada && props.telemetry.suhu.temp !== undefined ? props.telemetry.suhu.temp : '-' }}
                        </span>
                        <span class="text-xs text-slate-400">°C</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[10px] text-slate-400">
                        <span>Status: {{ props.telemetry?.suhu?.status || 'N/A' }}</span>
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
                                (props.telemetry?.suhu?.hum ?? 0) < 40 || (props.telemetry?.suhu?.hum ?? 0) > 60
                                    ? 'text-amber-500'
                                    : 'text-sky-600 dark:text-sky-400'
                            ]"
                        >
                            {{ props.telemetry?.suhu?.ada && props.telemetry.suhu.hum !== undefined ? props.telemetry.suhu.hum : '-' }}
                        </span>
                        <span class="text-xs text-slate-400">%</span>
                    </div>
                    <div class="mt-1 flex items-center justify-between text-[10px] text-slate-400">
                        <span>Status: {{ props.telemetry?.suhu?.status_hum || 'N/A' }}</span>
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
                            {{ props.telemetry?.pln?.voltage ?? '-' }}
                        </span>
                        <span class="text-xs text-slate-400">V</span>
                    </div>
                    <div class="mt-1 text-[10px] text-slate-400">
                        Arus: {{ props.telemetry?.pln?.current ?? '-' }} A
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
                            {{ props.telemetry?.pln?.power ?? '-' }}
                        </span>
                        <span class="text-xs text-slate-400">W</span>
                    </div>
                    <div class="mt-1 text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold uppercase">
                        {{ props.telemetry?.pln?.status || 'N/A' }}
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
                            {{ props.telemetry?.ups?.voltage ?? '-' }}
                        </span>
                        <span class="text-xs text-slate-400">V</span>
                    </div>
                    <div class="mt-1 text-[10px] text-slate-400">
                        Arus: {{ props.telemetry?.ups?.current ?? '-' }} A
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
                            {{ props.telemetry?.ups?.power ?? '-' }}
                        </span>
                        <span class="text-xs text-slate-400">W</span>
                    </div>
                    <div class="mt-1 text-[10px] font-semibold uppercase" :class="props.telemetry?.ups?.status === 'offline' ? 'text-rose-500' : 'text-emerald-500'">
                        {{ props.telemetry?.ups?.status || 'N/A' }}
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
                                    @click="emit('changeTimeFilter', 'session')"
                                    :class="[
                                        'rounded px-2 py-0.5 transition-colors cursor-pointer',
                                        props.selectedTimeFilter === 'session'
                                            ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                    ]"
                                >
                                    Live
                                </button>
                                <button
                                    type="button"
                                    @click="emit('changeTimeFilter', 'today')"
                                    :class="[
                                        'rounded px-2 py-0.5 transition-colors cursor-pointer',
                                        props.selectedTimeFilter === 'today'
                                            ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                    ]"
                                >
                                    24 Jam
                                </button>
                                <button
                                    type="button"
                                    @click="emit('changeTimeFilter', '3days')"
                                    :class="[
                                        'rounded px-2 py-0.5 transition-colors cursor-pointer',
                                        props.selectedTimeFilter === '3days'
                                            ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                    ]"
                                >
                                    3 Hari
                                </button>
                                <button
                                    type="button"
                                    @click="emit('changeTimeFilter', '7days')"
                                    :class="[
                                        'rounded px-2 py-0.5 transition-colors cursor-pointer',
                                        props.selectedTimeFilter === '7days'
                                            ? 'bg-slate-900 text-white dark:bg-slate-100 dark:text-slate-900'
                                            : 'text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white'
                                    ]"
                                >
                                    7 Hari
                                </button>
                            </div>

                            <span class="rounded bg-slate-100 px-2 py-1 text-[11px] font-mono text-slate-600 dark:bg-slate-800 dark:text-slate-400">
                                Slave #{{ props.telemetry?.slave ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="h-64 w-full">
                        <Line v-if="props.trendChartData" :data="props.trendChartData" :options="props.trendChartOptions" />
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
                                    <span :class="['text-[10px] font-bold uppercase', props.telemetry?.pln?.status === 'offline' ? 'text-rose-500' : 'text-emerald-500']">
                                        {{ props.telemetry?.pln?.status || 'N/A' }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center pt-1 border-t border-slate-100 dark:border-slate-800">
                                    <div>
                                        <span class="text-[10px] text-slate-400">Volt</span>
                                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ props.telemetry?.pln?.voltage ?? '-' }} V</p>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400">Arus</span>
                                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ props.telemetry?.pln?.current ?? '-' }} A</p>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400">Daya</span>
                                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ props.telemetry?.pln?.power ?? '-' }} W</p>
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
                                    <span :class="['text-[10px] font-bold uppercase', props.telemetry?.ups?.status === 'offline' ? 'text-rose-500' : 'text-emerald-500']">
                                        {{ props.telemetry?.ups?.status || 'N/A' }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center pt-1 border-t border-slate-100 dark:border-slate-800">
                                    <div>
                                        <span class="text-[10px] text-slate-400">Volt</span>
                                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ props.telemetry?.ups?.voltage ?? '-' }} V</p>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400">Arus</span>
                                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ props.telemetry?.ups?.current ?? '-' }} A</p>
                                    </div>
                                    <div>
                                        <span class="text-[10px] text-slate-400">Daya</span>
                                        <p class="font-bold text-slate-800 dark:text-slate-200">{{ props.telemetry?.ups?.power ?? '-' }} W</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                        <span>Total Daya (PLN + UPS):</span>
                        <span class="font-bold text-slate-900 dark:text-white">
                            {{ ((props.telemetry?.pln?.power ?? 0) + (props.telemetry?.ups?.power ?? 0)).toFixed(1) }} Watt
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
