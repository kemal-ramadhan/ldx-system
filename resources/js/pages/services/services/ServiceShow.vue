<script setup lang="ts">
import { formatNumber as formatMoney } from '@/lib/money';
import { Head, Link, router } from '@inertiajs/vue3';
import { formatItemTax } from '@/lib/tax';
import { ref } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import DeleteServiceDialog from '@/components/vanila/service/DeleteServiceDialog.vue';

import {
    Pencil,
    Ban,
    Trash2,
    Receipt,
    CheckCircle2,
    Mail,
} from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    service: any;
}>();

const isGenerating = ref(false);
const openGenerateDialog = ref(false);
const openDeleteDialog = ref(false);

/**
 * =========================================
 * STATUS ACTIONS (Activate / Suspend / Terminate)
 * =========================================
 */
type StatusAction = 'activate' | 'suspend' | 'terminate';

const statusActions: Record<StatusAction, {
    title: string;
    description: string;
    confirmLabel: string;
    variant: 'default' | 'destructive';
    buttonClass: string;
}> = {
    activate: {
        title: 'Aktifkan service ini?',
        description: 'Status service akan diubah menjadi Active.',
        confirmLabel: 'Activate',
        variant: 'default',
        buttonClass: 'bg-green-600 text-white hover:bg-green-700',
    },
    suspend: {
        title: 'Suspend service ini?',
        description: 'Status service akan diubah menjadi Suspended dan invoice tidak bisa di-generate sampai service diaktifkan kembali.',
        confirmLabel: 'Suspend',
        variant: 'default',
        buttonClass: 'bg-yellow-500 text-white hover:bg-yellow-600',
    },
    terminate: {
        title: 'Terminate service ini?',
        description: 'Service akan dihentikan dan invoice tidak bisa di-generate lagi untuk service ini.',
        confirmLabel: 'Terminate',
        variant: 'destructive',
        buttonClass: '',
    },
};

const pendingAction = ref<StatusAction | null>(null);
const openStatusDialog = ref(false);
const isUpdatingStatus = ref(false);

const openStatusAction = (action: StatusAction) => {
    pendingAction.value = action;
    openStatusDialog.value = true;
};

const activateService = () => openStatusAction('activate');
const suspendService = () => openStatusAction('suspend');
const terminateService = () => openStatusAction('terminate');

const confirmStatusAction = () => {
    if (!pendingAction.value) return;

    router.patch(
        `/admin/services/${props.service.id}/${pendingAction.value}`,
        {},
        {
            preserveScroll: true,
            onStart: () => (isUpdatingStatus.value = true),
            onFinish: () => {
                isUpdatingStatus.value = false;
                openStatusDialog.value = false;
            },
        }
    );
};

/**
 * =========================================
 * GENERATE INVOICE
 * =========================================
 */
