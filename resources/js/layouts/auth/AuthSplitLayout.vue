<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';

const page = usePage();
const name = page.props.name;

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div
        class="relative grid h-dvh flex-col items-center justify-center px-8 sm:px-0 lg:max-w-none lg:grid-cols-2 lg:px-0"
    >
        <!-- Left Panel: Data Center Theme -->
        <div
            class="relative hidden h-full flex-col justify-between overflow-hidden bg-zinc-950 p-10 text-white lg:flex dark:border-r"
        >
            <!-- 1. Background Image (Data Center) dengan filter luminosity agar terlihat elegan/monokrom -->
            <div 
                class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1558494949-ef010cbdcc31?q=80&w=2000&auto=format&fit=crop')] bg-cover bg-center opacity-30 mix-blend-luminosity" 
            />
            
            <!-- 2. Gradient Overlay agar bagian bawah lebih gelap dan teks terbaca -->
            <div class="absolute inset-0 bg-gradient-to-t from-zinc-950 via-zinc-950/60 to-transparent" />
            
            <!-- 3. Tech Grid Pattern tipis -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#ffffff0a_1px,transparent_1px),linear-gradient(to_bottom,#ffffff0a_1px,transparent_1px)] bg-[size:24px_24px]" />

            <!-- Logo Section -->
            <Link
                :href="home()"
                class="relative z-20 flex items-center text-lg font-medium transition-transform hover:scale-105"
            >
                <AppLogoIcon class="mr-2 w-22 fill-current text-zinc-900 bg-white p-2 rounded-md shadow-lg" />
            </Link>

            <!-- Bottom Text / Tagline -->
            <div class="relative z-20 mt-auto">
                <blockquote class="space-y-3">
                    <p class="text-lg font-medium leading-relaxed tracking-wide text-zinc-200">
                        "Infrastruktur data center enterprise-grade dengan colocation server yang aman, handal, dan terjangkau. Solusi terpercaya untuk bisnis Anda di Indonesia."
                    </p>
                    <footer class="text-sm font-medium text-zinc-400">
                        Reliability in Every Byte
                    </footer>
                </blockquote>
            </div>
        </div>

        <!-- Right Panel: Form Area -->
        <div class="lg:p-8">
            <div
                class="mx-auto flex w-full flex-col justify-center space-y-6 sm:w-[350px]"
            >
                <div class="flex flex-col space-y-2 text-center">
                    <h1 class="text-2xl font-semibold tracking-tight" v-if="title">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground" v-if="description">
                        {{ description }}
                    </p>
                </div>
                <slot />
            </div>
        </div>
    </div>
</template>