<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    quiz: Object,
});

const loading = ref(false);

const startQuiz = () => {
    if (loading.value) return;
    loading.value = true;
    router.post(`/webapp/quiz/${props.quiz.id}/start`, {}, {
        onError: () => { loading.value = false; },
    });
};
</script>

<template>
    <Head :title="quiz.title" />

    <div class="min-h-screen bg-slate-50 flex flex-col" style="font-family: 'Outfit', sans-serif;">

        <!-- Header -->
        <div class="bg-indigo-600 px-5 pt-10 pb-20 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-48 h-48 bg-white/10 rounded-full -mr-12 -mt-12 blur-2xl"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/5 rounded-full -ml-10 -mb-10"></div>
            <button @click="router.visit('/webapp')" class="flex items-center gap-2 text-indigo-200 text-sm mb-5 relative z-10">
                <i class="fas fa-arrow-left"></i>
                <span class="font-medium">Orqaga</span>
            </button>
            <div class="relative z-10">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center mb-4 border border-white/30">
                    <i class="fas fa-clipboard-list text-white text-2xl"></i>
                </div>
                <h1 class="text-white text-xl font-extrabold leading-tight">{{ quiz.title }}</h1>
                <p class="text-indigo-200 text-sm mt-1">{{ quiz.subject?.name }}</p>
            </div>
        </div>

        <!-- Content -->
        <div class="px-5 -mt-10 relative z-10 flex flex-col gap-4 flex-1">

            <!-- Quiz info cards -->
            <div class="bg-white rounded-2xl shadow-md p-5">
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-4">Test ma'lumotlari</h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-indigo-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-black text-indigo-600">{{ quiz.time_limit }}</div>
                        <div class="text-[9px] font-bold text-indigo-400 uppercase mt-0.5">Daqiqa</div>
                    </div>
                    <div class="bg-emerald-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-black text-emerald-600">{{ quiz.pass_score }}%</div>
                        <div class="text-[9px] font-bold text-emerald-400 uppercase mt-0.5">O'tish bali</div>
                    </div>
                    <div class="bg-amber-50 rounded-xl p-3 text-center">
                        <div class="text-xl font-black text-amber-600">1x</div>
                        <div class="text-[9px] font-bold text-amber-400 uppercase mt-0.5">Urinish</div>
                    </div>
                </div>
            </div>

            <!-- Warning -->
            <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4 flex items-start gap-3">
                <div class="w-9 h-9 bg-rose-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-exclamation-triangle text-rose-500"></i>
                </div>
                <div>
                    <h4 class="font-black text-rose-700 text-sm mb-1">Diqqat!</h4>
                    <ul class="text-rose-500 text-xs space-y-1 font-medium leading-relaxed">
                        <li>• Testni boshlaganingizdan so'ng uni qaytadan yechib bo'lmaydi</li>
                        <li>• Testni boshlaganingizdan so'ng vaqt hisoblana boshlaydi</li>
                        <li>• Vaqt tugasa test avtomatik yakunlanadi</li>
                        <li>• Natija darhol ko'rsatiladi</li>
                    </ul>
                </div>
            </div>

            <!-- Schedule info -->
            <div v-if="quiz.ends_at" class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-3 flex items-center gap-3 text-sm">
                <i class="fas fa-calendar-times text-amber-400 text-base flex-shrink-0"></i>
                <div class="text-amber-700 text-xs font-medium">
                    Bu test <strong>{{ new Date(quiz.ends_at).toLocaleString('uz-UZ', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}) }}</strong> gacha mavjud
                </div>
            </div>

            <!-- Spacer -->
            <div class="flex-1"></div>

            <!-- Start Button -->
            <div class="pb-8 pt-2">
                <button @click="startQuiz"
                        :disabled="loading"
                        class="w-full bg-indigo-600 text-white font-black text-base py-4 rounded-2xl shadow-xl shadow-indigo-200 active:scale-95 transition-all duration-200 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-3">
                    <span v-if="loading">
                        <i class="fas fa-spinner fa-spin mr-2"></i>Yuklanmoqda...
                    </span>
                    <span v-else>
                        <i class="fas fa-play mr-2"></i>Testni boshlash
                    </span>
                </button>
                <p class="text-center text-xs text-slate-400 mt-3 font-medium">
                    Boshlash tugmasini bosgandan so'ng vaqt boshlanadi
                </p>
            </div>
        </div>
    </div>
</template>
