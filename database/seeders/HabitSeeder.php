<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Habit;

class HabitSeeder extends Seeder
{
    public function run(): void
    {
        $habits = [
            // 5 vaqt namoz
            ['name' => 'Bomdod namozi',      'slug' => 'bomdod',      'type' => 'checkbox', 'icon' => 'ri-sun-line',          'sort_order' => 1],
            ['name' => 'Peshin namozi',       'slug' => 'peshin',      'type' => 'checkbox', 'icon' => 'ri-sun-foggy-line',    'sort_order' => 2],
            ['name' => 'Asr namozi',          'slug' => 'asr',         'type' => 'checkbox', 'icon' => 'ri-cloud-line',        'sort_order' => 3],
            ['name' => 'Shom namozi',         'slug' => 'shom',        'type' => 'checkbox', 'icon' => 'ri-moon-line',         'sort_order' => 4],
            ['name' => 'Xufton namozi',       'slug' => 'xufton',      'type' => 'checkbox', 'icon' => 'ri-moon-clear-line',   'sort_order' => 5],

            // Ro'za va Taroveh
            ['name' => "Ro'za",               'slug' => 'roza',        'type' => 'checkbox', 'icon' => 'ri-restaurant-line',   'sort_order' => 6],
            ['name' => 'Taroveh namozi',      'slug' => 'taroveh',     'type' => 'checkbox', 'icon' => 'ri-building-4-line',   'sort_order' => 7],

            // Qur'on va Zikr
            ['name' => "Qur'on o'qish (sahifa)", 'slug' => 'quron',   'type' => 'number',   'icon' => 'ri-book-open-line',    'sort_order' => 8],
            ['name' => 'Zikr / Duo',          'slug' => 'zikr',        'type' => 'checkbox', 'icon' => 'ri-heart-pulse-line',  'sort_order' => 9],
            ['name' => 'Istighfor (100 marta)', 'slug' => 'istighfor', 'type' => 'checkbox', 'icon' => 'ri-refresh-line',      'sort_order' => 10],
            ['name' => 'Salavot (100 marta)', 'slug' => 'salavot',     'type' => 'checkbox', 'icon' => 'ri-sparkling-line',    'sort_order' => 11],

            // Qo'shimcha
            ['name' => 'Sadaqa',              'slug' => 'sadaqa',      'type' => 'checkbox', 'icon' => 'ri-hand-heart-line',   'sort_order' => 12],
            ['name' => 'Duho namozi',         'slug' => 'duho',        'type' => 'checkbox', 'icon' => 'ri-sun-fill',          'sort_order' => 13],
            ['name' => 'Ilm o\'rganish',      'slug' => 'ilm',         'type' => 'checkbox', 'icon' => 'ri-graduation-cap-line', 'sort_order' => 14],
            ['name' => 'Ota-onaga yaxshilik', 'slug' => 'ota-ona',    'type' => 'checkbox', 'icon' => 'ri-parent-line',       'sort_order' => 15],
        ];

        foreach ($habits as $habit) {
            Habit::updateOrCreate(
                ['slug' => $habit['slug'], 'user_id' => null],
                array_merge($habit, ['is_default' => true])
            );
        }
    }
}
