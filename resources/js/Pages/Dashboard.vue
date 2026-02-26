<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    subjects: Array,
});
</script>

<template>
    <Head title="Asosiy Sahifa" />

    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-md mx-auto">
            <header class="mb-8 text-center">
                <h1 class="text-3xl font-bold text-indigo-900 leading-tight">Test Tizimiga Xush Kelibsiz!</h1>
                <p class="text-gray-600 mt-2">O'zingizga kerakli fanni tanlang va bilimingizni sinab ko'ring.</p>
            </header>

            <div class="grid gap-4">
                <div v-for="subject in subjects" :key="subject.id" class="space-y-2">
                    <h2 class="text-lg font-bold text-gray-700 ml-2 mt-4">{{ subject.name }}</h2>
                    <div v-for="quiz in subject.quizzes" :key="quiz.id"
                         @click="router.visit(route('webapp.quiz.show', quiz.id))"
                         class="bg-white p-4 rounded-2xl shadow-sm border hover:shadow-md transition cursor-pointer flex items-center space-x-4">
                        <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600">
                            <i :class="subject.icon || 'fas fa-book'"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-gray-800">{{ quiz.title }}</h3>
                            <p class="text-gray-500 text-xs">{{ quiz.time_limit }} daqiqa • {{ quiz.pass_score }}% o'tish bali</p>
                        </div>
                        <i class="fas fa-chevron-right text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
