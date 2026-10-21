<template>
  <div class="rounded-2xl overflow-hidden">

    <!-- Tab Bar -->
    <div class="flex items-center gap-1 bg-gray-50 dark:bg-gray-900 rounded-xl m-3 p-1.5 overflow-x-auto">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        :class="[
          'px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap cursor-pointer',
          activeTab === tab.key
            ? 'bg-gray-900 text-white font-medium dark:bg-gray-700 shadow-sm'
            : 'text-gray-500 hover:bg-white hover:text-gray-800 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white'
        ]"
      >
        {{ tab.label }}
        <span
          v-if="tab.badge"
          :class="[
            'ml-1 text-xs rounded-full px-2 py-0.5',
            activeTab === tab.key
              ? 'bg-white/25 text-white'
              : 'bg-gray-200 text-gray-500 dark:bg-gray-800 dark:text-gray-400'
          ]"
        >
          {{ tab.badge }}
        </span>
      </button>
    </div>

    <!-- Content -->
    <div class="px-5 pb-5 pt-1">
      <component
        :is="currentPanel"
        :clients="props.clients"
        :rack="props.rack"
        :invoices="props.invoices"
        :interconnections="props.interconnections"
        :telemetry="props.telemetry"
        :telemetry-response="props.telemetryResponse"
        :is-loading="props.isLoading"
        :auto-refresh="props.autoRefresh"
        :seconds-until-next="props.secondsUntilNext"
        :selected-time-filter="props.selectedTimeFilter"
        :trend-chart-data="props.trendChartData"
        :trend-chart-options="props.trendChartOptions"
        @refresh="emit('refresh')"
        @toggle-auto-refresh="emit('toggleAutoRefresh')"
        @change-time-filter="(val: any) => emit('changeTimeFilter', val)"
      />
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

import ClientRack from './tabs/ClientRack.vue'
import DiviceRack from './tabs/DiviceRack.vue'
import InvoiceClient from './tabs/InvoiceClient.vue'
import InterconnectionClient from './tabs/InterconnectionClient.vue'
import TelemetryRack from './tabs/TelemetryRack.vue'
import ImageRack from './tabs/ImageRack.vue'

const props = defineProps<{
  rack: any;
  clients?: any;
  invoices?: any;
  interconnections?: any[];
  telemetry?: any;
  telemetryResponse?: any;
  isLoading?: boolean;
  autoRefresh?: boolean;
  secondsUntilNext?: number;
  selectedTimeFilter?: string;
  trendChartData?: any;
  trendChartOptions?: any;
}>()

const emit = defineEmits<{
  (e: 'refresh'): void;
  (e: 'toggleAutoRefresh'): void;
  (e: 'changeTimeFilter', filter: any): void;
}>()

const activeTab = ref('client')

const tabs = computed(() => [
  { key: 'client',   label: 'Client',  badge: props.rack?.client_racks_count || 0 },
  { key: 'device',   label: 'Device',  badge: props.rack?.rack_divices_count || 0 },
  { key: 'invoice',  label: 'Invoice', badge: props.invoices?.total || 0 },
  { key: 'interconnection',  label: 'Crossconnect', badge: props.interconnections?.length || 0 },
  { key: 'images', label: 'Gambar', badge: props.rack?.images_count ?? props.rack?.images?.length ?? 0 },
  {
    key: 'telemetry',
    label: 'Sensor Suhu & Telemetri',
    badge: props.telemetry ? (props.telemetry.status?.toUpperCase() || 'LIVE') : (props.rack?.api_suhu ? 'LIVE' : null)
  },
])

const panels: Record<string, any> = {
  client:   ClientRack,
  device:   DiviceRack,
  invoice:  InvoiceClient,
  interconnection: InterconnectionClient,
  telemetry: TelemetryRack,
  images: ImageRack,
}

const currentPanel = computed(() => panels[activeTab.value])
</script>