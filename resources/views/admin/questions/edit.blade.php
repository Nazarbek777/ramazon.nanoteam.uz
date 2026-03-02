@extends('layouts.admin')

@section('title', 'Savolni tahrirlash')

@section('content')
<!-- KaTeX for math rendering -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>

<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.questions.update', $question) }}" method="POST" id="questionForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Side: Basic Info -->
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Fan</label>
                        <select name="subject_id" required class="w-full px-4 py-2 border rounded-lg">
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ $question->subject_id == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Turi</label>
                        <select name="type" class="w-full px-4 py-2 border rounded-lg">
                            <option value="single" {{ $question->type == 'single' ? 'selected' : '' }}>Yagona tanlov</option>
                            <option value="multiple" {{ $question->type == 'multiple' ? 'selected' : '' }}>Ko'p tanlov</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Ball</label>
                        <input type="number" name="points" value="{{ $question->points ?? 1 }}" min="1" class="w-full px-4 py-2 border rounded-lg">
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl shadow-lg hover:bg-indigo-700 transition">
                        Saqlash
                    </button>
                    <a href="{{ route('admin.questions.index') }}" class="w-full text-center text-gray-600 hover:text-gray-800 font-medium py-2">
                        Bekor qilish
                    </a>
                </div>
            </div>

            <!-- Right Side: Content & Options -->
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Savol matni (LaTeX qo'llab-quvvatlaydi)</label>
                    <textarea name="content" id="questionContent" rows="4" required
                              class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none"
                              placeholder="Masalan: $x^2 + y^2 = r^2$...">{{ $question->content }}</textarea>
                    <div id="preview" class="mt-4 p-4 bg-gray-50 border rounded-lg min-h-[50px] text-gray-800"></div>
                </div>

                {{-- Image upload/edit --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <label class="block text-sm font-bold text-gray-700 mb-3">
                        <i class="fas fa-image text-indigo-400 mr-1"></i> Rasm (ixtiyoriy)
                    </label>
                    <div id="imageDropArea"
                         onclick="document.getElementById('imageInput').click()"
                         class="border-2 border-dashed border-gray-200 rounded-xl p-4 text-center cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 transition-all group">
                        <input type="file" name="image" id="imageInput" accept="image/*" class="hidden">
                        <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

                        @if($question->image)
                        {{-- Existing image --}}
                        <div id="imagePlaceholder" class="hidden">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 group-hover:text-indigo-400 transition mb-2 block"></i>
                            <p class="text-xs text-gray-400 font-semibold">Yangi rasm yuklash uchun bosing</p>
                        </div>
                        <img id="imagePreview" src="{{ Storage::url($question->image) }}"
                             alt="rasm" class="max-h-48 mx-auto rounded-lg object-contain">
                        @else
                        <div id="imagePlaceholder">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 group-hover:text-indigo-400 transition mb-2 block"></i>
                            <p class="text-xs text-gray-400 font-semibold">Rasm yuklash uchun bosing</p>
                            <p class="text-[10px] text-gray-300 mt-1">PNG, JPG, WEBP — maks. 5MB</p>
                        </div>
                        <img id="imagePreview" src="" alt="preview" class="hidden max-h-48 mx-auto rounded-lg object-contain">
                        @endif
                    </div>
                    <button type="button" id="removeImageBtn"
                            class="{{ $question->image ? '' : 'hidden' }} mt-2 text-[10px] font-bold text-red-400 hover:text-red-600 transition flex items-center gap-1 mx-auto">
                        <i class="fas fa-times"></i> Rasmni o'chirish
                    </button>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <label class="block text-sm font-bold text-gray-700 mb-4">Javob variantlari</label>
                    <div id="optionsContainer" class="space-y-4">
                        @foreach($question->options as $index => $option)
                        <div class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 transition">
                            <input type="radio" name="correct_option" value="{{ $index }}" {{ $option->is_correct ? 'checked' : '' }} class="mt-3 w-5 h-5 text-indigo-600">
                            <div class="flex-1">
                                <input type="text" name="options[{{ $index }}][content]" value="{{ $option->content }}" required
                                       class="w-full px-3 py-2 border-b focus:border-indigo-500 outline-none bg-transparent"
                                       placeholder="Variant {{ $index + 1 }}...">
                            </div>
                        </div>
                        @endforeach

                        {{-- If fewer than 4 options, pad with empty ones --}}
                        @for($i = count($question->options); $i < 4; $i++)
                        <div class="flex items-start space-x-3 p-3 border rounded-lg hover:bg-gray-50 transition">
                            <input type="radio" name="correct_option" value="{{ $i }}" class="mt-3 w-5 h-5 text-indigo-600">
                            <div class="flex-1">
                                <input type="text" name="options[{{ $i }}][content]"
                                       class="w-full px-3 py-2 border-b focus:border-indigo-500 outline-none bg-transparent"
                                       placeholder="Variant {{ $i + 1 }}...">
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
    // ─── LaTeX Preview ───────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        const textarea = document.getElementById('questionContent');
        const preview = document.getElementById('preview');

        // Initial preview
        renderPreview(textarea.value);
        textarea.addEventListener('input', function() { renderPreview(this.value); });

        function renderPreview(text) {
            preview.innerHTML = text.replace(/\$(.*?)\$/g, (match, formula) => {
                try {
                    return katex.renderToString(formula, { throwOnError: false });
                } catch (e) { return match; }
            });
        }
    });

    // ─── Image Upload Preview ─────────────────────
    const imageInput       = document.getElementById('imageInput');
    const imagePreview     = document.getElementById('imagePreview');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    const removeImageBtn   = document.getElementById('removeImageBtn');
    const removeImageFlag  = document.getElementById('removeImageFlag');

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        removeImageFlag.value = '0';
        const reader = new FileReader();
        reader.onload = e => {
            imagePreview.src = e.target.result;
            imagePreview.classList.remove('hidden');
            imagePlaceholder.classList.add('hidden');
            removeImageBtn.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });

    removeImageBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        imageInput.value = '';
        imagePreview.src = '';
        imagePreview.classList.add('hidden');
        imagePlaceholder.classList.remove('hidden');
        removeImageBtn.classList.add('hidden');
        removeImageFlag.value = '1'; // tell controller to delete old image
    });
</script>
@endsection
