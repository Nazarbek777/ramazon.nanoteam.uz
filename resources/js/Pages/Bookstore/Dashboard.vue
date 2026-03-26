<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    todaySales: { type: Number, default: 0 },
    recentSales: { type: Array, default: () => [] },
    totalBooks: { type: Number, default: 0 },
    todayCount: { type: Number, default: 0 },
});

const paymentLabel = { cash: 'Naqd', card: 'Karta', click: 'Click', payme: 'Payme' };
</script>

<template>
    <Head title="Dashboard" />

    <BookstoreLayout>
        <template #header>Bosh sahifa</template>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-8">
            <!-- Today Sales -->
            <div class="p-6 rounded-3xl relative overflow-hidden"
                style="background: linear-gradient(135deg, rgba(233,69,96,0.15), rgba(233,69,96,0.05)); border: 1px solid rgba(233,69,96,0.2);">
                <div class="absolute -right-4 -top-4 text-6xl opacity-10">💰</div>
                <div class="text-xs font-bold uppercase tracking-widest mb-3" style="color: rgba(255,255,255,0.4);">Bugungi sotuv</div>
                <div class="text-3xl font-extrabold text-white">{{ todaySales.toLocaleString() }}</div>
                <div class="text-xs font-medium mt-1" style="color: rgba(255,255,255,0.3);">so'm</div>
            </div>

            <!-- Today Count -->
            <div class="p-6 rounded-3xl relative overflow-hidden"
                style="background: linear-gradient(135deg, rgba(83,52,131,0.3), rgba(83,52,131,0.1)); border: 1px solid rgba(83,52,131,0.3);">
                <div class="absolute -right-4 -top-4 text-6xl opacity-10">🛒</div>
                <div class="text-xs font-bold uppercase tracking-widest mb-3" style="color: rgba(255,255,255,0.4);">Bugungi savdo</div>
                <div class="text-3xl font-extrabold text-white">{{ todayCount }}</div>
                <div class="text-xs font-medium mt-1" style="color: rgba(255,255,255,0.3);">ta tranzaksiya</div>
            </div>

            <!-- Total Books -->
            <div class="p-6 rounded-3xl relative overflow-hidden"
                style="background: linear-gradient(135deg, rgba(15,52,96,0.4), rgba(15,52,96,0.15)); border: 1px solid rgba(15,52,96,0.5);">
                <div class="absolute -right-4 -top-4 text-6xl opacity-10">📚</div>
                <div class="text-xs font-bold uppercase tracking-widest mb-3" style="color: rgba(255,255,255,0.4);">Jami kitoblar</div>
                <div class="text-3xl font-extrabold text-white">{{ totalBooks }}</div>
                <div class="text-xs font-medium mt-1" style="color: rgba(255,255,255,0.3);">xill kitob</div>
            </div>

            <!-- Quick POS -->
            <a href="/bookstore/pos"
                class="p-6 rounded-3xl flex flex-col justify-between transition-all active:scale-95 cursor-pointer"
                style="background: linear-gradient(135deg, #e94560, #533483); box-shadow: 0 8px 32px rgba(233,69,96,0.3);">
                <div class="text-xl font-extrabold text-white leading-tight">POS Panelni<br>Ochish</div>
                <div class="text-3xl mt-3">→</div>
            </a>
        </div>

        <!-- Recent Sales -->
        <div class="rounded-3xl overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
            <div class="px-7 py-5 flex items-center justify-between" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                <h2 class="font-bold text-white text-base tracking-tight">Oxirgi sotuvlar</h2>
                <a href="/bookstore/pos" class="text-xs font-bold px-4 py-2 rounded-xl transition-all" style="color: #e94560; background: rgba(233,69,96,0.1);">
                    Yangi sotuv +
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr style="color: rgba(255,255,255,0.25);">
                            <th class="px-7 py-4 text-xs font-bold uppercase tracking-widest">ID</th>
                            <th class="px-7 py-4 text-xs font-bold uppercase tracking-widest">Xodim</th>
                            <th class="px-7 py-4 text-xs font-bold uppercase tracking-widest">Summa</th>
                            <th class="px-7 py-4 text-xs font-bold uppercase tracking-widest">To'lov</th>
                            <th class="px-7 py-4 text-xs font-bold uppercase tracking-widest">Vaqt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="sale in recentSales" :key="sale.id"
                            style="border-top: 1px solid rgba(255,255,255,0.04);" class="transition-colors hover:bg-white/[0.02]">
                            <td class="px-7 py-4 text-sm font-bold" style="color: rgba(255,255,255,0.5);">#{{ sale.id }}</td>
                            <td class="px-7 py-4 text-sm font-medium text-white">{{ sale.user?.name }}</td>
                            <td class="px-7 py-4 text-sm font-extrabold text-white">{{ Number(sale.total_amount).toLocaleString() }} <span style="color: rgba(255,255,255,0.3); font-weight: 400;">so'm</span></td>
                            <td class="px-7 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider"
                                    style="background: rgba(83,52,131,0.3); color: #a78bfa;">
                                    {{ paymentLabel[sale.payment_method] || sale.payment_method }}
                                </span>
                            </td>
                            <td class="px-7 py-4 text-sm" style="color: rgba(255,255,255,0.35);">
                                {{ new Date(sale.created_at).toLocaleTimeString('uz-UZ') }}
                            </td>
                        </tr>
                        <tr v-if="recentSales.length === 0">
                            <td colspan="5" class="px-7 py-16 text-center" style="color: rgba(255,255,255,0.2);">
                                <div class="text-4xl mb-3">🛒</div>
                                Bugun hali sotuvlar amalga oshirilmadi
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </BookstoreLayout>
</template>
