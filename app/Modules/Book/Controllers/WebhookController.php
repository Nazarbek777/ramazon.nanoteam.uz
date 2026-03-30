<?php

namespace App\Modules\Book\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Modules\Book\Models\BookUser;
use App\Modules\Bookstore\Models\Book as BookstoreBook;
use Illuminate\Support\Facades\Cache;
use App\Modules\Book\Services\TelegramService;
use App\Modules\Book\Services\BookService;

class WebhookController
{
    protected TelegramService $telegram;
    protected BookService $bookService;

    protected array $requiredChannels = [
        ['username' => '@Nurkitoblari_m1', 'name' => '📢 Nur kitoblar'],
    ];

    public function __construct()
    {
        $this->telegram = new TelegramService();
        $this->bookService = new BookService();
    }

    public function handle(Request $request): JsonResponse
    {
        $update = $request->all();

        try {
            if (isset($update['message'])) {
                $this->onMessage($update['message']);
            } elseif (isset($update['callback_query'])) {
                $this->onCallback($update['callback_query']);
            }
        } catch (\Exception $e) {
            Log::error('[BookBot] ' . $e->getMessage(), ['line' => $e->getLine()]);
        }

        return response()->json(['ok' => true]);
    }

    // ── MESSAGE ──────────────────────────────────────────

    protected function onMessage(array $msg): void
    {
        $chatId = $msg['chat']['id'];
        $text = trim($msg['text'] ?? '');
        $from = $msg['from'] ?? [];
        $userId = $from['id'] ?? $chatId;

        if (isset($msg['contact'])) {
            $this->onContact($chatId, $msg['contact'], $from, $msg['message_id'] ?? 0);
            return;
        }

        if (str_starts_with($text, '/start')) {
            $this->onStart($chatId, $text, $from);
            return;
        }

        // Raqamni qo'lda kiritishni rad etish
        $user = BookUser::where('telegram_id', $userId)->first();
        if ($user && empty($user->phone)) {
            $this->telegram->sendMessage($chatId, "⚠️ Iltimos, raqamingizni pastdagi **\"Raqamni yuborish\"** tugmasi orqali yuboring. Qo'lda yozib kiritish mumkin emas.");
            return;
        }

        // Barcha tugmalar uchun kanalga a'zolikni tekshirish
        $isJoined = $this->isJoinedAll($userId);
        Log::info("[BookBot] Membership", ['u_id' => $userId, 'is_joined' => $isJoined]);

        if (!$isJoined) {
            $this->askJoinChannel($chatId);
            return;
        }

        Log::info("[BookBot] onMessage", ['chat_id' => $chatId, 'text' => $text, 'u_id' => $userId]);

        if (str_contains($text, 'Profil')) {
            Log::info("[BookBot] Route: Profil");
            $this->onProfile($chatId, $from);
        } elseif (str_contains($text, 'Kitob qidirish')) {
            $this->telegram->sendMessage($chatId, "🔍 <b>Kitob izlash uchun uning nomini yoki muallifini yozib yuboring.</b>\n\nMasalan: <i>Sariq devni minib</i>");
        } elseif (str_contains($text, 'Reyting') || str_contains($text, 'Taklif') || str_contains($text, 'Sovrin') || str_contains($text, 'riqnoma')) {
            $this->telegram->sendMessage($chatId, "⚠️ Hozirda faol konkurslar mavjud emas.");
        } else {
            // Manzilni qabul qilish holatini tekshirish
            $state = Cache::get("book_bot_state_{$chatId}");
            if ($state && ($state['step'] ?? '') === 'address') {
                $this->onAddress($chatId, $text, $from);
                return;
            }

            Log::info("[BookBot] Route: Search", ['text' => $text]);
            $this->onSearch($chatId, $text);
        }
    }

    // ── CALLBACK ─────────────────────────────────────────

