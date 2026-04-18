<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['name', 'email', 'role', 'profile_photo', 'phone', 'address', 'password', 'school_name', 'status', 'trial_ends_at', 'plan', 'has_selected_template', 'school_domain', 'autologin_token', 'autologin_token_expires_at', 'employee_id', 'department', 'course', 'year_level', 'section', 'strand', 'settings', 'locked_until', 'custom_permissions'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;


    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'posted_by');
    }

    public function tenant()
    {
        return $this->hasOne(Tenant::class, 'owner_id');
    }

    /**
     * Determine the user's role.
     */
    public function getRoleAttribute(): string
    {
        return strtolower($this->attributes['role'] ?? 'user');
    }

    /**
     * Check if the user has selected a template.
     */
    public function getHasSelectedTemplateAttribute(): bool
    {
        return (bool) ($this->attributes['has_selected_template'] ?? false);
    }

    /**
     * Check if the user has a specific permission.
     * Evaluates custom_permissions first, then falls back to TenantRole defaults.
     */
    public function hasPermission(string $permission): bool
    {
        // For local dev or superadmins, you could bypass checking here if needed
        // if ($this->is_admin) return true; // Central admin check if applicable

        $custom = $this->custom_permissions ?? [];
        $granted = $custom['granted'] ?? [];
        $denied = $custom['denied'] ?? [];

        // 1. Explicitly denied?
        if (in_array($permission, $denied)) {
            return false;
        }

        // 2. Explicitly granted?
        if (in_array($permission, $granted)) {
            return true;
        }

        // 3. Fallback to Role Default
        $role = TenantRole::where('name', $this->role)->first();
        if ($role) {
            $rolePerms = $role->permissions ?? [];
            return in_array($permission, $rolePerms);
        }

        return false;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'settings' => 'array',
            'custom_permissions' => 'array',
        ];
    }
}
