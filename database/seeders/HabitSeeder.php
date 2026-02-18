<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Habit;

class HabitSeeder extends Seeder
{
    public function run(): void
    {
        $habits = [
            ['name' => 'Bomdod namozi',      'slug' => 'bomdod',    'type' => 'checkbox', 'icon' => 'ri-sun-line',          'sort_order' => 1],
            ['name' => 'Peshin namozi',       'slug' => 'peshin',    'type' => 'checkbox', 'icon' => 'ri-sun-foggy-line',    'sort_order' => 2],
            ['name' => 'Asr namozi',          'slug' => 'asr',       'type' => 'checkbox', 'icon' => 'ri-cloud-line',        'sort_order' => 3],
            ['name' => 'Shom namozi',         'slug' => 'shom',      'type' => 'checkbox', 'icon' => 'ri-moon-line',         'sort_order' => 4],
            ['name' => 'Xufton namozi',       'slug' => 'xufton',    'type' => 'checkbox', 'icon' => 'ri-moon-clear-line',   'sort_order' => 5],
            ['name' => "Ro'za",               'slug' => 'roza',      'type' => 'checkbox', 'icon' => 'ri-restaurant-line',   'sort_order' => 6],
            ['name' => "Qur'on (sahifa)",     'slug' => 'quron',     'type' => 'number',   'icon' => 'ri-book-open-line',    'sort_order' => 7],
            ['name' => 'Zikr',               'slug' => 'zikr',      'type' => 'checkbox', 'icon' => 'ri-heart-pulse-line',  'sort_order' => 8],
            ['name' => 'Sadaqa',             'slug' => 'sadaqa',    'type' => 'checkbox', 'icon' => 'ri-hand-heart-line',   'sort_order' => 9],
            ['name' => 'Taroveh namozi',     'slug' => 'taroveh',   'type' => 'checkbox', 'icon' => 'ri-building-4-line',   'sort_order' => 10],
        ];

        foreach ($habits as $habit) {
            Habit::updateOrCreate(
                ['slug' => $habit['slug']],
                array_merge($habit, ['is_default' => true])
            );
        }
    }
}
