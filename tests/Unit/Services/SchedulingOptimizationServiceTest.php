<?php

namespace Tests\Unit\Services;

use App\Services\SchedulingOptimizationService;
use PHPUnit\Framework\TestCase;

class SchedulingOptimizationServiceTest extends TestCase
{
    public function test_it_selects_the_maximum_number_of_non_overlapping_sessions(): void
    {
        $service = new SchedulingOptimizationService();

        $plan = $service->optimize([
            [
                'id' => 'session-a',
                'scheduled_start' => '2026-05-14 09:00:00',
                'scheduled_end' => '2026-05-14 10:00:00',
            ],
            [
                'id' => 'session-b',
                'scheduled_start' => '2026-05-14 10:00:00',
                'scheduled_end' => '2026-05-14 11:00:00',
            ],
            [
                'id' => 'session-c',
                'scheduled_start' => '2026-05-14 09:30:00',
                'scheduled_end' => '2026-05-14 10:30:00',
            ],
        ]);

        $this->assertCount(2, $plan['selected']);
        $this->assertSame(['session-a', 'session-b'], array_values(array_map(static fn (array $session): string => $session['id'], $plan['selected'])));
        $this->assertCount(1, $plan['rejected']);
        $this->assertSame('session-c', $plan['rejected'][0]['id']);
    }
}