@extends('layouts.admin')

@section('title', 'Testni tahrirlash')

@section('content')
<div class="max-w-xl mx-auto">

    <div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
        <a href="{{ route('admin.quizzes.index') }}" class="hover:text-indigo-600 transition">Testlar</a>
        <i class="fas fa-chevron-right text-[10px]"></i>
        <span class="text-gray-700 font-semibold">Tahrirlash</span>
    </div>

    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-8">
            <div class="flex items-center gap-4 mb-8">
                <div class="w-12 h-12 bg-indigo-50 rounded-2xl flex items-center justify-center text-xl text-indigo-500">
                    <i class="fas fa-edit"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Testni tahrirlash</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Asosiy ma'lumotlarni o'zgartiring</p>
                </div>
            </div>

            <form action="{{ route('admin.quizzes.update', $quiz) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Fan --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Fan</label>
                    <select name="subject_id" required
                            class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all">
                        @foreach($subjects as $s)
                            <option value="{{ $s->id }}" {{ $quiz->subject_id == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Test nomi --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Test nomi</label>
                    <input type="text" name="title" value="{{ $quiz->title }}" required
                           class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all"
                           placeholder="Test nomi">
                </div>

                {{-- Vaqt + Ball --}}
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-2">Vaqt (daqiqa)</label>
                        <input type="number" name="time_limit" value="{{ $quiz->time_limit }}" min="1" required
                               class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-2">O'tish bali (%)</label>
                        <input type="number" name="pass_score" value="{{ $quiz->pass_score }}" min="1" max="100" required
                               class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all">
                    </div>
                </div>

                {{-- Boshlanish + Tugash --}}
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-2">Boshlanish vaqti</label>
                        <input type="datetime-local" name="starts_at"
                               value="{{ $quiz->starts_at ? $quiz->starts_at->format('Y-m-d\TH:i') : '' }}"
                               class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all">
                        <p class="text-[10px] text-gray-400 mt-1">Bo'sh qolsa — darhol ochiq</p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-2">Tugash vaqti</label>
                        <input type="datetime-local" name="ends_at"
                               value="{{ $quiz->ends_at ? $quiz->ends_at->format('Y-m-d\TH:i') : '' }}"
                               class="w-full px-5 py-3.5 bg-gray-50 border border-gray-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all">
                        <p class="text-[10px] text-gray-400 mt-1">Bo'sh qolsa — vaqtsiz</p>
                    </div>
                </div>

                {{-- Access code --}}
                <div class="mb-8 p-6 bg-indigo-50/50 rounded-[2rem] border border-indigo-100/50">
                    <label class="block text-xs font-black uppercase tracking-widest text-indigo-400 mb-3 ml-1">Kirish kodi (Access ID)</label>
                    <input type="text" name="access_code" value="{{ $quiz->access_code }}"
                           class="w-full px-6 py-4 bg-white border border-indigo-100 rounded-2xl text-base font-mono outline-none focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-400 transition-all"
                           placeholder="Bo'sh qolsa — ommaviy">
                    <p class="text-[10px] text-indigo-300 mt-2 ml-1 font-bold uppercase tracking-tighter">Faqat ID orqali kirish mumkin bo'ladi</p>
                </div>

                <div class="flex gap-4">
                    <a href="{{ url()->previous() }}"
                       class="flex-1 text-center py-4 rounded-2xl border border-gray-100 text-sm font-bold text-gray-400 hover:bg-gray-50 transition-all">
                        Bekor qilish
                    </a>
                    <button type="submit"
                            class="flex-[2] bg-indigo-600 hover:bg-indigo-700 text-white font-black py-4 rounded-2xl shadow-lg shadow-indigo-100 transition-all">
                        O'zgarishlarni saqlash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
