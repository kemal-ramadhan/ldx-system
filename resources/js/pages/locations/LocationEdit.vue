<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Code } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    location: any;
}>();


const form = useForm({
    name: props.location.name,
    code: props.location.code,
    address: props.location.address,
});

const updateLocation = () => {
    form.put(`/admin/locations/${props.location.id}`);
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Location Management',
                href: '/admin/locations',
            },
        ],
    },
});
</script>

<template>
    <Head :title="title" />
    
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-bold">Edit Location</h1>
        <!-- Add your form fields here -->
        <Form
            @submit.prevent="updateLocation"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-3"
        >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full rounded-md border overflow-x-auto p-4">
            <div class="grid gap-3 w-full">
                <Label for="code">Location Code <span class="text-red-500">*</span></Label>
                <Input
                    id="code"
                    v-model="form.code"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="code"
                    name="code"
                    placeholder="Location code"
                />
                <InputError :message="errors.code" />
            </div>
            <div class="grid gap-3 w-full">
                <Label for="name">Location Name <span class="text-red-500">*</span></Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    :tabindex="2"
                    autocomplete="name"
                    name="name"
                    placeholder="Location name"
                />
                <InputError :message="errors.name" />
            </div>
            <div class="grid col-span-2 gap-3 w-full">
                <Label for="address">Location Address <span class="text-red-500">*</span></Label>
                <textarea
                    id="address"
                    v-model="form.address"
                    required
                    autofocus
                    :tabindex="3"
                    autocomplete="address"
                    name="address"
                    placeholder="Location address"
                    class="w-full rounded-md border border-gray-300 bg-white py-2 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none resize-none h-24 dark:bg-transparent dark:text-gray-300"
                ></textarea>
                <InputError :message="errors.address" />
            </div>
        </div>
        <div class="flex items-center justify-end gap-4">
            <Button
                type="submit"
                :disabled="form.processing"
                class="bg-primary text-white hover:bg-primary/90 disabled:bg-gray-300 disabled:text-gray-500 dark:text-gray-900"
            >
                <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />

                {{ form.processing ? 'Updating...' : 'Update Location' }}
            </Button>
        </div>
    </Form>
    </div>
</template>