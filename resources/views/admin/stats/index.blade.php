@extends('layouts.admin')

@section('title', 'Test Statistikasi')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-2xl font-bold text-gray-800">Test Statistikasi</h3>
            <p class="text-gray-500 mt-1">Barcha testlar bo'yicha umumiy holat</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-white p-6 rounded-[32px] shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600">
                <i class="fas fa-play text-xl"></i>
            </div>
            <div>
                <p class="text-[10px] uppercase font-black text-indigo-400 tracking-widest leading-none mb-1">Jami urinishlar</p>
                <p class="text-2xl font-black text-indigo-900 leading-none">{{ \App\Models\QuizAttempt::count() }} ta</p>
            </div>
        </div>
        <!-- Add more global stats if needed -->
    </div>

    <div class="bg-white rounded-[32px] shadow-xl shadow-indigo-100/20 border border-white overflow-hidden">
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-8 py-5 text-xs uppercase font-black text-slate-400 tracking-widest">Test Nomi</th>
                    <th class="px-8 py-5 text-xs uppercase font-black text-slate-400 tracking-widest">Fan</th>
                    <th class="px-8 py-5 text-xs uppercase font-black text-slate-400 tracking-widest text-center">Urinishlar</th>
                    <th class="px-8 py-5 text-xs uppercase font-black text-slate-400 tracking-widest text-right">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($quizzes as $quiz)
                <tr class="hover:bg-slate-50/50 transition-colors">
                    <td class="px-8 py-5">
                        <div class="font-bold text-slate-800">{{ $quiz->title }}</div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">{{ $quiz->access_code ?? 'ID-siz' }}</div>
                    </td>
                    <td class="px-8 py-5 text-sm font-semibold text-slate-600">
                        {{ $quiz->subject->name }}
                    </td>
                    <td class="px-8 py-5 text-center">
                        <span class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-600 rounded-full text-xs font-black">
                            {{ $quiz->attempts_count }}
                        </span>
                    </td>
                    <td class="px-8 py-5 text-right">
                        <a href="{{ route('admin.stats.show', $quiz->id) }}" class="text-indigo-600 hover:text-indigo-800 font-bold text-sm">Batafsil <i class="fas fa-arrow-right ml-1 text-[10px]"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-8 border-t border-slate-50">
            {{ $quizzes->links() }}
        </div>
    </div>
</div>
@endsection
