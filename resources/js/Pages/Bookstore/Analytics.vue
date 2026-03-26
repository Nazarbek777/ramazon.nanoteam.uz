<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    topBooks:    { type: Array,  default: () => [] },
    lowStock:    { type: Array,  default: () => [] },
    monthlyData: { type: Array,  default: () => [] },
    neverSold:   { type: Number, default: 0 },
    totalBooks:  { type: Number, default: 0 },
});

const barChartRef    = ref(null);
const monthChartRef  = ref(null);
const fmt = (n) => Number(n || 0).toLocaleString('uz-UZ');

onMounted(async () => {
    const { Chart, registerables } = await import('chart.js');
    Chart.register(...registerables);

    // Bar chart — Top 10 books by sold count
    if (barChartRef.value) {
        const top10 = props.topBooks.slice(0, 10);
        new Chart(barChartRef.value, {
            type: 'bar',
            data: {
                labels: top10.map(b => b.title.length > 20 ? b.title.slice(0, 20) + '…' : b.title),
                datasets: [{
                    label: 'Sotilgan',
                    data: top10.map(b => b.total_sold),
                    backgroundColor: 'rgba(99,102,241,0.6)',
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(99,102,241,0.9)',
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, indexAxis: 'y',
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + c.raw + ' dona' } } },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 11 } } },
                    y: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.6)', font: { size: 12 } } },
                },
            },
        });
    }

    // Monthly bar chart
    if (monthChartRef.value) {
        new Chart(monthChartRef.value, {
            type: 'bar',
            data: {
                labels: props.monthlyData.map(m => m.month),
                datasets: [{
                    label: 'Daromad',
                    data: props.monthlyData.map(m => m.total),
                    backgroundColor: 'rgba(139,92,246,0.5)',
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(139,92,246,0.85)',
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => ' ' + Number(c.raw).toLocaleString() + ' so\'m' } } },
                scales: {
                    x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.4)', font: { size: 11 } } },
                    y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 10 }, callback: v => v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? Math.round(v/1000) + 'k' : v } },
                },
            },
        });
    }
});
</script>

