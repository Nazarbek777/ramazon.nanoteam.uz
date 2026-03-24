<?php

namespace App\Modules\Contest\Controllers;

use App\Modules\Contest\Models\ContestBot;
use App\Modules\Contest\Models\Contest;
use App\Modules\Contest\Models\ContestChannel;
use App\Modules\Contest\Models\ContestKeyword;
use App\Modules\Contest\Models\ContestParticipant;
use Illuminate\Http\Request;

class ContestAdminController
{
    // ── Contests CRUD ────────────────────────────────────

    public function index(ContestBot $bot)
    {
        $contests = $bot->contests()->withCount('participants', 'channels', 'keywords')->latest()->get();
        return view('contest-admin.contests.index', compact('bot', 'contests'));
    }

    public function create(ContestBot $bot)
    {
        return view('contest-admin.contests.create', compact('bot'));
    }

    public function store(Request $request, ContestBot $bot)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_text' => 'nullable|string',
            'rules_text' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'require_phone' => 'boolean',
            'require_channel_join' => 'boolean',
            'require_referral' => 'boolean',
            'referral_points' => 'integer|min:0',
        ]);

        $data['contest_bot_id'] = $bot->id;
        $data['require_phone'] = $request->has('require_phone');
        $data['require_channel_join'] = $request->has('require_channel_join');
        $data['require_referral'] = $request->has('require_referral');
        $data['referral_points'] = $request->input('referral_points', 1);

        Contest::create($data);

        return redirect()->route('contest-admin.bots.contests.index', $bot)
            ->with('success', 'Konkurs yaratildi!');
    }

    public function edit(ContestBot $bot, Contest $contest)
    {
        $contest->load('channels', 'keywords');
        return view('contest-admin.contests.edit', compact('bot', 'contest'));
    }

    public function update(Request $request, ContestBot $bot, Contest $contest)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_text' => 'nullable|string',
            'rules_text' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'require_phone' => 'boolean',
            'require_channel_join' => 'boolean',
            'require_referral' => 'boolean',
            'referral_points' => 'integer|min:0',
            'referral_text' => 'nullable|string',
            'referral_button_text' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = $request->all();
        $data['require_phone'] = $request->has('require_phone');
        $data['require_channel_join'] = $request->has('require_channel_join');
        $data['require_referral'] = $request->has('require_referral');
        $data['is_active'] = $request->has('is_active');
        // The instruction removed the default for referral_points, so it will be null if not provided.
        // If a default is needed, it should be added back here or in validation.

        $contest->update($data);

        return redirect()->route('contest-admin.bots.contests.edit', [$bot, $contest])
            ->with('success', 'Konkurs yangilandi!');
    }

    public function destroy(ContestBot $bot, Contest $contest)
    {
        $contest->delete();
        return redirect()->route('contest-admin.bots.contests.index', $bot)
            ->with('success', 'Konkurs o\'chirildi!');
    }

    // ── Channels ──────────────────────────────────────────

    public function storeChannel(Request $request, ContestBot $bot, Contest $contest)
    {
        $request->validate([
            'channel_id' => 'required|string',
            'channel_name' => 'required|string|max:255',
            'channel_url' => 'nullable|url',
        ]);

        ContestChannel::create([
            'contest_id' => $contest->id,
            'channel_id' => $request->channel_id,
            'channel_name' => $request->channel_name,
            'channel_url' => $request->channel_url,
        ]);

        return redirect()->route('contest-admin.bots.contests.edit', [$bot, $contest])
            ->with('success', 'Kanal qo\'shildi!');
    }

    public function destroyChannel(ContestBot $bot, Contest $contest, ContestChannel $channel)
    {
        $channel->delete();
        return redirect()->route('contest-admin.bots.contests.edit', [$bot, $contest])
            ->with('success', 'Kanal o\'chirildi!');
    }

    // ── Keywords ──────────────────────────────────────────

    public function storeKeyword(Request $request, ContestBot $bot, Contest $contest)
    {
        $request->validate([
            'keyword' => 'required|string',
            'response_text' => 'required|string',
            'response_photo' => 'nullable|string',
            'is_menu_button' => 'nullable|boolean',
            'action' => 'nullable|string',
            'sort_order' => 'integer',
        ]);

        $contest->keywords()->create([
            'keyword' => $request->keyword,
            'response_text' => $request->response_text,
            'response_photo' => $request->response_photo,
            'is_menu_button' => $request->has('is_menu_button'),
            'action' => $request->action,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return redirect()->route('contest-admin.bots.contests.edit', [$bot, $contest])
            ->with('success', 'Kalit so\'z qo\'shildi!');
    }

    public function destroyKeyword(ContestBot $bot, Contest $contest, ContestKeyword $keyword)
    {
        $keyword->delete();
        return redirect()->route('contest-admin.bots.contests.edit', [$bot, $contest])
            ->with('success', 'Kalit so\'z o\'chirildi!');
    }

    // ── Participants ──────────────────────────────────────

    public function participants(Request $request, ContestBot $bot, Contest $contest)
    {
        $query = ContestParticipant::where('contest_id', $contest->id)
            ->where('is_registered', true);

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('username', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        $sortBy = $request->input('sort', 'points');
        $sortDir = $request->input('dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $participants = $query->paginate(50);

        return view('contest-admin.contests.participants', compact('bot', 'contest', 'participants'));
    }

    public function exportParticipants(ContestBot $bot, Contest $contest)
    {
        $participants = ContestParticipant::where('contest_id', $contest->id)
            ->where('is_registered', true)
            ->orderByDesc('points')
            ->get();

        $csv = "O'rin,Ism,Familiya,Username,Telefon,Do'stlar,Ballar\n";
        foreach ($participants as $i => $p) {
            $csv .= ($i + 1) . ",{$p->first_name},{$p->last_name},@{$p->username},{$p->phone},{$p->referral_count},{$p->points}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', "attachment; filename=participants_{$contest->id}.csv");
    }
}
