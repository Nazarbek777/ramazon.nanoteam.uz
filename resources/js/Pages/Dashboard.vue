<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    subjects: Array,
    quizStatuses: { type: Object, default: () => ({}) },
});

const testId = ref('');

const joinQuiz = () => {
    if (!testId.value) return;
    router.post('/webapp/quiz/join', { code: testId.value });
};

const getStatus = (quizId) => {
    return props.quizStatuses[quizId] || null;
};
</script>

<template>
    <Head title="Asosiy Sahifa" />
    <AppLayout>
        <!-- Header Section -->
        <div class="bg-indigo-600 px-5 pt-10 pb-20 rounded-b-[36px] shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
            <div class="relative z-10">
                <h1 class="text-white text-2xl font-extrabold tracking-tight">Assalomu alaykum!</h1>
                <p class="text-indigo-100 mt-1 opacity-90 text-sm">Keling, bugun bilimingizni sinaymiz</p>
            </div>
        </div>

        <!-- content container -->
        <div class="px-5 -mt-12 relative z-20">
            <!-- Test ID Input -->
            <div class="bg-white p-5 rounded-2xl shadow-lg shadow-indigo-100/40 border border-white mb-6">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center text-sm">
                    <i class="fas fa-key text-indigo-500 mr-2"></i>
                    Testga ID orqali kirish
                </h3>
                <form @submit.prevent="joinQuiz" class="relative">
                    <input v-model="testId" type="text" placeholder="Test ID (masalan: MAT-2024)"
                           class="w-full bg-slate-50 border-none rounded-xl py-3.5 pl-4 pr-12 text-sm font-semibold focus:ring-4 focus:ring-indigo-500/10 placeholder:text-slate-400 transition-all uppercase">
                    <button type="submit"
                            class="absolute right-1.5 top-1.5 bottom-1.5 bg-indigo-600 text-white w-9 h-9 rounded-lg flex items-center justify-center shadow-md active:scale-90 transition-all">
                        <i class="fas fa-arrow-right text-sm"></i>
                    </button>
                    <p v-if="$page.props.flash?.error" class="text-xs text-red-500 mt-2 ml-1 font-bold">{{ $page.props.flash.error }}</p>
                </form>
            </div>

            <!-- Subjects and Quizzes -->
            <div class="space-y-6">
                <div v-for="subject in subjects" :key="subject.id" class="space-y-3">
                    <div class="flex items-center space-x-2 px-1">
                        <div class="w-1 h-5 bg-indigo-600 rounded-full"></div>
                        <h2 class="text-base font-bold text-gray-800">{{ subject.name }}</h2>
                    </div>

                    <div class="grid gap-3">
                        <div v-for="quiz in subject.quizzes" :key="quiz.id"
                             @click="router.visit('/webapp/quiz/' + quiz.id)"
                             class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-all duration-200 cursor-pointer group relative overflow-hidden">

                            <div v-if="getStatus(quiz.id)?.status === 'completed'"
                                 class="absolute top-0 right-0 bg-emerald-500 text-white text-[9px] font-bold px-2.5 py-1 rounded-bl-xl uppercase">
                                ✓ Yechilgan
                            </div>
                            <div v-else-if="getStatus(quiz.id)?.status === 'expired'"
                                 class="absolute top-0 right-0 bg-amber-500 text-white text-[9px] font-bold px-2.5 py-1 rounded-bl-xl uppercase">
                                ⏰ Vaqt tugagan
                            </div>
                            <div v-else-if="getStatus(quiz.id)?.status === 'in_progress'"
                                 class="absolute top-0 right-0 bg-blue-500 text-white text-[9px] font-bold px-2.5 py-1 rounded-bl-xl uppercase">
                                ▶ Davom etmoqda
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-lg transition-colors"
                                         :class="getStatus(quiz.id)?.status === 'completed' ? 'bg-emerald-50 text-emerald-600' :
                                                 getStatus(quiz.id)?.status === 'expired' ? 'bg-amber-50 text-amber-600' :
                                                 'bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white'">
                                        <i :class="getStatus(quiz.id)?.status === 'completed' ? 'fas fa-check-circle' :
                                                    getStatus(quiz.id)?.status === 'expired' ? 'fas fa-hourglass-end' :
                                                    (subject.icon || 'fas fa-book-open')"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 text-sm">{{ quiz.title }}</h3>
                                        <div class="flex items-center space-x-3 mt-0.5 text-[11px] text-gray-400 font-medium">
                                            <span><i class="far fa-clock mr-1"></i>{{ quiz.time_limit }} daq</span>
                                            <span><i class="fas fa-bullseye mr-1"></i>{{ quiz.pass_score }}%</span>
                                            <span v-if="getStatus(quiz.id)?.status === 'completed'" class="text-emerald-500 font-bold">
                                                {{ getStatus(quiz.id).score }}%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <i class="fas fa-chevron-right text-gray-200 text-sm"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="subjects.length === 0" class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-200">
                    <i class="fas fa-folder-open text-gray-200 text-3xl mb-3"></i>
                    <p class="text-gray-400 font-medium text-sm">Hozircha fanlar mavjud emas</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
