<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    quiz: Object,
    questions: Array,
});

const currentQuestionIndex = ref(0);
const answers = ref({});
const timeLeft = ref(props.quiz.time_limit * 60);

const currentQuestion = computed(() => props.questions[currentQuestionIndex.value]);

onMounted(() => {
    const timer = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timer);
            submitQuiz();
        }
    }, 1000);
});

const formatTime = (seconds) => {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s < 10 ? '0' : ''}${s}`;
};

const nextQuestion = () => {
    if (currentQuestionIndex.value < props.questions.length - 1) {
        currentQuestionIndex.value++;
    }
};

const prevQuestion = () => {
    if (currentQuestionIndex.value > 0) {
        currentQuestionIndex.value--;
    }
};

const selectOption = (optionId) => {
    answers.value[currentQuestion.value.id] = optionId;
};

const submitQuiz = () => {
    router.post(route('webapp.quiz.submit', props.quiz.id), {
        answers: answers.value
    });
};
</script>

<template>
    <Head :title="quiz.title" />

    <div class="min-h-screen bg-gray-50 flex flex-col p-4">
        <div class="max-w-md mx-auto w-full flex-1 flex flex-col">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="font-bold text-gray-800">{{ quiz.title }}</h1>
                    <p class="text-xs text-gray-500">{{ currentQuestionIndex + 1 }} / {{ questions.length }} savol</p>
                </div>
                <div class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full font-mono font-bold">
                    {{ formatTime(timeLeft) }}
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-1.5 mb-8">
                <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-300" 
                     :style="{ width: ((currentQuestionIndex + 1) / questions.length * 100) + '%' }"></div>
            </div>

            <!-- Question Content -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border mb-6 flex-1">
                <div class="text-lg text-gray-800 font-medium mb-8 leading-relaxed">
                    {{ currentQuestion.content }}
                </div>

                <div class="space-y-4">
                    <div v-for="option in currentQuestion.options" :key="option.id"
                         @click="selectOption(option.id)"
                         class="p-4 rounded-xl border-2 transition cursor-pointer flex items-center space-x-3"
                         :class="answers[currentQuestion.id] === option.id ? 'border-indigo-600 bg-indigo-50' : 'border-gray-100 hover:border-indigo-200'">
                        <div class="w-6 h-6 rounded-full border-2 flex items-center justify-center"
                             :class="answers[currentQuestion.id] === option.id ? 'border-indigo-600 bg-indigo-600' : 'border-gray-300'">
                            <div v-if="answers[currentQuestion.id] === option.id" class="w-2 h-2 bg-white rounded-full"></div>
                        </div>
                        <span class="text-gray-700 font-medium">{{ option.content }}</span>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex space-x-4">
                <button @click="prevQuestion" :disabled="currentQuestionIndex === 0"
                        class="flex-1 py-3 border border-gray-300 rounded-xl text-gray-600 font-bold disabled:opacity-30">
                    Oldingisi
                </button>
                <button v-if="currentQuestionIndex < questions.length - 1" @click="nextQuestion"
                        class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-bold shadow-lg shadow-indigo-200">
                    Keyingisi
                </button>
                <button v-else @click="submitQuiz"
                        class="flex-1 py-3 bg-green-600 text-white rounded-xl font-bold shadow-lg shadow-green-200">
                    Tugallash
                </button>
            </div>
        </div>
    </div>
</template>
