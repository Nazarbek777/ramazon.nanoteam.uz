<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    attempts: { type: Array, default: () => [] },
});

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' }) +
        ' ' + d.toLocaleTimeString('uz-UZ', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Test tarixi" />
    <AppLayout>
        <div class="bg-indigo-600 px-5 pt-10 pb-14 rounded-b-[36px] relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-10 -mt-10 blur-2xl"></div>
            <h1 class="text-white text-xl font-extrabold relative z-10">Test tarixi</h1>
            <p class="text-indigo-200 text-xs mt-1 relative z-10">Barcha yechilgan testlar</p>
        </div>

        <div class="px-4 -mt-8 relative z-10">
            <!-- Attempts list -->
            <div v-if="attempts.length > 0" class="space-y-3">
                <div v-for="attempt in attempts" :key="attempt.id"
                     class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
                    <div class="flex items-center gap-3">
                        <!-- Score badge -->
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-sm flex-shrink-0"
                             :class="attempt.score >= (attempt.quiz?.pass_score || 70) ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                            {{ attempt.score }}%
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-slate-800 text-sm truncate">{{ attempt.quiz?.title || 'Test' }}</h3>
                            <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-400">
                                <span><i class="fas fa-check text-emerald-400 mr-0.5"></i>{{ attempt.correct_answers }}/{{ attempt.total_questions }}</span>
                                <span><i class="far fa-calendar mr-0.5"></i>{{ formatDate(attempt.completed_at) }}</span>
                            </div>
                        </div>

                        <!-- Status -->
                        <span class="px-2 py-1 rounded-lg text-[9px] font-bold uppercase flex-shrink-0"
                              :class="attempt.score >= (attempt.quiz?.pass_score || 70) ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                            {{ attempt.score >= (attempt.quiz?.pass_score || 70) ? 'O\'tdi' : 'O\'tmadi' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Empty state -->
            <div v-else class="bg-white rounded-2xl p-10 text-center shadow-sm border border-slate-100">
                <i class="fas fa-clipboard-list text-slate-200 text-4xl mb-3"></i>
                <p class="text-slate-500 font-bold text-sm mb-1">Tarix bo'sh</p>
                <p class="text-slate-400 text-xs">Hali birorta test yechilmagan</p>
            </div>
        </div>
    </AppLayout>
</template>
