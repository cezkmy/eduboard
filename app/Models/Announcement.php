<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'content',
        'category',
        'posted_by',
        'is_pinned',
        'pinned_at',
        'target_program',
        'target_year',
        'target_section',
        'media',
        'media_paths',
        'heart_count',
        'like_count',
        'fire_count',
        'sad_count'
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'pinned_at' => 'datetime',
        'media' => 'array',
        'media_paths' => 'array'
    ];

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }
}
