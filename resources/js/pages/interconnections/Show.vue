<script setup lang="ts">

import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'

import {
    Head,
    Link,
} from '@inertiajs/vue3'

import {
    ArrowLeft,
    ArrowRight,
    Cable,
    CheckCircle2,
    Circle,
    Clock3,
    MapPin,
} from 'lucide-vue-next'

const props = defineProps<{
    title: string
    interconnection: any
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Interconnections',
                href: '/admin/interconnections',
            },
            {
                title: 'Detail',
                href: '#',
            },
        ],
    },
})


const item = computed(() => props.interconnection)


const source = computed(() => {

    const port = item.value.source_port
    const device = port?.device
    const rack = device?.rack
    const client = device?.client

    return {
        client,
        rack,
        device,
        port,
    }

})


const destination = computed(() => {

    const port = item.value.destination_port
    const device = port?.device
    const rack = device?.rack
    const client = device?.client

    return {
        client,
        rack,
        device,
        port,
    }

})


const statusLabel = computed(() => {

    return item.value.status
        ?.replaceAll('_', ' ')
        ?.replace(/\b\w/g, (char: string) => char.toUpperCase())

})


const steps = [
    {
        key: 'submitted',
        label: 'Request Submitted',
    },
    {
        key: 'waiting_destination_approval',
        label: 'Destination Approval',
    },
    {
        key: 'waiting_dc_approval',
        label: 'DC Approval',
    },
    {
        key: 'in_progress',
        label: 'Installation',
    },
    {
        key: 'testing',
        label: 'Testing',
    },
    {
        key: 'completed',
        label: 'Completed',
    },
]


const stepIndex = computed(() => {

    const status = item.value.status

    const index = steps.findIndex(
        step => step.key === status
    )

    if (status === 'approved') {
        return 3
    }

    return index >= 0 ? index : 0

})


const isStepDone = (index: number) => {

    return index < stepIndex.value

}


const isCurrentStep = (index: number) => {

    return index === stepIndex.value

}

const rejectModal = ref(false)

const rejectionType = ref<
    'destination' | 'dc' | null
>(null)

const rejectForm = useForm({
    rejection_reason: '',
})

const approveDestination = () => {
    router.patch(
        `/admin/interconnections/${props.interconnection.id}/approve-destination`,
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success(
                    'Destination approval completed.'
                )
            },

            onError: (errors) => {
                toast.error(
                    Object.values(errors)[0] ??
                    'Failed to approve destination.'
                )
            },
        }
    )
}

const approveDc = () => {
    router.patch(
        `/admin/interconnections/${props.interconnection.id}/approve-dc`,
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success(
                    'DC approval completed.'
                )
            },

            onError: (errors) => {
                toast.error(
                    Object.values(errors)[0] ??
                    'Failed to approve DC.'
                )
            },
        }
    )
}

const startInstallation = () => {
    router.patch(
        `/admin/interconnections/${props.interconnection.id}/start-installation`,
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success(
                    'Installation started.'
                )
            },

            onError: (errors) => {
                toast.error(
                    Object.values(errors)[0] ??
                    'Failed to start installation.'
                )
            },
        }
    )
}

const startTesting = () => {
    router.patch(
        `/admin/interconnections/${props.interconnection.id}/start-testing`,
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success(
                    'Testing started.'
                )
            },

            onError: (errors) => {
                toast.error(
                    Object.values(errors)[0] ??
                    'Failed to start testing.'
                )
            },
        }
    )
}

const completeInterconnection = () => {
    router.patch(
        `/admin/interconnections/${props.interconnection.id}/complete`,
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success(
                    'Interconnection completed successfully.'
                )
            },

            onError: (errors) => {
                toast.error(
                    Object.values(errors)[0] ??
                    'Failed to complete interconnection.'
                )
            },
        }
    )
}

const openRejectModal = (
    type: 'destination' | 'dc'
) => {
    rejectionType.value = type
    rejectForm.reset()
    rejectForm.clearErrors()
    rejectModal.value = true
}

