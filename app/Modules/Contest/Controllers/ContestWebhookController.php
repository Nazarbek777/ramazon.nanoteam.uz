<?php

namespace App\Modules\Contest\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Modules\Contest\Models\ContestBot;
use App\Modules\Contest\Models\Contest;
use App\Modules\Contest\Models\ContestParticipant;
use App\Modules\Contest\Models\ContestKeyword;
use App\Modules\Contest\Services\ContestTelegramService;

class ContestWebhookController
{
    protected ContestTelegramService $telegram;
    protected ContestBot $bot;
    protected ?Contest $contest = null;

    public function handle(Request $request, int $botId): JsonResponse
    {
        $secret = $request->query('secret');

        $this->bot = ContestBot::findOrFail($botId);

        // Security check
        if ($this->bot->webhook_secret && $this->bot->webhook_secret !== $secret) {
            Log::warning('[ContestBot] Invalid webhook secret', ['bot_id' => $botId]);
            return response()->json(['ok' => false], 403);
        }

        if (!$this->bot->is_active) {
            return response()->json(['ok' => true, 'message' => 'Bot inactive']);
        }

        $this->telegram = new ContestTelegramService($this->bot->token);
        $this->contest = $this->bot->activeContest();

        $update = $request->all();

        Log::info('[ContestBot] Webhook received', [
            'bot_id' => $botId,
            'update' => $update,
        ]);

        if (isset($update['message'])) {
            $this->onMessage($update['message']);
        } elseif (isset($update['callback_query'])) {
            $this->onCallback($update['callback_query']);
        }

        return response()->json(['ok' => true]);
    }

    protected function onMessage(array $msg): void
    {
        $chatId = $msg['chat']['id'];
        $from = $msg['from'] ?? [];
        $text = $msg['text'] ?? '';

        // Contact shared
        if (isset($msg['contact'])) {
            $this->onContact($chatId, $msg['contact'], $from);
            return;
        }

        // /start command
        if (str_starts_with($text, '/start')) {
            $this->onStart($chatId, $text, $from);
            return;
        }

        if (!$this->contest) {
            $this->telegram->sendMessage($chatId, "⏳ Hozirda faol konkurs mavjud emas.");
            return;
        }

        // 1. Check Keywords (including Menu Buttons)
        $keyword = ContestKeyword::where('contest_id', $this->contest->id)
            ->whereRaw('LOWER(keyword) = ?', [mb_strtolower($text)])
            ->first();

        if ($keyword) {
            // Check for System Actions
            if ($keyword->action) {
                switch ($keyword->action) {
                    case 'profile':
                        $this->onProfile($chatId, $from);
                        return;
                    case 'leaderboard':
                        $this->onLeaderboard($chatId);
                        return;
                    case 'referral':
                        $this->onReferralLink($chatId, $from);
                        return;
                    case 'rules':
                        $this->onRules($chatId);
                        return;
                }
            }

            // Normal Keyword Response (Text/Photo)
            if ($keyword->response_photo) {
                // If the response_photo looks like a file_id, use it directly. 
                // If it's a URL, Telegram will handle it.
                $this->telegram->sendPhoto($chatId, $keyword->response_photo, [
                    'caption' => $keyword->response_text
                ]);
            } else {
                $this->telegram->sendMessage($chatId, $keyword->response_text);
            }
            return;
        }

        $this->sendMainKeyboard($chatId, "🤔 Tushunmadim. Quyidagi tugmalardan foydalaning:");
    }

    protected function onCallback(array $cb): void
    {
        $chatId = $cb['message']['chat']['id'] ?? null;
        $data = $cb['data'] ?? '';
        $from = $cb['from'] ?? [];

        $this->telegram->answerCallbackQuery($cb['id']);

        if (!$chatId) return;

        if ($data === 'check_channels') {
            $this->onCheckChannels($chatId, $from);
        }
    }

