<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Code, Power } from 'lucide-vue-next';
import CardRack from '@/components/vanila/card/CardRack.vue';
import TabMenuRack from '@/components/vanila/menu/TabMenuRack.vue';
import { ChevronLeft, Pencil } from 'lucide-vue-next';
import client from '@/routes/client';

const props = defineProps<{
    title: string;
    rack: any;
    clients: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Rack Management',
                href: '/admin/racks',
            },
        ],
    },
});
</script>

<template>

    <Head :title="title" />

    <div class="flex flex-nowrap justify-between items-center p-4">
        <div class="flex items-start gap-3">
            <!-- TITLE -->
            <div class="flex flex-col">
                <h1 class="text-lg font-bold">
                    {{ props.rack.code }}
                </h1>

                <span class="text-xs text-muted-foreground">
                    View and manage your racks in one place
                </span>
            </div>
        </div>
        <div class="flex gap-2">
            <Link href="/admin/racks/create"
                class="w-full inline-flex items-center justify-center rounded-md border border-transparent bg-primary dark:text-gray-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-primary/50 disabled:hover:bg-primary/50">
            <Pencil class="w-4 h-4 mr-2" />
            Edit Rack
            </Link>
        </div>
    </div>

    <CardRack :rack="rack" />
    <TabMenuRack :rack="rack" :clients="clients" />
</template>