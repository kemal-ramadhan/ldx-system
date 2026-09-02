<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

import { computed } from 'vue';

import {
    Plus,
    Trash2,
} from 'lucide-vue-next';

type ServiceItem = {
    id: number | null;
    product_id: number | null;
    quantity: number;
    price: number;
    subtotal: number;
};

type ServiceForm = {
    client_id: number | null;
    rack_id: number | null;
    name: string;
    description: string;
    billing_cycle: string;
    start_date: string;
    end_date: string;
    next_due_date: string;
    ppn_enabled: boolean;
    ppn_percentage: number;
    pph23_enabled: boolean;
    pph23_percentage: number;
    status: string;
    items: ServiceItem[];
};

const props = defineProps<{
    title: string;
    service: any;
    clients: any[];
    racks: any[];
    products: any[];
}>();

const form = useForm<ServiceForm>({
    client_id: props.service.client_id ?? null,
    rack_id: props.service.rack_id ?? null,

    name: props.service.name ?? '',
    description: props.service.description ?? '',

    billing_cycle: props.service.billing_cycle ?? 'monthly',

    start_date: props.service.start_date ?? '',
    end_date: props.service.end_date ?? '',
    next_due_date: props.service.next_due_date ?? '',

    ppn_enabled: Boolean(props.service.ppn_enabled),
    ppn_percentage: Number(props.service.ppn_percentage ?? 11),

    pph23_enabled: Boolean(props.service.pph23_enabled),
    pph23_percentage: Number(props.service.pph23_percentage ?? 2),

    status: props.service.status ?? 'pending',

    items: (props.service.service_items ?? []).map((item: any) => ({
        id: item.id,
        product_id: item.product_id ?? null,
        quantity: Number(item.quantity ?? 1),
        price: Number(item.price ?? 0),
        subtotal: Number(
            item.subtotal ??
            (Number(item.quantity ?? 1) * Number(item.price ?? 0))
        ),
    })),
});