const generateInvoice = () => {

    router.post(
        `/admin/services/${props.service.id}/generate-invoice`,
        {},
        {
            onStart: () => (isGenerating.value = true),
            onFinish: () => {
                isGenerating.value = false;
                openGenerateDialog.value = false;
            },
        }
    );
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
                                <span v-if="service.rack">{{ service.rack.name }}</span>
                            <span v-else
                                class="inline-flex rounded-full bg-muted px-2.5 py-0.5 text-xs font-medium text-muted-foreground">
                                Tanpa Rack
                            </span>
                            </p>
                        </div>
                    </div>
                    <p v-if="!service.rack"
                        class="mt-4 rounded-lg bg-muted px-3 py-2 text-xs text-muted-foreground">
                        Service ini <strong>tanpa rack</strong> (layanan non-colocation), invoice tetap dibuat seperti biasa.
                    </p>
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
                                        PPN
                                    </th>

                                    <th class="px-4 py-3">
                                        PPh 23
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
                                        Rp {{ formatMoney(Number(item.price)) }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ formatItemTax(item.ppn_enabled, item.ppn_percentage, item.ppn_amount) }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        {{ formatItemTax(item.pph23_enabled, item.pph23_percentage, item.pph23_amount) }}
                                    </td>

                                    <td class="px-4 py-3 font-medium">
                                        Rp {{ formatMoney(Number(item.subtotal)) }}
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
                                Rp {{ formatMoney(Number(service.monthly_total)) }}
                            </span>
                        </div>
                        
                        <div v-if="Number(service.ppn_amount) > 0" class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                PPN
                            </span>

                            <span class="font-semibold">
                                Rp {{ formatMoney(Number(service.ppn_amount)) }}
                            </span>
                        </div>
                        
                        <div v-if="Number(service.pph23_amount) > 0" class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                PPh 23
                            </span>

                            <span class="font-semibold">
                                Rp {{ formatMoney(Number(service.pph23_amount)) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">
                                Grand Total
                            </span>

                            <span class="font-semibold">
                                Rp {{ formatMoney(Number(service.total)) }}
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

                    <!-- Delete -->
                    <button @click="openDeleteDialog = true"
                        class="inline-flex items-center rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm text-red-700 hover:bg-red-100">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </button>

                    <div class="md:col-span-3">
                        <!-- Generate Invoice -->
                        <button v-if="['pending', 'active'].includes(service.status)" @click="openGenerateDialog = true"
                            :disabled="isGenerating"
                            class="inline-flex w-full justify-center items-center rounded-lg bg-primary px-4 py-2 text-sm text-white hover:bg-primary/90 disabled:opacity-60 dark:text-gray-900">
                            <Receipt class="mr-2 h-4 w-4" />
                            {{ isGenerating ? 'Generating & Sending...' : 'Generate Invoice' }}
                        </button>

                        <p v-if="service.status === 'pending'" class="mt-2 text-center text-xs text-muted-foreground">
                            Alur: Generate Invoice → client bayar → Mark as Paid → Activate
                        </p>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <DeleteServiceDialog v-model:open="openDeleteDialog" :service="service" />

    <!-- STATUS ACTION CONFIRMATION -->
    <Dialog :open="openStatusDialog" @update:open="(val) => { if (!isUpdatingStatus) openStatusDialog = val }">
        <DialogContent v-if="pendingAction">
            <DialogHeader class="space-y-3">
                <DialogTitle>
                    {{ statusActions[pendingAction].title }}
                </DialogTitle>
                <DialogDescription>
                    {{ statusActions[pendingAction].description }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="pendingAction === 'activate' && !service.has_paid_invoice"
                class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900/50 dark:bg-amber-950/30 dark:text-amber-300">
                Belum ada invoice yang <strong>Paid</strong> untuk service ini.
                Pastikan client sudah membayar sebelum mengaktifkan layanan.
            </div>

            <div class="grid gap-2 rounded-lg border p-4 text-sm dark:border-gray-700">
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Client</span>
                    <span class="font-medium text-right">{{ service.client?.company_name || '-' }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Status saat ini</span>
                    <span class="font-medium capitalize">{{ service.status }}</span>
                </div>
            </div>

            <DialogFooter class="gap-2">
                <Button variant="secondary" :disabled="isUpdatingStatus" @click="openStatusDialog = false">
                    Batal
                </Button>
                <Button :variant="statusActions[pendingAction].variant" :disabled="isUpdatingStatus"
                    :class="statusActions[pendingAction].buttonClass" @click="confirmStatusAction">
                    <Spinner v-if="isUpdatingStatus" class="mr-2 h-4 w-4" />
                    {{ statusActions[pendingAction].confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>

    <!-- GENERATE INVOICE CONFIRMATION -->
    <Dialog :open="openGenerateDialog" @update:open="(val) => { if (!isGenerating) openGenerateDialog = val }">
        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>
                    Generate invoice untuk service ini?
                </DialogTitle>
                <DialogDescription>
                    Invoice akan dibuat dengan status <strong>Sent</strong> dan langsung dikirim ke email client.
                    <template v-if="service.status === 'pending'">
                        <br><br>
                        Service tetap <strong>Pending</strong>. Klik <strong>Activate</strong> setelah invoice dibayar (Paid)
                        dan layanan sudah diinfokan ke client.
                    </template>
                </DialogDescription>
            </DialogHeader>

            <div class="grid gap-2 rounded-lg border p-4 text-sm dark:border-gray-700">
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Client</span>
                    <span class="font-medium text-right">{{ service.client?.company_name || '-' }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Email</span>
                    <span class="inline-flex items-center gap-1 font-medium text-right">
                        <Mail class="h-3.5 w-3.5 text-muted-foreground" />
                        {{ service.client?.company_email || '-' }}
                    </span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Due Date</span>
                    <span class="font-medium">{{ service.next_due_date || '-' }}</span>
                </div>
                <div class="flex justify-between gap-4 border-t pt-2 dark:border-gray-700">
                    <span class="text-muted-foreground">Total</span>
                    <span class="font-semibold">Rp {{ formatMoney(Number(service.total)) }}</span>
                </div>
            </div>

            <DialogFooter class="gap-2">
                <Button variant="secondary" :disabled="isGenerating" @click="openGenerateDialog = false">
                    Batal
                </Button>
                <Button :disabled="isGenerating" @click="generateInvoice"
                    class="bg-primary text-white hover:bg-primary/90 dark:text-gray-900">
                    <Spinner v-if="isGenerating" class="mr-2 h-4 w-4" />
                    {{ isGenerating ? 'Generating & Sending...' : 'Generate & Kirim' }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>