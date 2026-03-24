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
        return back()->with('success', 'Bot holati o\'zgartirildi.');
    }

    public function toggleWebhook(ContestBot $bot)
    {
        try {
            if ($bot->webhook_set) {
                $this->botService->deleteWebhook($bot);
                $bot->update(['webhook_set' => false]);
                $msg = 'Webhook o\'chirildi.';
            } else {
                $this->botService->setupBot($bot);
                $bot->update(['webhook_set' => true]);
                $msg = 'Webhook o\'rnatildi.';
            }
            return back()->with('success', $msg);
        } catch (\Exception $e) {
            return back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }

    public function resetWebhook(ContestBot $bot)
    {
        try {
            $this->botService->setupBot($bot);
            $bot->update(['webhook_set' => true]);
            return back()->with('success', 'Webhook qayta o\'rnatildi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Xatolik: ' . $e->getMessage());
        }
    }
}
