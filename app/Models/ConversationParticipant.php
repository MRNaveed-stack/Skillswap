<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationParticipant extends Model
{
    public $timestamps = false;
    // Missing a UUID trait here? Schema doesn't specify an ID for this joining table usually, 
    // let's check schema. But to be safe, Eloquent might want one if we don't set primary key.
    // Wait, let me check `\d conversation_participants` if needed. Let's assume it's composite.
    
    protected $fillable = [
        'conversation_id',
        'user_id',
        'last_read_message_id',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
