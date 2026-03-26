<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const sidebarOpen = ref(true);

const navItems = [
    { href: '/bookstore', icon: '🏠', label: 'Dashboard', exact: true },
    { href: '/bookstore/pos', icon: '🔄', label: 'POS - Sotuv' },
    { href: '/bookstore/books', icon: '📚', label: 'Kitoblar' },
];

const isActive = (item) => {
    if (item.exact) return page.url === item.href;
    return page.url.startsWith(item.href);
};
</script>

<template>
    <div class="min-h-screen flex" style="background: #0f0f1a; font-family: 'Outfit', 'Inter', sans-serif;">

        <!-- Sidebar -->
        <aside class="w-72 flex-shrink-0 flex flex-col relative z-10"
            style="background: linear-gradient(180deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border-right: 1px solid rgba(255,255,255,0.06);">

            <!-- Logo -->
            <div class="p-8 pb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-xl"
                        style="background: linear-gradient(135deg, #e94560, #0f3460);">📚</div>
                    <div>
                        <div class="text-white font-extrabold text-lg tracking-tight leading-none">KITOB DO'KONI</div>
                        <div class="text-xs font-medium mt-0.5" style="color: rgba(255,255,255,0.35);">Admin Panel</div>
                    </div>
                </div>
            </div>

            <!-- Nav Items -->
            <nav class="flex-grow px-4 space-y-1">
                <template v-for="item in navItems" :key="item.href">
                    <Link :href="item.href"
                        class="flex items-center space-x-3 px-4 py-3 rounded-2xl transition-all duration-200 group relative overflow-hidden"
                        :class="isActive(item)
                            ? 'text-white font-bold'
                            : 'text-gray-400 hover:text-white hover:bg-white/5'"
                        :style="isActive(item) ? 'background: linear-gradient(135deg, rgba(233,69,96,0.2), rgba(15,52,96,0.3)); border: 1px solid rgba(233,69,96,0.3);' : ''">
                        <span v-if="isActive(item)" class="absolute left-0 top-0 bottom-0 w-1 rounded-r-full"
                            style="background: linear-gradient(180deg, #e94560, #0f3460);"></span>
                        <span class="text-lg ml-1">{{ item.icon }}</span>
                        <span class="text-sm">{{ item.label }}</span>
                    </Link>
                </template>
            </nav>

            <!-- User Info -->
            <div class="p-4 m-4 rounded-2xl" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-sm font-black text-white"
                        style="background: linear-gradient(135deg, #e94560, #533483);">
                        {{ user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="overflow-hidden flex-1 min-w-0">
                        <div class="text-white font-semibold text-sm truncate leading-none">{{ user?.name }}</div>
                        <div class="text-xs truncate mt-0.5" style="color: rgba(255,255,255,0.35);">{{ user?.email }}</div>
                    </div>
                </div>
                <Link href="/bookstore/logout" method="post" as="button"
                    class="w-full text-left px-3 py-2 rounded-xl text-xs font-bold uppercase tracking-widest transition-all"
                    style="color: #e94560; background: rgba(233,69,96,0.08);"
                    @mouseover="e => e.target.style.background='rgba(233,69,96,0.18)'"
                    @mouseleave="e => e.target.style.background='rgba(233,69,96,0.08)'">
                    Chiqish 🚪
                </Link>
            </div>
        </aside>

        <!-- Main -->
        <div class="flex-grow flex flex-col min-h-screen" style="background: #0f0f1a;">
            <!-- Topbar -->
            <header class="h-16 flex items-center justify-between px-8" style="background: rgba(255,255,255,0.02); border-bottom: 1px solid rgba(255,255,255,0.05);">
                <h1 class="font-bold text-white text-lg tracking-tight">
                    <slot name="header"></slot>
                </h1>
                <div class="flex items-center space-x-3">
                    <div class="px-4 py-2 rounded-xl text-xs font-bold" style="background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.5);">
                        {{ new Date().toLocaleDateString('uz-UZ', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                    </div>
                </div>
            </header>

            <!-- Content -->
            <main class="flex-grow p-8 overflow-auto">
                <slot></slot>
            </main>
        </div>
    </div>
</template>
