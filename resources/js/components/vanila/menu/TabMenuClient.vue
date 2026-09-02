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
      <component :is="currentPanel" :client="props.client" :users="props.users" :racks="props.racks" :devices="props.devices" />
    </div>

  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

import MemberClient from './tabs/MemberClient.vue'
import RackClient from './tabs/RackClient.vue'
import DiviceClient from './tabs/DiviceClient.vue'

const props = defineProps({
  client: Object,
  users: Array,
  racks: Array,
  devices: Array
})

const activeTab = ref('member')

const tabs = [
  { key: 'member',   label: 'Member',  badge: props.client?.pics?.length || 0 },
  { key: 'rack',   label: 'Rack',  badge: props.racks?.length || 0 },
  { key: 'divice',   label: 'Device',  badge: props.devices?.length || 0 },
]

const panels = {
  member:   MemberClient,
  rack:   RackClient,
  divice:   DiviceClient,
}

const currentPanel = computed(() => panels[activeTab.value])

</script>