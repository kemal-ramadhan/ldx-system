<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue'
import PasswordInput from '@/components/PasswordInput.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Spinner } from '@/components/ui/spinner'

const props = defineProps<{
    title: string
    user: any
    roles: any[]
}>()

const form = useForm({
    name: props.user.name ?? '',
    email: props.user.email ?? '',
    phone: props.user.phone ?? '',
    role_id: props.user.role_id ?? '',
    password: '',
    password_confirmation: '',
})

const updateUser = () => {
    form.put(`/admin/users/${props.user.id}`)
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'User Management',
                href: '/admin/users',
            },
            {
                title: 'Edit User',
                href: '#',
            },
        ],
    },
})
</script>

<template>
    <Head :title="title" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <h1 class="text-xl font-bold">Edit User</h1>

        <Form
            @submit.prevent="updateUser"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors }"
            class="flex flex-col gap-3"
        >
            <div
                class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full rounded-md border overflow-x-auto p-4"
            >
                <div class="grid gap-3 w-full">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        autofocus
                        autocomplete="name"
                        placeholder="Full Name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-3 w-full">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-3 w-full">
                    <Label for="role">Role</Label>

                    <select
                        v-model="form.role_id"
                        class="rounded-lg border border-gray-200 px-4 py-2 dark:border-gray-700 dark:bg-gray-900"
                    >
                        <option
                            v-for="role in roles"
                            :key="role.id"
                            :value="role.id"
                        >
                            {{ role.name }}
                        </option>
                    </select>

                    <InputError :message="errors.role_id" />
                </div>

                <div class="grid gap-3 w-full">
                    <Label for="phone">Phone</Label>
                    <Input
                        id="phone"
                        v-model="form.phone"
                        type="text"
                        required
                        placeholder="Phone Number"
                    />
                    <InputError :message="errors.phone" />
                </div>

                <div class="grid gap-3 w-full">
                    <Label for="password">
                        Password
                        <span class="text-xs text-gray-500">
                            (Kosongkan jika tidak diubah)
                        </span>
                    </Label>

                    <PasswordInput
                        id="password"
                        v-model="form.password"
                        name="password"
                        placeholder="New Password"
                    />

                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-3 w-full">
                    <Label for="password_confirmation">
                        Confirm Password
                    </Label>

                    <PasswordInput
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        name="password_confirmation"
                        placeholder="Confirm New Password"
                    />

                    <InputError
                        :message="errors.password_confirmation"
                    />
                </div>
            </div>

            <div class="flex items-center justify-end gap-4">
                <Button
                    type="button"
                    variant="outline"
                    @click="$inertia.visit('/admin/users')"
                >
                    Cancel
                </Button>

                <Button
                    type="submit"
                    :disabled="form.processing"
                >
                    <Spinner
                        v-if="form.processing"
                        class="mr-2 h-4 w-4"
                    />

                    {{
                        form.processing
                            ? 'Updating...'
                            : 'Update User'
                    }}
                </Button>
            </div>
        </Form>
    </div>
</template>