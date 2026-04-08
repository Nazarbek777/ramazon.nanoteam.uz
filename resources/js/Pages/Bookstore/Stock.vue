<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    books: { type: Array, default: () => [] },
    isLocked: { type: Boolean, default: true },
});

const pinCode = ref('');
const loginError = ref('');
const unlockSubmit = () => {
    router.post('/bookstore/stock/unlock', { code: pinCode.value }, {
        onSuccess: (page) => {
            if (page.props.flash?.error) {
                loginError.value = page.props.flash.error;
            } else {
                pinCode.value = '';
                loginError.value = '';
            }
        }
    });
};

const search = ref('');
const showModal = ref(false);
const editingBook = ref(null);

const filtered = computed(() => {
    const q = search.value.toLowerCase();
    if (!q) return props.books;
    return props.books.filter(b =>
        b.title.toLowerCase().includes(q) ||
        b.barcode.toLowerCase().includes(q) ||
        (b.author || '').toLowerCase().includes(q)
    );
});

const form = useForm({
    title: '',
    author: '',
    barcode: '',
    price: '',
    cost_price: '',
    stock: '',
});

const openEdit = (book) => {
    editingBook.value = book;
    form.title = book.title;
    form.author = book.author || '';
    form.barcode = book.barcode;
    form.price = book.price;
    form.cost_price = book.cost_price || '';
    form.stock = book.stock;
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; form.reset(); };

const submit = () => {
    if (editingBook.value) {
        form.put(`/bookstore/books/${editingBook.value.id}`, { onSuccess: closeModal });
    }
};

const deleteBook = (book) => {
    if (!confirm(`DIQQAT! "${book.title}" ni o'chirsangiz, unga tegishli BARCHA tarix BUTUNLAY o'chib ketadi. Davom etasizmi?`)) return;
    
    const code = prompt("Ushbu amalni tasdiqlash uchun xavfsizlik kodini kiriting:");
    if (code !== '7777') {
        alert("Xavfsizlik kodi noto'g'ri!");
        return;
    }

    router.delete(`/bookstore/books/${book.id}`, {
        data: { code: code },
        onBefore: () => confirm("Haqiqatan ham hamma narsani tozalab o'chirmoqchimisiz?"),
    });
};

// QR code
const showQr = ref(false);
const qrBook = ref(null);
const openQr = (book) => { qrBook.value = book; showQr.value = true; };
const closeQr = () => { showQr.value = false; qrBook.value = null; };
const qrUrl = (barcode) => `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(barcode)}&margin=10`;
const printQr = () => window.print();
</script>

