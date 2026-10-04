<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'

const props = defineProps<{
    title: string
    user: any
    companies?: any[]
}>()

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Mail, Phone, Calendar, Clock, UserIcon, Building2 } from 'lucide-vue-next'
import CardCompany from '@/components/vanila/card/CardCompany.vue'

const getInitials = (name: string) => {
    return name
        ?.split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2) || 'U'
}

const getAvatarUrl = (avatar: string | null) => {
    if (!avatar) return ''
    return avatar.startsWith('http') ? avatar : `/storage/${avatar}`
}

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

    <div class="flex flex-col gap-6 p-6 w-full">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Detail User</h1>
                <p class="text-muted-foreground mt-1">Manage and view user information.</p>
            </div>

            <div class="flex gap-3">
                <Link href="/admin/users">
                    <Button variant="outline" class="gap-2">
                        Back
                    </Button>
                </Link>
                <Link :href="`/admin/users/${user.id}/edit`">
                    <Button class="gap-2">
                        Edit User
                    </Button>
                </Link>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Profile Card -->
            <Card class="md:col-span-1 shadow-sm border-gray-200 dark:border-gray-800">
                <CardContent class="p-6 flex flex-col items-center text-center">
                    <Avatar class="h-28 w-28 mb-4 border-4 border-blue-50 dark:border-gray-800 shadow-sm">
                        <AvatarImage :src="getAvatarUrl(user.avatar)" :alt="user.name" class="object-cover" />
                        <AvatarFallback class="text-2xl bg-blue-100 text-blue-700 dark:bg-gray-800 dark:text-gray-300">
                            {{ getInitials(user.name) }}
                        </AvatarFallback>
                    </Avatar>
                    
                    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ user.name }}</h2>
                    <Badge variant="secondary" class="mt-2 capitalize bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-300 border-none">
                        {{ user.role?.name || 'No Role' }}
                    </Badge>
                </CardContent>
            </Card>

            <!-- Right Details Card -->
            <Card class="md:col-span-2 shadow-sm border-gray-200 dark:border-gray-800">
                <CardHeader class="border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/20 pb-4">
                    <CardTitle class="text-lg">Contact & System Info</CardTitle>
                </CardHeader>
                <CardContent class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-6 gap-x-8">
                        <div class="space-y-1">
                            <Label class="text-xs uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                                <Mail class="w-3.5 h-3.5" /> Email
                            </Label>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ user.email }}</div>
                        </div>

                        <div class="space-y-1">
                            <Label class="text-xs uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                                <Phone class="w-3.5 h-3.5" /> Phone
                            </Label>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ user.phone || '-' }}</div>
                        </div>

                        <div class="space-y-1">
                            <Label class="text-xs uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                                <Calendar class="w-3.5 h-3.5" /> Created At
                            </Label>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ new Date(user.created_at).toLocaleString() }}</div>
                        </div>

                        <div class="space-y-1">
                            <Label class="text-xs uppercase tracking-wider text-muted-foreground flex items-center gap-2">
                                <Clock class="w-3.5 h-3.5" /> Updated At
                            </Label>
                            <div class="font-medium text-gray-900 dark:text-gray-100">{{ new Date(user.updated_at).toLocaleString() }}</div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Companies Handled Section -->
        <div v-if="user.role?.slug === 'client' && companies" class="mt-6">
            <div class="flex items-center gap-2 mb-4">
                <Building2 class="w-5 h-5 text-gray-500" />
                <h3 class="text-lg font-bold">Perusahaan yang Ditangani ({{ companies.length }})</h3>
            </div>
            
            <div v-if="companies.length === 0" class="p-8 text-center bg-gray-50 dark:bg-gray-900/50 rounded-xl border border-dashed border-gray-200 dark:border-gray-800">
                <p class="text-muted-foreground">User ini belum terhubung ke perusahaan mana pun.</p>
            </div>

            <div v-else class="grid grid-cols-1 xl:grid-cols-2 gap-4">
                <!-- Using CardCompany for each handled client -->
                <div v-for="company in companies" :key="company.id" class="transform transition duration-200 hover:scale-[1.01]">
                    <Link :href="`/admin/clients/${company.id}`" class="block h-full">
                        <CardCompany :client="company" class="h-full hover:border-blue-300 dark:hover:border-blue-700 transition-colors" />
                    </Link>
                </div>
            </div>
        </div>

    </div>
</template>