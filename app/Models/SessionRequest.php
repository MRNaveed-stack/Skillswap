<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SessionRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'learner_id',
        'mentor_id',
        'user_skill_id',
        'proposed_start',
        'proposed_end',
        'learner_message',
        'status',
        'credits_reserved',
        'rejection_reason',
    ];

    protected $casts = [
        'proposed_start' => 'datetime',
        'proposed_end' => 'datetime',
        'credits_reserved' => 'decimal:2',
    ];

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
