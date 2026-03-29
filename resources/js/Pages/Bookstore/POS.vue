<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

// ─── Props (flash from server) ───────────────────────────────────────────────
const page = usePage();

// ─── State ───────────────────────────────────────────────────────────────────
// ─── Offline book cache (localStorage) ─────────────────────────────────────
const CACHE_KEY = 'bookstore_books_cache';
const PENDING_KEY = 'bookstore_pending_sales';

const loadCache = () => {
    try { return JSON.parse(localStorage.getItem(CACHE_KEY) || '[]'); }
    catch { return []; }
};
const saveCache = (books) => localStorage.setItem(CACHE_KEY, JSON.stringify(books));
const loadPending = () => {
    try { return JSON.parse(localStorage.getItem(PENDING_KEY) || '[]'); }
    catch { return []; }
};
const savePending = (list) => localStorage.setItem(PENDING_KEY, JSON.stringify(list));

// ─── State ───────────────────────────────────────────────────────────────────
const cart = ref([]);
const currentBarcode = ref('');
const scanError = ref('');
const isScanning = ref(false);
const successFlash = ref(false);
const isOnline = ref(navigator.onLine);
const showReceipt = ref(false);
const receipt = ref(null);
const searchMode = ref('barcode'); // 'barcode' or 'manual'
let debounceTimer = null;
const booksCache = ref(loadCache());
const selectBook = (book) => {
    const existing = cart.value.find(i => i.id === book.id);
    if (existing) { existing.quantity++; }
    else { cart.value.unshift({ ...book, quantity: 1 }); }
    currentBarcode.value = '';
    successFlash.value = true;
    setTimeout(() => successFlash.value = false, 700);
};


// ─── Offline book cache (localStorage) ─────────────────────────────────────

watch(searchMode, () => {
    setTimeout(() => {
        document.getElementById('barcodeInput')?.focus();
    }, 50);
});

const syncBooksCache = async () => {
    try {
        const { data } = await axios.get('/bookstore/books-cache');
        saveCache(data);
        booksCache.value = data;
        console.log('[POS] Books cached offline:', data.length);
    } catch (e) {
        console.warn('[POS] Could not sync books cache:', e.message);
    }
};

const findInCache = (barcode) => {
    const books = loadCache();
    return books.find(b => b.barcode === barcode) || null;
};

// ─── Pending sales sync ──────────────────────────────────────────────────────
const pendingSalesCount = ref(loadPending().length);

const syncPending = async () => {
    const pending = loadPending();
    if (!pending.length || !isOnline.value) return;
    const synced = [];
    for (const sale of pending) {
        try {
            await axios.post('/bookstore/sales/offline-sync', sale);
            synced.push(sale.localId);
        } catch {}
    }
    if (synced.length) {
        const remaining = pending.filter(s => !synced.includes(s.localId));
        savePending(remaining);
        pendingSalesCount.value = remaining.length;
        console.log('[POS] Synced pending sales:', synced.length);
    }
};

// ─── Computed ────────────────────────────────────────────────────────────────
const totalAmount = computed(() => cart.value.reduce((s, i) => s + i.price * i.quantity, 0));
const netTotal = computed(() => Math.max(0, totalAmount.value - form.discount));

