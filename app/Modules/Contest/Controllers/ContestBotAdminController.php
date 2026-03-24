<?php

namespace App\Modules\Contest\Controllers;

use App\Modules\Contest\Models\ContestBot;
use App\Modules\Contest\Services\ContestBotService;
use Illuminate\Http\Request;

class ContestBotAdminController
{
    protected ContestBotService $botService;

    public function __construct()
    {
        $this->botService = new ContestBotService();
    }

    public function index()
    {
        $bots = ContestBot::withCount('contests')->latest()->get();
        return view('contest-admin.bots.index', compact('bots'));
    }

    public function create()
    {
        return view('contest-admin.bots.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'token' => 'required|string',
        ]);

        $bot = ContestBot::create([
            'name' => $request->name,
            'token' => $request->token,
        ]);

        // Auto-setup webhook & get bot info
        $result = $this->botService->setupBot($bot);

        if (!($result['ok'] ?? false)) {
            return back()->withInput()->withErrors(['token' => 'Telegram token yaroqsiz yoki webhook o\'rnatilmadi.']);
        }

        return redirect()->route('contest-admin.bots.index')
            ->with('success', "Bot \"{$bot->name}\" (@{$bot->username}) muvaffaqiyatli yaratildi!");
    }

    public function edit(ContestBot $bot)
    {
        return view('contest-admin.bots.edit', compact('bot'));
    }

    public function update(Request $request, ContestBot $bot)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'token' => 'required|string',
        ]);

        $tokenChanged = $bot->token !== $request->token;

        $bot->update([
            'name' => $request->name,
            'token' => $request->token,
        ]);

        if ($tokenChanged) {
            $this->botService->setupBot($bot);
        }

        return redirect()->route('contest-admin.bots.index')
            ->with('success', "Bot \"{$bot->name}\" yangilandi!");
    }

    public function destroy(ContestBot $bot)
    {
        $this->botService->removeWebhook($bot);
        $bot->delete();

        return redirect()->route('contest-admin.bots.index')
            ->with('success', 'Bot o\'chirildi!');
    }

    public function toggleActive(ContestBot $bot)
    {
        $bot->update(['is_active' => !$bot->is_active]);

        $status = $bot->is_active ? 'faollashtirildi' : 'o\'chirildi';
        return redirect()->route('contest-admin.bots.index')
            ->with('success', "Bot {$status}!");
    }

    public function resetWebhook(ContestBot $bot)
    {
        $result = $this->botService->setupBot($bot);

        if ($result['ok'] ?? false) {
            return redirect()->route('contest-admin.bots.index')
                ->with('success', 'Webhook qayta o\'rnatildi!');
        }

        return redirect()->route('contest-admin.bots.index')
            ->withErrors(['webhook' => 'Webhook o\'rnatishda xatolik!']);
    }
}
