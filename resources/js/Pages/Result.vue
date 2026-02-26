<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    attempt: Object,
    quiz: Object,
});
</script>

<template>
    <Head title="Test Natijasi" />

    <div class="min-h-screen bg-indigo-900 flex items-center justify-center p-6 text-white">
        <div class="max-w-md w-full text-center">
            <div class="mb-8">
                <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4 border-4 border-white/30">
                    <i class="fas fa-trophy text-4xl text-yellow-400"></i>
                </div>
                <h1 class="text-3xl font-bold italic">TABRIKLAYMIZ!</h1>
                <p class="text-indigo-200 mt-2">Siz testni muvaffaqiyatli yakunladingiz.</p>
            </div>

            <div class="bg-white text-gray-800 rounded-3xl p-8 shadow-2xl mb-8">
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <div class="p-4 bg-gray-50 rounded-2xl border">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">To'g'ri javoblar</p>
                        <p class="text-2xl font-black text-indigo-600">{{ attempt.correct_answers }} / {{ attempt.total_questions }}</p>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border">
                        <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Umumiy Ball</p>
                        <p class="text-2xl font-black text-green-600">{{ attempt.score }}%</p>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between text-sm py-2 border-b">
                        <span class="text-gray-500">Test nomi:</span>
                        <span class="font-bold">{{ quiz.title }}</span>
                    </div>
                    <div class="flex justify-between text-sm py-2 border-b">
                        <span class="text-gray-500">Holat:</span>
                        <span class="font-bold" :class="attempt.score >= quiz.pass_score ? 'text-green-600' : 'text-red-600'">
                            {{ attempt.score >= quiz.pass_score ? "O'tdi" : "O'tmadi" }}
                        </span>
                    </div>
                </div>
            </div>

            <Link href="/webapp" class="block w-full py-4 bg-white text-indigo-900 font-bold rounded-2xl shadow-lg hover:bg-indigo-50 transition transform hover:-translate-y-1">
                Asosiy sahifaga qaytish
            </Link>
        </div>
    </div>
</template>
