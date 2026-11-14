<script setup lang="ts">
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { ArrowRight, Paperclip, Search } from 'lucide-vue-next'
import {
    type Movement,
    TYPE_LABELS,
    formatDateTime,
    position,
    typeClass,
} from '@/lib/deviceMovement'

/**
 * Rack detail tab: keluar-masuk device history of this rack (admin only).
 */
const props = defineProps<{
    rack: any
    movements?: Movement[]
}>()

const search = ref('')
const type = ref<string>('all')

const filtered = computed(() =>
    (props.movements ?? []).filter((m) => {
        if (type.value !== 'all' && m.type !== type.value) return false
        if (!search.value) return true

        const q = search.value.toLowerCase()
        return [m.movement_number, m.device_name, m.device_code, m.serial_number, m.carrier_name, m.client?.company_name]
            .some((v) => v?.toLowerCase().includes(q))
    }),
)
</script>

<template>
    <div class="p-4">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-semibold">Riwayat Keluar-Masuk</h3>
                <p class="text-xs text-muted-foreground">50 pergerakan terakhir yang melibatkan rack ini.</p>
            </div>
            <Link :href="`/admin/device-movements?rack_id=${rack.id}`" class="text-sm font-medium text-primary hover:underline">
                Lihat semua →
            </Link>
        </div>

        <div class="mb-4 flex flex-col gap-2 sm:flex-row">
            <div class="relative flex-1">
                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                <input v-model="search" type="text" placeholder="Cari device, serial, pembawa..."
                    class="w-full rounded-xl border border-input bg-background py-2.5 pl-9 pr-3 text-sm" />
            </div>
            <select v-model="type" class="rounded-xl border border-input bg-background px-3 py-2.5 text-sm">
                <option value="all">Semua jenis</option>
                <option v-for="(label, key) in TYPE_LABELS" :key="key" :value="key">{{ label }}</option>
            </select>
        </div>

        <div v-if="!filtered.length" class="rounded-2xl border border-dashed p-10 text-center text-sm text-muted-foreground">
            Belum ada riwayat keluar-masuk.
        </div>

        <ol v-else class="space-y-3">
            <li v-for="m in filtered" :key="m.id" class="rounded-2xl border bg-card p-4 shadow-sm dark:bg-transparent">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span :class="['rounded-full px-2.5 py-0.5 text-xs font-semibold', typeClass(m.type)]">
                            {{ TYPE_LABELS[m.type] }}
                        </span>
                        <Link :href="`/admin/devices/${m.rack_divice_id}/history`" class="font-medium hover:underline">
                            {{ m.device_name }}
                        </Link>
                        <span class="text-xs text-muted-foreground">{{ m.device_code }}</span>
                    </div>
                    <span class="text-xs text-muted-foreground">{{ formatDateTime(m.occurred_at) }}</span>
                </div>

                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-muted-foreground">{{ position(m.from_rack, m.from_start_unit, m.from_end_unit) }}</span>
                    <ArrowRight class="h-3.5 w-3.5 text-muted-foreground" />
                    <span>{{ m.type === 'check_out' ? 'Keluar DC' : m.type === 'temp_out' ? 'Keluar sementara' : position(m.to_rack, m.to_start_unit, m.to_end_unit) }}</span>
                </div>

                <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted-foreground">
                    <span>{{ m.client?.company_name ?? '-' }}</span>
                    <span v-if="m.carrier_name">Pembawa: {{ m.carrier_name }}{{ m.carrier_company ? ` (${m.carrier_company})` : '' }}</span>
                    <span v-if="m.recorder">Dicatat: {{ m.recorder.name }}</span>
                    <span v-if="m.attachments_count" class="inline-flex items-center gap-1"><Paperclip class="h-3 w-3" /> {{ m.attachments_count }} lampiran</span>
                </div>

                <p v-if="m.reason" class="mt-2 text-sm">{{ m.reason }}</p>

                <Link :href="`/admin/device-movements/${m.id}`" class="mt-2 inline-block text-xs font-medium text-primary hover:underline">
                    {{ m.movement_number }} · detail
                </Link>
            </li>
        </ol>
    </div>
</template>
