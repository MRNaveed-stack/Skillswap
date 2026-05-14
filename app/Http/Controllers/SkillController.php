<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SkillCategory;
use App\Services\RecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    public function __construct(private readonly RecommendationService $recommendationService)
    {
    }

    public function index(Request $request)
    {
        if ($request->filled('search') && Auth::check()) {
            $this->recommendationService->rememberSearchTerms(Auth::user(), [
                $request->search,
                $request->filled('category') ? $request->category : null,
            ]);
        }

        $query = Skill::with('category')->where('is_active', true);
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }
        
        if ($request->has('category')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        $skills = $query->paginate(12);
        $categories = SkillCategory::orderBy('name')->get();

        return view('skills.index', compact('skills', 'categories'));
    }

    public function show($slug)
    {
        $skill = Skill::with('category')->where('slug', $slug)->firstOrFail();

        if (Auth::check()) {
            $this->recommendationService->rememberSearchTerms(Auth::user(), [
                $skill->title,
                $skill->category?->name,
            ]);
        }
        
        $mentors = $skill->userSkills()
            ->with(['user.profile', 'user.wallet'])
            ->where('is_active', true)
            ->paginate(10);

        return view('skills.show', compact('skill', 'mentors'));
    }
}