    protected function onStart(int $chatId, string $text, array $from): void
    {
        if (!$this->contest) {
            $this->telegram->sendMessage($chatId, "👋 Salom! Hozirda faol konkurs mavjud emas.");
            return;
        }

        // Parse referral: /start ref_12345
        $referrerTelegramId = null;
        if (preg_match('/\/start\s+ref_(\d+)/', $text, $m)) {
            $referrerTelegramId = (int) $m[1];
        }

        // Find or create participant
        $participant = ContestParticipant::firstOrCreate(
            ['contest_id' => $this->contest->id, 'telegram_id' => $from['id']],
            [
                'username' => $from['username'] ?? null,
                'first_name' => $from['first_name'] ?? '',
                'last_name' => $from['last_name'] ?? null,
            ]
        );

        // If registered, send main keyboard
        if ($participant->is_registered) {
            $this->sendMainKeyboard($chatId, "✅ Siz allaqachon ro'yxatdan o'tgansiz!");
            return;
        }

        // Save referrer if applicable
        if ($referrerTelegramId && $referrerTelegramId !== $from['id'] && !$participant->referrer_id) {
            $referrer = ContestParticipant::where('contest_id', $this->contest->id)
                ->where('telegram_id', $referrerTelegramId)
                ->first();

            if ($referrer) {
                $participant->update(['referrer_id' => $referrer->id]);
            }
        }

        // Check channel join requirement
        if ($this->contest->require_channel_join && $this->contest->channels->isNotEmpty()) {
            if (!$this->isJoinedAll($from['id'])) {
                $this->askJoinChannel($chatId);
                return;
            }
        }

        // Phone requirement
        if ($this->contest->require_phone && !$participant->phone) {
            $this->telegram->sendContactRequest(
                $chatId,
                $this->contest->start_text ?: "👋 Assalomu alaykum!\n\n📱 Ro'yxatdan o'tish uchun telefon raqamingizni yuboring:"
            );
            return;
        }

        // If no phone required, register immediately
        $participant->update(['is_registered' => true]);
        $this->creditReferral($participant);
        $this->sendMainKeyboard($chatId, "✅ Tabriklaymiz! Siz konkursda qatnashyapsiz! 🎉");
    }

    protected function onContact(int $chatId, array $contact, array $from): void
    {
        if (!$this->contest) return;

        // Validate contact belongs to the sender
        $contactUserId = $contact['user_id'] ?? null;
        if ($contactUserId !== $from['id']) {
            $this->telegram->sendMessage($chatId, "⚠️ Iltimos, faqat o'zingizning telefon raqamingizni yuboring!");
            return;
        }

        $phone = $contact['phone_number'] ?? '';

        $participant = ContestParticipant::where('contest_id', $this->contest->id)
            ->where('telegram_id', $from['id'])
            ->first();

        if (!$participant) {
            $participant = ContestParticipant::create([
                'contest_id' => $this->contest->id,
                'telegram_id' => $from['id'],
                'username' => $from['username'] ?? null,
                'first_name' => $from['first_name'] ?? '',
                'last_name' => $from['last_name'] ?? null,
                'phone' => $phone,
            ]);
        }

        // Check duplicate phone
        $existing = ContestParticipant::where('contest_id', $this->contest->id)
            ->where('phone', $phone)
            ->where('id', '!=', $participant->id)
            ->first();

        if ($existing) {
            $this->telegram->sendMessage($chatId, "⚠️ Bu telefon raqami allaqachon ro'yxatdan o'tgan!");
            return;
        }

        $participant->update([
            'phone' => $phone,
            'is_registered' => true,
            'username' => $from['username'] ?? $participant->username,
            'first_name' => $from['first_name'] ?? $participant->first_name,
            'last_name' => $from['last_name'] ?? $participant->last_name,
        ]);

        $this->creditReferral($participant);
        $this->sendMainKeyboard($chatId, "✅ Tabriklaymiz! Siz muvaffaqiyatli ro'yxatdan o'tdingiz! 🎉");
    }

    protected function creditReferral(ContestParticipant $participant): void
    {
        if (!$participant->referrer_id) return;

        $referrer = ContestParticipant::find($participant->referrer_id);
        if (!$referrer) return;

        $referrer->increment('referral_count');
        $referrer->increment('points', $this->contest->referral_points);

        // Notify referrer
        $this->telegram->sendMessage(
            $referrer->telegram_id,
            "🎉 Sizning do'stingiz {$participant->first_name} konkursga qo'shildi!\n" .
            "👥 Jami do'stlaringiz: {$referrer->referral_count}\n" .
            "⭐ Ballaringiz: {$referrer->points}"
        );
    }

    protected function isJoinedAll(int $userId): bool
    {
        foreach ($this->contest->channels as $channel) {
            if (!$this->telegram->isUserInChannel($channel->channel_id, $userId)) {
                return false;
            }
        }
        return true;
    }

    protected function askJoinChannel(int $chatId, string $message = null): void
    {
        $text = $message ?? "📢 Konkursda qatnashish uchun quyidagi kanallarga a'zo bo'ling:";

        $buttons = [];
        foreach ($this->contest->channels as $channel) {
            $url = $channel->channel_url ?: "https://t.me/{$channel->channel_id}";
            $buttons[] = [['text' => "➕ {$channel->channel_name}", 'url' => $url]];
        }
        $buttons[] = [['text' => '✅ Tekshirish', 'callback_data' => 'check_channels']];

        $this->telegram->sendMessageWithKeyboard($chatId, $text, $buttons);
    }

