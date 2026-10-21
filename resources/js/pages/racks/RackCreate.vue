<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import InputError from '@/components/InputError.vue';
import ImageUploadPreview from '@/components/vanila/rack-image/ImageUploadPreview.vue';
import {
    Activity,
    Thermometer,
    Zap,
    Search,
    RefreshCw,
    Check,
    Unlink,
    SlidersHorizontal,
    X,
    AlertCircle,
    CheckCircle2,
    XCircle,
    AlertTriangle,
} from 'lucide-vue-next';
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps<{
    title: string;
    rooms: any;
}>();

const form = useForm({
    room_id: '',
    code: '',
    name: '',
    total_units: '',
    power_capacity: '',
    weight_capacity: '',
    description: '',
    status: 'active',
    api_suhu: '',
    images: [] as File[],
});

const createRack = () => {
    form.post(`/admin/racks`, { forceFormData: true });
};

// Modal Connect API Suhu State
const isConnectModalOpen = ref(false);
const isLoadingApiRacks = ref(false);
const apiRacksList = ref<any[]>([]);
const apiSearchQuery = ref('');
const apiErrorMessage = ref('');
const previewRackData = ref<any | null>(null);

const openConnectModal = async () => {
    isConnectModalOpen.value = true;
    if (apiRacksList.value.length === 0) {
        await fetchApiRacks();
    }
};

const closeConnectModal = () => {
    isConnectModalOpen.value = false;
};

const fetchApiRacks = async () => {
    isLoadingApiRacks.value = true;
    apiErrorMessage.value = '';
    try {
        const response = await axios.get('/admin/racks/monitoring/data');
        if (response.data && response.data.ok && Array.isArray(response.data.data)) {
            apiRacksList.value = response.data.data;
        } else {
            apiErrorMessage.value = response.data?.error || 'Format data API tidak sesuai.';
        }
    } catch (err: any) {
        apiErrorMessage.value = err.response?.data?.message || err.message || 'Gagal memuat daftar sensor suhu API.';
    } finally {
        isLoadingApiRacks.value = false;
    }
};

const selectApiRack = (rackName: string) => {
    form.api_suhu = rackName;
    closeConnectModal();
};

const clearApiConnection = () => {
    form.api_suhu = '';
};

