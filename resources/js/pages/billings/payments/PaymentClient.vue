<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import {
    CreditCard,
    ReceiptText,
    Upload,
} from 'lucide-vue-next'

const props = defineProps<{
    title: string
    invoice: any
}>()

const proofPreview = computed(() => {

    if (!form.proof) {
        return null
    }

    return URL.createObjectURL(form.proof)
})

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Invoices',
                href: '/admin/invoices',
            },
            {
                title: 'Payment Confirmation',
                href: '#',
            },
        ],
    },
})

const form = useForm({
    payment_method: 'bank_transfer',
    amount: props.invoice.total,
    paid_at: '',
    reference_number: '',
    notes: '',
    proof: null as File | null,

    /**
     * =========================================
     * SALES DIRECT PAYMENT
     * =========================================
     */
    is_direct_payment: true,
})

const submitPayment = () => {

    form.post(
        `/client/invoices/${props.invoice.id}/payment`,
        {
            forceFormData: true,
        }
    )
}

/**
 * =========================================
 * FORMAT RUPIAH
 * =========================================
 */
const formatRupiah = (value: number) => {

    return new Intl.NumberFormat(
        'id-ID',
        {
            style: 'currency',
            currency: 'IDR',
        }
    ).format(value || 0);
};

const accountNumber = ref('7435303471')

const copied = ref(false)


const copyAccountNumber = async () => {

    await navigator.clipboard.writeText(accountNumber.value)

    copied.value = true


    setTimeout(() => {
        copied.value = false
    }, 2000)

}

</script>

