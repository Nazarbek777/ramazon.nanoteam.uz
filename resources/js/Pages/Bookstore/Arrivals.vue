<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    arrivals:      { type: Object, default: () => ({ data: [], links: [] }) },
    books:         { type: Array,  default: () => [] },
    periodRevenue: { type: Number, default: 0 },
    periodCost:    { type: Number, default: 0 },
    plData:        { type: Array,  default: () => [] },
    filters:       { type: Object, default: () => ({}) },
});

const from      = ref(props.filters.from || '');
const to        = ref(props.filters.to   || '');
const showForm  = ref(false);
const chartRef  = ref(null);
const fmt       = (n) => Number(n || 0).toLocaleString('uz-UZ');
const profit    = computed(() => props.periodRevenue - props.periodCost);
const bookSearch = ref('');
const activeTab = ref('book'); // 'book' or 'expense'


const filteredBooks = computed(() => {
    const q = bookSearch.value.toLowerCase();
    if (!q) return props.books;
    return props.books.filter(b => b.title.toLowerCase().includes(q) || b.barcode?.includes(q));
});

const form = useForm({
    book_id:    '',
    is_new_book: false,
    title:      '',
    author:     '',
    barcode:    '',
    price:      '',
    quantity:   '',
    cost_price: '',
    supplier:   '',
    note:       '',
    arrived_at: new Date().toISOString().slice(0, 10),
});

const openModal = (tab = 'book') => {
    form.reset();
    activeTab.value = tab;
    bookSearch.value = '';
    showForm.value = true;
};

const barcodeInput = ref('');
const handleBarcode = async () => {
    const bc = barcodeInput.value.trim();
    if (!bc) return;

    // Check if book exists
    const response = await fetch(`/api/bookstore/books/${bc}`);
    if (response.ok) {
        const book = await response.json();
        selectBook(book);
    } else {
        // Switch to new book mode
        form.is_new_book = true;
        form.barcode = bc;
        form.book_id = '';
        bookSearch.value = 'Yangi kitob: ' + bc;
        // Focus on title next (we'll handle focus in template)
    }
    barcodeInput.value = '';
};

const selectBook = (book) => {
    form.book_id    = book.id;
    form.cost_price = book.cost_price || '';
    bookSearch.value = book.title;
    filteredOpen.value = false;
};
const filteredOpen = ref(false);

const applyFilter = () => {
    router.get('/bookstore/arrivals', { from: from.value, to: to.value }, { preserveState: true, replace: true });
};

const submit = () => {
    form.post('/bookstore/arrivals', {
        onSuccess: () => { 
            showForm.value = false; 
            form.reset(); 
            bookSearch.value = ''; 
            isOtherExpense.value = false; 
        },
    });
};

const deleteArrival = (id) => {
    if (!confirm('O\'chirishni tasdiqlaysizmi? Zaxira kamayadi!')) return;
    router.delete(`/bookstore/arrivals/${id}`);
};

const exportCsv = () => {
    window.location.href = `/bookstore/arrivals/export?from=${from.value}&to=${to.value}`;
};

onMounted(async () => {
    const { Chart, registerables } = await import('chart.js');
    Chart.register(...registerables);
    if (!chartRef.value) return;

    new Chart(chartRef.value, {
        type: 'bar',
        data: {
            labels: props.plData.map(m => m.month),
            datasets: [
                {
                    label: 'Daromad',
                    data: props.plData.map(m => m.revenue),
                    backgroundColor: 'rgba(99,102,241,0.6)',
                    borderRadius: 5,
                    barPercentage: 0.45,
                    categoryPercentage: 0.8,
                },
                {
                    label: 'Chiqim',
                    data: props.plData.map(m => m.cost),
                    backgroundColor: 'rgba(239,68,68,0.5)',
                    borderRadius: 5,
                    barPercentage: 0.45,
                    categoryPercentage: 0.8,
                },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { labels: { color: 'rgba(255,255,255,0.4)', font: { size: 11 }, boxWidth: 10, borderRadius: 3 } },
                tooltip: { callbacks: { label: c => ' ' + Number(c.raw).toLocaleString() + ' so\'m' } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: 'rgba(255,255,255,0.35)', font: { size: 11 } } },
                y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.3)', font: { size: 10 }, callback: v => v >= 1000000 ? (v/1000000).toFixed(1)+'M' : v >= 1000 ? Math.round(v/1000)+'k' : v } },
            },
        },
    });
});
</script>

