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
                <p class="text-indigo-100 mt-1 text-sm opacity-90">Abdullayevna Test Bot</p>
            </div>
        </div>

        <div class="px-4 -mt-12 relative z-20 space-y-4">
            <!-- Test ID Input -->
            <form @submit.prevent="joinQuiz" class="bg-white rounded-2xl shadow-md shadow-indigo-100/40 border border-white px-4 py-3 flex gap-2">
                <input v-model="testId" type="text" placeholder="Test ID kiriting..."
                       class="flex-1 bg-slate-50 rounded-xl py-2.5 px-3 text-sm font-semibold focus:ring-4 focus:ring-indigo-500/10 placeholder:text-slate-400 uppercase outline-none">
                <button type="submit"
                        class="bg-indigo-600 text-white px-4 rounded-xl font-bold text-sm active:scale-90 transition-all flex items-center gap-1">
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            <p v-if="$page.props.flash?.error" class="text-xs text-red-500 -mt-3 ml-1 font-bold">{{ $page.props.flash.error }}</p>

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
