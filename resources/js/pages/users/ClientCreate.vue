<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    title: string;
    user: any;
}>();


const form = useForm({
    company_name: '',
    company_email: '',
    company_phone: '',
    company_npwp: '',
    company_address: '',
    company_city: '',
    company_province: '',
    company_postal_code: '',
    contract_date: '',
    contract_done_date: '',
    status: 'active',
});

const createUser = () => {
    form.post(`/admin/users/${props.user.id}/client`);
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'User Management',
                href: '/admin/users',
            },
        ],
    },
});
</script>

<template>
    <Head :title="title" />
    
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-bold">Create Client {{ props.user?.name || '' }}</h1>
        <!-- Add your form fields here -->
        <Form
            @submit.prevent="createUser"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-3"
        >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full rounded-md border overflow-x-auto p-4">
            <div class="grid gap-3 w-full">
                <Label for="company_name">Company Name <span class="text-red-500">*</span></Label>
                <Input
                    id="company_name"
                    v-model="form.company_name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="company_name"
                    placeholder="Company name"
                />
                <InputError :message="errors.company_name" />
            </div>
            <div class="grid gap-3 w-full">
                <Label for="company_email">Company Email <span class="text-red-500">*</span></Label>
                <Input
                    id="company_email"
                    v-model="form.company_email"
                    type="email"
                    required
                    autofocus
                    :tabindex="2"
                    autocomplete="company_email"
                    name="company_email"
                    placeholder="Company email"
                />
                <InputError :message="errors.company_email" />
            </div>
            <div class="grid gap-3 w-full">
                <Label for="company_phone">Company Phone <span class="text-red-500">*</span></Label>
                <Input
                    id="company_phone"
                    v-model="form.company_phone"
                    type="text"
                    required
                    autofocus
                    :tabindex="3"
                    autocomplete="company_phone"
                    name="company_phone"
                    placeholder="Company phone"
                />
                <InputError :message="errors.company_phone" />
            </div>
            <div class="grid gap-3 w-full">
                <Label for="company_npwp">Company NPWP</Label>
                <Input
                    id="company_npwp"
                    v-model="form.company_npwp"
                    type="text"
                    autofocus
                    :tabindex="4"
                    autocomplete="company_npwp"
                    name="company_npwp"
                    placeholder="Company NPWP"
                />
                <InputError :message="errors.company_npwp" />
            </div>
            <div class="grid col-span-2 gap-3 w-full">
                <Label for="company_address">Company Address <span class="text-red-500">*</span></Label>
                <textarea
                    id="company_address"
                    v-model="form.company_address"
                    required
                    autofocus
                    :tabindex="5"
                    autocomplete="company_address"
                    name="company_address"
                    placeholder="Company address"
                    class="w-full rounded-md border border-gray-300 bg-white py-2 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none resize-none h-24 dark:bg-transparent dark:text-gray-300"
                ></textarea>
                <InputError :message="errors.company_address" />
            </div>
            <div class="grid col-span-2 grid-cols-3 gap-3 w-full">
                <div class="grid gap-3 w-full">
                    <Label for="company_city">Company City <span class="text-red-500">*</span></Label>
                    <Input
                        id="company_city"
                        v-model="form.company_city"
                        type="text"
                        required
                        autofocus
                        :tabindex="6"
                        autocomplete="company_city"
                        name="company_city"
                        placeholder="Company city"
                    />
                    <InputError :message="errors.company_city" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="company_province">Company Province <span class="text-red-500">*</span></Label>
                    <Input
                        id="company_province"
                        v-model="form.company_province"
                        type="text"
                        required
                        autofocus
                        :tabindex="7"
                        autocomplete="company_province"
                        name="company_province"
                        placeholder="Company province"
                    />
                    <InputError :message="errors.company_province" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="company_postal_code">Company Postal Code</Label>
                    <Input
                        id="company_postal_code"
                        v-model="form.company_postal_code"
                        type="text"
                        autofocus
                        :tabindex="8"
                        autocomplete="company_postal_code"
                        name="company_postal_code"
                        placeholder="Company postal code"
                    />
                    <InputError :message="errors.company_postal_code" />
                </div>
            </div>
            <div class="grid gap-3 w-full">
                <Label for="contract_date">Contract Date <span class="text-red-500">*</span></Label>
                <Input
                    id="contract_date"
                    v-model="form.contract_date"
                    type="date"
                    required
                    autofocus
                    :tabindex="9"
                    autocomplete="contract_date"
                    name="contract_date"
                    placeholder="Contract date"
                />
                <InputError :message="errors.contract_date" />
            </div>
            <div class="grid gap-3 w-full">
                <Label for="contract_done_date">Contract Done Date</Label>
                <Input
                    id="contract_done_date"
                    v-model="form.contract_done_date"
                    type="date"
                    autofocus
                    :tabindex="10"
                    autocomplete="contract_done_date"
                    name="contract_done_date"
                    placeholder="Contract done date"
                />
                <InputError :message="errors.contract_done_date" />
            </div>
        </div>
        <div class="flex items-center justify-end gap-4">
            <Button
                type="submit"
                :disabled="form.processing"
                class="bg-primary text-white hover:bg-primary/90 disabled:bg-gray-300 disabled:text-gray-500 dark:text-gray-900"
            >
                <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />

                {{ form.processing ? 'Creating...' : 'Create Client' }}
            </Button>
        </div>
    </Form>
    </div>
</template>