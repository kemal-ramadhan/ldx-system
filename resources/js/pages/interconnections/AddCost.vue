<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import { ArrowLeft, Plus, Trash2, Save } from 'lucide-vue-next'
import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'
import ItemTaxFields from '@/components/ItemTaxFields.vue'
import { defaultItemTax, taxSummary, type ItemTax } from '@/lib/tax'

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
    products: [] as Array<{ product: any; quantity: number } & ItemTax>,
});

const addProduct = () => {
    form.products.push({ product: null, quantity: 1, ...defaultItemTax() });
};

const removeProduct = (index: number) => {
    form.products.splice(index, 1);
};

const itemSubtotal = (p: { product: any; quantity: number }) =>
    p.product ? Number(p.product.base_price) * Number(p.quantity) : 0;

const summary = computed(() =>
    taxSummary(form.products.map((p) => ({ subtotal: itemSubtotal(p), tax: p }))),
);

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
                quantity: p.quantity,
                ppn_enabled: p.ppn_enabled,
                ppn_percentage: p.ppn_percentage,
                pph23_enabled: p.pph23_enabled,
                pph23_percentage: p.pph23_percentage,
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
/* vue-multiselect follows the app theme tokens (light & dark) */
.add-cost-page .multiselect__tags {
    border-radius: 0.5rem;
    border-color: var(--input);
    background-color: var(--background);
    color: var(--foreground);
    padding-top: 0.5rem;
    min-height: 2.5rem;
}
.add-cost-page .multiselect__input,
.add-cost-page .multiselect__single {
    background-color: transparent;
    color: var(--foreground);
}
.add-cost-page .multiselect__placeholder {
    color: var(--muted-foreground);
}
.add-cost-page .multiselect__content-wrapper {
    background-color: var(--popover);
    border-color: var(--border);
    color: var(--popover-foreground);
}
.add-cost-page .multiselect__option {
    color: var(--popover-foreground);
}
.add-cost-page .multiselect__option--highlight {
    background-color: var(--accent);
    color: var(--accent-foreground);
}
.add-cost-page .multiselect__option--selected,
.add-cost-page .multiselect__option--selected.multiselect__option--highlight {
    background-color: var(--muted);
    color: var(--foreground);
}
</style>

<template>
    <Head :title="title" />

    <div class="add-cost-page p-4 text-foreground dark:scheme-dark">
        <!-- HEADER -->
        <div class="mb-6 flex items-start justify-between">
            <div class="flex items-start gap-3">
                <Link :href="`/admin/interconnections/${interconnection.id}`" class="rounded-xl border p-2 text-muted-foreground hover:bg-muted">
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

        <div class="rounded-2xl border bg-card p-6 shadow-sm dark:bg-transparent">
            <form @submit.prevent="submit" class="space-y-6">
                
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-foreground">Billing Cycle</label>
                    <select v-model="form.billing_cycle" class="mt-1 block w-full md:w-1/3 rounded-lg border border-input bg-background py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm">
                        <option value="one-time">One-Time</option>
                        <option value="monthly">Monthly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                    <p class="text-sm text-red-500" v-if="form.errors.billing_cycle">{{ form.errors.billing_cycle }}</p>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b pb-3">
                        <h3 class="text-base font-semibold text-foreground">Products & Costs</h3>
                        <button type="button" @click="addProduct" class="inline-flex items-center rounded-lg bg-primary px-3 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90">
                            <Plus class="w-4 h-4 mr-2" /> Add Product
                        </button>
                    </div>

                    <div v-if="form.products.length === 0" class="text-center py-8 text-sm text-muted-foreground rounded-lg border border-dashed">
                        No products added. Click "Add Product" to include costs.
                    </div>

                    <div v-for="(item, index) in form.products" :key="index" class="flex flex-wrap gap-4 items-start bg-muted/50 p-4 rounded-xl border">
                        <div class="flex-1 min-w-[250px] space-y-1">
                            <label class="block text-xs font-medium text-muted-foreground">Product</label>
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
                            <label class="block text-xs font-medium text-muted-foreground">Quantity</label>
                            <input type="number" min="1" v-model="item.quantity" class="block w-full h-[40px] rounded-lg border border-input bg-background px-3 text-sm focus:border-blue-500 focus:ring-blue-500" />
                        </div>
                        <div class="w-40 space-y-1">
                            <label class="block text-xs font-medium text-muted-foreground">Subtotal</label>
                            <div class="flex items-center h-[40px] text-sm font-medium text-foreground">
                                <span v-if="item.product">
                                    {{ formatCurrency(Number(item.product.base_price) * item.quantity) }}
                                </span>
                                <span v-else>Rp 0</span>
                            </div>
                        </div>
                        <div class="pt-5">
                            <button type="button" @click="removeProduct(index)" class="inline-flex items-center justify-center h-[40px] w-[40px] rounded-lg text-red-600 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                                <Trash2 class="w-5 h-5" />
                            </button>
                        </div>
                        <div class="w-full">
                            <ItemTaxFields :subtotal="itemSubtotal(item)"
                                v-model:ppn-enabled="item.ppn_enabled" v-model:ppn-percentage="item.ppn_percentage"
                                v-model:pph23-enabled="item.pph23_enabled"
                                v-model:pph23-percentage="item.pph23_percentage" />
                        </div>
                    </div>
                </div>

                <!-- Summary -->
                <div class="rounded-xl border p-5 mt-6 bg-muted/50 dark:bg-transparent">
                    <h2 class="text-lg font-semibold mb-4">Invoice Summary</h2>
                    <div class="flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">Subtotal</span>
                            <span class="font-medium">{{ formatCurrency(summary.subtotal) }}</span>
                        </div>
                        <div v-if="summary.ppn > 0" class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">PPN</span>
                            <span class="font-medium text-blue-600 dark:text-blue-300">+ {{ formatCurrency(summary.ppn) }}</span>
                        </div>
                        <div v-if="summary.pph23 > 0" class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground">PPh 23</span>
                            <span class="font-medium text-red-600 dark:text-red-300">- {{ formatCurrency(summary.pph23) }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between border-t pt-4">
                            <span class="font-semibold">Grand Total</span>
                            <span class="text-lg font-bold text-foreground">{{ formatCurrency(summary.total) }}</span>
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
