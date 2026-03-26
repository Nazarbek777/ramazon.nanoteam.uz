<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    todaySales:   { type: Number, default: 0 },
    todayCount:   { type: Number, default: 0 },
    weekSales:    { type: Number, default: 0 },
    monthSales:   { type: Number, default: 0 },
    yearSales:    { type: Number, default: 0 },
    totalBooks:   { type: Number, default: 0 },
    lowStock:     { type: Number, default: 0 },
    outOfStock:   { type: Number, default: 0 },
    chartDays:    { type: Array, default: () => [] },
    chartTotals:  { type: Array, default: () => [] },
    chartCounts:  { type: Array, default: () => [] },
    paymentStats: { type: Array, default: () => [] },
    recentSales:  { type: Array, default: () => [] },
});

const expandedSale = ref(null);
const fmt = (n) => Number(n).toLocaleString('uz-UZ');
const payLabel = { cash: 'Naqd', card: 'Karta', click: 'Click', payme: 'Payme' };

const lineChartRef = ref(null);
const donutChartRef = ref(null);

onMounted(async () => {
    const { Chart, registerables } = await import('chart.js');
    Chart.register(...registerables);

    // Line Chart — 30-day revenue
    if (lineChartRef.value) {
        new Chart(lineChartRef.value, {
            type: 'line',
            data: {
                labels: props.chartDays,
                datasets: [{
                    label: 'Daromad (so\'m)',
                    data: props.chartTotals,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99,102,241,0.08)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#6366f1',
                    borderWidth: 2,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (c) => ' ' + Number(c.raw).toLocaleString() + ' so\'m',
                        },
                    },
                },
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 10 }, maxRotation: 0, autoSkip: true, maxTicksLimit: 10 } },
                    y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 10 }, callback: (v) => (v>=1000?Math.round(v/1000)+'k':v) } },
                },
            },
        });
    }

    // Donut — payment methods
    if (donutChartRef.value && props.paymentStats.length) {
        const colors = { cash:'#6366f1', card:'#8b5cf6', click:'#14b8a6', payme:'#f59e0b' };
        new Chart(donutChartRef.value, {
            type: 'doughnut',
            data: {
                labels: props.paymentStats.map(p => payLabel[p.payment_method] || p.payment_method),
                datasets: [{
                    data: props.paymentStats.map(p => p.total),
                    backgroundColor: props.paymentStats.map(p => colors[p.payment_method] || '#94a3b8'),
                    borderWidth: 0,
                    hoverOffset: 4,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '72%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: 'rgba(255,255,255,0.5)', font: { size: 11 }, padding: 16, boxWidth: 10, borderRadius: 3 } },
                    tooltip: { callbacks: { label: (c) => ' ' + Number(c.raw).toLocaleString() + ' so\'m' } },
                },
            },
        });
    }
});
</script>

