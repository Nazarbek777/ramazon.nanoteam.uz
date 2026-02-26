<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ quiz: Object });
const loading = ref(false);

const start = () => {
    if (loading.value) return;
    loading.value = true;
    router.post(`/webapp/quiz/${props.quiz.id}/start`, {}, {
        onError: () => { loading.value = false; },
    });
};
</script>

<template>
    <Head :title="quiz.title" />
    <div class="min-h-screen bg-white flex flex-col" style="font-family:'Outfit',sans-serif;">

        <!-- Top hero -->
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-800 px-5 pt-12 pb-10">
            <button @click="router.visit('/webapp')" class="flex items-center gap-1.5 text-indigo-200 text-sm mb-8">
                <i class="fas fa-arrow-left text-xs"></i> Orqaga
            </button>
            <p class="text-indigo-300 text-xs font-bold uppercase tracking-widest mb-1">{{ quiz.subject?.name }}</p>
            <h1 class="text-white text-2xl font-extrabold leading-tight">{{ quiz.title }}</h1>
        </div>

        <!-- Body -->
        <div class="flex-1 flex flex-col px-5 pt-6 pb-8 gap-5">

            <!-- Quick stats -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-indigo-50 rounded-2xl p-4 text-center">
                    <div class="text-2xl font-black text-indigo-600">{{ quiz.time_limit }}</div>
                    <div class="text-[10px] font-bold text-indigo-400 uppercase mt-1">Daqiqa</div>
                </div>
                <div class="bg-emerald-50 rounded-2xl p-4 text-center">
                    <div class="text-2xl font-black text-emerald-600">{{ quiz.pass_score }}%</div>
                    <div class="text-[10px] font-bold text-emerald-400 uppercase mt-1">O'tish</div>
                </div>
                <div class="bg-rose-50 rounded-2xl p-4 text-center">
                    <div class="text-2xl font-black text-rose-500">1✗</div>
                    <div class="text-[10px] font-bold text-rose-400 uppercase mt-1">Urinish</div>
                </div>
            </div>

            <!-- Rules -->
            <div class="bg-slate-50 rounded-2xl p-5 space-y-3.5">
                <h3 class="font-black text-slate-700 text-sm flex items-center gap-2">
                    <i class="fas fa-info-circle text-indigo-400"></i> Qoidalar
                </h3>
                <div class="flex items-start gap-3 text-sm text-slate-600">
                    <div class="w-7 h-7 bg-rose-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-ban text-rose-500 text-xs"></i>
                    </div>
                    <span class="font-medium leading-snug mt-0.5">Faqat <strong>bir marta</strong> yechiladi</span>
                </div>
                <div class="flex items-start gap-3 text-sm text-slate-600">
                    <div class="w-7 h-7 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-stopwatch text-amber-500 text-xs"></i>
                    </div>
                    <span class="font-medium leading-snug mt-0.5">Boshlashda <strong>{{ quiz.time_limit }} daqiqa</strong> vaqt boshlanadi</span>
                </div>
                <div class="flex items-start gap-3 text-sm text-slate-600">
                    <div class="w-7 h-7 bg-slate-200 rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-hourglass-end text-slate-500 text-xs"></i>
                    </div>
                    <span class="font-medium leading-snug mt-0.5">Vaqt tugasa <strong>avtomatik</strong> yakunlanadi</span>
                </div>
            </div>

            <!-- Deadline -->
            <div v-if="quiz.ends_at" class="flex items-center gap-3 bg-amber-50 border border-amber-100 rounded-xl px-4 py-3">
                <i class="fas fa-calendar-times text-amber-400 flex-shrink-0"></i>
                <span class="text-xs text-amber-700 font-semibold">
                    Muddati: <strong>{{ new Date(quiz.ends_at).toLocaleString('uz-UZ',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}) }}</strong>
                </span>
            </div>

            <!-- CTA -->
            <button @click="start" :disabled="loading"
                    class="w-full bg-indigo-600 text-white font-black text-base py-4 rounded-2xl shadow-lg shadow-indigo-200 active:scale-[0.97] transition-all disabled:opacity-60 flex items-center justify-center gap-2">
                <i v-if="loading" class="fas fa-spinner fa-spin"></i>
                <i v-else class="fas fa-play"></i>
                {{ loading ? 'Yuklanmoqda...' : 'Testni boshlash' }}
            </button>
        </div>
    </div>
</template>
