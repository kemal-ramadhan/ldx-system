<script setup lang="ts">
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { AlertTriangle, ArrowLeft, Info, Plus, Save, Trash2, UserPlus, X } from 'lucide-vue-next'
import { type Visit, type VisitMember, type VisitRack, rackLocation, todayLocal } from '@/lib/visitRequest'

/**
 * Client portal: create (or edit, while pending) a visit request.
 */
const props = defineProps<{
    visit?: Visit
    racks: VisitRack[]
    types: Record<string, string>
    companyName: string
}>()

const isEdit = computed(() => !!props.visit)

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Pengajuan Kunjungan', href: '/client/visit-requests' },
            { title: 'Form', href: '#' },
        ],
    },
})

const emptyMember = (): VisitMember => ({
    name: '',
    nik: '',
    phone: '',
    email: '',
    address: '',
    company_name: props.companyName ?? '',
    position_in_company: '',
})

const form = useForm({
    visit_date: props.visit?.visit_date?.slice(0, 10) ?? '',
    start_time: props.visit?.start_time?.slice(0, 5) ?? '09:00',
    end_time: props.visit?.end_time?.slice(0, 5) ?? '12:00',
    type_of_visit: props.visit?.type_of_visit ?? 'Kunjungan',
    purpose: props.visit?.purpose ?? '',
    notes: props.visit?.notes ?? '',
    rack_ids: (props.visit?.racks ?? []).map((r) => r.id) as number[],
    members: (props.visit?.members?.length
        ? props.visit.members.map((m) => ({
            name: m.name,
            nik: m.nik,
            phone: m.phone,
            email: m.email,
            address: m.address,
            company_name: m.company_name,
            position_in_company: m.position_in_company,
        }))
        : [emptyMember()]) as VisitMember[],
})

const addMember = () => {
    if (form.members.length < 20) form.members.push(emptyMember())
}

const removeMember = (index: number) => {
    if (form.members.length > 1) form.members.splice(index, 1)
}

const toggleRack = (id: number) => {
    form.rack_ids = form.rack_ids.includes(id) ? form.rack_ids.filter((r) => r !== id) : [...form.rack_ids, id]
}

/*
|--------------------------------------------------------------------------
| CLIENT-SIDE CHECK + popup of what's missing (same idea as the guest book)
|--------------------------------------------------------------------------
*/
const missing = ref<string[]>([])

const MEMBER_FIELDS: [keyof VisitMember, string][] = [
    ['name', 'Nama'],
    ['nik', 'NIK/Paspor'],
    ['phone', 'Telepon'],
    ['email', 'Email'],
    ['address', 'Alamat'],
    ['company_name', 'Perusahaan'],
    ['position_in_company', 'Jabatan'],
]

const validate = () => {
    const list: string[] = []

    if (!form.visit_date) list.push('Tanggal kunjungan wajib diisi')
    else if (form.visit_date < todayLocal()) list.push('Tanggal kunjungan minimal hari ini')
    if (!form.start_time || !form.end_time) list.push('Jam mulai & selesai wajib diisi')
    else if (form.end_time <= form.start_time) list.push('Jam selesai harus setelah jam mulai')
    if (!form.purpose.trim()) list.push('Keperluan kunjungan wajib diisi')

    form.members.forEach((member, i) => {
        MEMBER_FIELDS.forEach(([key, label]) => {
            if (!String(member[key] ?? '').trim()) list.push(`Tamu ${i + 1}: ${label} wajib diisi`)
        })
        if (member.email && !/^\S+@\S+\.\S+$/.test(member.email)) list.push(`Tamu ${i + 1}: format email tidak valid`)
    })

    missing.value = list
    return list.length === 0
}

const submit = () => {
    if (!validate()) return

    const options = {
        preserveScroll: true,
        onError: (errors: Record<string, string>) => {
            missing.value = Object.entries(errors).map(([key, message]) => {
                const match = key.match(/^members\.(\d+)\./)
                return match ? `Tamu ${Number(match[1]) + 1}: ${message}` : message
            })
        },
    }

    if (isEdit.value) form.put(`/client/visit-requests/${props.visit!.id}`, options)
    else form.post('/client/visit-requests', options)
}

const memberError = (index: number, field: string) =>
    (form.errors as Record<string, string | undefined>)[`members.${index}.${field}`]

const inputClass = 'w-full rounded-xl border border-input bg-background px-3 py-2.5 text-sm'
</script>