    protected function onCallback(array $cb): void
    {
        $chatId = $cb['message']['chat']['id'] ?? null;
        $data = $cb['data'] ?? '';
        $from = $cb['from'] ?? [];

        if (!$chatId)
            return;

        $this->telegram->answerCallbackQuery($cb['id']);

        if ($data === 'check_channels') {
            $this->onCheckChannels($chatId, $from);
        } elseif (str_starts_with($data, 'order_')) {
            $bookId = (int) str_replace('order_', '', $data);
            $this->onOrder($chatId, $bookId, $from);
        } elseif (str_starts_with($data, 'delivery_')) {
            $type = str_replace('delivery_', '', $data);
            $this->onDeliveryTypeSelect($chatId, $type, $from);
        }
    }

    // ── /start ───────────────────────────────────────────

    protected function onStart(int $chatId, string $text, array $from): void
    {
        $user = $this->bookService->getOrCreateUser([
            'id' => $from['id'] ?? $chatId,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? '',
            'last_name' => $from['last_name'] ?? '',
        ]);

        // Tayyor (mavjud foydalanuvchi uchun xush kelibsiz)
        $text = "📚 <b>\"Nur kitoblar\" do'konining rasmiy botiga xush kelibsiz!</b>\n\nBu yerda o'zingizga kerakli kitoblarni izlashingiz mumkin.\n\n🔍 <b>Qidiruv uchun kitob nomini yoki muallifni yozing.</b>";
        $this->sendMainKeyboard($chatId, $text);
    }

    // ── KONTAKT (telefon) ────────────────────────────────

    protected function onContact(int $chatId, array $contact, array $from, int $msgId = 0): void
    {
        $phone = $contact['phone_number'] ?? '';
        $userId = $from['id'] ?? $chatId;
        $contactUserId = $contact['user_id'] ?? null;

        // 1. O'zining raqamini yuborganligini tekshirish (bot aralashuvini oldini olish)
        if ($contactUserId != $userId) {
            $this->telegram->sendMessage($chatId, "⚠️ Kechirasiz, siz faqat o'zingizning shaxsiy raqamingizni yuborishingiz mumkin! Boshqa kontakt yuborish taqiqlangan.");
            return;
        }

        // 2. Raqam O'zbekiston raqami ekanligini tekshirish
        $cleanPhone = preg_replace('/\D/', '', $phone);
        if (!str_starts_with($cleanPhone, '998') || strlen($cleanPhone) < 12) {
            $this->telegram->sendMessage($chatId, "⚠️ Kechirasiz, tanlovda faqat O'zbekiston (+998) raqamlari bilan qatnashish mumkin!");
            return;
        }

        // 3. Raqam bazada boshqa profil tomonidan band qilinmaganini tekshirish
        $existing = BookUser::where('phone', 'like', "%{$cleanPhone}%")->where('telegram_id', '!=', $userId)->first();
        if ($existing) {
            $this->telegram->sendMessage($chatId, "⚠️ Bu raqam egasi avval ishtirok etgan! Bitta raqam bilan faqat bitta Telegram profildan qatnashish mumkin.");
            return;
        }

        $user = BookUser::where('telegram_id', $userId)->first();
        if ($user) {
            $user->phone = $cleanPhone;
            $user->save();
        }

        // Eski xabarlarni o'chirish
        if ($msgId > 0) {
            $this->telegram->deleteMessage($chatId, $msgId);     // kontakt xabarni o'chirish
            $this->telegram->deleteMessage($chatId, $msgId - 1); // "raqamni yuboring" xabarni o'chirish
        }

        // Kanal tekshiruvi
        if (!$this->isJoinedAll($userId)) {
            $this->askJoinChannel($chatId, "✅ Raqamingiz saqlandi!\n\n⚠️ Konkursda ishtirok etish uchun eng avvalo quyidagi guruhga a'zo bo'lishingiz shart.");
            return;
        }

        if ($user && $user->referrer_id) {
            $this->bookService->processReferral($user->referrer_id, $user->id);
        }

        // Buyurtma jarayonida bo'lsa
        $state = Cache::get("book_bot_state_{$chatId}");
        if ($state && ($state['step'] ?? '') === 'phone') {
            $this->askDeliveryType($chatId);
            return;
        }

        $text = "✅ Raqam saqlandi! Botdan to'liq foydalanishingiz mumkin.";
        $this->sendMainKeyboard($chatId, $text);
    }

