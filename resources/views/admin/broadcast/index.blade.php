@extends('layouts.admin')

@section('title', 'Umumiy xabar yuborish')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h3 class="text-2xl font-bold text-gray-800">Premium Broadcast</h3>
            <p class="text-gray-500 mt-1">Boy matnli va rasmli xabarlarni yuborish</p>
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
            <form action="{{ route('admin.broadcast.send') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <!-- Message Body -->
                <div class="mb-8">
                    <label for="editor" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Xabar matni</label>
                    <div class="rounded-2xl overflow-hidden border border-gray-100 shadow-sm focus-within:ring-4 focus-within:ring-indigo-500/10 transition-all">
                        <textarea name="message" id="editor" rows="8"
                              class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-gray-700 text-sm resize-y"
                              placeholder="Xabaringizni shu yerga yozing... (HTML ishlatish mumkin: &lt;b&gt;qalin&lt;/b&gt;, &lt;i&gt;kursiv&lt;/i&gt;)"></textarea>
                    </div>
                </div>

                <!-- Image Upload -->
                <div class="mb-8">
                    <label class="block text-sm font-bold text-gray-700 mb-2 ml-1">Rasm yuklash (Ixtiyoriy)</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="image" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-200 border-dashed rounded-[20px] cursor-pointer bg-gray-50 hover:bg-gray-100 transition-all group">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <i class="fas fa-cloud-upload-alt text-2xl text-gray-400 group-hover:text-indigo-500 mb-2 transition-colors"></i>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-bold text-indigo-600">Yuklash uchun bosing</span> yoki rasmni sudrab keling</p>
                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">PNG, JPG, JPEG (Maks. 5MB)</p>
                            </div>
                            <input id="image" name="image" type="file" class="hidden" accept="image/*" />
                        </label>
                    </div>
                </div>

                <!-- Custom Button -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                    <div>
                        <label for="button_text" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Tugma matni</label>
                        <input type="text" name="button_text" id="button_text" 
                               class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-gray-700 placeholder:text-gray-300"
                               placeholder="Masalan: Saytga o'tish">
                    </div>
                    <div>
                        <label for="button_url" class="block text-sm font-bold text-gray-700 mb-2 ml-1">Tugma linki (URL)</label>
                        <input type="url" name="button_url" id="button_url" 
                               class="w-full px-5 py-4 bg-gray-50 border border-gray-100 rounded-2xl focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all text-gray-700 placeholder:text-gray-300"
                               placeholder="Masalan: https://google.com">
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex items-center justify-end">
                    <button type="submit" onclick="return confirm('Haqiqatan ham barcha foydalanuvchilarga xabar yubormoqchimisiz?')"
                            class="group bg-indigo-600 hover:bg-indigo-700 text-white font-black px-12 py-5 rounded-[20px] shadow-2xl shadow-indigo-200 transition-all active:scale-95 flex items-center space-x-4">
                        <span class="text-lg">Broadcastni boshlash</span>
                        <i class="fas fa-paper-plane group-hover:translate-x-1 group-hover:-translate-y-1 transition-transform"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="bg-indigo-50/30 p-8 border-t border-indigo-50 flex items-start space-x-6">
            <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-indigo-600 shrink-0 shadow-sm">
                <i class="fas fa-magic text-xl"></i>
            </div>
            <div class="flex-1">
                <h4 class="font-bold text-indigo-900 mb-1">Maslahat: Premium xabarlar</h4>
                <p class="text-sm text-indigo-600/70 leading-relaxed">
                    Editor orqali matnni chiroyli ko'rinishga keltiring. Agar rasm yuklasangiz, matn rasmning tagida <b>caption</b> sifatida ko'rinadi. 
                    Telegram cheklovi tufayli caption matni 1024 belgidan oshmasligi kerak.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

