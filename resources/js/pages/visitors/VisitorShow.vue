<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    visitor: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Visitor Management',
                href: '/admin/visitors',
            },
        ],
    },
});

function formatDateTime(date: string | null) {
    if (!date) return '-'

    return new Date(date).toLocaleString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}
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
                                props.visitor?.company_name
                                    ?.split(' ')
                                    .map((word: string) => word[0])
                            .join('')
                            .slice(0, 4)
                            }}
                        </div>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: 18px; font-weight: 500;" class="dark:text-gray-900 text-white">{{
                            props.visitor?.name || '' }}</p>
                        <p style="margin: 0; font-size: 18px; font-weight: 500;" class="dark:text-gray-900 text-white">{{
                            props.visitor?.company_name || '' }}</p>
                    </div>
                    <div style="margin-left: auto;">
                        <span
                            style="font-size: 12px; padding: 4px 12px; border-radius: 20px; border: 0.5px solid rgba(255,255,255,0.35);" class="bg-green-100 text-green-800 dark:bg-gray-800 dark:text-green-100">
                            <i class="ti ti-circle-check dark:text-white" style="font-size:13px; vertical-align:-1px; margin-right:4px;"
                                aria-hidden="true"></i>
                            {{ props.visitor?.status || '' }}
                        </span>
                    </div>
                </div>

                <!-- Fields Grid -->
                <div style="padding: 1.25rem 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0;" >

                    <div class="md:col-span-2 my-4">
                        <hr>
                        <h2 class="font-semibold mt-4">Visitor Information</h2>
                    </div>

                    <!-- Nama -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Full Name</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.visitor?.name || '' }}</p>
                        </div>
                    </div>
                    <!-- Email -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Email</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.visitor?.email || '' }}</p>
                        </div>
                    </div>
                    <!-- notelp -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Phone</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.visitor?.phone || '' }}</p>
                        </div>
                    </div>
                    <!-- No Indentity -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            No Identity</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.visitor?.nik || '' }}</p>
                        </div>
                    </div>
                    <!-- Address -->
                    <div
                    class="md:col-span-2"
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Address</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.visitor?.address || '' }}</p>
                        </div>
                    </div>

                    <div class="md:col-span-2 my-4">
                        <hr>
                        <h2 class="font-semibold mt-4">Company Information</h2>
                    </div>

                    <!-- Company -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Company Name</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.visitor?.company_name || '' }}</p>
                        </div>
                    </div>
                    <!-- posituion -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Position</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.visitor?.position_in_company || '' }}</p>
                        </div>
                    </div>

                    <div class="md:col-span-2 my-4">
                        <hr>
                        <h2 class="font-semibold mt-4">Visit Information</h2>
                    </div>

                    <!-- type visit -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Type Visit</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.visitor?.type_of_visit || '' }}</p>
                        </div>
                    </div>
                    <!-- visit_purpose -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Visit Purpose</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.visitor?.visit_purpose || '' }}</p>
                        </div>
                    </div>
                    <!-- visit_note -->
                    <div
                    class="md:col-span-2"
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Visit Note</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{
                                props.visitor?.visit_note || '' }}</p>
                        </div>
                    </div>

                    <div class="md:col-span-2 my-4">
                        <hr>
                        <h2 class="font-semibold mt-4">Time Information</h2>
                    </div>
                    <!-- visit_date -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Visit Date</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">
                                {{ new Date(props.visitor.visit_date).toLocaleDateString('id-ID') }}</p>
                        </div>
                    </div>
                    <!-- checkin_time -->
                    <div
                        style="padding: 10px 12px 10px 0; border-bottom: 0.5px solid var(--color-border-tertiary); border-right: 0.5px solid var(--color-border-tertiary);">
                        <p
                            style="margin: 0; font-size: 11px; color: var(--color-text-tertiary); text-transform: uppercase; letter-spacing: 0.04em;">
                            Checkin Time</p>
                        <div style="display: flex; align-items: center; gap: 6px; margin-top: 4px;">
                            <p style="margin: 0; font-size: 13px; color: var(--color-text-primary);">{{ formatDateTime(props.visitor?.checkin_time) }}</p>
                        </div>
                    </div>
                    
                </div>

                <div class="p-4">
                    <div class="md:col-span-2 my-4">
                        <hr>
                        <h2 class="font-semibold mt-4">Visitor Information</h2>
                    </div>

                    <div v-if="visitor.visitor_attemps?.length" class="grid grid-cols-4 gap-4">
                        <div v-for="img in visitor.visitor_attemps" :key="img.id">
                            <img :src="'/storage/' + img.file" class="w-full" />
                            <span class="text-xs text-gray-500">{{ formatDateTime(img.created_at) }}</span>
                        </div>
                    </div>
                    <div v-else>
                        <p>No images found.</p>
                    </div>
                </div>


            </div>
        </div>
    </div>
</template>