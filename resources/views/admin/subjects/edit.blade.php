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
                    <label class="flex items-center space-x-3 cursor-pointer p-4 bg-gray-50 rounded-xl border border-gray-100 transition hover:bg-gray-100">
                        <input type="checkbox" name="is_active" value="1" {{ $subject->is_active ? 'checked' : '' }} class="w-5 h-5 text-indigo-600 rounded focus:ring-indigo-500 border-gray-300">
                        <span class="text-sm font-semibold text-gray-700">Botda ko'rinsin</span>
                    </label>
                    <p class="text-gray-400 text-[10px] mt-2 ml-8">Agar belgilansa, bu fan va uning testlari botda ko'rinadi.</p>
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
