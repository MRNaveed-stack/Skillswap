<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Notification extends Model
{
    use HasUuids;
    public $timestamps = false; // Only created_at in schema

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'is_read',
        'related_entity_type',
        'related_entity_id',
        'created_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
