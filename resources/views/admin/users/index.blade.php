@extends('layouts.admin')

@section('title', 'Foydalanuvchilar')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-2xl font-bold text-gray-800">Foydalanuvchilar</h3>
            <p class="text-gray-500 text-sm mt-1">Bot orqali ro'yxatdan o'tgan barcha foydalanuvchilar</p>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-4 text-sm font-medium">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Search --}}
    <form method="GET" class="mb-4 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Ism, telefon yoki Telegram ID..."
               class="flex-1 px-4 py-2.5 border border-gray-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400">
        <button type="submit" class="bg-indigo-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold hover:bg-indigo-700">
            <i class="fas fa-search"></i> Qidirish
        </button>
    </form>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-5 py-3.5 text-xs font-black text-gray-500 uppercase tracking-wide">#</th>
                    <th class="text-left px-5 py-3.5 text-xs font-black text-gray-500 uppercase tracking-wide">Ism</th>
                    <th class="text-left px-5 py-3.5 text-xs font-black text-gray-500 uppercase tracking-wide">Telefon</th>
                    <th class="text-left px-5 py-3.5 text-xs font-black text-gray-500 uppercase tracking-wide">Telegram ID</th>
                    <th class="text-center px-5 py-3.5 text-xs font-black text-gray-500 uppercase tracking-wide">Testlar</th>
                    <th class="text-center px-5 py-3.5 text-xs font-black text-gray-500 uppercase tracking-wide">Holat</th>
                    <th class="text-right px-5 py-3.5 text-xs font-black text-gray-500 uppercase tracking-wide">Amallar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50/50 transition-colors {{ $user->is_blocked ? 'opacity-60' : '' }}">
                    <td class="px-5 py-3.5 text-gray-400 font-mono text-xs">{{ $user->id }}</td>
                    <td class="px-5 py-3.5">
                        <span class="font-semibold text-gray-800">{{ $user->name }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-gray-500 font-mono text-xs">{{ $user->phone_number ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-gray-500 font-mono text-xs">{{ $user->telegram_id ?? '—' }}</td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="bg-indigo-50 text-indigo-600 font-bold px-2.5 py-0.5 rounded-lg text-xs">
                            {{ $user->attempts_count }}
                        </span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($user->is_blocked)
                            <span class="bg-red-100 text-red-600 font-bold px-2.5 py-0.5 rounded-full text-[10px] uppercase">Bloklangan</span>
                        @else
                            <span class="bg-green-100 text-green-600 font-bold px-2.5 py-0.5 rounded-full text-[10px] uppercase">Aktiv</span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-end gap-2">
                            <form method="POST" action="{{ route('admin.users.block', $user) }}">
                                @csrf
                                <button type="submit"
                                        class="text-xs font-bold px-3 py-1.5 rounded-lg transition-all
                                               {{ $user->is_blocked ? 'bg-green-50 text-green-600 hover:bg-green-100' : 'bg-orange-50 text-orange-600 hover:bg-orange-100' }}">
                                    {{ $user->is_blocked ? 'Ochish' : 'Bloklash' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                  onsubmit="return confirm('Haqiqatan ham o\'chirmoqchimisiz?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-xs font-bold px-3 py-1.5 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition-all">
                                    <i class="fas fa-trash text-[10px]"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-10 text-center text-gray-400">
                        <i class="fas fa-users text-3xl mb-2 block"></i>
                        Foydalanuvchilar topilmadi
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        @if($users->hasPages())
        <div class="px-5 py-3.5 border-t border-gray-100 bg-gray-50">
            {{ $users->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
