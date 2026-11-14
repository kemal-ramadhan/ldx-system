<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { AlertTriangle, ArrowLeft, Cable, Clock3, ExternalLink, Server, X } from 'lucide-vue-next'
import {
    type ClientCrossConnect,
    cableLabel,
    endpoint,
    formatDate,
    isExternal,
    statusClass,
    statusLabel,
} from '@/lib/crossConnect'

/**
 * Client portal: cross connect detail (read-only) + terminate request.
 * The terminate itself is done by the LDX team in the admin panel.
 */
const props = defineProps<{
    crossConnect: ClientCrossConnect
    mySide: 'source' | 'destination' | null
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Cross Connect', href: '/client/cross-connects' },
            { title: 'Detail', href: '#' },
        ],
    },
})

const cc = computed(() => props.crossConnect)
const source = computed(() => endpoint(cc.value, 'source'))
const destination = computed(() => endpoint(cc.value, 'destination'))

const canRequestTerminate = computed(() => cc.value.status === 'active' && !cc.value.terminate_requested_at)

/*
|--------------------------------------------------------------------------
| TERMINATE REQUEST
|--------------------------------------------------------------------------
*/
const showTerminate = ref(false)
const terminateForm = useForm({ reason: '' })

const submitTerminate = () => {
    terminateForm.post(`/client/cross-connects/${cc.value.id}/terminate-request`, {
        preserveScroll: true,
        onSuccess: () => {
            showTerminate.value = false
            terminateForm.reset()
        },
    })
}

const ends = computed(() => [
    { key: 'source', title: 'Source', data: source.value, mine: props.mySide === 'source' },
    { key: 'destination', title: 'Destination', data: destination.value, mine: props.mySide === 'destination' },
])
</script>

