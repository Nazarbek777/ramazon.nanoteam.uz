@extends('layouts.admin')

@section('title', $subject->name . ' — Bazalar')

@section('content')
<div class="flex items-center gap-2 text-sm text-gray-400 mb-5">
    <a href="{{ route('admin.subjects.index') }}" class="hover:text-indigo-600">Fanlar</a>
    <i class="fas fa-chevron-right text-[10px]"></i>
    <span class="text-gray-700 font-semibold">{{ $subject->name }}</span>
</div>

@if(session('success'))
<div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 mb-4 text-sm flex items-center gap-2">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center shrink-0">
            @if($subject->icon && str_starts_with($subject->icon, 'fa'))
                <i class="{{ $subject->icon }} text-indigo-500 text-xl"></i>
            @else
                <span class="text-xl">{{ $subject->icon ?? '📚' }}</span>
            @endif
        </div>
        <div>
            <h3 class="text-xl font-bold text-gray-800">{{ $subject->name }}</h3>
            <p class="text-sm text-gray-400">{{ $bazalar->count() }} ta baza</p>
        </div>
    </div>
    @if(auth()->user()->hasPermission('questions.create'))
    <button onclick="openModal('addModal')"
            class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow text-sm">
        <i class="fas fa-plus"></i> Baza qo'shish
    </button>
    @endif
</div>

{{-- Bazalar grid --}}
@if($bazalar->isEmpty())
<div class="bg-white rounded-2xl border border-dashed border-gray-200 py-14 text-center text-gray-400">
    <i class="fas fa-database text-4xl block mb-3 text-gray-200"></i>
    <p class="font-semibold text-sm">Bu fanda hali baza yaratilmagan</p>
    @if(auth()->user()->hasPermission('questions.create'))
    <button onclick="openModal('addModal')" class="text-indigo-500 font-semibold hover:underline text-sm mt-2 inline-block">
        + Baza qo'shish
    </button>
    @endif
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($bazalar as $baza)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition group flex flex-col">
        <a href="{{ route('admin.questions.baza', [$subject, $baza]) }}"
           class="flex items-center gap-4 p-5 flex-1">
            <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
                <i class="fas fa-database text-indigo-400"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="font-bold text-gray-800 group-hover:text-indigo-600 transition text-sm truncate">{{ $baza->name }}</p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $baza->questions_count }} ta savol</p>
            </div>
            <i class="fas fa-chevron-right text-gray-300 group-hover:text-indigo-400 transition text-xs shrink-0"></i>
        </a>
        {{-- Actions --}}
        <div class="border-t border-gray-50 px-4 py-2.5 flex items-center gap-2">
            @if(auth()->user()->hasPermission('questions.edit'))
            <button onclick="openEditModal({{ $baza->id }}, '{{ addslashes($baza->name) }}')"
                    class="flex-1 text-xs font-semibold text-gray-500 hover:text-indigo-600 flex items-center justify-center gap-1.5 py-1.5 rounded-lg hover:bg-indigo-50 transition">
                <i class="fas fa-edit"></i> Tahrirlash
            </button>
            @endif
            @if(auth()->user()->hasPermission('questions.delete'))
            <form method="POST" action="{{ route('admin.bazalar.destroy', [$subject, $baza]) }}"
                  onsubmit="return confirm('\"{{ $baza->name }}\" bazasini o\'chirilsinmi?')">
                @csrf @method('DELETE')
                <button type="submit"
                        class="w-8 h-8 rounded-lg text-red-400 hover:text-red-600 hover:bg-red-50 transition flex items-center justify-center text-xs" title="O'chirish">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ═══════════════ ADD MODAL ═══════════════ --}}
<div id="addModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('addModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h4 class="text-lg font-bold text-gray-800">Yangi baza qo'shish</h4>
            <button onclick="closeModal('addModal')" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.bazalar.store', $subject) }}">
            @csrf
            <input type="hidden" name="parent_id" value="">
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-600 mb-2">Baza nomi</label>
                <input type="text" name="name" placeholder="masalan: Asosiy nazariya" required autofocus
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('addModal')"
                        class="flex-1 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                    Bekor qilish
                </button>
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold text-sm transition">
                    <i class="fas fa-plus mr-1"></i> Qo'shish
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ═══════════════ EDIT MODAL ═══════════════ --}}
<div id="editModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeModal('editModal')"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6 z-10">
        <div class="flex items-center justify-between mb-5">
            <h4 class="text-lg font-bold text-gray-800">Bazani tahrirlash</h4>
            <button onclick="closeModal('editModal')" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-600 mb-2">Baza nomi</label>
                <input type="text" name="name" id="editName" required
                       class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('editModal')"
                        class="flex-1 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                    Bekor qilish
                </button>
                <button type="submit"
                        class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-3 rounded-xl font-bold text-sm transition">
                    <i class="fas fa-save mr-1"></i> Saqlash
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
    if (input) setTimeout(() => input.focus(), 50);
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
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal('addModal');
        closeModal('editModal');
    }
});
</script>
@endsection
