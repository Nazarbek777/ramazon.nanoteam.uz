@extends('layouts.admin')

@section('title', 'Fanni tahrirlash')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-md overflow-hidden border">
        <div class="px-8 py-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Fan ma'lumotlarini tahrirlash</h3>
            <form action="{{ route('admin.subjects.update', $subject->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-5">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Fan nomi</label>
                    <input type="text" name="name" id="name" value="{{ $subject->name }}" required 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                           placeholder="Masalan: Matematika">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-5">
                    <label for="parent_id" class="block text-sm font-medium text-gray-700 mb-2">Yuqori fan (Parent Subject)</label>
                    <select name="parent_id" id="parent_id" class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        <option value="">— Yuqori fanni tanlang (ixtiyoriy) —</option>
                        @foreach($subjects as $parent)
                            <option value="{{ $parent->id }}" {{ $subject->parent_id == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-8">
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">Icon (FontAwesome class)</label>
                    <input type="text" name="icon" id="icon" value="{{ $subject->icon }}"
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                           placeholder="fas fa-calculator">
                    <p class="text-gray-400 text-xs mt-2">Masalan: fas fa-square-root-alt, fas fa-flask</p>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('admin.subjects.index') }}" class="text-gray-600 hover:text-gray-800 font-medium">Bekor qilish</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-8 rounded-lg shadow transition">
                        Yangilash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
