<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Profile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'full_name',
        'bio',
        'avatar_url',
        'resume_url',
        'linkedin_url',
        'portfolio_url',
        'timezone',
        'total_credits_earned',
        'total_credits_spent',
        'response_rate',
        'sessions_completed_as_mentor',
        'sessions_completed_as_learner',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
