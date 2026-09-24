<script setup lang="ts">

const props = defineProps<{
    client: any;
}>();
</script>

<template>
    <div class="w-full overflow-x-auto p-4">
        <div class="w-full overflow-x-auto">
            <h2 class="sr-only">Company Information Card</h2>

            <div
                class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-800 overflow-hidden">

                <!-- Header -->
                <div
                    class="p-5 flex items-center gap-4 bg-gray-900 dark:bg-gray-800">
                    <div
                        class="w-14 h-14 rounded-xl flex items-center justify-center bg-gray-200 dark:bg-gray-700">
                        <div
                            class="flex items-center justify-center text-sm font-bold uppercase text-gray-900 dark:text-gray-100">
                            {{
                                props.client?.company_name
                                    ?.split(' ')
                                    .map((word: string) => word[0])
                            .join('')
                            .slice(0, 4)
                            }}
                        </div>
                    </div>
                    <div>
                        <p class="m-0 text-lg font-medium text-white dark:text-gray-100">{{
                            props.client?.company_name || '' }}</p>
                        <p class="m-0 mt-0.5 text-sm text-gray-300 dark:text-gray-400">{{
                            props.client?.company_code || '' }}</p>
                    </div>
                    <div class="ml-auto">
                        <span
                            class="text-xs px-3 py-1 rounded-full border border-white/30 bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100 dark:border-green-800">
                            <i class="ti ti-circle-check text-green-600 dark:text-green-400 text-sm align-middle mr-1"
                                aria-hidden="true"></i>
                            {{ props.client?.status || '' }}
                        </span>
                    </div>
                </div>

                <!-- Fields Grid -->
                <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-0" >

                    <!-- Email -->
                    <div
                        class="py-2.5 pr-3 border-b border-gray-200 dark:border-gray-800 md:border-r">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Email</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="ti ti-mail text-emerald-600 dark:text-emerald-500 text-base" aria-hidden="true"></i>
                            <p class="m-0 text-sm text-gray-900 dark:text-gray-100">{{
                                props.client?.company_email || '' }}</p>
                        </div>
                    </div>

                    <!-- Telepon -->
                    <div class="py-2.5 pl-0 md:pl-3.5 border-b border-gray-200 dark:border-gray-800">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Telepon</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="ti ti-phone text-emerald-600 dark:text-emerald-500 text-base" aria-hidden="true"></i>
                            <p class="m-0 text-sm text-gray-900 dark:text-gray-100">{{
                                props.client?.company_phone || '' }}</p>
                        </div>
                    </div>

                    <!-- NPWP -->
                    <div
                        class="py-2.5 pr-3 border-b border-gray-200 dark:border-gray-800 md:border-r">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            NPWP</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="ti ti-id-badge text-emerald-600 dark:text-emerald-500 text-base" aria-hidden="true"></i>
                            <p class="m-0 text-sm text-gray-900 dark:text-gray-100">{{
                                props.client?.company_npwp || '' }}</p>
                        </div>
                    </div>

                    <!-- Kode Perusahaan -->
                    <div class="py-2.5 pl-0 md:pl-3.5 border-b border-gray-200 dark:border-gray-800">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Kode perusahaan</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="ti ti-hash text-emerald-600 dark:text-emerald-500 text-base" aria-hidden="true"></i>
                            <p class="m-0 text-sm text-gray-900 dark:text-gray-100">{{
                                props.client?.company_code || '' }}</p>
                        </div>
                    </div>

                    <!-- Alamat (full width) -->
                    <div
                        class="py-2.5 pr-3 border-b border-gray-200 dark:border-gray-800 md:col-span-2">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Alamat</p>
                        <div class="flex items-start gap-1.5 mt-1">
                            <i class="ti ti-map-pin text-emerald-600 dark:text-emerald-500 text-base mt-0.5"
                                aria-hidden="true"></i>
                            <p class="m-0 text-sm text-gray-900 dark:text-gray-100">{{
                                props.client?.company_address || '' }}</p>
                        </div>
                    </div>

                    <!-- Kota -->
                    <div
                        class="py-2.5 pr-3 border-b border-gray-200 dark:border-gray-800 md:border-r">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Kota</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="ti ti-building-skyscraper text-emerald-600 dark:text-emerald-500 text-base"
                                aria-hidden="true"></i>
                            <p class="m-0 text-sm text-gray-900 dark:text-gray-100">{{
                                props.client?.company_city || '' }}</p>
                        </div>
                    </div>

                    <!-- Provinsi -->
                    <div class="py-2.5 pl-0 md:pl-3.5 border-b border-gray-200 dark:border-gray-800">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Provinsi</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="ti ti-map text-emerald-600 dark:text-emerald-500 text-base" aria-hidden="true"></i>
                            <p class="m-0 text-sm text-gray-900 dark:text-gray-100">{{
                                props.client?.company_province || '' }}</p>
                        </div>
                    </div>

                    <!-- Kode Pos -->
                    <div
                        class="py-2.5 pr-3 border-b border-gray-200 dark:border-gray-800 md:border-r">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Kode pos</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="ti ti-mailbox text-emerald-600 dark:text-emerald-500 text-base" aria-hidden="true"></i>
                            <p class="m-0 text-sm text-gray-900 dark:text-gray-100">{{
                                props.client?.company_postal_code || '' }}</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="py-2.5 pl-0 md:pl-3.5 border-b border-gray-200 dark:border-gray-800">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Status</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="ti ti-toggle-right text-emerald-600 dark:text-emerald-500 text-base"
                                aria-hidden="true"></i>
                            <span
                                class="text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100 px-2.5 py-0.5 rounded-full font-medium">{{
                                props.client?.status || '' }}</span>
                        </div>
                    </div>

                    <!-- Tanggal Kontrak -->
                    <div class="py-2.5 pr-3 md:border-r border-gray-200 dark:border-gray-800">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Tanggal kontrak</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="ti ti-calendar text-emerald-600 dark:text-emerald-500 text-base" aria-hidden="true"></i>
                            <p class="m-0 text-sm text-gray-900 dark:text-gray-100">{{
                                props.client?.contract_date || '' }}</p>
                        </div>
                    </div>

                    <!-- Kontrak Selesai -->
                    <div class="py-2.5 pl-0 md:pl-3.5">
                        <p
                            class="m-0 text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Kontrak selesai</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <i class="ti ti-calendar-check text-emerald-600 dark:text-emerald-500 text-base"
                                aria-hidden="true"></i>
                            <p class="m-0 text-sm text-gray-900 dark:text-gray-100">{{
                                props.client?.contract_done_date || '-' }}</p>
                        </div>
                    </div>

                </div>

                <!-- Member Section -->
                <div class="border-t border-gray-200 dark:border-gray-800 p-4 bg-gray-50 dark:bg-gray-800/50">
                    <div
                        class="flex items-center justify-between mb-2.5">
                        <div class="flex items-center gap-1.5">
                            <i class="ti ti-users text-emerald-600 dark:text-emerald-500 text-base" aria-hidden="true"></i>
                            <p class="m-0 text-sm font-medium text-gray-900 dark:text-gray-100">
                                Member</p>
                        </div>
                        <span
                            class="text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-100 px-2.5 py-0.5 rounded-full font-medium">{{
                                props.client.pics?.length || '0' }} anggota</span>
                    </div>
                    <div class="flex items-center">
                        <template v-for="(pic, index) in props.client.pics" :key="pic.id">
                            <div class="flex items-center gap-2 mr-5">
                                <img v-if="pic.user?.avatar" :src="pic.user.avatar" :alt="pic.user.name"
                                    class="w-8 h-8 rounded-full object-cover" />

                                <!-- fallback kalau tidak ada avatar -->
                                <div v-else
                                    class="w-8 h-8 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center text-xs font-medium text-gray-700 dark:text-gray-200">
                                    {{ pic.user?.name?.charAt(0) }}
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>