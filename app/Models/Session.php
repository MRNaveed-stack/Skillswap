<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Session extends Model
{
    use HasUuids;

    protected $fillable = [
        'request_id',
        'learner_id',
        'mentor_id',
        'user_skill_id',
        'scheduled_start',
        'scheduled_end',
        'actual_start',
        'actual_end',
        'status',
        'credits_charged',
        'meeting_url',
        'notes',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
        'actual_start' => 'datetime',
        'actual_end' => 'datetime',
        'credits_charged' => 'decimal:2',
    ];

    public function sessionRequest()
    {
        return $this->belongsTo(SessionRequest::class, 'request_id');
    }

    public function learner()
    {
        return $this->belongsTo(User::class, 'learner_id');
    }

    public function mentor()
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function userSkill()
    {
        return $this->belongsTo(UserSkill::class);
    }
}
