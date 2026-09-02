<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import CardCompany from '@/components/vanila/card/CardCompany.vue';
import TabMenuClient from '@/components/vanila/menu/TabMenuClient.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    title: string;
    client: any;
    users: any;
    racks: any;
    devices: any;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Client Management',
                href: '/admin/clients',
            },
        ],
    },
});
</script>

<template>

    <Head :title="title" />

    <CardCompany :client="props.client" />

    <TabMenuClient :client="props.client" :users="props.users" :racks="props.racks" :devices="props.devices" />
</template>