<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { computed } from 'vue';

import ItemTaxFields from '@/components/ItemTaxFields.vue';
import { defaultItemTax, taxSummary } from '@/lib/tax';

import {
    Plus,
    Trash2,
} from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    clients: any[];
    racks: any[];
    products: any[];
}>();

const form = useForm({
    client_id: '',
    rack_id: '',

    name: '',
    description: '',

    billing_cycle: 'monthly',

    start_date: '',
    end_date: '',
    next_due_date: '',

    status: 'pending',

    items: [
        {
            product_id: '',
            qty: 1,
            unit_price: 0,
            subtotal: 0,
            ...defaultItemTax(),
        },
    ],
});

const addItem = () => {
    form.items.push({
        product_id: '',
        qty: 1,
        unit_price: 0,
        subtotal: 0,
        ...defaultItemTax(),
    });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const updateProduct = (index: number) => {

    const selected = props.products.find(
        (item) => item.id == form.items[index].product_id
    );

    if (selected) {

        form.items[index].unit_price = Number(selected.base_price);

        form.items[index].subtotal =
            Number(form.items[index].qty) *
            Number(selected.base_price);
    }
};

const updateSubtotal = (index: number) => {

    form.items[index].subtotal =
        Number(form.items[index].qty) *
        Number(form.items[index].unit_price);
};

/**
 * =========================================
 * TAX SUMMARY
 * =========================================
 */

const summary = computed(() =>
    taxSummary(
        form.items.map((item) => ({
            subtotal: Number(item.qty) * Number(item.unit_price),
            tax: item,
        })),
    ),
);

const createService = () => {
    form.post('/admin/services');
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Services Management',
                href: '/admin/services',
            },
            {
                title: 'Create Service',
                href: '/admin/services/create',
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
                Create Service
            </h1>

            <p class="text-sm text-muted-foreground">
                Create a new client service
            </p>
        </div>

        <Form @submit.prevent="createService" v-slot="{ errors }" class="flex flex-col gap-4">

            <!-- Basic Information -->
            <div class="rounded-xl border p-5">

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
                            <option value="">
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
                            <option value="">
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
                    <div class="grid gap-2 md:col-span-2">
                        <Label>
                            Next Due Date
                        </Label>

                        <Input v-model="form.next_due_date" type="date" />

                        <InputError :message="errors.next_due_date" />
                    </div>



                    <!-- Status -->
                    <div class="grid gap-2 md:col-span-2">
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
                    </div>

                    <!-- Description -->
                    <div class="grid gap-2 md:col-span-2">
                        <Label>
                            Description
                        </Label>

                        <textarea v-model="form.description"
                            class="h-28 resize-none rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none"
                            placeholder="Service description..." />

                        <InputError :message="errors.description" />
                    </div>
                </div>
            </div>

            <!-- Service Items -->
            <div class="rounded-xl border p-5">

                <div class="mb-4 flex items-center justify-between">

                    <div>
                        <h2 class="text-lg font-semibold">
                            Service Items
                        </h2>

                        <p class="text-sm text-muted-foreground">
                            Add products to this service
                        </p>
                    </div>

                    <Button type="button" @click="addItem">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Item
                    </Button>
                </div>

                <div class="flex flex-col gap-4">

                    <div v-for="(item, index) in form.items" :key="index"
                        class="grid grid-cols-1 gap-4 rounded-xl border p-4 md:grid-cols-12">

                        <!-- Product -->
                        <div class="grid gap-2 md:col-span-4">
                            <Label>
                                Product
                            </Label>

                            <select v-model="item.product_id" @change="updateProduct(index)"
                                class="rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white dark:text-gray-900">
                                <option value="">
                                    Select Product
                                </option>

                                <option v-for="product in products" :key="product.id" :value="product.id">
                                    {{ product.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Qty -->
                        <div class="grid gap-2 md:col-span-2">
                            <Label>
                                Qty
                            </Label>

                            <Input v-model="item.qty" type="number" @input="updateSubtotal(index)" />
                        </div>

                        <!-- Price -->
                        <div class="grid gap-2 md:col-span-2">
                            <Label>
                                Unit Price
                            </Label>

                            <Input v-model="item.unit_price" type="number" @input="updateSubtotal(index)" />
                        </div>

                        <!-- Subtotal -->
                        <div class="grid gap-2 md:col-span-3">
                            <Label>
                                Subtotal
                            </Label>

                            <Input :model-value="Number(item.subtotal).toLocaleString('id-ID')" readonly />
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
                            <ItemTaxFields :subtotal="Number(item.qty) * Number(item.unit_price)"
                                v-model:ppn-enabled="item.ppn_enabled" v-model:ppn-percentage="item.ppn_percentage"
                                v-model:pph23-enabled="item.pph23_enabled"
                                v-model:pph23-percentage="item.pph23_percentage" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="rounded-xl border p-5">
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
                            Rp {{
                                summary.subtotal.toLocaleString('id-ID')
                            }}
                        </span>
                    </div>
                    <!-- PPN -->
                    <div v-if="summary.ppn > 0" class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">
                            PPN
                        </span>
                        <span class="font-medium">
                            + Rp {{
                                summary.ppn.toLocaleString('id-ID')
                            }}
                        </span>
                    </div>
                    <!-- PPh 23 -->
                    <div v-if="summary.pph23 > 0" class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">
                            PPh 23
                        </span>
                        <span class="font-medium">
                            - Rp {{
                                summary.pph23.toLocaleString('id-ID')
                            }}
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
                            Rp {{
                                summary.total.toLocaleString('id-ID')
                            }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">

                <Button type="button" variant="outline" @click="$inertia.visit('/admin/services')">
                    Cancel
                </Button>

                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />

                    {{
                        form.processing
                            ? 'Creating...'
                            : 'Create Service'
                    }}
                </Button>
            </div>
        </Form>
    </div>
</template>