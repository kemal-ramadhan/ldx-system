<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import {
    Plus,
    MoreHorizontal,
    Trash2
} from 'lucide-vue-next'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
    DialogTrigger,
} from '@/components/ui/dialog'

import Multiselect from 'vue-multiselect'
import 'vue-multiselect/dist/vue-multiselect.min.css'

import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { route } from 'ziggy-js'
import { toast } from 'vue-sonner'


const page = usePage()

interface User {
    id: number
    name: string
    email: string
}

interface MemberInvite {
    id: number
    user: User
    invited_at?: string
    created_at?: string
    status?: string
    role?: string
}

interface Clients {
    pics?: MemberInvite[]
    [key: string]: any
}


const props = defineProps<{ clients?: Clients, rack: any }>()

const openMenu = ref<number | null>(null)
const openEditOwner = ref(false)
const selectedOwner = ref<any>(null)
const toggleMenu = (id: number) => {
    openMenu.value = openMenu.value === id ? null : id
}

const form = useForm<{ owner_id: number | null }>({
    owner_id: null
})

const openAddOwner = ref(false)

const ownerForm = useForm({
    client_id: '',
    rented_units: '',
    monthly_fee: '',
    rental_start_date: '',
    rental_end_date: '',
    status: 'active',
})

const selectedClient = ref<any>(null)

watch(selectedClient, (val) => {
    ownerForm.client_id = val?.id || ''
})

const editOwner = (member: any) => {

    selectedOwner.value = member

    ownerForm.client_id = member.client_id
    ownerForm.rented_units = member.rented_units
    ownerForm.monthly_fee = member.monthly_fee
    ownerForm.rental_start_date = member.rental_start_date
    ownerForm.rental_end_date = member.rental_end_date
    ownerForm.status = member.status

    openEditOwner.value = true

    openMenu.value = null
}

const submitOwner = () => {

    ownerForm.post(`/admin/racks/${props.rack.id}/owners`, {

        preserveScroll: true,
        onSuccess: () => {
            toast.success('Rack owner assigned successfully.')
            openAddOwner.value = false
            ownerForm.reset()
        },

        onError: (errors) => {
            Object.values(errors).forEach((message: any) => {
                toast.error(message)
            })
        }
    })
}

const deleteMember = (id: number) => {
    form.delete(`/admin/racks/${id}/owner`, {
        preserveScroll: true,
        onSuccess: () => {
            openMenu.value = null
            toast.success('Rack owner removed successfully.')
        },
        onError: (errors) => {

            Object.values(errors).forEach((message: any) => {

                toast.error(message)
            })
        }
    })
}

const updateOwner = () => {
    ownerForm.put(
        `/admin/racks/owners/${selectedOwner.value.id}`,
        {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Owner updated successfully.')
                openEditOwner.value = false
            },

            onError: (errors) => {
                Object.values(errors).forEach((message: any) => {
                    toast.error(message)
                })
            }
        }
    )
}
</script>

