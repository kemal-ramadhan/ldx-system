<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Code, Power } from 'lucide-vue-next';
import CardRack from '@/components/vanila/card/CardRack.vue';
import TabMenuRackClient from '@/components/vanila/menu/TabMenuRackClient.vue';
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
                href: '/client/racks',
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
    </div>

    <CardRack :rack="rack" />
    <TabMenuRackClient :rack="rack" />
</template>