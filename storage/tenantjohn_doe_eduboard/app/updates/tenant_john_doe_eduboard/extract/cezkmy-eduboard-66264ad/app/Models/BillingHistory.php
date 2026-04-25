<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingHistory extends Model
{
    protected $connection = 'mysql';

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->connection = config('tenancy.database.central_connection', 'mysql');
    }

    protected $fillable = [
        'tenant_id',
        'plan',
        'amount',
        'payment_status',
        'paid_at',
        'invoice_number',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