<template>

    <Head :title="title" />

    <div class="space-y-6 p-4">

        <!-- Header -->
        <div
            class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900 md:flex-row md:items-center md:justify-between">

            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Payment Confirmation
                </h1>

                <p class="mt-1 text-sm text-gray-500">
                    Verify client payments or confirm direct payments from sales.
                </p>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="rounded-full bg-red-100 px-3 py-1 text-xs font-medium uppercase border-red-200 text-red-700 dark:bg-red-500/10 dark:text-white">
                    {{ invoice.status === 'sent' ? 'Awaiting Payment' : invoice.status }}

                </span>
            </div>
        </div>

        <!-- Invoice Information -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Invoice Information
                </h2>
            </div>

            <div class="grid gap-6 p-6 md:grid-cols-2 xl:grid-cols-4">

                <!-- Invoice Number -->
                <div>
                    <p class="text-sm text-gray-500">
                        Invoice Number
                    </p>

                    <h3 class="mt-1 font-semibold text-gray-900 dark:text-white">
                        {{ invoice.invoice_number }}
                    </h3>
                </div>

                <!-- Client -->
                <div>
                    <p class="text-sm text-gray-500">
                        Client
                    </p>

                    <h3 class="mt-1 font-semibold text-gray-900 dark:text-white">
                        {{ invoice.client?.company_name }}
                    </h3>
                </div>

                <!-- Service -->
                <div>
                    <p class="text-sm text-gray-500">
                        Service
                    </p>

                    <h3 class="mt-1 font-semibold text-gray-900 dark:text-white">
                        {{ invoice.service?.name }}
                    </h3>
                </div>

                <!-- Amount -->
                <div>
                    <p class="text-sm text-gray-500">
                        Total Amount
                    </p>

                    <h3 class="mt-1 font-semibold text-primary">
                        Rp {{ Number(invoice.total).toLocaleString('id-ID') }}
                    </h3>
                </div>

            </div>
        </div>

        <!-- Invoice Items -->
        <div class="overflow-hidden rounded-2xl border bg-white shadow-sm dark:bg-transparent">

            <div class="border-b px-5 py-4">
                <h2 class="font-semibold">
                    Invoice Items
                </h2>
            </div>

            <div class="overflow-x-auto">

                <table class="min-w-full">

                    <thead class="bg-muted/40">

                        <tr class="text-left text-sm">

                            <th class="px-5 py-3">
                                Item
                            </th>

                            <th class="px-5 py-3">
                                Quantity
                            </th>

                            <th class="px-5 py-3">
                                Price
                            </th>

                            <th class="px-5 py-3">
                                Total
                            </th>
                        </tr>
                    </thead>

                    <tbody>

                        <tr v-for="item in invoice.items" :key="item.id" class="border-t">

                            <td class="px-5 py-4">

                                <div class="font-medium">
                                    {{ item.name }}
                                </div>

                                <div class="text-sm text-muted-foreground">
                                    {{ item.description }}
                                </div>

                            </td>

                            <td class="px-5 py-4">
                                {{ item.quantity }}
                            </td>

                            <td class="px-5 py-4">
                                {{ formatRupiah(item.price) }}
                            </td>

                            <td class="px-5 py-4 font-semibold">
                                {{ formatRupiah(item.total) }}
                            </td>

                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- transfer visit -->
        <div class="rounded-2xl border bg-white dark:bg-transparent p-6 shadow-sm">

            <div class="flex items-center gap-3 mb-5">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50">
                    <CreditCard class="h-5 w-5 text-blue-600" />
                </div>

                <div>
                    <h3 class="font-semibold">
                        Bank Account Details
                    </h3>

                    <p class="text-sm">
                        Transfer payment to this account
                    </p>
                </div>
            </div>


            <div class="rounded-xl bg-gray-50 dark:bg-transparent p-5 space-y-4">


                <div>
                    <p class="text-xs uppercase">
                        Bank
                    </p>

                    <p class="mt-1 font-semibold ">
                        BCA
                    </p>
                </div>


                <div>
                    <p class="text-xs uppercase">
                        Account Name
                    </p>

                    <p class="mt-1 font-semibold">
                        RONI M
                    </p>
                </div>



                <div>
                    <p class="text-xs uppercase">
                        Account Number
                    </p>

                    <div class="mt-1 flex items-center justify-between">

                        <p class="font-bold tracking-wider text-lg">
                            7435303471
                        </p>


                        <button @click="copyAccountNumber" class="rounded-lg border px-3 py-1 text-sm hover:bg-white dark:hover:bg-gray-800">
                            {{ copied ? 'Copied!' : 'Copy' }}
                        </button>

                    </div>

                </div>


            </div>


            <div class="mt-4 rounded-xl border border-yellow-200 bg-yellow-50 p-4">

                <p class="text-sm text-yellow-800">
                    Please make sure the transfer is sent to the correct
                    account. Payment will be processed after confirmation.
                </p>

            </div>


        </div>

        <!-- Sales Manual Confirmation -->
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Manual Payment Confirmation
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Use this form for the confirm payment
                </p>
            </div>

            <form class="space-y-6 p-6" @submit.prevent="submitPayment">

                <div class="grid gap-6 md:grid-cols-2">

                    <!-- Payment Method -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Payment Method
                        </label>

                        <select v-model="form.payment_method"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white">

                            <option value="bank_transfer">
                                Bank Transfer
                            </option>

                            <option value="cash">
                                Cash
                            </option>

                            <option value="transfer">
                                Transfer
                            </option>

                            <option value="qris">
                                QRIS
                            </option>

                        </select>

                        <p v-if="form.errors.payment_method" class="mt-1 text-sm text-red-500">
                            {{ form.errors.payment_method }}
                        </p>
                    </div>

                    <!-- Amount -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Amount
                        </label>

                        <input type="number" placeholder="0" v-model="form.amount" readonly
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />

                        <p v-if="form.errors.amount" class="mt-1 text-sm text-red-500">
                            {{ form.errors.amount }}
                        </p>
                    </div>

                </div>

                <!-- Notes -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notes
                    </label>

                    <textarea rows="4" placeholder="Note payment ..." v-model="form.notes"
                        class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                </div>

                <!-- Upload Proof -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Upload Proof (Optional)
                    </label>

                    <input type="file" class="hidden" id="proof"
                        @change="form.proof = ($event.target as HTMLInputElement).files?.[0] || null" />

                    <div
                        class="flex items-center justify-center rounded-2xl border-2 border-dashed border-gray-300 p-8 dark:border-gray-700">

                        <div class="text-center">

                            <Upload class="mx-auto h-8 w-8 text-gray-400" />

                            <p class="mt-3 text-sm text-gray-500">
                                Upload payment proof or receipt
                            </p>

                            <label for="proof"
                                class="mt-4 inline-block cursor-pointer rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">
                                Choose File
                            </label>

                            <!-- FILE NAME -->
                            <p v-if="form.proof" class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                                Selected:
                                {{ form.proof.name }}
                            </p>

                            <img v-if="proofPreview" :src="proofPreview"
                                class="mx-auto mt-4 h-40 rounded-xl border object-cover" />
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div
                    class="flex flex-col gap-3 border-t border-gray-200 pt-6 dark:border-gray-800 md:flex-row md:justify-end">

                    <button type="submit" :disabled="form.processing"
                        class="inline-flex items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-medium text-white hover:bg-primary/90">

                        <CreditCard class="mr-2 h-4 w-4" />

                        {{ form.processing ? 'Processing...' : 'Sent Payment' }}

                    </button>

                </div>

            </form>

        </div>

    </div>
</template>