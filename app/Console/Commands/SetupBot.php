<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetupBot extends Command
{
    protected $signature = 'bot:setup';
    protected $description = 'Set Telegram bot name, description and about text';

    private string $token = '8756417207:AAFyg2vohZbQFrECi6q1qKlN1ep_uGwf-LM';

    public function handle(): void
    {
        $this->info('Bot sozlamalari yuklanmoqda...');

        $this->call_api('setMyName', [
            'name' => 'Attestatsiya Test Bot',
        ]);
        $this->info('✅ Nom belgilandi');

        $this->call_api('setMyDescription', [
            'description' =>
                "🎓 Maktabgacha ta'lim tarbiyachilari uchun attestatsiya tayyorgarlik boti!\n\n" .
                "✅ Test yechib ko'rish — BEPUL!\n" .
                "📚 11 ta fan bo'yicha testlar mavjud\n" .
                "📊 Natijalaringizni kuzating\n\n" .
                "📢 Kanal: @attestatsiya_jamoa\n" .
                "👤 Admin: @abdullayevna_jamoa",
        ]);
        $this->info('✅ Description belgilandi');

        $this->call_api('setMyShortDescription', [
            'short_description' =>
                "🎓 Attestatsiya tayyorgarlik boti | Test yechish bepul! | @attestatsiya_jamoa",
        ]);
        $this->info('✅ About belgilandi');

        $this->call_api('setMyCommands', [
            'commands' => json_encode([
                ['command' => 'start', 'description' => 'Botni ishga tushirish'],
                ['command' => 'yoriqnoma', 'description' => "Yo'riqnoma va bog'lanish"],
            ]),
        ]);
        $this->info('✅ Buyruqlar belgilandi');

        $this->info("\n🎉 Bot muvaffaqiyatli sozlandi! @expert_abdullayevna_test_bot");
    }

    private function call_api(string $method, array $params): void
    {
        $ch = curl_init("https://api.telegram.org/bot{$this->token}/{$method}");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $res = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($res, true);
        if (!($data['ok'] ?? false)) {
            $this->warn("  ⚠️  {$method}: " . ($data['description'] ?? 'xato'));
        }
    }
}
