<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    title: string;
    categories: any;
}>();

const form = useForm({
    product_category_id: '',
    code: '',
    name: '',
    description: '',
    unit: '',
    base_price: '',
    billing_type: 'recurring',
    status: 'active',
});

const createProduct = () => {
    form.post('/admin/products');
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Products Management',
                href: '/admin/products',
            },
            {
                title: 'Create Product',
                href: '/admin/products/create',
            },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-bold">Create Product</h1>

        <Form
            @submit.prevent="createProduct"
            v-slot="{ errors }"
            class="flex flex-col gap-3"
        >
            <div
                class="grid grid-cols-1 md:grid-cols-2 gap-4 w-full rounded-xl border p-4"
            >

                <!-- Product Code -->
                <div class="grid gap-2">
                    <Label for="code">
                        Product Code
                        <span class="text-red-500">*</span>
                    </Label>

                    <Input
                        id="code"
                        v-model="form.code"
                        type="text"
                        placeholder="RACK-1U"
                    />

                    <InputError :message="errors.code" />
                </div>

                <!-- Product Name -->
                <div class="grid gap-2">
                    <Label for="name">
                        Product Name
                        <span class="text-red-500">*</span>
                    </Label>

                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        placeholder="Rack Space 1U"
                    />

                    <InputError :message="errors.name" />
                </div>

                <!-- Category -->
                <div class="grid gap-2">
                    <Label>
                        Category
                        <span class="text-red-500">*</span>
                    </Label>

                    <select
                        v-model="form.product_category_id"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white dark:text-gray-900"
                    >
                        <option value="">Select Category</option>

                        <option
                            v-for="item in categories"
                            :key="item.id"
                            :value="item.id"
                        >
                            {{ item.categori }}
                        </option>
                    </select>

                    <InputError :message="errors.product_category_id" />
                </div>

                <!-- Unit -->
                <div class="grid gap-2">
                    <Label for="unit">
                        Unit
                        <span class="text-red-500">*</span>
                    </Label>

                    <Input
                        id="unit"
                        v-model="form.unit"
                        type="text"
                        placeholder="U / A / Mbps / IP"
                    />

                    <InputError :message="errors.unit" />
                </div>

                <!-- Base Price -->
                <div class="grid gap-2">
                    <Label for="base_price">
                        Base Price
                        <span class="text-red-500">*</span>
                    </Label>

                    <Input
                        id="base_price"
                        v-model="form.base_price"
                        type="number"
                        placeholder="200000"
                    />

                    <InputError :message="errors.base_price" />
                </div>

                <!-- Billing Type -->
                <div class="grid gap-2">
                    <Label>
                        Billing Type
                        <span class="text-red-500">*</span>
                    </Label>

                    <select
                        v-model="form.billing_type"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white dark:text-gray-900"
                    >
                        <option value="recurring">Recurring</option>
                        <option value="one_time">One Time</option>
                    </select>

                    <InputError :message="errors.billing_type" />
                </div>

                <!-- Description -->
                <div class="grid md:col-span-2 gap-2">
                    <Label for="description">
                        Description
                    </Label>

                    <textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Product description"
                        class="w-full rounded-md border border-gray-300 bg-white py-2 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none resize-none h-28 dark:bg-transparent dark:text-gray-300"
                    />

                    <InputError :message="errors.description" />
                </div>

                <!-- Status -->
                <div class="grid md:col-span-2 gap-2">
                    <Label>
                        Status
                        <span class="text-red-500">*</span>
                    </Label>

                    <select
                        v-model="form.status"
                        class="rounded-lg border border-gray-200 px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-white dark:text-gray-900"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>

                    <InputError :message="errors.status" />
                </div>
            </div>

            <!-- Action -->
            <div class="flex items-center justify-end gap-4">
                <Button
                    type="button"
                    class="bg-gray-300 text-gray-700 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    @click="$inertia.visit('/admin/products')"
                >
                    Cancel
                </Button>

                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="bg-primary text-white hover:bg-primary/90 disabled:bg-gray-300 disabled:text-gray-500"
                >
                    <Spinner
                        v-if="form.processing"
                        class="mr-2 h-4 w-4"
                    />

                    {{ form.processing ? 'Creating...' : 'Create Product' }}
                </Button>
            </div>
        </Form>
    </div>
</template>