<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, computed, onUnmounted } from 'vue';

// Debug: Global Error Handler
if (typeof window !== 'undefined') {
    window.onerror = function(message, source, lineno, colno, error) {
        alert('JS Error: ' + message + ' \nLine: ' + lineno);
    };
    window.onunhandledrejection = function(event) {
        alert('Promise Rejection: ' + event.reason);
    };
}

const props = defineProps({
    quiz: { type: Object, default: () => ({ title: 'Test', time_limit: 30 }) },
    questions: { type: Array, default: () => [] },
    startedAt: { type: String, default: () => new Date().toISOString() },
    attemptId: Number
});

const currentQuestionIndex = ref(0);
const answers = ref({});

// Timer persistence logic
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

onMounted(() => {
    timerInterval = setInterval(() => {
        timeLeft.value = calculateTimeLeft();
        if (timeLeft.value <= 0) {
            clearInterval(timerInterval);
            autoSubmit();
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
        performSubmit();
    }
};

const autoSubmit = () => {
    performSubmit();
};

const performSubmit = () => {
    router.post('/webapp/quiz/' + props.quiz.id + '/submit', {
        answers: answers.value,
        attempt_id: props.attemptId
    });
};
</script>

<template>
    <Head :title="quiz.title" />

    <div class="min-h-screen bg-[#F0F2F9] flex flex-col font-outfit select-none">
        <!-- Premium Header Area -->
        <div class="bg-indigo-600 pt-10 pb-20 px-6 rounded-b-[40px] shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -mr-16 -mt-16 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-32 h-32 bg-indigo-400/20 rounded-full -ml-8 -mb-8 blur-2xl"></div>
            
            <div class="relative z-10 flex justify-between items-center mb-6">
                <button @click="router.visit('/webapp')" class="w-10 h-10 bg-white/20 backdrop-blur-md rounded-xl flex items-center justify-center border border-white/30 text-white">
                    <i class="fas fa-times"></i>
                </button>
                <div class="flex items-center space-x-2 px-4 py-2 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20">
                    <i class="far fa-clock text-indigo-200" :class="timeLeft < 60 ? 'animate-ping text-red-400' : 'animate-pulse'"></i>
                    <span class="text-white font-black font-mono text-xl">{{ formatTime(timeLeft) }}</span>
                </div>
            </div>

            <div class="relative z-10 text-center" v-if="questions && questions.length > 0">
                <h1 class="text-white text-lg font-bold opacity-80 mb-1">{{ quiz.title }}</h1>
                <div class="flex items-center justify-center space-x-1">
                    <div v-for="i in questions.length" :key="i" 
                         class="h-1 rounded-full transition-all duration-300"
                         :class="[
                            questions[i-1] && (i-1 === currentQuestionIndex) ? 'w-6 bg-white' : 
                            questions[i-1] && answers[questions[i-1].id] ? 'w-2 bg-emerald-400' : 'w-2 bg-white/30'
                         ]">
                    </div>
                </div>
            </div>
            <div class="relative z-10 text-center" v-else>
                <h1 class="text-white text-lg font-bold opacity-80 mb-1">{{ quiz.title }}</h1>
                <p class="text-white/60 text-xs">Savollar yuklanmoqda...</p>
            </div>
        </div>

        <!-- Main Content -->
        <div class="px-6 -mt-12 relative z-20 flex-1 flex flex-col pb-10">
            <!-- Loading/Empty State -->
            <div v-if="!questions || questions.length === 0" class="bg-white rounded-[32px] shadow-2xl p-10 text-center">
                <i class="fas fa-exclamation-circle text-indigo-200 text-6xl mb-6"></i>
                <h2 class="text-xl font-bold text-slate-800 mb-2">Savollar topilmadi</h2>
                <p class="text-slate-500 mb-8">Ushbu testda hozircha savollar yo'q yoki texnik xatolik yuz berdi.</p>
                <button @click="router.visit('/webapp')" class="w-full h-14 bg-indigo-600 text-white rounded-2xl font-bold">Orqaga qaytish</button>
            </div>

            <!-- Question Content -->
            <div v-else-if="currentQuestion" class="bg-white rounded-[32px] shadow-2xl shadow-indigo-200/50 p-6 sm:p-8 flex-1 flex flex-col border border-white">
                 <div class="flex items-center space-x-2 mb-6 text-indigo-600">
                    <div class="w-8 h-8 bg-indigo-50 rounded-lg flex items-center justify-center font-black text-xs">
                        {{ currentQuestionIndex + 1 }}
                    </div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">Savol mazmuni</span>
                </div>

                <div class="text-xl sm:text-2xl font-black text-slate-800 leading-[1.4] mb-10 min-h-[100px]">
                    {{ currentQuestion.content }}
                </div>

                <!-- Custom Radio Options -->
                <div class="space-y-3 mb-10 overflow-y-auto max-h-[40vh] pr-2 custom-scrollbar">
                    <div v-for="(option, index) in currentQuestion.options" :key="option.id"
                         @click="selectOption(option.id)"
                         class="group relative flex items-center p-4 rounded-2xl border-2 transition-all duration-300 cursor-pointer"
                         :class="answers[currentQuestion.id] === option.id ? 'border-indigo-600 bg-indigo-50/50' : 'border-slate-50 hover:border-indigo-100 hover:bg-slate-50/50'">
                        
                        <div class="w-9 h-9 rounded-xl border flex items-center justify-center text-sm font-black transition-all duration-300 mr-4"
                             :class="answers[currentQuestion.id] === option.id ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-slate-50 border-slate-100 text-slate-400 group-hover:bg-white group-hover:border-indigo-200'">
                            {{ String.fromCharCode(65 + index) }}
                        </div>
                        
                        <div class="flex-1 font-bold text-slate-700 group-hover:text-indigo-900 transition-colors pr-8">
                            {{ option.content }}
                        </div>

                        <div v-if="answers[currentQuestion.id] === option.id" class="absolute right-4 w-5 h-5 bg-indigo-600 rounded-full flex items-center justify-center shadow-lg shadow-indigo-200 animate-in zoom-in duration-300">
                            <i class="fas fa-check text-[10px] text-white"></i>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-auto pt-6 flex space-x-4">
                    <button @click="prevQuestion" 
                            :disabled="currentQuestionIndex === 0"
                            class="w-14 h-14 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-all border border-slate-100 disabled:opacity-20 active:scale-90">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    
                    <button v-if="currentQuestionIndex < questions.length - 1" 
                            @click="nextQuestion"
                            class="flex-1 h-14 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black shadow-xl shadow-indigo-200 flex items-center justify-center space-x-3 transition-all active:scale-95">
                        <span>Keyingi savol</span>
                        <i class="fas fa-arrow-right text-xs"></i>
                    </button>
                    
                    <button v-else 
                            @click="submitQuiz"
                            class="flex-1 h-14 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black shadow-xl shadow-emerald-200 flex items-center justify-center space-x-3 transition-all active:scale-95">
                        <span>Testni yakunlash</span>
                        <i class="fas fa-check-double text-xs"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #E2E8F0;
    border-radius: 10px;
}
</style>
