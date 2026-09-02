<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

import {
    Pencil,
    Ban,
    Trash2,
    Receipt,
    CheckCircle2
} from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    service: any;
}>();

/**
 * =========================================
 * ACTIVATE SERVICE
 * =========================================
 */
const activateService = () => {

    if (
        confirm('Activate this service?')
    ) {

        router.patch(
            `/admin/services/${props.service.id}/activate`
        );
    }
};

/**
 * =========================================
 * SUSPEND SERVICE
 * =========================================
 */
const suspendService = () => {

    if (
        confirm('Suspend this service?')
    ) {

        router.patch(
            `/admin/services/${props.service.id}/suspend`
        );
    }
};

/**
 * =========================================
 * TERMINATE SERVICE
 * =========================================
 */
const terminateService = () => {

    if (
        confirm(
            'Terminate this service permanently?'
        )
    ) {

        router.patch(
            `/admin/services/${props.service.id}/terminate`
        );
    }
};

/**
 * =========================================
 * GENERATE INVOICE
 * =========================================
 */
const generateInvoice = () => {

    if (
        confirm(
            'Generate invoice for this service?'
        )
    ) {

        router.post(
            `/admin/services/${props.service.id}/generate-invoice`
        );
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Services Management',
                href: '/admin/services',
            },
            {
                title: 'Service Detail',
                href: '#',
            },
        ],
    },
});
</script>

