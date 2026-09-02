<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { Search, Plus, Pencil, Eye, Trash2 } from 'lucide-vue-next';
import { ref, watch } from 'vue';


const props = defineProps<{
    title: string;
    location: any;
}>();

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

    <Head :title="props.title" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex flex-nowrap justify-between items-center">
            <div class="flex flex-col gap-2">
                <div class="flex gap-2">
                    <h1 class="text-xl font-bold">{{ props.title }}</h1>
                </div>
                <span class="text-xs">View location details</span>
            </div>
            <div class="flex gap-2">
                <Link href="/admin/locations"
                    class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50">
                    Back to Locations
                </Link>
            </div>
        </div>
        <div class="grid grid-cols-1 gap-3 w-full rounded-md border overflow-x-auto p-4">
            <div class="grid gap-3 w-full">
                <Label for="code" class="font-medium">Location Code</Label>
                <span>{{ props.location.code }}</span>
            </div>
            <div class="grid gap-3 w-full">
                <Label for="name" class="font-medium">Location Name</Label>
                <span>{{ props.location.name }}</span>
            </div>
            <div class="grid gap-3 w-full">
                <Label for="address" class="font-medium">Location Address</Label>
                <span>{{ props.location.address }}</span>
            </div>
        </div>
    </div>
</template>
