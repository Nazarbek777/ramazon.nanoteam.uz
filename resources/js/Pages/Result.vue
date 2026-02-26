<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    attempt: Object,
    quiz: Object,
});

const passed = computed(() => props.attempt?.score >= props.quiz?.pass_score);
const wrongCount = computed(() => (props.attempt?.total_questions || 0) - (props.attempt?.correct_answers || 0));
</script>

<template>
    <Head title="Natija" />

    <div class="min-h-screen bg-slate-50 flex flex-col select-none" style="font-family: 'Outfit', sans-serif;">

        <!-- Top Accent -->
        <div class="px-4 pt-4 pb-6 text-center relative"
             :class="passed ? 'bg-emerald-500' : 'bg-rose-500'">
            <div class="w-16 h-16 rounded-2xl mx-auto mb-3 flex items-center justify-center rotate-6"
                 :class="passed ? 'bg-emerald-400' : 'bg-rose-400'">
                <i class="text-white text-3xl -rotate-6"
                   :class="passed ? 'fas fa-trophy' : 'fas fa-times-circle'"></i>
            </div>
            <h1 class="text-white text-lg font-extrabold">{{ passed ? 'Tabriklaymiz!' : 'Afsuski...' }}</h1>
            <p class="text-white/70 text-xs mt-0.5">{{ passed ? 'Siz testdan muvaffaqiyatli o\'tdingiz' : 'Yana urinib ko\'ring' }}</p>
        </div>

        <!-- Score Card -->
        <div class="px-4 -mt-3 relative z-10">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <!-- Score Circle -->
                <div class="flex items-center justify-center mb-5">
                    <div class="relative w-24 h-24">
                        <svg class="w-24 h-24 transform -rotate-90">
                            <circle class="text-slate-100" stroke-width="8" stroke="currentColor" fill="transparent" r="42" cx="48" cy="48" />
                            <circle :class="passed ? 'text-emerald-500' : 'text-rose-500'"
                                    stroke-width="8"
                                    :stroke-dasharray="2 * Math.PI * 42"
                                    :stroke-dashoffset="2 * Math.PI * 42 * (1 - (attempt?.score || 0) / 100)"
                                    stroke-linecap="round" stroke="currentColor" fill="transparent" r="42" cx="48" cy="48"
                                    class="transition-all duration-1000" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-black text-slate-800">{{ attempt?.score || 0 }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-emerald-50 rounded-xl p-3 text-center">
                        <div class="text-lg font-black text-emerald-600">{{ attempt?.correct_answers || 0 }}</div>
                        <div class="text-[10px] font-bold text-emerald-500 uppercase">To'g'ri</div>
                    </div>
                    <div class="bg-rose-50 rounded-xl p-3 text-center">
                        <div class="text-lg font-black text-rose-600">{{ wrongCount }}</div>
                        <div class="text-[10px] font-bold text-rose-500 uppercase">Noto'g'ri</div>
                    </div>
                </div>

                <!-- Info Rows -->
                <div class="border-t border-slate-50 pt-3 space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Test:</span>
                        <span class="text-slate-700 font-bold">{{ quiz?.title }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400 font-medium">Holat:</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase"
                              :class="passed ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                            {{ passed ? 'Muvaffaqiyatli' : 'Yechilmadi' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="px-4 mt-4 pb-6">
            <Link href="/webapp"
                  class="block w-full h-12 bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-200 flex items-center justify-center active:scale-[0.97] transition-all">
                <i class="fas fa-redo-alt mr-2 text-xs"></i>
                Yana yechib ko'rish
            </Link>
        </div>
    </div>
</template>