<template>

    <Head :title="title" />

    <div class="flex flex-col gap-4 p-4">

        <!-- Header -->
        <div class="grid grid-cols-1 gap-4">

            <div>
                <div class="flex items-center gap-3">

                    <h1 class="text-2xl font-bold">
                        {{ service.name }}
                    </h1>

                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-medium" :class="{
                        'bg-green-100 text-green-700':
                            service.status === 'active',

                        'bg-yellow-100 text-yellow-700':
                            service.status === 'pending',

                        'bg-orange-100 text-orange-700':
                            service.status === 'suspended',

                        'bg-red-100 text-red-700':
                            service.status === 'terminated',
                    }">
                        {{ service.status }}
                    </span>
                </div>

                <p class="mt-1 text-sm text-muted-foreground">
                    {{ service.code }}
                </p>
            </div>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">

            <!-- Left -->
            <div class="flex flex-col gap-4 lg:col-span-2">

                <!-- Basic Information -->
                <div class="rounded-2xl border p-5">

                    <h2 class="mb-4 text-lg font-semibold">
                        Basic Information
                    </h2>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                        <div>
                            <p class="text-sm text-muted-foreground">
                                Client
                            </p>

                            <p class="font-medium">
                                {{ service.client?.company_name }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">
                                Billing Cycle
                            </p>

                            <p class="font-medium capitalize">
                                {{ service.billing_cycle }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">
                                Start Date
                            </p>

                            <p class="font-medium">
                                {{ service.start_date }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">
                                End Date
                            </p>

                            <p class="font-medium">
                                {{ service.end_date || '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">
                                Next Due Date
                            </p>

                            <p class="font-medium">
                                {{ service.next_due_date }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Rack Information -->
                <div class="rounded-2xl border p-5">

                    <h2 class="mb-4 text-lg font-semibold">
                        Rack Information
                    </h2>

                    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

                        <div>
                            <p class="text-sm text-muted-foreground">
                                Location
                            </p>

                            <p class="font-medium">
                                {{
                                    service.rack?.room
                                        ?.location_data_center?.name || '-'
                                }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">
                                Room
                            </p>

                            <p class="font-medium">
                                {{ service.rack?.room?.name || '-' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-muted-foreground">
                                Rack
                            </p>

                            <p class="font-medium">
                                {{ service.rack?.name || '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Service Items -->
                <div class="rounded-2xl border p-5">

                    <div class="mb-4 flex items-center justify-between">

                        <div>
                            <h2 class="text-lg font-semibold">
                                Service Items
                            </h2>

                            <p class="text-sm text-muted-foreground">
                                Products included in this service
                            </p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">

                        <table class="min-w-full divide-y divide-gray-200">

                            <thead>
                                <tr class="text-left text-sm font-semibold text-muted-foreground">
                                    <th class="px-4 py-3">
                                        Product
                                    </th>

                                    <th class="px-4 py-3">
                                        Qty
                                    </th>

                                    <th class="px-4 py-3">
                                        Unit Price
                                    </th>

                                    <th class="px-4 py-3">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>

                            <tbody>

                                <tr v-for="item in service.service_items" :key="item.id" class="border-t">
                                    <td class="px-4 py-3">
                                        {{ item.product?.name }}
                                    </td>

                                    <td class="px-4 py-3">
                                        {{ item.quantity }}
                                    </td>

                                    <td class="px-4 py-3">
                                        Rp {{ Number(item.price).toLocaleString('id-ID') }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        Rp {{ Number(item.subtotal).toLocaleString('id-ID') }}
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Description -->
                <div class="rounded-2xl border p-5">

                    <h2 class="mb-4 text-lg font-semibold">
                        Description
                    </h2>

                    <p class="text-sm leading-relaxed text-muted-foreground">
                        {{ service.description || '-' }}
                    </p>
                </div>
            </div>

            <!-- Right -->
            <div class="flex flex-col gap-4">

                <!-- Summary -->
                <div class="rounded-2xl border p-5">

                    <h2 class="mb-4 text-lg font-semibold">
                        Billing Summary
                    </h2>

                    <div class="flex flex-col gap-4">

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                Monthly Total
                            </span>

                            <span class="font-semibold">
                                Rp {{ Number(service.monthly_total).toLocaleString('id-ID') }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                PPn ({{ service.ppn_percentage }}%)
                            </span>

                            <span class="font-semibold">
                                Rp {{ Number(service.ppn_amount).toLocaleString('id-ID') }}
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                PPh23 ({{ service.pph23_percentage }}%)
                            </span>

                            <span class="font-semibold">
                                Rp {{ Number(service.pph23_amount).toLocaleString('id-ID') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                Grand Total
                            </span>

                            <span class="font-semibold">
                                Rp {{ Number(service.total).toLocaleString('id-ID') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                Billing Cycle
                            </span>

                            <span class="font-semibold capitalize">
                                {{ service.billing_cycle }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                Status
                            </span>

                            <span class="font-semibold capitalize">
                                {{ service.status }}
                            </span>
                        </div>
                    </div>
                </div>


                <!-- Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                    <!-- Edit -->
                    <Link :href="`/admin/services/${service.id}/edit`"
                        class="inline-flex items-center rounded-lg border px-4 py-2 text-sm hover:bg-muted">
                        <Pencil class="mr-2 h-4 w-4" />
                        Edit
                    </Link>

                    <!-- Activate -->
                    <button v-if="service.status !== 'active'" @click="activateService"
                        class="inline-flex items-center rounded-lg border border-green-200 bg-green-50 px-4 py-2 text-sm text-green-700 hover:bg-green-100">
                        <CheckCircle2 class="mr-2 h-4 w-4" />
                        Activate
                    </button>

                    <!-- Suspend -->
                    <button v-if="service.status === 'active'" @click="suspendService"
                        class="inline-flex items-center rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-2 text-sm text-yellow-700 hover:bg-yellow-100">
                        <Ban class="mr-2 h-4 w-4" />
                        Suspend
                    </button>

                    <!-- Terminate -->
                    <button v-if="service.status !== 'terminated'" @click="terminateService"
                        class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700 hover:bg-red-100">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Terminate
                    </button>

                    <div class="md:col-span-3">
                        <!-- Generate Invoice -->
                        <button @click="generateInvoice"
                            class="inline-flex w-full justify-center items-center rounded-lg bg-primary px-4 py-2 text-sm text-white hover:bg-primary/90 dark:text-gray-900">
                            <Receipt class="mr-2 h-4 w-4" />
                            Generate Invoice
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>
</template>