<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    attempts: { type: Array, default: () => [] },
    bySubject: { type: Object, default: () => ({}) },
});

const activeSubject = ref(null); // null = subject list, string = show quiz list

const subjectNames = computed(() => Object.keys(props.bySubject));

const formatDate = (d) => {
    if (!d) return '';
    return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' });
};

const lastDate = (attempts) => {
    if (!attempts?.length) return '';
    return formatDate(attempts[0].completed_at);
};
</script>

<template>
    <Head title="Test tarixi" />
    <AppLayout>
        <!-- Header -->
        <div class="bg-indigo-600 px-5 pt-10 pb-20 rounded-b-[36px] relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
            <div class="relative z-10">
                <button v-if="activeSubject" @click="activeSubject = null"
                        class="flex items-center gap-1.5 text-indigo-200 text-sm mb-4">
                    <i class="fas fa-arrow-left text-xs"></i> Orqaga
                </button>
                <h1 class="text-white text-2xl font-extrabold tracking-tight">Test tarixi</h1>
                <p class="text-indigo-100 text-sm mt-1 opacity-90">{{ attempts.length }} ta test yechilgan</p>
            </div>
        </div>

        <div class="px-4 -mt-12 relative z-20 space-y-3">

            <!-- Subject cards -->
            <template v-if="!activeSubject">
                <div v-for="subject in subjectNames" :key="subject"
                     @click="activeSubject = subject"
                     class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 cursor-pointer active:scale-[0.98] transition-all group">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center flex-shrink-0 group-active:bg-indigo-600 group-active:text-white transition-colors">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-gray-900 text-sm truncate">{{ subject }}</h3>
                            <div class="flex items-center gap-3 mt-0.5 text-[11px] text-slate-400 font-medium">
                                <span><i class="fas fa-check-circle text-emerald-400 mr-1"></i>{{ bySubject[subject].length }} ta yechilgan</span>
                                <span><i class="far fa-calendar mr-1"></i>{{ lastDate(bySubject[subject]) }}</span>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-slate-200 text-sm flex-shrink-0"></i>
                    </div>
                </div>

                <div v-if="!subjectNames.length" class="bg-white rounded-2xl p-10 text-center border border-dashed border-slate-200">
                    <i class="fas fa-clipboard-list text-slate-200 text-4xl mb-3"></i>
                    <p class="text-slate-500 font-bold text-sm mb-1">Tarix bo'sh</p>
                    <p class="text-slate-400 text-xs">Hali birorta test yechilmagan</p>
                </div>
            </template>

            <!-- Attempts inside subject -->
            <template v-else>
                <div v-for="attempt in bySubject[activeSubject]" :key="attempt.id"
                     @click="router.visit('/webapp/attempt/' + attempt.id)"
                     class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 cursor-pointer active:scale-[0.98] transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center font-black text-sm flex-shrink-0"
                             :class="attempt.score >= (attempt.quiz?.pass_score || 70) ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600'">
                            {{ attempt.score }}%
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="font-bold text-slate-800 text-sm truncate">{{ attempt.quiz?.title || 'Test' }}</h3>
                            <div class="flex items-center gap-3 mt-1 text-[11px] text-slate-400">
                                <span><i class="fas fa-check mr-0.5 text-emerald-400"></i>{{ attempt.correct_answers }}/{{ attempt.total_questions }}</span>
                                <span><i class="far fa-calendar mr-0.5"></i>{{ formatDate(attempt.completed_at) }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1.5">
                            <span class="px-2 py-1 rounded-lg text-[9px] font-bold uppercase"
                                  :class="attempt.score >= (attempt.quiz?.pass_score || 70) ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                                {{ attempt.score >= (attempt.quiz?.pass_score || 70) ? 'O\'tdi' : 'O\'tmadi' }}
                            </span>
                            <i class="fas fa-chevron-right text-slate-200 text-xs"></i>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
