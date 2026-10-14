<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    rack: any;
    telemetry?: any;
    isLoading?: boolean;
}>();

// UNIT
const usedUnits = computed(() => {
    return props.rack.client_racks_sum_rented_units || 0;
});

const availableUnit = computed(() => {
    return props.rack.total_units - usedUnits.value;
});

const usagePercent = computed(() => {
    return Math.min(
        Math.round((usedUnits.value / props.rack.total_units) * 100),
        100
    );
});

// POWER
const usedPower = computed(() => {
    return props.rack.rack_divices_sum_power_usage || 0;
});

// REALTIME POWER FROM API TELEMETRY
const hasRealtimePower = computed(() => {
    return Boolean(
        props.telemetry &&
        (props.telemetry.pln?.power !== undefined ||
         props.telemetry.ups?.power !== undefined ||
         props.telemetry.pln?.current !== undefined ||
         props.telemetry.ups?.current !== undefined)
    );
});

const realtimePowerWatt = computed(() => {
    if (!props.telemetry) return 0;
    const pln = Number(props.telemetry.pln?.power) || 0;
    const ups = Number(props.telemetry.ups?.power) || 0;
    return Number((pln + ups).toFixed(1));
});

const realtimeCurrentAmpere = computed(() => {
    if (!props.telemetry) return 0;
    const pln = Number(props.telemetry.pln?.current) || 0;
    const ups = Number(props.telemetry.ups?.current) || 0;
    return Number((pln + ups).toFixed(2));
});

const plnPower = computed(() => {
    return props.telemetry?.pln?.power !== undefined ? Number(props.telemetry.pln.power) : 0;
});

const plnCurrent = computed(() => {
    return props.telemetry?.pln?.current !== undefined ? Number(props.telemetry.pln.current) : 0;
});

const upsPower = computed(() => {
    return props.telemetry?.ups?.power !== undefined ? Number(props.telemetry.ups.power) : 0;
});

const upsCurrent = computed(() => {
    return props.telemetry?.ups?.current !== undefined ? Number(props.telemetry.ups.current) : 0;
});

const powerPercent = computed(() => {
    if (!props.rack.power_capacity) return 0;
    // Jika kapasitas di database berupa Watt (biasanya > 50) atau Ampere (<= 50)
    const currentVal = props.rack.power_capacity > 50 ? realtimePowerWatt.value : realtimeCurrentAmpere.value;
    return Math.min(
        Math.round((currentVal / props.rack.power_capacity) * 100),
        100
    );
});

// WEIGHT
const usedWeight = computed(() => {
    return props.rack.rack_divices_sum_weight_usage || 0;
});

const weightPercent = computed(() => {
    return Math.min(
        Math.round((usedWeight.value / props.rack.weight_capacity) * 100),
        100
    );
});
</script>

