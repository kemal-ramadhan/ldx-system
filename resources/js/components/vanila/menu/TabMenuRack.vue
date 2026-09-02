<template>
  <div class="rounded-2xl overflow-hidden">

    <!-- Tab Bar -->
    <div class="flex items-center gap-1 bg-gray-50 rounded-xl m-3 p-1.5">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        :class="[
          'px-4 py-2 rounded-lg text-sm transition-all whitespace-nowrap',
          activeTab === tab.key
            ? 'bg-gray-900 text-white font-medium dark:bg-gray-700'
            : 'text-gray-500 hover:bg-white hover:text-gray-800 dark:text-gray-900 dark:hover:bg-gray-700 dark:hover:text-white'
        ]"
      >
        {{ tab.label }}
        <span
          v-if="tab.badge"
          :class="[
            'ml-1 text-xs rounded-full px-2 py-0.5',
            activeTab === tab.key
              ? 'bg-white/25 text-white'
              : 'bg-gray-200 text-gray-500'
          ]"
        >
          {{ tab.badge }}
        </span>
      </button>
    </div>

    <!-- Content -->
    <div class="px-5 pb-5 pt-1">
      <component :is="currentPanel" :clients="props.clients" :rack="props.rack" />
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

import ClientRack from './tabs/ClientRack.vue'
import DiviceRack from './tabs/DiviceRack.vue'

const props = defineProps({
  rack: Object,
  clients: Object
})

const activeTab = ref('client')

const tabs = [
  { key: 'client',   label: 'Client',  badge: props.rack.client_racks_count || 0 },
  { key: 'device',   label: 'Device',  badge: props.rack.rack_divices_count || 0 },
]

const panels = {
  client:   ClientRack,
  device:   DiviceRack,
}

const currentPanel = computed(() => panels[activeTab.value])

</script>