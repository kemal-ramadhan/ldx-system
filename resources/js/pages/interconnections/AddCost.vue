<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ArrowLeft, Plus, Trash2, Save } from 'lucide-vue-next'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'

const props = defineProps<{
    title: string;
    interconnection: any;
    products: any[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Interconnections',
                href: '/admin/interconnections',
            },
            {
                title: 'Set Cost',
                href: '#',
            },
        ],
    },
})

const form = useForm({
    billing_cycle: 'monthly',
    products: [] as Array<{ product: any; quantity: number }>,
    ppn_enabled: false,
    ppn_percentage: 11,
    pph23_enabled: false,
    pph23_percentage: 2,
});

const addProduct = () => {
    form.products.push({ product: null, quantity: 1 });
};

const removeProduct = (index: number) => {
    form.products.splice(index, 1);
};

const subtotal = computed(() => {
    return form.products.reduce((total, p) => {
        if (p.product) {
            return total + (Number(p.product.base_price) * p.quantity);
        }
        return total;
    }, 0);
});

const ppnAmount = computed(() => {
    if (!form.ppn_enabled) return 0;
    return subtotal.value * (Number(form.ppn_percentage) / 100);
});

const pph23Amount = computed(() => {
    if (!form.pph23_enabled) return 0;
    return subtotal.value * (Number(form.pph23_percentage) / 100);
});

const grandTotal = computed(() => {
    return subtotal.value + ppnAmount.value - pph23Amount.value;
});

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
    }).format(amount);
};

const submit = () => {
    // Transform products array for backend
    const payload = {
        ...form.data(),
        products: form.products
            .filter(p => p.product)
            .map(p => ({
                id: p.product.id,
                quantity: p.quantity
            }))
    };
    
    router.post(`/admin/interconnections/${props.interconnection.id}/add-cost`, payload, {
        onSuccess: () => {
            router.visit(`/admin/interconnections/${props.interconnection.id}`);
        },
    });
};
</script>

<style>
/* Adjust multiselect for better contrast */
.multiselect__tags {
    border-radius: 0.5rem;
    border-color: #d1d5db;
    padding-top: 0.5rem;
    min-height: 2.5rem;
}
.dark .multiselect__tags {
    background-color: #1f2937;
    border-color: #374151;
    color: #f9fafb;
}
.dark .multiselect__input {
    background-color: transparent;
    color: #f9fafb;
}
.dark .multiselect__single {
    background-color: transparent;
    color: #f9fafb;
}
.dark .multiselect__content-wrapper {
    background-color: #1f2937;
    border-color: #374151;
}
.dark .multiselect__option {
    color: #f9fafb;
}
.dark .multiselect__option--highlight {
    background-color: #3b82f6;
    color: #ffffff;
}
.dark .multiselect__option--selected {
    background-color: #374151;
    color: #ffffff;
}
</style>

