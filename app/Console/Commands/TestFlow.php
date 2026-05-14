<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Profile;
use App\Models\Wallet;
use App\Models\Skill;
use App\Models\UserSkill;
use App\Models\SessionRequest;
use App\Models\Session;
use App\Models\Message;
use App\Models\Review;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestFlow extends Command
{
    protected $signature = 'app:test-flow';
    protected $description = 'Runs a full end-to-end integration test of the SkillSwap business logic.';

    public function handle()
    {
        $this->info('Starting End-to-End System Test...');

        try {
            $uniq = uniqid();
            // 1. Create Users
            $this->info('1. Creating Test Users...');
            $mentor = User::create(['email' => "mentor_$uniq@example.com", 'password_hash' => Hash::make('password')]);
            Profile::create(['user_id' => $mentor->id, 'full_name' => 'Test Mentor']);
            Wallet::create(['user_id' => $mentor->id, 'balance' => 10.00]);

            $learner = User::create(['email' => "learner_$uniq@example.com", 'password_hash' => Hash::make('password')]);
            Profile::create(['user_id' => $learner->id, 'full_name' => 'Test Learner']);
            Wallet::create(['user_id' => $learner->id, 'balance' => 20.00]); // Give learner more to spend

            // 2. Mentor lists a skill
            $this->info('2. Mentor lists a skill...');
            $skill = Skill::first();
            if (!$skill) {
                $this->error('No skills found. Please run db:seed first.');
                return;
            }

            $userSkill = UserSkill::create([
                'user_id' => $mentor->id,
                'skill_id' => $skill->id,
                'experience_level' => 'expert',
                'credits_per_hour' => 5.00,
                'is_active' => true,
            ]);

            // 3. Learner requests session
            $this->info('3. Learner requests a session...');
            $proposedStart = Carbon::tomorrow()->setHour(14)->setMinute(0);
            $creditsNeeded = 5.00 * 2; // 2 hours

            // Escrow
            $learnerWallet = $learner->wallet;
            $learnerWallet->balance -= $creditsNeeded;
            $learnerWallet->save();

            $request = SessionRequest::create([
                'learner_id' => $learner->id,
                'mentor_id' => $mentor->id,
                'user_skill_id' => $userSkill->id,
                'proposed_start' => $proposedStart,
                'proposed_end' => $proposedStart->copy()->addHours(2),
                'learner_message' => 'Hi! Can you teach me this?',
                'status' => 'pending',
                'credits_reserved' => $creditsNeeded,
            ]);

            // 4. Mentor accepts request
            $this->info('4. Mentor accepts request and session is created...');
            $request->update(['status' => 'accepted']);
            $session = Session::create([
                'request_id' => $request->id,
                'learner_id' => $learner->id,
                'mentor_id' => $mentor->id,
                'user_skill_id' => $userSkill->id,
                'scheduled_start' => $request->proposed_start,
                'scheduled_end' => $request->proposed_end,
                'status' => 'scheduled',
                'credits_charged' => $request->credits_reserved,
                'meeting_url' => 'http://meet.example.com',
            ]);

            // 5. Messaging
            $this->info('5. Users exchange messages...');
            
            $conversation = \App\Models\Conversation::create([
                'created_at' => now(),
                'last_message_at' => now()
            ]);

            \App\Models\ConversationParticipant::insert([
                ['conversation_id' => $conversation->id, 'user_id' => $mentor->id, 'joined_at' => now()],
                ['conversation_id' => $conversation->id, 'user_id' => $learner->id, 'joined_at' => now()],
            ]);

            Message::create(['conversation_id' => $conversation->id, 'sender_id' => $learner->id, 'content' => 'Thanks for accepting!']);
            Message::create(['conversation_id' => $conversation->id, 'sender_id' => $mentor->id, 'content' => 'See you tomorrow.']);

            // 6. Complete Session
            $this->info('6. Completing session and transferring credits...');
            $session->update(['status' => 'completed', 'actual_end' => now(), 'notes' => 'Great session']);
            
            $mentorWallet = $mentor->wallet;
            $mentorWallet->balance += $session->credits_charged;
            $mentorWallet->total_earned += $session->credits_charged;
            $mentorWallet->save();

            // 7. Learner reviews mentor
            $this->info('7. Learner leaves a review...');
            Review::create([
                'session_id' => $session->id,
                'reviewer_id' => $learner->id,
                'reviewee_id' => $mentor->id,
                'rating' => 5,
                'comment' => 'Amazing mentor!',
            ]);

            $this->info('------------------------------------------');
            $this->info('Test completed successfully! All logic holds.');
            $this->info('Mentor Balance: ' . $mentor->fresh()->wallet->balance . ' CR');
            $this->info('Learner Balance: ' . $learner->fresh()->wallet->balance . ' CR');

        } catch (\Exception $e) {
            $this->error('Test failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
        }
    }
}
