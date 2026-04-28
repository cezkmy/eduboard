<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'content',
        'template_id',
        'bg_color',
        'layout_type',
        'border_radius',
        'media_layout',
        'font_style',
        'title_color',
        'content_color',
        'category_color',
        'border_color',
        'category',
        'status',
        'posted_by',
        'is_pinned',
        'pinned_at',
        'target_college',
        'target_program',
        'target_year',
        'target_grade_level',
        'target_strand',
        'target_section',
        'target_roles',
        'media',
        'media_paths',
        'attachments',
        'heart_count',
        'like_count',
        'fire_count',
        'sad_count'
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'pinned_at' => 'datetime',
        'media' => 'array',
        'media_paths' => 'array',
        'attachments' => 'array',
        'target_college' => 'array',
        'target_program' => 'array',
        'target_year' => 'array',
        'target_grade_level' => 'array',
        'target_strand' => 'array',
        'target_section' => 'array',
        'target_roles' => 'array'
    ];

    public function scopeForUser($query, $user)
    {
        // Admins and the Author should see everything
        if ($user->role === 'admin') {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            // Always show to the author
            $q->where('posted_by', $user->id)
              ->orWhere(function ($sq) use ($user) {
                // Role Targeting (Exclusive if set)
                $sq->where(function($rsq) use ($user) {
                    $rsq->whereNull('target_roles')
                        ->orWhereJsonContains('target_roles', $user->role);
                });

                // Department/College Targeting (Exclusive if set)
                $sq->where(function($csq) use ($user) {
                    $csq->whereNull('target_college')
                        ->orWhereJsonContains('target_college', $user->department);
                });

                // Program/Course Targeting (Exclusive if set)
                $sq->where(function($psq) use ($user) {
                    $psq->whereNull('target_program')
                        ->orWhereJsonContains('target_program', $user->course);
                });

                // Year/Grade Level (Unified check)
                $sq->where(function($lsq) use ($user) {
                    $lsq->where(function($inner) {
                        $inner->whereNull('target_year')->whereNull('target_grade_level');
                    })
                    ->orWhereJsonContains('target_year', $user->year_level)
                    ->orWhereJsonContains('target_grade_level', $user->year_level);
                });

                // Section Targeting (Exclusive if set)
                $sq->where(function($ssq) use ($user) {
                    $ssq->whereNull('target_section')
                        ->orWhereJsonContains('target_section', $user->section);
                });

                // Strand Targeting (Exclusive if set)
                $sq->where(function($strq) use ($user) {
                    $strq->whereNull('target_strand')
                        ->orWhereJsonContains('target_strand', $user->strand);
                });
            });
        });
    }

    public function isTargeted()
    {
        return !empty($this->target_roles) || 
               !empty($this->target_college) || 
               !empty($this->target_program) || 
               !empty($this->target_year) || 
               !empty($this->target_grade_level) || 
               !empty($this->target_section) || 
               !empty($this->target_strand);
    }

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