<template>
    <Head title="Keldi / Chiqim" />
    <BookstoreLayout>
        <template #header>
            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="rgba(255,255,255,0.5)" stroke-width="2" style="margin-right:6px;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
            </svg>
            Keldi / Inventar
        </template>

        <!-- Summary Cards -->
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;">
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:18px 20px;">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Daromad</div>
                <div style="color:#a5b4fc;font-size:20px;font-weight:800;">{{ fmt(periodRevenue) }}</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">so'm</div>
            </div>
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:18px 20px;">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Chiqim (xarid)</div>
                <div style="color:#fca5a5;font-size:20px;font-weight:800;">{{ fmt(periodCost) }}</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">so'm</div>
            </div>
            <div :style="`background:#0d0d1f;border:1px solid ${profit>=0?'rgba(34,197,94,0.15)':'rgba(239,68,68,0.15)'};border-radius:16px;padding:18px 20px;`">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Sof foyda</div>
                <div :style="`font-size:20px;font-weight:800;${profit>=0?'color:#86efac;':'color:#fca5a5;'}`">{{ profit >= 0 ? '+' : '' }}{{ fmt(profit) }}</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">so'm</div>
            </div>
            <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:18px 20px;">
                <div style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;margin-bottom:6px;">Rentabellik</div>
                <div style="color:#fcd34d;font-size:20px;font-weight:800;">{{ periodCost > 0 ? Math.round(profit / periodCost * 100) : 0 }}%</div>
                <div style="color:rgba(255,255,255,0.2);font-size:11px;">marja</div>
            </div>
        </div>

        <!-- P&L Chart -->
        <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;padding:22px 24px;margin-bottom:18px;">
            <h3 style="color:#fff;font-weight:700;font-size:13px;margin:0 0 18px;">{{ new Date().getFullYear() }} — Daromad vs Chiqim (oylik)</h3>
            <div style="height:220px;"><canvas ref="chartRef"></canvas></div>
        </div>

        <!-- Filter + Add -->
        <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:16px;padding:16px 20px;margin-bottom:18px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Dan</label>
                <input type="date" v-model="from" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:9px;padding:8px 12px;color:#fff;font-size:13px;outline:none;" />
            </div>
            <div style="display:flex;flex-direction:column;gap:4px;">
                <label style="color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;">Gacha</label>
                <input type="date" v-model="to" style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.08);border-radius:9px;padding:8px 12px;color:#fff;font-size:13px;outline:none;" />
            </div>
            <button @click="applyFilter" style="margin-top:18px;background:linear-gradient(135deg,#6366f1,#8b5cf6);border:none;border-radius:9px;padding:9px 20px;color:#fff;font-size:13px;font-weight:700;cursor:pointer;">Filtrlash</button>
            <button @click="exportCsv" style="margin-top:18px;background:rgba(20,184,166,0.12);border:1px solid rgba(20,184,166,0.2);border-radius:9px;padding:9px 20px;color:#5eead4;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;">
                <svg width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                CSV
            </button>
            <button @click="openModal('book')" style="margin-top:18px;margin-left:auto;background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);border-radius:9px;padding:9px 20px;color:#86efac;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Kitob kelishi
            </button>
            <button @click="openModal('expense')" style="margin-top:18px;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);border-radius:9px;padding:9px 20px;color:#fca5a5;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                Boshqa chiqim
            </button>
        </div>

        <!-- Add Arrival Form -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);" @click.self="showForm=false">
                    <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.1);border-radius:24px;padding:32px;width:100%;max-width:500px;">
                        <!-- Tabs -->
                        <div style="display:flex;background:rgba(255,255,255,0.04);padding:4px;border-radius:12px;margin-bottom:24px;">
                            <button type="button" @click="activeTab='book'; form.book_id=''" :style="`flex:1;padding:10px;border-radius:9px;border:none;font-size:13px;font-weight:700;cursor:pointer;transition:0.3s;${activeTab==='book'?'background:#6366f1;color:#fff;':'background:transparent;color:rgba(255,255,255,0.4);'}`">📦 Kitob Kelishi</button>
                            <button type="button" @click="activeTab='expense'; form.book_id=''; form.is_new_book=false" :style="`flex:1;padding:10px;border-radius:9px;border:none;font-size:13px;font-weight:700;cursor:pointer;transition:0.3s;${activeTab==='expense'?'background:#ef4444;color:#fff;':'background:transparent;color:rgba(255,255,255,0.4);'}`">💸 Boshqa Chiqim</button>
                        </div>

                        <form @submit.prevent="submit">
                            <!-- mode: BOOK -->
                            <div v-if="activeTab==='book'">
                                <!-- Barcode / Search -->
                                <div style="margin-bottom:20px;position:relative;">
                                    <label style="display:block;color:rgba(255,255,255,0.4);font-size:11px;font-weight:700;margin-bottom:8px;">SKAYNERLANG YOKI QIDIRING</label>
                                    <input v-model="barcodeInput" @keyup.enter="handleBarcode" type="text" placeholder="Barcode skanerlang yoki kitob nomi..." autofocus
                                        style="width:100%;padding:12px 16px;box-sizing:border-box;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.3);border-radius:12px;color:#fff;font-size:14px;outline:none;" />
                                    
                                    <!-- Search results dropdown -->
                                    <div v-if="barcodeInput.length > 1" style="position:absolute;top:100%;left:0;right:0;background:#141426;border:1px solid rgba(255,255,255,0.1);border-radius:12px;overflow:hidden;z-index:20;margin-top:6px;max-height:200px;overflow-y:auto;">
                                        <div v-for="b in books.filter(x => x.title.toLowerCase().includes(barcodeInput.toLowerCase()) || x.barcode.includes(barcodeInput)).slice(0,5)" 
                                            :key="b.id" @mousedown="selectBook(b); barcodeInput=''"
                                            style="padding:12px 16px;color:#fff;font-size:13px;cursor:pointer;border-bottom:1px solid rgba(255,255,255,0.05);display:flex;justify-content:space-between;">
                                            <span>{{ b.title }}</span>
                                            <span style="color:rgba(255,255,255,0.3);">{{ b.barcode }}</span>
                                        </div>
                                        <div @mousedown="form.is_new_book=true; form.barcode=barcodeInput; barcodeInput=''" 
                                            style="padding:12px 16px;color:#a5b4fc;font-size:13px;cursor:pointer;background:rgba(99,102,241,0.1);text-align:center;font-weight:700;">
                                            ＋ Yangi kitob sifatida qo'shish: "{{ barcodeInput }}"
                                        </div>
                                    </div>
                                </div>

                                <!-- Selected Book / New Book UI -->
                                <div v-if="form.book_id || form.is_new_book" style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:16px;padding:20px;margin-bottom:20px;">
                                    <div v-if="form.book_id" style="display:flex;justify-content:space-between;margin-bottom:15px;">
                                        <div>
                                            <div style="color:rgba(255,255,255,0.4);font-size:10px;text-transform:uppercase;font-weight:800;">Tanlangan kitob</div>
                                            <div style="color:#fff;font-size:15px;font-weight:700;margin-top:2px;">{{ bookSearch }}</div>
                                        </div>
                                        <button type="button" @click="form.book_id=''; bookSearch=''" style="color:#fca5a5;font-size:11px;background:none;border:none;cursor:pointer;">O'zgartirish</button>
                                    </div>

                                    <div v-if="form.is_new_book">
                                        <div style="display:flex;justify-content:space-between;margin-bottom:15px;">
                                            <div style="color:#a5b4fc;font-size:10px;text-transform:uppercase;font-weight:800;">Yangi kitob ma'lumotlari</div>
                                            <button type="button" @click="form.is_new_book=false; form.title=''" style="color:rgba(255,255,255,0.3);font-size:11px;background:none;border:none;cursor:pointer;">Bekor qilish</button>
                                        </div>
                                        <div style="margin-bottom:12px;">
                                            <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:4px;">NOMI</label>
                                            <input v-model="form.title" type="text" required style="width:100%;padding:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;outline:none;" />
                                        </div>
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                                            <div>
                                                <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:4px;">BARCODE (ixtiyoriy)</label>
                                                <div style="display:flex;gap:4px;">
                                                    <input v-model="form.barcode" type="text" placeholder="Skanerlang..." style="flex:1;padding:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;outline:none;" />
                                                    <button type="button" @click="form.barcode = 'BK-' + Math.random().toString(36).substring(2, 11).toUpperCase()"
                                                        style="padding:0 10px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#a5b4fc;cursor:pointer;font-size:10px;font-weight:700;">
                                                        Auto
                                                    </button>
                                                </div>
                                            </div>
                                            <div>
                                                <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:4px;">SOTISH NARXI</label>
                                                <input v-model="form.price" type="number" required style="width:100%;padding:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;outline:none;" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Common inputs for book arrival -->
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                        <div>
                                            <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:4px;">MIQDOR</label>
                                            <input v-model="form.quantity" type="number" required min="1" style="width:100%;padding:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;outline:none;" />
                                        </div>
                                        <div>
                                            <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:4px;">KELISH NARXI</label>
                                            <input v-model="form.cost_price" type="number" required style="width:100%;padding:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;outline:none;" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- mode: EXPENSE -->
                            <div v-if="activeTab==='expense'">
                                <div style="margin-bottom:15px;">
                                    <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:6px;">XARAJAT NOMI (Masalan: Ijara, Elektr...)</label>
                                    <input v-model="form.note" type="text" required placeholder="Xarajat sarlavhasi..." style="width:100%;padding:12px 16px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;color:#fff;outline:none;" />
                                </div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:15px;">
                                    <div>
                                        <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:6px;">SUMMA (so'm)</label>
                                        <input v-model="form.cost_price" type="number" required style="width:100%;padding:12px 16px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;color:#fff;outline:none;" @input="form.quantity=1" />
                                    </div>
                                    <div>
                                        <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:6px;">SANA</label>
                                        <input v-model="form.arrived_at" type="date" required style="width:100%;padding:12px 16px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:12px;color:#fff;outline:none;" />
                                    </div>
                                </div>
                            </div>

                            <!-- Supplier etc -->
                            <div v-if="activeTab==='book' && (form.book_id || form.is_new_book)" style="margin-bottom:20px;">
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                                    <div>
                                        <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:4px;">YETKAZUVCHI</label>
                                        <input v-model="form.supplier" type="text" style="width:100%;padding:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;outline:none;" />
                                    </div>
                                    <div>
                                        <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:4px;">SANA</label>
                                        <input v-model="form.arrived_at" type="date" required style="width:100%;padding:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;outline:none;" />
                                    </div>
                                </div>
                                <label style="display:block;color:rgba(255,255,255,0.3);font-size:10px;font-weight:700;margin-bottom:4px;">IZOH</label>
                                <textarea v-model="form.note" rows="2" style="width:100%;padding:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:8px;color:#fff;outline:none;resize:none;"></textarea>
                            </div>

                            <div style="display:flex;gap:12px;margin-top:10px;">
                                <button type="button" @click="showForm=false" style="flex:1;padding:14px;border-radius:14px;border:1px solid rgba(255,255,255,0.1);background:rgba(255,255,255,0.04);color:rgba(255,255,255,0.5);font-weight:700;cursor:pointer;">Bekor qilish</button>
                                <button type="submit" :disabled="form.processing || (activeTab==='book' && !form.book_id && !form.is_new_book)" 
                                    :style="`flex:2;padding:14px;border-radius:14px;border:none;color:#fff;font-weight:800;cursor:pointer;background:${activeTab==='book'?'linear-gradient(135deg,#6366f1,#4f46e5)':'linear-gradient(135deg,#ef4444,#dc2626)'};opacity:${form.processing?0.6:1}`">
                                    {{ activeTab==='book' ? 'Saqlash va Zaxirani yangilash' : 'Xarajatni qayd etish' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Arrivals Table -->
        <div style="background:#0d0d1f;border:1px solid rgba(255,255,255,0.07);border-radius:18px;overflow:hidden;">
            <div style="padding:18px 24px;border-bottom:1px solid rgba(255,255,255,0.05);">
                <h3 style="color:#fff;font-weight:700;font-size:13px;margin:0;">Keldi ro'yxati</h3>
            </div>
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:rgba(255,255,255,0.02);">
                        <th style="padding:10px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Sana</th>
                        <th style="padding:10px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Kitob</th>
                        <th style="padding:10px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Miqdor</th>
                        <th style="padding:10px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Qolgan</th>
                        <th style="padding:10px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Narxi</th>
                        <th style="padding:10px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Jami</th>
                        <th style="padding:10px 22px;text-align:left;color:rgba(255,255,255,0.2);font-size:10px;font-weight:700;letter-spacing:0.07em;text-transform:uppercase;">Yetkazuvchi</th>
                        <th style="padding:10px 22px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="a in arrivals.data" :key="a.id" style="border-top:1px solid rgba(255,255,255,0.04);">
                        <td style="padding:13px 22px;color:rgba(255,255,255,0.5);font-size:12px;">{{ a.arrived_at }}</td>
                        <td style="padding:13px 22px;">
                            <template v-if="a.book">
                                <div style="color:#fff;font-size:13px;font-weight:500;">{{ a.book.title }}</div>
                                <div style="color:rgba(255,255,255,0.3);font-size:11px;font-family:monospace;">{{ a.book.barcode }}</div>
                            </template>
                            <template v-else>
                                <div style="color:rgba(255,255,255,0.5);font-size:13px;font-style:italic;">Boshqa xarajat</div>
                            </template>
                        </td>
                        <td style="padding:13px 22px;color:#fff;font-weight:700;font-size:13px;">{{ a.quantity }} <span style="color:rgba(255,255,255,0.3);font-weight:400;font-size:11px;">dona</span></td>
                        <td style="padding:13px 22px;">
                            <div v-if="a.book" :style="`font-size:13px;font-weight:700;${a.remaining_stock > 0 ? 'color:#86efac;' : 'color:rgba(255,255,255,0.2);text-decoration:line-through;'}`">
                                {{ a.remaining_stock }} <span style="font-weight:400;font-size:11px;">qoldi</span>
                            </div>
                            <div v-else style="color:rgba(255,255,255,0.2);font-size:11px;">—</div>
                        </td>
                        <td style="padding:13px 22px;color:#fff;font-size:13px;">{{ fmt(a.cost_price) }} <span style="color:rgba(255,255,255,0.3);font-size:11px;">so'm</span></td>
                        <td style="padding:13px 22px;color:#fca5a5;font-size:13px;font-weight:700;">{{ fmt(a.total_cost) }} <span style="color:rgba(239,68,68,0.4);font-weight:400;font-size:11px;">so'm</span></td>
                        <td style="padding:13px 22px;color:rgba(255,255,255,0.4);font-size:12px;">{{ a.supplier || '—' }}</td>
                        <td style="padding:13px 22px;text-align:right;">
                            <button @click="deleteArrival(a.id)" style="padding:5px 12px;border-radius:7px;border:1px solid rgba(239,68,68,0.2);background:rgba(239,68,68,0.08);color:#fca5a5;font-size:11px;font-weight:600;cursor:pointer;">O'chirish</button>
                        </td>
                    </tr>
                    <tr v-if="!arrivals.data?.length">
                        <td colspan="7" style="padding:60px;text-align:center;color:rgba(255,255,255,0.15);font-size:13px;">Bu davr uchun keldi qaydlari yo'q</td>
                    </tr>
                </tbody>
            </table>
            <!-- Pagination -->
            <div v-if="arrivals.links?.length > 3" style="padding:14px 22px;display:flex;gap:6px;border-top:1px solid rgba(255,255,255,0.05);">
                <template v-for="link in arrivals.links" :key="link.label">
                    <button v-if="link.url" @click="router.get(link.url)"
                        :style="`padding:6px 12px;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;border:1px solid ${link.active?'rgba(99,102,241,0.5)':'rgba(255,255,255,0.06)'};background:${link.active?'rgba(99,102,241,0.15)':'transparent'};color:${link.active?'#a5b4fc':'rgba(255,255,255,0.35)'};`"
                        v-html="link.label"></button>
                </template>
            </div>
        </div>
    </BookstoreLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
