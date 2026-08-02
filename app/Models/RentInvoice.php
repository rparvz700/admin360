<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class RentInvoice extends Pivot
{
    protected $table = 'rent_invoices';

    protected $fillable = [
        'rent_base_id',
        'invoice_id',
        'billing_month',
    ];

    public function rentBase()
    {
        return $this->belongsTo(RentBase::class, 'rent_base_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
