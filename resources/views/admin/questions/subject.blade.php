@extends('layouts.admin')

@section('title', $subject->name . ' — Bazalar')

@section('content')

{{-- Breadcrumb --}}
<div class="flex items-center gap-2 text-sm text-gray-400 mb-6">
    <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600 transition">Fanlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-700 font-semibold">{{ $subject->name }}</span>
</div>

@if(session('success'))
<div class="bg-emerald-50 border border-emerald-100 text-emerald-700 rounded-2xl px-5 py-3 mb-6 flex items-center gap-3 animate-fade-in shadow-sm">
    <div class="w-8 h-8 rounded-full bg-emerald-500/10 flex items-center justify-center">
        <i class="fas fa-check text-emerald-500"></i>
    </div>
    <span class="font-medium text-sm">{{ session('success') }}</span>
</div>
@endif

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
                <i class="fas fa-database text-[10px]"></i>
                {{ $bazalar->count() }} ta ma'lumotlar bazasi
            </p>
        </div>
    </div>
    @if(auth()->user()->hasPermission('questions.create'))
    <button onclick="openModal('addModal')"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-2xl transition shadow-lg hover:shadow-indigo-200/50">
        <i class="fas fa-plus"></i>
        <span>Yangi baza</span>
    </button>
    @endif
</div>

{{-- Bazalar Grid --}}
@if($bazalar->isEmpty())
<div class="bg-white rounded-[3rem] border border-dashed border-gray-200 py-24 text-center">
    <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
        <i class="fas fa-database text-4xl text-gray-200"></i>
    </div>
    <h5 class="text-xl font-bold text-gray-800 mb-2">Hali bazalar yo'q</h5>
    <p class="text-gray-400 text-sm max-w-xs mx-auto mb-8">Ushbu fan uchun savollar bazasini yarating.</p>
    @if(auth()->user()->hasPermission('questions.create'))
    <button onclick="openModal('addModal')"
            class="inline-flex items-center gap-2 bg-gray-900 text-white font-bold py-4 px-10 rounded-[2rem] shadow-xl hover:bg-indigo-600 transition">
        + Baza yaratish
    </button>
    @endif
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
    @foreach($bazalar as $baza)
    <div class="group bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl hover:border-indigo-100 transition-all duration-300 flex flex-col overflow-hidden">
        <a href="{{ route('admin.questions.baza', [$subject, $baza]) }}"
           class="flex items-center gap-4 p-6 flex-1">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center shrink-0 group-hover:bg-indigo-600 transition-colors duration-300">
                <i class="fas fa-folder-open text-indigo-400 group-hover:text-white transition-colors"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-800 group-hover:text-indigo-600 transition-colors text-base truncate">{{ $baza->name }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="text-[10px] font-black uppercase text-gray-400 tracking-wider">{{ $baza->questions_count }} ta savol</span>
                </div>
            </div>
            <i class="fas fa-arrow-right text-gray-200 group-hover:text-indigo-400 group-hover:translate-x-1 transition-all text-sm shrink-0"></i>
        </a>
        
        {{-- Actions --}}
        <div class="bg-gray-50/50 px-5 py-3.5 flex items-center justify-between border-t border-gray-50">
            @if(auth()->user()->hasPermission('questions.edit'))
            <button onclick="openEditModal({{ $baza->id }}, '{{ addslashes($baza->name) }}')"
                    class="text-[10px] font-black uppercase tracking-wider text-gray-400 hover:text-indigo-600 transition flex items-center gap-1.5">
                <i class="fas fa-edit"></i> Tahrirlash
            </button>
            @endif
            @if(auth()->user()->hasPermission('questions.delete'))
            <button onclick="openDeleteModal({{ $baza->id }}, '{{ addslashes($baza->name) }}')"
                    class="w-8 h-8 rounded-lg text-gray-300 hover:text-red-600 hover:bg-red-50 transition flex items-center justify-center" title="O'chirish">
                <i class="fas fa-trash-alt text-xs"></i>
            </button>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ═══════════════ ADD MODAL ═══════════════ --}}
