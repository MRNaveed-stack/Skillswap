<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkillCategory extends Model
{
    public $timestamps = false; // Only has created_at, we can handle it or let Laravel handle if we set UPDATED_AT to null
    const UPDATED_AT = null;

    protected $fillable = [
        'name',
        'slug',
    ];

    public function skills()
    {
        return $this->hasMany(Skill::class, 'category_id');
    }
}
