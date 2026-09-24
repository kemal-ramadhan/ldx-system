<script setup lang="ts">
import { Head, Form, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { Code, Power } from 'lucide-vue-next';

const props = defineProps<{
    title: string;
    rooms: any;
}>();


const form = useForm({
    room_id: '',
    code: '',
    name: '',
    total_units: '',
    power_capacity: '',
    weight_capacity: '',
    description: '',
    status: 'active',
});

const createRack = () => {
    form.post(`/admin/racks`);
};

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

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-xl font-bold">Create Rack</h1>
        <!-- Add your form fields here -->
        <Form @submit.prevent="createRack" v-slot="{ errors, processing }" class="flex flex-col gap-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 w-full rounded-md border overflow-x-auto p-4">
                <div class="grid gap-3 w-full">
                    <Label for="code">Rack Code <span class="text-red-500">*</span></Label>
                    <Input id="code" v-model="form.code" type="text" required autofocus :tabindex="1"
                        autocomplete="code" name="code" placeholder="Rack code" />
                    <InputError :message="errors.code" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="name">Rack Name <span class="text-red-500">*</span></Label>
                    <Input id="name" v-model="form.name" type="text" required autofocus :tabindex="2"
                        autocomplete="name" name="name" placeholder="Rack name" />
                    <InputError :message="errors.name" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="total_units">Total Units <span class="text-red-500">*</span></Label>
                    <Input id="total_units" v-model="form.total_units" type="number" required autofocus :tabindex="2"
                        autocomplete="total_units" name="total_units" placeholder="Total units" />
                    <InputError :message="errors.total_units" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="power_capacity">Power Capacity (Ampere) <span class="text-red-500">*</span></Label>
                    <Input id="power_capacity" v-model="form.power_capacity" type="number" required autofocus
                        :tabindex="2" autocomplete="power_capacity" name="power_capacity"
                        placeholder="Power capacity" />
                    <InputError :message="errors.power_capacity" />
                </div>
                <div class="grid gap-3 w-full">
                    <Label for="weight_capacity">Weight Capacity (kg) <span class="text-red-500">*</span></Label>
                    <Input id="weight_capacity" v-model="form.weight_capacity" type="number" required autofocus
                        :tabindex="2" autocomplete="weight_capacity" name="weight_capacity"
                        placeholder="Weight capacity" />
                    <InputError :message="errors.weight_capacity" />
                </div>
                <div class="grid w-full gap-3">
                    <Label>
                        Status
                        <span class="text-red-500">*</span>
                    </Label>

                    <div class="grid grid-cols-2 gap-2 md:grid-cols-4">
                        <button type="button" @click="form.status = 'active'" :class="[
                            'rounded-md border px-4 py-2 text-sm font-medium transition-all',
                            form.status === 'active'
                                ? 'border-primary bg-primary text-white dark:text-gray-900'
                                : 'border-input bg-background hover:bg-muted dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:bg-transparent'
                        ]">
                            Active
                        </button>

                        <button type="button" @click="form.status = 'inactive'" :class="[
                            'rounded-md border px-4 py-2 text-sm font-medium transition-all',
                            form.status === 'inactive'
                                ? 'border-primary bg-primary text-white dark:text-gray-900'
                                : 'border-input bg-background hover:bg-muted dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:bg-transparent'
                        ]">
                            Inactive
                        </button>

                        <button type="button" @click="form.status = 'maintenance'" :class="[
                            'rounded-md border px-4 py-2 text-sm font-medium transition-all',
                            form.status === 'maintenance'
                                ? 'border-primary bg-primary text-white dark:text-gray-900'
                                : 'border-input bg-background hover:bg-muted dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:bg-transparent'
                        ]">
                            Maintenance
                        </button>

                        <button type="button" @click="form.status = 'full'" :class="[
                            'rounded-md border px-4 py-2 text-sm font-medium transition-all',
                            form.status === 'full'
                                ? 'border-primary bg-primary text-white dark:text-gray-900'
                                : 'border-input bg-background hover:bg-muted dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800 dark:bg-transparent'
                        ]">
                            Full
                        </button>
                    </div>

                    <InputError :message="errors.status" />
                </div>
                <div class="grid md:col-span-2 gap-3 w-full">
                    <Label for="description">Description <span class="text-red-500">*</span></Label>
                    <textarea id="description" v-model="form.description" required autofocus :tabindex="3"
                        autocomplete="description" name="description" placeholder="Rack description"
                        class="w-full rounded-md border border-gray-300 bg-white py-2 px-4 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none resize-none h-24 dark:bg-transparent dark:text-gray-300 dark:border-gray-700"></textarea>
                    <InputError :message="errors.description" />
                </div>
                <div class="grid md:col-span-2 gap-3 w-full">
                    <Label for="description">Location<span class="text-red-500">*</span></Label>
                    <select v-model="form.room_id" required
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm focus:border-primary focus:ring-1 focus:ring-primary focus:outline-none dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700">
                        <option value="">Location Room</option>

                        <option v-for="item in rooms" :key="item.id" :value="item.id">
                            {{ item.location_data_center?.name }} - {{ item.name }}
                        </option>
                    </select>
                    <InputError :message="errors.room_id" />
                </div>
            </div>
            <div class="flex items-center justify-end gap-4">
                <Button type="button"
                    class="bg-gray-300 text-gray-700 hover:bg-gray-400 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    @click="$inertia.visit('/admin/racks')">
                    Cancel
                </Button>
                <Button type="submit" :disabled="form.processing"
                    class="bg-primary text-white hover:bg-primary/90 disabled:bg-gray-300 disabled:text-gray-500 dark:text-gray-900">
                    <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />

                    {{ form.processing ? 'Creating...' : 'Create Rack' }}
                </Button>
            </div>
        </Form>
    </div>
</template>