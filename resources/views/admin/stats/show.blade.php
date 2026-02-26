@extends('layouts.admin')

@section('title', $quiz->title . ' - Batafsil Statistika')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <a href="{{ route('admin.stats.index') }}" class="text-indigo-600 font-bold text-sm mb-4 inline-flex items-center hover:translate-x-1 transition-transform">
            <i class="fas fa-arrow-left mr-2"></i> Orqaga qaytish
        </a>
        <h3 class="text-3xl font-black text-slate-800 mt-2">{{ $quiz->title }}</h3>
        <p class="text-slate-500 font-medium">Batafsil tahlil va foydalanuvchilar natijalari</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-6 rounded-[32px] shadow-sm border border-white flex flex-col justify-center">
            <span class="text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">Jami urinishlar</span>
            <span class="text-3xl font-black text-slate-800">{{ $stats['total_attempts'] }} ta</span>
        </div>
        <div class="bg-white p-6 rounded-[32px] shadow-sm border border-white flex flex-col justify-center">
            <span class="text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">O'rtacha ball</span>
            <span class="text-3xl font-black text-indigo-600">{{ round($stats['avg_score'], 1) }}%</span>
        </div>
        <div class="bg-white p-6 rounded-[32px] shadow-sm border border-white flex flex-col justify-center">
            <span class="text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">O'tganlar</span>
            <span class="text-3xl font-black text-emerald-600">{{ $stats['passed_count'] }} ta</span>
        </div>
        <div class="bg-white p-6 rounded-[32px] shadow-sm border border-white flex flex-col justify-center">
            <span class="text-[10px] uppercase font-black text-slate-400 tracking-widest mb-2">O'tolmaganlar</span>
            <span class="text-3xl font-black text-rose-600">{{ $stats['total_attempts'] - $stats['passed_count'] }} ta</span>
        </div>
    </div>

    <div class="bg-white rounded-[40px] shadow-xl shadow-indigo-100/20 border border-white overflow-hidden">
        <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
            <h4 class="font-bold text-slate-800">Foydalanuvchilar Ro'yxati</h4>
            <div class="text-[10px] font-black uppercase text-indigo-400 bg-indigo-50 px-3 py-1 rounded-full">Oxirgi natijalar</div>
        </div>
        <table class="w-full text-left">
            <thead>
                <tr class="bg-slate-50/50">
                    <th class="px-8 py-4 text-[10px] uppercase font-black text-slate-400 tracking-widest">Foydalanuvchi</th>
                    <th class="px-8 py-4 text-[10px] uppercase font-black text-slate-400 tracking-widest">Telefon</th>
                    <th class="px-8 py-4 text-[10px] uppercase font-black text-slate-400 tracking-widest text-center">Ball</th>
                    <th class="px-8 py-4 text-[10px] uppercase font-black text-slate-400 tracking-widest text-center">To'g'ri/Jami</th>
                    <th class="px-8 py-4 text-[10px] uppercase font-black text-slate-400 tracking-widest text-right">Vaqt</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($attempts as $attempt)
                <tr>
                    <td class="px-8 py-5">
                        <div class="flex items-center">
                            <img src="https://ui-avatars.com/api/?name={{ $attempt->user->name }}&background=f1f5f9&color=6366f1" class="w-8 h-8 rounded-lg mr-3">
                            <div>
                                <div class="font-bold text-slate-700 leading-none">{{ $attempt->user->name }}</div>
                                <div class="text-[10px] text-slate-400 font-bold mt-1 tracking-wider">{{ $attempt->user->telegram_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-8 py-5 text-sm font-bold text-slate-500">
                        {{ $attempt->user->phone_number ?? '-' }}
                    </td>
                    <td class="px-8 py-5 text-center">
                        <div @class([
                            'inline-flex items-center px-3 py-1 rounded-xl text-xs font-black',
                            'bg-emerald-50 text-emerald-600' => $attempt->score >= $quiz->pass_score,
                            'bg-rose-50 text-rose-600' => $attempt->score < $quiz->pass_score,
                        ])>
                            {{ $attempt->score }}%
                        </div>
                    </td>
                    <td class="px-8 py-5 text-center text-sm font-black text-slate-400">
                        <span class="text-slate-800">{{ $attempt->correct_answers }}</span> / {{ $attempt->total_questions }}
                    </td>
                    <td class="px-8 py-5 text-right text-xs font-bold text-slate-400 uppercase tracking-tighter">
                        {{ $attempt->created_at->format('d.m.Y H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-8 border-t border-slate-50">
            {{ $attempts->links() }}
        </div>
    </div>
</div>
@endsection
