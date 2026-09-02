<script setup lang="ts">
import { Head, Form, useForm, Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    title: string;
    roles: any[];
}>();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    phone: '',
    role_id: '',
});

const createUser = () => {
    form.post('/admin/users');
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

onMounted(() => {
    const defaultRole = props.roles.find((role) => role.slug === 'client');
    if (defaultRole) {
        form.role_id = defaultRole.id;
    }
});
</script>

<template>
    <Head :title="title" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-bold">Create User</h1>
        <!-- Add your form fields here -->
        <Form
            @submit.prevent="createUser"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-3"
        >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full rounded-md border overflow-x-auto p-4">
            <div class="grid gap-3 w-full">
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    autofocus
                    :tabindex="1"
                    autocomplete="name"
                    name="name"
                    placeholder="Full name"
                />
                <InputError :message="errors.name" />
            </div>
            <div class="grid gap-3 w-full">
                <Label for="email">Email address</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    autofocus
                    :tabindex="2"
                    autocomplete="email"
                    name="email"
                    placeholder="email@example.com"
                />
                <InputError :message="errors.email" />
            </div>
            <div class="grid gap-3 w-full">
                <Label for="role">Role</Label>
                <select
                    v-model="form.role_id"
                    class="rounded-lg border border-gray-200 px-4 py-2"
                >

                    <option
                        v-for="item in roles"
                        :key="item.id"
                        :value="item.id"
                        :tabindex="3"
                    >
                        {{ item.name }}
                    </option>
                </select>
                <InputError :message="errors.role" />
            </div>
            <div class="grid gap-3 w-full">
                <Label for="phone">Phone</Label>
                <Input
                    id="phone"
                    v-model="form.phone"
                    type="text"
                    required
                    autofocus
                    :tabindex="4"
                    autocomplete="name"
                    name="phone"
                    placeholder="Phone number"
                />
                <InputError :message="errors.phone" />
            </div>
            <div class="grid gap-3 w-full">
                <Label for="password">Password</Label>
                <PasswordInput
                    id="password"
                    v-model="form.password"
                    required
                    :tabindex="5"
                    name="password"
                    placeholder="••••••••"
                />
                <InputError :message="errors.password" />
            </div>
            <div class="grid gap-3 w-full">
                <Label for="password_confirmation">Confirm password</Label>
                <PasswordInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    required
                    :tabindex="6"
                    autocomplete="new-password"
                    name="password_confirmation"
                    placeholder="Confirm password"
                />
                <InputError :message="errors.password_confirmation" />
            </div>
        </div>
        <div class="flex items-center justify-end gap-4">
            <Button
                type="button"
                class="bg-gray-300 text-gray-700 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                @click="$inertia.visit('/admin/users')"
            >
                Cancel
            </Button>
            <Button
                type="submit"
                :disabled="form.processing"
                class="bg-primary text-white hover:bg-primary/90 disabled:bg-gray-300 disabled:text-gray-500 dark:text-gray-900"
            >
                <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />

                {{ form.processing ? 'Creating...' : 'Create User' }}
            </Button>
        </div>
    </Form>
    </div>
</template>