// ─── Scan logic ──────────────────────────────────────────────────────────────
const handleScan = async () => {
    const code = currentBarcode.value.trim();
    if (!code || isScanning.value) return;

    isScanning.value = true;
    scanError.value = '';

    try {
        let data = null;

        if (isOnline.value) {
            try {
                const resp = await axios.get(`/bookstore/book-find/${encodeURIComponent(code)}`);
                data = resp.data;
                // Update local cache entry
                const cache = loadCache();
                const idx = cache.findIndex(b => b.id === data.id);
                if (idx >= 0) cache[idx] = data; else cache.push(data);
                saveCache(cache);
            } catch (err) {
                if (err.response?.status === 404) {
                    data = findInCache(code);
                    if (!data) throw { offline: false, message: `Barcode topilmadi: "${code}"` };
                } else {
                    // Server error — try cache
                    data = findInCache(code);
                    if (!data) throw { offline: true, message: `Server xatosi, keshda ham topilmadi` };
                }
            }
        } else {
            // Offline mode
            data = findInCache(code);
            if (!data) throw { offline: true, message: `Oflayn: "${code}" topilmadi` };
        }

        const existing = cart.value.find(i => i.id === data.id);
        if (existing) { existing.quantity++; }
        else { cart.value.unshift({ ...data, quantity: 1 }); }

        successFlash.value = true;
        setTimeout(() => successFlash.value = false, 700);

    } catch (err) {
        scanError.value = '❌ ' + (err.message || 'Xato yuz berdi');
        setTimeout(() => scanError.value = '', 4000);
    } finally {
        currentBarcode.value = '';
        isScanning.value = false;
    }
};

// ─── Submit sale ─────────────────────────────────────────────────────────────
const form = useForm({ items: [], discount: 0, payment_method: 'cash' });

const submitSale = () => {
    if (!cart.value.length) return;
    const items = cart.value.map(i => ({ id: i.id, quantity: i.quantity }));

    if (!isOnline.value) {
        // Save offline
        const pending = loadPending();
        const localId = Date.now();
        pending.push({ localId, items, discount: form.discount, payment_method: form.payment_method });
        savePending(pending);
        pendingSalesCount.value = pending.length;

        // Show offline receipt
        receipt.value = {
            id: `OFFLINE-${localId}`,
            offline: true,
            total_amount: netTotal.value,
            discount: form.discount,
            payment_method: form.payment_method,
            created_at: new Date().toLocaleString('uz-UZ'),
            user: 'Xodim',
            items: cart.value.map(i => ({
                title: i.title,
                quantity: i.quantity,
                unit_price: i.price,
                total_price: i.price * i.quantity,
            })),
        };
        showReceipt.value = true;
        cart.value = [];
        form.reset('discount');
        return;
    }

    form.items = items;
    form.post('/bookstore/sales', {
        onSuccess: () => {
            cart.value = [];
            form.reset('discount');
        },
    });
};

// Watch for saleReceipt flash from server (populated after Inertia redirect)
watch(
    () => page.props.flash?.saleReceipt,
    (val) => {
        if (val) {
            receipt.value = val;
            showReceipt.value = true;
        }
    },
    { immediate: true }
);


