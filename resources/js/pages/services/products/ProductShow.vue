<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{
    title: string;
    product: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Products Management',
                href: '/admin/products',
            },
            {
                title: 'Product Detail',
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
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">
                    Product Detail
                </h1>

                <p class="text-sm text-muted-foreground">
                    Detailed information about this product
                </p>
            </div>

            <button
                @click="$inertia.visit('/admin/products')"
                class="rounded-lg border px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-gray-800"
            >
                Back
            </button>
        </div>

        <!-- Content -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Basic Information -->
            <div class="rounded-xl border p-5 flex flex-col gap-4">
                <h2 class="text-lg font-semibold">
                    Basic Information
                </h2>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Product Code
                    </p>

                    <p class="font-medium">
                        {{ product.code }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Product Name
                    </p>

                    <p class="font-medium">
                        {{ product.name }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Category
                    </p>

                    <p class="font-medium">
                        {{ product.categories?.categori }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Unit
                    </p>

                    <p class="font-medium">
                        {{ product.unit }}
                    </p>
                </div>
            </div>

            <!-- Billing Information -->
            <div class="rounded-xl border p-5 flex flex-col gap-4">
                <h2 class="text-lg font-semibold">
                    Billing Information
                </h2>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Base Price
                    </p>

                    <p class="font-medium">
                        Rp {{ Number(product.base_price).toLocaleString('id-ID') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Billing Type
                    </p>

                    <p class="font-medium capitalize">
                        {{ product.billing_type.replace('_', ' ') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-muted-foreground">
                        Status
                    </p>

                    <span
                        class="inline-flex w-fit rounded-full px-3 py-1 text-xs font-medium"
                        :class="{
                            'bg-green-100 text-green-700':
                                product.status === 'active',

                            'bg-red-100 text-red-700':
                                product.status === 'inactive',
                        }"
                    >
                        {{ product.status }}
                    </span>
                </div>
            </div>

            <!-- Description -->
            <div class="rounded-xl border p-5 flex flex-col gap-4 md:col-span-2">
                <h2 class="text-lg font-semibold">
                    Description
                </h2>

                <p class="text-sm leading-relaxed text-muted-foreground">
                    {{ product.description || '-' }}
                </p>
            </div>

            <!-- Metadata -->
            <div class="rounded-xl border p-5 flex flex-col gap-4 md:col-span-2">
                <h2 class="text-lg font-semibold">
                    Metadata
                </h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-muted-foreground">
                            Created At
                        </p>

                        <p class="font-medium">
                            {{ product.created_at }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-muted-foreground">
                            Updated At
                        </p>

                        <p class="font-medium">
                            {{ product.updated_at }}
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>