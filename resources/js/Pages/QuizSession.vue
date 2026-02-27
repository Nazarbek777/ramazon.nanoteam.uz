<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, onUnmounted } from 'vue';

const props = defineProps({
    quiz: { type: Object, default: () => ({ title: 'Test', time_limit: 30 }) },
    questions: { type: Array, default: () => [] },
    startedAt: { type: String, default: () => new Date().toISOString() },
    attemptId: Number
});

const currentQuestionIndex = ref(0);

// Restore answers from localStorage on reload
const storageKey = computed(() => `quiz_answers_${props.attemptId}`);
const savedAnswers = localStorage.getItem(`quiz_answers_${props.attemptId}`);
const answers = ref(savedAnswers ? JSON.parse(savedAnswers) : {});

const calculateTimeLeft = () => {
    try {
        if (!props.startedAt) return (props.quiz?.time_limit || 30) * 60;
        const started = new Date(props.startedAt).getTime();
        if (isNaN(started)) return (props.quiz?.time_limit || 30) * 60;
        const now = new Date().getTime();
        const elapsedSeconds = Math.floor((now - started) / 1000);
        const limitSeconds = (props.quiz?.time_limit || 30) * 60;
        const remaining = limitSeconds - elapsedSeconds;
        return remaining > 0 ? remaining : 0;
    } catch (e) {
        return 30 * 60;
    }
};

const timeLeft = ref(calculateTimeLeft());
let timerInterval = null;

const currentQuestion = computed(() => props.questions[currentQuestionIndex.value]);
const progress = computed(() => ((currentQuestionIndex.value + 1) / props.questions.length) * 100);
const answeredCount = computed(() => Object.keys(answers.value).length);

onMounted(() => {
    timerInterval = setInterval(() => {
        timeLeft.value = calculateTimeLeft();
        if (timeLeft.value <= 0) {
            clearInterval(timerInterval);
            performSubmit();
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
    // Persist to localStorage so reload doesn't lose answers
    localStorage.setItem(storageKey.value, JSON.stringify(answers.value));
};

const submitQuiz = () => {
    if (confirm('Testni yakunlamoqchimisiz?')) {
        performSubmit();
    }
};

const performSubmit = () => {
    localStorage.removeItem(storageKey.value); // clear saved answers
    router.post('/webapp/quiz/' + props.quiz.id + '/submit', {
        answers: answers.value,
        attempt_id: props.attemptId
    });
};
</script>

<template>
    <Head :title="quiz.title" />

    <div class="min-h-screen bg-slate-50 flex flex-col select-none" style="font-family: 'Outfit', sans-serif;">

        <!-- Compact Top Bar -->
        <div class="bg-indigo-600 px-4 pt-3 pb-4 relative">
            <div class="flex items-center justify-between mb-3">
                <button @click="router.visit('/webapp')" class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center text-white text-sm">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <h1 class="text-white text-sm font-bold truncate mx-3 flex-1 text-center">{{ quiz.title }}</h1>
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold"
                     :class="timeLeft < 60 ? 'bg-red-500 text-white animate-pulse' : 'bg-white/20 text-white'">
                    <i class="far fa-clock text-[10px]"></i>
                    <span class="font-mono">{{ formatTime(timeLeft) }}</span>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="flex items-center gap-2">
                <div class="flex-1 h-1.5 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-400 rounded-full transition-all duration-500" :style="{ width: progress + '%' }"></div>
                </div>
                <span class="text-white/80 text-[10px] font-bold whitespace-nowrap">{{ currentQuestionIndex + 1 }}/{{ questions.length }}</span>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="!questions || questions.length === 0" class="flex-1 flex items-center justify-center p-6">
            <div class="text-center bg-white rounded-2xl p-8 shadow-sm">
                <i class="fas fa-inbox text-slate-200 text-4xl mb-3"></i>
                <p class="text-slate-600 font-bold text-sm mb-1">Savollar topilmadi</p>
                <p class="text-slate-400 text-xs mb-4">Bu testda hozircha savollar yo'q</p>
                <button @click="router.visit('/webapp')" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-xs font-bold">Orqaga</button>
            </div>
        </div>

        <!-- Question Content -->
        <div v-else class="flex-1 flex flex-col p-4 pb-2">
            <!-- Question Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-3 flex-shrink-0">
                <div class="flex items-center gap-2 mb-3">
                    <span class="w-6 h-6 bg-indigo-100 text-indigo-600 rounded-md flex items-center justify-center text-xs font-black">{{ currentQuestionIndex + 1 }}</span>
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Savol</span>
                    <span class="ml-auto text-[10px] text-slate-400 font-medium">{{ answeredCount }}/{{ questions.length }} javob</span>
                </div>
                <p class="text-sm font-bold text-slate-800 leading-relaxed">{{ currentQuestion.content }}</p>
            </div>

            <!-- Options -->
            <div class="flex-1 overflow-y-auto space-y-2 mb-3 custom-scroll">
                <div v-for="(option, index) in currentQuestion.options" :key="option.id"
                     @click="selectOption(option.id)"
                     class="flex items-center gap-3 p-3.5 rounded-xl border-2 transition-all duration-200 cursor-pointer active:scale-[0.98]"
                     :class="answers[currentQuestion.id] === option.id
                         ? 'border-indigo-500 bg-indigo-50'
                         : 'border-slate-100 bg-white hover:border-slate-200'">

                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-black flex-shrink-0 transition-all"
                         :class="answers[currentQuestion.id] === option.id
                             ? 'bg-indigo-600 text-white shadow-md shadow-indigo-200'
                             : 'bg-slate-100 text-slate-400'">
                        {{ String.fromCharCode(65 + index) }}
                    </div>

                    <span class="text-sm font-semibold flex-1"
                          :class="answers[currentQuestion.id] === option.id ? 'text-indigo-700' : 'text-slate-600'">
                        {{ option.content }}
                    </span>

                    <i v-if="answers[currentQuestion.id] === option.id" class="fas fa-check-circle text-indigo-500 text-sm"></i>
                </div>
            </div>

            <!-- Bottom Nav -->
            <div class="flex gap-2 pt-2 pb-1 flex-shrink-0">
                <button @click="prevQuestion"
                        :disabled="currentQuestionIndex === 0"
                        class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 disabled:opacity-30 active:scale-90 transition-all">
                    <i class="fas fa-chevron-left text-sm"></i>
                </button>

                <button v-if="currentQuestionIndex < questions.length - 1"
                        @click="nextQuestion"
                        class="flex-1 h-12 bg-indigo-600 text-white rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg shadow-indigo-200 active:scale-[0.97] transition-all">
                    Keyingi
                    <i class="fas fa-chevron-right text-xs"></i>
                </button>

                <button v-else
                        @click="submitQuiz"
                        class="flex-1 h-12 bg-emerald-500 text-white rounded-xl font-bold text-sm flex items-center justify-center gap-2 shadow-lg shadow-emerald-200 active:scale-[0.97] transition-all">
                    <i class="fas fa-check-double text-xs"></i>
                    Yakunlash
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar { width: 3px; }
.custom-scroll::-webkit-scrollbar-track { background: transparent; }
.custom-scroll::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
</style>
