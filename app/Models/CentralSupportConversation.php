<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentralSupportConversation extends Model
{
    use HasFactory;

    protected $table = 'central_support_conversations';
    protected $guarded = [];

    public function messages()
    {
        return $this->hasMany(CentralSupportMessage::class, 'conversation_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
