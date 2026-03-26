<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/bookstore/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Kitob Do'koni — Kirish" />

    <div class="min-h-screen flex items-center justify-center p-4"
        style="background: radial-gradient(ellipse at 60% 40%, #1a1a3e 0%, #0a0a1a 60%, #0f0f1a 100%); font-family: 'Outfit', 'Inter', sans-serif;">

        <!-- Glow blobs -->
        <div class="fixed pointer-events-none" style="top:-120px;left:-120px;width:400px;height:400px;background:radial-gradient(circle,rgba(233,69,96,0.15),transparent 70%);border-radius:50%;filter:blur(40px);"></div>
        <div class="fixed pointer-events-none" style="bottom:-100px;right:-100px;width:350px;height:350px;background:radial-gradient(circle,rgba(15,52,96,0.4),transparent 70%);border-radius:50%;filter:blur(40px);"></div>

        <div class="w-full max-w-sm relative">
            <!-- Card -->
            <div class="p-8 rounded-3xl relative overflow-hidden"
                style="background: rgba(255,255,255,0.04); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.08); box-shadow: 0 32px 80px rgba(0,0,0,0.5);">

                <!-- Icon + Title -->
                <div class="text-center mb-10">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-3xl text-3xl mb-5 shadow-xl"
                        style="background: linear-gradient(135deg, #e94560 0%, #533483 100%);">
                        📚
                    </div>
                    <h1 class="text-2xl font-extrabold text-white tracking-tight">Kitob Do'koni</h1>
                    <p class="mt-1.5 text-sm" style="color: rgba(255,255,255,0.4);">Xodimlar paneli — kirish</p>
                </div>

                <!-- Error -->
                <div v-if="form.errors.email" class="mb-6 px-4 py-3 rounded-2xl text-sm font-semibold"
                    style="background: rgba(233,69,96,0.12); border: 1px solid rgba(233,69,96,0.3); color: #ff6b84;">
                    ⚠️ {{ form.errors.email }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest mb-2" style="color: rgba(255,255,255,0.4);">E-pochta</label>
                        <input v-model="form.email" type="email" required
                            class="w-full px-5 py-4 rounded-2xl text-white placeholder-gray-600 transition-all focus:outline-none"
                            style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); font-size: 15px;"
                            placeholder="admin@bookstore.uz" />
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-widest mb-2" style="color: rgba(255,255,255,0.4);">Parol</label>
                        <input v-model="form.password" type="password" required
                            class="w-full px-5 py-4 rounded-2xl text-white placeholder-gray-600 transition-all focus:outline-none"
                            style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1); font-size: 15px;"
                            placeholder="••••••••" />
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="form.processing"
                        class="w-full py-4 rounded-2xl font-extrabold text-white text-base transition-all active:scale-95 disabled:opacity-50 mt-2"
                        style="background: linear-gradient(135deg, #e94560 0%, #533483 100%); box-shadow: 0 8px 30px rgba(233,69,96,0.35);">
                        <span v-if="!form.processing">Kirish →</span>
                        <span v-else class="inline-flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                            Kirilmoqda...
                        </span>
                    </button>
                </form>
            </div>

            <p class="text-center mt-6 text-xs font-medium" style="color: rgba(255,255,255,0.2);">
                &copy; {{ new Date().getFullYear() }} Kitob Do'koni Panel
            </p>
        </div>
    </div>
</template>
