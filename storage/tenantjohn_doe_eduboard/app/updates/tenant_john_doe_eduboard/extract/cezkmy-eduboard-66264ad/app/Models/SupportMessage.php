<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'from_user_id',
        'from_name',
        'from_role',
        'to_user_id',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    /** The user who sent this message */
    public function sender()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    /** The user who received this message (null = school admin) */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    /**
     * Get the conversation thread between a user and the admin.
     * Returns messages in both directions between the two parties.
     */
    public static function threadWith(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        $adminId = User::where('role', 'admin')->value('id');

        return static::where(function ($q) use ($userId, $adminId) {
            // Messages FROM user TO admin (to_user_id = null or admin id)
            $q->where('from_user_id', $userId)
              ->whereIn('to_user_id', [null, $adminId]);
        })->orWhere(function ($q) use ($userId, $adminId) {
            // Messages FROM admin TO user
            $q->where('from_user_id', $adminId)
              ->where('to_user_id', $userId);
        })->orderBy('created_at', 'asc')->get();
    }
}