<template>
    <Head title="Ombor Boshqaruvi" />

    <div class="min-h-screen font-sans text-white pb-20" style="background: #0f172a;">
        <!-- Mobile Header -->
        <header class="sticky top-0 z-40 p-4 pb-6" style="background: linear-gradient(180deg, #0f172a 0%, rgba(15, 23, 42, 0.9) 100%); backdrop-filter: blur(8px);">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-black tracking-tight text-white">NUR KITOBLAR</h1>
                    <div class="text-[10px] uppercase tracking-widest text-indigo-400 font-bold">Ombor Boshqaruvi</div>
                </div>
                <div v-if="!isLocked" class="flex gap-2">
                    <button @click="router.get('/bookstore/arrivals')" class="p-3 rounded-2xl bg-white/5 border border-white/10 active:scale-95 transition-all text-lg">📦</button>
                </div>
            </div>

            <div v-if="!isLocked" class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">🔍</span>
                <input v-model="search" type="text" placeholder="Kitob qidirish..."
                    class="w-full pl-11 pr-5 py-4 rounded-2xl text-white text-sm bg-white/5 border border-white/10 focus:outline-none focus:border-indigo-500/50 transition-all placeholder:text-white/20" />
            </div>
        </header>

        <!-- PIN Login State -->
        <div v-if="isLocked" class="flex items-center justify-center pt-20 px-6">
            <div class="w-full max-w-sm p-8 rounded-[40px] text-center"
                style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                <div class="text-6xl mb-6">🔐</div>
                <h3 class="text-2xl font-black text-white mb-2 italic">OMBORGA KIRISH</h3>
                <p class="text-xs text-white/40 mb-10 leading-relaxed uppercase tracking-widest">Xavfsizlik PIN-kodini kiriting</p>
                
                <form @submit.prevent="unlockSubmit" class="space-y-6">
                    <input v-model="pinCode" type="password" placeholder="PIN"
                        class="w-full px-5 py-5 rounded-3xl text-center text-3xl font-black tracking-[1em] text-white bg-white/5 border border-white/10 focus:outline-none focus:border-indigo-500/50 transition-all placeholder:tracking-normal placeholder:font-normal placeholder:text-sm" />
                    
                    <div v-if="loginError" class="text-rose-400 text-xs font-bold uppercase tracking-wider">{{ loginError }}</div>

                    <button type="submit"
                        class="w-full py-5 rounded-3xl font-black text-white text-sm uppercase tracking-widest transition-all active:scale-95 shadow-2xl"
                        style="background: #4f46e5;">
                        TASDIQLASH
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <main v-else class="px-4 space-y-4">
            <div v-for="book in filtered" :key="book.id" 
                class="p-5 rounded-[32px] space-y-4 transition-all active:scale-[0.98]"
                style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
                
                <div class="flex justify-between items-start gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="font-bold text-white text-lg leading-tight mb-1">{{ book.title }}</div>
                        <div class="text-xs text-white/40 uppercase tracking-wide font-medium">{{ book.author }}</div>
                    </div>
                    <div class="shrink-0 text-right">
                        <span class="block px-3 py-1.5 rounded-xl text-xs font-black mb-1"
                            :style="book.stock > 5 ? 'background:rgba(34,197,94,0.1);color:#4ade80;' : 'background:rgba(233,69,96,0.1);color:#f87171;'">
                            {{ book.stock }} ta
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2">
                    <div class="px-4 py-2 rounded-xl bg-white/5 border border-white/5 text-[10px] text-white/50 font-mono tracking-wider">{{ book.barcode }}</div>
                    <div class="px-4 py-2 rounded-xl bg-indigo-500/10 border border-indigo-500/10 text-indigo-300 font-bold text-xs">{{ Number(book.price).toLocaleString() }} so'm</div>
                </div>

                <div class="grid grid-cols-3 gap-3 pt-3 border-t border-white/5">
                    <button @click="openQr(book)" class="py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-blue-400 bg-blue-500/10 border border-blue-500/10">📷 QR</button>
                    <button @click="openEdit(book)" class="py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-indigo-400 bg-indigo-500/10 border border-indigo-500/10">✏️ TAHRIR</button>
                    <button @click="deleteBook(book)" class="py-3.5 rounded-2xl text-[10px] font-black uppercase tracking-widest text-rose-400 bg-rose-500/10 border border-rose-500/10">🗑️ O'CHIRISH</button>
                </div>
            </div>

            <div v-if="filtered.length === 0" class="py-20 text-center">
                <div class="text-6xl mb-4 opacity-10">📚</div>
                <div class="text-sm font-bold text-white/20 uppercase tracking-[0.2em]">Kitoblar topilmadi</div>
            </div>
        </main>

        <!-- Edit Modal (Mobile Styled) -->
        <Teleport to="body">
            <Transition name="slide-up">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-end justify-center" @click.self="closeModal">
                    <div class="fixed inset-0 bg-black/60 backdrop-blur-sm -z-10"></div>
                    <div class="w-full max-w-md bg-[#1e293b] rounded-t-[40px] p-8 pb-12 shadow-2xl border-t border-white/10">
                        <div class="w-12 h-1.5 bg-white/20 rounded-full mx-auto mb-8"></div>
                        
                        <h2 class="text-2xl font-black text-white mb-8 tracking-tight italic">TAHRIRLASH</h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest mb-2 text-white/40">Kitob nomi</label>
                                <input v-model="form.title" type="text" required
                                    class="w-full px-5 py-4 rounded-2xl text-white text-sm bg-white/5 border border-white/10 focus:outline-none transition-all placeholder:text-white/10"
                                    placeholder="Kitob nomi" />
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest mb-2 text-white/40">Sotuv narxi</label>
                                    <input v-model="form.price" type="number" required
                                        class="w-full px-5 py-4 rounded-2xl text-white text-sm bg-white/5 border border-white/10 focus:outline-none transition-all" />
                                </div>
                                <div>
                                    <label class="block text-[10px] font-black uppercase tracking-widest mb-2 text-white/40">Zaxira (ta)</label>
                                    <input v-model="form.stock" type="number" required
                                        class="w-full px-5 py-4 rounded-2xl text-white text-sm bg-white/5 border border-white/10 focus:outline-none transition-all" />
                                </div>
                            </div>

                            <div class="flex gap-4 pt-4">
                                <button type="button" @click="closeModal"
                                    class="flex-1 py-5 rounded-[24px] font-bold text-xs uppercase tracking-widest bg-white/5 text-white/40">BEKOR QILISH</button>
                                <button type="submit"
                                    class="flex-1 py-5 rounded-[24px] font-black text-xs uppercase tracking-widest bg-indigo-500 text-white shadow-lg active:scale-95 transition-all">SAQLASH</button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- QR Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showQr" class="fixed inset-0 z-50 flex items-center justify-center p-6 bg-black/80 backdrop-blur-xl" @click.self="closeQr">
                    <div class="w-full max-w-sm rounded-[50px] overflow-hidden bg-white text-black p-10 text-center shadow-2xl border-4 border-indigo-500/20">
                        <div id="qr-print-area">
                            <div class="font-black text-2xl tracking-tighter mb-1">{{ qrBook?.title }}</div>
                            <div class="text-xs uppercase tracking-widest font-bold text-gray-400 mb-8">{{ qrBook?.author }}</div>
                            
                            <div class="flex justify-center mb-8">
                                <div class="p-4 bg-white rounded-3xl border-2 border-gray-100 shadow-sm">
                                    <img v-if="qrBook" :src="qrUrl(qrBook.barcode)" alt="QR Code"
                                        class="w-[200px] h-[200px]" />
                                </div>
                            </div>
                            
                            <div class="inline-block px-6 py-2 rounded-full bg-black text-white font-mono text-xs mb-4">{{ qrBook?.barcode }}</div>
                            <div class="text-2xl font-black italic">{{ Number(qrBook?.price).toLocaleString() }} SO'M</div>
                        </div>

                        <div class="mt-10 flex gap-4 no-print">
                            <button @click="printQr" class="flex-1 py-4 rounded-3xl font-black text-white bg-indigo-500 text-xs uppercase tracking-widest">🖨️ CHOP ETISH</button>
                            <button @click="closeQr" class="flex-1 py-4 rounded-3xl font-bold bg-gray-100 text-gray-500 text-xs uppercase tracking-widest">YOPISH</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.slide-up-enter-active, .slide-up-leave-active { transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.slide-up-enter-from, .slide-up-leave-to { transform: translateY(100%); }

.no-print { @media print { display: none !important; } }
</style>

<style>
@media print {
    body > * { display: none !important; }
    #qr-print-area { display: block !important; position: fixed; inset: 0; background: white; color: #000; padding: 20mm; text-align: center; }
}
</style>
