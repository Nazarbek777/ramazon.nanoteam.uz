<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    subjects: Array,
});

const testId = ref('');

const joinQuiz = () => {
    if (!testId.value) return;
    router.post('/webapp/quiz/join', { code: testId.value });
};
</script>

<template>
    <Head title="Asosiy Sahifa" />

    <div class="min-h-screen bg-[#F8F9FF] text-[#1E293B] pb-10">
        <!-- Header Section -->
        <div class="bg-indigo-600 px-6 pt-12 pb-24 rounded-b-[40px] shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-indigo-400/20 rounded-full -ml-8 -mb-8 blur-2xl"></div>
            
            <div class="relative z-10 flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-white text-3xl font-extrabold tracking-tight">Assalomu alaykum!</h1>
                    <p class="text-indigo-100 mt-1 opacity-90">Keling, bugun bilimingizni sinaymiz</p>
                </div>
                <div class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-2xl flex items-center justify-center border border-white/30">
                    <i class="fas fa-bell text-white"></i>
                </div>
            </div>
        </div>

        <!-- content container -->
        <div class="px-6 -mt-16 relative z-20">
            <!-- Test ID Input Section -->
            <div class="bg-white p-6 rounded-[32px] shadow-xl shadow-indigo-100/50 border border-white mb-8">
                <h3 class="font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-key text-indigo-500 mr-2"></i>
                    Testga ID orqali kirish
                </h3>
                <form @submit.prevent="joinQuiz" class="relative">
                    <input v-model="testId" type="text" placeholder="Test ID kodi (masalan: MAT-2024)"
                           class="w-full bg-slate-50 border-none rounded-2xl py-4 pl-5 pr-14 text-sm font-semibold focus:ring-4 focus:ring-indigo-500/10 placeholder:text-slate-400 transition-all uppercase">
                    <button type="submit" 
                            class="absolute right-2 top-2 bottom-2 bg-indigo-600 text-white w-10 h-10 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200 active:scale-90 transition-all">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <p v-if="$page.props.flash?.error" class="text-xs text-red-500 mt-2 ml-1 font-bold">{{ $page.props.flash.error }}</p>
                </form>
            </div>

            <!-- Subjects and Quizzes -->
            <div class="space-y-8">
                <div v-for="subject in subjects" :key="subject.id" class="space-y-4">
                    <div class="flex items-center space-x-2 px-2">
                        <div class="w-1.5 h-6 bg-indigo-600 rounded-full"></div>
                        <h2 class="text-xl font-bold text-gray-800 tracking-tight">{{ subject.name }}</h2>
                    </div>
                    
                    <div class="grid gap-4">
                        <div v-for="quiz in subject.quizzes" :key="quiz.id"
                             @click="router.visit('/webapp/quiz/' + quiz.id)"
                             class="bg-white p-5 rounded-[24px] shadow-sm border border-gray-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer group">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300">
                                        <i :class="subject.icon || 'fas fa-book-open'" class="text-2xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-gray-900 group-hover:text-indigo-600 transition-colors">{{ quiz.title }}</h3>
                                        <div class="flex items-center space-x-3 mt-1 text-xs text-gray-500 font-medium">
                                            <span class="flex items-center"><i class="far fa-clock mr-1 text-indigo-400"></i> {{ quiz.time_limit }} daqiqa</span>
                                            <span class="flex items-center"><i class="fas fa-bullseye mr-1 text-emerald-400"></i> {{ quiz.pass_score }}%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center group-hover:bg-indigo-50 transition-colors">
                                    <i class="fas fa-chevron-right text-gray-300 group-hover:text-indigo-600"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="subjects.length === 0" class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-300">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                        <i class="fas fa-folder-open text-3xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">Hozircha fanlar mavjud emas</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
body {
    -webkit-tap-highlight-color: transparent;
}
</style>
