<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const barcodeInput = ref(null);
const currentBarcode = ref('');
const cart = ref([]);
const scanError = ref('');
const isScanning = ref(false);
const successFlash = ref(false);
let debounceTimer = null;

const totalAmount = computed(() => cart.value.reduce((s, i) => s + i.price * i.quantity, 0));
const netTotal = computed(() => Math.max(0, totalAmount.value - form.discount));

const handleScan = async () => {
    const code = currentBarcode.value.trim();
    if (!code || isScanning.value) return;

    isScanning.value = true;
    scanError.value = '';
    clearTimeout(debounceTimer);

    const url = `/bookstore/book-find/${encodeURIComponent(code)}`;
    console.log('[POS] Scanning:', code, '→', url);

    try {
        const { data } = await axios.get(url);
        if (!data?.id) throw new Error('Invalid response');

        const existing = cart.value.find(i => i.id === data.id);
        if (existing) {
            existing.quantity++;
        } else {
            cart.value.unshift({ ...data, quantity: 1 });
        }

        successFlash.value = true;
        setTimeout(() => successFlash.value = false, 800);

    } catch (err) {
        const status = err.response?.status;
        console.error('[POS] Error:', status, err.response?.data ?? err.message);
        scanError.value = status === 404
            ? `❌ Barcode topilmadi: "${code}"`
            : `❌ Xato: ${err.message}`;
        setTimeout(() => scanError.value = '', 4000);
    } finally {
        currentBarcode.value = '';
        isScanning.value = false;
    }
};

// ─── Global keystroke capture ────────────────────────────────────────────────
// Captures ALL keystrokes on the page regardless of which element has focus.
// This is the standard POS pattern for USB/Bluetooth barcode scanners.
const handleGlobalKey = (e) => {
    // If user is actively typing in a different input (discount field, etc.), ignore
    const active = document.activeElement;
    const isOtherInput = (active?.tagName === 'INPUT' || active?.tagName === 'TEXTAREA')
        && active !== barcodeInput.value;
    if (isOtherInput) return;

    if (e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(debounceTimer);
        if (currentBarcode.value.trim()) handleScan();
        return;
    }

    // Append printable characters to barcode buffer
    if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
        currentBarcode.value += e.key;
        // Also auto-trigger 200ms after last keystroke (in case no Enter)
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            if (currentBarcode.value.trim()) handleScan();
        }, 200);
    }
};

const removeItem = (idx) => cart.value.splice(idx, 1);

const form = useForm({ items: [], discount: 0, payment_method: 'cash' });

const submitSale = () => {
    if (!cart.value.length) return;
    form.items = cart.value.map(i => ({ id: i.id, quantity: i.quantity }));
    form.post('/bookstore/sales', {
        onSuccess: () => { cart.value = []; form.reset('discount'); },
    });
};

const payMethods = [
    { key: 'cash',  label: 'Naqd',  icon: '💵' },
    { key: 'card',  label: 'Karta', icon: '💳' },
    { key: 'click', label: 'Click', icon: '📱' },
    { key: 'payme', label: 'Payme', icon: '🅿️' },
];

onMounted(() => {
    window.addEventListener('keydown', handleGlobalKey);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKey);
    clearTimeout(debounceTimer);
});
</script>

