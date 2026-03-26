<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    sales:        { type: Object, default: () => ({ data: [], links: [], meta: {} }) },
    summary:      { type: Object, default: () => ({}) },
    payBreakdown: { type: Array,  default: () => [] },
    stockStats:   { type: Object, default: () => ({ total_quantity:0, total_cost_value:0, total_sale_value:0, potential_profit:0 }) },
    filters:      { type: Object, default: () => ({}) },
});

const from    = ref(props.filters.from    || '');
const to      = ref(props.filters.to      || '');
const payment = ref(props.filters.payment || '');
const expanded = ref(null);

const fmt = (n) => Number(n || 0).toLocaleString('uz-UZ');
const payLabel = { cash: 'Naqd', card: 'Karta', click: 'Click', payme: 'Payme' };
const payColors = { cash:'rgba(99,102,241,0.15)', card:'rgba(139,92,246,0.15)', click:'rgba(20,184,166,0.15)', payme:'rgba(245,158,11,0.15)' };
const payTextColors = { cash:'#a5b4fc', card:'#c4b5fd', click:'#5eead4', payme:'#fcd34d' };

const applyFilter = () => {
    router.get('/bookstore/reports', { from: from.value, to: to.value, payment: payment.value }, { preserveState: true, replace: true });
};

const exportCsv = () => {
    const params = new URLSearchParams({ from: from.value, to: to.value, payment: payment.value });
    window.location.href = `/bookstore/reports/export?${params}`;
};
</script>

