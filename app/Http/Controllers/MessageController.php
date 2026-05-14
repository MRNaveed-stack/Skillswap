<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // Find all conversations the user is a part of
        $conversations = Conversation::whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->with(['participants.user.profile', 'messages' => function($q) {
            $q->orderBy('created_at', 'desc')->take(1);
        }])
        ->orderBy('last_message_at', 'desc')
        ->get();

        $contacts = collect();

        foreach ($conversations as $conversation) {
            $otherParticipant = $conversation->participants->where('user_id', '!=', $userId)->first();
            if (!$otherParticipant) continue;

            $contact = $otherParticipant->user;
            $contact->conversation_id = $conversation->id;
            $contact->latestMessage = $conversation->messages->first();
            
            // Simplified unread logic (could be improved using last_read_message_id)
            $contact->unreadCount = 0; 
            
            $contacts->push($contact);
        }

        return view('messages.index', compact('contacts'));
    }

    public function show($userId)
    {
        $currentUserId = Auth::id();
        $contact = User::with('profile')->findOrFail($userId);

        // Find existing conversation
        $conversation = Conversation::whereHas('participants', function ($q) use ($currentUserId) {
            $q->where('user_id', $currentUserId);
        })->whereHas('participants', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->first();

        if (!$conversation) {
            $messages = collect();
        } else {
            $messages = $conversation->messages()->with('sender.profile')->orderBy('created_at', 'asc')->get();
        }

        return view('messages.show', compact('messages', 'contact'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'receiver_id' => ['required', 'exists:users,id'],
            'content' => ['required', 'string', 'max:2000'],
        ]);

        $currentUserId = Auth::id();
        $receiverId = $request->receiver_id;

        DB::beginTransaction();
        try {
            $conversation = Conversation::whereHas('participants', function ($q) use ($currentUserId) {
                $q->where('user_id', $currentUserId);
            })->whereHas('participants', function ($q) use ($receiverId) {
                $q->where('user_id', $receiverId);
            })->first();

            if (!$conversation) {
                $conversation = Conversation::create([
                    'created_at' => now(),
                    'last_message_at' => now()
                ]);

                ConversationParticipant::insert([
                    ['conversation_id' => $conversation->id, 'user_id' => $currentUserId, 'joined_at' => now()],
                    ['conversation_id' => $conversation->id, 'user_id' => $receiverId, 'joined_at' => now()],
                ]);
            } else {
                $conversation->update(['last_message_at' => now()]);
            }

            Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $currentUserId,
                'content' => $request->content,
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to send message.']);
        }

        return redirect()->route('messages.show', $receiverId);
    }
}