// Filtered API Racks for modal
const filteredApiRacks = () => {
    if (!apiSearchQuery.value.trim()) return apiRacksList.value;
    const q = apiSearchQuery.value.toLowerCase().trim();
    return apiRacksList.value.filter(
        (r) => r.rack?.toLowerCase().includes(q) || r.slave?.toString().includes(q)
    );
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Rack Management',
                href: '/admin/racks',
            },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-bold">Create Rack</h1>

        <Form @submit.prevent="createRack" v-slot="{ errors, processing }" class="flex flex-col gap-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full rounded-md border overflow-x-auto p-4">
                <div class="grid gap-3 w-full">
                    <Label for="code">Rack Code <span class="text-red-500">*</span></Label>
                    <Input id="code" v-model="form.code" type="text" required autofocus :tabindex="1"
                        autocomplete="code" name="code" placeholder="Rack code" />
                    <InputError :message="form.errors.code" />
                </div>

                <div class="grid gap-3 w-full">
                    <Label for="name">Rack Name <span class="text-red-500">*</span></Label>
                    <Input id="name" v-model="form.name" type="text" required :tabindex="2"
                        autocomplete="name" name="name" placeholder="Rack name" />
                    <InputError :message="form.errors.name" />
                </div>

                <div class="grid gap-3 w-full">
                    <Label for="total_units">Total Units <span class="text-red-500">*</span></Label>
                    <Input id="total_units" v-model="form.total_units" type="number" required :tabindex="2"
                        autocomplete="total_units" name="total_units" placeholder="Total units" />
                    <InputError :message="form.errors.total_units" />
                </div>

                <div class="grid gap-3 w-full">
                    <Label for="power_capacity">Power Capacity (Ampere) <span class="text-red-500">*</span></Label>
                    <Input id="power_capacity" v-model="form.power_capacity" type="number" required
                        :tabindex="2" autocomplete="power_capacity" name="power_capacity"
                        placeholder="Power capacity" />
                    <InputError :message="form.errors.power_capacity" />
                </div>

                <div class="grid gap-3 w-full">
                    <Label for="weight_capacity">Weight Capacity (kg) <span class="text-red-500">*</span></Label>
                    <Input id="weight_capacity" v-model="form.weight_capacity" type="number" required
                        :tabindex="2" autocomplete="weight_capacity" name="weight_capacity"
                        placeholder="Weight capacity" />
                    <InputError :message="form.errors.weight_capacity" />
                </div>

                <!-- Status Selector -->
                <div class="grid w-full gap-3">
                    <Label>
                        Status
                        <span class="text-red-500">*</span>
                    </Label>

                    <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                        <button type="button" @click="form.status = 'active'" :class="[
                            'rounded-md border px-4 py-2 text-sm font-medium transition-all',
                            form.status === 'active'
                                ? 'border-primary bg-primary text-white dark:text-gray-900'
                                : 'border-input bg-background hover:bg-muted dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:bg-transparent'
                        ]">
                            Active
                        </button>

                        <button type="button" @click="form.status = 'inactive'" :class="[
                            'rounded-md border px-4 py-2 text-sm font-medium transition-all',
                            form.status === 'inactive'
                                ? 'border-primary bg-primary text-white dark:text-gray-900'
                                : 'border-input bg-background hover:bg-muted dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:bg-transparent'
                        ]">
                            Inactive
                        </button>

                        <button type="button" @click="form.status = 'maintenance'" :class="[
                            'rounded-md border px-4 py-2 text-sm font-medium transition-all',
                            form.status === 'maintenance'
                                ? 'border-primary bg-primary text-white dark:text-gray-900'
                                : 'border-input bg-background hover:bg-muted dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:bg-transparent'
                        ]">
                            Maintenance
                        </button>

                        <button type="button" @click="form.status = 'full'" :class="[
                            'rounded-md border px-4 py-2 text-sm font-medium transition-all',
                            form.status === 'full'
                                ? 'border-primary bg-primary text-white dark:text-gray-900'
                                : 'border-input bg-background hover:bg-muted dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:bg-transparent'
                        ]">
                            Full
                        </button>
                    </div>

                    <InputError :message="form.errors.status" />
                </div>

                <!-- API Suhu Connection Section -->
                <div class="grid md:col-span-2 gap-3 w-full rounded-xl border border-dashed border-cyan-200 bg-cyan-50/40 p-4 dark:border-cyan-900/50 dark:bg-cyan-950/20">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-cyan-500 text-white shadow-sm shadow-cyan-500/30">
                                <Activity class="h-4 w-4" />
                            </div>
                            <div>
                                <Label class="text-sm font-semibold text-slate-900 dark:text-white">
                                    Integrasi API Sensor Suhu & Telemetri
                                </Label>
                                <p class="text-xs text-slate-500">
                                    Hubungkan rack ini dengan identifier sensor pada endpoint telemetri Data Center
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                @click="openConnectModal"
                                class="border-cyan-300 bg-white text-cyan-700 hover:bg-cyan-50 dark:border-cyan-800 dark:bg-slate-900 dark:text-cyan-300 dark:hover:bg-slate-800"
                            >
                                <Thermometer class="mr-1.5 h-3.5 w-3.5 text-orange-500" />
                                {{ form.api_suhu ? 'Ganti Koneksi API Suhu' : 'Connect To API Suhu' }}
                            </Button>

                            <Button
                                v-if="form.api_suhu"
                                type="button"
                                variant="ghost"
                                size="sm"
                                @click="clearApiConnection"
                                class="text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-950/30"
                                title="Putuskan Koneksi"
                            >
                                <Unlink class="h-3.5 w-3.5 mr-1" />
                                Putus
                            </Button>
                        </div>
                    </div>

                    <!-- Connected Status Badge & Input Field -->
                    <div class="mt-2 flex flex-col sm:flex-row sm:items-center gap-3">
                        <div class="flex-1">
                            <Input
                                id="api_suhu"
                                v-model="form.api_suhu"
                                type="text"
                                name="api_suhu"
                                placeholder="Contoh: 4A-2 (atau pilih melalui tombol Connect To API Suhu di samping)"
                                class="bg-white dark:bg-slate-900 font-mono text-xs"
                            />
                        </div>

                        <div v-if="form.api_suhu" class="flex items-center gap-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/40 rounded-lg px-3 py-2">
                            <CheckCircle2 class="h-4 w-4" />
                            <span>Terkoneksi ke ID Rack API: <strong>{{ form.api_suhu }}</strong></span>
                        </div>
                        <div v-else class="text-xs text-slate-400 italic">
                            Belum terhubung ke sensor API.
                        </div>
                    </div>

                    <InputError :message="form.errors.api_suhu" />
                </div>

                <!-- Rack Images -->
                <div class="grid md:col-span-2 gap-3 w-full">
                    <Label>Gambar Rack</Label>
                    <ImageUploadPreview v-model="form.images" />
                    <InputError :message="form.errors.images" />
                </div>

                <div class="grid md:col-span-2 gap-3 w-full">
                    <Label for="description">Description <span class="text-red-500">*</span></Label>
                    <textarea id="description" v-model="form.description" required :tabindex="3"
                        autocomplete="description" name="description" placeholder="Rack description"
                        class="w-full rounded-md border border-gray-300 bg-white py-2 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none resize-none h-24 dark:bg-transparent dark:text-gray-300 dark:border-gray-700"></textarea>
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid md:col-span-2 gap-3 w-full">
                    <Label for="room_id">Location<span class="text-red-500">*</span></Label>
                    <select id="room_id" v-model="form.room_id" required
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700">
                        <option value="">Location Room</option>
                        <option v-for="item in rooms" :key="item.id" :value="item.id">
                            {{ item.location_data_center?.name }} - {{ item.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.room_id" />
                </div>
            </div>

            <div class="flex items-center justify-end gap-4">
                <Button type="button"
                    class="bg-gray-300 text-gray-700 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    @click="$inertia.visit('/admin/racks')">
                    Cancel
                </Button>
                <Button type="submit" :disabled="form.processing"
                    class="bg-primary text-white hover:bg-primary/90 disabled:bg-gray-300 disabled:text-gray-500 dark:text-gray-900">
                    <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Creating...' : 'Create Rack' }}
                </Button>
            </div>
        </Form>

        <!-- ============================================= -->
        <!-- MODAL DAFTAR SUHU API UNTUK MEMILIH RACK -->
        <!-- ============================================= -->
        <div
            v-if="isConnectModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-sm"
            @click.self="closeConnectModal"
        >
            <div class="flex max-h-[85vh] w-full max-w-2xl flex-col rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900 overflow-hidden">
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 p-5 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-orange-50 text-orange-600 dark:bg-orange-950/40 dark:text-orange-400">
                            <Thermometer class="h-5 w-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                Pilih Sensor Rack dari API Suhu
                            </h3>
                            <p class="text-xs text-slate-500">
                                Data ditarik dari endpoint <span class="font-mono text-[11px]">https://ldx.theinvention.net/api/data.php</span>
                            </p>
                        </div>
                    </div>
                    <button
                        @click="closeConnectModal"
                        class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-600 dark:hover:bg-slate-800"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Search and Refresh Bar -->
                <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/50 p-3.5 dark:border-slate-800 dark:bg-slate-950/40">
                    <div class="relative flex-1">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <input
                            v-model="apiSearchQuery"
                            type="text"
                            placeholder="Cari ID Rack (contoh: 4A-2, 2A-1)..."
                            class="w-full rounded-lg border border-slate-200 bg-white py-1.5 pl-9 pr-3 text-xs text-slate-900 focus:border-blue-500 focus:outline-none dark:border-slate-800 dark:bg-slate-900 dark:text-white"
                        />
                    </div>
                    <button
                        type="button"
                        @click="fetchApiRacks"
                        :disabled="isLoadingApiRacks"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50 disabled:opacity-50 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300"
                    >
                        <RefreshCw :class="['h-3.5 w-3.5', isLoadingApiRacks ? 'animate-spin' : '']" />
                        Refresh API
                    </button>
                </div>

                <!-- Modal Body: Daftar Rack -->
                <div class="flex-1 overflow-y-auto p-4 space-y-2">
                    <div v-if="isLoadingApiRacks" class="flex flex-col items-center justify-center py-12 text-slate-500">
                        <Spinner class="h-7 w-7 text-blue-600 mb-2" />
                        <span class="text-xs">Menghubungkan ke API LDX Suhu Monitoring...</span>
                    </div>

                    <div v-else-if="apiErrorMessage" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-xs text-rose-700 dark:border-rose-900/40 dark:bg-rose-950/30 dark:text-rose-300">
                        <div class="flex items-center gap-2 font-semibold">
                            <AlertCircle class="h-4 w-4 text-rose-600" />
                            Gagal Memuat Data
                        </div>
                        <p class="mt-1">{{ apiErrorMessage }}</p>
                    </div>

                    <div v-else-if="filteredApiRacks().length === 0" class="py-10 text-center text-xs text-slate-400">
                        Tidak ada data rack API yang cocok dengan pencarian.
                    </div>

                    <div
                        v-for="r in filteredApiRacks()"
                        :key="r.rack + r.slave"
                        @click="selectApiRack(r.rack)"
                        :class="[
                            'group flex cursor-pointer items-center justify-between rounded-xl border p-3 transition-all',
                            form.api_suhu === r.rack
                                ? 'border-cyan-500 bg-cyan-50/70 shadow-sm dark:border-cyan-600 dark:bg-cyan-950/30'
                                : 'border-slate-200 bg-white hover:border-blue-400 hover:bg-slate-50/80 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-blue-600'
                        ]"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 font-bold font-mono text-sm text-slate-800 dark:bg-slate-800 dark:text-slate-200">
                                {{ r.rack }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-sm text-slate-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                        Rack {{ r.rack }}
                                    </span>
                                    <span class="text-[10px] font-mono text-slate-400 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">
                                        Slave #{{ r.slave }}
                                    </span>
                                    <span
                                        :class="[
                                            'text-[10px] font-semibold px-2 py-0.5 rounded-full capitalize',
                                            r.status === 'normal' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' :
                                            r.status === 'warning' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400' :
                                            'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400'
                                        ]"
                                    >
                                        {{ r.status }}
                                    </span>
                                </div>
                                <div class="mt-1 flex items-center gap-3 text-xs text-slate-500">
                                    <span class="flex items-center gap-1">
                                        <Thermometer class="h-3.5 w-3.5 text-orange-500" />
                                        {{ r.suhu?.temp ?? '-' }} °C
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Zap class="h-3.5 w-3.5 text-amber-500" />
                                        PLN: {{ r.pln?.voltage ?? '-' }} V
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Zap class="h-3.5 w-3.5 text-cyan-500" />
                                        UPS: {{ r.ups?.voltage ?? '-' }} V
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <div v-if="form.api_suhu === r.rack" class="flex items-center gap-1 text-xs font-semibold text-cyan-600 dark:text-cyan-400">
                                <Check class="h-4 w-4" />
                                Terpilih
                            </div>
                            <Button
                                type="button"
                                size="sm"
                                variant="outline"
                                class="text-xs group-hover:bg-blue-600 group-hover:text-white transition-colors"
                            >
                                Sambungkan
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50/50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                    <span class="text-xs text-slate-500">
                        Total Sensor Tersedia: <strong>{{ apiRacksList.length }} Rack</strong>
                    </span>
                    <Button
                        type="button"
                        variant="secondary"
                        size="sm"
                        @click="closeConnectModal"
                    >
                        Batal
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>