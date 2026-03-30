<?php

namespace App\Modules\Book\Services;

use App\Modules\Book\Models\BookUser;
use App\Modules\Book\Models\Referral;
use Illuminate\Support\Facades\DB;
                                                                                                                        use Illuminate\Support\Facades\Log;
use App\Modules\Book\Services\TelegramService;
use App\Modules\Book\Services\MeilisearchService;
use App\Modules\Bookstore\Models\Book as BookstoreBook;

class BookService
{
    protected TelegramService $telegram;
    protected MeilisearchService $meilisearch;

    public function __construct()
    {
        $this->telegram = new TelegramService();
        $this->meilisearch = new MeilisearchService();
    }

    public function getOrCreateUser(array $tgUser, ?int $referrerTgId = null): BookUser
    {
        return DB::transaction(function () use ($tgUser, $referrerTgId) {
            $user = BookUser::where('telegram_id', $tgUser['id'])->first();

            if (!$user) {
                $referrerId = null;
                if ($referrerTgId && $referrerTgId != $tgUser['id']) {
                    $referrer = BookUser::where('telegram_id', $referrerTgId)->first();
                    if ($referrer) {
                        $referrerId = $referrer->id;
                    }
                }

                $user = BookUser::create([
                    'telegram_id' => $tgUser['id'],
                    'username' => $tgUser['username'] ?? null,
                    'first_name' => $tgUser['first_name'] ?? null,
                    'last_name' => $tgUser['last_name'] ?? null,
                    'referrer_id' => $referrerId,
                ]);

                // Referrerni saqlaymiz, lekin ballni telefon kiritilganda beramiz
            }

            return $user;
        });
    }

    public function processReferral(int $referrerId, int $referredId): void
    {
        // Oldinroq ball berilganligini tekshiramiz
        $exists = Referral::where('referred_id', $referredId)->exists();
        if ($exists) {
            return;
        }

        $points = 1;

        Referral::create([
            'referrer_id' => $referrerId,
            'referred_id' => $referredId,
            'points_earned' => $points,
        ]);

        BookUser::where('id', $referrerId)->increment('points', $points);

        $referrer = BookUser::find($referrerId);
        $referred = BookUser::find($referredId);

        if ($referrer && $referred && !empty($referrer->telegram_id)) {
            $name = $referred->first_name ?: 'Foydalanuvchi';
            $msg = "🎉 <b>Tabriklaymiz!</b>\n\nSizning havolangiz orqali <b>{$name}</b> konkursga qo'shildi va sizga <b>1 ball</b> taqdim etildi! 👏\n\nKo'proq do'stlaringizni taklif qilib, g'alaba qozonish imkoniyatingizni oshiring!";
            $this->telegram->sendMessage($referrer->telegram_id, $msg);
        }
    }

    public function getLeaderboard(int $limit = 10)
    {
        return BookUser::orderByDesc('points')->limit($limit)->get();
    }

