<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdateLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'update_id',
        'type', // info, success, warning, error
        'message',
    ];
}
