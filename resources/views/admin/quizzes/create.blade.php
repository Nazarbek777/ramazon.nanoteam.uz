@extends('layouts.admin')

@section('title', 'Yangi test yaratish')

@section('content')
<div class="max-w-xl mx-auto">

    {{-- Step indicator --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center text-sm font-black">1</div>
            <span class="text-sm font-bold text-gray-800">Test ma'lumotlari</span>
        </div>
        <div class="flex-1 h-0.5 bg-gray-200 rounded-full">
            <div class="h-full w-0 bg-indigo-600 rounded-full"></div>
        </div>
        <div class="flex items-center gap-2 opacity-40">
            <div class="w-8 h-8 rounded-full border-2 border-gray-300 text-gray-400 flex items-center justify-center text-sm font-black">2</div>
            <span class="text-sm font-semibold text-gray-400">Bazalar tanlash</span>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6">
            <h3 class="text-xl font-bold text-gray-800 mb-6">Yangi test</h3>
            <form action="{{ route('admin.quizzes.store') }}" method="POST">
                @csrf

                {{-- Fan --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Fan</label>
                    @if(request('subject_id'))
                        @php $preSubject = $subjects->find(request('subject_id')); @endphp
                        <input type="text" value="{{ $preSubject?->name }}" disabled
                               class="w-full px-4 py-2.5 border border-gray-200 bg-gray-50 rounded-xl text-gray-500 cursor-not-allowed text-sm">
                        <input type="hidden" name="subject_id" value="{{ request('subject_id') }}">
                    @else
                        <select name="subject_id" required
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                            @foreach($subjects as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                {{-- Test nomi --}}
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Test nomi</label>
                    <input type="text" name="title" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400"
                           placeholder="masalan: 1-chorak yakuniy testi">
                </div>

                {{-- Vaqt + Ball --}}
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-2">Vaqt (daqiqa)</label>
                        <input type="number" name="time_limit" value="30" min="1" required
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 mb-2">O'tish bali (%)</label>
                        <input type="number" name="pass_score" value="70" min="1" max="100" required
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
                    </div>
                </div>

                {{-- Public / Private toggle --}}
                <div class="mb-6 rounded-2xl border border-gray-100 bg-gray-50 p-4">
                    <p class="text-sm font-semibold text-gray-700 mb-3">Ko'rinish</p>
                    <div class="grid grid-cols-2 gap-3" id="visibilityToggle">
                        <label class="cursor-pointer">
                            <input type="radio" name="_visibility" value="public" class="sr-only peer" checked>
                            <div class="rounded-xl border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 p-3 text-center transition">
                                <i class="fas fa-globe text-green-500 text-lg block mb-1"></i>
                                <p class="text-xs font-bold text-gray-700">Ommaviy</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Botda hammaga ko'rinadi</p>
                            </div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="_visibility" value="private" class="sr-only peer">
                            <div class="rounded-xl border-2 border-gray-200 peer-checked:border-indigo-500 peer-checked:bg-indigo-50 p-3 text-center transition">
                                <i class="fas fa-lock text-indigo-500 text-lg block mb-1"></i>
                                <p class="text-xs font-bold text-gray-700">Yopiq (kod bilan)</p>
                                <p class="text-[10px] text-gray-400 mt-0.5">Faqat ID bilganlar kiradi</p>
                            </div>
                        </label>
                    </div>

                    {{-- Access code (shown only if private) --}}
                    <div id="codeWrapper" class="mt-3 hidden">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5">Test ID (kirish kodi)</label>
                        <input type="text" name="access_code" id="access_code"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400"
                               placeholder="masalan: MAT-2024">
                    </div>

                    {{-- Hidden access_code when public --}}
                    <input type="hidden" id="access_code_hidden" name="access_code" value="">
                </div>

                {{-- Hidden defaults --}}
                <input type="hidden" name="is_random" value="1">

                <div class="flex gap-3">
                    <a href="{{ route('admin.quizzes.index') }}"
                       class="flex-1 text-center py-3 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition">
                        Bekor qilish
                    </a>
                    <button type="submit"
                            class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow transition">
                        Keyingi →
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const radios = document.querySelectorAll('input[name="_visibility"]');
const codeWrapper = document.getElementById('codeWrapper');
const codeInput = document.getElementById('access_code');
const codeHidden = document.getElementById('access_code_hidden');

function toggle() {
    const isPrivate = document.querySelector('input[name="_visibility"]:checked').value === 'private';
    codeWrapper.classList.toggle('hidden', !isPrivate);
    codeInput.required = isPrivate;
    // Only one of them should be active
    if (isPrivate) {
        codeHidden.removeAttribute('name');
        codeInput.name = 'access_code';
    } else {
        codeInput.name = '';
        codeHidden.name = 'access_code';
        codeHidden.value = '';
    }
}

radios.forEach(r => r.addEventListener('change', toggle));
toggle(); // init
</script>
@endsection
