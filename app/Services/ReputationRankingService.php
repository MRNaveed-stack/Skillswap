<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class ReputationRankingService
{
    public const CACHE_KEY = 'skillswap.reputation.rankings';

    /**
     * PageRank-style trust propagation over the review graph.
     *
     * Each iteration updates trust by mixing a user's direct review score with the
     * accumulated trust from reviewers who endorsed them. Five iterations are enough
     * for a stable presentation-layer ranking while keeping runtime predictable.
     */
    public function refreshTrustScores(int $iterations = 5, float $dampingFactor = 0.85): array
    {
        $cacheKey = $this->cacheKey();

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($iterations, $dampingFactor) {
            $reviews = Review::query()->get(['reviewer_id', 'reviewee_id', 'rating']);

            if ($reviews->isEmpty()) {
                return [];
            }

            $userIds = $this->collectUserIds($reviews);
            $trustScores = $userIds->mapWithKeys(static fn (string $userId): array => [$userId => 0.5])->all();
            $incomingReviews = $reviews->groupBy('reviewee_id');

            $averageRatings = $incomingReviews->map(static function (Collection $items): float {
                return (float) $items->avg('rating');
            })->all();

            for ($iteration = 0; $iteration < $iterations; $iteration++) {
                $nextTrustScores = [];
                $normalization = max(1, $userIds->count());

                foreach ($userIds as $userId) {
                    $receivedReviews = $incomingReviews->get($userId, collect());
                    $baseRating = isset($averageRatings[$userId]) ? max(0.0, min(1.0, $averageRatings[$userId] / 5.0)) : 0.5;

                    $weightedReviewerTrust = 0.0;
                    $weightTotal = 0.0;

                    foreach ($receivedReviews as $review) {
                        $reviewerTrust = (float) ($trustScores[$review->reviewer_id] ?? 0.5);
                        $reviewWeight = max(0.2, ((float) $review->rating) / 5.0);

                        $weightedReviewerTrust += $reviewerTrust * $reviewWeight;
                        $weightTotal += $reviewWeight;
                    }

                    $weightedReviewerTrust = $weightTotal > 0 ? $weightedReviewerTrust / $weightTotal : $baseRating;

                    $rawTrust = ($baseRating * 0.7) + ($weightedReviewerTrust * 0.3);
                    $nextTrustScores[$userId] = ((1.0 - $dampingFactor) / $normalization) + ($dampingFactor * $rawTrust);
                }

                $trustScores = $nextTrustScores;
            }

            $ranked = collect($trustScores)
                ->map(static fn (float $score, string $userId): array => [
                    'user_id' => $userId,
                    'trust_score' => round(max(0.0, min(1.0, $score)), 6),
                    'total_reviews' => (int) ($incomingReviews->get($userId, collect())->count()),
                    'average_rating' => isset($averageRatings[$userId]) ? round((float) $averageRatings[$userId], 2) : null,
                ])
                ->sortByDesc('trust_score')
                ->values()
                ->map(function (array $row, int $index): array {
                    $row['rank_position'] = $index + 1;

                    return $row;
                })
                ->keyBy('user_id')
                ->all();

            return $ranked;
        });
    }

    public function cacheKey(): string
    {
        return self::CACHE_KEY;
    }

    private function collectUserIds(Collection $reviews): Collection
    {
        return collect($reviews)
            ->flatMap(static fn ($review): array => [$review->reviewer_id, $review->reviewee_id])
            ->filter()
            ->unique()
            ->values();
    }
}