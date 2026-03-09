@extends('layouts.admin')

@section('title', 'Telegram Live Stream')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="text-2xl font-bold text-gray-800">Telegram Live Stream</h3>
                <p class="text-gray-500 mt-1">Video yoki Yutub linkini doimiy efirga qo'yish</p>
            </div>
            <div class="flex items-center space-x-4">
                <div id="status-badge-running"
                    class="{{ ($stream && $stream->is_active) ? '' : 'hidden' }} bg-green-50 px-6 py-3 rounded-2xl border border-green-100 flex items-center shadow-sm">
                    <span class="relative flex h-3 w-3 mr-3">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <div>
                        <p class="text-[10px] uppercase font-black text-green-400 tracking-widest leading-none mb-1">Holat
                        </p>
                        <p class="text-xl font-black text-green-900 leading-none">Jonli efirda</p>
                    </div>
                </div>
                <div id="status-badge-stopped"
                    class="{{ ($stream && $stream->is_active) ? 'hidden' : '' }} bg-gray-50 px-6 py-3 rounded-2xl border border-gray-100 flex items-center shadow-sm">
                    <i class="fas fa-stop-circle text-gray-400 mr-3 text-xl"></i>
                    <div>
                        <p class="text-[10px] uppercase font-black text-gray-400 tracking-widest leading-none mb-1">Holat
                        </p>
                        <p class="text-xl font-black text-gray-900 leading-none">To'xtatilgan</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-[32px] shadow-xl shadow-indigo-100/20 border border-white overflow-hidden mb-8">
            <div class="p-8">
                <form id="settings-form" action="{{ route('admin.live-stream.update') }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="video_url" class="block text-sm font-bold text-gray-700 mb-2 ml-1">YouTube Video
                            Linki</label>
                        <input type="text" name="video_url" id="video_url" value="{{ $stream->video_url ?? '' }}"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-gray-700 placeholder:text-gray-300"
                            placeholder="Masalan: https://www.youtube.com/watch?v=..." required>
                        <p class="text-[11px] text-gray-400 mt-2 ml-1 italic">Tizim videoni avtomatik loop rejimida efirga
                            uzatadi.</p>
                    </div>

                    <div class="mb-6">
                        <label for="stream_url" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Server URL</label>
                        <input type="text" name="stream_url" id="stream_url"
                            value="{{ $stream->stream_url ?? 'rtmps://dc4-1.rtmp.t.me/s/' }}"
                            class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-gray-700 placeholder:text-gray-300"
                            placeholder="Telegrandan olingan Server URL" required>
                    </div>

                    <div class="mb-8">
                        <label for="stream_key" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Telegram Stream
                            Key</label>
                        <div class="relative">
                            <input type="password" name="stream_key" id="stream_key" value="{{ $stream->stream_key ?? '' }}"
                                class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-gray-700 placeholder:text-gray-300"
                                placeholder="Telegramdan olingan Stream Key" required>
                            <button type="button"
                                onclick="document.getElementById('stream_key').type = document.getElementById('stream_key').type === 'password' ? 'text' : 'password'"
                                class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-indigo-600 transition-colors">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-gray-50 pt-8">
                        <button type="submit"
                            class="bg-white hover:bg-gray-50 text-gray-700 font-bold px-8 py-4 rounded-2xl border border-gray-200 transition-all active:scale-95">
                            Sozlamalarni saqlash
                        </button>

                        <button type="button" id="toggle-stream-btn"
                            data-active="{{ ($stream && $stream->is_active) ? '1' : '0' }}"
                            class="group {{ ($stream && $stream->is_active) ? 'bg-red-600 hover:bg-red-700 shadow-red-200' : 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-200' }} text-white font-black px-12 py-5 rounded-[20px] shadow-2xl transition-all active:scale-95 flex items-center space-x-4 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span id="toggle-btn-text"
                                class="text-lg">{{ ($stream && $stream->is_active) ? 'Efirni to\'xtatish' : 'Efirni boshlash' }}</span>
                            <i id="toggle-btn-icon"
                                class="fas {{ ($stream && $stream->is_active) ? 'fa-stop-circle' : 'fa-play-circle' }}"></i>
                            <i id="toggle-btn-spinner" class="fas fa-spinner fa-spin hidden"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="stats-container"
            class="{{ ($stream && $stream->is_active) ? '' : 'hidden' }} grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Uptime</div>
                <div id="stat-uptime" class="text-2xl font-black text-indigo-600">00:00:00</div>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Bitrate</div>
                <div id="stat-bitrate" class="text-2xl font-black text-indigo-600">0 kbps</div>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Speed</div>
                <div id="stat-speed" class="text-2xl font-black text-indigo-600">0x</div>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-sm">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Ma'lumot</div>
                <div id="stat-size" class="text-2xl font-black text-indigo-600">0 MB</div>
            </div>
        </div>

        <div class="bg-indigo-900 rounded-[32px] p-8 text-white shadow-2xl shadow-indigo-200 mb-12">
            <h4 class="text-xl font-black mb-6 flex items-center">
                <i class="fas fa-book-open mr-3 text-indigo-400"></i> Efirni boshlash bo'yicha yo'riqnoma
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <div
                            class="bg-indigo-500/30 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">
                            1</div>
                        <p class="text-sm text-indigo-100">Telegram kanalingizga kiring va <strong>"Stream with..."</strong>
                            (Efir boshlash) tugmasini bosing.</p>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div
                            class="bg-indigo-500/30 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">
                            2</div>
                        <p class="text-sm text-indigo-100">Paydo bo'lgan oynadan <strong>"Server URL"</strong> va
                            <strong>"Stream Key"</strong> ma'lumotlarini nusxalab oling.
                        </p>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div
                            class="bg-indigo-500/30 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">
                            3</div>
                        <p class="text-sm text-indigo-100">Ushbu sahifadagi mos maydonlarga ma'lumotlarni joylashtiring va
                            YouTube linkini kiriting.</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <div
                            class="bg-indigo-500/30 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">
                            4</div>
                        <p class="text-sm text-indigo-100"><strong>"Sozlamalarni saqlash"</strong> tugmasini bosing, so'ngra
                            <strong>"Efirni boshlash"</strong> tugmasini bosing.
                        </p>
                    </div>
                    <div class="flex items-start space-x-4">
                        <div
                            class="bg-indigo-500/30 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 font-bold text-sm">
                            5</div>
                        <p class="text-sm text-indigo-100">Telegramga qaytib <strong>"Start Streaming"</strong> tugmasini
                            bosing. Efir boshlanadi!</p>
                    </div>
                    <div class="bg-indigo-800/50 p-4 rounded-2xl border border-indigo-700/50">
                        <p class="text-[11px] text-indigo-300 leading-relaxed italic">
                            <i class="fas fa-exclamation-triangle mr-1 text-yellow-500"></i>
                            Eslatma: Efir boshlangandan so'ng biroz kutishingiz kerak bo'lishi mumkin. Agar efir to'xtab
                            qolsa, qaytadan "Boshlash"ni bosing.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('toggle-stream-btn');
            const toggleText = document.getElementById('toggle-btn-text');
            const toggleIcon = document.getElementById('toggle-btn-icon');
            const toggleSpinner = document.getElementById('toggle-btn-spinner');
            const statsContainer = document.getElementById('stats-container');
            const badgeRunning = document.getElementById('status-badge-running');
            const badgeStopped = document.getElementById('status-badge-stopped');

            function updateUIState(isActive) {
                toggleBtn.setAttribute('data-active', isActive ? '1' : '0');
                if (isActive) {
                    toggleBtn.classList.remove('bg-indigo-600', 'hover:bg-indigo-700', 'shadow-indigo-200');
                    toggleBtn.classList.add('bg-red-600', 'hover:bg-red-700', 'shadow-red-200');
                    toggleText.innerText = 'Efirni to\'xtatish';
                    toggleIcon.classList.remove('fa-play-circle');
                    toggleIcon.classList.add('fa-stop-circle');
                    statsContainer.classList.remove('hidden');
                    badgeRunning.classList.remove('hidden');
                    badgeStopped.classList.add('hidden');
                } else {
                    toggleBtn.classList.remove('bg-red-600', 'hover:bg-red-700', 'shadow-red-200');
                    toggleBtn.classList.add('bg-indigo-600', 'hover:bg-indigo-700', 'shadow-indigo-200');
                    toggleText.innerText = 'Efirni boshlash';
                    toggleIcon.classList.remove('fa-stop-circle');
                    toggleIcon.classList.add('fa-play-circle');
                    statsContainer.classList.add('hidden');
                    badgeRunning.classList.add('hidden');
                    badgeStopped.classList.remove('hidden');
                }
            }

            // Handle toggle button click
            if (toggleBtn) {
                toggleBtn.addEventListener('click', function () {
                    toggleBtn.disabled = true;
                    toggleIcon.classList.add('hidden');
                    toggleSpinner.classList.remove('hidden');

                    fetch('{{ route("admin.live-stream.toggle") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                updateUIState(data.active);
                            } else {
                                alert(data.message || 'Xatolik yuz berdi');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Server bilan bog\'lanishda xatolik');
                        })
                        .finally(() => {
                            toggleBtn.disabled = false;
                            toggleIcon.classList.remove('hidden');
                            toggleSpinner.classList.add('hidden');
                        });
                });
            }

            function syncStatsAndState() {
                fetch('{{ route("admin.live-stream.stats") }}')
                    .then(response => response.json())
                    .then(data => {
                        // Update global UI state based on DB/Process status
                        updateUIState(data.is_running || data.is_active_db);

                        if (data.is_running) {
                            document.getElementById('stat-uptime').innerText = data.uptime;
                            document.getElementById('stat-bitrate').innerText = data.bitrate;
                            document.getElementById('stat-speed').innerText = data.speed;
                            document.getElementById('stat-size').innerText = data.total_size;
                        }
                    })
                    .catch(err => console.error('Sync error:', err));
            }

            setInterval(syncStatsAndState, 3000);
            syncStatsAndState();
        });
    </script>
@endsection