    // ── KANAL TEKSHIRUVI ─────────────────────────────────

    protected function isJoinedAll(int $userId): bool
    {
        foreach ($this->requiredChannels as $ch) {
            if (!$this->telegram->isUserInChannel($ch['username'], $userId)) {
                return false;
            }
        }
        return true;
    }

    protected function askJoinChannel(int $chatId, string $message = null): void
    {
        $keyboard = [];
        foreach ($this->requiredChannels as $ch) {
            $name = ltrim($ch['username'], '@');
            $keyboard[] = [['text' => $ch['name'], 'url' => "https://t.me/{$name}"]];
        }
        $keyboard[] = [['text' => '✅ Tekshirish', 'callback_data' => 'check_channels']];

        $text = $message ?? "⚠️ Botdan to'liq foydalanish va konkursda qatnashish uchun quyidagi guruhga a'zo bo'lishingiz shart.\n\nA'zo bo'lgach <b>\"✅ Tekshirish\"</b> tugmasini bosing.";

        $this->telegram->sendMessageWithKeyboard($chatId, $text, $keyboard);
    }

    protected function onCheckChannels(int $chatId, array $from): void
    {
        $userId = $from['id'] ?? $chatId;

        if ($this->isJoinedAll($userId)) {
            $user = BookUser::where('telegram_id', $userId)->first();
            
            // Kanalga muvaffaqiyatli a'zo bo'lgandan SO'NG ball beriladi
            if ($user && $user->referrer_id && !empty($user->phone)) {
                $this->bookService->processReferral($user->referrer_id, $user->id);
            }

            $text = "✅ Ajoyib! Siz barcha guruhlarga aʼzo boʻldingiz!";
            $this->sendMainKeyboard($chatId, $text);
        } else {
            $this->telegram->sendMessage($chatId, "❌ Hali guruhlarga aʼzo boʻlmagansiz.");
            $this->askJoinChannel($chatId);
        }
    }

    // ── AFISHA ───────────────────────────────────────────

