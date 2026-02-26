<script setup>
import { computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const currentUrl = computed(() => page.url);

const tabs = [
    { name: 'Asosiy', icon: 'fas fa-home', href: '/webapp' },
    { name: 'Tarix', icon: 'fas fa-history', href: '/webapp/history' },
    { name: 'Profil', icon: 'fas fa-user', href: '/webapp/profile' },
];

const isActive = (href) => {
    if (href === '/webapp') return currentUrl.value === '/webapp' || currentUrl.value.startsWith('/webapp?');
    return currentUrl.value.startsWith(href);
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col select-none" style="font-family: 'Outfit', sans-serif;">
        <!-- Page Content -->
        <div class="flex-1 pb-20">
            <slot />
        </div>

        <!-- Bottom Navigation -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-slate-100 z-50 safe-bottom">
            <div class="flex items-center justify-around h-16 max-w-md mx-auto">
                <button v-for="tab in tabs" :key="tab.href"
                        @click="router.visit(tab.href)"
                        class="flex flex-col items-center justify-center gap-0.5 w-full h-full transition-all duration-200"
                        :class="isActive(tab.href) ? 'text-indigo-600' : 'text-slate-400'">
                    <div class="relative">
                        <div v-if="isActive(tab.href)" class="absolute -inset-2 bg-indigo-50 rounded-xl"></div>
                        <i :class="tab.icon" class="relative text-lg"></i>
                    </div>
                    <span class="text-[10px] font-bold mt-0.5">{{ tab.name }}</span>
                </button>
            </div>
        </nav>
    </div>
</template>

<style scoped>
.safe-bottom {
    padding-bottom: env(safe-area-inset-bottom, 0px);
}
</style>
