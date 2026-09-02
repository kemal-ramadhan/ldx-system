<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    rack: any;
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

const powerPercent = computed(() => {
    return Math.min(
        Math.round((usedPower.value / props.rack.power_capacity) * 100),
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
                class="rounded-3xl bg-gray-900 p-6 text-white shadow-lg"
            >
                <p class="text-sm font-medium opacity-90">
                    Power Capacity
                </p>

                <h2 class="text-xl font-bold">
                    {{ props.rack.power_capacity }} -
                    Ampere (A)
                </h2>
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