<?php

namespace App\Http\Controllers;

use App\Models\SessionRequest;
use App\Models\Session;
use App\Models\UserSkill;
use App\Services\SchedulingOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SessionRequestController extends Controller
{
    public function __construct(private readonly SchedulingOptimizationService $schedulingOptimizationService)
    {
    }

    public function index()
    {
        $incomingRequests = SessionRequest::with(['learner.profile', 'userSkill.skill'])
            ->where('mentor_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        $outgoingRequests = SessionRequest::with(['mentor.profile', 'userSkill.skill'])
            ->where('learner_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('session_requests.index', compact('incomingRequests', 'outgoingRequests'));
    }

    public function create(Request $request)
    {
        $userSkill = UserSkill::with(['user.profile', 'user.availabilitySlots', 'skill'])->findOrFail($request->user_skill_id);
        
        if ($userSkill->user_id === Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'You cannot request a session with yourself.']);
        }

        return view('session_requests.create', compact('userSkill'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_skill_id' => ['required', 'exists:user_skills,id'],
            'proposed_start' => ['required', 'date', 'after:now'],
            'duration_hours' => ['required', 'numeric', 'min:0.5', 'max:4'],
            'learner_message' => ['nullable', 'string', 'max:1000'],
        ]);

        $userSkill = UserSkill::findOrFail($request->user_skill_id);
        
        $start = Carbon::parse($request->proposed_start);
        $end = $start->copy()->addMinutes($request->duration_hours * 60);
        $creditsNeeded = $userSkill->credits_per_hour * $request->duration_hours;

        // Basic check for wallet balance
        $wallet = Auth::user()->wallet;
        if ($wallet->balance < $creditsNeeded) {
            return back()->withErrors(['error' => 'Insufficient credits for this session.'])->withInput();
        }

        if (!$this->canScheduleSession($userSkill->user_id, Auth::id(), $start, $end)) {
            return back()->withErrors(['error' => 'This time conflicts with an existing session. Please choose another slot.'])->withInput();
        }

        DB::beginTransaction();
        try {
            // Deduct credits temporarily (escrow logic essentially handled by deduction and status tracking)
            $wallet->balance -= $creditsNeeded;
            $wallet->save();

                SessionRequest::create([
                'learner_id' => Auth::id(),
                'mentor_id' => $userSkill->user_id,
                'user_skill_id' => $userSkill->id,
                'proposed_start' => $start,
                'proposed_end' => $end,
                'learner_message' => $request->learner_message,
                'status' => 'pending',
                'credits_reserved' => $creditsNeeded,
            ]);

            \App\Models\Notification::create([
                'user_id' => $userSkill->user_id,
                'type' => 'request_received',
                'title' => 'New Session Request',
                'body' => Auth::user()->profile->full_name . ' has requested a session for ' . $userSkill->skill->title,
                'related_entity_type' => 'session_request',
                'created_at' => now(),
            ]);

            DB::commit();
            return redirect()->route('session-requests.index')->with('success', 'Session request sent successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred. Please try again.']);
        }
    }

    public function update(Request $request, $id)
    {
        $sessionRequest = SessionRequest::with('userSkill.skill')->where('mentor_id', Auth::id())->findOrFail($id);

        $request->validate([
            'action' => ['required', 'in:accept,reject'],
            'rejection_reason' => ['nullable', 'string', 'max:500', 'required_if:action,reject'],
        ]);

        DB::beginTransaction();
        try {
            if ($request->action === 'accept') {
                if (!$this->canScheduleSession($sessionRequest->mentor_id, $sessionRequest->learner_id, $sessionRequest->proposed_start, $sessionRequest->proposed_end)) {
                    DB::rollBack();
                    return back()->withErrors(['error' => 'This request now conflicts with an existing session.']);
                }

                $sessionRequest->update(['status' => 'accepted']);
                
                // Create the scheduled session
                $session = Session::create([
                    'request_id' => $sessionRequest->id,
                    'learner_id' => $sessionRequest->learner_id,
                    'mentor_id' => $sessionRequest->mentor_id,
                    'user_skill_id' => $sessionRequest->user_skill_id,
                    'scheduled_start' => $sessionRequest->proposed_start,
                    'scheduled_end' => $sessionRequest->proposed_end,
                    'status' => 'scheduled',
                    'credits_charged' => $sessionRequest->credits_reserved,
                    'meeting_url' => 'https://meet.jit.si/SkillSwapSession-' . uniqid(), // Generates a real working free video room
                ]);

                \App\Models\Notification::create([
                    'user_id' => $sessionRequest->learner_id,
                    'type' => 'request_accepted',
                    'title' => 'Session Accepted!',
                    'body' => 'Your request for ' . $sessionRequest->userSkill->skill->title . ' has been accepted.',
                    'related_entity_type' => 'session',
                    'related_entity_id' => $session->id,
                    'created_at' => now(),
                ]);

                $message = 'Session accepted and scheduled.';
            } else {
                $sessionRequest->update([
                    'status' => 'rejected',
                    'rejection_reason' => $request->rejection_reason,
                ]);

                // Refund the learner's wallet
                $learnerWallet = $sessionRequest->learner->wallet;
                $learnerWallet->balance += $sessionRequest->credits_reserved;
                $learnerWallet->save();
                
                \App\Models\Notification::create([
                    'user_id' => $sessionRequest->learner_id,
                    'type' => 'request_rejected',
                    'title' => 'Session Declined',
                    'body' => 'Your request for ' . $sessionRequest->userSkill->skill->title . ' was declined.',
                    'related_entity_type' => 'session_request',
                    'related_entity_id' => $sessionRequest->id,
                    'created_at' => now(),
                ]);

                $message = 'Session request rejected. Credits refunded to learner.';
            }

            DB::commit();
            return redirect()->route('session-requests.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('SessionRequestUpdateError: ' . $e->getMessage());
            return back()->withErrors(['error' => 'An error occurred: ' . $e->getMessage()]);
        }
    }

    private function canScheduleSession(string|int $mentorId, string|int $learnerId, Carbon $start, Carbon $end): bool
    {
        $existingSessions = Session::query()
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where(function ($query) use ($mentorId, $learnerId) {
                $query->where('mentor_id', $mentorId)
                    ->orWhere('learner_id', $learnerId);
            })
            ->get(['id', 'scheduled_start', 'scheduled_end']);

        $candidate = [
            '__candidate' => true,
            'scheduled_start' => $start,
            'scheduled_end' => $end,
        ];

        $plan = $this->schedulingOptimizationService->optimize(
            $existingSessions->concat([$candidate]),
            'scheduled_start',
            'scheduled_end'
        );

        return collect($plan['selected'])->contains(static function ($item): bool {
            return is_array($item) && ($item['__candidate'] ?? false) === true;
        });
    }
}
