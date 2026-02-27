<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    attempt: Object,
    quiz: Object,
});
</script>

<template>
    <Head title="Javoblar tahlili" />
    <AppLayout>
        <!-- Header -->
        <div class="bg-indigo-600 px-5 pt-10 pb-6">
            <button @click="router.visit('/webapp/history')" class="flex items-center gap-1.5 text-indigo-200 text-sm mb-4">
                <i class="fas fa-arrow-left text-xs"></i> Tarixga qaytish
            </button>
            <h1 class="text-white text-lg font-extrabold">Javoblar tahlili</h1>
            <p class="text-indigo-200 text-xs mt-0.5">{{ quiz?.title }}</p>

            <div class="flex gap-4 mt-4">
                <div class="flex items-center gap-1.5 text-sm font-bold text-emerald-300">
                    <i class="fas fa-check-circle"></i>
                    {{ attempt?.correct_answers || 0 }} to'g'ri
                </div>
                <div class="flex items-center gap-1.5 text-sm font-bold text-rose-300">
                    <i class="fas fa-times-circle"></i>
                    {{ (attempt?.total_questions || 0) - (attempt?.correct_answers || 0) }} xato
                </div>
            </div>
        </div>

        <!-- Questions -->
        <div class="px-4 py-4 space-y-3">
            <div v-for="(answer, i) in attempt?.answers" :key="answer.id"
                 class="bg-white rounded-xl border p-4"
                 :class="answer.is_correct ? 'border-emerald-100' : 'border-rose-100'">

                <!-- Question header -->
                <div class="flex items-start gap-2 mb-3">
                    <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-black flex-shrink-0 mt-0.5"
                         :class="answer.is_correct ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'">
                        {{ i + 1 }}
                    </div>
                    <p class="text-sm font-semibold text-slate-800 leading-snug flex-1">{{ answer.question?.content }}</p>
                    <i class="text-sm flex-shrink-0 mt-0.5"
                       :class="answer.is_correct ? 'fas fa-check-circle text-emerald-500' : 'fas fa-times-circle text-rose-500'"></i>
                </div>

                <!-- Options -->
                <div class="space-y-1.5">
                    <div v-for="option in answer.question?.options" :key="option.id"
                         class="flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-medium"
                         :class="option.is_correct
                             ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                             : option.id === answer.option_id && !answer.is_correct
                                 ? 'bg-rose-50 text-rose-600 border border-rose-200'
                                 : 'bg-slate-50 text-slate-500'">
                        <i v-if="option.is_correct" class="fas fa-check text-emerald-500 flex-shrink-0 text-[10px]"></i>
                        <i v-else-if="option.id === answer.option_id && !answer.is_correct" class="fas fa-times text-rose-500 flex-shrink-0 text-[10px]"></i>
                        <div v-else class="w-2 h-2 rounded-full bg-slate-300 flex-shrink-0"></div>
                        {{ option.content }}
                    </div>
                </div>
            </div>

            <div v-if="!attempt?.answers?.length" class="bg-white rounded-2xl p-10 text-center border border-dashed border-slate-200">
                <i class="fas fa-inbox text-slate-200 text-3xl mb-2"></i>
                <p class="text-slate-400 text-sm">Javoblar topilmadi</p>
            </div>
        </div>
    </AppLayout>
</template>
