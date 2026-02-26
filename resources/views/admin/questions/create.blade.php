@extends('layouts.admin')

@section('title', 'Yangi savol qo\'shish')

@section('content')
<!-- KaTeX for math rendering in preview -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>

<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.questions.store') }}" method="POST" id="questionForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Side: Basic Info -->
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Fan</label>
                        <select name="subject_id" required class="w-full px-4 py-2 border rounded-lg">
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Turi</label>
                        <select name="type" class="w-full px-4 py-2 border rounded-lg">
                            <option value="single">Yagona tanlov</option>
                            <option value="multiple">Ko'p tanlov</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Ball</label>
                        <input type="number" name="points" value="1" min="1" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                </div>
                
                <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-indigo-700 transition">
                    Saqlash
                </button>
            </div>

            <!-- Right Side: Content & Options -->
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Savol matni (LaTeX qo'llab-quvvatlaydi)</label>
                    <textarea name="content" id="questionContent" rows="4" required 
                              class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none" 
                              placeholder="Masalan: $x^2 + y^2 = r^2$ tenglamasini yeching..."></textarea>
                    <div id="preview" class="mt-4 p-4 bg-gray-50 border rounded-lg min-h-[50px] text-gray-800"></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <label class="block text-sm font-bold text-gray-700 mb-4">Javob variantlari</label>
                    <div id="optionsContainer" class="space-y-4">
                        <!-- Default 4 options -->
                        @for($i=0; $i<4; $i++)
                        <div class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 transition">
                            <input type="radio" name="correct_option" value="{{ $i }}" {{ $i == 0 ? 'checked' : '' }} class="mt-3 w-5 h-5 text-indigo-600">
                            <div class="flex-1">
                                <input type="text" name="options[{{ $i }}][content]" required 
                                       class="w-full px-3 py-2 border-b focus:border-indigo-500 outline-none bg-transparent" 
                                       placeholder="Variant {{ $i+1 }}...">
                            </div>
                        </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('questionContent');
        const preview = document.getElementById('preview');

        textarea.addEventListener('input', function() {
            let text = this.value;
            preview.innerHTML = text.replace(/\$(.*?)\$/g, (match, formula) => {
                try {
                    return katex.renderToString(formula, { throwOnError: false });
                } catch (e) {
                    return match;
                }
            });
        });
    });
</script>
@endsection
