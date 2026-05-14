<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Session;
use App\Services\ReputationRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class ReviewController extends Controller
{
    public function __construct(private readonly ReputationRankingService $reputationRankingService)
    {
    }

    public function create($sessionId)
    {
        $session = Session::with(['learner.profile', 'mentor.profile', 'userSkill.skill'])
            ->where('id', $sessionId)
            ->where('status', 'completed')
            ->firstOrFail();

        // Ensure current user is part of the session
        if (Auth::id() !== $session->learner_id && Auth::id() !== $session->mentor_id) {
            abort(403, 'Unauthorized action.');
        }

        // Check if review already exists
        $existingReview = Review::where('session_id', $sessionId)
            ->where('reviewer_id', Auth::id())
            ->first();

        if ($existingReview) {
            return redirect()->route('sessions.show', $sessionId)->withErrors(['error' => 'You have already reviewed this session.']);
        }

        $reviewee = Auth::id() === $session->learner_id ? $session->mentor : $session->learner;

        return view('reviews.create', compact('session', 'reviewee'));
    }

    public function store(Request $request, $sessionId)
    {
        $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $session = Session::findOrFail($sessionId);
        
        $revieweeId = Auth::id() === $session->learner_id ? $session->mentor_id : $session->learner_id;

        Review::create([
            'session_id' => $session->id,
            'reviewer_id' => Auth::id(),
            'reviewee_id' => $revieweeId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        Cache::forget($this->reputationRankingService->cacheKey());
        $trustScores = $this->reputationRankingService->refreshTrustScores();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your review has been submitted.',
                'reviewee_id' => $revieweeId,
                'trust_score' => $trustScores[$revieweeId]['trust_score'] ?? null,
            ], 201);
        }

        return redirect()->route('sessions.show', $session->id)->with('success', 'Your review has been submitted.');
    }
}