const printReceipt = () => {
    const r = receipt.value;
    if (!r) return;
    const rows = (r.items || []).map(i => `
        <tr>
            <td style="padding:5px 0;border-bottom:1px solid #eee;">${i.title}</td>
            <td style="padding:5px 8px;text-align:center;border-bottom:1px solid #eee;">${i.quantity}</td>
            <td style="padding:5px 0;text-align:right;border-bottom:1px solid #eee;">${Number(i.unit_price).toLocaleString()}</td>
            <td style="padding:5px 0 5px 8px;text-align:right;border-bottom:1px solid #eee;font-weight:700;">${Number(i.total_price).toLocaleString()}</td>
        </tr>`).join('');
    const html = `<!DOCTYPE html><html><head>
        <meta charset="UTF-8"><title>Chek #${r.id}</title>
        <style>
            * { margin:0; padding:0; box-sizing:border-box; }
            body { font-family: 'Courier New', monospace; font-size: 13px; color: #000; background: #fff; padding: 24px 20px; max-width: 320px; margin: 0 auto; }
            h2 { font-size: 16px; font-weight: 800; text-align: center; margin-bottom: 4px; letter-spacing: 0.5px; }
            .sub { text-align: center; font-size: 11px; color: #555; margin-bottom: 14px; }
            .divider { border: none; border-top: 1px dashed #999; margin: 10px 0; }
            table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
            th { font-size: 10px; text-align: left; color: #777; padding-bottom: 6px; border-bottom: 1px solid #ccc; }
            th:nth-child(2), th:nth-child(3), th:nth-child(4) { text-align: right; }
            .totals { font-size: 13px; }
            .totals div { display: flex; justify-content: space-between; padding: 3px 0; }
            .grand { font-size: 16px; font-weight: 800; margin-top: 4px; }
            .footer { text-align: center; font-size: 11px; color: #777; margin-top: 14px; }
            .badge { display: inline-block; background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        </style>
    </head><body>
        <h2>KITOB DO'KONI</h2>
        <div class="sub">${r.created_at} &nbsp;|&nbsp; Chek #${r.id}</div>
        <hr class="divider">
        <table>
            <thead><tr>
                <th>Kitob</th>
                <th style="text-align:center;">Dona</th>
                <th style="text-align:right;">Narx</th>
                <th style="text-align:right;padding-left:8px;">Summa</th>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table>
        <hr class="divider">
        <div class="totals">
            ${r.discount ? `<div><span>Chegirma</span><span>−${Number(r.discount).toLocaleString()} so'm</span></div>` : ''}
            <div class="grand"><span>JAMI</span><span>${Number(r.total_amount).toLocaleString()} so'm</span></div>
            <div style="margin-top:6px;"><span>To'lov</span><span class="badge">${(r.payment_method || '').toUpperCase()}</span></div>
        </div>
        <hr class="divider">
        <div class="footer">Xaridingiz uchun rahmat!</div>
        <script>window.onload=function(){window.print();setTimeout(function(){window.close();},800);}<\/script>
    </body></html>`;
    const w = window.open('', '_blank', 'width=380,height=600');
    w.document.write(html);
    w.document.close();
};
const closeReceipt = () => { showReceipt.value = false; receipt.value = null; };

// ─── Global key capture ──────────────────────────────────────────────────────
const handleGlobalKey = (e) => {
    const active = document.activeElement;
    const isOther = (active?.tagName === 'INPUT' || active?.tagName === 'TEXTAREA')
        && !['barcodeInput'].includes(active.id);
    if (isOther) return;
    if (e.key === 'Enter') { e.preventDefault(); clearTimeout(debounceTimer); if (currentBarcode.value.trim()) handleScan(); return; }
    if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
        currentBarcode.value += e.key;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => { if (currentBarcode.value.trim()) handleScan(); }, 200);
    }
};

const removeItem = (idx) => cart.value.splice(idx, 1);

const payMethods = [
    { key: 'cash', label: 'Naqd',  icon: '💵' },
    { key: 'card', label: 'Karta', icon: '💳' },
    { key: 'click', label: 'Click', icon: '📱' },
    { key: 'payme', label: 'Payme', icon: '🅿️' },
];

const onlineHandler = () => { isOnline.value = true; syncPending(); syncBooksCache(); };
const offlineHandler = () => { isOnline.value = false; };

onMounted(() => {
    window.addEventListener('keydown', handleGlobalKey);
    window.addEventListener('online', onlineHandler);
    window.addEventListener('offline', offlineHandler);
    // Load cache on mount; sync if online
    if (isOnline.value) { syncBooksCache(); syncPending(); }
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKey);
    window.removeEventListener('online', onlineHandler);
    window.removeEventListener('offline', offlineHandler);
    clearTimeout(debounceTimer);
});
</script>

<template>
    <Head title="POS — Sotuv" />

    <!-- ══ PRINT STYLES ═══════════════════════════════════════════════════ -->
    <Teleport to="head">
        <style>
            @media print {
                body > * { display: none !important; }
                #receipt-print { display: block !important; position: fixed; inset: 0; background: white; padding: 24px; font-family: monospace; font-size: 13px; color: #000; }
            }
            #receipt-print { display: none; }
        </style>
    </Teleport>

    <!-- ══ PRINT RECEIPT DOM ═══════════════════════════════════════════════ -->
    <div id="receipt-print" v-if="receipt">
        <div style="text-align:center; font-weight:bold; font-size:16px; margin-bottom:8px;">🏪 KITOB DO'KONI</div>
        <div style="text-align:center; font-size:11px; color:#666; margin-bottom:12px;">{{ receipt.created_at }}</div>
        <div style="border-top:1px dashed #000; margin:8px 0;"></div>
        <table style="width:100%; border-collapse:collapse; font-size:12px;">
            <thead><tr><td style="font-weight:bold;">Kitob</td><td style="text-align:center; font-weight:bold;">Dona</td><td style="text-align:right; font-weight:bold;">Summa</td></tr></thead>
            <tbody>
                <tr v-for="item in receipt.items" :key="item.title">
                    <td style="padding:3px 0;">{{ item.title }}</td>
                    <td style="text-align:center;">{{ item.quantity }}</td>
                    <td style="text-align:right;">{{ Number(item.total_price).toLocaleString() }}</td>
                </tr>
            </tbody>
        </table>
        <div style="border-top:1px dashed #000; margin:8px 0;"></div>
        <div style="display:flex;justify-content:space-between;"><span>Chegirma:</span><span>{{ Number(receipt.discount).toLocaleString() }} so'm</span></div>
        <div style="display:flex;justify-content:space-between; font-weight:bold; font-size:15px; margin-top:4px;"><span>JAMI:</span><span>{{ Number(receipt.total_amount).toLocaleString() }} so'm</span></div>
        <div style="margin-top:6px; font-size:11px;">To'lov: <b>{{ receipt.payment_method?.toUpperCase() }}</b> | Chek №{{ receipt.id }}</div>
        <div style="border-top:1px dashed #000; margin:12px 0;"></div>
        <div style="text-align:center; font-size:11px;">Xaridingiz uchun rahmat! 🙏</div>
    </div>

    <BookstoreLayout>
        <template #header>
            🔄 Sotuv Paneli (POS)
            <span v-if="!isOnline" class="ml-3 text-xs font-bold px-3 py-1 rounded-full animate-pulse"
                style="background: rgba(234,179,8,0.2); color: #facc15; border: 1px solid rgba(234,179,8,0.3);">
                📡 OFLAYN
            </span>
            <span v-if="pendingSalesCount > 0" class="ml-2 text-xs font-bold px-3 py-1 rounded-full"
                style="background: rgba(234,179,8,0.15); color: #fb923c;">
                {{ pendingSalesCount }} ta sotuv sinxronlanmagan
            </span>
        </template>

        <div class="flex gap-6" style="height: calc(100vh - 9rem);">

            <!-- LEFT: Scanner + Cart -->
            <div class="flex-grow flex flex-col gap-4 min-w-0">

                <!-- Scanner Zone -->
                <div class="p-5 rounded-3xl transition-all duration-300"
                    :style="successFlash
                        ? 'background:linear-gradient(135deg,rgba(34,197,94,.2),rgba(34,197,94,.05));border:1px solid rgba(34,197,94,.5);'
                        : scanError
                            ? 'background:linear-gradient(135deg,rgba(233,69,96,.15),rgba(233,69,96,.05));border:1px solid rgba(233,69,96,.35);'
                            : 'background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);'">
                    <!-- Mode Toggles -->
                    <div class="flex gap-2 mb-4 p-1 rounded-2xl bg-white/5 w-fit">
                        <button @click="searchMode = 'barcode'; currentBarcode = '';" 
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                            :class="searchMode === 'barcode' ? 'bg-indigo-500 text-white shadow-lg' : 'text-white/40 hover:text-white'">
                            📑 Shtrix-kod
                        </button>
                        <button @click="searchMode = 'manual'; currentBarcode = '';" 
                            class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                            :class="searchMode === 'manual' ? 'bg-indigo-500 text-white shadow-lg' : 'text-white/40 hover:text-white'">
                            🔍 Qidirish
                        </button>
                    </div>

                    <div class="flex items-center gap-4 relative">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0"
                            :style="successFlash ? 'background:rgba(34,197,94,.2);' : isOnline ? 'background:rgba(255,255,255,.06);' : 'background:rgba(234,179,8,.12);'">
                            {{ successFlash ? '✅' : isScanning ? '⏳' : isOnline ? '📡' : '🔌' }}
                        </div>
                        <div class="flex-grow relative">
                            <input id="barcodeInput"
                                v-model="currentBarcode"
                                @keydown.enter.prevent="handleScan"
                                type="text" autocomplete="off"
                                :placeholder="searchMode === 'barcode' ? 'Shtrix-kodni skanerlang...' : 'Kitob nomi yoki muallifi...'"
                                class="w-full px-5 py-3.5 rounded-2xl text-white text-base font-mono focus:outline-none transition-all"
                                style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);" />
                            
                            <!-- Search Results Dropdown (Only in Manual Mode) -->
                            <div v-if="searchMode === 'manual' && currentBarcode.length > 1" 
                                class="absolute top-full left-0 right-0 z-50 mt-2 rounded-2xl overflow-hidden shadow-2xl"
                                style="background:#1a1a2e;border:1px solid rgba(255,255,255,.1);">
                                <div v-for="b in booksCache.filter(x => x.title.toLowerCase().includes(currentBarcode.toLowerCase()) || x.barcode.includes(currentBarcode)).slice(0, 6)"
                                    :key="b.id" @click="selectBook(b)"
                                    class="px-5 py-3 flex items-center justify-between cursor-pointer hover:bg-white/5 border-b border-white/5 last:border-0">
                                    <div class="min-w-0">
                                        <div class="text-sm font-bold text-white truncate">{{ b.title }}</div>
                                        <div class="text-[10px] text-white/30 font-mono">{{ b.barcode }}</div>
                                    </div>
                                    <div class="text-xs font-black text-indigo-400 ml-4">{{ Number(b.price).toLocaleString() }} so'm</div>
                                </div>
                                <div v-if="!booksCache.some(x => x.title.toLowerCase().includes(currentBarcode.toLowerCase()) || x.barcode.includes(currentBarcode))"
                                    class="px-5 py-6 text-center text-xs text-white/20">
                                    Hech narsa topilmadi 😕
                                </div>
                            </div>
                        </div>
                        <button @click="handleScan"
                            class="px-6 py-3.5 rounded-2xl font-bold text-white text-sm flex-shrink-0 transition-all active:scale-95"
                            style="background:linear-gradient(135deg,#e94560,#533483);">
                            Qidirish
                        </button>
                    </div>
                    <div v-if="scanError" class="mt-3 text-sm font-bold px-4 py-2 rounded-xl"
                        style="color:#ff6b84;background:rgba(233,69,96,.1);">{{ scanError }}</div>
                    <div v-if="!isOnline" class="mt-3 text-xs px-4 py-2 rounded-xl"
                        style="color:#facc15;background:rgba(234,179,8,.08);">
                        ⚠️ Internet yo'q — oflayn rejimda ishlayapsiz. Mahsulotlar lokal keshdan olinadi.
                    </div>
                </div>

                <!-- Cart -->
                <div class="flex-grow rounded-3xl flex flex-col overflow-hidden"
                    style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);">
                    <div class="px-6 py-4 flex items-center justify-between"
                        style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <h3 class="font-bold text-white flex items-center gap-2">
                            Savat
                            <span v-if="cart.length" class="px-2 py-0.5 rounded-full text-xs font-black"
                                style="background:rgba(233,69,96,.2);color:#e94560;">{{ cart.length }}</span>
                        </h3>
                        <button v-if="cart.length" @click="cart = []"
                            class="text-xs font-bold px-3 py-1.5 rounded-xl"
                            style="color:rgba(255,255,255,.35);background:rgba(255,255,255,.04);">
                            Savatni tozalash
                        </button>
                    </div>
                    <div class="flex-grow overflow-auto">
                        <div v-if="!cart.length" class="h-full flex flex-col items-center justify-center gap-4">
                            <div class="text-6xl opacity-10">🛒</div>
                            <div class="text-sm font-medium" style="color:rgba(255,255,255,.2);">Barcode skanerlang</div>
                        </div>
                        <div v-for="(item, idx) in cart" :key="item.id"
                            class="flex items-center justify-between px-6 py-4 transition-colors group"
                            :style="idx > 0 ? 'border-top:1px solid rgba(255,255,255,.04);' : ''">
                            <div class="flex items-center gap-4 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                                    style="background:rgba(255,255,255,.06);">📖</div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-white text-sm truncate">{{ item.title }}</div>
                                    <div class="text-xs font-mono mt-0.5" style="color:rgba(255,255,255,.3);">{{ item.barcode }}</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-5 flex-shrink-0 ml-4">
                                <div class="flex items-center gap-2 rounded-xl p-1" style="background:rgba(255,255,255,.06);">
                                    <button @click="item.quantity > 1 ? item.quantity-- : removeItem(idx)"
                                        class="w-7 h-7 rounded-lg text-sm font-bold flex items-center justify-center text-white hover:bg-white/10">−</button>
                                    <span class="w-7 text-center text-sm font-black text-white">{{ item.quantity }}</span>
                                    <button @click="item.quantity++"
                                        class="w-7 h-7 rounded-lg text-sm font-bold flex items-center justify-center text-white hover:bg-white/10">+</button>
                                </div>
                                <div class="text-right w-28">
                                    <div class="font-extrabold text-white text-sm">{{ (item.price * item.quantity).toLocaleString() }} so'm</div>
                                    <div class="text-[10px] mt-0.5" style="color:rgba(255,255,255,.3);">× {{ Number(item.price).toLocaleString() }}</div>
                                </div>
                                <button @click="removeItem(idx)"
                                    class="opacity-0 group-hover:opacity-100 w-7 h-7 rounded-lg flex items-center justify-center text-red-400 text-sm hover:bg-red-500/20">✕</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Checkout -->
            <div class="w-80 flex-shrink-0 flex flex-col gap-4">
                <div class="rounded-3xl flex flex-col gap-6 p-6"
                    style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);">
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest mb-4" style="color:rgba(255,255,255,.3);">Hisob-kitob</div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm" style="color:rgba(255,255,255,.5);">Jami summa</span>
                                <span class="font-bold text-white text-sm">{{ totalAmount.toLocaleString() }} so'm</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm" style="color:rgba(255,255,255,.5);">Chegirma</span>
                                <div class="flex items-center gap-1">
                                    <input v-model.number="form.discount" id="discountInput" type="number" min="0"
                                        class="w-24 text-right text-sm font-bold text-white bg-transparent focus:outline-none border-b pb-0.5"
                                        style="border-color:rgba(255,255,255,.15);" placeholder="0" />
                                    <span class="text-xs" style="color:rgba(255,255,255,.3);">so'm</span>
                                </div>
                            </div>
                            <div class="pt-3 flex justify-between items-end" style="border-top:1px dashed rgba(255,255,255,.1);">
                                <span class="text-xs font-bold uppercase tracking-widest" style="color:rgba(255,255,255,.3);">To'lanadigan</span>
                                <span class="text-2xl font-extrabold" style="color:#e94560;">{{ netTotal.toLocaleString() }}<span class="text-sm font-medium ml-1" style="color:rgba(255,255,255,.4);">so'm</span></span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest mb-3" style="color:rgba(255,255,255,.3);">To'lov turi</div>
                        <div class="grid grid-cols-2 gap-2">
                            <button v-for="m in payMethods" :key="m.key" @click="form.payment_method = m.key"
                                class="py-3 px-2 rounded-2xl font-bold text-sm flex flex-col items-center gap-1 transition-all"
                                :style="form.payment_method === m.key
                                    ? 'background:linear-gradient(135deg,rgba(233,69,96,.3),rgba(83,52,131,.3));border:1px solid rgba(233,69,96,.5);color:white;'
                                    : 'background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);color:rgba(255,255,255,.4);'">
                                <span class="text-lg">{{ m.icon }}</span>
                                <span class="text-xs">{{ m.label }}</span>
                            </button>
                        </div>
                    </div>

                    <button @click="submitSale" :disabled="!cart.length || form.processing"
                        class="w-full py-5 rounded-2xl font-extrabold text-white text-base transition-all active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed"
                        style="background:linear-gradient(135deg,#22c55e,#16a34a);box-shadow:0 8px 30px rgba(34,197,94,.25);">
                        ✅ Sotuvni tasdiqlash
                        <span v-if="!isOnline" class="text-xs block font-normal opacity-75">(oflayn saqlash)</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ══ RECEIPT MODAL ═════════════════════════════════════════════════ -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showReceipt"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="background:rgba(0,0,0,.75);backdrop-filter:blur(6px);">
                    <div class="w-full max-w-sm rounded-3xl overflow-hidden"
                        style="background:#1a1a2e;border:1px solid rgba(255,255,255,.1);box-shadow:0 40px 100px rgba(0,0,0,.6);">
                        <!-- Header -->
                        <div class="px-7 py-6 text-center" style="background:linear-gradient(135deg,rgba(34,197,94,.15),rgba(34,197,94,.05));">
                            <div class="text-4xl mb-2">🧾</div>
                            <div class="font-extrabold text-white text-lg">Sotuv muvaffaqiyatli!</div>
                            <div class="text-xs mt-1" style="color:rgba(255,255,255,.4);">{{ receipt?.created_at }}</div>
                            <div v-if="receipt?.offline" class="mt-2 text-xs px-3 py-1 rounded-full inline-block font-bold"
                                style="background:rgba(234,179,8,.15);color:#facc15;">📡 Oflayn saqlandi</div>
                        </div>

                        <!-- Items -->
                        <div class="px-7 py-4 space-y-2 max-h-60 overflow-y-auto">
                            <div v-for="item in receipt?.items" :key="item.title"
                                class="flex justify-between items-center text-sm">
                                <div class="min-w-0 flex-1">
                                    <div class="font-medium text-white truncate">{{ item.title }}</div>
                                    <div class="text-xs" style="color:rgba(255,255,255,.35);">{{ item.quantity }} × {{ Number(item.unit_price).toLocaleString() }}</div>
                                </div>
                                <div class="font-bold text-white ml-4">{{ Number(item.total_price).toLocaleString() }}</div>
                            </div>
                        </div>

                        <!-- Totals -->
                        <div class="px-7 py-4" style="border-top:1px solid rgba(255,255,255,.07);">
                            <div v-if="receipt?.discount" class="flex justify-between text-sm mb-2" style="color:rgba(255,255,255,.5);">
                                <span>Chegirma</span>
                                <span>−{{ Number(receipt.discount).toLocaleString() }} so'm</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="font-bold text-white">JAMI</span>
                                <span class="text-2xl font-extrabold" style="color:#22c55e;">{{ Number(receipt?.total_amount).toLocaleString() }} <span class="text-sm font-normal" style="color:rgba(255,255,255,.4);">so'm</span></span>
                            </div>
                            <div class="text-xs mt-1" style="color:rgba(255,255,255,.35);">
                                To'lov: {{ receipt?.payment_method?.toUpperCase() }} | Chek №{{ receipt?.id }}
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="px-7 pb-7 flex gap-3">
                            <button @click="printReceipt"
                                class="flex-1 py-3.5 rounded-2xl font-bold text-white text-sm transition-all active:scale-95"
                                style="background:linear-gradient(135deg,#3b82f6,#2563eb);">
                                🖨️ Chek chiqarish
                            </button>
                            <button @click="closeReceipt"
                                class="flex-1 py-3.5 rounded-2xl font-bold text-sm transition-all active:scale-95"
                                style="background:rgba(255,255,255,.06);color:rgba(255,255,255,.5);">
                                Yopish
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </BookstoreLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