    protected function sendAfisha(int $chatId): void
    {
        $text = "<b>╔═══📚🌙🌸═══╗\n";
        $text .= "   BAYRAMONA KONKURS\n";
        $text .= "╚═══🌸🌙📚═══╝</b>\n\n";
        $text .= "Ramazon hayiti munosabati bilan <b>\"Nur kitoblar\"</b> doʻkoni yirik sovrinli konkursni eʼlon qiladi! 🎉\n\n";
        $text .= "🎁 <b>Sovrinlar:</b>\n\n";
        $text .= "🥇 <b>1-oʻrin</b>\n";
        $text .= "📖 Qurʼoni Karim\n";
        $text .= "(maʼnolarining tarjima va tafsiri)\n\n";
        $text .= "🥈 <b>2-oʻrin</b>\n";
        $text .= "📚 Odam boʻlish qiyin\n";
        $text .= "📚 Zirapcha qiz (Qishloqlik Romeo va Juletta)\n\n";
        $text .= "🥉 <b>3-oʻrin</b>\n";
        $text .= "📚 Lobar, Lobar, Lobarim mening\n";
        $text .= "📚 Gʻoyib boʻlgan atirgul\n\n";
        $text .= "🏅 <b>4-oʻrin</b>\n";
        $text .= "📚 Zamonga yengilma\n";
        $text .= "📚 Alanga ichidagi ayol\n\n";
        $text .= "🎲 <b>Tasodifiy g'oliblar: (2 ta)</b>\n";
        $text .= "📚 10 tadan ko'p odam qo'shgan ishtirokchilar orasidan 2 nafar tasodifiy g'oliblar tanlanadi va 1 tadan kitob sovg'a qilinadi!\n\n";
        $text .= "👥 Doʻstlaringizni taklif qiling va qimmatli kitoblarni yutib oling!\n\n";
        $text .= "🚀 Faol boʻling va gʻoliblar qatoridan joy oling!\n";
        $text .= "⚡️ Har bir qoʻshilgan odam sizni gʻalabaga yaqinlashtiradi!";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── REFERRAL LINK ────────────────────────────────────

    protected function sendReferralLink(int $chatId, $user): void
    {
        $now = now()->setTimezone('Asia/Tashkent');
        $endTime = \Carbon\Carbon::create(2026, 3, 21, 21, 0, 0, 'Asia/Tashkent');

        if ($now->gt($endTime)) {
            $this->telegram->sendMessage($chatId, "🏁 Konkurs tugaganligi sahali havola berilmaydi.");
            return;
        }

        $botInfo = $this->telegram->getMe();
        $username = $botInfo['result']['username'] ?? 'bot';
        $link = "https://t.me/{$username}?start={$user->telegram_id}";

        $text = "🔗 <b>Sizning havolangiz:</b>\n\n";
        $text .= "Quyidagi xabarni doʻstlaringizga yuboring:\n\n";
        $text .= "👇👇👇\n\n";
        $text .= "📚 Kitob yutishni xohlaysizmi?\n";
        $text .= "\"Nur kitoblar\" doʻkonining bayramona tanlovida qatnashing!\n";
        $text .= "🎁 Qurʼoni Karim va boshqa qimmatli kitoblar sovg'a!\n\n";
        $text .= "👉 {$link}\n\n";
        $text .= "⚡️ Botga kirib /start bosing!";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── ASOSIY KEYBOARD (pastdan chiqadigan) ─────────────

    protected function sendMainKeyboard(int $chatId, string $text = null): void
    {
        $message = $text ?? "📚 Kerakli bo'limni tanlang:";
        $this->telegram->sendMessageWithReplyKeyboard($chatId, $message, [
            [['text' => '🏆 Reyting'], ['text' => '👤 Profil']],
            [['text' => '🔗 Taklif qilish']],
            [['text' => '🎁 Sovrinlar'], ['text' => '📋 Yoʻriqnoma']],
            [['text' => '🔍 Kitob qidirish']],
        ]);
    }

    // ── YOʻRIQNOMA ──────────────────────────────────────

    protected function sendYoriqnoma(int $chatId): void
    {
        $text = "📋 <b>QOIDA VA SHARTLAR</b>\n";
        $text .= "━━━━━━━━━━━━━━━━━━\n\n";

        $text .= "🗓 <b>Muddat:</b>\n";
        $text .= "Boshlanish: <b>16-mart 10:00</b>\n";
        $text .= "Tugash: <b>21-mart 21:00</b>\n\n";

        $text .= "📝 <b>Qanday qatnashish mumkin:</b>\n";
        $text .= "1️⃣ Botga /start bosib roʻyxatdan oʻting\n";
        $text .= "2️⃣ Telefon raqamingizni yuboring\n";
        $text .= "3️⃣ Kerakli guruhlarga aʼzo boʻling\n";
        $text .= "4️⃣ Doʻstlaringizga o'z havolangizni yuboring\n\n";

        $text .= "⚡️ <b>Ball tizimi:</b>\n";
        $text .= "Sizning havolangiz orqali kirgan har bir doʻstingiz uchun <b>1 ball</b> beriladi.\n\n";

        $text .= "🎁 <b>Sovrinlar:</b>\n";
        $text .= "🥇 1-oʻrin — Qurʼoni Karim (tarjima va tafsiri)\n";
        $text .= "🥈 2-oʻrin — 2 ta kitob\n";
        $text .= "🥉 3-oʻrin — 2 ta kitob\n";
        $text .= "🏅 4-oʻrin — 2 ta kitob\n";
        $text .= "🎲 Tasodifiy g'oliblar (2 ta) — 1 tadan kitob (10 tadan ko'p do'stini qo'shganlar orasidan)\n\n";

        $text .= "━━━━━━━━━━━━━━━━━━\n";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── PROFIL ───────────────────────────────────────────

    protected function onProfile(int $chatId, array $from): void
    {
        $user = $this->bookService->getOrCreateUser([
            'id' => $from['id'] ?? $chatId,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? '',
            'last_name' => $from['last_name'] ?? '',
        ]);

        $name = $user->first_name ?: 'Foydalanuvchi';
        $count = $user->referrals()->count();

        $botInfo = $this->telegram->getMe();
        $username = $botInfo['result']['username'] ?? 'bot';
        $link = "https://t.me/{$username}?start={$user->telegram_id}";

        $text = "👤 <b>{$name}</b>\n\n";
        $text .= "💰 Ball: <b>{$user->points}</b>\n";
        $text .= "👥 Taklif: <b>{$count}</b> ta\n";
        $text .= "📱 Raqam: {$user->phone}\n\n";
        $text .= "🔗 Havola:\n<code>{$link}</code>";

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── REYTING ──────────────────────────────────────────

    protected function onLeaderboard(int $chatId): void
    {
        $leaders = $this->bookService->getLeaderboard(10);

        $text = "🏆 <b>Top 10</b>\n\n";
        $medals = ['🥇', '🥈', '🥉'];

        foreach ($leaders as $i => $l) {
            $m = $medals[$i] ?? ($i + 1) . '.';
            $n = $l->first_name ?: 'Foydalanuvchi';
            $text .= "{$m} {$n} — <b>{$l->points}</b> ball\n";
        }

        if ($leaders->isEmpty()) {
            $text .= "Hozircha ishtirokchilar yoʻq.";
        }

        $this->telegram->sendMessage($chatId, $text);
    }

    // ── TAKLIF QILISH ────────────────────────────────────

    protected function onReferral(int $chatId, array $from): void
    {
        $user = $this->bookService->getOrCreateUser([
            'id' => $from['id'] ?? $chatId,
            'username' => $from['username'] ?? null,
            'first_name' => $from['first_name'] ?? '',
            'last_name' => $from['last_name'] ?? '',
        ]);

        $this->sendReferralLink($chatId, $user);
    }

    // ── QIDIRUV ──────────────────────────────────────────

    protected function onSearch(int $chatId, string $query): void
    {
        if (strlen($query) < 2) {
            $this->telegram->sendMessage($chatId, "⚠️ Qidiruv uchun kamida 2 ta belgi kiriting.");
            return;
        }

        $books = $this->bookService->searchBooks($query);

        if ($books->isEmpty()) {
            $this->telegram->sendMessage($chatId, "😔 Kechirasiz, <b>\"{$query}\"</b> bo'yicha hech qanday kitob topilmadi.");
            return;
        }

        $text = "🔍 <b>Qidiruv natijalari:</b>\n\n";
        foreach ($books as $book) {
            $bookText = "📖 <b>{$book->title}</b>\n";
            $bookText .= "💰 Narxi: " . number_format($book->price, 0, '.', ' ') . " so'm\n";
            $bookText .= "📦 Mavjud: {$book->stock} ta\n";
            $bookText .= "━━━━━━━━━━━━━━━━━━\n";
            
            $keyboard = [
                [['text' => "🛍 Buyurtma qilish", 'callback_data' => "order_{$book->id}"]]
            ];
            
            $this->telegram->sendMessageWithKeyboard($chatId, $bookText, $keyboard);
        }
    }

    // ── BUYURTMA ─────────────────────────────────────────

    protected function onOrder(int $chatId, int $bookId, array $from): void
    {
        $userId = $from['id'] ?? $chatId;
        $user = BookUser::where('telegram_id', $userId)->first();
        
        Cache::put("book_bot_state_{$chatId}", [
            'book_id' => $bookId,
            'step' => 'phone'
        ], now()->addMinutes(30));

        if (!$user || empty($user->phone)) {
            $this->telegram->sendContactRequest(
                $chatId,
                "📞 Buyurtmani rasmiylashtirish uchun pastdagi <b>\"📱 Raqamni yuborish\"</b> tugmasi orqali telefon raqamingizni yuboring:"
            );
            return;
        }

        $this->askDeliveryType($chatId);
    }

    protected function askDeliveryType(int $chatId): void
    {
        Cache::put("book_bot_state_{$chatId}", array_merge(
            Cache::get("book_bot_state_{$chatId}", []),
            ['step' => 'delivery_type']
        ), now()->addMinutes(30));

        $keyboard = [
            [
                ['text' => "🚚 Yetkazib berish", 'callback_data' => "delivery_courier"],
                ['text' => "🏃 Borib olish", 'callback_data' => "delivery_pickup"]
            ]
        ];

        $this->telegram->sendMessageWithKeyboard($chatId, "🚚 Kitobni qanday usulda qabul qilib olasiz?", $keyboard);
    }

    protected function onDeliveryTypeSelect(int $chatId, string $type, array $from): void
    {
        $state = Cache::get("book_bot_state_{$chatId}");
        if (!$state) return;

        $state['delivery_type'] = $type;
        
        if ($type === 'courier') {
            $state['step'] = 'address';
            Cache::put("book_bot_state_{$chatId}", $state, now()->addMinutes(30));
            $this->telegram->sendMessage($chatId, "📍 Iltimos, manzilingizni to'liq yozib yuboring (shahar, tuman, ko'cha, uy):");
        } else {
            $state['delivery_address'] = "Do'kondan olib ketish";
            Cache::put("book_bot_state_{$chatId}", $state, now()->addMinutes(30));
            $this->sendFinalOrder($chatId, $state, $from);
        }
    }

    protected function onAddress(int $chatId, string $address, array $from): void
    {
        $state = Cache::get("book_bot_state_{$chatId}");
        if (!$state) return;

        $state['delivery_address'] = $address;
        $this->sendFinalOrder($chatId, $state, $from);
    }

    protected function sendFinalOrder(int $chatId, array $state, array $from): void
    {
        $userId = $from['id'] ?? $chatId;
        $user = BookUser::where('telegram_id', $userId)->first();
        $book = BookstoreBook::find($state['book_id']);
        
        if (!$book) {
            $this->telegram->sendMessage($chatId, "⚠️ Xatolik yuz berdi. Iltimos qaytadan urinib ko'ring.");
            Cache::forget("book_bot_state_{$chatId}");
            return;
        }

        $deliveryText = ($state['delivery_type'] === 'courier') ? "🚚 Yetkazib berish" : "🏃 Borib olish";

        // Adminni xabardor qilish
        $adminChatId = '8586236246';
        $adminMsg = "🆕 <b>YANGI BUYURTMA!</b>\n\n";
        $adminMsg .= "📚 Kitob: <b>{$book->title}</b>\n";
        $adminMsg .= "💰 Narxi: " . number_format($book->price, 0, '.', ' ') . " so'm\n\n";
        $adminMsg .= "👤 Kimdan: {$user->first_name} {$user->last_name} (@" . ($user->username ?? 'yo\'q') . ")\n";
        $adminMsg .= "📞 Tel: +{$user->phone}\n";
        $adminMsg .= "📦 Usul: {$deliveryText}\n";
        $adminMsg .= "📍 Manzil: {$state['delivery_address']}\n\n";
        $adminMsg .= "🆔 Chat ID: <code>{$userId}</code>";

        $this->telegram->sendMessage($adminChatId, $adminMsg);

        // Foydalanuvchiga tasdiqlash
        $this->telegram->sendMessage($chatId, "✅ <b>Buyurtmangiz qabul qilindi!</b>\n\nSiz bilan bog'lanamiz. Rahmat!");
        
        Cache::forget("book_bot_state_{$chatId}");
    }
}
