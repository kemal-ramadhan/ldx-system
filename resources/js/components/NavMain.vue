<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import {
    ChevronDown,
} from 'lucide-vue-next';

import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar
} from '@/components/ui/sidebar';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

import { useCurrentUrl } from '@/composables/useCurrentUrl';

defineProps<{
    items: any[];
}>();

const openMenu = ref<string | null>(null);

const toggleMenu = (title: string) => {
    openMenu.value =
        openMenu.value === title ? null : title;
};

const { isCurrentUrl } = useCurrentUrl();

const { state } = useSidebar();

/*
 * Badges: items with `badgeKey` show page.props.navBadges[badgeKey];
 * a parent shows the sum of its children.
 */
const page = usePage();
const navBadges = computed(() => (page.props.navBadges ?? {}) as Record<string, number>);

const badgeOf = (item: any): number =>
    item.badgeKey
        ? Number(navBadges.value[item.badgeKey] ?? 0)
        : (item.children ?? []).reduce((sum: number, child: any) => sum + badgeOf(child), 0);
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>
            Platform
        </SidebarGroupLabel>

        <SidebarMenu>

            <SidebarMenuItem
                v-for="item in items"
                :key="item.title"
            >

                <!-- MENU TANPA SUBMENU -->
                <SidebarMenuButton
                    v-if="!item.children"
                    as-child
                    :is-active="isCurrentUrl(item.href)"
                    :tooltip="item.title"
                >
                    <Link :href="item.href">
                        <component :is="item.icon" />

                        <span v-if="state !== 'collapsed'">
                            {{ item.title }}
                        </span>
                        <span v-if="state !== 'collapsed' && badgeOf(item)" class="ml-auto rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white">{{ badgeOf(item) }}</span>
                    </Link>
                </SidebarMenuButton>

                <!-- MODE COLLAPSED -->
                <DropdownMenu
                    v-else-if="state === 'collapsed'"
                >

                    <DropdownMenuTrigger as-child>

                        <SidebarMenuButton
                            :tooltip="item.title"
                        >
                            <component :is="item.icon" />
                        </SidebarMenuButton>

                    </DropdownMenuTrigger>

                    <DropdownMenuContent
                        side="right"
                        align="start"
                        class="w-52"
                    >

                        <DropdownMenuItem
                            v-for="child in item.children"
                            :key="child.title"
                            as-child
                        >
                            <Link :href="child.href" class="flex items-center justify-between gap-2">
                                {{ child.title }}
                                <span v-if="badgeOf(child)" class="ml-auto rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white">{{ badgeOf(child) }}</span>
                            </Link>
                        </DropdownMenuItem>

                    </DropdownMenuContent>

                </DropdownMenu>

                <!-- MODE NORMAL -->
                <div v-else>

                    <SidebarMenuButton
                        @click="toggleMenu(item.title)"
                        :tooltip="item.title"
                    >
                        <component :is="item.icon" />

                        <span>
                            {{ item.title }}
                        </span>

                        <span v-if="badgeOf(item)" class="ml-auto rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white">{{ badgeOf(item) }}</span>

                        <ChevronDown
                            class="ml-auto h-4 w-4 transition-transform"
                            :class="{
                                'rotate-180':
                                    openMenu === item.title
                            }"
                        />
                    </SidebarMenuButton>

                    <div
                        v-if="openMenu === item.title"
                        class="ml-6 mt-1 flex flex-col gap-1"
                    >

                        <Link
                            v-for="child in item.children"
                            :key="child.title"
                            :href="child.href"
                            class="rounded-md px-3 py-2 text-sm hover:bg-muted"
                            :class="{
                                'bg-muted':
                                    isCurrentUrl(child.href)
                            }"
                        >
                            <span class="flex items-center justify-between gap-2">
                                {{ child.title }}
                                <span v-if="badgeOf(child)" class="rounded-full bg-red-500 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white">{{ badgeOf(child) }}</span>
                            </span>
                        </Link>

                    </div>

                </div>

            </SidebarMenuItem>

        </SidebarMenu>
    </SidebarGroup>
</template>