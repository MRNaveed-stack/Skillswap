<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'password_hash',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function getAuthPassword()
    {
        return $this->password ?? $this->password_hash;
    }

    public function getRememberTokenName()
    {
        return ''; // Disable remember_token
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function userSkills()
    {
        return $this->hasMany(UserSkill::class);
    }

    public function availabilitySlots()
    {
        return $this->hasMany(AvailabilitySlot::class);
    }

    public function receivedReviews()
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function givenReviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function sessionsAsMentor()
    {
        return $this->hasMany(Session::class, 'mentor_id');
    }

    public function sessionsAsLearner()
    {
        return $this->hasMany(Session::class, 'learner_id');
    }
}
