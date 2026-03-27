<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Contest\Models\ContestBot;
use App\Modules\Contest\Models\Contest;
use App\Modules\Contest\Models\ContestKeyword;
use App\Modules\Contest\Models\ContestChannel;
use App\Modules\Contest\Models\ContestPrize;
use Carbon\Carbon;

class MTTExpertContestSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Botni yaratish yoki topish
        $bot = ContestBot::updateOrCreate(
            ['token' => '8775655708:AAEJb5vL_EPUFDCQGKP2MZM3PaCOkXmgAv0'],
            [
                'name' => 'Mega Contest Bot',
                'is_active' => true,
            ]
        );

        // 2. Konkursni yaratish
        $contestTitle = 'MTT EXPERTI SITORA ABDULLAYEVNA SOVRINLI OʼYINI';
        $contest = Contest::updateOrCreate(
            ['contest_bot_id' => $bot->id, 'title' => $contestTitle],
            [
                'description' => "❗️ABDULLAYEVNA JAMOASI YUTUQLI OʻYINIDA QATNASHING.\n\n1-oʻrin: 300ming \n2-oʻrin: 200ming\n3-oʻrin: 100ming\n4-10-oʻrinlar attestatsiya ehtimoliy testlar yopiq guruhiga qoʻshilish imkoniyati.\n\n🎉Gʻoliblar kanalga eng koʻp obunachi taklif qilganlar orasidan tanlanadi.",
                'start_text' => "👋 Assalomu alaykum!\n\n🏆 *MTT EXPERTI SITORA ABDULLAYEVNA* sovrinli o'yiniga xush kelibsiz!\n\n🎁 Konkursda qatnashish uchun quyidagi tugmalardan foydalaning. Do'stlarni taklif qiling va ballar yig'ing!",
                'rules_text' => "📋 *Konkurs Qoidalari:*\n\n1. @attestatsiya_jamoa kanaliga a'zo bo'lish shart.\n2. Telefon raqamni tasdiqlash lozim.\n3. Botga eng koʼp doʼstini taklif qilgan ishtirokchi gʼolib hisoblanadi.\n4. Nakrutka taqiqlanadi!",
                'referral_text' => "🔗 *Sizning referral havolangiz:*\n\n{link}\n\n👆 Ushbu havolani do'stlaringizga yuboring! Har bir qo'shilgan do'stingiz uchun sizga *{points} ball* beriladi. Omad!",
                'require_phone' => true,
                'require_channel_join' => true,
                'require_referral' => true,
                'referral_points' => 1,
                'is_active' => true,
                'start_date' => Carbon::create(2026, 3, 28),
                'end_date' => Carbon::create(2026, 4, 20, 23, 59, 59),
            ]
        );

        // 3. Menyu Tugmalari
        $menuButtons = [
            [
                'keyword' => '👤 Mening profilim',
                'action' => 'profile',
                'sort_order' => 1,
                'response_text' => 'Profil ma\'lumotlari',
            ],
            [
                'keyword' => '🏆 Reyting (TOP 20)',
                'action' => 'leaderboard',
                'sort_order' => 2,
                'response_text' => 'TOP 20 ishtirokchilar',
            ],
            [
                'keyword' => '🔗 Taklif qilish',
                'action' => 'referral',
                'sort_order' => 3,
                'response_text' => 'Referral havola olish',
            ],
            [
                'keyword' => '📋 Qoidalar',
                'action' => 'rules',
                'sort_order' => 4,
                'response_text' => 'Konkurs qoidalari',
            ],
            [
                'keyword' => '🎁 Sovg\'alar',
                'action' => 'prizes',
                'sort_order' => 5,
                'response_text' => 'Sovg\'alar ro\'yxati',
            ],
        ];

        foreach ($menuButtons as $btn) {
            ContestKeyword::updateOrCreate(
                ['contest_id' => $contest->id, 'keyword' => $btn['keyword']],
                array_merge($btn, ['is_menu_button' => true])
            );
        }

        // 4. Majburiy kanallar
        $channels = [
            [
                'channel_name' => 'MTT EXPERTI | SITORA ABDULLAYEVNA',
                'channel_id' => '@attestatsiya_jamoa',
                'channel_url' => 'https://t.me/attestatsiya_jamoa'
            ],
        ];

        foreach ($channels as $ch) {
            ContestChannel::updateOrCreate(
                ['contest_id' => $contest->id, 'channel_id' => $ch['channel_id']],
                $ch
            );
        }

        // 5. Sovg'alar (Eskilarini o'chirib, yangilarini kiritamiz)
        $contest->prizes()->delete();
        $prizes = [
            [
                'title' => '1-oʻrin',
                'points_required' => 1,
                'description' => '300ming',
                'sort_order' => 1
            ],
            [
                'title' => '2-oʻrin',
                'points_required' => 1,
                'description' => '200ming',
                'sort_order' => 2
            ],
            [
                'title' => '3-oʻrin',
                'points_required' => 1,
                'description' => '100ming',
                'sort_order' => 3
            ],
            [
                'title' => '4-10-oʻrinlar',
                'points_required' => 1,
                'description' => 'attestatsiya ehtimoliy testlar yopiq guruhiga qoʻshilish imkoniyati.',
                'sort_order' => 4
            ],
        ];

        foreach ($prizes as $prize) {
            ContestPrize::updateOrCreate(
                ['contest_id' => $contest->id, 'title' => $prize['title']],
                $prize
            );
        }
    }
}
