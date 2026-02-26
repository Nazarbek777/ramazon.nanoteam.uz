<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    subject: Object,
    quizzes: Array,
    quizStatuses: { type: Object, default: () => ({}) },
});

const getStatus = (quizId) => props.quizStatuses[quizId] || null;
</script>

<template>
    <Head :title="subject.name" />
    <AppLayout>
        <!-- Header -->
        <div class="bg-indigo-600 px-5 pt-10 pb-16 rounded-b-[36px] relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500 to-indigo-700"></div>
            <button @click="router.visit('/webapp')" class="relative z-10 flex items-center gap-1.5 text-indigo-200 text-sm mb-5">
                <i class="fas fa-arrow-left text-xs"></i> Orqaga
            </button>
            <div class="relative z-10">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mb-3 border border-white/20">
                    <i :class="subject.icon || 'fas fa-book'" class="text-white text-xl"></i>
                </div>
                <h1 class="text-white text-xl font-extrabold">{{ subject.name }}</h1>
                <p class="text-indigo-200 text-sm mt-0.5">{{ quizzes.length }} ta test mavjud</p>
            </div>
        </div>

        <!-- Quizzes list -->
        <div class="px-4 -mt-8 relative z-10 space-y-3">
            <div v-for="quiz in quizzes" :key="quiz.id"
                 @click="router.visit('/webapp/quiz/' + quiz.id)"
                 class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 cursor-pointer active:scale-[0.98] transition-all duration-150 relative overflow-hidden">

                <!-- Status badge top-right -->
                <div v-if="getStatus(quiz.id)?.status === 'completed'"
                     class="absolute top-0 right-0 bg-emerald-500 text-white text-[9px] font-bold px-2.5 py-1 rounded-bl-xl uppercase">
                    ✓ Yechilgan
                </div>
                <div v-else-if="getStatus(quiz.id)?.status === 'expired'"
                     class="absolute top-0 right-0 bg-rose-500 text-white text-[9px] font-bold px-2.5 py-1 rounded-bl-xl uppercase">
                    ⌛ Vaqt tugagan
                </div>
                <div v-else-if="getStatus(quiz.id)?.status === 'in_progress'"
                     class="absolute top-0 right-0 bg-blue-500 text-white text-[9px] font-bold px-2.5 py-1 rounded-bl-xl uppercase">
                    ▶ Davom etmoqda
                </div>

                <div class="flex items-center gap-3">
                    <!-- Icon -->
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-colors"
                         :class="getStatus(quiz.id)?.status === 'completed' ? 'bg-emerald-50 text-emerald-500' :
                                 getStatus(quiz.id)?.status === 'expired' ? 'bg-rose-50 text-rose-400' :
                                 'bg-indigo-50 text-indigo-600'">
                        <i :class="getStatus(quiz.id)?.status === 'completed' ? 'fas fa-check-circle' :
                                    getStatus(quiz.id)?.status === 'expired' ? 'fas fa-hourglass-end' :
                                    'fas fa-clipboard-list'"></i>
                    </div>

                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-gray-900 text-sm truncate">{{ quiz.title }}</h3>
                        <div class="flex items-center gap-3 mt-0.5 text-[11px] text-slate-400 font-medium">
                            <span><i class="far fa-clock mr-1"></i>{{ quiz.time_limit }} daq</span>
                            <span><i class="fas fa-bullseye mr-1"></i>{{ quiz.pass_score }}%</span>
                            <span v-if="getStatus(quiz.id)?.status === 'completed'" class="text-emerald-500 font-bold">
                                {{ getStatus(quiz.id).score }}%
                            </span>
                        </div>
                        <!-- Schedule -->
                        <div v-if="quiz.ends_at" class="mt-1 text-[10px] text-rose-400 flex items-center gap-1">
                            <i class="fas fa-stop-circle"></i>
                            {{ new Date(quiz.ends_at).toLocaleString('uz-UZ',{day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'}) }} gacha
                        </div>
                    </div>

                    <i class="fas fa-chevron-right text-slate-200 text-sm flex-shrink-0"></i>
                </div>
            </div>

            <div v-if="quizzes.length === 0" class="bg-white rounded-2xl p-10 text-center border border-dashed border-slate-200">
                <i class="fas fa-inbox text-slate-200 text-3xl mb-3"></i>
                <p class="text-slate-400 font-medium text-sm">Bu fanda hozircha testlar yo'q</p>
            </div>
        </div>
    </AppLayout>
</template>
