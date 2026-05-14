<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class SchedulingOptimizationService
{
    /**
     * Greedy interval scheduling keeps the activity that finishes first, because an early
     * finishing session leaves the maximum remaining room for future sessions.
     *
     * Complexity: O(n log n) from the sort, followed by a single linear scan.
     */
    public function optimize(Collection|array $sessions, string $startField = 'scheduled_start', string $endField = 'scheduled_end'): array
    {
        $normalized = [];

        foreach ($this->asCollection($sessions) as $session) {
            $start = $this->extractDateTime($session, $startField);
            $end = $this->extractDateTime($session, $endField);

            if ($start === null || $end === null || $start->greaterThanOrEqualTo($end)) {
                continue;
            }

            $normalized[] = [
                'item' => $session,
                'start' => $start,
                'end' => $end,
            ];
        }

        usort($normalized, static function (array $left, array $right): int {
            $endComparison = $left['end']->getTimestamp() <=> $right['end']->getTimestamp();

            if ($endComparison !== 0) {
                return $endComparison;
            }

            return $left['start']->getTimestamp() <=> $right['start']->getTimestamp();
        });

        $selected = [];
        $rejected = [];
        $currentEnd = null;

        foreach ($normalized as $entry) {
            if ($currentEnd === null || $entry['start']->greaterThanOrEqualTo($currentEnd)) {
                $selected[] = $entry['item'];
                $currentEnd = $entry['end'];
                continue;
            }

            $rejected[] = $entry['item'];
        }

        return [
            'selected' => $selected,
            'rejected' => $rejected,
        ];
    }

    private function extractDateTime(mixed $session, string $field): ?CarbonInterface
    {
        $value = is_array($session) ? ($session[$field] ?? null) : ($session->{$field} ?? null);

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