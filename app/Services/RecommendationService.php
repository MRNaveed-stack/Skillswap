<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Skill;
use App\Models\User;
use App\Models\UserSkill;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RecommendationService
{
    public const SEARCH_HISTORY_PREFIX = 'skillswap.search.history';

    public function __construct(private readonly ReputationRankingService $reputationRankingService)
    {
    }

    /**
     * Store recent skill searches in cache so the recommendation layer can reuse real user intent
     * without introducing a new database table.
     */
    public function rememberSearchTerms(User $user, array $terms): void
    {
        $normalizedTerms = $this->normalizeTerms($terms);

        if ($normalizedTerms === []) {
            return;
        }

        $key = $this->searchHistoryKey($user);
        $history = collect(Cache::get($key, []))
            ->merge($normalizedTerms)
            ->unique()
            ->take(20)
            ->values()
            ->all();

        Cache::put($key, $history, now()->addDays(30));
    }

    /**
     * Recommend mentors using explainable weights for recent searches, category similarity,
     * review quality, and profile response rate.
     */
    public function recommendMentors(User $user, int $limit = 8, array $searchTerms = []): array
    {
        $limit = max(1, $limit);
        $searchTerms = $this->normalizeTerms($searchTerms !== [] ? $searchTerms : Cache::get($this->searchHistoryKey($user), []));

        $matchingSkills = $this->matchingSkillsForTerms($searchTerms);
        $matchingCategories = $matchingSkills->pluck('category_id')->filter()->unique()->values()->all();
        $trustScores = $this->reputationRankingService->refreshTrustScores();

        $candidateGroups = UserSkill::query()
            ->with([
                'user.profile',
                'user.receivedReviews',
                'user.availabilitySlots',
                'skill.category',
            ])
            ->where('is_active', true)
            ->where('user_id', '!=', $user->id)
            ->get()
            ->groupBy('user_id');

        $recommendations = $candidateGroups->map(function (Collection $listings) use ($searchTerms, $matchingSkills, $matchingCategories, $trustScores): array {
            $mentor = $listings->first()?->user;
            $profile = $mentor?->profile;
            $mentorTrust = (float) ($trustScores[$mentor?->id]['trust_score'] ?? 0.5);
            $averageRating = (float) ($mentor?->receivedReviews?->avg('rating') ?? 0);
            $ratingScore = $averageRating > 0 ? min(1.0, $averageRating / 5.0) : 0.5;
            $responseRateScore = $this->normalizePercentage($profile?->response_rate);

            $bestListing = $listings->map(function (UserSkill $listing) use ($searchTerms, $matchingSkills, $matchingCategories): array {
                $searchMatch = $this->searchMatchScore($listing, $searchTerms, $matchingSkills);
                $categoryScore = $this->categoryMatchScore($listing, $matchingCategories);

                return [
                    'listing' => $listing,
                    'search_match' => $searchMatch,
                    'category_score' => $categoryScore,
                    'component_score' => ($searchMatch * 0.45) + ($categoryScore * 0.20),
                ];
            })->sortByDesc('component_score')->first();

            $listing = $bestListing['listing'];
            $searchMatch = $bestListing['search_match'];
            $categoryScore = $bestListing['category_score'];

            $qualityScore = ($ratingScore * 0.7) + ($mentorTrust * 0.3);
            $finalScore = ($searchMatch * 0.45)
                + ($categoryScore * 0.20)
                + ($qualityScore * 0.25)
                + ($responseRateScore * 0.10);

            $reasons = $this->buildReasons(
                $listing,
                $searchTerms,
                $matchingCategories,
                $qualityScore,
                $responseRateScore,
                $mentorTrust
            );

            return [
                'mentor_id' => $mentor?->id,
                'mentor_name' => $profile?->full_name,
                'trust_score' => round($mentorTrust, 6),
                'score' => round($finalScore, 6),
                'listing' => $listing,
                'reasons' => $reasons,
            ];
        })->sortByDesc('score')->take($limit)->values()->all();

        return $recommendations;
    }

    private function matchingSkillsForTerms(array $searchTerms): Collection
    {
        if ($searchTerms === []) {
            return collect();
        }

        return Skill::query()
            ->with('category')
            ->where(function ($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->orWhere('title', 'ilike', '%' . $term . '%')
                        ->orWhere('description', 'ilike', '%' . $term . '%')
                        ->orWhereHas('category', function ($categoryQuery) use ($term) {
                            $categoryQuery->where('name', 'ilike', '%' . $term . '%')
                                ->orWhere('slug', 'ilike', '%' . $term . '%');
                        });
                }
            })
            ->get();
    }

    private function searchMatchScore(UserSkill $listing, array $searchTerms, Collection $matchingSkills): float
    {
        if ($searchTerms === []) {
            return 0.0;
        }

        $listingText = strtolower(implode(' ', array_filter([
            $listing->skill?->title,
            $listing->skill?->description,
            $listing->skill?->category?->name,
        ])));

        $termMatches = 0;
        foreach ($searchTerms as $term) {
            if ($term !== '' && str_contains($listingText, $term)) {
                $termMatches++;
            }
        }

        $matchScore = $termMatches / max(1, count($searchTerms));

        if ($matchingSkills->contains('id', $listing->skill_id)) {
            $matchScore = max($matchScore, 0.9);
        }

        return min(1.0, $matchScore);
    }

    private function categoryMatchScore(UserSkill $listing, array $matchingCategories): float
    {
        if ($matchingCategories === []) {
            return 0.5;
        }

        return in_array($listing->skill?->category_id, $matchingCategories, true) ? 1.0 : 0.0;
    }

    private function buildReasons(UserSkill $listing, array $searchTerms, array $matchingCategories, float $qualityScore, float $responseRateScore, float $trustScore): array
    {
        $reasons = [];

        if ($searchTerms !== [] && $this->termHitsListing($listing, $searchTerms)) {
            $reasons[] = 'Matches recent skill search terms.';
        }

        if ($matchingCategories !== [] && in_array($listing->skill?->category_id, $matchingCategories, true)) {
            $reasons[] = 'Shares a category with the user search history.';
        }

        if ($qualityScore >= 0.7) {
            $reasons[] = 'High review quality and trust propagation score.';
        }

        if ($responseRateScore >= 0.7) {
            $reasons[] = 'Reliable response rate.';
        }

        if ($trustScore >= 0.7) {
            $reasons[] = 'Strong trust score from the review graph.';
        }

        if ($reasons === []) {
            $reasons[] = 'High-quality mentor profile with balanced activity signals.';
        }

        return array_values(array_unique($reasons));
    }

    private function termHitsListing(UserSkill $listing, array $searchTerms): bool
    {
        $listingText = strtolower(implode(' ', array_filter([
            $listing->skill?->title,
            $listing->skill?->description,
            $listing->skill?->category?->name,
        ])));

        foreach ($searchTerms as $term) {
            if ($term !== '' && str_contains($listingText, $term)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeTerms(array $terms): array
    {
        return array_values(array_unique(array_filter(array_map(static function ($term): string {
            return strtolower(trim((string) $term));
        }, $terms))));
    }

    private function normalizePercentage(mixed $value): float
    {
        if ($value === null) {
            return 0.50;
        }

        return max(0.0, min(1.0, ((float) $value) / 100.0));
    }

    private function searchHistoryKey(User $user): string
    {
        return self::SEARCH_HISTORY_PREFIX . ':' . $user->getKey();
    }
}