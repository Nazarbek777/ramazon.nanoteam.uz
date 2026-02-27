@extends('layouts.admin')

@section('title', 'Fanlar')

@section('content')

{{-- Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h3 class="text-2xl font-bold text-gray-800">Fanlar</h3>
        <p class="text-sm text-gray-400 mt-0.5">{{ $subjects->total() }} ta fan</p>
    </div>
    <a href="{{ route('admin.subjects.create') }}"
       class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl transition shadow text-sm">
        <i class="fas fa-plus"></i> Yangi fan
    </a>
</div>

{{-- Subject cards --}}
@forelse($subjects as $subject)
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md hover:border-indigo-100 transition mb-3 flex items-center px-5 py-4 gap-4">

    {{-- Icon --}}
    <div class="w-11 h-11 rounded-xl bg-indigo-50 flex items-center justify-center shrink-0">
        @if($subject->icon && str_starts_with($subject->icon, 'fa'))
            <i class="{{ $subject->icon }} text-indigo-500"></i>
        @else
            <span class="text-lg">{{ $subject->icon ?? '📚' }}</span>
        @endif
    </div>

    {{-- Name --}}
    <div class="flex-1 min-w-0">
        <p class="font-bold text-gray-800 text-sm truncate">{{ $subject->name }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $subject->created_at->format('d.m.Y') }}</p>
    </div>

    {{-- Actions --}}
    <div class="flex items-center gap-2 shrink-0">
        <a href="{{ route('admin.subjects.show', $subject) }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-2 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 transition">
            <i class="fas fa-database text-xs"></i> Bazalar
        </a>
        <a href="{{ route('admin.subjects.edit', $subject) }}"
           class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-indigo-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition" title="Tahrirlash">
            <i class="fas fa-edit text-sm"></i>
        </a>
        <button onclick="openDeleteModal({{ $subject->id }}, '{{ addslashes($subject->name) }}')"
                class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-red-400 hover:text-red-600 hover:border-red-200 hover:bg-red-50 transition" title="O'chirish">
            <i class="fas fa-trash text-sm"></i>
        </button>
    </div>
</div>
@empty
<div class="bg-white rounded-2xl border border-dashed border-gray-200 py-14 text-center text-gray-400">
    <i class="fas fa-book-open text-4xl block mb-3 text-gray-200"></i>
    <p class="font-semibold text-sm">Hali fan qo'shilmagan</p>
    <a href="{{ route('admin.subjects.create') }}" class="text-indigo-500 font-semibold hover:underline text-sm mt-2 inline-block">
        + Yangi fan qo'shish
    </a>
</div>
@endforelse

{{-- Pagination --}}
@if($subjects->hasPages())
<div class="mt-4">{{ $subjects->links() }}</div>
@endif

{{-- Delete Modal --}}
<div id="deleteModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" onclick="closeDeleteModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 z-10">
        <div class="text-center mb-5">
            <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-trash text-red-500 text-xl"></i>
            </div>
            <h4 class="text-lg font-bold text-gray-800">Fanni o'chirish</h4>
            <p class="text-sm text-gray-500 mt-1">"<span id="deleteSubjectName" class="font-semibold text-gray-700"></span>" o'chirilsinmi?</p>
            <p class="text-xs text-red-400 mt-2">Bu amal qaytarib bo'lmaydi.</p>
        </div>
        <form method="POST" id="deleteForm">
            @csrf @method('DELETE')
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                    Bekor qilish
                </button>
                <button type="submit"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-bold text-sm transition">
                    Ha, o'chirish
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openDeleteModal(id, name) {
    document.getElementById('deleteSubjectName').textContent = name;
    document.getElementById('deleteForm').action = '{{ url("admin/subjects") }}/' + id;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDeleteModal(); });
</script>
@endsection
