<script setup lang="ts">
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { AlertTriangle, X } from 'lucide-vue-next'
import MovementCarrierFields from './MovementCarrierFields.vue'
import { type MovementAction, nowLocal, position } from '@/lib/deviceMovement'

/**
 * One modal for every keluar-masuk action on an existing device.
 */
const props = defineProps<{
    action: MovementAction | null
    device: any | null
    rackOptions?: { client_id: number; rack_id: number; rack_label: string; free_units: number }[]
    visitors?: any[]
}>()

const emit = defineEmits<{ (e: 'close'): void }>()

const META: Record<MovementAction, { title: string; desc: string; submit: string; danger?: boolean; reason: boolean }> = {
    temp_out: {
        title: 'Keluar Sementara',
        desc: 'Device dibawa keluar sementara (servis/RMA/dll) dan akan kembali.',
        submit: 'Catat Keluar Sementara',
        reason: true,
    },
    return_in: {
        title: 'Kembalikan ke Rack',
        desc: 'Device yang keluar sementara dibawa masuk kembali.',
        submit: 'Catat Kembali Masuk',
        reason: false,
    },
    relocate: {
        title: 'Pindah Rack / Unit',
        desc: 'Pindahkan device ke rack atau posisi unit lain milik client yang sama.',
        submit: 'Pindahkan Device',
        reason: true,
    },
    check_out: {
        title: 'Keluarkan Permanen',
        desc: 'Device dibawa keluar dari data center. Device tidak dihapus — riwayatnya tetap tersimpan.',
        submit: 'Catat Keluar Permanen',
        danger: true,
        reason: true,
    },
}

const meta = computed(() => (props.action ? META[props.action] : null))

const form = useForm({
    occurred_at: nowLocal(),
    reason: '',
    notes: '',
    reserve_units: true,
    rack_id: '' as number | '',
    start_unit: '' as number | '',
    carrier_name: '',
    carrier_company: '',
    carrier_id_number: '',
    carrier_phone: '',
    visitor_id: null as number | null,
    photos: [] as File[],
    bast: [] as File[],
})

// reset every time the modal opens for a device
watch(
    () => [props.action, props.device?.id],
    () => {
        form.reset()
        form.clearErrors()
        form.occurred_at = nowLocal()
    },
)

const unitsReserved = computed(() => (props.device?.rack_units ?? []).length > 0)

// racks the device's client rents (for relocate / return without reserved units)
const rackChoices = computed(() =>
    (props.rackOptions ?? []).filter((o) => o.client_id === props.device?.client_id),
)

const needsRack = computed(
    () => props.action === 'relocate' || (props.action === 'return_in' && !unitsReserved.value),
)

const connectedPorts = computed(() => (props.device?.ports ?? []).filter((p: any) => p.status === 'connected').length)

const submit = () => {
    if (!props.action || !props.device) return

    const url = {
        temp_out: 'temp-out',
        return_in: 'return-in',
        relocate: 'relocate',
        check_out: 'check-out',
    }[props.action]

    form.transform((data) => ({
        ...data,
        reserve_units: data.reserve_units ? 1 : 0,
        rack_id: data.rack_id || null,
        start_unit: data.start_unit || null,
    })).post(`/admin/devices/${props.device.id}/${url}`, {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => emit('close'),
    })
}

// errors raised by the service that are not form fields (e.g. no contiguous units)
const serverError = (key: 'units' | 'device') => (form.errors as Record<string, string | undefined>)[key]

const inputClass = 'w-full rounded-xl border border-input bg-background px-3 py-2.5 text-sm'
</script>

