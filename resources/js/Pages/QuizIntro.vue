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
    <div class="min-h-screen flex flex-col bg-slate-50" style="font-family:'Outfit',sans-serif;">

        <!-- Top banner -->
        <div class="bg-indigo-600 pt-10 pb-16 px-5 relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-indigo-700"></div>
            <button @click="router.visit('/webapp')" class="relative z-10 flex items-center gap-1.5 text-indigo-200 text-sm mb-6">
                <i class="fas fa-arrow-left text-xs"></i> Orqaga
            </button>
            <div class="relative z-10">
                <p class="text-indigo-300 text-xs font-semibold uppercase tracking-wider mb-1">{{ quiz.subject?.name }}</p>
                <h1 class="text-white text-xl font-extrabold leading-snug">{{ quiz.title }}</h1>
            </div>
        </div>

        <!-- Card floated over banner -->
        <div class="px-4 -mt-6 flex flex-col gap-4 flex-1">

            <!-- Stats row -->
            <div class="bg-white rounded-2xl shadow-md p-4 flex items-center justify-around">
                <div class="text-center">
                    <div class="text-2xl font-black text-indigo-600">{{ quiz.time_limit }}</div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">Daqiqa</div>
                </div>
                <div class="w-px h-10 bg-slate-100"></div>
                <div class="text-center">
                    <div class="text-2xl font-black text-emerald-600">{{ quiz.pass_score }}%</div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">O'tish bali</div>
                </div>
                <div class="w-px h-10 bg-slate-100"></div>
                <div class="text-center">
                    <div class="text-2xl font-black text-amber-500">1</div>
                    <div class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">Urinish</div>
                </div>
            </div>

            <!-- Warning -->
            <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4">
                <div class="flex items-center gap-2 mb-2">
                    <i class="fas fa-exclamation-circle text-rose-500"></i>
                    <span class="font-black text-rose-600 text-sm">Boshlashdan oldin o'qing</span>
                </div>
                <ul class="text-rose-500 text-xs space-y-1.5 font-medium">
                    <li class="flex items-start gap-2"><i class="fas fa-times-circle mt-0.5 flex-shrink-0"></i>Test bir martagina yechilishi mumkin</li>
                    <li class="flex items-start gap-2"><i class="fas fa-clock mt-0.5 flex-shrink-0"></i>Boshlagan zahoti vaqt hisoblana boshlaydi</li>
                    <li class="flex items-start gap-2"><i class="fas fa-hourglass-end mt-0.5 flex-shrink-0"></i>Vaqt tugasa test avtomatik yakunlanadi</li>
                </ul>
            </div>

            <!-- Deadline badge -->
            <div v-if="quiz.ends_at" class="bg-amber-50 border border-amber-100 rounded-xl px-4 py-2.5 flex items-center gap-2 text-xs text-amber-700 font-medium">
                <i class="fas fa-calendar-alt text-amber-400 flex-shrink-0"></i>
                Muddati: <strong>{{ new Date(quiz.ends_at).toLocaleString('uz-UZ',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}) }}</strong>
            </div>

            <div class="flex-1"></div>

            <!-- CTA -->
            <div class="pb-8">
                <button @click="start" :disabled="loading"
                        class="w-full bg-indigo-600 text-white font-black text-base py-4 rounded-2xl shadow-lg shadow-indigo-200 active:scale-95 transition-all disabled:opacity-60 flex items-center justify-center gap-2">
                    <i v-if="loading" class="fas fa-spinner fa-spin"></i>
                    <i v-else class="fas fa-play"></i>
                    {{ loading ? 'Yuklanmoqda...' : 'Testni boshlash' }}
                </button>
                <p class="text-center text-[11px] text-slate-400 mt-2">Boshlagan zahoti qaytib bo'lmaydi</p>
            </div>
        </div>
    </div>
</template>
