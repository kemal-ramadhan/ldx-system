<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

import InputError from '@/components/InputError.vue';
import ItemTaxFields from '@/components/ItemTaxFields.vue';
import SearchSelect from '@/components/SearchSelect.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { defaultItemTax, itemTaxFrom, taxSummary, type ItemTax } from '@/lib/tax';

import { ArrowLeft, Lock, Plus, Trash2 } from 'lucide-vue-next';

type InvoiceItem = ItemTax & {
    id: number | null;
    product_id: number | null;
    quantity: number;
    price: number;
};

const props = defineProps<{
    title: string;
    invoice: any;
    products: any[];
}>();

const dateOnly = (value: string | null) => (value ? String(value).slice(0, 10) : '');

const form = useForm({
    issue_date: dateOnly(props.invoice.issue_date),
    due_date: dateOnly(props.invoice.due_date),
    notes: props.invoice.notes ?? '',
    terms: props.invoice.terms ?? '',

    items: (props.invoice.items ?? []).map(
        (item: any): InvoiceItem => ({
            id: item.id,
            product_id: item.product_id ?? null,
            quantity: Number(item.quantity ?? 1),
            price: Number(item.price ?? 0),
            ...itemTaxFrom(item),
        }),
    ) as InvoiceItem[],
});

const addItem = () => {
    form.items.push({
        id: null,
        product_id: null,
        quantity: 1,
        price: 0,
        ...defaultItemTax(),
    });
};

const removeItem = (index: number) => {
    form.items.splice(index, 1);
};

const updateProduct = (index: number) => {
    const selected = props.products.find((product) => product.id == form.items[index].product_id);

    if (selected) {
        form.items[index].price = Number(selected.base_price);
    }
};

const itemSubtotal = (item: InvoiceItem) => Number(item.quantity) * Number(item.price);

const summary = computed(() =>
    taxSummary(form.items.map((item) => ({ subtotal: itemSubtotal(item), tax: item }))),
);

const rupiah = (value: number) => `Rp ${Number(value || 0).toLocaleString('id-ID')}`;

const itemError = (index: number, field: string) =>
    (form.errors as Record<string, string>)[`items.${index}.${field}`];