<template>
    <div v-if="action && device && meta" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 text-foreground dark:scheme-dark"
        @click.self="emit('close')">
        <div class="flex max-h-[92vh] w-full max-w-2xl flex-col rounded-2xl border bg-card shadow-xl">
            <!-- HEADER -->
            <div class="flex items-start justify-between gap-3 border-b px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold">{{ meta.title }}</h3>
                    <p class="mt-0.5 text-sm text-muted-foreground">
                        <strong class="text-foreground">{{ device.divice_name }}</strong>
                        · {{ device.code }} · posisi {{ position(device.rack, device.start_unit, device.end_unit) }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">{{ meta.desc }}</p>
                </div>
                <button type="button" class="rounded-lg p-1 text-muted-foreground hover:bg-muted" @click="emit('close')">
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- BODY -->
            <div class="space-y-5 overflow-y-auto px-6 py-5">
                <!-- warnings -->
                <div v-if="action === 'check_out' && connectedPorts"
                    class="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300">
                    <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                    {{ connectedPorts }} port masih terhubung cross connect. Cross connect akan di-terminate, port device ini
                    dinonaktifkan, dan port di ujung lain dikembalikan menjadi available.
                </div>
                <div v-if="action === 'temp_out' && connectedPorts"
                    class="flex items-start gap-2 rounded-xl border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300">
                    <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0" />
                    {{ connectedPorts }} port masih terhubung cross connect. Cross connect tidak diubah saat keluar sementara.
                </div>

                <!-- when -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Tanggal &amp; Jam <span class="text-red-500">*</span></label>
                        <input v-model="form.occurred_at" type="datetime-local" :class="inputClass" />
                        <p v-if="form.errors.occurred_at" class="mt-1 text-xs text-red-500">{{ form.errors.occurred_at }}</p>
                    </div>

                    <!-- temp out: keep units -->
                    <div v-if="action === 'temp_out'">
                        <label class="mb-1.5 block text-sm font-medium">Posisi unit di rack</label>
                        <label class="flex cursor-pointer items-start gap-2 rounded-xl border border-input px-3 py-2.5 text-sm">
                            <input v-model="form.reserve_units" type="checkbox" class="mt-0.5 h-4 w-4 accent-primary" />
                            <span>
                                Tahan posisi {{ position(device.rack, device.start_unit, device.end_unit) }}
                                <span class="block text-xs text-muted-foreground">Unit tidak dipakai device lain sampai device kembali.</span>
                            </span>
                        </label>
                    </div>

                    <div v-if="action === 'return_in' && unitsReserved" class="rounded-xl bg-muted px-3 py-2.5 text-sm">
                        Device kembali ke posisi yang ditahan:
                        <strong>{{ position(device.rack, device.start_unit, device.end_unit) }}</strong>
                    </div>
                </div>

                <!-- target rack -->
                <div v-if="needsRack" class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">
                            Rack tujuan <span v-if="action === 'relocate'" class="text-red-500">*</span>
                        </label>
                        <select v-model="form.rack_id" :class="inputClass">
                            <option value="">{{ action === 'return_in' ? 'Rack asal (otomatis)' : 'Pilih rack' }}</option>
                            <option v-for="o in rackChoices" :key="o.rack_id" :value="o.rack_id">
                                {{ o.rack_label }} — {{ o.free_units }} unit kosong
                            </option>
                        </select>
                        <p class="mt-1 text-xs text-muted-foreground">Hanya rack yang disewa client pemilik device.</p>
                        <p v-if="form.errors.rack_id" class="mt-1 text-xs text-red-500">{{ form.errors.rack_id }}</p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium">Mulai dari unit (opsional)</label>
                        <input v-model="form.start_unit" type="number" min="1" placeholder="Otomatis" :class="inputClass" />
                        <p class="mt-1 text-xs text-muted-foreground">Butuh {{ device.total_unit }}U berurutan.</p>
                        <p v-if="serverError('units')" class="mt-1 text-xs text-red-500">{{ serverError('units') }}</p>
                    </div>
                </div>

                <!-- reason -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium">
                        Alasan <span v-if="meta.reason" class="text-red-500">*</span>
                    </label>
                    <textarea v-model="form.reason" rows="2" maxlength="2000"
                        :placeholder="action === 'check_out' ? 'Contoh: kontrak selesai, perangkat ditarik pemilik' : 'Contoh: servis ke vendor'"
                        :class="inputClass" />
                    <p v-if="form.errors.reason" class="mt-1 text-xs text-red-500">{{ form.errors.reason }}</p>
                </div>

                <MovementCarrierFields :form="form" :visitors="visitors" />

                <div>
                    <label class="mb-1.5 block text-sm font-medium">Catatan</label>
                    <textarea v-model="form.notes" rows="2" maxlength="2000" :class="inputClass" />
                </div>

                <p v-if="serverError('device')" class="text-sm text-red-500">{{ serverError('device') }}</p>
            </div>

            <!-- FOOTER -->
            <div class="flex justify-end gap-2 border-t px-6 py-4">
                <button type="button" class="rounded-lg border px-4 py-2.5 text-sm font-medium hover:bg-muted" @click="emit('close')">
                    Batal
                </button>
                <button type="button" :disabled="form.processing" @click="submit" :class="[
                    'rounded-lg px-4 py-2.5 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-50',
                    meta.danger
                        ? 'bg-red-600 text-white hover:bg-red-700'
                        : 'bg-primary text-primary-foreground hover:bg-primary/90',
                ]">
                    {{ form.processing ? 'Menyimpan...' : meta.submit }}
                </button>
            </div>
        </div>
    </div>
</template>