<template>
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 p-4">
        <!-- LEFT CONTENT -->
        <div
            class="lg:col-span-2 rounded-3xl bg-gray-900 p-6 text-white shadow-lg"
        >
            <!-- HEADER -->
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium opacity-90">
                        Total Units
                    </p>

                    <h1 class="text-xl font-bold">
                        {{ props.rack.total_units }} - U
                    </h1>
                </div>

                <div
                    class="rounded-full bg-white px-4 py-2 text-xs font-bold text-slate-800"
                >
                    {{ usagePercent }}%
                </div>
            </div>

            <!-- DESCRIPTION -->
            <p class="mt-3 text-sm">
                Used {{ props.rack.client_racks_sum_rented_units || 0 }} U from
                {{ props.rack.total_units }} U
            </p>

            <!-- PROGRESS -->
            <div class="mt-2">
                <div
                    class="h-5 w-full overflow-hidden rounded-full bg-white/70"
                >
                    <div
                        class="h-full rounded-full bg-red-500 transition-all duration-500"
                        :style="{ width: `${usagePercent}%` }"
                    />
                </div>
            </div>

            <!-- INFO BOX -->
            <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                <div
                    class="rounded-2xl bg-white p-6 text-slate-900 shadow"
                >
                    <p class="text-sm font-medium">
                        Unit Tersedia
                    </p>

                    <h2 class="mt-3 text-sm font-bold">
                        {{ availableUnit }} - U
                    </h2>
                </div>

                <div
                    class="rounded-2xl bg-white p-6 text-slate-900 shadow"
                >
                    <p class="text-sm font-medium">
                        Unit Terpakai
                    </p>

                    <h2 class="mt-3 text-sm font-bold">
                        {{ props.rack.client_racks_sum_rented_units || 0 }} - U
                    </h2>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="mt-4">
                <h3 class="text-sm font-semibold tracking-wide">
                    {{ props.rack.name || 'No description available' }} - {{ props.rack?.room?.name }}
                </h3>
                <span class="text-xs">{{ props.rack?.room?.location_data_center?.address }}</span>
            </div>
        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="flex flex-col gap-4">
            <!-- OWNERS -->
            <div
                class="rounded-3xl bg-gray-900 p-6 text-white shadow-lg"
            >
                <p class="text-sm font-medium opacity-90">
                    Owned by
                </p>

                <h2 class="text-xl font-bold">
                    {{ props.rack.client_racks_count }} owners
                </h2>
            </div>

            <!-- POWER -->
            <div
                class="rounded-3xl bg-gray-900 p-6 text-white shadow-lg flex flex-col justify-between"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium opacity-90 flex items-center gap-2">
                        <span>Power Capacity</span>
                    </p>

                    <span
                        v-if="hasRealtimePower"
                        class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500/20 px-2.5 py-0.5 text-xs font-semibold text-emerald-400 border border-emerald-500/30"
                    >
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        Realtime API
                    </span>
                    <span
                        v-else
                        class="rounded-full bg-slate-800 px-2.5 py-0.5 text-xs text-slate-400 border border-slate-700"
                    >
                        Database
                    </span>
                </div>

                <!-- REALTIME API VALUE -->
                <div v-if="hasRealtimePower" class="mt-2">
                    <div class="flex items-baseline gap-2">
                        <h2 class="text-2xl font-bold tracking-tight text-white">
                            {{ realtimePowerWatt }}
                        </h2>
                        <span class="text-sm font-semibold text-amber-400">Watt</span>
                        <span class="text-xs text-slate-500">|</span>
                        <span class="text-base font-bold text-cyan-400">{{ realtimeCurrentAmpere }} A</span>
                    </div>

                    <div class="mt-2.5 flex items-center justify-between text-[11px] text-slate-300 border-t border-white/10 pt-2">
                        <span class="flex items-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-400"></span>
                            PLN: {{ plnPower }} W ({{ plnCurrent }} A)
                        </span>
                        <span class="flex items-center gap-1">
                            <span class="h-1.5 w-1.5 rounded-full bg-cyan-400"></span>
                            UPS: {{ upsPower }} W ({{ upsCurrent }} A)
                        </span>
                    </div>
                </div>

                <!-- FALLBACK DATABASE VALUE -->
                <div v-else class="mt-2">
                    <h2 class="text-xl font-bold">
                        {{ props.rack.power_capacity }} -
                        Ampere (A)
                    </h2>
                    <p v-if="props.rack.api_suhu" class="mt-1 text-xs text-slate-400 flex items-center gap-1">
                        <span class="h-1.5 w-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                        Menghubungkan ke API...
                    </p>
                    <p v-else class="mt-1 text-xs text-slate-400">
                        Belum terhubung ke API sensor
                    </p>
                </div>
            </div>

            <!-- WEIGHT -->
            <div
                class="rounded-3xl bg-gray-900 p-6 text-white shadow-lg"
            >
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium opacity-90">
                        Weight Limit
                    </p>

                    <span class="text-sm font-semibold">
                        {{ weightPercent }}%
                    </span>
                </div>

                <h2 class="text-xl font-bold leading-tight">
                    {{ props.rack.weight_capacity }} / {{ usedWeight }}
                    Kilograms (kg)
                </h2>

                <!-- WEIGHT PROGRESS -->
                <div class="mt-3">
                    <div
                        class="h-4 w-full overflow-hidden rounded-full bg-white/70"
                    >
                        <div
                            class="h-full rounded-full bg-yellow-400 transition-all duration-500"
                            :style="{ width: `${weightPercent}%` }"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>