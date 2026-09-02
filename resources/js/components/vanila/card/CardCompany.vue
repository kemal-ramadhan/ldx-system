<script setup lang="ts">

const props = defineProps<{
    client: any;
}>();
</script>

<template>
    <div class="w-full overflow-x-auto">
        <div class="w-full overflow-x-auto">
            <h2 class="sr-only">Company Information Card</h2>

            <div
                style="background: var(--color-background-primary); border-radius: var(--border-radius-lg); border: 0.5px solid var(--color-border-tertiary); overflow: hidden;">

                <!-- Header -->
                <div
                    style="padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 14px;" class="bg-gray-900 dark:bg-white">
                    <div
                        style="width: 52px; height: 52px; border-radius: 12px; display: flex; align-items: center; justify-content: center;" class="dark:bg-gray-400 bg-gray-200">
                        <div
                            class="flex items-center justify-center text-sm font-bold uppercase text-gray-900 dark:text-gray-900">
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
                        <p style="margin: 0; font-size: 18px; font-weight: 500;" class="dark:text-gray-900 text-white">{{
                            props.client?.company_name || '' }}</p>
                        <p style="margin: 2px 0 0; font-size: 13px;" class="dark:text-gray-900 text-white">{{
                            props.client?.company_code || '' }}</p>
                    </div>
                    <div style="margin-left: auto;">
                        <span
                            style="font-size: 12px; padding: 4px 12px; border-radius: 20px; border: 0.5px solid rgba(255,255,255,0.35);" class="bg-green-100 text-green-800 dark:bg-gray-800 dark:text-green-100">
                            <i class="ti ti-circle-check dark:text-white" style="font-size:13px; vertical-align:-1px; margin-right:4px;"
                                aria-hidden="true"></i>
                            {{ props.client?.status || '' }}
                        </span>
                    </div>
                </div>

                <!-- Fields Grid -->
                <div style="padding: 1.25rem 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0;" >

                    <!-- Email -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Email</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-mail" style="font-size:15px; color: #1D9E75;" aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.client?.company_email || '' }}</p>
                        </div>
                    </div>

                    <!-- Telepon -->
                    <div style="padding: 10px 0 10px 14px; border-bottom: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Telepon</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-phone" style="font-size:15px; color: #1D9E75;" aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.client?.company_phone || '' }}</p>
                        </div>
                    </div>

                    <!-- NPWP -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            NPWP</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-id-badge" style="font-size:15px; color: #1D9E75;" aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.client?.company_npwp || '' }}</p>
                        </div>
                    </div>

                    <!-- Kode Perusahaan -->
                    <div style="padding: 10px 0 10px 14px; border-bottom: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Kode perusahaan</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-hash" style="font-size:15px; color: #1D9E75;" aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.client?.company_code || '' }}</p>
                        </div>
                    </div>

                    <!-- Alamat (full width) -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); grid-column: span 2;">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Alamat</p>
                        <div style="display: flex; align-items: flex-start; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-map-pin" style="font-size:15px; color: #1D9E75; margin-top:1px;"
                                aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.client?.company_address || '' }}</p>
                        </div>
                    </div>

                    <!-- Kota -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Kota</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-building-skyscraper" style="font-size:15px; color: #1D9E75;"
                                aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.client?.company_city || '' }}</p>
                        </div>
                    </div>

                    <!-- Provinsi -->
                    <div style="padding: 10px 0 10px 14px; border-bottom: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Provinsi</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-map" style="font-size:15px; color: #1D9E75;" aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.client?.company_province || '' }}</p>
                        </div>
                    </div>

                    <!-- Kode Pos -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Kode pos</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-mailbox" style="font-size:15px; color: #1D9E75;" aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.client?.company_postal_code || '' }}</p>
                        </div>
                    </div>

                    <!-- Status -->
                    <div style="padding: 10px 0 10px 14px; border-bottom: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Status</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-toggle-right" style="font-size:15px; color: #1D9E75;"
                                aria-hidden="true"></i>
                            <span
                                style="font-size: 12px; background: #E1F5EE; color: #0F6E56; padding: 2px 10px; border-radius: 20px; font-weight: 500;">{{
                                props.client?.status || '' }}</span>
                        </div>
                    </div>

                    <!-- Tanggal Kontrak -->
                    <div style="padding: 10px 12px 10px 0; border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Tanggal kontrak</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-calendar" style="font-size:15px; color: #1D9E75;" aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.client?.contract_date || '' }}</p>
                        </div>
                    </div>

                    <!-- Kontrak Selesai -->
                    <div style="padding: 10px 0 10px 14px;">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Kontrak selesai</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <i class="ti ti-calendar-check" style="font-size:15px; color: #1D9E75;"
                                aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.client?.contract_done_date || '-' }}</p>
                        </div>
                    </div>

                </div>

                <!-- Member Section -->
                <div style="border-top: 0.5px solid var(--color-border-tertiary); padding: 1rem 1.5rem;" class="bg-gray-50 dark:bg-gray-800">
                    <div
                        style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i class="ti ti-users" style="font-size:16px; color: #1D9E75;" aria-hidden="true"></i>
                            <p style="margin: 0; font-size: 13px; font-weight: 500; color: var(--color-text-primary);">
                                Member</p>
                        </div>
                        <span
                            style="font-size: 12px; background: #E1F5EE; color: #0F6E56; padding: 3px 10px; border-radius: 20px; font-weight: 500;">{{
                                props.client.pics.length || '0' }} anggota</span>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <template v-for="(pic, index) in props.client.pics" :key="pic.id">
                            <div style="display: flex; align-items: center; gap: 8px; margin-right: 20px;">
                                <img v-if="pic.user?.avatar" :src="pic.user.avatar" :alt="pic.user.name"
                                    style="width:32px; height:32px; border-radius:50%; object-fit:cover;" />

                                <!-- fallback kalau tidak ada avatar -->
                                <div v-else
                                    style="width:32px; height:32px; border-radius:50%; background:#ccc; display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:500; color:#555;">
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