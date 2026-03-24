@extends('contest-admin.layout')

@section('title', 'Ishtirokchilar - ' . $contest->title)

@section('content')
<div class="mb-8 flex items-center justify-between">
    <div>
        <a href="{{ route('contest-admin.bots.contests.edit', [$bot, $contest]) }}" class="text-slate-400 hover:text-violet-400 transition text-sm">
            <i class="fas fa-arrow-left mr-1"></i> {{ $contest->title }} ga qaytish
        </a>
        <h2 class="text-3xl font-bold text-white mt-2">👥 Ishtirokchilar</h2>
        <p class="text-slate-400 text-sm mt-1">{{ $contest->title }} — {{ $participants->total() }} ta ishtirokchi</p>
    </div>
    <div class="flex items-center space-x-3">
        <a href="{{ route('contest-admin.bots.contests.export', [$bot, $contest]) }}"
            class="px-5 py-2.5 rounded-xl bg-emerald-500/20 text-emerald-400 font-bold text-sm hover:bg-emerald-500/30 transition">
            <i class="fas fa-download mr-1"></i> CSV Export
        </a>
    </div>
</div>

<!-- Search & Sort -->
<div class="glass-card rounded-2xl p-4 mb-6">
    <form action="" method="GET" class="flex items-center space-x-4">
        <div class="flex-1 relative">
            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                class="input-dark w-full rounded-xl pl-11 pr-4 py-3"
                placeholder="Ism, username yoki telefon bo'yicha qidirish...">
        </div>
        <select name="sort" class="input-dark rounded-xl px-4 py-3 text-sm">
            <option value="points" {{ request('sort') === 'points' ? 'selected' : '' }}>Ball bo'yicha</option>
            <option value="referral_count" {{ request('sort') === 'referral_count' ? 'selected' : '' }}>Referral bo'yicha</option>
            <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Sana bo'yicha</option>
        </select>
        <select name="dir" class="input-dark rounded-xl px-4 py-3 text-sm">
            <option value="desc" {{ request('dir') === 'desc' ? 'selected' : '' }}>Kamayish ↓</option>
            <option value="asc" {{ request('dir') === 'asc' ? 'selected' : '' }}>O'sish ↑</option>
        </select>
        <button type="submit" class="btn-primary px-5 py-3 rounded-xl font-bold text-white text-sm">
            <i class="fas fa-filter mr-1"></i> Filter
        </button>
    </form>
</div>

<!-- Table -->
<div class="glass-card rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-white/10">
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase">#</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase">Ism</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase">Username</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-400 uppercase">Telefon</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-400 uppercase">Do'stlar</th>
                    <th class="px-6 py-4 text-center text-xs font-bold text-slate-400 uppercase">Ballar</th>
                    <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 uppercase">Sana</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($participants as $i => $p)
                    <tr class="hover:bg-white/5 transition">
                        <td class="px-6 py-4 font-bold text-slate-400">
                            @php $rank = ($participants->currentPage() - 1) * $participants->perPage() + $i + 1; @endphp
                            @if($rank <= 3)
                                <span class="text-lg">{{ ['🥇','🥈','🥉'][$rank-1] }}</span>
                            @else
                                {{ $rank }}
                            @endif
                        </td>
                        <td class="px-6 py-4 font-bold text-white">
                            {{ $p->first_name }} {{ $p->last_name }}
                        </td>
                        <td class="px-6 py-4 text-slate-400">
                            {{ $p->username ? '@'.$p->username : '—' }}
                        </td>
                        <td class="px-6 py-4 text-slate-300 font-mono text-xs">
                            {{ $p->phone ?? '—' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-500/20 text-blue-400">
                                👥 {{ $p->referral_count }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-violet-500/20 text-violet-400">
                                ⭐ {{ $p->points }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right text-xs text-slate-400">
                            {{ $p->created_at->format('d.m.Y H:i') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-slate-400">
                            <i class="fas fa-users text-3xl mb-3 block text-slate-600"></i>
                            Ishtirokchilar topilmadi.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($participants->hasPages())
        <div class="px-6 py-4 border-t border-white/10">
            {{ $participants->withQueryString()->links('pagination::tailwind') }}
        </div>
    @endif
</div>
@endsection
