<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

import { computed } from 'vue';

import ItemTaxFields from '@/components/ItemTaxFields.vue';
import { defaultItemTax, itemTaxFrom, taxSummary, type ItemTax } from '@/lib/tax';

import {
    Plus,
    Trash2,
} from 'lucide-vue-next';

type ServiceItem = ItemTax & {
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
        ...itemTaxFrom(item),
    })),
});

const addItem = () => {
    form.items.push({
        id: null,
        product_id: null,
        quantity: 1,
        price: 0,
        subtotal: 0,
        ...defaultItemTax(),
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

const summary = computed(() =>
    taxSummary(
        form.items.map((item) => ({
            subtotal: Number(item.quantity) * Number(item.price),
            tax: item,
        })),
    ),
);

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

                        <!-- Tax per item -->
                        <div class="md:col-span-12">
                            <ItemTaxFields :subtotal="Number(item.quantity) * Number(item.price)"
                                v-model:ppn-enabled="item.ppn_enabled" v-model:ppn-percentage="item.ppn_percentage"
                                v-model:pph23-enabled="item.pph23_enabled"
                                v-model:pph23-percentage="item.pph23_percentage" />
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
                        Summary of service price and taxes from each item
                    </p>

                </div>

                <div class="flex flex-col gap-3">

                    <!-- Subtotal -->
                    <div class="flex items-center justify-between">

                        <span class="text-sm text-muted-foreground">
                            Subtotal
                        </span>

                        <span class="font-medium">
                            Rp {{ summary.subtotal.toLocaleString('id-ID') }}
                        </span>

                    </div>

                    <!-- PPN -->
                    <div v-if="summary.ppn > 0" class="flex items-center justify-between">

                        <span class="text-sm text-muted-foreground">
                            PPN
                        </span>

                        <span class="font-medium">
                            + Rp {{ summary.ppn.toLocaleString('id-ID') }}
                        </span>

                    </div>

                    <!-- PPh 23 -->
                    <div v-if="summary.pph23 > 0" class="flex items-center justify-between">

                        <span class="text-sm text-muted-foreground">
                            PPh 23
                        </span>

                        <span class="font-medium">
                            - Rp {{ summary.pph23.toLocaleString('id-ID') }}
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
                            Rp {{ summary.total.toLocaleString('id-ID') }}
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