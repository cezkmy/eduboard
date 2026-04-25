<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'label',
        'group'
    ];
}
