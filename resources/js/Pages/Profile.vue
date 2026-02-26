<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    user: Object,
    stats: Object,
});
</script>

<template>
    <Head title="Profil" />
    <AppLayout>
        <div class="bg-indigo-600 px-5 pt-10 pb-16 rounded-b-[36px] relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-10 -mt-10 blur-2xl"></div>

            <div class="relative z-10 flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-white text-xl font-black border border-white/30">
                    {{ user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                </div>
                <div>
                    <h1 class="text-white text-lg font-extrabold">{{ user?.name || 'Foydalanuvchi' }}</h1>
                    <p class="text-indigo-200 text-xs mt-0.5">
                        <i class="fas fa-phone mr-1"></i>{{ user?.phone_number || 'Telefon kiritilmagan' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="px-4 -mt-8 relative z-10">
            <!-- Stats Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-4">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Statistika</h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="text-center bg-indigo-50 rounded-xl p-3">
                        <div class="text-xl font-black text-indigo-600">{{ stats?.total || 0 }}</div>
                        <div class="text-[9px] font-bold text-indigo-400 uppercase mt-0.5">Jami test</div>
                    </div>
                    <div class="text-center bg-emerald-50 rounded-xl p-3">
                        <div class="text-xl font-black text-emerald-600">{{ stats?.passed || 0 }}</div>
                        <div class="text-[9px] font-bold text-emerald-400 uppercase mt-0.5">Muvaffaq</div>
                    </div>
                    <div class="text-center bg-amber-50 rounded-xl p-3">
                        <div class="text-xl font-black text-amber-600">{{ stats?.avg_score || 0 }}%</div>
                        <div class="text-[9px] font-bold text-amber-400 uppercase mt-0.5">O'rtacha</div>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Ma'lumotlar</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-2"><i class="fas fa-user text-xs"></i>Ism</span>
                        <span class="font-bold text-slate-700">{{ user?.name || '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-2"><i class="fas fa-phone text-xs"></i>Telefon</span>
                        <span class="font-bold text-slate-700">{{ user?.phone_number || '—' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-2"><i class="fab fa-telegram text-xs"></i>Telegram ID</span>
                        <span class="font-bold text-slate-700">{{ user?.telegram_id || '—' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
