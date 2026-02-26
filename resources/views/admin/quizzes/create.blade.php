@extends('layouts.admin')

@section('title', 'Yangi test yaratish')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-xl shadow-md overflow-hidden border">
        <div class="px-8 py-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Test ma'lumotlarini kiriting</h3>
            <form action="{{ route('admin.quizzes.store') }}" method="POST">
                @csrf
                <div class="mb-5">
                    <label for="subject_id" class="block text-sm font-medium text-gray-700 mb-2">Fan</label>
                    <select name="subject_id" id="subject_id" required 
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-5">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Test nomi</label>
                    <input type="text" name="title" id="title" required 
                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition"
                           placeholder="Masalan: 1-chorak yakuniy testi">
                </div>

                <div class="grid grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="time_limit" class="block text-sm font-medium text-gray-700 mb-2">Vaqt limiti (daqiqa)</label>
                        <input type="number" name="time_limit" id="time_limit" value="30" min="1" required 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                    <div>
                        <label for="pass_score" class="block text-sm font-medium text-gray-700 mb-2">O'tish bali (%)</label>
                        <input type="number" name="pass_score" id="pass_score" value="70" min="1" max="100" required 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition">
                    </div>
                </div>

                <div class="mb-8">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_random" value="0">
                        <input type="checkbox" name="is_random" value="1" checked class="sr-only peer">
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