<template>
    <Head :title="isEdit ? 'Ubah Pengajuan Kunjungan' : 'Ajukan Kunjungan'" />

    <div class="flex flex-col gap-5 p-4 text-foreground dark:scheme-dark">
        <!-- HEADER -->
        <div class="flex items-start gap-3">
            <Link :href="isEdit ? `/client/visit-requests/${visit!.id}` : '/client/visit-requests'"
                class="rounded-xl border p-2 text-muted-foreground hover:bg-muted">
                <ArrowLeft class="h-4 w-4" />
            </Link>
            <div>
                <h1 class="text-xl font-semibold">{{ isEdit ? `Ubah ${visit!.request_number}` : 'Ajukan Kunjungan' }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Setelah disetujui tim LDX, Anda menerima kode kunjungan. Pada hari H tamu cukup check-in di Buku Tamu
                    dengan kode tersebut.
                </p>
            </div>
        </div>

        <!-- VISIT -->
        <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
            <h2 class="mb-4 font-semibold">Detail Kunjungan</h2>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Tanggal <span class="text-red-500">*</span></label>
                    <input v-model="form.visit_date" type="date" :min="todayLocal()" :class="inputClass" />
                    <p v-if="form.errors.visit_date" class="mt-1 text-xs text-red-500">{{ form.errors.visit_date }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Jam Mulai <span class="text-red-500">*</span></label>
                    <input v-model="form.start_time" type="time" :class="inputClass" />
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Jam Selesai <span class="text-red-500">*</span></label>
                    <input v-model="form.end_time" type="time" :class="inputClass" />
                    <p v-if="form.errors.end_time" class="mt-1 text-xs text-red-500">{{ form.errors.end_time }}</p>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium">Jenis Kunjungan <span class="text-red-500">*</span></label>
                    <select v-model="form.type_of_visit" :class="inputClass">
                        <option v-for="(label, key) in types" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>

                <div class="md:col-span-4">
                    <label class="mb-1.5 block text-sm font-medium">Keperluan <span class="text-red-500">*</span></label>
                    <input v-model="form.purpose" type="text" maxlength="255" placeholder="Contoh: Instalasi server baru di rack kami"
                        :class="inputClass" />
                    <p v-if="form.errors.purpose" class="mt-1 text-xs text-red-500">{{ form.errors.purpose }}</p>
                </div>

                <div class="md:col-span-4">
                    <label class="mb-1.5 block text-sm font-medium">Rack yang dikunjungi</label>
                    <div v-if="racks.length" class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        <button v-for="rack in racks" :key="rack.id" type="button" @click="toggleRack(rack.id)" :class="[
                            'rounded-xl border px-3 py-2.5 text-left text-sm transition',
                            form.rack_ids.includes(rack.id)
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'border-input bg-background hover:bg-muted',
                        ]">
                            {{ rackLocation(rack) }}
                        </button>
                    </div>
                    <p v-else class="text-sm text-muted-foreground">Perusahaan Anda belum memiliki rack aktif.</p>
                    <p class="mt-1 text-xs text-muted-foreground">Opsional — pilih jika kunjungan ke rack tertentu.</p>
                    <p v-if="form.errors.rack_ids" class="mt-1 text-xs text-red-500">{{ form.errors.rack_ids }}</p>
                </div>

                <div class="md:col-span-4">
                    <label class="mb-1.5 block text-sm font-medium">Catatan</label>
                    <textarea v-model="form.notes" rows="2" maxlength="2000" placeholder="Informasi tambahan untuk tim LDX (opsional)"
                        :class="inputClass" />
                </div>
            </div>
        </div>

        <!-- MEMBERS -->
        <div class="rounded-2xl border bg-card p-5 shadow-sm dark:bg-transparent">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 class="font-semibold">Tamu yang Berkunjung ({{ form.members.length }})</h2>
                    <p class="text-xs text-muted-foreground">
                        Isi data lengkap setiap orang. Saat check-in, tamu memvalidasi dengan 4 digit terakhir NIK/Paspor.
                    </p>
                </div>
                <button type="button" @click="addMember" :disabled="form.members.length >= 20"
                    class="inline-flex items-center gap-2 rounded-xl border px-3 py-2 text-sm font-medium hover:bg-muted disabled:opacity-50">
                    <UserPlus class="h-4 w-4" /> Tambah Tamu
                </button>
            </div>

            <div class="space-y-4">
                <div v-for="(member, index) in form.members" :key="index" class="rounded-xl border p-4">
                    <div class="mb-3 flex items-center justify-between">
                        <span class="text-sm font-semibold">Tamu {{ index + 1 }}</span>
                        <button v-if="form.members.length > 1" type="button" @click="removeMember(index)"
                            class="rounded-lg p-1.5 text-muted-foreground hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40" title="Hapus tamu">
                            <Trash2 class="h-4 w-4" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                        <div>
                            <input v-model="member.name" type="text" placeholder="Nama lengkap *" :class="inputClass" />
                            <p v-if="memberError(index, 'name')" class="mt-1 text-xs text-red-500">{{ memberError(index, 'name') }}</p>
                        </div>
                        <div>
                            <input v-model="member.nik" type="text" maxlength="20" placeholder="NIK / Paspor *" :class="inputClass" />
                            <p v-if="memberError(index, 'nik')" class="mt-1 text-xs text-red-500">{{ memberError(index, 'nik') }}</p>
                        </div>
                        <div>
                            <input v-model="member.phone" type="tel" maxlength="20" placeholder="No. telepon *" :class="inputClass" />
                        </div>
                        <div>
                            <input v-model="member.email" type="email" placeholder="Email *" :class="inputClass" />
                            <p v-if="memberError(index, 'email')" class="mt-1 text-xs text-red-500">{{ memberError(index, 'email') }}</p>
                        </div>
                        <div>
                            <input v-model="member.company_name" type="text" placeholder="Perusahaan *" :class="inputClass" />
                        </div>
                        <div>
                            <input v-model="member.position_in_company" type="text" placeholder="Jabatan *" :class="inputClass" />
                        </div>
                        <div class="md:col-span-3">
                            <input v-model="member.address" type="text" maxlength="255" placeholder="Alamat *" :class="inputClass" />
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" @click="addMember" :disabled="form.members.length >= 20"
                class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-input px-3 py-3 text-sm text-muted-foreground hover:bg-muted disabled:opacity-50">
                <Plus class="h-4 w-4" /> Tambah tamu lain
            </button>
        </div>

        <p class="flex items-start gap-2 rounded-xl bg-muted px-4 py-3 text-xs text-muted-foreground">
            <Info class="mt-0.5 h-4 w-4 shrink-0" />
            Pengajuan bisa diubah selama belum ditinjau. Setelah disetujui, perubahan tamu dilakukan dengan membatalkan dan
            mengajukan ulang.
        </p>

        <!-- ACTIONS -->
        <div class="flex justify-end gap-3">
            <Link :href="isEdit ? `/client/visit-requests/${visit!.id}` : '/client/visit-requests'"
                class="inline-flex items-center rounded-xl border px-5 py-2.5 text-sm font-medium hover:bg-muted">
                Batal
            </Link>
            <button type="button" @click="submit" :disabled="form.processing"
                class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 disabled:opacity-50">
                <Save class="h-4 w-4" />
                {{ form.processing ? 'Mengirim...' : isEdit ? 'Simpan Perubahan' : 'Kirim Pengajuan' }}
            </button>
        </div>
    </div>

    <!-- MISSING DATA POPUP -->
    <div v-if="missing.length" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 text-foreground"
        @click.self="missing = []">
        <div class="w-full max-w-md rounded-2xl border bg-card shadow-xl">
            <div class="flex items-start gap-3 border-b px-6 py-5">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-950/40">
                    <AlertTriangle class="h-5 w-5 text-red-600 dark:text-red-400" />
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold">Data belum lengkap</h3>
                    <p class="mt-1 text-sm text-muted-foreground">Lengkapi data berikut. Isian Anda tetap tersimpan.</p>
                </div>
                <button type="button" class="rounded-lg p-1 text-muted-foreground hover:bg-muted" @click="missing = []">
                    <X class="h-4 w-4" />
                </button>
            </div>
            <ul class="max-h-72 list-disc space-y-1 overflow-y-auto px-10 py-4 text-sm text-red-700 dark:text-red-300">
                <li v-for="item in missing" :key="item">{{ item }}</li>
            </ul>
            <div class="flex justify-end border-t px-6 py-4">
                <button type="button" @click="missing = []"
                    class="rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">
                    Lengkapi Data
                </button>
            </div>
        </div>
    </div>
</template>
