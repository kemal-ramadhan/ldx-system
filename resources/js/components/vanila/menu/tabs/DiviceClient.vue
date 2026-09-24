<script setup lang="ts">
defineProps<{
    devices: any[]
}>()

const badgeClass = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-700'
        case 'inactive':
            return 'bg-red-100 text-red-700'
        default:
            return 'bg-gray-100 text-gray-700'
    }
}
</script>

<template>
    <div class="overflow-hidden rounded-xl border dark:border-gray-700">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800 dark:text-gray-100">
                <tr>
                    <th class="px-4 py-3 text-left">Code</th>
                    <th class="px-4 py-3 text-left">Device</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Rack</th>
                    <th class="px-4 py-3 text-left">Unit</th>
                    <th class="px-4 py-3 text-left">Power</th>
                    <th class="px-4 py-3 text-left">IP Address</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>

            <tbody>
                <tr
                    v-for="device in devices"
                    :key="device.id"
                    class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50"
                >
                    <td class="px-4 py-3 font-medium dark:text-gray-100">
                        {{ device.code }}
                    </td>

                    <td class="px-4 py-3">
                        <div>
                            <div class="font-medium dark:text-gray-100">
                                {{ device.divice_name }}
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ device.model }}
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-3 dark:text-gray-300">
                        {{ device.divice_type }}
                    </td>

                    <td class="px-4 py-3">
                        <div>
                            <div class="font-medium dark:text-gray-100">
                                {{ device.rack?.name }}
                            </div>

                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                {{ device.rack?.code }}
                            </div>
                        </div>
                    </td>

                    <td class="px-4 py-3 dark:text-gray-300">
                        {{ device.total_unit }} U
                    </td>

                    <td class="px-4 py-3 dark:text-gray-300">
                        {{ device.power_usage }} W
                    </td>

                    <td class="px-4 py-3 dark:text-gray-300">
                        {{ device.ip_address || '-' }}
                    </td>

                    <td class="px-4 py-3">
                        <span
                            :class="[
                                'rounded-full px-2 py-1 text-xs font-medium',
                                badgeClass(device.status)
                            ]"
                        >
                            {{ device.status }}
                        </span>
                    </td>
                </tr>

                <tr v-if="!devices.length">
                    <td
                        colspan="8"
                        class="py-10 text-center text-gray-500 dark:text-gray-400"
                    >
                        No devices found
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>