<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted, nextTick, computed } from 'vue';
import axios from 'axios';

const barcodeInput = ref(null);
const currentBarcode = ref('');
const cart = ref([]);
const isProcessing = ref(false);
const error = ref('');

const totalAmount = computed(() => {
    return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

const focusInput = () => {
    barcodeInput.value?.focus();
};

const handleScan = async () => {
    if (!currentBarcode.value) return;
    
    error.value = '';
    try {
        const response = await axios.get(`/api/bookstore/books/${currentBarcode.value}`);
        const book = response.data;
        
        const existingItem = cart.value.find(item => item.id === book.id);
        if (existingItem) {
            existingItem.quantity++;
        } else {
            cart.value.unshift({
                ...book,
                quantity: 1
            });
        }
        
        currentBarcode.value = '';
    } catch (err) {
        error.value = 'Kitob topilmadi: ' + currentBarcode.value;
        currentBarcode.value = '';
    }
};

const removeFromCart = (index) => {
    cart.value.splice(index, 1);
};

const form = useForm({
    items: [],
    discount: 0,
    payment_method: 'cash'
});

const submitSale = () => {
    if (cart.value.length === 0) return;
    
    form.items = cart.value.map(item => ({
        id: item.id,
        quantity: item.quantity
    }));
    
    form.post('/bookstore/sales', {
        onSuccess: () => {
            cart.value = [];
            focusInput();
        }
    });
};

onMounted(() => {
    focusInput();
    // Re-focus input if user clicks anywhere else
    window.addEventListener('click', focusInput);
});
</script>

<template>
    <Head title="POS - Sotuv Paneli" />
    
    <BookstoreLayout>
        <template #header>Sotuv Paneli (POS)</template>
        
        <div class="flex gap-8 h-full">
            <!-- Left Side: Scanner & Cart -->
            <div class="flex-grow flex flex-col space-y-6 overflow-hidden">
                <!-- Scanner Input Box -->
                <div class="bg-indigo-900 p-8 rounded-3xl shadow-xl border border-indigo-800 text-white relative">
                    <div class="absolute top-4 right-6 text-indigo-400 font-mono text-xs uppercase tracking-widest">Scanner Active</div>
                    <label class="block text-sm font-bold mb-4 uppercase tracking-widest text-indigo-300">Barcode Skaner</label>
                    <div class="relative">
                        <input
                            ref="barcodeInput"
                            v-model="currentBarcode"
                            @keydown.enter="handleScan"
                            type="text"
                            class="w-full bg-indigo-950 border-2 border-indigo-700/50 rounded-2xl px-6 py-5 focus:ring-4 focus:ring-indigo-500 focus:outline-none transition-all text-2xl font-mono tracking-tight placeholder:text-indigo-800"
                            placeholder="Skanerlash kutilmoqda..."
                            autocomplete="off"
                        />
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-3xl">📡</div>
                    </div>
                    <div v-if="error" class="mt-4 text-red-300 font-bold animate-pulse">{{ error }}</div>
                </div>

                <!-- Cart Items -->
                <div class="flex-grow bg-white rounded-3xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-bold text-gray-800 uppercase tracking-tight">Savat</h3>
                        <span class="bg-gray-100 px-3 py-1 rounded-full text-xs font-bold text-gray-600">{{ cart.length }} xill</span>
                    </div>
                    
                    <div class="flex-grow overflow-auto p-4 space-y-3">
                        <div v-for="(item, index) in cart" :key="item.id" 
                            class="flex items-center justify-between p-4 rounded-2xl bg-gray-50 border border-gray-100 transition-all hover:border-indigo-200 group">
                            <div class="flex items-center space-x-4">
                                <div class="w-12 h-12 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-xl">📖</div>
                                <div>
                                    <div class="font-bold text-gray-800">{{ item.title }}</div>
                                    <div class="text-xs text-gray-500 font-mono">{{ item.barcode }}</div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-6">
                                <div class="flex items-center space-x-3 bg-white border border-gray-200 rounded-xl p-1">
                                    <button @click="item.quantity > 1 ? item.quantity-- : null" class="w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors">−</button>
                                    <span class="w-8 text-center font-bold text-gray-800">{{ item.quantity }}</span>
                                    <button @click="item.quantity++" class="w-8 h-8 rounded-lg hover:bg-gray-100 transition-colors">+</button>
                                </div>
                                <div class="text-right w-24">
                                    <div class="font-bold text-gray-900">{{ (item.price * item.quantity).toLocaleString() }}</div>
                                    <div class="text-[10px] text-gray-400 leading-none">@{{ item.price.toLocaleString() }}</div>
                                </div>
                                <button @click="removeFromCart(index)" class="text-gray-300 hover:text-red-500 transition-all text-xl opacity-0 group-hover:opacity-100">🗑️</button>
                            </div>
                        </div>
                        
                        <div v-if="cart.length === 0" class="h-full flex flex-col items-center justify-center text-gray-300 space-y-4">
                            <div class="text-6xl grayscale opacity-20">🛒</div>
                            <div class="text-sm font-medium">Savat bo'sh. Skanerlang!</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Summary & Checkout -->
            <div class="w-96 flex-shrink-0 flex flex-col space-y-6">
                <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-8 flex flex-col">
                    <h3 class="font-bold text-gray-800 uppercase tracking-tight mb-8">Hisob-kitob</h3>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between text-gray-600">
                            <span>Jami summa:</span>
                            <span class="font-bold text-gray-800">{{ totalAmount.toLocaleString() }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600 items-center">
                            <span>Chegirma:</span>
                            <input v-model.number="form.discount" type="number" class="w-24 text-right border-b border-gray-200 focus:border-indigo-500 focus:outline-none font-bold text-indigo-600 px-1" />
                        </div>
                        <div class="border-t border-dashed border-gray-100 pt-4 mt-4 flex justify-between items-end">
                            <span class="text-gray-400 font-bold uppercase text-xs tracking-widest">To'lanadigan:</span>
                            <span class="text-4xl font-extrabold text-indigo-600 tracking-tight">{{ (totalAmount - form.discount).toLocaleString() }}</span>
                        </div>
                    </div>

                    <div class="space-y-3 mb-8">
                        <label class="text-xs font-bold text-gray-400 uppercase tracking-widest">To'lov turi</label>
                        <div class="grid grid-cols-2 gap-3">
                            <button @click="form.payment_method = 'cash'" 
                                :class="form.payment_method === 'cash' ? 'bg-indigo-600 text-white shadow-lg border-indigo-600' : 'bg-gray-50 text-gray-600 border-gray-100'"
                                class="p-4 rounded-2xl border font-bold transition-all text-sm flex flex-col items-center uppercase tracking-tight">
                                <span class="text-xl mb-1">💵</span> Naqd
                            </button>
                            <button @click="form.payment_method = 'card'"
                                :class="form.payment_method === 'card' ? 'bg-indigo-600 text-white shadow-lg border-indigo-600' : 'bg-gray-50 text-gray-600 border-gray-100'"
                                class="p-4 rounded-2xl border font-bold transition-all text-sm flex flex-col items-center uppercase tracking-tight">
                                <span class="text-xl mb-1">💳</span> Karta
                            </button>
                        </div>
                    </div>

                    <button
                        @click="submitSale"
                        :disabled="cart.length === 0 || form.processing"
                        class="w-full bg-green-500 hover:bg-green-600 text-white text-xl font-extrabold py-6 rounded-3xl shadow-xl shadow-green-200 transition-all active:scale-95 disabled:grayscale disabled:opacity-50 flex items-center justify-center space-x-3"
                    >
                        <span>SOTUVNI YAKUNLASH</span>
                        <span v-if="form.processing" class="animate-spin text-2xl">🌀</span>
                    </button>
                    
                    <button @click="cart = []; focusInput()" class="mt-4 text-gray-400 hover:text-red-500 font-bold text-sm transition-all uppercase tracking-widest">
                        Tozalash
                    </button>
                </div>
            </div>
        </div>
    </BookstoreLayout>
</template>
