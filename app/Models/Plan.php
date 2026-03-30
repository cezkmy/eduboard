<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    /**
     * Set the explicit central database connection
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('tenancy.database.central_connection', 'mysql');
    }

    protected $fillable = ['name', 'price', 'period', 'features', 'is_popular'];

    protected $casts = [
        'features' => 'array',
        'is_popular' => 'boolean',
    ];
}
