<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Contest\Models\ContestBot;
use App\Modules\Contest\Services\ContestBotService;

class ContestBotSeeder extends Seeder
{
    public function run(): void
    {
        $bot = ContestBot::updateOrCreate(
            ['token' => '8775655708:AAEJb5vL_EPUFDCQGKP2MZM3PaCOkXmgAv0'],
            [
                'name' => 'Mega Contest Bot',
                'is_active' => true,
            ]
        );

        // Note: We don't call setupBot (webhook) here because it needs to be on the server 
        // with a public URL. The user will run this on the server.
    }
}