<template>
    <Head title="POS — Sotuv" />

    <BookstoreLayout>
        <template #header>🔄 Sotuv Paneli (POS)</template>

        <div class="flex gap-6 h-[calc(100vh-9rem)]">

            <!-- LEFT: Scanner + Cart -->
            <div class="flex-grow flex flex-col gap-4 min-w-0">

                <!-- Scanner Zone -->
                <div class="p-5 rounded-3xl transition-all duration-300"
                    :style="successFlash
                        ? 'background: linear-gradient(135deg, rgba(34,197,94,0.2), rgba(34,197,94,0.05)); border: 1px solid rgba(34,197,94,0.5);'
                        : scanError
                            ? 'background: linear-gradient(135deg, rgba(233,69,96,0.15), rgba(233,69,96,0.05)); border: 1px solid rgba(233,69,96,0.35);'
                            : 'background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08);'">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-2xl flex-shrink-0"
                            :style="successFlash ? 'background: rgba(34,197,94,0.2);' : 'background: rgba(255,255,255,0.06);'">
                            {{ successFlash ? '✅' : isScanning ? '⏳' : '📡' }}
                        </div>
                        <div class="flex-grow relative">
                            <input
                                ref="barcodeInput"
                                v-model="currentBarcode"
                                @input="onBarcodeInput"
                                @keydown.enter.prevent="handleScan"
                                @keyup.enter.prevent="handleScan"
                                @click.stop
                                type="text"
                                autocomplete="off"
                                placeholder="Barcode skanerlang yoki yozing..."
                                class="w-full px-5 py-3.5 rounded-2xl text-white text-base font-mono transition-all focus:outline-none"
                                style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                            />
                        </div>
                        <button @click="handleScan"
                            class="px-6 py-3.5 rounded-2xl font-bold text-white text-sm flex-shrink-0 transition-all active:scale-95"
                            style="background: linear-gradient(135deg, #e94560, #533483);">
                            Skanerlash
                        </button>
                    </div>
                    <div v-if="scanError" class="mt-3 text-sm font-bold px-4 py-2 rounded-xl" style="color: #ff6b84; background: rgba(233,69,96,0.1);">
                        ⚠️ {{ scanError }}
                    </div>
                </div>

                <!-- Cart Table -->
                <div class="flex-grow rounded-3xl flex flex-col overflow-hidden"
                    style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                    <div class="px-6 py-4 flex items-center justify-between" style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                        <h3 class="font-bold text-white flex items-center gap-2">
                            Savat
                            <span v-if="cart.length" class="px-2 py-0.5 rounded-full text-xs font-black"
                                style="background: rgba(233,69,96,0.2); color: #e94560;">{{ cart.length }}</span>
                        </h3>
                        <button v-if="cart.length" @click="cart = []" class="text-xs font-bold px-3 py-1.5 rounded-xl transition-all"
                            style="color: rgba(255,255,255,0.35); background: rgba(255,255,255,0.04);">
                            Savatni tozalash
                        </button>
                    </div>

                    <div class="flex-grow overflow-auto">
                        <!-- Empty state -->
                        <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center gap-4">
                            <div class="text-6xl opacity-10">🛒</div>
                            <div class="text-sm font-medium" style="color: rgba(255,255,255,0.2);">Barcode skanerlang yoki matnli barcode kiriting</div>
                        </div>

                        <!-- Items -->
                        <div v-for="(item, idx) in cart" :key="item.id"
                            class="flex items-center justify-between px-6 py-4 transition-colors group"
                            :style="idx > 0 ? 'border-top: 1px solid rgba(255,255,255,0.04);' : ''">
                            <div class="flex items-center gap-4 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0"
                                    style="background: rgba(255,255,255,0.06);">📖</div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-white text-sm truncate">{{ item.title }}</div>
                                    <div class="text-xs font-mono mt-0.5" style="color: rgba(255,255,255,0.3);">{{ item.barcode }}</div>
                                </div>
                            </div>

                            <div class="flex items-center gap-5 flex-shrink-0 ml-4">
                                <!-- Qty controls -->
                                <div class="flex items-center gap-2 rounded-xl p-1" style="background: rgba(255,255,255,0.06);">
                                    <button @click="item.quantity > 1 ? item.quantity-- : removeItem(idx)"
                                        class="w-7 h-7 rounded-lg text-sm font-bold transition-all hover:bg-white/10 flex items-center justify-center text-white">−</button>
                                    <span class="w-7 text-center text-sm font-black text-white">{{ item.quantity }}</span>
                                    <button @click="item.quantity++"
                                        class="w-7 h-7 rounded-lg text-sm font-bold transition-all hover:bg-white/10 flex items-center justify-center text-white">+</button>
                                </div>

                                <!-- Price -->
                                <div class="text-right w-28">
                                    <div class="font-extrabold text-white text-sm">{{ (item.price * item.quantity).toLocaleString() }} so'm</div>
                                    <div class="text-[10px] mt-0.5" style="color: rgba(255,255,255,0.3);">× {{ Number(item.price).toLocaleString() }}</div>
                                </div>

                                <button @click="removeItem(idx)"
                                    class="opacity-0 group-hover:opacity-100 w-7 h-7 rounded-lg flex items-center justify-center transition-all hover:bg-red-500/20 text-red-400 text-sm">✕</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Checkout Panel -->
            <div class="w-80 flex-shrink-0 flex flex-col gap-4">
                <div class="rounded-3xl flex flex-col gap-6 p-6"
                    style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">

                    <!-- Summary -->
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest mb-4" style="color: rgba(255,255,255,0.3);">Hisob-kitob</div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm" style="color: rgba(255,255,255,0.5);">Jami summa</span>
                                <span class="font-bold text-white text-sm">{{ totalAmount.toLocaleString() }} so'm</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm" style="color: rgba(255,255,255,0.5);">Chegirma</span>
                                <div class="flex items-center gap-1">
                                    <input v-model.number="form.discount" type="number" min="0"
                                        class="w-24 text-right text-sm font-bold text-white bg-transparent focus:outline-none border-b pb-0.5"
                                        style="border-color: rgba(255,255,255,0.15);" placeholder="0" />
                                    <span class="text-xs" style="color: rgba(255,255,255,0.3);">so'm</span>
                                </div>
                            </div>
                            <div class="pt-3 flex justify-between items-end" style="border-top: 1px dashed rgba(255,255,255,0.1);">
                                <span class="text-xs font-bold uppercase tracking-widest" style="color: rgba(255,255,255,0.3);">To'lanadigan</span>
                                <span class="text-2xl font-extrabold" style="color: #e94560;">{{ netTotal.toLocaleString() }}<span class="text-sm font-medium ml-1" style="color: rgba(255,255,255,0.4);">so'm</span></span>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div>
                        <div class="text-xs font-bold uppercase tracking-widest mb-3" style="color: rgba(255,255,255,0.3);">To'lov turi</div>
                        <div class="grid grid-cols-2 gap-2">
                            <button v-for="m in payMethods" :key="m.key"
                                @click="form.payment_method = m.key"
                                class="py-3 px-2 rounded-2xl font-bold text-sm flex flex-col items-center gap-1 transition-all"
                                :style="form.payment_method === m.key
                                    ? 'background: linear-gradient(135deg, rgba(233,69,96,0.3), rgba(83,52,131,0.3)); border: 1px solid rgba(233,69,96,0.5); color: white;'
                                    : 'background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); color: rgba(255,255,255,0.4);'">
                                <span class="text-lg">{{ m.icon }}</span>
                                <span class="text-xs">{{ m.label }}</span>
                            </button>
                        </div>
                    </div>

                    <!-- Checkout Button -->
                    <button @click="submitSale" :disabled="cart.length === 0 || form.processing"
                        class="w-full py-5 rounded-2xl font-extrabold text-white text-base transition-all active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                        style="background: linear-gradient(135deg, #22c55e, #16a34a); box-shadow: 0 8px 30px rgba(34,197,94,0.25);">
                        <span>✅ Sotuvni tasdiqlash</span>
                        <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </BookstoreLayout>
</template>
