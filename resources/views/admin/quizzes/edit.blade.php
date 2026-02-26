@extends('layouts.admin')

@section('title', 'Testni tahrirlash')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-md overflow-hidden border">
        <div class="px-8 py-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Test ma'lumotlarini o'zgartirish</h3>
            <form action="{{ route('admin.quizzes.update', $quiz) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-5">
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">Fan</label>
                    <select name="subject_id" id="subject_id" required 
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ $quiz->subject_id == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Test nomi</label>
                    <input type="text" name="title" id="title" value="{{ $quiz->title }}" required 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="time_limit" class="block text-sm font-medium text-gray-700 mb-2">Vaqt limiti (daqiqa)</label>
                        <input type="number" name="time_limit" id="time_limit" value="{{ $quiz->time_limit }}" min="1" required 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                    <div>
                        <label for="pass_score" class="block text-sm font-medium text-gray-700 mb-2">O'tish bali (%)</label>
                        <input type="number" name="pass_score" id="pass_score" value="{{ $quiz->pass_score }}" min="1" max="100" required 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                </div>

                <div class="mb-8">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_random" value="0">
                        <input type="checkbox" name="is_random" value="1" {{ $quiz->is_random ? 'checked' : '' }} class="sr-only peer">
                        <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                        <span class="ms-3 text-sm font-medium text-gray-700">Savollarni aralash ko'rsatish</span>
                    </label>
                </div>

                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('admin.quizzes.index') }}" class="text-gray-600 hover:text-gray-800 font-medium">Bekor qilish</a>
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-8 rounded-lg shadow transition">
                        Saqlash
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