const submitReject = () => {
    if (!rejectionType.value) {
        return
    }

    const endpoint =
        rejectionType.value === 'destination'
            ? `/admin/interconnections/${props.interconnection.id}/reject-destination`
            : `/admin/interconnections/${props.interconnection.id}/reject-dc`

    rejectForm.patch(endpoint, {
        preserveScroll: true,

        onSuccess: () => {
            toast.success(
                'Interconnection request rejected.'
            )

            rejectModal.value = false
            rejectionType.value = null
            rejectForm.reset()
        },

        onError: (errors) => {
            toast.error(
                Object.values(errors)[0] ??
                'Failed to reject request.'
            )
        },
    })
}

const cancelRequest = () => {
    if (
        !confirm(
            'Are you sure you want to cancel this interconnection request?'
        )
    ) {
        return
    }

    router.patch(
        `/admin/interconnections/${props.interconnection.id}/cancel`,
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                toast.success(
                    'Interconnection request cancelled.'
                )
            },

            onError: (errors) => {
                toast.error(
                    Object.values(errors)[0] ??
                    'Failed to cancel request.'
                )
            },
        }
    )
}

const actionTitle = computed(() => {
    switch (props.interconnection.status) {
        case 'waiting_destination_approval':
            return 'Destination Approval Required'

        case 'waiting_dc_approval':
            return 'Data Center Approval Required'

        case 'approved':
            return 'Ready for Installation'

        case 'in_progress':
            return 'Installation in Progress'

        case 'testing':
            return 'Testing in Progress'

        default:
            return 'No Action Required'
    }
})

const actionDescription = computed(() => {
    switch (props.interconnection.status) {
        case 'waiting_destination_approval':
            return 'Review the destination port and approve or reject this request.'

        case 'waiting_dc_approval':
            return 'Review the interconnection request and approve it for installation.'

        case 'approved':
            return 'The request has been approved and is ready for physical installation.'

        case 'in_progress':
            return 'The interconnection is currently being installed.'

        case 'testing':
            return 'Installation is complete. Perform testing before completing the request.'

        default:
            return ''
    }
})

const isFailedStatus = computed(() => {
    return [
        'rejected',
        'cancelled',
    ].includes(
        props.interconnection.status
    )
})

</script>


