<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-indigo-900 text-white flex-shrink-0 flex flex-col transition-all duration-300">
            <div class="p-6 text-2xl font-bold border-b border-indigo-800 tracking-wider">
                KITOB DO'KONI
            </div>
            
            <nav class="flex-grow p-4 space-y-2">
                <Link :href="route('bookstore.dashboard')" 
                    class="flex items-center space-x-3 p-3 rounded-xl transition-all hover:bg-indigo-800"
                    :class="{ 'bg-indigo-700 shadow-lg': route().current('bookstore.dashboard') }">
                    <span class="text-xl">📊</span>
                    <span>Bosh sahifa</span>
                </Link>
                
                <Link :href="route('bookstore.pos')" 
                    class="flex items-center space-x-3 p-3 rounded-xl transition-all hover:bg-indigo-800"
                    :class="{ 'bg-indigo-700 shadow-lg': route().current('bookstore.pos') }">
                    <span class="text-xl">🛒</span>
                    <span>Sotuv Paneli (POS)</span>
                </Link>
            </nav>

            <div class="p-4 border-t border-indigo-800">
                <div class="flex items-center space-x-3 p-3 mb-2">
                    <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center font-bold">
                        {{ user?.name?.charAt(0) }}
                    </div>
                    <div class="overflow-hidden">
                        <div class="font-medium truncate">{{ user?.name }}</div>
                        <div class="text-xs text-indigo-300 truncate">{{ user?.email }}</div>
                    </div>
                </div>
                
                <Link :href="route('bookstore.logout')" method="post" as="button"
                    class="w-full text-left p-3 rounded-xl hover:bg-red-500/20 text-red-300 transition-all flex items-center space-x-3">
                    <span>🚪</span>
                    <span>Chiqish</span>
                </Link>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow flex flex-col overflow-hidden">
            <header class="bg-white border-b border-gray-200 h-16 flex items-center justify-between px-8">
                <h1 class="text-xl font-semibold text-gray-800 uppercase tracking-tight">
                    <slot name="header"></slot>
                </h1>
                
                <div class="flex items-center space-x-4">
                    <div class="bg-gray-100 px-4 py-2 rounded-full text-sm text-gray-600 font-medium">
                        Bugun: {{ new Date().toLocaleDateString('uz-UZ') }}
                    </div>
                </div>
            </header>
            
            <div class="flex-grow overflow-auto p-8">
                <slot></slot>
            </div>
        </main>
    </div>
</template>