<template>
    <Head title="Analitika" />
    <BookstoreLayout>
        <template #header>
            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.5)" stroke-width="2" style="margin-right:6px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Analitika
        </template>

        <!-- Quick stats -->
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px;">
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:18px 22px;">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Jami kitob turlari</div>
                <div style="color:#fff;font-size:24px;font-weight:800;">{{ totalBooks }}</div>
            </div>
            <div style="background:rgba(245,158,11,0.07);border:1px solid rgba(245,158,11,0.15);border-radius:16px;padding:18px 22px;">
                <div style="color:rgba(245,158,11,0.7);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Kam zaxira (≤10 dona)</div>
                <div style="color:#fcd34d;font-size:24px;font-weight:800;">{{ lowStock.length }}</div>
            </div>
            <div style="background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.15);border-radius:16px;padding:18px 22px;">
                <div style="color:rgba(239,68,68,0.7);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Hech sotilmagan kitoblar</div>
                <div style="color:#fca5a5;font-size:24px;font-weight:800;">{{ neverSold }}</div>
            </div>
        </div>

        <!-- Charts Row -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:22px;">
            <!-- Top 10 books bar -->
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:22px 24px;">
                <h3 style="color:#fff;font-weight:700;font-size:13px;margin:0 0 18px;">TOP 10 — eng ko'p sotiladigan kitoblar</h3>
                <div style="height:280px;"><canvas ref="barChartRef"></canvas></div>
            </div>
            <!-- Monthly chart -->
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:22px 24px;">
                <h3 style="color:#fff;font-weight:700;font-size:13px;margin:0 0 18px;">{{ new Date().getFullYear() }} — oylik daromad</h3>
                <div style="height:280px;"><canvas ref="monthChartRef"></canvas></div>
            </div>
        </div>

        <!-- Top books table -->
        <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;overflow:hidden;margin-bottom:22px;">
            <div style="padding:18px 24px;border-bottom:1px solid rgba(255,255,255,0.05);">
                <h3 style="color:#fff;font-weight:700;font-size:13px;margin:0;">Eng ko'p sotiladigan kitoblar (TOP 20)</h3>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.02);">
                        <th style="padding:10px 24px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">#</th>
                        <th style="padding:10px 24px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Kitob</th>
                        <th style="padding:10px 24px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Sotilgan</th>
                        <th style="padding:10px 24px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Daromad</th>
                        <th style="padding:10px 24px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Zaxira</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(book, i) in topBooks" :key="book.id" style="border-top:1px solid rgba(255,255,255,0.04);">
                        <td style="padding:12px 24px;">
                            <span :style="`width:24px;height:24px;border-radius:6px;display:inline-flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;${i<3?'background:rgba(99,102,241,0.2);color:#a5b4fc;':'color:rgba(255,255,255,0.25);'}`">{{ i+1 }}</span>
                        </td>
                        <td style="padding:12px 24px;">
                            <div style="color:#fff;font-size:13px;font-weight:500;">{{ book.title }}</div>
                            <div style="color:rgba(255,255,255,0.3);font-size:11px;margin-top:1px;">{{ book.author }}</div>
                        </td>
                        <td style="padding:12px 24px;color:#fff;font-size:13px;font-weight:700;">{{ book.total_sold }} <span style="color:rgba(255,255,255,0.3);font-weight:400;font-size:11px;">dona</span></td>
                        <td style="padding:12px 24px;color:#fff;font-size:13px;font-weight:700;">{{ fmt(book.total_revenue) }} <span style="color:rgba(255,255,255,0.3);font-weight:400;font-size:11px;">so'm</span></td>
                        <td style="padding:12px 24px;">
                            <span :style="`padding:2px 9px;border-radius:5px;font-size:11px;font-weight:700;${book.stock<=0?'background:rgba(239,68,68,0.12);color:#fca5a5;':book.stock<=5?'background:rgba(245,158,11,0.12);color:#fcd34d;':'background:rgba(34,197,94,0.1);color:#86efac;'}`">
                                {{ book.stock }} ta
                            </span>
                        </td>
                    </tr>
                    <tr v-if="!topBooks.length">
                        <td colspan="5" style="padding:50px;text-align:center;color:rgba(255,255,255,0.15);font-size:13px;">Hali sotuv amalga oshmagan</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Low stock warnings -->
        <div v-if="lowStock.length" style="background:#0d0d1f;border:1px solid rgba(245,158,11,0.15);border-radius:18px;overflow:hidden;">
            <div style="padding:18px 24px;border-bottom:1px solid rgba(245,158,11,0.1);display:flex;align-items:center;gap:10px;">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#fcd34d" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <h3 style="color:#fcd34d;font-weight:700;font-size:13px;margin:0;">Zaxira kam yoki tugagan kitoblar</h3>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <tbody>
                    <tr v-for="book in lowStock" :key="book.id" style="border-top:1px solid rgba(255,255,255,0.04);">
                        <td style="padding:12px 24px;">
                            <div style="color:#fff;font-size:13px;">{{ book.title }}</div>
                            <div style="color:rgba(255,255,255,0.3);font-size:11px;">{{ book.barcode }}</div>
                        </td>
                        <td style="padding:12px 24px;color:rgba(255,255,255,0.4);font-size:12px;">{{ book.author }}</td>
                        <td style="padding:12px 24px;font-weight:700;font-size:13px;color:#fff;">{{ fmt(book.price) }} so'm</td>
                        <td style="padding:12px 24px;text-align:right;">
                            <span :style="`padding:3px 10px;border-radius:6px;font-size:11px;font-weight:800;${book.stock===0?'background:rgba(239,68,68,0.15);color:#fca5a5;':'background:rgba(245,158,11,0.12);color:#fcd34d;'}`">
                                {{ book.stock === 0 ? 'Tugagan' : book.stock + ' dona qoldi' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </BookstoreLayout>
</template>
