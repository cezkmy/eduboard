<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingHistory extends Model
{
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
