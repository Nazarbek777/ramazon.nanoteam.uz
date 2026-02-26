<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    attempt: Object,
    quiz: Object,
});
</script>

<template>
    <Head title="Natija" />

    <div class="min-h-screen bg-[#F0F3FF] flex flex-col font-outfit select-none">
        <div class="max-w-md mx-auto w-full flex-1 flex flex-col p-8 pt-10">
            <!-- trophy icon -->
            <div class="relative mb-12 text-center">
                <div class="w-32 h-32 bg-indigo-600 rounded-[40px] rotate-[15deg] flex items-center justify-center mx-auto shadow-2xl shadow-indigo-300/60 relative animate-in zoom-in spin-in-12 duration-700">
                    <div class="absolute inset-0 bg-white/10 rounded-[40px] animate-pulse"></div>
                    <i class="fas fa-trophy text-white text-5xl -rotate-[15deg] drop-shadow-lg"></i>
                </div>
                <!-- sparkles -->
                <i class="fas fa-star absolute top-0 right-1/4 text-yellow-400 text-xl animate-bounce delay-100"></i>
                <i class="fas fa-star absolute bottom-0 left-1/4 text-yellow-400 text-sm animate-pulse opacity-50"></i>
                <i class="fas fa-sparkles absolute top-1/2 left-0 text-indigo-400 text-lg opacity-30 animate-pulse"></i>
            </div>

            <div class="text-center mb-10">
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">Ajoyib natija!</h1>
                <p class="text-gray-500 mt-2 font-medium italic">Bilimga intilish — muvaffaqiyat garovi.</p>
            </div>

            <!-- Score Card -->
            <div class="bg-white rounded-[40px] p-8 shadow-2xl shadow-indigo-100 border border-white mb-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50/30 rounded-full -mr-16 -mt-16 blur-xl"></div>
                
                <div class="flex items-center justify-center mb-10">
                    <div class="relative">
                        <svg class="w-36 h-36 transform -rotate-90">
                            <circle class="text-gray-100" stroke-width="12" stroke="currentColor" fill="transparent" r="60" cx="72" cy="72" />
                            <circle class="text-indigo-600 transition-all duration-1000 ease-out" stroke-width="12" :stroke-dasharray="2 * Math.PI * 60" :stroke-dashoffset="2 * Math.PI * 60 * (1 - attempt.score / 100)" stroke-linecap="round" stroke="currentColor" fill="transparent" r="60" cx="72" cy="72" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-4xl font-black text-gray-900">{{ attempt.score }}%</span>
                            <span class="text-[10px] uppercase font-bold text-gray-400 tracking-widest mt-1">Umumiy ball</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-emerald-50/50 p-5 rounded-3xl border border-emerald-100 flex flex-col items-center">
                        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center text-emerald-600 mb-2">
                            <i class="fas fa-check"></i>
                        </div>
                        <span class="text-2xl font-black text-emerald-700 leading-none">{{ attempt.correct_answers }}</span>
                        <span class="text-[10px] font-bold text-emerald-600/60 uppercase mt-1 tracking-wider">To'g'ri</span>
                    </div>
                    <div class="bg-rose-50/50 p-5 rounded-3xl border border-rose-100 flex flex-col items-center">
                        <div class="w-10 h-10 bg-rose-100 rounded-xl flex items-center justify-center text-rose-600 mb-2">
                            <i class="fas fa-times"></i>
                        </div>
                        <span class="text-2xl font-black text-rose-700 leading-none">{{ attempt.total_questions - attempt.correct_answers }}</span>
                        <span class="text-[10px] font-bold text-rose-600/60 uppercase mt-1 tracking-wider">Noto'g'ri</span>
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-gray-50 space-y-4">
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-400 font-medium">Test:</span>
                        <span class="text-gray-800 font-bold">{{ quiz.title }}</span>
                    </div>
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-gray-400 font-medium">Holat:</span>
                        <span :class="attempt.score >= quiz.pass_score ? 'text-emerald-600 bg-emerald-100' : 'text-rose-600 bg-rose-100'" 
                              class="px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest">
                            {{ attempt.score >= quiz.pass_score ? "Muvaffaqiyatli" : "Yechilmadi" }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- buttons -->
            <div class="space-y-4 py-4">
                <Link href="/webapp" class="block w-full h-16 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black shadow-xl shadow-indigo-200 transition-all flex items-center justify-center active:scale-95">
                    Yana yechib ko'rish
                </Link>
            </div>
        </div>
    </div>
</template>