    protected function onCheckChannels(int $chatId, array $from): void
    {
        if (!$this->contest) return;

        if (!$this->isJoinedAll($from['id'])) {
            $this->askJoinChannel($chatId, "❌ Siz hali barcha kanallarga a'zo bo'lmadingiz. Iltimos, a'zo bo'ling:");
            return;
        }

        // Channels OK, continue registration
        $participant = ContestParticipant::where('contest_id', $this->contest->id)
            ->where('telegram_id', $from['id'])
            ->first();

        if ($this->contest->require_phone && (!$participant || !$participant->phone)) {
            $this->telegram->sendContactRequest(
                $chatId,
                "✅ Ajoyib! Endi telefon raqamingizni yuboring:"
            );
            return;
        }

        if ($participant && !$participant->is_registered) {
            $participant->update(['is_registered' => true]);
            $this->creditReferral($participant);
        }

        $this->sendMainKeyboard($chatId, "✅ Tabriklaymiz! Siz konkursda qatnashyapsiz! 🎉");
    }

    protected function sendMainKeyboard(int $chatId, string $text = null): void
    {
        $buttons = $this->contest->keywords()
            ->where('is_menu_button', true)
            ->orderBy('sort_order')
            ->get();

        if ($buttons->isEmpty()) {
            $this->telegram->sendMessage($chatId, $text ?? '👇');
            return;
        }

        $keyboard = [];
        $row = [];
        foreach ($buttons as $btn) {
            $row[] = $btn->keyword;
            if (count($row) === 2) {
                $keyboard[] = $row;
                $row = [];
            }
        }
        if (!empty($row)) {
            $keyboard[] = $row;
        }

        $this->telegram->sendMessageWithReplyKeyboard(
            $chatId,
            $text ?? '👇 Tugmalardan birini tanlang:',
            $keyboard
        );
    }

    protected function onProfile(int $chatId, array $from): void
    {
        if (!$this->contest) return;

        $participant = ContestParticipant::where('contest_id', $this->contest->id)
            ->where('telegram_id', $from['id'])
            ->first();

        if (!$participant || !$participant->is_registered) {
            $this->telegram->sendMessage($chatId, "❌ Siz hali ro'yxatdan o'tmagansiz. /start bosing.");
            return;
        }

        // Calculate rank
        $rank = ContestParticipant::where('contest_id', $this->contest->id)
            ->where('is_registered', true)
            ->where('points', '>', $participant->points)
            ->count() + 1;

        $total = ContestParticipant::where('contest_id', $this->contest->id)
            ->where('is_registered', true)
            ->count();

        $text = "📊 <b>Sizning natijalaringiz</b>\n\n"
            . "👤 Ism: {$participant->first_name} {$participant->last_name}\n"
            . "📱 Telefon: {$participant->phone}\n"
            . "👥 Do'stlar: {$participant->referral_count}\n"
            . "⭐ Ballar: {$participant->points}\n"
            . "🏆 O'rin: {$rank} / {$total}";

        $this->telegram->sendMessage($chatId, $text);
    }

    protected function onLeaderboard(int $chatId): void
    {
        if (!$this->contest) return;

        $top = ContestParticipant::where('contest_id', $this->contest->id)
            ->where('is_registered', true)
            ->orderByDesc('points')
            ->orderByDesc('referral_count')
            ->limit(20)
            ->get();

        if ($top->isEmpty()) {
            $this->telegram->sendMessage($chatId, "📊 Hali ishtirokchilar yo'q.");
            return;
        }

        $text = "🏆 <b>TOP 20 Reyting</b>\n\n";
        $medals = ['🥇', '🥈', '🥉'];

        foreach ($top as $i => $p) {
            $prefix = $medals[$i] ?? ($i + 1) . '.';
            $text .= "{$prefix} {$p->first_name} — ⭐{$p->points} (👥{$p->referral_count})\n";
        }

        $this->telegram->sendMessage($chatId, $text);
    }

    protected function onReferralLink(int $chatId, array $from): void
    {
        if (!$this->contest) return;

        $botUsername = $this->bot->username;
        $link = "https://t.me/{$botUsername}?start=ref_{$from['id']}";

        $text = $this->contest->referral_text ?: "🔗 <b>Sizning referral havolangiz:</b>\n\n<code>{link}</code>\n\n👆 Havolani do'stlaringizga yuboring va ball yig'ing!";

        $text = str_replace(
            ['{link}', '{points}', '{name}'],
            [$link, $this->contest->referral_points, $from['first_name'] ?? ''],
            $text
        );

        $this->telegram->sendMessage($chatId, $text);
    }

    protected function onRules(int $chatId): void
    {
        if (!$this->contest) return;

        $text = $this->contest->rules_text ?: "📋 Konkurs qoidalari hali kiritilmagan.";
        $this->telegram->sendMessage($chatId, $text);
    }
}
