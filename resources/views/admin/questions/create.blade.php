@extends('layouts.admin')

@section('title', 'Yangi savol qo\'shish')

@section('content')
<!-- KaTeX for math rendering in preview -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
<script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>

<div class="max-w-4xl mx-auto">
    <form action="{{ route('admin.questions.store') }}" method="POST" id="questionForm" enctype="multipart/form-data">
        @csrf
        {{-- Hidden baza_id if coming from a baza page --}}
        @if(request('baza_id'))
        <input type="hidden" name="baza_id" value="{{ request('baza_id') }}">
        @endif
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Side: Basic Info -->
            <div class="md:col-span-1 space-y-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Fan</label>
                        @if(request('subject_id'))
                            @php $preSubject = $subjects->find(request('subject_id')); @endphp
                            <input type="text" value="{{ $preSubject?->name }}" disabled
                                   class="w-full px-4 py-2 border border-gray-200 bg-gray-50 rounded-lg text-gray-500 cursor-not-allowed">
                            <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                        @else
                            <select name="subject_id" required class="w-full px-4 py-2 border rounded-lg">
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>


                    <input type="hidden" name="type" value="single">


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

                {{-- Image upload --}}
                <div class="bg-white p-6 rounded-xl shadow-sm border">
                    <label class="block text-sm font-bold text-gray-700 mb-3">
                        <i class="fas fa-image text-indigo-400 mr-1"></i> Rasm (ixtiyoriy)
                    </label>
                    <div id="imageDropArea"
                         onclick="document.getElementById('imageInput').click()"
                         class="border-2 border-dashed border-gray-200 rounded-xl p-6 text-center cursor-pointer hover:border-indigo-400 hover:bg-indigo-50/30 transition-all group">
                        <input type="file" name="image" id="imageInput" accept="image/*" class="hidden">
                        <div id="imagePlaceholder">
                            <i class="fas fa-cloud-upload-alt text-3xl text-gray-300 group-hover:text-indigo-400 transition mb-2 block"></i>
                            <p class="text-xs text-gray-400 font-semibold">Rasm yuklash uchun bosing</p>
                            <p class="text-[10px] text-gray-300 mt-1">PNG, JPG, WEBP — maks. 5MB</p>
                        </div>
                        <img id="imagePreview" src="" alt="preview" class="hidden max-h-48 mx-auto rounded-lg object-contain">
                    </div>
                    <button type="button" id="removeImageBtn"
                            class="hidden mt-2 text-[10px] font-bold text-red-400 hover:text-red-600 transition flex items-center gap-1 mx-auto">
                        <i class="fas fa-times"></i> Rasmni olib tashlash
                    </button>
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
    // ─── LaTeX Preview ───────────────────────────
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

    // ─── Image Upload Preview ─────────────────────
    const imageInput    = document.getElementById('imageInput');
    const imagePreview  = document.getElementById('imagePreview');
    const imagePlaceholder = document.getElementById('imagePlaceholder');
    const removeImageBtn   = document.getElementById('removeImageBtn');

    imageInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            imagePreview.src = e.target.result;
            imagePreview.classList.remove('hidden');
            imagePlaceholder.classList.add('hidden');
            removeImageBtn.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    });

    removeImageBtn.addEventListener('click', function () {
        imageInput.value = '';
        imagePreview.src = '';
        imagePreview.classList.add('hidden');
        imagePlaceholder.classList.remove('hidden');
        removeImageBtn.classList.add('hidden');
    });
</script>
@endsection