<template>

    <div class="p-4">
        <div class="w-full">

            <div class="flex justify-between items-center">
                <!-- Header -->
                <div class="mb-8">
                    <h2 class="text-lg font-medium">
                        Owner with {{ props.rack?.client_racks?.length || 0 }} members
                    </h2>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Manage and view owner rack
                    </p>
                </div>
                <div class="flex gap-2">
                    <Dialog v-model:open="openAddOwner">

                        <DialogTrigger as-child>
                            <button
                                class="inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90">
                                <Plus class="w-4 h-4 mr-2" />
                                Add Owner
                            </button>
                        </DialogTrigger>

                        <DialogContent class="sm:max-w-xl">

                            <DialogHeader>
                                <DialogTitle>
                                    Add Rack Owner
                                </DialogTitle>

                                <DialogDescription>
                                    Assign client ownership to this rack
                                </DialogDescription>
                            </DialogHeader>

                            <!-- FORM -->
                            <form @submit.prevent="submitOwner" class="grid gap-4 py-4">

                                <!-- CLIENT -->
                                <div class="grid gap-2">
                                    <Label>
                                        Client
                                    </Label>

                                    <Multiselect v-model="selectedClient" :options="props.clients" label="company_name"
                                        track-by="id" placeholder="Search Client" :searchable="true"
                                        :close-on-select="true" :allow-empty="false" />
                                </div>

                                <!-- RENTED UNIT -->
                                <div class="grid gap-2">
                                    <Label>
                                        Rented Units
                                    </Label>

                                    <Input v-model="ownerForm.rented_units" type="number" placeholder="10" required />
                                </div>

                                <!-- MONTHLY FEE -->
                                <div class="grid gap-2">
                                    <Label>
                                        Monthly Fee
                                    </Label>

                                    <Input v-model="ownerForm.monthly_fee" type="number" placeholder="1000000"
                                        required />
                                </div>

                                <!-- START DATE -->
                                <div class="grid gap-2">
                                    <Label>
                                        Rental Start Date
                                    </Label>

                                    <Input v-model="ownerForm.rental_start_date" type="date" required />
                                </div>

                                <!-- END DATE -->
                                <div class="grid gap-2">
                                    <Label>
                                        Rental End Date
                                    </Label>

                                    <Input v-model="ownerForm.rental_end_date" type="date" />
                                </div>

                                <!-- STATUS -->
                                <div class="grid gap-2">
                                    <Label>
                                        Status
                                    </Label>

                                    <select v-model="ownerForm.status" class="rounded-md border px-3 py-2 text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                                        <option value="active">
                                            Active
                                        </option>

                                        <option value="inactive">
                                            Inactive
                                        </option>

                                        <option value="suspended">
                                            Suspended
                                        </option>

                                        <option value="terminated">
                                            Terminated
                                        </option>
                                    </select>
                                </div>

                                <DialogFooter>
                                    <Button type="submit" :disabled="ownerForm.processing">
                                        {{
                                            ownerForm.processing
                                                ? 'Saving...'
                                                : 'Save Owner'
                                        }}
                                    </Button>
                                </DialogFooter>

                            </form>

                        </DialogContent>

                    </Dialog>
                </div>
            </div>

            <!-- Invited Members List -->
            <div class="mt-8 mb-32">
                <div class="mt-4 grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                    <!-- EMPTY STATE -->
                    <div v-if="!props.rack?.client_racks?.length"
                        class="flex flex-col items-center justify-center rounded-2xl border border-dashed bg-gray-50 dark:bg-gray-900 dark:border-gray-700 px-6 py-14 text-center">

                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-800">
                            <Plus class="h-8 w-8 text-gray-500" />
                        </div>

                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            No Owner Assigned
                        </h3>

                        <p class="mt-2 max-w-md text-sm text-muted-foreground">
                            This rack does not have any owners yet.
                            Add a client owner to start managing rented rack units.
                        </p>

                        <button @click="openAddOwner = true"
                            class="mt-5 inline-flex items-center rounded-lg bg-primary px-4 py-2 text-sm font-medium text-white hover:bg-primary/90">
                            <Plus class="mr-2 h-4 w-4" />
                            Add Owner
                        </button>
                    </div>
                    <div v-for="member in props.rack?.client_racks || []" :key="member.id"
                        class="relative rounded-2xl border bg-white dark:bg-gray-900 dark:border-gray-800 p-4 shadow-sm">
                        <!-- More Menu -->
                        <div class="absolute right-4 top-4">

                            <button @click="toggleMenu(member.id)" class="rounded-lg p-2 transition hover:bg-gray-100 dark:hover:bg-gray-800">
                                <MoreHorizontal class="h-5 w-5 text-gray-500" />
                            </button>

                            <!-- DROPDOWN -->
                            <div v-if="openMenu === member.id"
                                class="absolute right-0 top-12 z-20 w-44 overflow-hidden rounded-xl border bg-white dark:bg-gray-900 dark:border-gray-800 shadow-lg">

                                <!-- DETAIL -->
                                <button
                                    class="flex w-full items-center gap-2 px-4 py-3 text-sm transition hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <Eye class="h-4 w-4" />
                                    Detail
                                </button>

                                <!-- UPDATE -->
                                <button @click="editOwner(member)"
                                    class="flex w-full items-center gap-2 px-4 py-3 text-sm transition hover:bg-gray-50 dark:hover:bg-gray-800">
                                    <Pencil class="h-4 w-4" />
                                    Update
                                </button>

                                <!-- DELETE -->
                                <button @click="deleteMember(member.id)"
                                    class="flex w-full items-center gap-2 px-4 py-3 text-sm text-red-500 transition hover:bg-red-50 dark:hover:bg-red-900/20">
                                    <Trash2 class="h-4 w-4" />
                                    Remove
                                </button>

                            </div>

                        </div>

                        <!-- Content -->
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-300 dark:bg-gray-800 text-sm font-medium text-gray-600 dark:text-gray-300">
                                {{
                                    member.client?.company_name
                                        .split(' ')
                                        .map((n: string) => n[0])
                                        .join('')
                                }}
                            </div>

                            <!-- Info -->
                            <div class="pr-10">
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ member.client?.company_name }}

                                    <span class="text-sm text-muted-foreground">
                                        ({{ member.status }})
                                    </span>
                                </p>

                                <p class="text-sm text-muted-foreground">
                                    {{ member.client?.company_email }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Rent Unit : {{ member.rented_units }}
                                </p>

                                <p class="text-sm text-muted-foreground">
                                    Rent on {{
                                        member.rental_start_date ? new Date(member.created_at).toLocaleDateString('en-US', {
                                            day: 'numeric',
                                            month: 'long',
                                            year: 'numeric'
                                        }) : 'N/A'
                                    }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    </div>

    <div v-if="openEditOwner" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">

        <div class="w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-900 shadow-xl p-6 border dark:border-gray-800">

            <!-- HEADER -->
            <div class="mb-6 flex items-center justify-between">

                <div>
                    <h2 class="text-xl font-bold">
                        Update Owner
                    </h2>

                    <p class="text-sm text-muted-foreground">
                        Update rack owner information
                    </p>
                </div>

                <button @click="openEditOwner = false" class="rounded-lg p-2 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                    ✕
                </button>
            </div>

            <!-- FORM -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">

                <div>
                    <label class="mb-2 block text-sm font-medium dark:text-gray-300">
                        Rent Units
                    </label>

                    <input v-model="ownerForm.rented_units" type="number" class="w-full rounded-lg border px-4 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium dark:text-gray-300">
                        Monthly Fee
                    </label>

                    <input v-model="ownerForm.monthly_fee" type="number" class="w-full rounded-lg border px-4 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium dark:text-gray-300">
                        Start Date
                    </label>

                    <input v-model="ownerForm.rental_start_date" type="date"
                        class="w-full rounded-lg border px-4 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium dark:text-gray-300">
                        End Date
                    </label>

                    <input v-model="ownerForm.rental_end_date" type="date" class="w-full rounded-lg border px-4 py-2 dark:bg-gray-800 dark:border-gray-700 dark:text-white" />
                </div>

            </div>

            <!-- ACTION -->
            <div class="mt-6 flex justify-end gap-2">

                <button @click="openEditOwner = false" class="rounded-lg border px-4 py-2 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    Cancel
                </button>

                <button @click="updateOwner" class="rounded-lg bg-primary px-4 py-2 text-white dark:text-gray-900">
                    Update Owner
                </button>

            </div>

        </div>

    </div>
</template>

<style>
html.dark .multiselect__tags {
    background-color: #1f2937 !important;
    border-color: #374151 !important;
    color: white !important;
}
html.dark .multiselect__input {
    background-color: #1f2937 !important;
    color: white !important;
}
html.dark .multiselect__single {
    background-color: #1f2937 !important;
    color: white !important;
}
html.dark .multiselect__content-wrapper {
    background-color: #1f2937 !important;
    border-color: #374151 !important;
}
html.dark .multiselect__option {
    color: white !important;
    background-color: #1f2937 !important;
}
html.dark .multiselect__option--highlight {
    background-color: #374151 !important;
    color: white !important;
}
</style>
