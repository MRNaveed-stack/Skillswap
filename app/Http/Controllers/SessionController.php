<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function index()
    {
        $learningSessions = Session::with(['mentor.profile', 'userSkill.skill'])
            ->where('learner_id', Auth::id())
            ->orderBy('scheduled_start', 'asc')
            ->get();

        $teachingSessions = Session::with(['learner.profile', 'userSkill.skill'])
            ->where('mentor_id', Auth::id())
            ->orderBy('scheduled_start', 'asc')
            ->get();

        return view('sessions.index', compact('learningSessions', 'teachingSessions'));
    }

    public function show($id)
    {
        $session = Session::with(['learner.profile', 'mentor.profile', 'userSkill.skill'])
            ->where(function($query) {
                $query->where('learner_id', Auth::id())
                      ->orWhere('mentor_id', Auth::id());
            })
            ->findOrFail($id);

        return view('sessions.show', compact('session'));
    }

    public function update(Request $request, $id)
    {
        $session = Session::where('mentor_id', Auth::id())->findOrFail($id);

        $request->validate([
            'status' => ['required', 'in:completed,no_show'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $session->update([
                'status' => $request->status,
                'actual_end' => now(), // Assume ended now
                'notes' => $request->notes,
            ]);

            if ($request->status === 'completed') {
                // Transfer credits to mentor wallet
                $mentorWallet = Auth::user()->wallet;
                $mentorWallet->balance += $session->credits_charged;
                $mentorWallet->total_earned += $session->credits_charged;
                $mentorWallet->save();

                // Update learner's spent stats
                $learnerWallet = $session->learner->wallet;
                $learnerWallet->total_spent += $session->credits_charged;
                $learnerWallet->save();

                // Increment completion counters
                $session->mentor->profile->increment('sessions_completed_as_mentor');
                $session->learner->profile->increment('sessions_completed_as_learner');
            } elseif ($request->status === 'no_show') {
                // In a real app, maybe partial refund or full refund depending on policy
                // For now, mentor keeps it.
                $mentorWallet = Auth::user()->wallet;
                $mentorWallet->balance += $session->credits_charged;
                $mentorWallet->total_earned += $session->credits_charged;
                $mentorWallet->save();
            }

            DB::commit();
            return redirect()->route('sessions.show', $session->id)->with('success', 'Session marked as ' . $request->status);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'An error occurred.']);
        }
    }
}