const updateInvoice = () => {
    form.put(`/admin/invoices/${props.invoice.id}`);
};

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Invoices', href: '/admin/invoices' },
            { title: 'Edit Invoice', href: '#' },
        ],
    },
});
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-col gap-4 p-4 text-foreground dark:scheme-dark">

        <!-- Header -->
        <div class="flex items-start gap-3">
            <Link :href="`/admin/invoices/${invoice.id}`"
                class="rounded-xl border p-2 text-muted-foreground hover:bg-muted">
                <ArrowLeft class="h-5 w-5" />
            </Link>

            <div>
                <h1 class="text-2xl font-bold">
                    Edit Invoice
                </h1>

                <p class="text-sm text-muted-foreground">
                    Correct invoice items, taxes and dates. Paid invoices cannot be edited.
                </p>
            </div>
        </div>

        <form @submit.prevent="updateInvoice" class="flex flex-col gap-4">

            <InputError :message="(form.errors as Record<string, string>).error" />

            <!-- Locked info -->
            <div class="rounded-2xl border bg-card p-5">
                <div class="mb-4 flex items-center gap-2">
                    <Lock class="h-4 w-4 text-muted-foreground" />
                    <h2 class="text-lg font-semibold">
                        Invoice Information
                    </h2>
                    <span class="text-xs text-muted-foreground">(tidak dapat diubah)</span>
                </div>

                <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-4">
                    <div>
                        <p class="text-muted-foreground">Invoice Number</p>
                        <p class="mt-1 font-semibold">{{ invoice.invoice_number }}</p>
                    </div>

                    <div>
                        <p class="text-muted-foreground">Client</p>
                        <p class="mt-1 font-semibold">{{ invoice.client?.company_name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-muted-foreground">Service</p>
                        <p class="mt-1 font-semibold">{{ invoice.service?.name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-muted-foreground">Status</p>
                        <p class="mt-1 font-semibold capitalize">{{ invoice.status }}</p>
                    </div>
                </div>
            </div>

            <!-- Dates & notes -->
            <div class="rounded-2xl border bg-card p-5">
                <h2 class="mb-4 text-lg font-semibold">
                    Invoice Details
                </h2>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label>
                            Issue Date
                            <span class="text-red-500">*</span>
                        </Label>
                        <Input v-model="form.issue_date" type="date" />
                        <InputError :message="form.errors.issue_date" />
                    </div>

                    <div class="grid gap-2">
                        <Label>
                            Due Date
                            <span class="text-red-500">*</span>
                        </Label>
                        <Input v-model="form.due_date" type="date" />
                        <InputError :message="form.errors.due_date" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Notes</Label>
                        <textarea v-model="form.notes" rows="3" placeholder="Notes for this invoice..."
                            class="resize-none rounded-lg border border-input bg-background px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" />
                        <InputError :message="form.errors.notes" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Terms</Label>
                        <textarea v-model="form.terms" rows="3" placeholder="Payment terms..."
                            class="resize-none rounded-lg border border-input bg-background px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none" />
                        <InputError :message="form.errors.terms" />
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="rounded-2xl border bg-card p-5">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">
                            Invoice Items
                        </h2>
                        <p class="text-sm text-muted-foreground">
                            Products billed on this invoice
                        </p>
                    </div>

                    <Button type="button" @click="addItem">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Item
                    </Button>
                </div>

                <InputError :message="form.errors.items" />

                <div class="flex flex-col gap-4">
                    <div v-for="(item, index) in form.items" :key="item.id ?? `new-${index}`"
                        class="grid grid-cols-1 gap-4 rounded-xl border p-4 md:grid-cols-12">

                        <!-- Product -->
                        <div class="grid gap-2 md:col-span-4">
                            <Label>Product</Label>
                            <SearchSelect v-model="item.product_id" :options="products" label="name"
                                placeholder="Search Product" @change="updateProduct(index)" />
                            <InputError :message="itemError(index, 'product_id')" />
                        </div>

                        <!-- Quantity -->
                        <div class="grid gap-2 md:col-span-2">
                            <Label>Quantity</Label>
                            <Input v-model="item.quantity" type="number" min="1" />
                            <InputError :message="itemError(index, 'quantity')" />
                        </div>

                        <!-- Price -->
                        <div class="grid gap-2 md:col-span-2">
                            <Label>Price</Label>
                            <Input v-model="item.price" type="number" min="0" />
                            <InputError :message="itemError(index, 'price')" />
                        </div>

                        <!-- Subtotal -->
                        <div class="grid gap-2 md:col-span-3">
                            <Label>Subtotal</Label>
                            <Input :model-value="itemSubtotal(item).toLocaleString('id-ID')" readonly />
                        </div>

                        <!-- Remove -->
                        <div class="flex items-end md:col-span-1">
                            <button type="button" :disabled="form.items.length === 1" @click="removeItem(index)"
                                class="rounded-lg p-2 text-red-500 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40 dark:hover:bg-red-950/40"
                                title="Remove item">
                                <Trash2 class="h-4 w-4" />
                            </button>
                        </div>

                        <!-- Tax per item -->
                        <div class="md:col-span-12">
                            <ItemTaxFields :subtotal="itemSubtotal(item)" v-model:ppn-enabled="item.ppn_enabled"
                                v-model:ppn-percentage="item.ppn_percentage" v-model:pph23-enabled="item.pph23_enabled"
                                v-model:pph23-percentage="item.pph23_percentage" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary -->
            <div class="rounded-2xl border bg-card p-5">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold">
                        Invoice Summary
                    </h2>
                    <p class="text-sm text-muted-foreground">
                        Totals recalculated from the items above
                    </p>
                </div>

                <div class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">Subtotal</span>
                        <span class="font-medium">{{ rupiah(summary.subtotal) }}</span>
                    </div>

                    <div v-if="summary.ppn > 0" class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">PPN</span>
                        <span class="font-medium">+ {{ rupiah(summary.ppn) }}</span>
                    </div>

                    <div v-if="summary.pph23 > 0" class="flex items-center justify-between">
                        <span class="text-sm text-muted-foreground">PPh 23</span>
                        <span class="font-medium">- {{ rupiah(summary.pph23) }}</span>
                    </div>

                    <div class="my-2 border-t"></div>

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-lg font-semibold">Grand Total</p>
                            <p class="text-xs text-muted-foreground">
                                Before: {{ rupiah(Number(invoice.total)) }}
                            </p>
                        </div>
                        <span class="text-2xl font-bold">{{ rupiah(summary.total) }}</span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3">
                <Link :href="`/admin/invoices/${invoice.id}`"
                    class="inline-flex h-9 items-center rounded-md border px-4 text-sm font-medium hover:bg-muted">
                    Cancel
                </Link>

                <Button type="submit" :disabled="form.processing">
                    <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </Button>
            </div>
        </form>
    </div>
</template>
