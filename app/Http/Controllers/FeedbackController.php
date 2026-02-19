<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index()
    {
        $feedbacks = Feedback::where('is_approved', true)
            ->where('is_public', true)
            ->latest()
            ->paginate(20);

        return view('feedback.index', compact('feedbacks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'is_public' => 'boolean'
        ]);

        Feedback::create([
            'content' => $request->content,
            'is_public' => $request->boolean('is_public'),
            'ip_address' => $request->ip(),
        ]);

        return redirect()->back()->with('success', 'Fikringiz uchun rahmat!');
    }

    public function like(Feedback $feedback)
    {
        $sessionKey = 'voted_feedback_' . $feedback->id;
        if (!session()->has($sessionKey)) {
            $feedback->increment('likes_count');
            session()->put($sessionKey, 'liked');
            return response()->json(['success' => true, 'likes' => $feedback->likes_count]);
        }
        return response()->json(['success' => false, 'message' => 'Siz allaqachon munosabat bildirdingiz.']);
    }

    public function dislike(Feedback $feedback)
    {
        $sessionKey = 'voted_feedback_' . $feedback->id;
        if (!session()->has($sessionKey)) {
            $feedback->increment('dislikes_count');
            session()->put($sessionKey, 'disliked');
            return response()->json(['success' => true, 'dislikes' => $feedback->dislikes_count]);
        }
        return response()->json(['success' => false, 'message' => 'Siz allaqachon munosabat bildirdingiz.']);
    }
}
