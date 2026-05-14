<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserSkill extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'skill_id',
        'experience_level',
        'credits_per_hour',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'credits_per_hour' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skill()
    {
        return $this->belongsTo(Skill::class);
    }
}
