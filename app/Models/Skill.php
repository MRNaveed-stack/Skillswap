<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Skill extends Model
{
    use HasUuids;

    protected $fillable = [
        'category_id',
        'title',
        'description',
        'slug',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(SkillCategory::class, 'category_id');
    }

    public function userSkills()
    {
        return $this->hasMany(UserSkill::class);
    }
}
