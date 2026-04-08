<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { debounce } from 'lodash';

const props = defineProps({
    debts: Object,
    filters: Object,
    total_pending_amount: Number
});

const fromDate = ref(props.filters.from);
const toDate = ref(props.filters.to);

const filter = debounce(() => {
    router.get(route('bookstore.debts.index'), {
        from: fromDate.value,
        to: toDate.value
    }, { preserveState: true, replace: true });
}, 500);

watch([fromDate, toDate], () => filter());

const markAsPaid = (id) => {
    if (confirm('Ushbu buyurtma to\'landi deb belgilansinmi?')) {
        router.post(route('bookstore.debts.mark-as-paid', id));
    }
};

const deleteDebt = (id) => {
    if (confirm('Haqiqatan ham o\'chirmoqchimisiz?')) {
        router.delete(route('bookstore.debts.destroy', id));
    }
};

const formatPrice = (p) => new Intl.NumberFormat('uz-UZ').format(p);
</script>

<template>
    <Head title="To'lanmagan Qarzlar" />

    <BookstoreLayout>
        <div class="min-h-screen p-6 space-y-6">
            <!-- Header -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-black text-white tracking-tight">To'lanmagan Qarzlar</h1>
                    <p class="text-white/40 text-sm mt-1">Yetkazib berish yoki naqd qarzga berilgan kitoblar ro'yxati</p>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20">
                        <div class="text-[10px] font-bold text-amber-500/50 uppercase tracking-widest">Umumiy Qarz</div>
                        <div class="text-xl font-black text-amber-500">{{ formatPrice(total_pending_amount) }} so'm</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="p-4 rounded-3xl bg-white/5 border border-white/10 flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-white/30 uppercase tracking-widest">Sana dan:</span>
                    <input type="date" v-model="fromDate" class="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500" />
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-bold text-white/30 uppercase tracking-widest">Sana gacha:</span>
                    <input type="date" v-model="toDate" class="bg-white/5 border border-white/10 rounded-xl px-4 py-2 text-sm text-white focus:outline-none focus:border-indigo-500" />
                </div>
            </div>

            <!-- Debts Table -->
            <div class="bg-white/5 border border-white/10 rounded-3xl overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-white/10 bg-white/5">
                            <th class="px-6 py-4 text-[11px] font-bold text-white/40 uppercase tracking-widest">ID / Sana</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-white/40 uppercase tracking-widest">Mijoz / Telefon</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-white/40 uppercase tracking-widest">Manzil / Izoh</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-white/40 uppercase tracking-widest text-right">Summa</th>
                            <th class="px-6 py-4 text-[11px] font-bold text-white/40 uppercase tracking-widest text-center">Amallar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        <tr v-for="debt in debts.data" :key="debt.id" class="hover:bg-white/[0.02] transition-colors group">
                            <td class="px-6 py-4">
                                <div class="text-white font-bold text-sm">#{{ debt.id }}</div>
                                <div class="text-white/30 text-[10px]">{{ new Date(debt.created_at).toLocaleString() }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-white font-bold text-sm">{{ debt.customer_name || 'Ismsiz mijoz' }}</div>
                                <div class="text-indigo-400 text-xs font-medium">{{ debt.customer_phone || '-' }}</div>
                            </td>
                            <td class="px-6 py-4 max-w-xs">
                                <div class="text-white/60 text-xs truncate">{{ debt.address || '-' }}</div>
                                <div class="text-white/20 text-[10px] italic">Yetkazib berish: {{ debt.is_delivery ? 'Ha' : 'Yo\'q' }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="text-amber-500 font-black">{{ formatPrice(debt.total_amount) }} so'm</div>
                                <div v-if="debt.delivery_fee > 0" class="text-white/30 text-[10px]">Dostavka: {{ formatPrice(debt.delivery_fee) }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click="markAsPaid(debt.id)" 
                                        class="px-4 py-2 bg-green-500/10 hover:bg-green-500/20 border border-green-500/20 text-green-500 text-[11px] font-bold rounded-xl transition-all active:scale-95">
                                        TO'LANDI
                                    </button>
                                    <button @click="deleteDebt(debt.id)"
                                        class="p-2 bg-red-500/10 hover:bg-red-500/20 border border-red-500/20 text-red-500 rounded-xl transition-all active:scale-95">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!debts.data.length">
                            <td colspan="5" class="px-6 py-12 text-center text-white/20 italic">Hozircha to'lanmagan qarzlar yo'q</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="debts.links && debts.links.length > 3" class="flex justify-center mt-6 gap-2">
                <template v-for="(link, k) in debts.links" :key="k">
                    <div v-if="link.url === null" class="px-4 py-2 text-white/20 text-sm border border-white/5 rounded-xl cursor-default" v-html="link.label"></div>
                    <Link v-else :href="link.url" 
                        class="px-4 py-2 text-sm border border-white/10 rounded-xl transition-all hover:bg-white/5" 
                        :class="{'bg-indigo-500 text-white border-indigo-500': link.active, 'text-white/60': !link.active}" 
                        v-html="link.label" />
                </template>
            </div>
        </div>
    </BookstoreLayout>
</template>
