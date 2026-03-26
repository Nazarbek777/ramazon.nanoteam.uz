<script setup>
import { useForm, Head } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '' });
const submit = () => form.post('/bookstore/login', { onFinish: () => form.reset('password') });
</script>

<template>
    <Head title="Kitob Do'koni — Kirish" />

    <div style="
        min-height: 100vh; display: flex; align-items: center; justify-content: center;
        background: #080812; font-family: 'Inter', 'Outfit', sans-serif;
        position: relative; overflow: hidden;
    ">
        <!-- Ambient glows -->
        <div style="position:fixed;top:-200px;left:-200px;width:600px;height:600px;background:radial-gradient(circle,rgba(99,102,241,0.12) 0%,transparent 70%);pointer-events:none;"></div>
        <div style="position:fixed;bottom:-150px;right:-150px;width:500px;height:500px;background:radial-gradient(circle,rgba(139,92,246,0.08) 0%,transparent 70%);pointer-events:none;"></div>

        <div style="width: 100%; max-width: 380px; padding: 16px; position: relative;">

            <!-- Card -->
            <div style="
                background: #0d0d1f;
                border: 1px solid rgba(255,255,255,0.07);
                border-radius: 24px;
                padding: 40px 36px;
                box-shadow: 0 40px 80px rgba(0,0,0,0.6);
            ">
                <!-- Icon -->
                <div style="display: flex; flex-direction: column; align-items: center; margin-bottom: 36px;">
                    <div style="
                        width: 52px; height: 52px; border-radius: 16px; margin-bottom: 18px;
                        background: linear-gradient(135deg, #6366f1, #8b5cf6);
                        display: flex; align-items: center; justify-content: center;
                        box-shadow: 0 8px 24px rgba(99,102,241,0.4);
                    ">
                        <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h1 style="color: #fff; font-size: 20px; font-weight: 800; letter-spacing: -0.5px; margin: 0 0 4px;">Kitob Do'koni</h1>
                    <p style="color: rgba(255,255,255,0.3); font-size: 13px; margin: 0;">Xodimlar paneli</p>
                </div>

                <!-- Error -->
                <div v-if="form.errors.email" style="
                    background: rgba(239,68,68,0.08);
                    border: 1px solid rgba(239,68,68,0.25);
                    border-radius: 12px; padding: 12px 14px;
                    color: #fca5a5; font-size: 13px; font-weight: 500;
                    margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
                ">
                    <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="flex-shrink:0;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    {{ form.errors.email }}
                </div>

                <form @submit.prevent="submit">
                    <!-- Email -->
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; color: rgba(255,255,255,0.35); font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px;">
                            E-pochta
                        </label>
                        <div style="position: relative;">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.25)" stroke-width="2"
                                style="position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <input v-model="form.email" type="email" required placeholder="admin@bookstore.uz"
                                style="
                                    width: 100%; padding: 12px 14px 12px 40px; box-sizing: border-box;
                                    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
                                    border-radius: 12px; color: #fff; font-size: 14px; outline: none;
                                    transition: border-color 0.2s;
                                " />
                        </div>
                    </div>

                    <!-- Password -->
                    <div style="margin-bottom: 24px;">
                        <label style="display: block; color: rgba(255,255,255,0.35); font-size: 11px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 8px;">
                            Parol
                        </label>
                        <div style="position: relative;">
                            <svg width="15" height="15" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.25)" stroke-width="2"
                                style="position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <input v-model="form.password" type="password" required placeholder="••••••••"
                                style="
                                    width: 100%; padding: 12px 14px 12px 40px; box-sizing: border-box;
                                    background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08);
                                    border-radius: 12px; color: #fff; font-size: 14px; outline: none;
                                " />
                        </div>
                    </div>

                    <button type="submit" :disabled="form.processing" style="
                        width: 100%; padding: 13px; border: none; border-radius: 12px; cursor: pointer;
                        background: linear-gradient(135deg, #6366f1, #8b5cf6);
                        color: #fff; font-size: 14px; font-weight: 700;
                        box-shadow: 0 6px 20px rgba(99,102,241,0.4);
                        transition: opacity 0.2s, transform 0.1s;
                        display: flex; align-items: center; justify-content: center; gap: 8px;
                        opacity: 1;
                    " :style="form.processing ? 'opacity:0.5;' : ''">
                        <svg v-if="form.processing" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" style="animation: spin 1s linear infinite;">
                            <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                        </svg>
                        {{ form.processing ? 'Kirilmoqda...' : 'Kirish' }}
                    </button>
                </form>
            </div>

            <p style="text-align: center; margin-top: 20px; color: rgba(255,255,255,0.15); font-size: 11px;">
                &copy; {{ new Date().getFullYear() }} Kitob Do'koni Panel
            </p>
        </div>
    </div>
</template>

<style>
@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
</style>
