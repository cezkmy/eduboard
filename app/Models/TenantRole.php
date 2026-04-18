<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenantRole extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * We explicitly name it to avoid conflict with standard `roles` table if one exists centrally.
     */
    protected $table = 'tenant_roles';

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'permissions' => 'array',
        ];
    }
}
