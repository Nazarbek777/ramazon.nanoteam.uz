<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    attempts: { type: Array, default: () => [] },
    bySubject: { type: Object, default: () => ({}) },
});

const subjects = computed(() => Object.keys(props.bySubject));
const activeSubject = ref('all');

const filtered = computed(() => {
    if (activeSubject.value === 'all') return props.attempts;
    return props.bySubject[activeSubject.value] || [];
});

const formatDate = (d) => {
    if (!d) return '';
    const dt = new Date(d);
    return dt.toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' })
        + ' ' + dt.toLocaleTimeString('uz-UZ', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Head title="Test tarixi" />
    <AppLayout>
        <!-- Header -->
        <div class="bg-indigo-600 px-5 pt-10 pb-14 rounded-b-[36px] relative overflow-hidden">
            <div class="absolute top-0 right-0 w-40 h-40 bg-white/10 rounded-full -mr-10 -mt-10 blur-2xl"></div>
            <h1 class="text-white text-xl font-extrabold relative z-10">Test tarixi</h1>
            <p class="text-indigo-200 text-xs mt-1 relative z-10">{{ attempts.length }} ta test yechilgan</p>
        </div>

        <div class="px-4 -mt-8 relative z-10">

            <!-- Subject filter tabs -->
            <div v-if="subjects.length > 1" class="flex gap-2 overflow-x-auto pb-2 mb-3 scrollbar-hide">
                <button @click="activeSubject = 'all'"
                        class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-bold transition-all"
                        :class="activeSubject === 'all' ? 'bg-indigo-600 text-white' : 'bg-white text-slate-500 border border-slate-200'">
                    Barchasi
                </button>
                <button v-for="subject in subjects" :key="subject"
                        @click="activeSubject = subject"
                        class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-bold transition-all whitespace-nowrap"
                        :class="activeSubject === subject ? 'bg-indigo-600 text-white' : 'bg-white text-slate-500 border border-slate-200'">
                    {{ subject }}
                </button>
            </div>

            <!-- Attempts -->
            <div v-if="filtered.length" class="space-y-3">
                <div v-for="attempt in filtered" :key="attempt.id"
                     @click="router.visit('/webapp/attempt/' + attempt.id)"
                     class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 cursor-pointer active:scale-[0.98] transition-all">
                    <div class="flex items-center gap-3">
                        <!-- Score badge -->
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-sm flex-shrink-0"
                             :class="attempt.score >= (attempt.quiz?.pass_score || 70) ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                            {{ attempt.score }}%
                        </div>

                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-slate-800 text-sm truncate">{{ attempt.quiz?.title || 'Test' }}</h3>
                            <p class="text-[10px] text-indigo-400 font-semibold">{{ attempt.quiz?.subject?.name }}</p>
                            <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-400">
                                <span><i class="fas fa-check text-emerald-400 mr-0.5"></i>{{ attempt.correct_answers }}/{{ attempt.total_questions }}</span>
                                <span><i class="far fa-calendar mr-0.5"></i>{{ formatDate(attempt.completed_at) }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-1">
                            <span class="px-2 py-1 rounded-lg text-[9px] font-bold uppercase"
                                  :class="attempt.score >= (attempt.quiz?.pass_score || 70) ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                                {{ attempt.score >= (attempt.quiz?.pass_score || 70) ? 'O\'tdi' : 'O\'tmadi' }}
                            </span>
                            <i class="fas fa-chevron-right text-slate-200 text-xs"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty -->
            <div v-else class="bg-white rounded-2xl p-10 text-center border border-dashed border-slate-200">
                <i class="fas fa-clipboard-list text-slate-200 text-4xl mb-3"></i>
                <p class="text-slate-500 font-bold text-sm mb-1">Tarix bo'sh</p>
                <p class="text-slate-400 text-xs">Hali birorta test yechilmagan</p>
            </div>
        </div>
    </AppLayout>
</template>
