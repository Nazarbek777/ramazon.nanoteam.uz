<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    attempt: Object,
    quiz: Object,
});

const passed = computed(() => props.attempt?.score >= props.quiz?.pass_score);
const wrongCount = computed(() => (props.attempt?.total_questions || 0) - (props.attempt?.correct_answers || 0));
const showReview = ref(false);
</script>

<template>
    <Head title="Natija" />
    <div class="min-h-screen bg-slate-50 flex flex-col" style="font-family:'Outfit',sans-serif;">

        <!-- Top -->
        <div class="px-5 pt-10 pb-8 text-center"
             :class="passed ? 'bg-emerald-500' : 'bg-rose-500'">
            <div class="w-16 h-16 rounded-2xl mx-auto mb-3 flex items-center justify-center rotate-6"
                 :class="passed ? 'bg-emerald-400' : 'bg-rose-400'">
                <i class="text-white text-3xl -rotate-6"
                   :class="passed ? 'fas fa-trophy' : 'fas fa-times-circle'"></i>
            </div>
            <h1 class="text-white text-xl font-extrabold">{{ passed ? 'Tabriklaymiz! 🎉' : 'Afsuski...' }}</h1>
            <p class="text-white/70 text-sm mt-1">
                {{ passed ? 'Testdan muvaffaqiyatli o\'tdingiz' : 'Keyingi safar muvaffaq bo\'lasiz' }}
            </p>
        </div>

        <div class="px-4 -mt-4 relative z-10 space-y-4 pb-8">

            <!-- Score card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="flex items-center justify-center mb-4">
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

                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="bg-emerald-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-black text-emerald-600">{{ attempt?.correct_answers || 0 }}</div>
                        <div class="text-[10px] font-bold text-emerald-400 uppercase">To'g'ri</div>
                    </div>
                    <div class="bg-rose-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-black text-rose-600">{{ wrongCount }}</div>
                        <div class="text-[10px] font-bold text-rose-400 uppercase">Xato</div>
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-3 space-y-2 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Test:</span>
                        <span class="font-bold text-slate-700">{{ quiz?.title }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">Holat:</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase"
                              :class="passed ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                            {{ passed ? 'O\'tdi' : 'O\'tmadi' }}
                        </span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-400">O'tish bali:</span>
                        <span class="font-bold text-slate-700">{{ quiz?.pass_score }}%</span>
                    </div>
                </div>
            </div>

            <!-- View detail button -->
            <button @click="router.visit('/webapp/attempt/' + attempt.id)"
                    class="w-full bg-white border border-slate-200 text-slate-700 font-bold text-sm py-3.5 rounded-2xl flex items-center justify-center gap-2 active:scale-[0.97] transition-all">
                <i class="fas fa-list-check text-indigo-500"></i>
                Javoblarni ko'rish
            </button>

            <!-- Home -->
            <Link href="/webapp"
                  class="block w-full bg-indigo-600 text-white rounded-2xl font-bold text-sm py-3.5 shadow-lg shadow-indigo-200 flex items-center justify-center gap-2 active:scale-[0.97] transition-all">
                <i class="fas fa-home"></i>
                Bosh sahifa
            </Link>
        </div>
    </div>
</template>
