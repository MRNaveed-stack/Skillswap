<?php

namespace Tests\Unit\Services;

use App\Models\AvailabilitySlot;
use App\Models\Profile;
use App\Models\Review;
use App\Models\Skill;
use App\Models\SkillCategory;
use App\Models\User;
use App\Models\UserSkill;
use App\Services\MentorMatchingService;
use PHPUnit\Framework\TestCase;

class MentorMatchingServiceTest extends TestCase
{
    public function test_it_returns_the_highest_scoring_mentor_first(): void
    {
        $service = new MentorMatchingService();

        $category = new SkillCategory();
        $category->id = 1;
        $category->name = 'Programming';

        $skill = new Skill();
        $skill->id = 'skill-1';
        $skill->title = 'PHP';
        $skill->description = 'Backend development';
        $skill->category_id = $category->id;
        $skill->setRelation('category', $category);

        $highMentor = $this->buildMentorListing('mentor-high', 'expert', 4.8, 92, 1, $skill, $category, true);
        $lowMentor = $this->buildMentorListing('mentor-low', 'beginner', 2.1, 40, 2, $skill, $category, false);

        $ranked = $service->topK([
            $lowMentor,
            $highMentor,
        ], 1, [
            'target_skill_id' => $skill->id,
            'target_category_id' => $category->id,
            'search_terms' => ['php'],
        ]);

        $this->assertCount(1, $ranked);
        $this->assertSame('mentor-high', $ranked[0]['mentor_skill']->user_id);
        $this->assertGreaterThan(0.0, $ranked[0]['score']);
    }

    private function buildMentorListing(
        string $mentorId,
        string $experienceLevel,
        float $rating,
        float $responseRate,
        int $dayOfWeek,
        Skill $skill,
        SkillCategory $category,
        bool $matchedAvailability
    ): UserSkill {
        $user = new User();
        $user->id = $mentorId;

        $profile = new Profile();
        $profile->id = $mentorId . '-profile';
        $profile->full_name = 'Mentor ' . $mentorId;
        $profile->response_rate = $responseRate;

        $review = new Review();
        $review->rating = $rating;

        $slot = new AvailabilitySlot();
        $slot->day_of_week = $dayOfWeek;
        $slot->start_time = '09:00';
        $slot->end_time = '17:00';

        $user->setRelation('profile', $profile);
        $user->setRelation('receivedReviews', collect([$review]));
        $user->setRelation('availabilitySlots', $matchedAvailability ? collect([$slot]) : collect());

        $listing = new UserSkill();
        $listing->id = $mentorId . '-listing';
        $listing->user_id = $mentorId;
        $listing->skill_id = $skill->id;
        $listing->experience_level = $experienceLevel;
        $listing->is_active = true;
        $listing->setRelation('user', $user);
        $listing->setRelation('skill', $skill);

        return $listing;
    }
}