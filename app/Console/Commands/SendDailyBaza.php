<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BazaBotController;

class SendDailyBaza extends Command
{
    protected $signature   = 'bot:send-daily-baza';
    protected $description = 'Har kuni MySQL bazasini mysqldump orqali yuklab, barcha bot foydalanuvchilariga yuborish';

    public function handle(): int
    {
        $this->info('[BazaBot] Baza yuborish boshlandi...');

        // ── 1. Dump MySQL database ──────────────────────────────────────────
        $host     = config('database.connections.mysql.host', '127.0.0.1');
        $port     = config('database.connections.mysql.port', '3306');
        $database = config('database.connections.mysql.database', 'ramazon');
        $username = config('database.connections.mysql.username', 'ramazon');
        $password = config('database.connections.mysql.password');

        $date     = now()->format('Y-m-d');
        $sqlFile  = storage_path("app/baza_{$date}.sql");
        $zipFile  = storage_path("app/baza_{$date}.zip");

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($password),
            escapeshellarg($database),
            escapeshellarg($sqlFile)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode !== 0 || !file_exists($sqlFile) || filesize($sqlFile) === 0) {
            $error = 'mysqldump muvaffaqiyatsiz tugadi. Output: ' . implode("\n", $output);
            Log::channel('single')->error('[BazaBot] ' . $error);
            $this->error($error);
            return self::FAILURE;
        }

        // ── 2. Zip the dump ─────────────────────────────────────────────────
        $zip = new \ZipArchive();
        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $zip->addFile($sqlFile, "baza_{$date}.sql");
            $zip->close();
        } else {
            // If zip fails, send raw SQL file
            $zipFile = $sqlFile;
            $this->warn('[BazaBot] ZIP yaratib bo\'lmadi, SQL fayl yuboriladi.');
        }

        // ── 3. Load users ───────────────────────────────────────────────────
        $users = BazaBotController::loadUsers();

        if (empty($users)) {
            $this->warn('[BazaBot] Hech qanday foydalanuvchi ro\'yxatdan o\'tmagan.');
            $this->cleanup($sqlFile, $zipFile);
            return self::SUCCESS;
        }

        $this->info('[BazaBot] ' . count($users) . ' ta foydalanuvchiga yuborilmoqda...');

        // ── 4. Send file to each user ────────────────────────────────────────
        $bot     = new BazaBotController();
        $caption = "📦 <b>Kunlik baza</b> — {$date}\n\n";
        $caption .= "🗄 Fayl: <code>baza_{$date}</code>\n";
        $caption .= "🕗 Yuborildi: " . now()->format('H:i') . " (UZT)";

        $sent   = 0;
        $failed = 0;

        foreach ($users as $chatId => $user) {
            $result = $bot->callApi('sendDocument', [
                'chat_id'    => $chatId,
                'document'   => new \CURLFile($zipFile),
                'caption'    => $caption,
                'parse_mode' => 'HTML',
            ]);

            if (isset($result['ok']) && $result['ok']) {
                $sent++;
                Log::channel('single')->info("[BazaBot] Yuborildi: {$chatId}");
            } else {
                $failed++;
                $errorDesc = $result['description'] ?? 'Noma\'lum xato';
                Log::channel('single')->warning("[BazaBot] Yuborilmadi {$chatId}: {$errorDesc}");
            }

            // Small delay to avoid Telegram rate limits
            usleep(100000); // 0.1s
        }

        $this->info("[BazaBot] ✅ Yuborildi: {$sent}, ❌ Muvaffaqiyatsiz: {$failed}");

        // ── 5. Cleanup temp files ────────────────────────────────────────────
        $this->cleanup($sqlFile, $zipFile);

        return self::SUCCESS;
    }

    private function cleanup(string $sqlFile, string $zipFile): void
    {
        if (file_exists($sqlFile)) @unlink($sqlFile);
        if ($zipFile !== $sqlFile && file_exists($zipFile)) @unlink($zipFile);
    }
}
