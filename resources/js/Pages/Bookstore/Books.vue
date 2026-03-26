<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    books: { type: Array, default: () => [] },
});

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
    stock: '',
});

const openAdd = () => {
    form.reset();
    editingBook.value = null;
    showModal.value = true;
};

const openEdit = (book) => {
    editingBook.value = book;
    form.title = book.title;
    form.author = book.author || '';
    form.barcode = book.barcode;
    form.price = book.price;
    form.stock = book.stock;
    showModal.value = true;
};

const closeModal = () => { showModal.value = false; form.reset(); };

const submit = () => {
    if (editingBook.value) {
        form.put(`/bookstore/books/${editingBook.value.id}`, { onSuccess: closeModal });
    } else {
        form.post('/bookstore/books', { onSuccess: closeModal });
    }
};

const deleteBook = (book) => {
    if (!confirm(`"${book.title}" ni o'chirasizmi?`)) return;
    router.delete(`/bookstore/books/${book.id}`);
};
</script>

<template>
    <Head title="Kitoblar boshqaruvi" />

    <BookstoreLayout>
        <template #header>📚 Kitoblar</template>

        <!-- Toolbar -->
        <div class="flex items-center justify-between gap-4 mb-6">
            <div class="relative flex-grow max-w-sm">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">🔍</span>
                <input v-model="search" type="text" placeholder="Qidirish: nom, barcode, muallif..."
                    class="w-full pl-10 pr-5 py-3.5 rounded-2xl text-white text-sm bg-transparent focus:outline-none transition-all"
                    style="background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);" />
            </div>
            <button @click="openAdd"
                class="px-6 py-3.5 rounded-2xl font-bold text-white text-sm transition-all active:scale-95 flex items-center gap-2 flex-shrink-0"
                style="background: linear-gradient(135deg, #e94560, #533483); box-shadow: 0 6px 20px rgba(233,69,96,0.3);">
                ＋ Yangi kitob
            </button>
        </div>

        <!-- Table -->
        <div class="rounded-3xl overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07);">
            <table class="w-full text-left">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                        <th class="px-7 py-4 text-xs font-bold uppercase tracking-widest" style="color: rgba(255,255,255,0.25);">Kitob nomi</th>
                        <th class="px-7 py-4 text-xs font-bold uppercase tracking-widest" style="color: rgba(255,255,255,0.25);">Barcode</th>
                        <th class="px-7 py-4 text-xs font-bold uppercase tracking-widest" style="color: rgba(255,255,255,0.25);">Narx</th>
                        <th class="px-7 py-4 text-xs font-bold uppercase tracking-widest" style="color: rgba(255,255,255,0.25);">Zaxira</th>
                        <th class="px-7 py-4 text-xs font-bold uppercase tracking-widest" style="color: rgba(255,255,255,0.25);">Amallar</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(book, idx) in filtered" :key="book.id"
                        class="transition-colors hover:bg-white/[0.02] group"
                        :style="idx > 0 ? 'border-top: 1px solid rgba(255,255,255,0.04);' : ''">
                        <td class="px-7 py-4">
                            <div class="font-semibold text-white text-sm">{{ book.title }}</div>
                            <div class="text-xs mt-0.5" style="color: rgba(255,255,255,0.3);">{{ book.author }}</div>
                        </td>
                        <td class="px-7 py-4">
                            <span class="px-3 py-1 rounded-xl text-xs font-mono font-bold"
                                style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.6);">
                                {{ book.barcode }}
                            </span>
                        </td>
                        <td class="px-7 py-4 font-bold text-white text-sm">{{ Number(book.price).toLocaleString() }} <span style="color: rgba(255,255,255,0.3); font-weight: 400; font-size: 11px;">so'm</span></td>
                        <td class="px-7 py-4">
                            <span class="px-3 py-1.5 rounded-xl text-xs font-black"
                                :style="book.stock > 5
                                    ? 'background: rgba(34,197,94,0.12); color: #4ade80;'
                                    : book.stock > 0
                                        ? 'background: rgba(234,179,8,0.12); color: #facc15;'
                                        : 'background: rgba(233,69,96,0.12); color: #f87171;'">
                                {{ book.stock }} ta
                            </span>
                        </td>
                        <td class="px-7 py-4">
                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all">
                                <button @click="openEdit(book)"
                                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                                    style="background: rgba(83,52,131,0.2); color: #a78bfa;">
                                    ✏️ Tahrirlash
                                </button>
                                <button @click="deleteBook(book)"
                                    class="px-4 py-2 rounded-xl text-xs font-bold transition-all"
                                    style="background: rgba(233,69,96,0.1); color: #f87171;">
                                    🗑️ O'chirish
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr v-if="filtered.length === 0">
                        <td colspan="5" class="px-7 py-16 text-center">
                            <div class="text-5xl mb-3 opacity-20">📚</div>
                            <div class="text-sm font-medium" style="color: rgba(255,255,255,0.2);">Kitoblar topilmadi</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <Transition name="fade">
                <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);" @click.self="closeModal">
                    <div class="w-full max-w-md rounded-3xl p-8"
                        style="background: #1a1a2e; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 40px 100px rgba(0,0,0,0.6);">

                        <h2 class="text-xl font-extrabold text-white mb-7">
                            {{ editingBook ? '✏️ Kitobni tahrirlash' : '➕ Yangi kitob qo\'shish' }}
                        </h2>

                        <form @submit.prevent="submit" class="space-y-4">
                            <!-- Title -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-widest mb-2" style="color: rgba(255,255,255,0.35);">Kitob nomi *</label>
                                <input v-model="form.title" type="text" required
                                    class="w-full px-4 py-3.5 rounded-2xl text-white text-sm focus:outline-none transition-all"
                                    style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                                    placeholder="Masalan: O'tkan kunlar" />
                                <div v-if="form.errors.title" class="text-red-400 text-xs mt-1.5">{{ form.errors.title }}</div>
                            </div>

                            <!-- Author -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-widest mb-2" style="color: rgba(255,255,255,0.35);">Muallif</label>
                                <input v-model="form.author" type="text"
                                    class="w-full px-4 py-3.5 rounded-2xl text-white text-sm focus:outline-none transition-all"
                                    style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                                    placeholder="Masalan: Abdulla Qodiriy" />
                            </div>

                            <!-- Barcode -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-widest mb-2" style="color: rgba(255,255,255,0.35);">Barcode *</label>
                                <input v-model="form.barcode" type="text" required
                                    class="w-full px-4 py-3.5 rounded-2xl text-white text-sm font-mono focus:outline-none transition-all"
                                    style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                                    placeholder="Masalan: 9780001" />
                                <div v-if="form.errors.barcode" class="text-red-400 text-xs mt-1.5">{{ form.errors.barcode }}</div>
                            </div>

                            <!-- Price + Stock row -->
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-widest mb-2" style="color: rgba(255,255,255,0.35);">Narx (so'm) *</label>
                                    <input v-model="form.price" type="number" required min="0"
                                        class="w-full px-4 py-3.5 rounded-2xl text-white text-sm focus:outline-none transition-all"
                                        style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                                        placeholder="45000" />
                                    <div v-if="form.errors.price" class="text-red-400 text-xs mt-1.5">{{ form.errors.price }}</div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-widest mb-2" style="color: rgba(255,255,255,0.35);">Zaxira (dona) *</label>
                                    <input v-model="form.stock" type="number" required min="0"
                                        class="w-full px-4 py-3.5 rounded-2xl text-white text-sm focus:outline-none transition-all"
                                        style="background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.1);"
                                        placeholder="10" />
                                    <div v-if="form.errors.stock" class="text-red-400 text-xs mt-1.5">{{ form.errors.stock }}</div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex gap-3 pt-2">
                                <button type="button" @click="closeModal"
                                    class="flex-1 py-3.5 rounded-2xl font-bold text-sm transition-all"
                                    style="background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.5);">
                                    Bekor qilish
                                </button>
                                <button type="submit" :disabled="form.processing"
                                    class="flex-1 py-3.5 rounded-2xl font-bold text-white text-sm transition-all active:scale-95 disabled:opacity-50"
                                    style="background: linear-gradient(135deg, #e94560, #533483);">
                                    {{ editingBook ? 'Saqlash' : 'Qo\'shish' }}
                                </button>
                            </div>
                        </form>
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
