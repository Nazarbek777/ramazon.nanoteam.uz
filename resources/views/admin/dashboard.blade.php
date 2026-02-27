@extends('layouts.admin')

@section('title', 'Bosh sahifa')

@section('content')
<div class="mb-8">
    <h3 class="text-2xl font-bold text-gray-800">Xush kelibsiz, {{ auth()->user()->name }}! 👋</h3>
    <p class="text-gray-400 text-sm mt-1">
        {{ auth()->user()->isSuperAdmin() ? 'Super Administrator' : 'Administrator' }} &bull;
        Sizga ochiq bo\'limlar quyida ko\'rsatilgan
    </p>
</div>

@php
$sections = [
    ['perm' => 'stats.view',      'route' => 'admin.stats.index',     'icon' => 'fas fa-chart-line',    'title' => 'Statistika',           'desc'  => 'Test natijalari va hisobotlar',   'color' => 'indigo'],
    ['perm' => 'subjects.view',   'route' => 'admin.subjects.index',  'icon' => 'fas fa-layer-group',   'title' => 'Fanlar',               'desc'  => 'Fanlarni boshqarish',            'color' => 'violet'],
    ['perm' => 'quizzes.view',    'route' => 'admin.quizzes.index',   'icon' => 'fas fa-tasks',         'title' => 'Testlar',              'desc'  => 'Testlarni boshqarish',           'color' => 'blue'],
    ['perm' => 'questions.view',  'route' => 'admin.questions.index', 'icon' => 'fas fa-question-circle','title' => 'Savollar',            'desc'  => 'Savollar va variantlar',          'color' => 'sky'],
    ['perm' => 'broadcast.view',  'route' => 'admin.broadcast.index', 'icon' => 'fas fa-bullhorn',      'title' => 'Broadcast',            'desc'  => 'Barcha userlarga xabar yuborish','color' => 'amber'],
    ['perm' => 'users.view',      'route' => 'admin.users.index',     'icon' => 'fas fa-users',         'title' => 'Foydalanuvchilar',     'desc'  => 'Userlarni boshqarish',           'color' => 'emerald'],
    ['perm' => 'permissions',     'route' => 'admin.permissions.index','icon' => 'fas fa-shield-alt',   'title' => 'Ruxsatlar',            'desc'  => 'Admin rol va ruxsatlarini boshqarish','color' => 'rose', 'superOnly' => true],
];

$colors = [
    'indigo'  => ['bg' => 'bg-indigo-50',  'icon' => 'text-indigo-500',  'hover' => 'hover:border-indigo-400  hover:bg-indigo-50'],
    'violet'  => ['bg' => 'bg-violet-50',  'icon' => 'text-violet-500',  'hover' => 'hover:border-violet-400  hover:bg-violet-50'],
    'blue'    => ['bg' => 'bg-blue-50',    'icon' => 'text-blue-500',    'hover' => 'hover:border-blue-400    hover:bg-blue-50'],
    'sky'     => ['bg' => 'bg-sky-50',     'icon' => 'text-sky-500',     'hover' => 'hover:border-sky-400     hover:bg-sky-50'],
    'amber'   => ['bg' => 'bg-amber-50',   'icon' => 'text-amber-500',   'hover' => 'hover:border-amber-400   hover:bg-amber-50'],
    'emerald' => ['bg' => 'bg-emerald-50', 'icon' => 'text-emerald-500', 'hover' => 'hover:border-emerald-400 hover:bg-emerald-50'],
    'rose'    => ['bg' => 'bg-rose-50',    'icon' => 'text-rose-500',    'hover' => 'hover:border-rose-400    hover:bg-rose-50'],
];

$accessible = collect($sections)->filter(function($s) {
    if (!empty($s['superOnly'])) return auth()->user()->isSuperAdmin();
    return auth()->user()->hasPermission($s['perm']);
});
@endphp

@if($accessible->isEmpty())
<div class="bg-white rounded-2xl border border-dashed border-gray-200 p-16 text-center">
    <i class="fas fa-lock text-4xl text-gray-300 mb-3 block"></i>
    <p class="text-gray-500 font-semibold">Sizga hali hech qaysi bo'lim uchun ruxsat berilmagan.</p>
    <p class="text-gray-400 text-sm mt-1">Super admin bilan bog'laning.</p>
</div>
@else
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    @foreach($accessible as $s)
    @php $c = $colors[$s['color']]; @endphp
    <a href="{{ route($s['route']) }}"
       class="group bg-white rounded-2xl border-2 border-gray-100 p-6 transition-all duration-200
              hover:shadow-lg hover:-translate-y-1 {{ $c['hover'] }}">
        <div class="w-12 h-12 {{ $c['bg'] }} rounded-xl flex items-center justify-center mb-4">
            <i class="{{ $s['icon'] }} {{ $c['icon'] }} text-xl"></i>
        </div>
        <h4 class="font-bold text-gray-800 text-base">{{ $s['title'] }}</h4>
        <p class="text-xs text-gray-400 mt-1">{{ $s['desc'] }}</p>
    </a>
    @endforeach
</div>
@endif
@endsection
