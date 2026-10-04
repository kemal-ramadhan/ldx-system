<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';


const props = defineProps<{
    title: string;
    users: any;
    roles: any[];
    filters: {
        search: string;
        role: string;
        type: string;
    };
}>();

const search = ref(props.filters.search || '');
const role = ref(props.filters.role || '');
const type = ref(props.filters.type || '');

watch([search, role, type], () => {
    router.get(
        '/admin/users',
        {
            search: search.value,
            role: role.value,
            type: type.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
});

const deleteUser = (id: number) => {
    if (confirm('Yakin ingin menghapus user ini?')) {
        router.delete(`/admin/users/${id}`);
    }
};

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'User Management',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <Head :title="props.title" />

    <div
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
    <div class="flex flex-nowrap justify-between items-center">
        <div class="flex flex-col gap-2">
            <div class="flex gap-2">
                <h1 class="text-xl font-bold">{{ props.title }}</h1>
                <span class="text-xs text-muted-foreground">({{ props.users.data.length }})</span>
            </div>
            <span class="text-xs">View and manage your users in one place</span>
        </div>
        <div class="flex gap-2">
            <Link
                href="/admin/users/create"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50"
            >
                <Plus class="w-4 h-4 mr-2" />
                Add User
            </Link>
        </div>
    </div>

    <!-- filter -->
     <div class="flex items-center mt-5 mb-2 gap-2">
        <div class="relative w-full">
            <input
                v-model="search"
                type="text"
                placeholder="Search users..."
                class="w-full rounded-xl border bg-white py-2.5 pl-10 pr-4 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent"
            />
            <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
        </div>
        <select
            v-if="type !== 'client'"
            v-model="role"
            class="rounded-xl border bg-white px-4 py-2.5 text-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary dark:bg-transparent"
        >
            <option value="">All Roles</option>

            <option
                v-for="item in roles"
                :key="item.id"
                :value="item.slug"
            >
                {{ item.name }}
            </option>
        </select>
     </div>
    <!-- table -->
    <div class="flex w-full items-center rounded-md border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead>
                <tr class="text-left text-sm font-semibold text-muted-foreground">
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th v-if="type === 'client'" class="px-4 py-2">Company</th>
                    <th class="px-4 py-2">Role</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr
                    v-for="user in props.users.data"
                    :key="user.id"
                    class="border-t hover:bg-muted/50"
                >
                    <td class="px-4 py-2">{{ user.name }}</td>
                    <td class="px-4 py-2">{{ user.email }}</td>
                    <td v-if="type === 'client'" class="px-4 py-2">
                        <div v-if="user.client_pic && user.client_pic.client" class="text-sm font-medium">
                            {{ user.client_pic.client.company_name }}
                        </div>
                        <div v-else class="flex flex-col gap-1 items-start">
                            <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/50 dark:text-red-300">
                                No Company
                            </span>
                            <Link
                                :href="`/admin/users/${user.id}/client`"
                                class="mt-1 inline-flex items-center rounded bg-blue-600 px-2 py-1 text-xs font-medium text-white hover:bg-blue-700"
                            >
                                Connect Company
                            </Link>
                        </div>
                    </td>
                    <td class="px-4 py-2 capitalize">{{ user.role?.name }}</td>
                    <td class="px-4 py-2 flex gap-2 flex-nowrap">
                        <Link
                            :href="`/admin/users/${user.id}`"
                            class="text-blue-500 hover:underline mr-2"
                        >
                            <Eye class="w-4 h-4" />
                        </Link>
                        <Link
                            :href="`/admin/users/${user.id}/edit`"
                            class="text-green-500 hover:underline mr-2"
                        >
                            <Pencil class="w-4 h-4" />
                        </Link>
                        <button
                            @click="deleteUser(user.id)"
                            class="text-red-500 hover:underline"
                        >
                            <Trash2 class="w-4 h-4" />
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <!-- pagination -->
    <div class="flex items-center justify-between border-t py-3">
        <div class="text-sm text-muted-foreground">
            Showing <span class="font-medium">{{ props.users.from }}</span> to <span class="font-medium">{{ props.users.to }}</span> of <span class="font-medium">{{ props.users.total }}</span> results
        </div>
        <div class="flex gap-2">
            <Link
                v-if="props.users.prev_page_url"
                :href="props.users.prev_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Previous
            </Link>
            <Link
                v-if="props.users.next_page_url"
                :href="props.users.next_page_url"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
            >
                Next
            </Link>
        </div>
    </div>
    </div>
</template>
