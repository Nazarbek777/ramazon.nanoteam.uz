<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);

const navItems = [
    { href: '/bookstore',           label: 'Dashboard',  exact: true },
    { href: '/bookstore/pos',       label: 'Sotuv (POS)' },
    { href: '/bookstore/books',     label: 'Kitoblar' },
    { href: '/bookstore/reports',   label: 'Hisobotlar' },
    { href: '/bookstore/analytics', label: 'Analitika' },
];

const isActive = (item) => item.exact ? page.url === item.href : page.url.startsWith(item.href);
const initials = computed(() => user.value?.name?.slice(0, 2).toUpperCase() || 'AD');
</script>

<template>
    <div class="min-h-screen flex" style="font-family: 'Inter', 'Outfit', sans-serif; background: #080812;">

        <!-- Sidebar -->
        <aside style="
            width: 260px; flex-shrink: 0;
            background: #0d0d1f;
            border-right: 1px solid rgba(255,255,255,0.05);
            display: flex; flex-direction: column;
        ">
            <!-- Logo -->
            <div style="padding: 28px 24px 20px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div style="
                        width: 38px; height: 38px; border-radius: 12px; flex-shrink: 0;
                        background: linear-gradient(135deg, #6366f1, #8b5cf6);
                        display: flex; align-items: center; justify-content: center;
                    ">
                        <!-- Book icon -->
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <div style="color: #fff; font-weight: 800; font-size: 14px; letter-spacing: -0.3px;">Kitob Do'koni</div>
                        <div style="color: rgba(255,255,255,0.3); font-size: 11px; margin-top: 1px;">Admin Panel</div>
                    </div>
                </div>
            </div>

            <!-- Nav -->
            <nav style="flex-grow: 1; padding: 8px 12px; display: flex; flex-direction: column; gap: 4px;">

                <!-- Nav items loop -->
                <template v-for="(item, i) in navItems" :key="item.href">
                    <Link :href="item.href"
                        :style="isActive(item)
                            ? 'background:rgba(99,102,241,0.15);border:1px solid rgba(99,102,241,0.25);border-radius:12px;padding:11px 14px;display:flex;align-items:center;gap:10px;color:#a5b4fc;font-weight:600;font-size:13px;text-decoration:none;'
                            : 'background:transparent;border:1px solid transparent;border-radius:12px;padding:11px 14px;display:flex;align-items:center;gap:10px;color:rgba(255,255,255,0.4);font-weight:500;font-size:13px;text-decoration:none;'">
                        <!-- Dashboard icon -->
                        <svg v-if="i===0" width="16" height="16" fill="none" viewBox="0 0 24 24" :stroke="isActive(item)?'#a5b4fc':'rgba(255,255,255,0.35)'" stroke-width="2">
                            <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
                        </svg>
                        <!-- POS icon -->
                        <svg v-else-if="i===1" width="16" height="16" fill="none" viewBox="0 0 24 24" :stroke="isActive(item)?'#a5b4fc':'rgba(255,255,255,0.35)'" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <!-- Books icon -->
                        <svg v-else-if="i===2" width="16" height="16" fill="none" viewBox="0 0 24 24" :stroke="isActive(item)?'#a5b4fc':'rgba(255,255,255,0.35)'" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <!-- Reports icon -->
                        <svg v-else-if="i===3" width="16" height="16" fill="none" viewBox="0 0 24 24" :stroke="isActive(item)?'#a5b4fc':'rgba(255,255,255,0.35)'" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <!-- Analytics icon -->
                        <svg v-else width="16" height="16" fill="none" viewBox="0 0 24 24" :stroke="isActive(item)?'#a5b4fc':'rgba(255,255,255,0.35)'" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        {{ item.label }}
                    </Link>
                </template>
            </nav>

            <!-- User -->
            <div style="padding: 12px; border-top: 1px solid rgba(255,255,255,0.05);">
                <div style="
                    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);
                    border-radius: 14px; padding: 12px 14px;
                ">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                        <div style="
                            width: 32px; height: 32px; border-radius: 10px; flex-shrink: 0;
                            background: linear-gradient(135deg, #6366f1, #8b5cf6);
                            display: flex; align-items: center; justify-content: center;
                            color: white; font-weight: 800; font-size: 11px;
                        ">{{ initials }}</div>
                        <div style="overflow: hidden; flex: 1; min-width: 0;">
                            <div style="color: #fff; font-weight: 600; font-size: 12px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ user?.name }}</div>
                            <div style="color: rgba(255,255,255,0.3); font-size: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ user?.email }}</div>
                        </div>
                    </div>
                    <Link href="/bookstore/logout" method="post" as="button" style="
                        width: 100%; display: flex; align-items: center; justify-content: center; gap: 6px;
                        padding: 7px 12px; border-radius: 9px; cursor: pointer;
                        background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
                        color: #fca5a5; font-weight: 600; font-size: 11px;
                    ">
                        <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Chiqish
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <div style="flex: 1; display: flex; flex-direction: column; min-h-screen; min-width: 0;">
            <!-- Topbar -->
            <header style="
                height: 60px; display: flex; align-items: center; justify-content: space-between;
                padding: 0 32px;
                background: rgba(255,255,255,0.015);
                border-bottom: 1px solid rgba(255,255,255,0.05);
            ">
                <h1 style="color: #fff; font-weight: 700; font-size: 15px; letter-spacing: -0.2px; display: flex; align-items: center; gap: 10px;">
                    <slot name="header"></slot>
                </h1>
                <div style="
                    color: rgba(255,255,255,0.25); font-size: 11px; font-weight: 500;
                    background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);
                    padding: 6px 12px; border-radius: 8px;
                ">{{ new Date().toLocaleDateString('uz-UZ', { day: 'numeric', month: 'long', year: 'numeric' }) }}</div>
            </header>

            <!-- Content -->
            <main style="flex: 1; padding: 28px 32px; overflow: auto;">
                <slot></slot>
            </main>
        </div>
    </div>
</template>
