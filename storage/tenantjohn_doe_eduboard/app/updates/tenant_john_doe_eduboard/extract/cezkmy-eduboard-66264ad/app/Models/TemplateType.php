<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateType extends Model
{
    protected $connection = 'mysql';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('tenancy.database.central_connection', 'mysql');
    }

    protected $fillable = ['name', 'color'];
}