const addItem = () => {
    form.items.push({
        id: null,
        product_id: null,
        quantity: 1,
        price: 0,
        subtotal: 0,
    });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const updateProduct = (index: number) => {
    const selected = props.products.find(
        (product) => product.id == form.items[index].product_id
    );

    if (selected) {
        form.items[index].price = Number(selected.base_price);

        calculateSubtotal(index);
    }
};

const calculateSubtotal = (index: number) => {
    form.items[index].subtotal =
        Number(form.items[index].quantity) *
        Number(form.items[index].price);
};

/**
 * =========================================
 * TAX SUMMARY
 * =========================================
 */

const subtotal = computed(() => {
    return form.items.reduce((total, item) => {
        return total +
            Number(item.quantity) *
            Number(item.price);
    }, 0);
});

const ppnAmount = computed(() => {
    if (!form.ppn_enabled) {
        return 0;
    }

    return subtotal.value *
        (Number(form.ppn_percentage) / 100);
});

const pph23Amount = computed(() => {
    if (!form.pph23_enabled) {
        return 0;
    }

    return subtotal.value *
        (Number(form.pph23_percentage) / 100);
});

/**
 * PPN menambah invoice
 * PPh23 mengurangi pembayaran
 */
const grandTotal = computed(() => {
    return (
        subtotal.value +
        ppnAmount.value -
        pph23Amount.value
    );
});

const updateService = () => {
    form.put(`/admin/services/${props.service.id}`);
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Services Management',
                href: '/admin/services',
            },
            {
                title: 'Edit Service',
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
        <div>
            <h1 class="text-2xl font-bold">
                Edit Service
            </h1>

            <p class="text-sm text-muted-foreground">
                Update service information and billing items
            </p>
        </div>

        <Form @submit.prevent="updateService" v-slot="{ errors }" class="flex flex-col gap-4">

            <!-- ========================================= -->
            <!-- BASIC INFORMATION -->
            <!-- ========================================= -->

            <div class="rounded-2xl border p-5">

                <h2 class="mb-4 text-lg font-semibold">
                    Basic Information
                </h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <!-- Client -->
                    <div class="grid gap-2">
                        <Label>
                            Client
                            <span class="text-red-500">*</span>
                        </Label>

                        <select v-model="form.client_id"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white dark:text-gray-900">
                            <option :value="null">
                                Select Client
                            </option>

                            <option v-for="client in clients" :key="client.id" :value="client.id">
                                {{ client.company_name }}
                            </option>
                        </select>

                        <InputError :message="errors.client_id" />
                    </div>

                    <!-- Rack -->
                    <div class="grid gap-2">
                        <Label>
                            Rack
                            <span class="text-red-500">*</span>
                        </Label>

                        <select v-model="form.rack_id"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white dark:text-gray-900">
                            <option :value="null">
                                Select Rack
                            </option>

                            <option v-for="rack in racks" :key="rack.id" :value="rack.id">
                                {{ rack.name }}
                            </option>
                        </select>

                        <InputError :message="errors.rack_id" />
                    </div>

                    <!-- Service Name -->
                    <div class="grid gap-2">
                        <Label>
                            Service Name
                            <span class="text-red-500">*</span>
                        </Label>

                        <Input v-model="form.name" type="text" placeholder="Rack R01 PT ABC" />

                        <InputError :message="errors.name" />
                    </div>

                    <!-- Billing Cycle -->
                    <div class="grid gap-2">
                        <Label>
                            Billing Cycle
                        </Label>

                        <select v-model="form.billing_cycle"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white dark:text-gray-900">
                            <option value="monthly">
                                Monthly
                            </option>

                            <option value="quarterly">
                                Quarterly
                            </option>

                            <option value="yearly">
                                Yearly
                            </option>
                        </select>

                        <InputError :message="errors.billing_cycle" />
                    </div>

                    <!-- Start Date -->
                    <div class="grid gap-2">
                        <Label>
                            Start Date
                        </Label>

                        <Input v-model="form.start_date" type="date" />

                        <InputError :message="errors.start_date" />
                    </div>

                    <!-- End Date -->
                    <div class="grid gap-2">
                        <Label>
                            End Date
                        </Label>

                        <Input v-model="form.end_date" type="date" />

                        <InputError :message="errors.end_date" />
                    </div>

                    <!-- Next Due -->
                    <div class="grid gap-2">
                        <Label>
                            Next Due Date
                        </Label>

                        <Input v-model="form.next_due_date" type="date" />

                        <InputError :message="errors.next_due_date" />
                    </div>

                    <!-- Status -->
                    <div class="grid gap-2">
                        <Label>
                            Status
                        </Label>

                        <select v-model="form.status"
                            class="rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white dark:text-gray-900">
                            <option value="pending">
                                Pending
                            </option>

                            <option value="active">
                                Active
                            </option>

                            <option value="suspended">
                                Suspended
                            </option>

                            <option value="terminated">
                                Terminated
                            </option>
                        </select>

                        <InputError :message="errors.status" />
                    </div>

                    <!-- Description -->
                    <div class="grid gap-2 md:col-span-2">
                        <Label>
                            Description
                        </Label>

                        <textarea v-model="form.description"
                            class="h-28 resize-none rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                            placeholder="Service description..."></textarea>

                        <InputError :message="errors.description" />
                    </div>

                </div>
            </div>

            <!-- ========================================= -->
            <!-- SERVICE ITEMS -->
            <!-- ========================================= -->

            <div class="rounded-2xl border p-5">

                <div class="mb-4 flex items-center justify-between">

                    <div>
                        <h2 class="text-lg font-semibold">
                            Service Items
                        </h2>

                        <p class="text-sm text-muted-foreground">
                            Manage service billing items
                        </p>
                    </div>

                    <Button type="button" @click="addItem">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Item
                    </Button>

                </div>

                <div class="flex flex-col gap-4">

                    <div v-for="(item, index) in form.items" :key="item.id ?? index"
                        class="grid grid-cols-1 gap-4 rounded-xl border p-4 md:grid-cols-12">

                        <!-- Product -->
                        <div class="grid gap-2 md:col-span-4">

                            <Label>
                                Product
                            </Label>

                            <select v-model="item.product_id" @change="updateProduct(index)"
                                class="rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white dark:text-gray-900">
                                <option :value="null">
                                    Select Product
                                </option>

                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>

                        </div>

                        <!-- Quantity -->
                        <div class="grid gap-2 md:col-span-2">

                            <Label>
                                Quantity
                            </Label>

                            <Input v-model="item.quantity" type="number" min="1" @input="calculateSubtotal(index)" />

                        </div>

                        <!-- Price -->
                        <div class="grid gap-2 md:col-span-2">

                            <Label>
                                Price
                            </Label>

                            <Input v-model="item.price" type="number" min="0" @input="calculateSubtotal(index)" />

                        </div>

                        <!-- Subtotal -->
                        <div class="grid gap-2 md:col-span-3">

                            <Label>
                                Subtotal
                            </Label>

                            <Input :model-value="Number(
                                item.quantity * item.price
                            ).toLocaleString('id-ID')
                                " readonly />

                        </div>

                        <!-- Remove -->
                        <div class="flex items-end md:col-span-1">

                            <button type="button" @click="removeItem(index)"
                                class="rounded-lg p-2 text-red-500 hover:bg-red-50">
                                <Trash2 class="h-4 w-4" />
                            </button>

                        </div>

                    </div>

                </div>
            </div>

            <!-- ========================================= -->
            <!-- TAX CONFIGURATION -->
            <!-- ========================================= -->

            <div class="rounded-2xl border p-5">

                <div class="mb-5">

                    <h2 class="text-lg font-semibold">
                        Tax Configuration
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Configure applicable taxes for invoices generated from this service.
                    </p>

                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                    <!-- PPN -->
                    <div class="rounded-xl border p-4">

                        <div class="flex items-start justify-between gap-4">

                            <div>
                                <h3 class="font-medium">
                                    PPN
                                </h3>

                                <p class="mt-1 text-sm text-muted-foreground">
                                    Apply Value Added Tax to this service.
                                </p>
                            </div>

                            <!-- Toggle -->
                            <button type="button" @click="form.ppn_enabled = !form.ppn_enabled" :class="[
                                'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors',
                                form.ppn_enabled
                                    ? 'bg-primary'
                                    : 'bg-gray-300 dark:bg-gray-700'
                            ]">
                                <span :class="[
                                    'inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform',
                                    form.ppn_enabled
                                        ? 'translate-x-5'
                                        : 'translate-x-0.5'
                                ]"></span>
                            </button>

                        </div>

                        <!-- PPN Percentage -->
                        <div v-if="form.ppn_enabled" class="mt-4">

                            <Label>
                                PPN Percentage
                            </Label>

                            <div class="relative mt-2">

                                <Input v-model="form.ppn_percentage" type="number" min="0" max="100" step="0.01"
                                    placeholder="11" class="pr-10" />

                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">
                                    %
                                </span>

                            </div>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Example: 11%
                            </p>

                            <InputError :message="form.errors.ppn_percentage" />

                        </div>

                        <!-- Disabled -->
                        <div v-else
                            class="mt-4 rounded-lg bg-gray-50 px-3 py-2 text-sm text-muted-foreground dark:bg-gray-900">
                            PPN is disabled for this service.
                        </div>

                    </div>

                    <!-- PPh 23 -->
                    <div class="rounded-xl border p-4">

                        <div class="flex items-start justify-between gap-4">

                            <div>
                                <h3 class="font-medium">
                                    PPh 23
                                </h3>

                                <p class="mt-1 text-sm text-muted-foreground">
                                    Apply withholding tax for this service.
                                </p>
                            </div>

                            <!-- Toggle -->
                            <button type="button" @click="form.pph23_enabled = !form.pph23_enabled" :class="[
                                'relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors',
                                form.pph23_enabled
                                    ? 'bg-primary'
                                    : 'bg-gray-300 dark:bg-gray-700'
                            ]">
                                <span :class="[
                                    'inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform',
                                    form.pph23_enabled
                                        ? 'translate-x-5'
                                        : 'translate-x-0.5'
                                ]"></span>
                            </button>

                        </div>

                        <!-- PPh 23 Percentage -->
                        <div v-if="form.pph23_enabled" class="mt-4">

                            <Label>
                                PPh 23 Percentage
                            </Label>

                            <div class="relative mt-2">

                                <Input v-model="form.pph23_percentage" type="number" min="0" max="100" step="0.01"
                                    placeholder="2" class="pr-10" />

                                <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">
                                    %
                                </span>

                            </div>

                            <p class="mt-1 text-xs text-muted-foreground">
                                Example: 2%
                            </p>

                            <InputError :message="form.errors.pph23_percentage" />

                        </div>

                        <!-- Disabled -->
                        <div v-else
                            class="mt-4 rounded-lg bg-gray-50 px-3 py-2 text-sm text-muted-foreground dark:bg-gray-900">
                            PPh 23 is disabled for this service.
                        </div>

                    </div>

                </div>
            </div>

            <!-- ========================================= -->
            <!-- INVOICE SUMMARY -->
            <!-- ========================================= -->

            <div class="rounded-2xl border p-5">

                <div class="mb-4">

                    <h2 class="text-lg font-semibold">
                        Invoice Summary
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Summary of service price and applicable taxes
                    </p>

                </div>

                <div class="flex flex-col gap-3">

                    <!-- Subtotal -->
                    <div class="flex items-center justify-between">

                        <span class="text-sm text-muted-foreground">
                            Subtotal
                        </span>

                        <span class="font-medium">
                            Rp {{ subtotal.toLocaleString('id-ID') }}
                        </span>

                    </div>

                    <!-- PPN -->
                    <div v-if="form.ppn_enabled" class="flex items-center justify-between">

                        <div class="flex items-center gap-2">

                            <span class="text-sm text-muted-foreground">
                                PPN
                            </span>

                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium dark:bg-gray-800">
                                {{ form.ppn_percentage }}%
                            </span>

                        </div>

                        <span class="font-medium">
                            + Rp {{ ppnAmount.toLocaleString('id-ID') }}
                        </span>

                    </div>

                    <!-- PPh 23 -->
                    <div v-if="form.pph23_enabled" class="flex items-center justify-between">

                        <div class="flex items-center gap-2">

                            <span class="text-sm text-muted-foreground">
                                PPh 23
                            </span>

                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium dark:bg-gray-800">
                                {{ form.pph23_percentage }}%
                            </span>

                        </div>

                        <span class="font-medium">
                            - Rp {{ pph23Amount.toLocaleString('id-ID') }}
                        </span>

                    </div>

                    <!-- Divider -->
                    <div class="my-2 border-t"></div>

                    <!-- Grand Total -->
                    <div class="flex items-center justify-between">

                        <div>
                            <p class="text-lg font-semibold">
                                Grand Total
                            </p>

                            <p class="text-xs text-muted-foreground">
                                Final amount after applicable taxes
                            </p>
                        </div>

                        <span class="text-2xl font-bold">
                            Rp {{ grandTotal.toLocaleString('id-ID') }}
                        </span>

                    </div>

                </div>
            </div>

            <!-- ========================================= -->
            <!-- ACTIONS -->
            <!-- ========================================= -->

            <div class="flex items-center justify-end gap-3">

                <Button type="button" variant="outline" @click="$inertia.visit('/admin/services')">
                    Cancel
                </Button>

                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />

                    {{
                        form.processing
                            ? 'Updating...'
                            : 'Update Service'
                    }}
                </Button>

            </div>

        </Form>

    </div>
</template>