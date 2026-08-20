<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpvAgreementSummary extends Model
{
    use HasFactory;

    protected $table = 'npv_agreement_summaries';

    protected $fillable = [
        'agreement_id',
        'discount_rate',
        'agreement_ref_no',
        'vendor_name',
        'site_name',
        'payment_start_date',
        'expiry_date',
        'from_date',
        'to_date',
        'total_months',
        'total_npv',
        'total_undiscounted_outflow',
        'total_gross_rent',
        'total_advance_deductions',
        'total_deposit_refunds',
        'calculated_at',
    ];

    public function getPaymentStartDateAttribute($value)
    {
        return $value ?? $this->attributes['from_date'] ?? null;
    }

    public function getExpiryDateAttribute($value)
    {
        return $value ?? $this->attributes['to_date'] ?? null;
    }

    public function getFromDateAttribute($value)
    {
        return $value ?? $this->attributes['payment_start_date'] ?? null;
    }

    public function getToDateAttribute($value)
    {
        return $value ?? $this->attributes['expiry_date'] ?? null;
    }

    protected $casts = [
        'discount_rate' => 'float',
        'total_months' => 'integer',
        'total_npv' => 'float',
        'total_undiscounted_outflow' => 'float',
        'total_gross_rent' => 'float',
        'total_advance_deductions' => 'float',
        'total_deposit_refunds' => 'float',
        'calculated_at' => 'datetime',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class, 'agreement_id');
    }
}
