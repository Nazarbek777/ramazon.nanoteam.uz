@extends('layouts.admin')

@section('title', 'Umumiy xabar yuborish')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-2xl font-bold text-gray-800">Broadcast Tizimi</h3>
            <p class="text-gray-500 mt-1">Barcha Telegram foydalanuvchilariga xabar yuborish</p>
        </div>
        <div class="bg-indigo-50 px-6 py-3 rounded-2xl border border-indigo-100 flex items-center">
            <i class="fas fa-users text-indigo-600 mr-3 text-xl"></i>
            <div>
                <p class="text-[10px] uppercase font-black text-indigo-400 tracking-widest leading-none mb-1">Jami a'zolar</p>
                <p class="text-xl font-black text-indigo-900 leading-none">{{ $userCount }} ta</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-[32px] shadow-xl shadow-indigo-100/20 border border-white overflow-hidden">
        <div class="p-8">
            <form action="{{ route('admin.broadcast.send') }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label for="message" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Xabar matni</label>
                    <textarea name="message" id="message" rows="8" required
                              class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all resize-none text-gray-700"
                              placeholder="Xabaringizni shu yerga yozing... (HTML qo'llab-quvvatlanadi)"></textarea>
                    <div class="mt-2 flex items-center text-[11px] text-gray-400 px-1">
                        <i class="fas fa-info-circle mr-1 text-indigo-400"></i>
                        <span>Maslahat: <b>&lt;b&gt;bold&lt;/b&gt;</b>, <i>&lt;i&gt;italic&lt;/i&gt;</i> kabi taglardan foydalanishingiz mumkin.</span>
                    </div>
                </div>

                <div class="flex items-center justify-end">
                    <button type="submit" onclick="return confirm('Haqiqatan ham barcha foydalanuvchilarga xabar yubormoqchimisiz?')"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-black px-10 py-4 rounded-2xl shadow-xl shadow-indigo-100 transition-all active:scale-95 flex items-center space-x-3">
                        <span>Xabarni yuborish</span>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="bg-slate-50 p-6 border-t border-slate-100 flex items-center space-x-4">
            <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center text-amber-600 shrink-0">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed font-medium">
                <b>Diqqat:</b> Ko'p sonli foydalanuvchilarga xabar yuborish vaqt talab qilishi mumkin. Yuborish jarayonida sahifani yangilamang yoki yopmang. 
                Bot cheklovlariga (Anti-spam) tushib qolmaslik uchun xabar mazmuniga ehtiyot bo'ling.
            </p>
        </div>
    </div>
</div>
@endsection
