<?php

namespace Database\Seeders;

use App\Models\Habit;
use Illuminate\Database\Seeder;

class HabitSeeder extends Seeder
{
    public function run(): void
    {
        $habits = [
            ['name' => 'Bomdod namozi', 'slug' => 'bomdod', 'type' => 'checkbox', 'icon' => '🌅', 'is_default' => true, 'sort_order' => 1],
            ['name' => 'Peshin namozi', 'slug' => 'peshin', 'type' => 'checkbox', 'icon' => '☀️', 'is_default' => true, 'sort_order' => 2],
            ['name' => 'Asr namozi', 'slug' => 'asr', 'type' => 'checkbox', 'icon' => '🌤', 'is_default' => true, 'sort_order' => 3],
            ['name' => 'Shom namozi', 'slug' => 'shom', 'type' => 'checkbox', 'icon' => '🌇', 'is_default' => true, 'sort_order' => 4],
            ['name' => 'Xufton namozi', 'slug' => 'xufton', 'type' => 'checkbox', 'icon' => '🌙', 'is_default' => true, 'sort_order' => 5],
            ['name' => 'Taroveh namozi', 'slug' => 'taroveh', 'type' => 'checkbox', 'icon' => '🕌', 'is_default' => true, 'sort_order' => 6],
            ['name' => 'Ro\'za', 'slug' => 'roza', 'type' => 'checkbox', 'icon' => '🍽', 'is_default' => true, 'sort_order' => 7],
            ['name' => 'Qur\'on (sahifa)', 'slug' => 'quron', 'type' => 'number', 'icon' => '📖', 'is_default' => true, 'sort_order' => 8],
            ['name' => 'Zikr', 'slug' => 'zikr', 'type' => 'checkbox', 'icon' => '📿', 'is_default' => true, 'sort_order' => 9],
            ['name' => 'Sadaqa', 'slug' => 'sadaqa', 'type' => 'checkbox', 'icon' => '💰', 'is_default' => true, 'sort_order' => 10],
        ];

        foreach ($habits as $habit) {
            Habit::updateOrCreate(
                ['slug' => $habit['slug'], 'is_default' => true],
                $habit
            );
        }
    }
}