<template>
    <Head title="Hisobotlar" />
    <BookstoreLayout>
        <template #header>
            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.5)" stroke-width="2" style="margin-right:6px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Hisobotlar
        </template>

        <!-- Filter Bar -->
        <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:16px 20px;margin-bottom:18px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Dan</label>
                <input type="date" v-model="from" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:9px;padding:8px 12px;color:#fff;font-size:13px;outline:none;" />
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Gacha</label>
                <input type="date" v-model="to" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:9px;padding:8px 12px;color:#fff;font-size:13px;outline:none;" />
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">To'lov turi</label>
                <select v-model="payment" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:9px;padding:8px 12px;color:#fff;font-size:13px;outline:none;">
                    <option value="">Hammasi</option>
                    <option value="cash">Naqd</option>
                    <option value="card">Karta</option>
                    <option value="click">Click</option>
                    <option value="payme">Payme</option>
                </select>
            </div>
            <button @click="applyFilter" style="margin-top:18px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;border-radius:9px;padding:9px 20px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;">
                Filtrlash
            </button>
            <button @click="exportCsv" style="margin-top:18px;background:rgba(20,184,166,0.12);border:1px solid rgba(20,184,166,0.2);border-radius:9px;padding:9px 20px;color:#5eead4;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                CSV Yuklab olish
            </button>
        </div>

        <!-- Warehouse Valuation Section (Ichma ich) -->
        <div style="background:linear-gradient(135deg,#1e1e3f,#0d0d1f);border:1px solid rgba(255,255,255,0.07);border-radius:22px;padding:24px;margin-bottom:24px;display:grid;grid-template-columns:1.5fr 1fr 1fr 1fr;gap:20px;align-items:center;">
            <div>
                <div style="color:rgba(255,255,255,0.4);font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:8px;">📦 Ombor Holati (Hozirgi vaqtda)</div>
                <div style="color:#fff;font-size:24px;font-weight:900;">Jami kitoblar: {{ fmt(stockStats.total_quantity) }} <span style="font-weight:400;font-size:14px;color:rgba(255,255,255,0.3);">ta</span></div>
            </div>
            <div style="background:rgba(255,255,255,0.03);padding:14px 18px;border-radius:14px;border:1px solid rgba(255,255,255,0.05);">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Xarid qiymati</div>
                <div style="color:#fff;font-size:17px;font-weight:800;">{{ fmt(stockStats.total_cost_value) }}</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">so'm (investitsiya)</div>
            </div>
            <div style="background:rgba(255,255,255,0.03);padding:14px 18px;border-radius:14px;border:1px solid rgba(255,255,255,0.05);">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Sotuv qiymati</div>
                <div style="color:#86efac;font-size:17px;font-weight:800;">{{ fmt(stockStats.total_sale_value) }}</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">so'm (kutilayotgan)</div>
            </div>
            <div style="background:rgba(99,102,241,0.08);padding:14px 18px;border-radius:14px;border:1px solid rgba(99,102,241,0.2);">
                <div style="color:rgba(165,180,252,0.6);font-size:10px;font-weight:700;text-transform:uppercase;margin-bottom:4px;">Kutilayotgan foyda</div>
                <div style="color:#a5b4fc;font-size:17px;font-weight:800;">{{ fmt(stockStats.potential_profit) }}</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">so'm (sof)</div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;">
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:18px 20px;">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Jami daromad</div>
                <div style="color:#fff;font-size:20px;font-weight:800;">{{ fmt(summary.total_revenue) }}</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">so'm</div>
            </div>
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:18px 20px;">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Sotuvlar soni</div>
                <div style="color:#fff;font-size:20px;font-weight:800;">{{ summary.total_count }}</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">ta tranzaksiya</div>
            </div>
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:18px 20px;">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">O'rtacha chek</div>
                <div style="color:#fff;font-size:20px;font-weight:800;">{{ fmt(summary.avg_sale) }}</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">so'm</div>
            </div>
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:18px 20px;">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Jami chegirma</div>
                <div style="color:#fff;font-size:20px;font-weight:800;">{{ fmt(summary.total_discount) }}</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">so'm</div>
            </div>
        </div>

        <!-- Pay breakdown pills -->
        <div v-if="payBreakdown.length" style="display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap;">
            <div v-for="p in payBreakdown" :key="p.payment_method"
                :style="`background:${payColors[p.payment_method]||'rgba(255,255,255,0.05)'};border:1px solid rgba(255,255,255,0.08);border-radius:10px;padding:10px 16px;display:flex;align-items:center;gap:10px;`">
                <div :style="`color:${payTextColors[p.payment_method]||'#fff'};font-weight:700;font-size:13px;`">{{ payLabel[p.payment_method] || p.payment_method }}</div>
                <div style="width:1px;height:16px;background:rgba(255,255,255,0.1);"></div>
                <div style="color:#fff;font-size:13px;font-weight:700;">{{ fmt(p.total) }} <span style="color:rgba(255,255,255,0.3);font-weight:400;font-size:11px;">so'm</span></div>
                <div style="color:rgba(255,255,255,0.3);font-size:11px;">· {{ p.count }} ta</div>
            </div>
        </div>

        <!-- Sales Table -->
        <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.02);border-bottom:1px solid rgba(255,255,255,0.05);">
                        <th style="padding:11px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Chek</th>
                        <th style="padding:11px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Sana</th>
                        <th style="padding:11px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Xodim</th>
                        <th style="padding:11px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Summa</th>
                        <th style="padding:11px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">To'lov</th>
                        <th style="padding:11px 22px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <template v-for="sale in sales.data" :key="sale.id">
                        <tr style="border-top:1px solid rgba(255,255,255,0.04);cursor:pointer;" @click="expanded = expanded === sale.id ? null : sale.id">
                            <td style="padding:13px 22px;color:rgba(255,255,255,0.3);font-size:12px;">#{{ sale.id }}</td>
                            <td style="padding:13px 22px;color:rgba(255,255,255,0.6);font-size:12px;">{{ sale.created_at }}</td>
                            <td style="padding:13px 22px;color:#fff;font-size:13px;">{{ sale.user?.name ?? '—' }}</td>
                            <td style="padding:13px 22px;color:#fff;font-size:13px;font-weight:700;">{{ fmt(sale.total_amount) }} <span style="color:rgba(255,255,255,0.25);font-weight:400;font-size:11px;">so'm</span></td>
                            <td style="padding:13px 22px;">
                                <span :style="`padding:2px 9px;border-radius:5px;font-size:10px;font-weight:700;background:${payColors[sale.payment_method]||'rgba(255,255,255,0.05)'};color:${payTextColors[sale.payment_method]||'#fff'};text-transform:uppercase;letter-spacing:0.05em;`">
                                    {{ payLabel[sale.payment_method] || sale.payment_method }}
                                </span>
                            </td>
                            <td style="padding:13px 22px;text-align:right;">
                                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.25)" stroke-width="2" :style="expanded===sale.id ? 'transform:rotate(180deg);' : ''">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </td>
                        </tr>
                        <tr v-if="expanded === sale.id" style="background:rgba(99,102,241,0.03);">
                            <td colspan="6" style="padding:14px 22px 14px 40px;">
                                <div v-for="item in sale.items" :key="item.title" style="display:flex;justify-content:space-between;padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.03);">
                                    <span style="color:rgba(255,255,255,0.65);font-size:12px;">{{ item.title }}</span>
                                    <span style="color:rgba(255,255,255,0.4);font-size:11px;">{{ item.quantity }} × {{ fmt(item.price) }} = <b style="color:#fff;">{{ fmt(item.total) }}</b></span>
                                </div>
                                <div v-if="sale.discount" style="margin-top:6px;display:flex;justify-content:space-between;color:rgba(255,255,255,0.3);font-size:11px;">
                                    <span>Chegirma</span><span>−{{ fmt(sale.discount) }} so'm</span>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <tr v-if="!sales.data?.length">
                        <td colspan="6" style="padding:60px;text-align:center;color:rgba(255,255,255,0.15);font-size:13px;">Hech narsa topilmadi</td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="sales.links?.length > 3" style="padding:14px 22px;display:flex;gap:6px;border-top:1px solid rgba(255,255,255,0.05);">
                <template v-for="link in sales.links" :key="link.label">
                    <button v-if="link.url" @click="router.get(link.url)"
                        :style="`padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid ${link.active ? 'rgba(99,102,241,0.5)' : 'rgba(255,255,255,0.06)'};background:${link.active ? 'rgba(99,102,241,0.15)' : 'transparent'};color:${link.active ? '#a5b4fc' : 'rgba(255,255,255,0.35)'};`"
                        v-html="link.label">
                    </button>
                </template>
            </div>
        </div>
    </BookstoreLayout>
</template>
