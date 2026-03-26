<script setup>
import BookstoreLayout from '@/Layouts/BookstoreLayout.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    todaySales: Number,
    recentSales: Array
});
</script>

<template>
    <Head title="Dashboard" />
    
    <BookstoreLayout>
        <template #header>Bosh sahifa</template>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Stat Card -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 transition-all hover:shadow-md">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-green-50 text-green-600 rounded-2xl text-2xl">💰</div>
                    <span class="text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded-full">+12.5%</span>
                </div>
                <div class="text-sm font-medium text-gray-500 mb-1">Bugungi sotuv</div>
                <div class="text-2xl font-bold text-gray-800">{{ todaySales.toLocaleString() }} so'm</div>
            </div>
            
            <!-- More stats can be added here -->
        </div>

        <!-- Recent Sales Table -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-gray-800 text-lg">Oxirgi sotuvlar</h3>
                <button class="text-indigo-600 text-sm font-semibold hover:underline">Hammasini ko'rish</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 text-gray-400 text-xs font-bold uppercase tracking-widest">
                            <th class="px-6 py-4">Sotuv ID</th>
                            <th class="px-6 py-4">Xodim</th>
                            <th class="px-6 py-4">Summa</th>
                            <th class="px-6 py-4">To'lov</th>
                            <th class="px-6 py-4">Vaqt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="sale in recentSales" :key="sale.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-medium text-gray-700">#{{ sale.id }}</td>
                            <td class="px-6 py-4 text-gray-600">{{ sale.user.name }}</td>
                            <td class="px-6 py-4 font-bold text-gray-800">{{ sale.total_amount.toLocaleString() }} so'm</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-blue-50 text-blue-600 rounded-full text-xs font-bold uppercase tracking-wider">
                                    {{ sale.payment_method }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-sm">
                                {{ new Date(sale.created_at).toLocaleTimeString('uz-UZ') }}
                            </td>
                        </tr>
                        <tr v-if="recentSales.length === 0">
                            <td colspan="5" class="px-6 py-10 text-center text-gray-400 italic">
                                Bugun hali sotuvlar amalga oshirilmadi.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </BookstoreLayout>
</template>
