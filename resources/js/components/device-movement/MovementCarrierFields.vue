<script setup lang="ts">
import { ref } from 'vue'
import { FileText, ImagePlus, Paperclip, X } from 'lucide-vue-next'

/**
 * Carrier (pembawa barang) + photo / BAST upload fields of a device movement.
 * Binds directly to an Inertia form that has: carrier_name, carrier_company,
 * carrier_id_number, carrier_phone, visitor_id, photos[], bast[].
 */
const props = defineProps<{
    form: any
    visitors?: { id: number; name: string; company_name?: string; nik?: string; phone?: string }[]
}>()

const photoInput = ref<HTMLInputElement | null>(null)
const bastInput = ref<HTMLInputElement | null>(null)

// pick a guest registered today -> fill carrier fields
const pickVisitor = (event: Event) => {
    const id = Number((event.target as HTMLSelectElement).value)
    const visitor = props.visitors?.find((v) => v.id === id)

    props.form.visitor_id = visitor ? visitor.id : null

    if (visitor) {
        props.form.carrier_name = visitor.name ?? ''
        props.form.carrier_company = visitor.company_name ?? ''
        props.form.carrier_id_number = visitor.nik ?? ''
        props.form.carrier_phone = visitor.phone ?? ''
    }
}

const addFiles = (key: 'photos' | 'bast', event: Event) => {
    const input = event.target as HTMLInputElement
    props.form[key] = [...(props.form[key] ?? []), ...Array.from(input.files ?? [])]
    input.value = ''
}

const removeFile = (key: 'photos' | 'bast', index: number) => {
    props.form[key] = props.form[key].filter((_: File, i: number) => i !== index)
}

const preview = (file: File) => URL.createObjectURL(file)

const inputClass = 'w-full rounded-xl border border-input bg-background px-3 py-2.5 text-sm'
</script>

<template>
    <div class="space-y-4">
        <!-- CARRIER -->
        <div>
            <p class="mb-2 text-sm font-semibold">Pembawa Barang</p>

            <select v-if="visitors && visitors.length" :value="form.visitor_id ?? ''" @change="pickVisitor"
                :class="[inputClass, 'mb-3']">
                <option value="">Pilih dari Buku Tamu hari ini (opsional)</option>
                <option v-for="visitor in visitors" :key="visitor.id" :value="visitor.id">
                    {{ visitor.name }}{{ visitor.company_name ? ` — ${visitor.company_name}` : '' }}
                </option>
            </select>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <input v-model="form.carrier_name" type="text" placeholder="Nama pembawa" :class="inputClass" />
                <input v-model="form.carrier_company" type="text" placeholder="Perusahaan" :class="inputClass" />
                <input v-model="form.carrier_id_number" type="text" placeholder="No. identitas (KTP/SIM)" :class="inputClass" />
                <input v-model="form.carrier_phone" type="text" placeholder="No. telepon" :class="inputClass" />
            </div>
            <p v-if="form.errors.carrier_name" class="mt-1 text-xs text-red-500">{{ form.errors.carrier_name }}</p>
        </div>

        <!-- ATTACHMENTS -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <!-- photos -->
            <div>
                <p class="mb-2 text-sm font-semibold">Foto Barang</p>
                <input ref="photoInput" type="file" accept="image/jpeg,image/png,image/webp" multiple class="hidden"
                    @change="addFiles('photos', $event)" />
                <button type="button" @click="photoInput?.click()"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-input px-3 py-3 text-sm text-muted-foreground hover:bg-muted">
                    <ImagePlus class="h-4 w-4" /> Tambah foto
                </button>
                <p class="mt-1 text-[11px] text-muted-foreground">JPG/PNG/WEBP, maks. 5 MB per foto.</p>

                <div v-if="form.photos?.length" class="mt-2 grid grid-cols-3 gap-2">
                    <div v-for="(file, i) in form.photos" :key="i" class="relative">
                        <img :src="preview(file)" class="aspect-square w-full rounded-lg object-cover" />
                        <button type="button" @click="removeFile('photos', i)"
                            class="absolute right-1 top-1 rounded-full bg-black/60 p-0.5 text-white">
                            <X class="h-3 w-3" />
                        </button>
                    </div>
                </div>
                <p v-for="(message, key) in form.errors" v-show="String(key).startsWith('photos')" :key="key"
                    class="mt-1 text-xs text-red-500">{{ message }}</p>
            </div>

            <!-- BAST -->
            <div>
                <p class="mb-2 text-sm font-semibold">Dokumen BAST</p>
                <input ref="bastInput" type="file" accept="application/pdf,image/jpeg,image/png" multiple class="hidden"
                    @change="addFiles('bast', $event)" />
                <button type="button" @click="bastInput?.click()"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border-2 border-dashed border-input px-3 py-3 text-sm text-muted-foreground hover:bg-muted">
                    <Paperclip class="h-4 w-4" /> Tambah dokumen
                </button>
                <p class="mt-1 text-[11px] text-muted-foreground">PDF/JPG/PNG, maks. 10 MB per file.</p>

                <ul v-if="form.bast?.length" class="mt-2 space-y-1">
                    <li v-for="(file, i) in form.bast" :key="i"
                        class="flex items-center justify-between gap-2 rounded-lg bg-muted px-2 py-1.5 text-xs">
                        <span class="flex min-w-0 items-center gap-1.5">
                            <FileText class="h-3.5 w-3.5 shrink-0" />
                            <span class="truncate">{{ file.name }}</span>
                        </span>
                        <button type="button" @click="removeFile('bast', i)" class="text-muted-foreground hover:text-foreground">
                            <X class="h-3.5 w-3.5" />
                        </button>
                    </li>
                </ul>
                <p v-for="(message, key) in form.errors" v-show="String(key).startsWith('bast')" :key="key"
                    class="mt-1 text-xs text-red-500">{{ message }}</p>
            </div>
        </div>
    </div>
</template>
