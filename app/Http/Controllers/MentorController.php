<?php

namespace App\Http\Controllers;

use App\Models\UserSkill;
use App\Models\Skill;
use App\Services\MentorMatchingService;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MentorController extends Controller
{
    public function __construct(
        private readonly MentorMatchingService $mentorMatchingService,
        private readonly RecommendationService $recommendationService,
    ) {
    }

    public function index(Request $request)
    {
        if ($request->filled('search') && Auth::check()) {
            $this->recommendationService->rememberSearchTerms(Auth::user(), [$request->search]);
        }

        $query = UserSkill::with(['user.profile', 'skill'])
            ->where('is_active', true);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($mentorQuery) use ($search) {
                $mentorQuery->whereHas('user.profile', function ($profileQuery) use ($search) {
                    $profileQuery->where('full_name', 'ilike', "%{$search}%");
                })->orWhereHas('skill', function ($skillQuery) use ($search) {
                    $skillQuery->where('title', 'ilike', "%{$search}%");
                });
            });
        }

        $mentors = $query->latest()->paginate(12);

        return view('mentors.index', compact('mentors'));
    }

    public function ranked(Request $request)
    {
        $limit = max(1, min(50, (int) $request->integer('limit', 12)));

        $query = UserSkill::query()->with([
            'user.profile',
            'user.receivedReviews',
            'user.availabilitySlots',
            'skill.category',
        ])->where('is_active', true);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($mentorQuery) use ($search) {
                $mentorQuery->whereHas('user.profile', function ($profileQuery) use ($search) {
                    $profileQuery->where('full_name', 'ilike', "%{$search}%");
                })->orWhereHas('skill', function ($skillQuery) use ($search) {
                    $skillQuery->where('title', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%");
                });
            });
        }

        $targetSkill = null;
        if ($request->filled('skill_slug')) {
            $targetSkill = Skill::with('category')->where('slug', $request->skill_slug)->first();
        }

        $rankedMentors = $this->mentorMatchingService->topK(
            $query->get(),
            $limit,
            [
                'target_skill_id' => $targetSkill?->id,
                'target_category_id' => $targetSkill?->category_id,
                'search_terms' => $this->extractSearchTerms($request->search ?? null),
                'desired_start' => $request->filled('desired_start') ? $request->desired_start : null,
                'desired_end' => $request->filled('desired_end') ? $request->desired_end : null,
            ]
        );

        $recommendations = Auth::check()
            ? $this->recommendationService->recommendMentors(Auth::user(), $limit, $this->extractSearchTerms($request->search ?? null))
            : [];

        return response()->json([
            'data' => $rankedMentors,
            'recommendations' => $recommendations,
            'meta' => [
                'limit' => $limit,
                'count' => count($rankedMentors),
                'algorithm' => 'weighted mentor matching + top-k priority queue',
            ],
        ]);
    }

    public function recommendations(Request $request)
    {
        abort_unless(Auth::check(), 401);

        $limit = max(1, min(20, (int) $request->integer('limit', 8)));

        return response()->json([
            'data' => $this->recommendationService->recommendMentors(
                Auth::user(),
                $limit,
                $this->extractSearchTerms($request->search ?? null)
            ),
        ]);
    }

    private function extractSearchTerms(?string $search): array
    {
        if ($search === null || trim($search) === '') {
            return [];
        }

        return preg_split('/\s+/', strtolower(trim($search))) ?: [];
    }
}
