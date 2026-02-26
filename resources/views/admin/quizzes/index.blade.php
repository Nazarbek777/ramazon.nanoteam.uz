@extends('layouts.admin')

@section('title', 'Testlar ro\'yxati')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h3 class="text-2xl font-bold text-gray-700">Mavjud Testlar</h3>
    <a href="{{ route('admin.quizzes.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200 shadow-md">
        <i class="fas fa-plus mr-2"></i> Yangi test yaratish
    </a>
</div>

<div class="bg-white rounded-xl shadow-sm overflow-hidden border">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fan</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vaqt</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadval</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Holat</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amallar</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($quizzes as $quiz)
            @php
                $now = now();
                $isExpired = $quiz->ends_at && $quiz->ends_at < $now;
                $isNotStarted = $quiz->starts_at && $quiz->starts_at > $now;
                $isActive = !$isExpired && !$isNotStarted;
            @endphp
            <tr class="hover:bg-gray-50 transition {{ $isExpired ? 'opacity-60' : '' }}">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                    {{ $quiz->title }}
                    @if($quiz->access_code)
                        <span class="ml-2 text-[10px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded font-mono">{{ $quiz->access_code }}</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quiz->subject->name }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $quiz->time_limit }} daq / {{ $quiz->pass_score }}%</td>
                <td class="px-6 py-4 text-xs text-gray-500">
                    @if($quiz->starts_at)
                        <div class="flex items-center gap-1 mb-1">
                            <i class="fas fa-play-circle text-emerald-400 text-[10px]"></i>
                            {{ $quiz->starts_at->format('d.m.Y H:i') }}
                        </div>
                    @else
                        <span class="text-gray-300 text-[10px]">Boshlanish — cheksiz</span><br>
                    @endif
                    @if($quiz->ends_at)
                        <div class="flex items-center gap-1">
                            <i class="fas fa-stop-circle text-rose-400 text-[10px]"></i>
                            {{ $quiz->ends_at->format('d.m.Y H:i') }}
                        </div>
                    @else
                        <span class="text-gray-300 text-[10px]">Tugash — cheksiz</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($isExpired)
                        <span class="px-2 py-1 text-[10px] font-bold bg-red-100 text-red-600 rounded-full uppercase">Tugagan</span>
                    @elseif($isNotStarted)
                        <span class="px-2 py-1 text-[10px] font-bold bg-amber-100 text-amber-600 rounded-full uppercase">Boshlanmagan</span>
                    @else
                        <span class="px-2 py-1 text-[10px] font-bold bg-emerald-100 text-emerald-600 rounded-full uppercase">Faol</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.quizzes.edit', $quiz) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.quizzes.destroy', $quiz) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Haqiqatan ham o\'chirmoqchimisiz?')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-500 italic">
                    Hozircha testlar yaratilmagan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-6 py-4 border-t">
        {{ $quizzes->links() }}
    </div>
</div>
@endsection
