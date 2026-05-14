<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Skill;
use App\Models\UserSkill;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use SplPriorityQueue;

class MentorMatchingService
{
    /**
     * Rank mentors with a weighted scoring model and keep only the best K candidates.
     *
     * The queue is maintained at size K, so each insert/extract operation costs log K.
     * Overall complexity is O(n log k), which is appropriate for a top-k selection problem.
     */
    public function topK(Collection|array $mentorListings, int $limit = 10, array $context = []): array
    {
        $limit = max(1, $limit);
        $queue = new SplPriorityQueue();
        $queue->setExtractFlags(SplPriorityQueue::EXTR_BOTH);

        foreach ($this->asCollection($mentorListings) as $mentorSkill) {
            $evaluation = $this->scoreMentor($mentorSkill, $context);
            $queue->insert($evaluation, -$evaluation['score']);

            if ($queue->count() > $limit) {
                $queue->extract();
            }
        }

        $ranked = [];
        $buffer = clone $queue;

        while (!$buffer->isEmpty()) {
            $item = $buffer->extract();
            $ranked[] = $item['data'];
        }

        usort($ranked, static fn (array $left, array $right): int => $right['score'] <=> $left['score']);

        return $ranked;
    }

    private function scoreMentor(UserSkill $mentorSkill, array $context): array
    {
        $targetSkillId = $context['target_skill_id'] ?? null;
        $targetCategoryId = $context['target_category_id'] ?? null;
        $searchTerms = $this->normalizeTerms($context['search_terms'] ?? []);
        $desiredStart = $this->toCarbon($context['desired_start'] ?? null);
        $desiredEnd = $this->toCarbon($context['desired_end'] ?? null);

        $profile = $mentorSkill->user?->profile;
        $reviews = $mentorSkill->user?->receivedReviews ?? collect();

        $ratingScore = $this->normalizeRating((float) ($reviews->avg('rating') ?? 0));
        $experienceScore = $this->experienceScore((string) ($mentorSkill->experience_level ?? 'intermediate'));
        $skillRelevanceScore = $this->skillRelevanceScore($mentorSkill, $targetSkillId, $targetCategoryId, $searchTerms);
        $availabilityScore = $this->availabilityScore($mentorSkill, $desiredStart, $desiredEnd);
        $responseRateScore = $this->normalizePercentage($profile?->response_rate);

        $score = ($ratingScore * 0.35)
            + ($experienceScore * 0.25)
            + ($skillRelevanceScore * 0.20)
            + ($availabilityScore * 0.10)
            + ($responseRateScore * 0.10);

        return [
            'mentor_skill' => $mentorSkill,
            'score' => round($score, 6),
            'breakdown' => [
                'rating' => round($ratingScore, 4),
                'experience' => round($experienceScore, 4),
                'skill_relevance' => round($skillRelevanceScore, 4),
                'availability' => round($availabilityScore, 4),
                'response_rate' => round($responseRateScore, 4),
            ],
        ];
    }

    private function skillRelevanceScore(UserSkill $mentorSkill, mixed $targetSkillId, mixed $targetCategoryId, array $searchTerms): float
    {
        if ($targetSkillId !== null && (string) $mentorSkill->skill_id === (string) $targetSkillId) {
            return 1.0;
        }

        if ($targetCategoryId !== null && (string) $mentorSkill->skill?->category_id === (string) $targetCategoryId) {
            return 0.85;
        }

        if ($searchTerms === []) {
            return 0.50;
        }

        $haystack = strtolower(implode(' ', array_filter([
            $mentorSkill->skill?->title,
            $mentorSkill->skill?->description,
            $mentorSkill->skill?->category?->name,
        ])));

        $matches = 0;
        foreach ($searchTerms as $term) {
            if ($term !== '' && str_contains($haystack, $term)) {
                $matches++;
            }
        }

        return min(1.0, $matches / max(1, count($searchTerms)));
    }

    private function availabilityScore(UserSkill $mentorSkill, ?CarbonInterface $desiredStart, ?CarbonInterface $desiredEnd): float
    {
        $slots = $mentorSkill->user?->availabilitySlots ?? collect();

        if ($desiredStart === null || $desiredEnd === null) {
            return $slots->isNotEmpty() ? 1.0 : 0.0;
        }

        $requestedMinutes = max(1, $desiredStart->diffInMinutes($desiredEnd));
        $requestedDay = (int) $desiredStart->dayOfWeek;
        $bestOverlap = 0.0;

        foreach ($slots as $slot) {
            if ((int) $slot->day_of_week !== $requestedDay) {
                continue;
            }

            if ($slot->valid_from !== null && $desiredStart->toDateString() < $slot->valid_from->toDateString()) {
                continue;
            }

            if ($slot->valid_until !== null && $desiredStart->toDateString() > $slot->valid_until->toDateString()) {
                continue;
            }

            $slotStart = Carbon::parse($desiredStart->toDateString() . ' ' . $slot->start_time, $desiredStart->getTimezone());
            $slotEnd = Carbon::parse($desiredStart->toDateString() . ' ' . $slot->end_time, $desiredStart->getTimezone());

            $overlapStart = $desiredStart->greaterThan($slotStart) ? $desiredStart : $slotStart;
            $overlapEnd = $desiredEnd->lessThan($slotEnd) ? $desiredEnd : $slotEnd;

            if ($overlapEnd->lessThanOrEqualTo($overlapStart)) {
                continue;
            }

            $bestOverlap = max($bestOverlap, $overlapStart->diffInMinutes($overlapEnd) / $requestedMinutes);
        }

        return min(1.0, $bestOverlap);
    }

    private function experienceScore(string $level): float
    {
        return match (strtolower($level)) {
            'beginner' => 0.25,
            'intermediate' => 0.50,
            'advanced' => 0.75,
            'expert' => 1.00,
            default => 0.50,
        };
    }

    private function normalizeRating(float $rating): float
    {
        if ($rating <= 0) {
            return 0.50;
        }

        return max(0.0, min(1.0, $rating / 5.0));
    }

    private function normalizePercentage(mixed $value): float
    {
        if ($value === null) {
            return 0.50;
        }

        $percentage = (float) $value;

        return max(0.0, min(1.0, $percentage / 100.0));
    }

    private function normalizeTerms(array $terms): array
    {
        return array_values(array_unique(array_filter(array_map(static function ($term): string {
            return strtolower(trim((string) $term));
        }, $terms))));
    }

    private function toCarbon(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse($value);
    }

    private function asCollection(Collection|array $values): Collection
    {
        return $values instanceof Collection ? $values : collect($values);
    }
}