<template>
    <Head :title="title" />

    <div class="p-4">
        <!-- HEADER -->
        <div class="mb-6 flex items-start justify-between">
            <div class="flex items-start gap-3">
                <Link :href="`/admin/interconnections/${interconnection.id}`" class="rounded-xl border p-2 text-gray-500 hover:bg-gray-50">
                    <ArrowLeft class="h-4 w-4" />
                </Link>
                <div>
                    <h1 class="text-lg font-semibold">
                        Set Cost for {{ interconnection.request_number }}
                    </h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Determine the products and costs for this interconnection.
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border bg-white p-6 shadow-sm dark:bg-transparent">
            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700">Billing Cycle</label>
                    <select v-model="form.billing_cycle" class="mt-1 block w-full md:w-1/3 rounded-lg border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
                        <option value="one-time">One-Time</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                    <p class="text-sm text-red-500" v-if="form.errors.billing_cycle">{{ form.errors.billing_cycle }}</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b pb-3">
                        <h3 class="text-base font-semibold text-gray-900">Products & Costs</h3>
                        <button type="button" @click="addProduct" class="inline-flex items-center rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-gray-800">
                            <Plus class="w-4 h-4 mr-2" /> Add Product
                        </button>
                    </div>

                    <div v-if="form.products.length === 0" class="text-center py-8 text-sm text-gray-500 rounded-lg border border-dashed">
                        No products added. Click "Add Product" to include costs.
                    </div>

                    <div v-for="(item, index) in form.products" :key="index" class="flex flex-wrap gap-4 items-start bg-gray-50 p-4 rounded-xl border">
                        <div class="flex-1 min-w-[250px] space-y-1">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Product</label>
                            <Multiselect
                                v-model="item.product"
                                :options="products"
                                placeholder="Search & select a product"
                                label="name"
                                track-by="id"
                                :searchable="true"
                                :allow-empty="false"
                                :custom-label="(option: any) => `${option.name} (${formatCurrency(Number(option.base_price))})`"
                            >
                                <template #noResult>
                                    <span>No product found.</span>
                                </template>
                            </Multiselect>
                        </div>
                        <div class="w-32 space-y-1">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Quantity</label>
                            <input type="number" min="1" v-model="item.quantity" class="block w-full h-[40px] rounded-lg border border-[#d1d5db] bg-white dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 px-3 text-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div class="w-40 space-y-1">
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400">Subtotal</label>
                            <div class="flex items-center h-[40px] text-sm font-medium text-gray-900 dark:text-gray-200">
                                <span v-if="item.product">
                                    {{ formatCurrency(Number(item.product.base_price) * item.quantity) }}
                                </span>
                                <span v-else>Rp 0</span>
                            </div>
                        </div>
                        <div class="pt-5">
                            <button type="button" @click="removeProduct(index)" class="inline-flex items-center justify-center h-[40px] w-[40px] rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                                <Trash2 class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Tax Configuration -->
                <div class="rounded-xl border p-5 mt-6">
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
                                    <h3 class="font-medium">PPN</h3>
                                    <p class="mt-1 text-sm text-muted-foreground">Apply Value Added Tax to this service.</p>
                                </div>
                                <button type="button" @click="form.ppn_enabled = !form.ppn_enabled" :class="['relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors', form.ppn_enabled ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-700']">
                                    <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform', form.ppn_enabled ? 'translate-x-5' : 'translate-x-0.5']" />
                                </button>
                            </div>
                            <div v-if="form.ppn_enabled" class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">PPN Percentage</label>
                                <div class="relative mt-2">
                                    <input v-model="form.ppn_percentage" type="number" min="0" max="100" step="0.01" placeholder="11" class="block w-full rounded-lg border-gray-300 py-2 pl-3 pr-10 text-sm focus:border-blue-500 focus:ring-blue-500" />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">%</span>
                                </div>
                                <p class="text-sm text-red-500 mt-1" v-if="form.errors.ppn_percentage">{{ form.errors.ppn_percentage }}</p>
                            </div>
                            <div v-else class="mt-4 rounded-lg bg-gray-50 px-3 py-2 text-sm text-muted-foreground dark:bg-gray-900">
                                PPN is disabled for this service.
                            </div>
                        </div>

                        <!-- PPh 23 -->
                        <div class="rounded-xl border p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="font-medium">PPh 23</h3>
                                    <p class="mt-1 text-sm text-muted-foreground">Apply withholding tax for this service.</p>
                                </div>
                                <button type="button" @click="form.pph23_enabled = !form.pph23_enabled" :class="['relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors', form.pph23_enabled ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-700']">
                                    <span :class="['inline-block h-5 w-5 transform rounded-full bg-white shadow transition-transform', form.pph23_enabled ? 'translate-x-5' : 'translate-x-0.5']" />
                                </button>
                            </div>
                            <div v-if="form.pph23_enabled" class="mt-4">
                                <label class="block text-sm font-medium text-gray-700">PPh 23 Percentage</label>
                                <div class="relative mt-2">
                                    <input v-model="form.pph23_percentage" type="number" min="0" max="100" step="0.01" placeholder="2" class="block w-full rounded-lg border-gray-300 py-2 pl-3 pr-10 text-sm focus:border-blue-500 focus:ring-blue-500" />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground">%</span>
                                </div>
                                <p class="text-sm text-red-500 mt-1" v-if="form.errors.pph23_percentage">{{ form.errors.pph23_percentage }}</p>
                            </div>
                            <div v-else class="mt-4 rounded-lg bg-gray-50 px-3 py-2 text-sm text-muted-foreground dark:bg-gray-900">
                                PPh 23 is disabled for this service.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="rounded-xl border p-5 mt-6 bg-gray-50 dark:bg-transparent">
                    <h2 class="text-lg font-semibold mb-4">Invoice Summary</h2>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Subtotal</span>
                            <span class="font-medium">{{ formatCurrency(subtotal) }}</span>
                        </div>
                        <div v-if="form.ppn_enabled" class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-muted-foreground">PPN</span>
                                <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium">{{ form.ppn_percentage }}%</span>
                            </div>
                            <span class="font-medium text-blue-600">+ {{ formatCurrency(ppnAmount) }}</span>
                        </div>
                        <div v-if="form.pph23_enabled" class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-muted-foreground">PPh 23</span>
                                <span class="rounded-full bg-gray-200 px-2 py-0.5 text-xs font-medium">{{ form.pph23_percentage }}%</span>
                            </div>
                            <span class="font-medium text-red-600">- {{ formatCurrency(pph23Amount) }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between border-t pt-4">
                            <span class="font-semibold">Grand Total</span>
                            <span class="text-lg font-bold text-gray-900">{{ formatCurrency(grandTotal) }}</span>
                        </div>
                    </div>
                </div>

                <div class="border-t pt-6 flex justify-end">
                    <button type="submit" :disabled="form.processing || form.products.length === 0" class="inline-flex items-center rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-700 disabled:opacity-50">
                        <Save class="w-4 h-4 mr-2" /> Save & Generate Invoice
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
