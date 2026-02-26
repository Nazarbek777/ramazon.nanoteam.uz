<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    quiz: Object,
    type: String,       // 'completed' | 'expired'
    attempt: Object,
    message: String,
});
</script>

<template>
    <Head :title="type === 'completed' ? 'Test yakunlangan' : 'Vaqt tugagan'" />

    <div class="min-h-screen bg-slate-50 flex flex-col select-none" style="font-family: 'Outfit', sans-serif;">

        <!-- Top -->
        <div class="px-4 pt-6 pb-8 text-center"
             :class="type === 'completed' ? 'bg-indigo-600' : 'bg-amber-500'">
            <div class="w-16 h-16 rounded-2xl mx-auto mb-3 flex items-center justify-center"
                 :class="type === 'completed' ? 'bg-indigo-500' : 'bg-amber-400'">
                <i class="text-white text-3xl"
                   :class="type === 'completed' ? 'fas fa-check-circle' : 'fas fa-hourglass-end'"></i>
            </div>
            <h1 class="text-white text-lg font-extrabold">
                {{ type === 'completed' ? 'Test allaqachon yechilgan' : 'Vaqt tugagan' }}
            </h1>
            <p class="text-white/70 text-xs mt-1">{{ message }}</p>
        </div>

        <!-- Info Card -->
        <div class="px-4 -mt-3 relative z-10">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5">
                <div class="text-center mb-4">
                    <p class="text-slate-500 text-xs font-medium">{{ quiz?.title }}</p>
                </div>

                <!-- If completed, show score -->
                <div v-if="type === 'completed' && attempt" class="space-y-3">
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="bg-indigo-50 rounded-xl p-3">
                            <div class="text-lg font-black text-indigo-600">{{ attempt.score }}%</div>
                            <div class="text-[9px] font-bold text-indigo-400 uppercase">Ball</div>
                        </div>
                        <div class="bg-emerald-50 rounded-xl p-3">
                            <div class="text-lg font-black text-emerald-600">{{ attempt.correct_answers || 0 }}</div>
                            <div class="text-[9px] font-bold text-emerald-400 uppercase">To'g'ri</div>
                        </div>
                        <div class="bg-rose-50 rounded-xl p-3">
                            <div class="text-lg font-black text-rose-600">{{ (attempt.total_questions || 0) - (attempt.correct_answers || 0) }}</div>
                            <div class="text-[9px] font-bold text-rose-400 uppercase">Noto'g'ri</div>
                        </div>
                    </div>
                </div>

                <!-- If expired -->
                <div v-if="type === 'expired'" class="text-center py-4">
                    <i class="fas fa-clock text-amber-300 text-4xl mb-3"></i>
                    <p class="text-slate-500 text-sm">Berilgan vaqt ichida testni yakunlay olmadingiz.</p>
                </div>
            </div>
        </div>

        <!-- Action -->
        <div class="px-4 mt-4 pb-6">
            <Link href="/webapp"
                  class="block w-full h-12 bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-200 flex items-center justify-center active:scale-[0.97] transition-all">
                <i class="fas fa-arrow-left mr-2 text-xs"></i>
                Bosh sahifaga qaytish
            </Link>
        </div>
    </div>
</template>