<template>

    <Head :title="title" />

    <div class="p-4">

        <!-- HEADER -->

        <div class="mb-6 flex items-start justify-between">

            <div class="flex items-start gap-3">

                <Link href="/admin/interconnections" class="rounded-xl border p-2 text-gray-500 hover:bg-gray-50">

                    <ArrowLeft class="h-4 w-4" />

                </Link>

                <div>

                    <div class="flex items-center gap-3">

                        <h1 class="text-lg font-semibold">
                            {{ item.request_number }}
                        </h1>

                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                            {{ statusLabel }}
                        </span>

                    </div>

                    <p class="mt-1 text-sm text-muted-foreground">
                        Interconnection Request
                    </p>

                </div>

            </div>

        </div>

        <div v-if="
            ![
                'completed',
                'rejected',
                'cancelled'
            ].includes(interconnection.status)
        " class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">
                        Current Action
                    </p>

                    <h3 class="mt-1 text-base font-semibold text-gray-900">
                        {{ actionTitle }}
                    </h3>

                    <p class="mt-1 text-sm text-gray-500">
                        {{ actionDescription }}
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <!-- Destination Approval -->
                    <template v-if="
                        interconnection.status ===
                        'waiting_destination_approval'
                    ">
                        <button type="button" @click="approveDestination"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800">
                            Approve
                        </button>

                        <button type="button" @click="
                            openRejectModal('destination')
                            " class="inline-flex items-center rounded-lg border border-red-200 px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50">
                            Reject
                        </button>
                    </template>

                    <!-- DC Approval -->
                    <template v-else-if="
                        interconnection.status ===
                        'waiting_dc_approval'
                    ">
                        <button type="button" @click="approveDc"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800">
                            Approve
                        </button>

                        <button type="button" @click="
                            openRejectModal('dc')
                            " class="inline-flex items-center rounded-lg border border-red-200 px-4 py-2.5 text-sm font-medium text-red-600 transition hover:bg-red-50">
                            Reject
                        </button>
                    </template>

                    <!-- Start Installation -->
                    <template v-else-if="
                        interconnection.status ===
                        'approved'
                    ">
                        <button type="button" @click="startInstallation"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800">
                            Start Installation
                        </button>
                    </template>

                    <!-- Start Testing -->
                    <template v-else-if="
                        interconnection.status ===
                        'in_progress'
                    ">
                        <button type="button" @click="startTesting"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800">
                            Start Testing
                        </button>
                    </template>

                    <!-- Complete -->
                    <template v-else-if="
                        interconnection.status ===
                        'testing'
                    ">
                        <button type="button" @click="completeInterconnection"
                            class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-gray-800">
                            Complete
                        </button>
                    </template>

                    <!-- Cancel -->
                    <button type="button" @click="cancelRequest"
                        class="inline-flex items-center rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isFailedStatus" class="rounded-2xl border border-red-100 bg-red-50 p-5">
            <p class="text-sm font-semibold text-red-800">
                Request {{
                    interconnection.status === 'rejected'
                        ? 'Rejected'
                        : 'Cancelled'
                }}
            </p>

            <p class="mt-1 text-sm text-red-700">
                {{
                    interconnection.rejection_reason ||
                    interconnection.notes ||
                'No additional reason provided.'
                }}
            </p>
        </div>


        <!-- CONNECTION -->

        <div class="rounded-2xl border bg-white p-6 shadow-sm dark:bg-transparent">

            <div class="grid grid-cols-1 items-center gap-8 lg:grid-cols-[1fr_auto_1fr]">


                <!-- SOURCE -->

                <div>

                    <div class="mb-4 flex items-center gap-2">

                        <div class="h-2.5 w-2.5 rounded-full bg-blue-500"></div>

                        <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Source
                        </span>

                    </div>

                    <h2 class="text-base font-semibold">
                        {{ source.client?.company_name || '-' }}
                    </h2>

                    <div class="mt-4 space-y-2 text-sm">

                        <div class="flex items-center gap-2">

                            <MapPin class="h-4 w-4 text-gray-400" />

                            <span>
                                Rack {{ source.rack?.code || '-' }}
                            </span>

                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">

                            <p class="font-medium">
                                {{ source.device?.divice_name || '-' }}
                            </p>

                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ source.device?.model || '-' }}
                            </p>

                            <div
                                class="mt-3 inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-medium shadow-sm">

                                <Cable class="mr-2 h-4 w-4 text-blue-500" />

                                {{ source.port?.port_name || '-' }}

                            </div>

                        </div>

                    </div>

                </div>


                <!-- CONNECTION -->

                <div class="flex flex-col items-center gap-3">

                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">

                        <Cable class="h-5 w-5 text-gray-500" />

                    </div>

                    <ArrowRight class="hidden h-5 w-5 text-gray-400 lg:block" />

                    <div class="text-center">

                        <p class="text-xs font-medium">
                            {{ item.cable_type?.replace('_', ' ') }}
                        </p>

                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ item.connector_type || '-' }}
                        </p>

                        <p v-if="item.cable_length" class="mt-1 text-xs text-muted-foreground">
                            {{ item.cable_length }}
                            {{ item.cable_length_unit }}
                        </p>

                    </div>

                </div>


                <!-- DESTINATION -->

                <div>

                    <div class="mb-4 flex items-center gap-2">

                        <div class="h-2.5 w-2.5 rounded-full bg-green-500"></div>

                        <span class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                            Destination
                        </span>

                    </div>

                    <h2 class="text-base font-semibold">
                        {{ destination.client?.company_name || '-' }}
                    </h2>

                    <div class="mt-4 space-y-2 text-sm">

                        <div class="flex items-center gap-2">

                            <MapPin class="h-4 w-4 text-gray-400" />

                            <span>
                                Rack {{ destination.rack?.code || '-' }}
                            </span>

                        </div>

                        <div class="rounded-xl bg-gray-50 p-4">

                            <p class="font-medium">
                                {{ destination.device?.divice_name || '-' }}
                            </p>

                            <p class="mt-1 text-xs text-muted-foreground">
                                {{ destination.device?.model || '-' }}
                            </p>

                            <div
                                class="mt-3 inline-flex items-center rounded-lg bg-white px-3 py-2 text-xs font-medium shadow-sm">

                                <Cable class="mr-2 h-4 w-4 text-green-500" />

                                {{ destination.port?.port_name || '-' }}

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- PROGRESS -->

        <div class="mt-5 rounded-2xl border bg-white p-6 shadow-sm dark:bg-transparent">

            <div class="mb-6 flex items-center justify-between">

                <div>

                    <h2 class="text-sm font-semibold">
                        Request Progress
                    </h2>

                    <p class="mt-1 text-xs text-muted-foreground">
                        Track the interconnection lifecycle.
                    </p>

                </div>

                <span class="text-lg font-semibold">
                    {{ item.progress }}%
                </span>

            </div>


            <!-- BAR -->

            <div class="mb-8 h-2 overflow-hidden rounded-full bg-gray-100">

                <div class="h-full rounded-full bg-primary transition-all" :style="{
                    width: `${item.progress}%`
                }"></div>

            </div>


            <!-- STEPS -->

            <div class="grid grid-cols-2 gap-6 md:grid-cols-3 xl:grid-cols-6">

                <div v-for="(step, index) in steps" :key="step.key" class="relative">

                    <div class="flex items-center gap-3">

                        <CheckCircle2 v-if="isStepDone(index)" class="h-5 w-5 shrink-0 text-green-500" />

                        <Clock3 v-else-if="isCurrentStep(index)" class="h-5 w-5 shrink-0 text-blue-500" />

                        <Circle v-else class="h-5 w-5 shrink-0 text-gray-300" />

                        <span :class="[
                            'text-xs',
                            isCurrentStep(index)
                                ? 'font-semibold text-gray-900'
                                : 'text-muted-foreground'
                        ]">
                            {{ step.label }}
                        </span>

                    </div>

                </div>

            </div>

        </div>


        <!-- INFORMATION -->

        <div class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">

            <div class="rounded-2xl border bg-white p-6 shadow-sm dark:bg-transparent">

                <h2 class="mb-5 text-sm font-semibold">
                    Request Information
                </h2>

                <div class="space-y-4 text-sm">

                    <div class="flex justify-between gap-4">

                        <span class="text-muted-foreground">
                            Requester
                        </span>

                        <span class="font-medium">
                            {{ item.requester_client?.company_name || '-' }}
                        </span>

                    </div>

                    <div class="flex justify-between gap-4">

                        <span class="text-muted-foreground">
                            Type
                        </span>

                        <span class="font-medium capitalize">
                            {{ item.interconnection_type?.replace('_', ' ') }}
                        </span>

                    </div>

                    <div class="flex justify-between gap-4">

                        <span class="text-muted-foreground">
                            Requested At
                        </span>

                        <span class="font-medium">
                            {{ item.requested_at || '-' }}
                        </span>

                    </div>

                    <div v-if="interconnection.rejection_reason" class="rounded-xl border border-red-100 bg-red-50 p-4">
                        <p class="text-sm font-medium text-red-800">
                            Rejection Reason
                        </p>

                        <p class="mt-1 text-sm text-red-700">
                            {{ interconnection.rejection_reason }}
                        </p>
                    </div>

                </div>

            </div>


            <div class="rounded-2xl border bg-white p-6 shadow-sm dark:bg-transparent">

                <h2 class="mb-5 text-sm font-semibold">
                    Description
                </h2>

                <p class="text-sm leading-6 text-muted-foreground">
                    {{ item.description || 'No description provided.' }}
                </p>

            </div>

        </div>

    </div>


    <div v-if="rejectModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-lg rounded-2xl bg-white shadow-xl">
            <div class="border-b border-gray-100 px-6 py-5">
                <h3 class="text-lg font-semibold text-gray-900">
                    Reject Interconnection
                </h3>

                <p class="mt-1 text-sm text-gray-500">
                    Please provide a reason for rejecting this request.
                </p>
            </div>

            <div class="px-6 py-5">
                <label class="mb-2 block text-sm font-medium text-gray-700">
                    Rejection Reason
                </label>

                <textarea v-model="rejectForm.rejection_reason" rows="5"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-gray-400 focus:ring-2 focus:ring-gray-100"
                    placeholder="Enter rejection reason..." />

                <p v-if="rejectForm.errors.rejection_reason" class="mt-2 text-sm text-red-600">
                    {{ rejectForm.errors.rejection_reason }}
                </p>
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-100 px-6 py-4">
                <button type="button" @click="rejectModal = false"
                    class="rounded-lg border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">
                    Cancel
                </button>

                <button type="button" @click="submitReject" :disabled="rejectForm.processing"
                    class="rounded-lg bg-red-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-50">
                    {{
                        rejectForm.processing
                            ? 'Rejecting...'
                            : 'Reject Request'
                    }}
                </button>
            </div>
        </div>
    </div>
</template>