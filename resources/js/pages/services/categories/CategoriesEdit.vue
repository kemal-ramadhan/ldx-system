<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Code } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    category: any;
}>();


const form = useForm({
    categori: props.category.categori,
    description: props.category.description,
});


const updateCategory = () => {
    form.put(`/admin/categories/${props.category.id}`);
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Categories Management',
                href: '/admin/categories',
            },
        ],
    },
});
</script>

<template>
    <Head :title="title" />
    
    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-bold">Create Location</h1>
        <!-- Add your form fields here -->
        <Form
            @submit.prevent="updateCategory"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-3"
        >
        <div class="grid grid-cols-1 gap-3 w-full rounded-md border overflow-x-auto p-4">
            <div class="grid gap-3 w-full">
                <Label for="categori">Category <span class="text-red-500">*</span></Label>
                <Input
                    id="code"
                    v-model="form.categori"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="categori"
                    name="code"
                    placeholder="Rack / Power / Network ..."
                />
                <InputError :message="errors.categori" />
            </div>
            <div class="grid col-span-2 gap-3 w-full">
                <Label for="description">Description <span class="text-red-500">*</span></Label>
                <textarea
                    id="address"
                    v-model="form.description"
                    required
                    autofocus
                    :tabindex="2"
                    autocomplete="description"
                    name="address"
                    placeholder="description"
                    class="w-full rounded-md border border-gray-300 bg-white py-2 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none resize-none h-24 dark:bg-transparent dark:text-gray-300"
                ></textarea>
                <InputError :message="errors.description" />
            </div>
        </div>
        <div class="flex items-center justify-end gap-4">
            <Button
                type="button"
                class="bg-gray-300 text-gray-700 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                @click="$inertia.visit('/admin/categories')"
            >
                Cancel
            </Button>
            <Button
                type="submit"
                :disabled="form.processing"
                class="bg-primary text-white hover:bg-primary/90 disabled:bg-gray-300 disabled:text-gray-500 dark:text-gray-900"
            >
                <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />

                {{ form.processing ? 'Updating...' : 'Update Category' }}
            </Button>
        </div>
    </Form>
    </div>
</template>