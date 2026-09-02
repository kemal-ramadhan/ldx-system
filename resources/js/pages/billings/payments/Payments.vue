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

const clientPayment = computed(() => {
    return props.invoice.payments?.[0] ?? null
})

const proofUrl = computed<string | undefined>(() => {
    if (!clientPayment.value?.proof_of_payment) {
        return undefined
    }

    return `/storage/${clientPayment.value.proof_of_payment}`
})

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
        `/admin/invoices/${props.invoice.id}/paymentbyadmin`,
        {
            forceFormData: true,
        }
    )
}

const rejectPayment = () => {

    router.patch(
        `/admin/invoices/${props.invoice.id}/reject-payment`
    )
}

const hasClientPayment = computed(() => {
    return !!clientPayment.value?.proof_of_payment
})


const paymentProofUrl = computed(() => {
    if (!clientPayment.value?.proof_of_payment) {
        return null
    }

    return `/storage/${clientPayment.value.proof_of_payment}`
})


const verifyForm = useForm({
    status: 'verified',
    notes: props.invoice.payments?.[0]?.notes ?? '',
})


// Add ref for rejection modal
const showRejectionModal = ref(false)
const rejectionReason = ref('')

const openRejectionModal = () => {
    showRejectionModal.value = true
    rejectionReason.value = ''
}

const closeRejectionModal = () => {
    showRejectionModal.value = false
    rejectionReason.value = ''
}

const submitRejection = () => {
    if (!rejectionReason.value.trim()) {
        // You might want to show a validation message
        return
    }

    router.patch(
        `/admin/invoices/${props.invoice.id}/reject-payment`,
        {
            rejection_reason: rejectionReason.value
        },
        {
            onSuccess: () => {
                closeRejectionModal()
            }
        }
    )
}
const verifyPayment = () => {

    verifyForm.patch(
        `/admin/invoices/${props.invoice.id}/verify`
    )

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
                    class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium uppercase text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400">

                    {{ invoice.status }}

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

        <!-- PAYMENT VERIFICATION -->
        <div v-if="hasClientPayment"
            class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 p-6 dark:border-gray-800">

                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Verify Client Payment
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Review uploaded payment proof from client.
                </p>
            </div>

            <div class="grid gap-6 p-6 md:grid-cols-2">
                <!-- Proof -->
                <div>
                    <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">
                        Payment Proof
                    </p>
                    <img :src="paymentProofUrl ?? undefined" class="w-full rounded-xl border object-cover" />
                </div>

                <!-- Detail -->
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">
                            Payment Method
                        </p>

                        <p class="font-semibold">
                            {{ clientPayment.payment_method }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">
                            Amount
                        </p>
                        <p class="font-semibold text-primary">
                            Rp {{ Number(clientPayment.amount).toLocaleString('id-ID') }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">
                            Current Status
                        </p>
                        <span class="rounded-full bg-yellow-100 px-3 py-1 text-xs font-medium text-yellow-700">
                            {{ clientPayment.status }}
                        </span>
                    </div>

                    <!-- Notes -->
                    <textarea v-model="verifyForm.notes" rows="4" placeholder="Verification notes..."
                        class="w-full rounded-xl border px-4 py-3"></textarea>

                    <!-- ACTION -->
                    <div class="flex gap-3" v-if="invoice.status?.toLowerCase() === 'waiting'">
                        <button type="button" @click="openRejectionModal"
                            class="rounded-xl bg-red-500 px-5 py-3 text-sm text-white hover:bg-red-600 transition-colors">
                            Reject
                        </button>

                        <button type="button" @click="verifyPayment" :disabled="verifyForm.processing"
                            class="rounded-xl bg-green-600 px-5 py-3 text-sm text-white hover:bg-green-700 transition-colors">

                            {{
                                verifyForm.processing
                                    ? 'Processing...'
                                    : 'Verify Payment'
                            }}

                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Manual Confirmation -->
        <div v-else class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">

            <div class="border-b border-gray-200 p-6 dark:border-gray-800">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Manual Payment Confirmation
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Use this form if payment was received directly by sales.
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

                        <input type="number" placeholder="0" v-model="form.amount"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />

                        <p v-if="form.errors.amount" class="mt-1 text-sm text-red-500">
                            {{ form.errors.amount }}
                        </p>
                    </div>

                    <!-- Paid Date -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Paid Date
                        </label>

                        <input type="date" v-model="form.paid_at"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                        <p v-if="form.errors.paid_at" class="mt-1 text-sm text-red-500">
                            {{ form.errors.paid_at }}
                        </p>
                    </div>

                    <!-- Reference Number -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Reference Number
                        </label>

                        <input type="text" placeholder="TRX-00001" v-model="form.reference_number"
                            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                    </div>

                </div>

                <!-- Notes -->
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Notes
                    </label>

                    <textarea rows="4" placeholder="Payment received directly by sales team..." v-model="form.notes"
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
                        class="inline-flex items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-medium text-white dark:text-gray-900 hover:bg-primary/90">

                        <CreditCard class="mr-2 h-4 w-4" />

                        {{ form.processing ? 'Processing...' : 'Pay Payment' }}

                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- Rejection Modal -->
        <div v-if="showRejectionModal" class="fixed inset-0 z-50 overflow-y-auto">
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-gray-50/10 transition-opacity" @click="closeRejectionModal"></div>

            <!-- Modal -->
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
                    <!-- Close button -->
                    <button @click="closeRejectionModal"
                        class="absolute right-4 top-4 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                        <X class="h-5 w-5" />
                    </button>

                    <!-- Header -->
                    <div class="mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Reject Payment
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Please provide a reason for rejecting this payment.
                        </p>
                    </div>

                    <!-- Form -->
                    <div class="space-y-4">
                        <div>
                            <label for="rejection_reason"
                                class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Rejection Reason <span class="text-red-500">*</span>
                            </label>
                            <textarea id="rejection_reason" v-model="rejectionReason" rows="4"
                                placeholder="Explain why this payment is being rejected..."
                                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20 dark:border-gray-700 dark:bg-gray-800 dark:text-white"></textarea>
                            <p v-if="!rejectionReason.trim() && showRejectionModal" class="mt-1 text-sm text-red-500">
                                Please provide a rejection reason.
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 pt-4">
                            <button @click="closeRejectionModal"
                                class="flex-1 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                                Cancel
                            </button>
                            <button @click="submitRejection" :disabled="!rejectionReason.trim()"
                                class="flex-1 rounded-xl bg-red-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                Reject Payment
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</template>