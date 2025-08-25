<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'agreement_id',
        'rent_payment_type',
        'amount',
        'gross',
        'vat',
        'tax',
        'net_rent',
        'less_advance',
        'car_parking',
        'dg_space_sft',
        'dg_space_rent',
        'service_charge',
        'store_space_sft',
        'store_space_rent',
        'utilities',
        'others',
        'net_payable',
        'payment_sheet',
        'created_at',
        'updated_at',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class, 'agreement_id');
    }
}