<template>
    <Head title="Dashboard" />
    <BookstoreLayout>
        <template #header>
            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.5)" stroke-width="2" style="margin-right:6px;">
                <rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>
            </svg>
            Dashboard
        </template>

        <!-- ── Stat Cards ────────────────────────────────────────── -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px;">

            <!-- Today -->
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:20px 22px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div style="color:rgba(255,255,255,0.35);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Bugun</div>
                        <div style="color:#fff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">{{ fmt(todaySales) }}</div>
                        <div style="color:rgba(255,255,255,0.25);font-size:11px;margin-top:2px;">so'm &nbsp;·&nbsp; {{ todayCount }} ta sotuv</div>
                    </div>
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(99,102,241,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#a5b4fc" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Week -->
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:20px 22px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div style="color:rgba(255,255,255,0.35);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Hafta</div>
                        <div style="color:#fff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">{{ fmt(weekSales) }}</div>
                        <div style="color:rgba(255,255,255,0.25);font-size:11px;margin-top:2px;">so'm</div>
                    </div>
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(139,92,246,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#c4b5fd" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Month -->
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:20px 22px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div style="color:rgba(255,255,255,0.35);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Oy</div>
                        <div style="color:#fff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">{{ fmt(monthSales) }}</div>
                        <div style="color:rgba(255,255,255,0.25);font-size:11px;margin-top:2px;">so'm</div>
                    </div>
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(20,184,166,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#5eead4" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    </div>
                </div>
            </div>

            <!-- Year -->
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:20px 22px;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <div style="color:rgba(255,255,255,0.35);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Yil</div>
                        <div style="color:#fff;font-size:22px;font-weight:800;letter-spacing:-0.5px;">{{ fmt(yearSales) }}</div>
                        <div style="color:rgba(255,255,255,0.25);font-size:11px;margin-top:2px;">so'm</div>
                    </div>
                    <div style="width:36px;height:36px;border-radius:10px;background:rgba(245,158,11,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="#fcd34d" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts row -->
        <div v-if="lowStock || outOfStock" style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:22px;">
            <div v-if="lowStock" style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:16px;padding:14px 18px;display:flex;align-items:center;gap:12px;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fcd34d" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <div><div style="color:#fcd34d;font-weight:700;font-size:13px;">{{ lowStock }} kitob — kam zaxira</div><div style="color:rgba(255,255,255,0.4);font-size:11px;margin-top:1px;">Zaxirasi ≤ 5 dona</div></div>
            </div>
            <div v-if="outOfStock" style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:16px;padding:14px 18px;display:flex;align-items:center;gap:12px;">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fca5a5" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <div><div style="color:#fca5a5;font-weight:700;font-size:13px;">{{ outOfStock }} kitob — tugagan</div><div style="color:rgba(255,255,255,0.4);font-size:11px;margin-top:1px;">Zaxira = 0</div></div>
            </div>
        </div>

        <!-- ── Charts Row ─────────────────────────────────────────── -->
        <div style="display:grid;grid-template-columns:1fr 300px;gap:16px;margin-bottom:22px;">
            <!-- Line chart -->
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:22px 24px;">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                    <h3 style="color:#fff;font-weight:700;font-size:13px;margin:0;">So'nggi 30 kun — daromad trendi</h3>
                    <a href="/bookstore/reports" style="color:#a5b4fc;font-size:11px;font-weight:600;text-decoration:none;background:rgba(99,102,241,0.1);padding:5px 12px;border-radius:7px;">Batafsil →</a>
                </div>
                <div style="height:200px;"><canvas ref="lineChartRef"></canvas></div>
            </div>
            <!-- Donut chart -->
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:22px 24px;">
                <h3 style="color:#fff;font-weight:700;font-size:13px;margin:0 0 18px;">To'lov usullari</h3>
                <div style="height:180px;"><canvas ref="donutChartRef"></canvas></div>
            </div>
        </div>

        <!-- ── Recent Sales ───────────────────────────────────────── -->
        <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;overflow:hidden;">
            <div style="padding:18px 24px;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid rgba(255,255,255,0.05);">
                <h3 style="color:#fff;font-weight:700;font-size:13px;margin:0;">Oxirgi sotuvlar</h3>
                <a href="/bookstore/reports" style="color:#a5b4fc;font-size:11px;font-weight:600;text-decoration:none;background:rgba(99,102,241,0.1);padding:5px 12px;border-radius:7px;">Hammasini ko'rish →</a>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.02);">
                        <th style="padding:10px 24px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Chek</th>
                        <th style="padding:10px 24px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Xodim</th>
                        <th style="padding:10px 24px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Kitoblar</th>
                        <th style="padding:10px 24px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Summa</th>
                        <th style="padding:10px 24px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">To'lov</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="sale in recentSales" :key="sale.id" style="border-top:1px solid rgba(255,255,255,0.04);cursor:pointer;" @click="expandedSale = expandedSale === sale.id ? null : sale.id">
                        <td style="padding:13px 24px;color:rgba(255,255,255,0.3);font-size:12px;font-weight:500;">#{{ sale.id }}</td>
                        <td style="padding:13px 24px;color:#fff;font-size:13px;">{{ sale.user?.name ?? '—' }}</td>
                        <td style="padding:13px 24px;">
                            <span style="color:rgba(255,255,255,0.4);font-size:12px;">{{ sale.items_count }} ta</span>
                        </td>
                        <td style="padding:13px 24px;color:#fff;font-size:13px;font-weight:700;">{{ fmt(sale.total_amount) }} <span style="color:rgba(255,255,255,0.25);font-weight:400;font-size:11px;">so'm</span></td>
                        <td style="padding:13px 24px;">
                            <span style="padding:2px 9px;border-radius:5px;font-size:10px;font-weight:700;background:rgba(99,102,241,0.12);color:#a5b4fc;text-transform:uppercase;letter-spacing:0.05em;">{{ payLabel[sale.payment_method] || sale.payment_method }}</span>
                        </td>
                    </tr>
                    <!-- Expanded items -->
                    <template v-for="sale in recentSales" :key="'exp-' + sale.id">
                        <tr v-if="expandedSale === sale.id" style="background:rgba(99,102,241,0.04);">
                            <td colspan="5" style="padding:14px 24px;">
                                <div v-for="item in sale.items" :key="item.title" style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                                    <span style="color:rgba(255,255,255,0.7);font-size:12px;">{{ item.title }}</span>
                                    <span style="color:rgba(255,255,255,0.4);font-size:11px;">{{ item.quantity }} × {{ fmt(item.price) }} = <b style="color:#fff;">{{ fmt(item.quantity * item.price) }}</b></span>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr v-if="!recentSales.length">
                        <td colspan="5" style="padding:50px 24px;text-align:center;color:rgba(255,255,255,0.15);font-size:13px;">Sotuvlar yo'q</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </BookstoreLayout>
</template>
