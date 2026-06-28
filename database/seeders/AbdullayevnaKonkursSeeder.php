<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Contest\Models\ContestBot;
use App\Modules\Contest\Models\Contest;
use App\Modules\Contest\Models\ContestKeyword;
use App\Modules\Contest\Models\ContestChannel;
use App\Modules\Contest\Models\ContestPrize;
use Carbon\Carbon;

class AbdullayevnaKonkursSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Botni topish/yangilash (@abdullayevna_konkurs_bot — panelда qo'shilgan, id=2)
        $bot = ContestBot::updateOrCreate(
            ['token' => '8840579827:AAH4xz4Ui10Ab2yeRRwmZ9WEPqUGlHPvLbw'],
            [
                'name' => 'Abdullayevna Konkurs',
                'is_active' => true,
            ]
        );

        // 2. Konkurs
        $contestTitle = "Abdullayevna jamoasi yutuqli o'yini";
        $contest = Contest::updateOrCreate(
            ['contest_bot_id' => $bot->id, 'title' => $contestTitle],
            [
                'description' => "❗️ABDULLAYEVNA JAMOASI YUTUQLI OʻYINIDA QATNASHING.\n\n" .
                    "1-oʻrin: 200ming\n" .
                    "2-oʻrin: 100ming\n" .
                    "3-oʻrin: 50ming\n" .
                    "4-10-oʻrinlar attestatsiya yopiq guruhiga qoʻshilish imkoniyati.\n\n" .
                    "🎉 Gʻoliblar kanalga eng koʻp obunachi taklif qilganlar orasidan tanlanadi.\n\n" .
                    "🗓 Sovrinli oʼyin 2-iyuldan 15-iyulga qadar davom etadi.",
                'start_text' => "👋 Assalomu alaykum!\n\n" .
                    "🏆 *Abdullayevna jamoasi yutuqli oʼyini*ga xush kelibsiz!\n\n" .
                    "🎁 Qatnashish uchun quyidagi tugmalardan foydalaning. Doʼstlaringizni taklif qiling va eng koʼp ball toʼplang!",
                'rules_text' => "📋 *Konkurs qoidalari:*\n\n" .
                    "1. Kanalga aʼzo boʼlish shart.\n" .
                    "2. Telefon raqamni tasdiqlash lozim.\n" .
                    "3. Botga eng koʼp doʼstini taklif qilgan ishtirokchi gʼolib hisoblanadi.\n" .
                    "4. Nakrutka (soxta taklif) taqiqlanadi!\n\n" .
                    "🥇 1-oʻrin: 200ming\n🥈 2-oʻrin: 100ming\n🥉 3-oʻrin: 50ming\n🎟 4-10-oʻrinlar: yopiq guruhga qoʻshilish.",
                'referral_text' => "🔗 *Sizning taklif havolangiz:*\n\n{link}\n\n" .
                    "👆 Ushbu havolani doʼstlaringizga yuboring! Har bir qoʼshilgan doʼstingiz uchun sizga *{points} ball* beriladi. Omad!",
                'referral_button_text' => "🎁 Doʼstlarni taklif qilish",
                'require_phone' => true,
                'require_channel_join' => true,
                'require_referral' => true,
                'referral_points' => 1,
                'is_active' => true,
                'start_date' => Carbon::create(2026, 7, 2),
                'end_date' => Carbon::create(2026, 7, 15, 23, 59, 59),
            ]
        );

        // 3. Menyu tugmalari
        $menuButtons = [
            ['keyword' => '👤 Mening profilim',   'action' => 'profile',     'sort_order' => 1, 'response_text' => 'Profil maʼlumotlari'],
            ['keyword' => '🏆 Reyting (TOP 20)',  'action' => 'leaderboard', 'sort_order' => 2, 'response_text' => 'TOP 20 ishtirokchilar'],
            ['keyword' => '🔗 Taklif qilish',     'action' => 'referral',    'sort_order' => 3, 'response_text' => 'Taklif havolasini olish'],
            ['keyword' => '📋 Qoidalar',          'action' => 'rules',       'sort_order' => 4, 'response_text' => 'Konkurs qoidalari'],
            ['keyword' => "🎁 Sovgʼalar",         'action' => 'prizes',      'sort_order' => 5, 'response_text' => 'Sovgʼalar roʼyxati'],
        ];

        foreach ($menuButtons as $btn) {
            ContestKeyword::updateOrCreate(
                ['contest_id' => $contest->id, 'keyword' => $btn['keyword']],
                array_merge($btn, ['is_menu_button' => true])
            );
        }

        // 4. Majburiy kanal
        // ⚠️ Kanalni o'zingiznikiga moslang va botni o'sha kanalga ADMIN qiling!
        $channels = [
            [
                'channel_name' => 'Abdullayevna jamoasi',
                'channel_id'   => '@attestatsiya_jamoa',
                'channel_url'  => 'https://t.me/attestatsiya_jamoa',
            ],
        ];

        foreach ($channels as $ch) {
            ContestChannel::updateOrCreate(
                ['contest_id' => $contest->id, 'channel_id' => $ch['channel_id']],
                $ch
            );
        }

        // 5. Sovrinlar (eskilarini o'chirib yangilaymiz)
        $contest->prizes()->delete();
        $prizes = [
            ['title' => '1-oʻrin',        'points_required' => 1, 'description' => '200ming',                                          'sort_order' => 1],
            ['title' => '2-oʻrin',        'points_required' => 1, 'description' => '100ming',                                          'sort_order' => 2],
            ['title' => '3-oʻrin',        'points_required' => 1, 'description' => '50ming',                                           'sort_order' => 3],
            ['title' => '4-10-oʻrinlar',  'points_required' => 1, 'description' => 'attestatsiya yopiq guruhiga qoʼshilish imkoniyati.', 'sort_order' => 4],
        ];

        foreach ($prizes as $prize) {
            ContestPrize::updateOrCreate(
                ['contest_id' => $contest->id, 'title' => $prize['title']],
                $prize
            );
        }

        $this->command->info("✅ Konkurs tayyor: \"{$contestTitle}\" (bot @{$bot->username}, id={$bot->id})");
    }
}
