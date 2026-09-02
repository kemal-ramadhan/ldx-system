<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import {
    Search,
    Upload,
    Link2,
    ChevronDown,
    MoreHorizontal,
    Trash2
} from 'lucide-vue-next'
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

interface Client {
    pics?: MemberInvite[]
    [key: string]: any
}

const props = defineProps<{ client?: Client, users?: User[] }>()


const search = ref('')

const openMenu = ref<number | null>(null)

const toggleMenu = (id: number) => {
    openMenu.value = openMenu.value === id ? null : id
}

const filteredUsers = computed(() => {
    if (!search.value) return []

    const existingUserIds = (props.client?.pics || []).map(
        (member) => member.user.id
    )

    return (props.users || []).filter((user: User) => {
        const matchSearch =
            user.name.toLowerCase().includes(search.value.toLowerCase()) ||
            user.email.toLowerCase().includes(search.value.toLowerCase())

        const notInvited = !existingUserIds.includes(user.id)

        return matchSearch && notInvited
    })
})

const form = useForm<{ user_id: number | null }>({
    user_id: null
})

const inviteMember = (userId: number) => {
    form.user_id = userId

    if (props.client?.id) {
        form.post(`/admin/clients/${props.client.id}/invite-member`, {
            preserveScroll: true,
            onSuccess: () => {
                search.value = ''
                openMenu.value = null
            }
        })
    }
}

const deleteMember = (id: number) => {
    form.delete(`/admin/clients/pic/${id}`, {
        preserveScroll: true,
        onSuccess: () => {
            openMenu.value = null
            toast.success('Rack owner assigned successfully.')
        }
    })
}

</script>

<template>
    <div class="p-4">
        <div class="mx-auto max-w-7xl">

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-xl font-bold">
                    Team Settings
                </h1>

                <p class="mt-2 text-sm">
                    Manage and view your coworkers and guests
                </p>
            </div>

            <!-- Invite Card -->
            <div class="relative flex-1">
                <div class="flex items-center gap-3 rounded-2xl px-4 py-3 shadow-sm dark:bg-white/5">
                    <Search class="h-5 w-5 text-gray-400" />

                    <input v-model="search" type="text" placeholder="Search and add users..."
                        class="w-full rounded-md border border-gray-300 bg-white py-2 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none resize-none dark:bg-transparent dark:text-gray-300" />
                </div>

                <!-- Search Result -->
                <div v-if="filteredUsers.length"
                    class="absolute z-50 mt-2 w-full overflow-hidden rounded-2xl border bg-white shadow-xl">
                    <button v-for="user in filteredUsers" :key="user.id" @click="inviteMember(user.id)"
                        class="flex w-full items-center justify-between border-b px-4 py-3 text-left transition hover:bg-gray-50 last:border-none">
                        <div>
                            <p class="font-medium dark:text-gray-900">
                                {{ user.name }}
                            </p>

                            <p class="text-sm text-gray-500">
                                {{ user.email }}
                            </p>
                        </div>

                        <span class="rounded-xl bg-black px-3 py-1 text-sm text-white">
                            {{ form.processing ? 'Inviting...' : 'Invite' }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Invited Members List -->
            <div class="mt-8">
                <h2 class="text-lg font-medium">
                    Working with {{ props.client?.pics?.length || 0 }} invited members
                </h2>
                <p class="mt-1 text-sm text-muted-foreground">
                    These members have been invited to your team but haven't accepted yet.
                </p>
                <div class="mt-4 grid grid-cols-1 gap-2 md:grid-cols-2 lg:grid-cols-3">
                    <div v-for="member in props.client?.pics || []" :key="member.id"
                        class="relative rounded-2xl border bg-white p-4 shadow-sm">
                        <!-- More Menu -->
                        <div class="absolute right-4 top-4">
                            <button @click="toggleMenu(member.id)" class="rounded-lg p-2 transition hover:bg-gray-100">
                                <MoreHorizontal class="h-5 w-5 text-gray-500" />
                            </button>

                            <!-- Dropdown -->
                            <div v-if="openMenu === member.id"
                                class="absolute right-0 top-12 z-20 w-40 overflow-hidden rounded-xl border bg-white shadow-lg">
                                <button @click="deleteMember(member.id)"
                                    class="flex w-full items-center gap-2 px-4 py-3 text-sm text-red-500 transition hover:bg-red-50">
                                    <Trash2 class="h-4 w-4" />
                                    Remove
                                </button>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex items-start gap-3">
                            <!-- Avatar -->
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-300 text-sm font-medium text-gray-600">
                                {{
                                    member.user.name
                                        .split(' ')
                                        .map((n) => n[0])
                                        .join('')
                                }}
                            </div>

                            <!-- Info -->
                            <div class="pr-10">
                                <p class="font-medium text-gray-900">
                                    {{ member.user.name }}

                                    <span class="text-sm text-muted-foreground">
                                        ({{ member.status }})
                                    </span>
                                </p>

                                <p class="text-sm text-muted-foreground">
                                    {{ member.user.email }}
                                </p>

                                <p class="text-sm text-muted-foreground">
                                    Invited on {{
                                        member.created_at ? new Date(member.created_at).toLocaleDateString('en-US', {
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
</template>