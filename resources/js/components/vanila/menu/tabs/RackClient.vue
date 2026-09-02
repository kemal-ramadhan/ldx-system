<script setup lang="ts">
const props = defineProps<{
    racks: any[]
}>()

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value)
}

const statusColor = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-100 text-green-700 border-green-200'
        case 'expired':
            return 'bg-red-100 text-red-700 border-red-200'
        default:
            return 'bg-gray-100 text-gray-700 border-gray-200'
    }
}
</script>

<template>
    <div>
        <div v-if="!racks.length" class="text-center py-12">
            <div class="text-5xl mb-3">🗄️</div>
            <h3 class="font-semibold text-lg">No Rack Assigned</h3>
            <p class="">
                This client does not have any rented rack yet.
            </p>
        </div>

        <div v-else class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            <div v-for="rack in racks" :key="rack.id"
                class="rounded-2xl border bg-white dark:bg-gray-900 overflow-hidden hover:shadow-lg transition-all">
                <!-- Header -->
                <div class="bg-gray-900 dark:bg-white dark:text-gray-900 p-4 text-white">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-semibold text-lg">
                                {{ rack.rack.name }}
                            </h3>

                            <p class="text-sm">
                                {{ rack.rack.code }}
                            </p>
                        </div>

                        <span class="px-2 py-1 rounded-full text-xs border bg-white/20">
                            {{ rack.status }}
                        </span>
                    </div>
                </div>

                <!-- Content -->
                <div class="p-4 space-y-4">

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-xs ">
                                Rented
                            </p>
                            <p class="font-bold">
                                {{ rack.rented_units }} U
                            </p>
                        </div>

                        <div>
                            <p class="text-xs ">
                                Used
                            </p>
                            <p class="font-bold">
                                {{ rack.used_units }} U
                            </p>
                        </div>

                        <div>
                            <p class="text-xs ">
                                Available
                            </p>
                            <p class="font-bold">
                                {{ rack.available_units }} U
                            </p>
                        </div>
                    </div>

                    <!-- Progress -->
                    <div class="mt-3">
                        <div class="flex justify-between text-xs mb-1">
                            <span>Usage</span>
                            <span>
                                {{
                                    rack.rented_units > 0
                                        ? Math.round(
                                            (rack.used_units /
                                                rack.rented_units) *
                                            100
                                )
                                : 0
                                }}%
                            </span>
                        </div>

                        <div class="h-2 rounded-full bg-gray-200">
                            <div class="h-2 rounded-full bg-blue-600" :style="{
                                width:
                                    (rack.used_units /
                                        rack.rented_units) *
                                    100 +
                                    '%'
                            }" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs ">
                                Power Capacity
                            </p>

                            <p class="font-semibold">
                                {{ rack.rack.power_capacity }}
                                W
                            </p>
                        </div>

                        <div>
                            <p class="text-xs ">
                                Weight Capacity
                            </p>

                            <p class="font-semibold">
                                {{ rack.rack.weight_capacity }}
                                Kg
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs ">
                            Monthly Fee
                        </p>

                        <p class="text-lg font-bold">
                            {{ formatCurrency(rack.monthly_fee) }}
                        </p>
                    </div>

                    <div class="border-t pt-3">
                        <div class="flex justify-between text-sm">
                            <span class="">
                                Start Date
                            </span>

                            <span>
                                {{ rack.rental_start_date }}
                            </span>
                        </div>

                        <div class="flex justify-between text-sm mt-1">
                            <span class="">
                                End Date
                            </span>

                            <span>
                                {{ rack.rental_end_date }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>