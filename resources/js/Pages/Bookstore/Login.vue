<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/bookstore/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Bookstore Login" />
    
    <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-900 via-indigo-800 to-indigo-900 p-4">
        <div class="max-w-md w-full">
            <!-- Glassmorphism Card -->
            <div class="bg-white/10 backdrop-blur-xl border border-white/20 p-8 rounded-3xl shadow-2xl relative overflow-hidden text-white">
                <!-- Decorative elements -->
                <div class="absolute -top-24 -right-24 w-48 h-48 bg-indigo-500/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-24 -left-24 w-48 h-48 bg-blue-500/20 rounded-full blur-3xl"></div>

                <div class="relative z-10">
                    <div class="text-center mb-10">
                        <div class="inline-block p-4 rounded-2xl bg-white/20 mb-4 text-3xl">📚</div>
                        <h2 class="text-3xl font-extrabold tracking-tight uppercase">Kitob Do'koni</h2>
                        <p class="mt-2 text-indigo-200">Xodimlar paneli</p>
                    </div>

                    <form @submit.prevent="submit" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium mb-2 opacity-80" for="email">E-pochta</label>
                            <input
                                v-model="form.email"
                                type="email"
                                id="email"
                                class="w-full bg-white/10 border border-white/20 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-indigo-400 focus:outline-none transition-all placeholder:text-white/30"
                                placeholder="name@example.com"
                                required
                            />
                            <div v-if="form.errors.email" class="text-red-300 text-sm mt-2 font-medium">{{ form.errors.email }}</div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-2 opacity-80" for="password">Parol</label>
                            <input
                                v-model="form.password"
                                type="password"
                                id="password"
                                class="w-full bg-white/10 border border-white/20 rounded-2xl px-5 py-4 focus:ring-2 focus:ring-indigo-400 focus:outline-none transition-all placeholder:text-white/30"
                                placeholder="••••••••"
                                required
                            />
                        </div>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-white text-indigo-900 font-bold py-4 rounded-2xl hover:bg-indigo-50 transition-all shadow-xl active:scale-95 flex items-center justify-center space-x-2 text-lg disabled:opacity-50"
                        >
                            <span>Kirish</span>
                            <span v-if="form.processing" class="animate-spin">🌀</span>
                        </button>
                    </form>
                </div>
            </div>
            
            <p class="text-center mt-8 text-white/50 text-sm font-medium tracking-wide">
                &copy; {{ new Date().getFullYear() }} Ramazon Bookstore Module
            </p>
        </div>
    </div>
</template>
