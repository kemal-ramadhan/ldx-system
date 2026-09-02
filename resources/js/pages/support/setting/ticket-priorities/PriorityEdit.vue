<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Code } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    priorities: any;
}>();


const form = useForm({
    name: props.priorities.name,
    description: props.priorities.name,
    color: props.priorities.color,
    level: props.priorities.level,
    response_minutes: props.priorities.response_minutes,
    resolution_minutes: props.priorities.resolution_minutes,
});

const updatePriorities = () => {
    form.put(`/admin/tickets-priority/${props.priorities.id}`);
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Ticket Priorities',
                href: '/admin/tickets-priority',
            },
        ],
    },
});
</script>

<template>

    <Head :title="title" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-bold">Update Priority</h1>
        <!-- Add your form fields here -->
        <Form @submit.prevent="updatePriorities" v-slot="{ errors, processing }" class="flex flex-col gap-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full rounded-md border overflow-x-auto p-4">
                <div class="grid gap-3 w-full">
                    <Label for="name">Priority Name <span class="text-red-500">*</span></Label>
                    <Input id="name" v-model="form.name" type="text" required autofocus :tabindex="1"
                        autocomplete="name" name="code" placeholder="Category Name" />
                    <InputError :message="errors.name" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="color">
                        Color <span class="text-red-500">*</span>
                    </Label>

                    <div class="flex items-center gap-3 rounded-lg border bg-white p-3 dark:bg-transparent">
                        <!-- Color Picker -->
                        <input id="color" v-model="form.color" type="color"
                            class="h-12 w-12 cursor-pointer rounded border border-gray-300 bg-transparent p-0" />

                        <!-- Hex Value -->
                        <Input v-model="form.color" type="text" placeholder="#2563EB" class="font-mono" />

                        <!-- Preview -->
                        <div class="h-10 w-10 rounded-lg border border-gray-300"
                            :style="{ backgroundColor: form.color || '#ffffff' }"></div>
                    </div>

                    <InputError :message="errors.color" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="level">Level <span class="text-red-500">*</span></Label>
                    <Input id="level" v-model="form.level" type="text" required autofocus :tabindex="2"
                        autocomplete="level" name="coloe" placeholder="level" />
                    <InputError :message="errors.level" />
                </div>
                <div class="grid md:col-span-2 gap-3 w-full">
                    <Label for="description">Description <span class="text-red-500">*</span></Label>
                    <textarea id="description" v-model="form.description" required autofocus :tabindex="3"
                        autocomplete="description" name="description" placeholder="description"
                        class="w-full rounded-md border border-gray-300 bg-white py-2 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none resize-none h-24 dark:bg-transparent dark:text-gray-300"></textarea>
                    <InputError :message="errors.description" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="response_minutes">Response Minutes <span class="text-red-500">*</span></Label>
                    <Input id="response_minutes" v-model="form.response_minutes" type="number" required autofocus
                        :tabindex="2" autocomplete="response_minutes" name="coloe" placeholder="response minutes" />
                    <InputError :message="errors.response_minutes" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="resolution_minutes">Resolution Minutes <span class="text-red-500">*</span></Label>
                    <Input id="resolution_minutes" v-model="form.resolution_minutes" type="number" required autofocus
                        :tabindex="2" autocomplete="resolution_minutes" name="coloe" placeholder="resolution minutes" />
                    <InputError :message="errors.response_minutes" />
                </div>
            </div>
            <div class="flex items-center justify-end gap-4">
                <Button type="button"
                    class="bg-gray-300 text-gray-700 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    @click="$inertia.visit('/admin/tickets-priority')">
                    Cancel
                </Button>
                <Button type="submit" :disabled="form.processing"
                    class="bg-primary text-white hover:bg-primary/90 disabled:bg-gray-300 disabled:text-gray-500 dark:text-gray-900">
                    <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />

                    {{ form.processing ? 'Updating...' : 'Update Priority' }}
                </Button>
            </div>
        </Form>
    </div>
</template>