<template>
    <Head :title="cc.cross_connect_number" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <!-- HEADER -->
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-start gap-3">
                <Link href="/client/cross-connects" class="rounded-xl border p-2 text-muted-foreground hover:bg-muted">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h1 class="text-xl font-semibold">{{ cc.cross_connect_number }}</h1>
                        <span :class="['rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset', statusClass(cc.status)]">
                            {{ statusLabel(cc.status) }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Terpasang {{ formatDate(cc.installed_at, true) }}
                        <template v-if="cc.terminated_at"> · Diterminasi {{ formatDate(cc.terminated_at, true) }}</template>
                    </p>
                </div>
            </div>

            <button v-if="canRequestTerminate" type="button" @click="showTerminate = true"
                class="inline-flex items-center gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-300 dark:hover:bg-red-900/40">
                <AlertTriangle class="h-4 w-4" />
                Ajukan Terminate
            </button>
        </div>

        <!-- TERMINATE REQUEST STATUS -->
        <div v-if="cc.terminate_requested_at"
            class="flex items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-300">
            <Clock3 class="mt-0.5 h-4 w-4 shrink-0" />
            <div>
                <p class="font-semibold">Permintaan terminate menunggu diproses tim LDX</p>
                <p class="mt-1">
                    Diajukan {{ formatDate(cc.terminate_requested_at, true) }}
                    <template v-if="cc.terminate_requester"> oleh {{ cc.terminate_requester.name }}</template>
                </p>
                <p class="mt-1 whitespace-pre-line">Alasan: {{ cc.terminate_request_reason }}</p>
            </div>
        </div>

        <!-- ENDPOINTS -->
        <div class="grid gap-5 lg:grid-cols-2">
            <div v-for="end in ends" :key="end.key" class="rounded-2xl border bg-card shadow-sm dark:bg-transparent">
                <div class="flex items-center justify-between gap-3 border-b p-5">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10">
                            <Server class="h-5 w-5 text-primary" />
                        </div>
                        <div>
                            <h2 class="font-semibold">{{ end.title }}</h2>
                            <p class="text-xs text-muted-foreground">{{ end.data.client }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <span v-if="end.data.badge"
                            class="rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-semibold text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">
                            {{ end.data.badge }}
                        </span>
                        <span v-if="end.mine" class="rounded-full bg-primary px-2 py-0.5 text-[10px] font-semibold text-primary-foreground">
                            Milik Anda
                        </span>
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-4 p-5 text-sm">
                    <div class="col-span-2">
                        <dt class="text-xs text-muted-foreground">Lokasi</dt>
                        <dd class="mt-1 font-medium">{{ end.data.location }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Rack</dt>
                        <dd class="mt-1 font-medium">{{ end.data.rack }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-muted-foreground">Device</dt>
                        <dd class="mt-1 font-medium">{{ end.data.device }}</dd>
                    </div>
                    <div class="col-span-2">
                        <dt class="text-xs text-muted-foreground">Port</dt>
                        <dd class="mt-1 font-medium">{{ end.data.port }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- CABLE -->
        <div class="rounded-2xl border bg-card shadow-sm dark:bg-transparent">
            <div class="flex items-center gap-3 border-b p-5">
                <Cable class="h-5 w-5 text-primary" />
                <h2 class="font-semibold">Detail Koneksi</h2>
            </div>
            <dl class="grid grid-cols-2 gap-4 p-5 text-sm md:grid-cols-4">
                <div>
                    <dt class="text-xs text-muted-foreground">Tipe Tujuan</dt>
                    <dd class="mt-1 font-medium">{{ isExternal(cc) ? 'MMR DC APJII' : 'Internal Data Center' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Cable</dt>
                    <dd class="mt-1 font-medium">{{ cableLabel(cc.cable_type) }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Connector</dt>
                    <dd class="mt-1 font-medium">{{ cc.connector_type || '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted-foreground">Panjang</dt>
                    <dd class="mt-1 font-medium">
                        {{ cc.cable_length ? `${Number(cc.cable_length)} ${cc.cable_length_unit}` : '-' }}
                    </dd>
                </div>
                <div v-if="cc.description" class="col-span-2 md:col-span-4">
                    <dt class="text-xs text-muted-foreground">Deskripsi</dt>
                    <dd class="mt-1 whitespace-pre-line">{{ cc.description }}</dd>
                </div>
                <div v-if="cc.notes" class="col-span-2 md:col-span-4">
                    <dt class="text-xs text-muted-foreground">Catatan</dt>
                    <dd class="mt-1 whitespace-pre-line">{{ cc.notes }}</dd>
                </div>
            </dl>

            <div v-if="cc.interconnection_request" class="border-t p-5">
                <Link :href="`/client/interconnections/${cc.interconnection_request.id}`"
                    class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium hover:bg-muted">
                    <ExternalLink class="h-4 w-4" />
                    Lihat request {{ cc.interconnection_request.request_number }}
                </Link>
            </div>
        </div>
    </div>

    <!-- TERMINATE REQUEST MODAL -->
    <div v-if="showTerminate" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 text-foreground dark:scheme-dark"
        @click.self="showTerminate = false">
        <div class="w-full max-w-lg rounded-2xl border bg-card shadow-xl">
            <div class="flex items-start justify-between gap-3 border-b px-6 py-5">
                <div>
                    <h3 class="text-lg font-semibold">Ajukan Terminate</h3>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Permintaan akan diproses oleh tim LDX. Koneksi tetap aktif sampai diproses.
                    </p>
                </div>
                <button type="button" class="rounded-lg p-1 text-muted-foreground hover:bg-muted" @click="showTerminate = false">
                    <X class="h-4 w-4" />
                </button>
            </div>

            <div class="px-6 py-5">
                <label class="mb-2 block text-sm font-medium">Alasan <span class="text-red-500">*</span></label>
                <textarea v-model="terminateForm.reason" rows="5" maxlength="2000" placeholder="Contoh: perangkat dipindahkan / layanan dihentikan"
                    class="w-full rounded-xl border border-input bg-background px-4 py-3 text-sm" />
                <p v-if="terminateForm.errors.reason" class="mt-2 text-sm text-red-600 dark:text-red-300">
                    {{ terminateForm.errors.reason }}
                </p>
            </div>

            <div class="flex justify-end gap-2 border-t px-6 py-4">
                <button type="button" class="rounded-lg border px-4 py-2.5 text-sm font-medium hover:bg-muted" @click="showTerminate = false">
                    Batal
                </button>
                <button type="button" :disabled="terminateForm.processing || !terminateForm.reason.trim()" @click="submitTerminate"
                    class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                    {{ terminateForm.processing ? 'Mengirim...' : 'Kirim Permintaan' }}
                </button>
            </div>
        </div>
    </div>
</template>
