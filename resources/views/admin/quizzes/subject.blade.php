@extends('layouts.admin')

@section('title', $subject->name . ' — Testlar')

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.quizzes.index') }}" class="hover:text-indigo-600 transition">Testlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-700 font-semibold">{{ $subject->name }}</span>
</div>

{{-- Header --}}
<div class="flex items-center justify-between mb-8">
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-2xl shadow-sm border border-indigo-100">
            @if($subject->icon && str_starts_with($subject->icon, 'fa'))
                <i class="{{ $subject->icon }} text-indigo-500"></i>
            @else
                <span class="text-xl">{{ $subject->icon ?? '📚' }}</span>
            @endif
        </div>
        <div>
            <h3 class="text-2xl font-black text-gray-800 tracking-tight">{{ $subject->name }}</h3>
            <p class="text-sm font-medium text-gray-400 flex items-center gap-1.5">
                <i class="fas fa-layer-group text-[10px]"></i>
                {{ $quizzes->count() }} ta mavjud test
            </p>
        </div>
    </div>
    @if(auth()->user()->hasPermission('quizzes.create'))
    <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id]) }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl transition shadow-lg hover:shadow-indigo-200/50">
        <i class="fas fa-plus"></i>
        <span>Yangi test</span>
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

{{-- Quizzes Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
    @forelse($quizzes as $quiz)
    @php
        $now = now();
        $isExpired   = $quiz->ends_at && $quiz->ends_at < $now;
        $isNotStarted = $quiz->starts_at && $quiz->starts_at > $now;
    @endphp
    <div class="group bg-white rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-xl hover:border-indigo-100 transition-all duration-300 relative flex flex-col {{ $isExpired ? 'bg-gray-50/50' : '' }}">
        
        {{-- Status Badge --}}
        <div class="absolute top-6 right-6">
            @if($isExpired)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-50 text-red-500 text-[10px] font-black uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                    Tugagan
                </span>
            @elseif($isNotStarted)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 text-amber-600 text-[10px] font-black uppercase tracking-wider">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    Kutilmoqda
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-wider border border-emerald-100">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></span>
                    Faol
                </span>
            @endif
        </div>

        {{-- Icon --}}
        <div class="w-12 h-12 rounded-2xl bg-gray-50 flex items-center justify-center mb-5 group-hover:bg-indigo-50 transition-colors">
            <i class="fas fa-file-invoice text-gray-400 group-hover:text-indigo-500 text-xl transition-colors"></i>
        </div>

        <div class="flex-1">
            <h4 class="text-lg font-bold text-gray-800 group-hover:text-indigo-600 transition-colors leading-tight mb-2 pr-20">{{ $quiz->title }}</h4>
            
            @if($quiz->access_code)
            <div class="flex items-center gap-1.5 mb-4">
                <i class="fas fa-lock text-[10px] text-indigo-400"></i>
                <span class="font-mono text-xs font-bold text-indigo-500 tracking-wider">#{{ $quiz->access_code }}</span>
            </div>
            @else
            <div class="flex items-center gap-1.5 mb-4">
                <i class="fas fa-globe text-[10px] text-emerald-400"></i>
                <span class="text-[10px] font-black uppercase text-emerald-500 tracking-widest">Ommaviy</span>
            </div>
            @endif

            <ul class="space-y-2 mb-6">
                <li class="flex items-center gap-2 text-xs text-gray-400">
                    <i class="far fa-clock w-3.5"></i>
                    <span class="font-semibold text-gray-600">{{ $quiz->time_limit }} daqiqa</span>
                </li>
                <li class="flex items-center gap-2 text-xs text-gray-400">
                    <i class="far fa-check-square w-3.5"></i>
                    <span class="font-semibold text-gray-600">O'tish bali: {{ $quiz->pass_score }}%</span>
                </li>
                <li class="flex items-center gap-2 text-xs text-gray-400">
                    <i class="fas fa-database w-3.5"></i>
                    <span class="font-semibold text-gray-600">{{ $quiz->sources_count }} ta baza ulanishi</span>
                </li>
            </ul>
        </div>

        {{-- Footer Actions --}}
        <div class="flex items-center justify-between pt-5 border-t border-gray-50">
            <a href="{{ route('admin.quizzes.build', $quiz) }}"
               class="flex items-center gap-2 text-xs font-black uppercase tracking-wider text-indigo-600 hover:text-indigo-800 transition py-1">
               <i class="fas fa-cog"></i> Bazalar
            </a>
            
            <div class="flex items-center gap-1">
                @if(auth()->user()->hasPermission('quizzes.edit'))
                <a href="{{ route('admin.quizzes.edit', $quiz) }}"
                   class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition" title="Tahrirlash">
                    <i class="fas fa-edit text-sm"></i>
                </a>
                @endif
                @if(auth()->user()->hasPermission('quizzes.delete'))
                <button onclick="openDeleteModal({{ $quiz->id }}, '{{ addslashes($quiz->title) }}')"
                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-red-600 hover:bg-red-50 transition" title="O'chirish">
                    <i class="fas fa-trash text-sm"></i>
                </button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full bg-white rounded-3xl border border-dashed border-gray-200 py-20 text-center animate-pulse">
        <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="fas fa-file-signature text-4xl text-gray-200"></i>
        </div>
        <h5 class="text-xl font-bold text-gray-800 mb-2">Hali testlar yo'q</h5>
        <p class="text-gray-400 text-sm max-w-xs mx-auto mb-8">Ushbu fan uchun hali birorta ham test yaratilgani yo'q.</p>
        @if(auth()->user()->hasPermission('quizzes.create'))
        <a href="{{ route('admin.quizzes.create', ['subject_id' => $subject->id]) }}"
           class="inline-flex items-center gap-2 bg-gray-900 text-white font-bold py-3 px-8 rounded-2xl shadow-xl hover:bg-indigo-600 transition-all duration-300">
            <i class="fas fa-plus"></i> Birinchi testni yaratish
        </a>
        @endif
    </div>
    @endforelse
</div>

{{-- ═══════════════ DELETE MODAL ═══════════════ --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-[2rem] shadow-2xl w-full max-w-sm p-8 z-10 animate-scale-up">
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-red-50 rounded-3xl flex items-center justify-center mx-auto mb-5 rotate-12">
                <i class="fas fa-trash-alt text-red-500 text-2xl -rotate-12"></i>
            </div>
            <h4 class="text-xl font-bold text-gray-800">Testni o'chirish</h4>
            <p class="text-sm text-gray-500 mt-2">"<span id="deleteTitle" class="font-bold text-gray-900"></span>" testi butunlay o'chirilsinmi?</p>
            <div class="mt-4 p-3 bg-red-50 rounded-xl text-[10px] text-red-500 font-bold uppercase tracking-wider">
                <i class="fas fa-exclamation-triangle mr-1"></i> Bu amalni qaytarib bo'lmaydi
            </div>
        </div>
        <form method="POST" id="deleteForm">
            @csrf @method('DELETE')
            <div class="flex flex-col gap-3">
                <button type="submit"
                        class="w-full bg-red-500 hover:bg-red-600 text-white py-4 rounded-2xl font-black text-sm transition-all shadow-lg shadow-red-200">
                    O'chirilsin
                </button>
                <button type="button" onclick="closeDeleteModal()"
                        class="w-full py-4 rounded-2xl text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all">
                    Bekor qilish
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(id, title) {
    document.getElementById('deleteTitle').textContent = title;
    document.getElementById('deleteForm').action = '{{ url("admin/quizzes") }}/' + id;
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
@keyframes scale-up { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
.animate-scale-up { animation: scale-up 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
</style>
@endsection
