<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'

const props = defineProps<{
    title: string
    user: any
}>()

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'User Management',
                href: '/admin/users',
            },
            {
                title: 'Detail User',
                href: '#',
            },
        ],
    },
})
</script>

<template>
    <Head :title="title" />

    <div class="flex flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">Detail User</h1>

            <div class="flex gap-2">
                <Link :href="`/admin/users/${user.id}/edit`">
                    <Button>
                        Edit User
                    </Button>
                </Link>

                <Link href="/admin/users">
                    <Button variant="outline">
                        Back
                    </Button>
                </Link>
            </div>
        </div>

        <div class="rounded-lg border bg-background p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <Label class="text-muted-foreground">
                        Name
                    </Label>

                    <div class="mt-1 font-medium">
                        {{ user.name }}
                    </div>
                </div>

                <div>
                    <Label class="text-muted-foreground">
                        Email
                    </Label>

                    <div class="mt-1 font-medium">
                        {{ user.email }}
                    </div>
                </div>

                <div>
                    <Label class="text-muted-foreground">
                        Phone
                    </Label>

                    <div class="mt-1 font-medium">
                        {{ user.phone || '-' }}
                    </div>
                </div>

                <div>
                    <Label class="text-muted-foreground">
                        Role
                    </Label>

                    <div class="mt-1 font-medium">
                        {{ user.role?.name || '-' }}
                    </div>
                </div>

                <div>
                    <Label class="text-muted-foreground">
                        Created At
                    </Label>

                    <div class="mt-1 font-medium">
                        {{ new Date(user.created_at).toLocaleString() }}
                    </div>
                </div>

                <div>
                    <Label class="text-muted-foreground">
                        Updated At
                    </Label>

                    <div class="mt-1 font-medium">
                        {{ new Date(user.updated_at).toLocaleString() }}
                    </div>
                </div>

            </div>
        </div>
    </div>
</template>