<div id="addModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeModal('addModal')"></div>
    <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-8 z-10 animate-scale-up">
        <div class="flex items-center justify-between mb-8">
            <h4 class="text-2xl font-black text-gray-800 tracking-tight">Yangi baza</h4>
            <button onclick="closeModal('addModal')" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.bazalar.store', $subject) }}">
            @csrf
            <input type="hidden" name="parent_id" value="">
            <div class="mb-8">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-3 ml-1">Baza nomi</label>
                <input type="text" name="name" placeholder="masalan: Qonun hujjatlari" required autofocus
                       class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-base outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-400 focus:bg-white transition-all">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('addModal')"
                        class="flex-1 py-4 rounded-2xl text-sm font-bold text-gray-500 hover:bg-gray-50 transition">
                    Bekor qilish
                </button>
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black text-sm transition shadow-lg shadow-indigo-100">
                    Qo'shish
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════ EDIT MODAL ═══════════════ --}}
<div id="editModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeModal('editModal')"></div>
    <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md p-8 z-10 animate-scale-up">
        <div class="flex items-center justify-between mb-8">
            <h4 class="text-2xl font-black text-gray-800 tracking-tight">Tahrirlash</h4>
            <button onclick="closeModal('editModal')" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 transition">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="mb-8">
                <label class="block text-xs font-black uppercase tracking-widest text-gray-400 mb-3 ml-1">Baza nomi</label>
                <input type="text" name="name" id="editName" required
                       class="w-full px-6 py-4 bg-gray-50 border border-gray-100 rounded-2xl text-base outline-none focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-400 focus:bg-white transition-all">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('editModal')"
                        class="flex-1 py-4 rounded-2xl text-sm font-bold text-gray-500 hover:bg-gray-50 transition">
                    Bekor qilish
                </button>
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-4 rounded-2xl font-black text-sm transition shadow-lg shadow-indigo-100">
                    Saqlash
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════ DELETE MODAL ═══════════════ --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeModal('deleteModal')"></div>
    <div class="relative bg-white rounded-[2.5rem] shadow-2xl w-full max-w-sm p-8 z-10 animate-scale-up">
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-red-50 rounded-[2rem] flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-trash-alt text-red-500 text-3xl"></i>
            </div>
            <h4 class="text-2xl font-black text-gray-800 tracking-tight">O'chirish</h4>
            <p class="text-sm text-gray-500 mt-2 px-4">"<span id="deleteBazaName" class="font-bold text-gray-900"></span>" bazasi o'chirilsinmi?</p>
            <div class="mt-6 flex items-center justify-center gap-2 py-1.5 px-3 bg-red-50 rounded-full text-[10px] text-red-500 font-extrabold uppercase tracking-widest inline-block mx-auto">
                <i class="fas fa-exclamation-triangle"></i> Qaytarib bo'lmaydi
            </div>
        </div>
        <form method="POST" id="deleteForm">
            @csrf @method('DELETE')
            <div class="grid grid-cols-2 gap-3">
                <button type="button" onclick="closeModal('deleteModal')"
                        class="py-4 rounded-[1.5rem] text-sm font-bold text-gray-500 hover:bg-gray-50 transition-all border border-gray-100">
                    Bekor
                </button>
                <button type="submit"
                        class="bg-red-500 hover:bg-red-600 text-white py-4 rounded-[1.5rem] font-black text-sm transition-all shadow-lg shadow-red-100">
                    O'chirilsin
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    const input = document.querySelector('#' + id + ' input[name=name]');
    if (input) setTimeout(() => input.focus(), 150);
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.body.style.overflow = '';
}
function openEditModal(bazaId, bazaName) {
    document.getElementById('editName').value = bazaName;
    document.getElementById('editForm').action =
        '{{ url("admin/subjects/" . $subject->id . "/bazalar") }}/' + bazaId;
    openModal('editModal');
}
function openDeleteModal(bazaId, bazaName) {
    document.getElementById('deleteBazaName').textContent = bazaName;
    document.getElementById('deleteForm').action =
        '{{ url("admin/subjects/" . $subject->id . "/bazalar") }}/' + bazaId;
    openModal('deleteModal');
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['addModal', 'editModal', 'deleteModal'].forEach(id => closeModal(id));
});
</script>

<style>
@keyframes fade-in { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fade-in 0.3s ease-out; }
@keyframes scale-up { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
.animate-scale-up { animation: scale-up 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
</style>
@endsection
