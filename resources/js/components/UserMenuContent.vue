<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { ref } from 'vue';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import axios from 'axios';

type Props = {
    user: User;
};

const isLogoutModalOpen = ref(false);

const handleLogout = async () => {
    router.flushAll();
    try {
        await axios.post(logout().url);
    } finally {
        window.location.href = '/';
    }
};

defineProps<Props>();
</script>

<template>
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <Dialog :open="isLogoutModalOpen" @update:open="(val) => isLogoutModalOpen = val">
        <DropdownMenuItem :as-child="true" @select.prevent="isLogoutModalOpen = true">
            <button
                class="block w-full cursor-pointer text-left"
                data-test="logout-button"
            >
                <div class="flex items-center">
                    <LogOut class="mr-2 h-4 w-4" />
                    Log out
                </div>
            </button>
        </DropdownMenuItem>

        <DialogContent>
            <DialogHeader class="space-y-3">
                <DialogTitle>Are you sure you want to log out?</DialogTitle>
                <DialogDescription>
                    You will be redirected to the landing page after logging out.
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <Button variant="secondary" @click="isLogoutModalOpen = false">Cancel</Button>
                <Button variant="destructive" @click="handleLogout">Log out</Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
