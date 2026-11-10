<script setup lang="ts">
import { formatNumber as formatMoney } from '@/lib/money';
import { computed } from 'vue';

import { Input } from '@/components/ui/input';
import { pph23Of, ppnOf } from '@/lib/tax';

/**
 * PPN / PPh 23 toggles for one service item.
 * Off by default; turning on fills the default rate (11% / 2%),
 * which can be changed per item.
 */
const props = defineProps<{
    subtotal: number;
}>();

const ppnEnabled = defineModel<boolean>('ppnEnabled', { required: true });
const ppnPercentage = defineModel<number>('ppnPercentage', { required: true });
const pph23Enabled = defineModel<boolean>('pph23Enabled', { required: true });
const pph23Percentage = defineModel<number>('pph23Percentage', { required: true });

const ppnAmount = computed(() =>
    ppnOf(Number(props.subtotal), {
        ppn_enabled: ppnEnabled.value,
        ppn_percentage: ppnPercentage.value,
        pph23_enabled: false,
        pph23_percentage: 0,
    }),
);

const pph23Amount = computed(() =>
    pph23Of(Number(props.subtotal), {
        ppn_enabled: false,
        ppn_percentage: 0,
        pph23_enabled: pph23Enabled.value,
        pph23_percentage: pph23Percentage.value,
    }),
);

const toggleClass = (on: boolean) => [
    'relative inline-flex h-5 w-9 shrink-0 items-center rounded-full transition-colors',
    on ? 'bg-primary' : 'bg-gray-300 dark:bg-gray-700',
];

const knobClass = (on: boolean) => [
    'inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform',
    on ? 'translate-x-4' : 'translate-x-0.5',
];
</script>

<template>
    <div class="grid grid-cols-1 gap-3 border-t pt-3 md:grid-cols-2">

        <!-- PPN -->
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" :class="toggleClass(ppnEnabled)" @click="ppnEnabled = !ppnEnabled"
                :aria-pressed="ppnEnabled" aria-label="Toggle PPN">
                <span :class="knobClass(ppnEnabled)" />
            </button>

            <span class="text-sm font-medium">PPN</span>

            <template v-if="ppnEnabled">
                <div class="relative w-24">
                    <Input v-model="ppnPercentage" type="number" min="0" max="100" step="0.01" class="h-8 pr-7" />
                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">%</span>
                </div>

                <span class="text-sm text-muted-foreground">
                    + Rp {{ formatMoney(ppnAmount) }}
                </span>
            </template>

            <span v-else class="text-xs text-muted-foreground">Tidak kena PPN</span>
        </div>

        <!-- PPh 23 -->
        <div class="flex flex-wrap items-center gap-3">
            <button type="button" :class="toggleClass(pph23Enabled)" @click="pph23Enabled = !pph23Enabled"
                :aria-pressed="pph23Enabled" aria-label="Toggle PPh 23">
                <span :class="knobClass(pph23Enabled)" />
            </button>

            <span class="text-sm font-medium">PPh 23</span>

            <template v-if="pph23Enabled">
                <div class="relative w-24">
                    <Input v-model="pph23Percentage" type="number" min="0" max="100" step="0.01" class="h-8 pr-7" />
                    <span class="absolute right-2 top-1/2 -translate-y-1/2 text-xs text-muted-foreground">%</span>
                </div>

                <span class="text-sm text-muted-foreground">
                    - Rp {{ formatMoney(pph23Amount) }}
                </span>
            </template>

            <span v-else class="text-xs text-muted-foreground">Tidak kena PPh 23</span>
        </div>
    </div>
</template>
