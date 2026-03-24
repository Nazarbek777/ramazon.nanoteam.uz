<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Contest\Models\ContestBot;
use App\Modules\Contest\Models\Contest;
use App\Modules\Contest\Models\ContestKeyword;
use App\Modules\Contest\Models\ContestChannel;

class ContestBotSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Botni yaratish (Siz bergan token bilan)
        $bot = ContestBot::updateOrCreate(
            ['token' => '8775655708:AAEJb5vL_EPUFDCQGKP2MZM3PaCOkXmgAv0'],
            [
                'name' => 'Mega Contest Bot',
                'is_active' => true,
            ]
        );

        // 2. Chotki shablon - Konkurs yaratish
        $contest = Contest::updateOrCreate(
            ['bot_id' => $bot->id, 'title' => 'Ramazon Mega Konkurs 2026'],
            [
                'description' => 'Ushbu konkursda qatnashib qimmatbaho sovg\'alarni yutib oling!',
                'start_text' => "👋 Assalomu alaykum!\n\n🏆 *Ramazon Mega Konkurs*imizga xush kelibsiz!\n\n🎁 Konkursda qatnashish uchun quyidagi tugmalardan foydalaning. Do'stlarni taklif qiling va ballar yig'ing!",
                'rules_text' => "📋 *Konkurs Qoidalari:*\n\n1. Kanallarga a'zo bo'lish shart.\n2. Telefon raqamni tasdiqlash lozim.\n3. Har bir taklif qilingan do'st uchun 1 ball beriladi.\n4. Nakrutka taqiqlanadi!",
                'referral_text' => "🔗 *Sizning referral havolangiz:*\n\n{link}\n\n👆 Ushbu havolani do'stlaringizga yuboring! Har bir qo'shilgan do'stingiz uchun sizga *{points} ball* beriladi. Omad!",
                'require_phone' => true,
                'require_channel_join' => true,
                'require_referral' => true,
                'referral_points' => 1,
                'is_active' => true,
                'start_date' => now(),
                'end_date' => now()->addMonth(),
            ]
        );

        // 3. Chotki Menyu Tugmalari (Tizim amallari bilan)
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
        ];

        foreach ($menuButtons as $btn) {
            ContestKeyword::updateOrCreate(
                ['contest_id' => $contest->id, 'keyword' => $btn['keyword']],
                array_merge($btn, ['is_menu_button' => true])
            );
        }

        // 4. Namuna uchun kanallar (Bularni o'zingiz tahrirlaysiz)
        $channels = [
            ['channel_name' => 'Asosiy Kanal', 'channel_id' => '@contest_channel_test'],
            ['channel_name' => 'Homiy Kanal', 'channel_id' => '@sponsor_channel_test'],
        ];

        foreach ($channels as $ch) {
            ContestChannel::updateOrCreate(
                ['contest_id' => $contest->id, 'channel_id' => $ch['channel_id']],
                $ch
            );
        }
    }
}
