<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentralSupportMessage extends Model
{
    use HasFactory;

    protected $table = 'central_support_messages';
    protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function conversation()
    {
        return $this->belongsTo(CentralSupportConversation::class, 'conversation_id');
    }
}