    public function searchBooks(string $query)
    {
        $query = trim($query);
        if (empty($query)) return collect();

        // 1. Meilisearch orqali qidiruv (Asosiy)
        $hits = $this->meilisearch->search($query);
        if (!empty($hits)) {
            $ids = collect($hits)->pluck('id')->toArray();
            return BookstoreBook::whereIn('id', $ids)
                ->orderByRaw("FIELD(id, " . implode(',', $ids) . ")")
                ->get();
        }

        // 2. Fallback: DB qidiruv
        Log::info("[BookSearch] Meili no hits, using DB fallback...");

        $replacements = [
            'õ' => "o'", 'ö' => "o'", 'o‘' => "o'", 'o’' => "o'", 'o`' => "o'",
            'ğ' => "g'", 'g‘' => "g'", 'g’' => "g'", 'g`' => "g'",
            '«' => '', '»' => '', '"' => '',
        ];
        $normalizedQuery = str_ireplace(array_keys($replacements), array_values($replacements), $query);

        // 2. So'zma-so'z qidiruv (Har bir so'z bo'yicha super-fuzzy match)
        $terms = explode(' ', $normalizedQuery);
        $terms = array_filter($terms, fn($t) => mb_strlen($t) > 1);

        if (empty($terms)) return collect();

        $results = BookstoreBook::where(function ($q) use ($terms) {
            foreach ($terms as $term) {
                $tLatin = $this->transliterate($term, 'toLatin');
                $tCyril = $this->transliterate($term, 'toCyrillic');
                
                // Super-fuzzy pattern: Har bir harf orasiga % qo'yamiz (User so'raganidek "har bir harf qidirish")
                $superFuzzy = '%' . implode('%', mb_str_split($term)) . '%';
                $superFuzzyLatin = '%' . implode('%', mb_str_split($tLatin)) . '%';
                $superFuzzyCyril = '%' . implode('%', mb_str_split($tCyril)) . '%';

                $q->where(function ($sub) use ($term, $tLatin, $tCyril, $superFuzzy, $superFuzzyLatin, $superFuzzyCyril) {
                    $sub->where('title', 'like', "%{$term}%")
                        ->orWhere('title', 'like', "%{$tLatin}%")
                        ->orWhere('title', 'like', "%{$tCyril}%")
                        ->orWhere('title', 'like', $superFuzzy)
                        ->orWhere('title', 'like', $superFuzzyLatin)
                        ->orWhere('title', 'like', $superFuzzyCyril)
                        ->orWhere('author', 'like', $superFuzzy);
                });
            }
        })
        ->limit(10)
        ->get();

        Log::info("[BookSearch] Super-Fuzzy Results Count: " . count($results));

        return $results;
    }

    public function syncBooks(): bool
    {
        $books = BookstoreBook::all()->map(function ($book) {
            return [
                'id' => $book->id,
                'title' => $book->title,
                'author' => $book->author,
                'price' => $book->price,
                'stock' => $book->stock,
            ];
        })->toArray();

        return $this->meilisearch->syncDocuments($books);
    }

    protected function transliterate(string $text, string $mode): string
    {
        $cyrillic = [
            'Ё', 'Й', 'Ц', 'У', 'К', 'Е', 'Н', 'Г', 'Ш', 'Щ', 'З', 'Х', 'Ъ',
            'ё', 'й', 'ц', 'у', 'k', 'е', 'н', 'г', 'ш', 'щ', 'з', 'х', 'ъ',
            'Ф', 'Ы', 'В', 'А', 'П', 'Р', 'О', 'Л', 'Д', 'Ж', 'Э',
            'ф', 'ы', 'в', 'а', 'п', 'р', 'о', 'л', 'д', 'ж', 'э',
            'Я', 'Ч', 'С', 'М', 'И', 'Т', 'Ь', 'Б', 'Ю',
            'я', 'ч', 'с', 'м', 'и', 'т', 'ь', 'б', 'ю'
        ];
        $latin = [
            'Yo', 'Y', 'Ts', 'U', 'K', 'E', 'N', 'G', 'Sh', 'Sch', 'Z', 'X', "'",
            'yo', 'y', 'ts', 'u', 'k', 'e', 'n', 'g', 'sh', 'sch', 'z', 'x', "'",
            'F', 'I', 'V', 'A', 'P', 'R', 'O', 'L', 'D', 'J', 'E',
            'f', 'i', 'v', 'a', 'p', 'r', 'o', 'l', 'd', 'j', 'e',
            'Ya', 'Ch', 'S', 'M', 'I', 'T', "'", 'B', 'Yu',
            'ya', 'ch', 's', 'm', 'i', 't', "'", 'b', 'yu'
        ];

        // O'zbekcha maxsus harflar (sh, ch, yo, yu, ya)
        $cyrSpec = ['Ў', 'ў', 'Қ', 'қ', 'Ғ', 'ғ', 'Ҳ', 'ҳ'];
        $latSpec = ["O'", "o'", 'Q', 'q', 'G', 'g', 'H', 'h'];

        if ($mode === 'toLatin') {
            $text = str_replace($cyrSpec, $latSpec, $text);
            return str_replace($cyrillic, $latin, $text);
        } else {
            $text = str_replace($latSpec, $cyrSpec, $text);
            return str_replace($latin, $cyrillic, $text);
        }
    }
}
