<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    todaySales:  { type: Number, default: 0 },
    recentSales: { type: Array,  default: () => [] },
    totalBooks:  { type: Number, default: 0 },
    todayCount:  { type: Number, default: 0 },
});

const payLabel = { cash: 'Naqd', card: 'Karta', click: 'Click', payme: 'Payme' };
const fmtTime  = (d) => new Date(d).toLocaleTimeString('uz-UZ', { hour: '2-digit', minute: '2-digit' });
</script>

<template>
    <Head title="Dashboard" />
    <BookstoreLayout>
        <template #header>
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.5)" stroke-width="2" style="margin-right:6px;">
                <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            Dashboard
        </template>

        <!-- Stats -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 28px;">

            <!-- Today Revenue -->
            <div style="background:#0d0d1f; border:1px solid rgba(255,255,255,0.07); border-radius:20px; padding:22px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(99,102,241,0.12);border:1px solid rgba(99,102,241,0.2);display:flex;align-items:center;justify-content:center;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#a5b4fc" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span style="color:#86efac;font-size:11px;font-weight:700;background:rgba(34,197,94,0.1);padding:3px 8px;border-radius:6px;">Bugun</span>
                </div>
                <div style="color:rgba(255,255,255,0.4);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Sotuv summasi</div>
                <div style="color:#fff;font-size:24px;font-weight:800;letter-spacing:-0.5px;">{{ todaySales.toLocaleString() }}</div>
                <div style="color:rgba(255,255,255,0.25);font-size:11px;margin-top:2px;">so'm</div>
            </div>

            <!-- Today Count -->
            <div style="background:#0d0d1f; border:1px solid rgba(255,255,255,0.07); border-radius:20px; padding:22px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(139,92,246,0.12);border:1px solid rgba(139,92,246,0.2);display:flex;align-items:center;justify-content:center;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#c4b5fd" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                </div>
                <div style="color:rgba(255,255,255,0.4);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Tranzaksiyalar</div>
                <div style="color:#fff;font-size:24px;font-weight:800;letter-spacing:-0.5px;">{{ todayCount }}</div>
                <div style="color:rgba(255,255,255,0.25);font-size:11px;margin-top:2px;">ta bugun</div>
            </div>

            <!-- Total Books -->
            <div style="background:#0d0d1f; border:1px solid rgba(255,255,255,0.07); border-radius:20px; padding:22px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(20,184,166,0.12);border:1px solid rgba(20,184,166,0.2);display:flex;align-items:center;justify-content:center;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#5eead4" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>
                <div style="color:rgba(255,255,255,0.4);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Jami kitoblar</div>
                <div style="color:#fff;font-size:24px;font-weight:800;letter-spacing:-0.5px;">{{ totalBooks }}</div>
                <div style="color:rgba(255,255,255,0.25);font-size:11px;margin-top:2px;">xil kitob</div>
            </div>

            <!-- Quick link -->
            <a href="/bookstore/pos" style="
                background: linear-gradient(135deg, #6366f1, #8b5cf6);
                border-radius:20px; padding:22px 24px; text-decoration:none;
                display:flex; flex-direction:column; justify-content:space-between;
                box-shadow: 0 8px 32px rgba(99,102,241,0.3);
                transition: transform 0.15s;
            ">
                <div style="width:36px;height:36px;border-radius:10px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;margin-bottom:auto;">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <div style="margin-top:28px;">
                    <div style="color:rgba(255,255,255,0.7);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:4px;">Sotuv paneli</div>
                    <div style="color:#fff;font-size:15px;font-weight:800;">POS ochish →</div>
                </div>
            </a>
        </div>

        <!-- Recent Sales Table -->
        <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:20px;overflow:hidden;">
            <div style="padding:20px 24px; display:flex;align-items:center;justify-content:space-between; border-bottom:1px solid rgba(255,255,255,0.05);">
                <h3 style="color:#fff;font-weight:700;font-size:14px;margin:0;">Oxirgi sotuvlar</h3>
                <a href="/bookstore/pos" style="color:#a5b4fc;font-size:12px;font-weight:600;text-decoration:none;background:rgba(99,102,241,0.1);padding:6px 14px;border-radius:8px;">
                    + Yangi sotuv
                </a>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.02);">
                        <th style="padding:12px 24px;text-align:left;color:rgba(255,255,255,0.25);font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">ID</th>
                        <th style="padding:12px 24px;text-align:left;color:rgba(255,255,255,0.25);font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">Xodim</th>
                        <th style="padding:12px 24px;text-align:left;color:rgba(255,255,255,0.25);font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">Summa</th>
                        <th style="padding:12px 24px;text-align:left;color:rgba(255,255,255,0.25);font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">To'lov</th>
                        <th style="padding:12px 24px;text-align:left;color:rgba(255,255,255,0.25);font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">Vaqt</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sale in recentSales" :key="sale.id" style="border-top:1px solid rgba(255,255,255,0.04);">
                        <td style="padding:14px 24px;color:rgba(255,255,255,0.35);font-size:12px;font-weight:500;">#{{ sale.id }}</td>
                        <td style="padding:14px 24px;color:#fff;font-size:13px;font-weight:500;">{{ sale.user?.name }}</td>
                        <td style="padding:14px 24px;color:#fff;font-size:13px;font-weight:700;">{{ Number(sale.total_amount).toLocaleString() }} <span style="color:rgba(255,255,255,0.25);font-weight:400;font-size:11px;">so'm</span></td>
                        <td style="padding:14px 24px;">
                            <span style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:700;background:rgba(99,102,241,0.12);color:#a5b4fc;text-transform:uppercase;letter-spacing:0.05em;">
                                {{ payLabel[sale.payment_method] || sale.payment_method }}
                            </span>
                        </td>
                        <td style="padding:14px 24px;color:rgba(255,255,255,0.35);font-size:12px;">{{ fmtTime(sale.created_at) }}</td>
                    </tr>
                    <tr v-if="!recentSales.length">
                        <td colspan="5" style="padding:60px 24px;text-align:center;">
                            <svg width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.1)" stroke-width="1.5" style="display:block;margin:0 auto 12px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <div style="color:rgba(255,255,255,0.2);font-size:13px;">Bugun sotuvlar yo'q</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </BookstoreLayout>
</template>
