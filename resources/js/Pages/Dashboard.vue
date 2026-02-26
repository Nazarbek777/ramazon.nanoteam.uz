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
</script>

<template>
    <Head title="Asosiy Sahifa" />
    <AppLayout>
        <!-- Header -->
        <div class="bg-indigo-600 px-5 pt-10 pb-20 rounded-b-[36px] shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
            <div class="relative z-10">
                <h1 class="text-white text-2xl font-extrabold tracking-tight">Assalomu alaykum! 👋</h1>
                <p class="text-indigo-100 mt-1 text-sm opacity-90">Attestatsiya fanini tanlang</p>
            </div>
        </div>

        <div class="px-4 -mt-12 relative z-20 space-y-4">
            <!-- Test ID Input -->
            <div class="bg-white p-4 rounded-2xl shadow-lg shadow-indigo-100/40 border border-white">
                <h3 class="font-bold text-gray-800 mb-3 flex items-center text-sm gap-2">
                    <i class="fas fa-key text-indigo-500"></i> ID orqali kirish
                </h3>
                <form @submit.prevent="joinQuiz" class="relative">
                    <input v-model="testId" type="text" placeholder="Test ID (masalan: MAT-2024)"
                           class="w-full bg-slate-50 border-none rounded-xl py-3 pl-4 pr-12 text-sm font-semibold focus:ring-4 focus:ring-indigo-500/10 placeholder:text-slate-400 uppercase transition-all">
                    <button type="submit"
                            class="absolute right-1.5 top-1.5 bottom-1.5 bg-indigo-600 text-white w-9 h-9 rounded-lg flex items-center justify-center active:scale-90 transition-all">
                        <i class="fas fa-arrow-right text-sm"></i>
                    </button>
                    <p v-if="$page.props.flash?.error" class="text-xs text-red-500 mt-2 ml-1 font-bold">{{ $page.props.flash.error }}</p>
                </form>
            </div>

            <!-- Subjects grid -->
            <div>
                <h2 class="text-xs font-black text-slate-400 uppercase tracking-widest px-1 mb-3">Fanlar</h2>
                <div class="grid gap-3">
                    <div v-for="subject in subjects" :key="subject.id"
                         @click="router.visit('/webapp/subject/' + subject.id)"
                         class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 cursor-pointer active:scale-[0.98] transition-all group">

                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl flex items-center justify-center text-lg flex-shrink-0 bg-indigo-50 text-indigo-600 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                <i :class="subject.icon || 'fas fa-book-open'"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-gray-900 text-sm truncate">{{ subject.name }}</h3>
                                <p class="text-[11px] text-slate-400 font-medium mt-0.5">
                                    {{ subject.quizzes?.length || 0 }} ta test
                                </p>
                            </div>
                            <i class="fas fa-chevron-right text-slate-200 text-sm flex-shrink-0"></i>
                        </div>
                    </div>

                    <div v-if="subjects.length === 0" class="text-center py-14 bg-white rounded-2xl border border-dashed border-slate-200">
                        <i class="fas fa-folder-open text-slate-200 text-3xl mb-3"></i>
                        <p class="text-slate-400 font-medium text-sm">Hozircha fanlar mavjud emas</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
