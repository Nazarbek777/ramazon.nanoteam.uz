@extends('layouts.admin')

@section('title', $baza->name . ' — Savollar')

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-6 flex-wrap">
    <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600 transition">Fanlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <a href="{{ route('admin.subjects.show', $subject) }}" class="hover:text-indigo-600 transition">{{ $subject->name }}</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-700 font-semibold">{{ $baza->name }}</span>
</div>

{{-- Header --}}
<div class="flex items-center justify-between mb-8">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-xl shadow-sm border border-indigo-100">
            <i class="fas fa-database text-indigo-500"></i>
        </div>
        <div>
            <h3 class="text-2xl font-black text-gray-800 tracking-tight">{{ $baza->name }}</h3>
            <div class="flex items-center gap-2 mt-0.5">
                <span class="px-2 py-0.5 rounded-lg bg-indigo-100 text-indigo-700 text-[10px] font-black uppercase">{{ $questions->count() }} ta savol</span>
                <span class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">{{ $subject->name }} bazasi</span>
            </div>
        </div>
    </div>
    @if(auth()->user()->hasPermission('questions.create'))
    <a href="{{ route('admin.questions.create', ['subject_id' => $subject->id, 'baza_id' => $baza->id]) }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl transition shadow-lg hover:shadow-indigo-200/50">
        <i class="fas fa-plus"></i>
        <span>Savol qo'shish</span>
    </a>
    @endif
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl px-5 py-3 mb-6 flex items-center gap-3 animate-fade-in shadow-sm">
    <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center">
        <i class="fas fa-check text-emerald-500"></i>
    </div>
    <span class="font-medium text-sm">{{ session('success') }}</span>
</div>
@endif

{{-- All other bazalar for "Move" feature --}}
@php
    $otherBazalar = \App\Models\Baza::where('subject_id', $subject->id)
        ->where('id', '!=', $baza->id)
        ->orderBy('name')
        ->get();
@endphp

{{-- Questions list --}}
<div class="space-y-3 mb-8">
    @forelse($questions as $i => $question)
    <div class="bg-white rounded-[2rem] border border-gray-100 p-5 shadow-sm hover:shadow-md hover:border-indigo-100 transition-all flex items-start gap-4">
        
        {{-- Number index --}}
        <div class="w-10 h-10 rounded-2xl bg-gray-50 flex items-center justify-center shrink-0 border border-gray-100">
            <span class="text-sm font-black text-gray-300">{{ $i+1 }}</span>
        </div>

        {{-- Content --}}
        <div class="flex-1 min-w-0 pt-1">
            <div class="prose prose-sm max-w-none text-gray-700 font-medium leading-relaxed">
                {{ $question->content }}
            </div>
            <div class="flex items-center gap-3 mt-3">
                <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">
                    <i class="far fa-list-alt mr-1"></i> {{ $question->options_count }} ta variant
                </span>
                @if($question->points > 1)
                <span class="text-[10px] font-black uppercase text-amber-500 tracking-wider bg-amber-50 px-1.5 py-0.5 rounded">
                    <i class="fas fa-star mr-1"></i> {{ $question->points }} ball
                </span>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col sm:flex-row items-center gap-2 ml-4 shrink-0 mt-1">
            
            {{-- Move to another baza --}}
            @if($otherBazalar->isNotEmpty() && auth()->user()->hasPermission('questions.edit'))
            <form method="POST" action="{{ route('admin.bazalar.moveQuestion', [$subject, $baza]) }}">
                @csrf
                <input type="hidden" name="question_id" value="{{ $question->id }}">
                <select name="target_baza_id" onchange="this.form.submit()" 
                        class="text-[10px] font-black uppercase tracking-wider border border-gray-100 rounded-xl px-3 py-2 bg-gray-50 text-gray-500 outline-none focus:ring-2 focus:ring-indigo-500/10 focus:border-indigo-400 cursor-pointer hover:bg-white transition-all max-w-[130px]"
                        title="Boshqa bazaga ko'chirish">
                    <option value="">↪ Ko'chirish</option>
                    @foreach($otherBazalar as $target)
                    <option value="{{ $target->id }}">{{ $target->name }}</option>
                    @endforeach
                </select>
            </form>
            @endif

            <div class="flex items-center gap-1">
                @if(auth()->user()->hasPermission('questions.edit'))
                <a href="{{ route('admin.questions.edit', $question) }}"
                   class="w-10 h-10 rounded-xl flex items-center justify-center text-indigo-400 hover:text-indigo-600 hover:bg-indigo-50 transition border border-transparent hover:border-indigo-100" title="Tahrirlash">
                    <i class="fas fa-edit"></i>
                </a>
                @endif
                @if(auth()->user()->hasPermission('questions.delete'))
                <button onclick="openDeleteModal({{ $question->id }}, 'Savol #{{ $i+1 }}')"
                        class="w-10 h-10 rounded-xl flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 transition border border-transparent hover:border-red-100" title="O'chirish">
                    <i class="fas fa-trash"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="bg-white rounded-[3rem] border border-dashed border-gray-200 py-24 text-center">
        <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-question text-4xl text-gray-200"></i>
        </div>
        <h5 class="text-xl font-bold text-gray-800 mb-2">Bu bazada hali savollar yo'q</h5>
        <p class="text-gray-400 text-sm max-w-xs mx-auto mb-8">Yangi savollar qo'shib bazani boyiting.</p>
        @if(auth()->user()->hasPermission('questions.create'))
        <a href="{{ route('admin.questions.create', ['subject_id' => $subject->id, 'baza_id' => $baza->id]) }}"
           class="inline-flex items-center gap-2 bg-indigo-600 text-white font-bold py-4 px-10 rounded-[2rem] shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition">
            <i class="fas fa-plus"></i> Birinchi savolni qo'shish
        </a>
        @endif
    </div>
    @endforelse
</div>

{{-- ═══════════════ DELETE MODAL ═══════════════ --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-sm p-8 z-10 animate-scale-up">
        <div class="text-center mb-10">
            <div class="w-20 h-20 bg-red-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-trash-alt text-red-500 text-3xl"></i>
            </div>
            <h4 class="text-2xl font-black text-gray-800 tracking-tight">Savolni o'chirish</h4>
            <p class="text-sm text-gray-500 mt-2 px-4"><span id="deleteTitle" class="font-bold"></span> o'chirilsinmi?</p>
            <div class="mt-6 flex items-center justify-center gap-2 py-1.5 px-3 bg-red-50 rounded-full text-[10px] text-red-500 font-extrabold uppercase tracking-widest inline-block mx-auto">
                <i class="fas fa-exclamation-triangle"></i> Qaytarib bo'lmaydi
            </div>
        </div>
        <form method="POST" id="deleteForm">
            @csrf @method('DELETE')
            <div class="grid grid-cols-2 gap-3">
                <button type="button" onclick="closeDeleteModal()"
                        class="py-4 rounded-3xl text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all border border-gray-100">
                    Bekor qilish
                </button>
                <button type="submit"
                        class="bg-red-500 hover:bg-red-600 text-white py-4 rounded-3xl font-black text-sm transition-all shadow-lg shadow-red-100">
                    O'chirilsin
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(id, title) {
    document.getElementById('deleteTitle').textContent = title;
    document.getElementById('deleteForm').action = '{{ url("admin/questions") }}/' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });
</script>

<style>
@keyframes fade-in { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fade-in 0.3s ease-out; }
@keyframes scale-up { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
.animate-scale-up { animation: scale-up 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
</style>
@endsection
