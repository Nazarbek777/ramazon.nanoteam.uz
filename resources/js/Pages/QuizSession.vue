<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, onUnmounted } from 'vue';

const props = defineProps({
    quiz: Object,
    questions: Array,
});

const currentQuestionIndex = ref(0);
const answers = ref({});
const timeLeft = ref(props.quiz.time_limit * 60);
let timerInterval = null;

const currentQuestion = computed(() => props.questions[currentQuestionIndex.value]);

onMounted(() => {
    timerInterval = setInterval(() => {
        if (timeLeft.value > 0) {
            timeLeft.value--;
        } else {
            clearInterval(timerInterval);
            submitQuiz();
        }
    }, 1000);
});

onUnmounted(() => {
    if (timerInterval) clearInterval(timerInterval);
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
    if (confirm('Testni yakunlamoqchimisiz?')) {
        router.post('/webapp/quiz/' + props.quiz.id + '/submit', {
            answers: answers.value
        });
    }
};
</script>

<template>
    <Head :title="quiz.title" />

    <div class="min-h-screen bg-[#F8F9FF] flex flex-col font-outfit">
        <!-- Top Bar -->
        <div class="bg-white px-6 py-4 flex justify-between items-center shadow-sm sticky top-0 z-50 border-b border-indigo-50">
            <div class="flex items-center space-x-3">
                <button @click="router.visit('/webapp')" class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">
                    <i class="fas fa-times"></i>
                </button>
                <div>
                    <h1 class="text-sm font-bold text-gray-900 leading-none mb-1">{{ quiz.title }}</h1>
                    <div class="flex items-center text-[10px] text-gray-400 font-bold uppercase tracking-wider">
                        <span class="text-indigo-600 mr-1">{{ currentQuestionIndex + 1 }}</span> / {{ questions.length }} savol
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2 px-4 py-2 bg-indigo-50 rounded-2xl border border-indigo-100">
                <i class="far fa-clock text-indigo-600 animate-pulse"></i>
                <span class="text-indigo-700 font-black font-mono text-lg">{{ formatTime(timeLeft) }}</span>
            </div>
        </div>

        <div class="max-w-md mx-auto w-full flex-1 flex flex-col p-6">
            <!-- Progress Tracker -->
            <div class="flex space-x-1 mb-8">
                <div v-for="(q, index) in questions" :key="index" 
                     class="h-1.5 flex-1 rounded-full transition-all duration-300"
                     :class="[
                        index === currentQuestionIndex ? 'bg-indigo-600 scale-y-125 shadow-lg shadow-indigo-100' : 
                        answers[q.id] ? 'bg-emerald-400' : 'bg-gray-200'
                     ]">
                </div>
            </div>

            <!-- Question Card -->
            <div class="flex-1 flex flex-col">
                <span class="text-[10px] font-black text-indigo-600 uppercase tracking-[0.2em] mb-3 block">Savol mazmuni</span>
                <div class="bg-white p-8 rounded-[32px] shadow-xl shadow-indigo-100/20 border border-white min-h-[200px] mb-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-indigo-50/50 rounded-full -mr-12 -mt-12"></div>
                    <div class="relative z-10 text-xl text-gray-800 font-bold leading-relaxed mb-10">
                        {{ currentQuestion.content }}
                    </div>

                    <!-- Options -->
                    <div class="space-y-4">
                        <div v-for="option in currentQuestion.options" :key="option.id"
                             @click="selectOption(option.id)"
                             class="group p-5 rounded-2xl border-2 transition-all duration-300 cursor-pointer flex items-center space-x-4 relative"
                             :class="answers[currentQuestion.id] === option.id ? 'border-indigo-600 bg-indigo-50/50' : 'border-gray-50 hover:border-indigo-100 hover:bg-white'">
                            
                            <div class="w-7 h-7 rounded-lg border-2 flex items-center justify-center transition-all duration-300 shadow-sm"
                                 :class="answers[currentQuestion.id] === option.id ? 'border-indigo-600 bg-indigo-600' : 'border-gray-200 bg-white group-hover:border-indigo-300'">
                                <i v-if="answers[currentQuestion.id] === option.id" class="fas fa-check text-white text-xs"></i>
                            </div>
                            
                            <span class="text-gray-700 font-bold group-hover:text-indigo-900 transition-colors">{{ option.content }}</span>
                            
                            <div v-if="answers[currentQuestion.id] === option.id" class="absolute right-4 w-2 h-2 bg-indigo-600 rounded-full"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Navigation -->
            <div class="flex space-x-4 sticky bottom-6 mt-auto">
                <button @click="prevQuestion" :disabled="currentQuestionIndex === 0"
                        class="w-16 h-14 bg-white border border-gray-100 rounded-2xl text-gray-400 hover:text-indigo-600 transition-all flex items-center justify-center disabled:opacity-30 disabled:pointer-events-none hover:shadow-lg">
                    <i class="fas fa-arrow-left"></i>
                </button>
                
                <button v-if="currentQuestionIndex < questions.length - 1" @click="nextQuestion"
                        class="flex-1 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black shadow-xl shadow-indigo-200 transition-all active:scale-95 flex items-center justify-center space-x-2">
                    <span>Keyingi savol</span>
                    <i class="fas fa-chevron-right text-xs opacity-70"></i>
                </button>
                
                <button v-else @click="submitQuiz"
                        class="flex-1 h-14 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black shadow-xl shadow-emerald-100 transition-all active:scale-95 flex items-center justify-center space-x-2">
                    <span>Testni yakunlash</span>
                    <i class="fas fa-flag-checkered"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.font-mono {
    font-family: 'Outfit', monospace;
}
</style>
