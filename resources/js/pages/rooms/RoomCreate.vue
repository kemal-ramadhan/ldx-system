<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Code } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    locations: any;
}>();


const form = useForm({
    code: '',
    name: '',
    description: '',
    location_id: '',
});

const createRoom = () => {
    form.post(`/admin/rooms`);
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Room Management',
                href: '/admin/rooms',
            },
        ],
    },
});
</script>

<template>

    <Head :title="title" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-bold">Create Room</h1>
        <!-- Add your form fields here -->
        <Form @submit.prevent="createRoom" v-slot="{ errors, processing }" class="flex flex-col gap-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full rounded-md border overflow-x-auto p-4">
                <div class="grid gap-3 w-full">
                    <Label for="code">Room Code <span class="text-red-500">*</span></Label>
                    <Input id="code" v-model="form.code" type="text" required autofocus :tabindex="1"
                        autocomplete="code" name="code" placeholder="Room code" />
                    <InputError :message="errors.code" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="name">Room Name <span class="text-red-500">*</span></Label>
                    <Input id="name" v-model="form.name" type="text" required autofocus :tabindex="2"
                        autocomplete="name" name="name" placeholder="Room name" />
                    <InputError :message="errors.name" />
                </div>
                <div class="grid md:col-span-2 gap-3 w-full">
                    <Label for="description">Room Description <span class="text-red-500">*</span></Label>
                    <textarea id="description" v-model="form.description" required autofocus :tabindex="3"
                        autocomplete="description" name="description" placeholder="Room description"
                        class="w-full rounded-md border border-gray-300 bg-white py-2 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none resize-none h-24 dark:bg-transparent dark:text-gray-300 dark:border-gray-700"></textarea>
                    <InputError :message="errors.description" />
                </div>
                <div class="grid md:col-span-2 gap-3 w-full">
                    <Label for="description">Location<span class="text-red-500">*</span></Label>
                    <select v-model="form.location_id" required
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700">
                        <option value="">Locations</option>

                        <option v-for="item in locations" :key="item.id" :value="item.id">
                            {{ item.name }}
                        </option>
                    </select>
                    <InputError :message="errors.location_id" />
                </div>

            </div>
            <div class="flex items-center justify-end gap-4">
                <Button type="button"
                    class="bg-gray-300 text-gray-700 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    @click="$inertia.visit('/admin/rooms')">
                    Cancel
                </Button>
                <Button type="submit" :disabled="form.processing"
                    class="bg-primary text-white hover:bg-primary/90 disabled:bg-gray-300 disabled:text-gray-500 dark:text-gray-900">
                    <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />

                    {{ form.processing ? 'Creating...' : 'Create Room' }}
                </Button>
            </div>
        </Form>
    </div>
</template>