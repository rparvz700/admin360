<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'rent_base_id',
        'component_type',
        'area_sft',
        'rate',
        'rent_amount',
        'vat_applicable',
        'vat_amount',
        'tax_amount',
        'total_amount',
    ];

    protected static function booted(): void
    {
        static::saved(function ($component) {
            $agreementId = $component->rentBase?->agreement_id;
            if ($agreementId) {
                \App\Models\NpvAgreementSummary::where('agreement_id', $agreementId)->delete();
            }
        });

        static::deleted(function ($component) {
            $agreementId = $component->rentBase?->agreement_id;
            if ($agreementId) {
                \App\Models\NpvAgreementSummary::where('agreement_id', $agreementId)->delete();
            }
        });
    }

    protected $casts = [
        'area_sft' => 'decimal:2',
        'rate' => 'decimal:2',
        'rent_amount' => 'decimal:2',
        'vat_applicable' => 'boolean',
        'vat_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function rentBase()
    {
        return $this->belongsTo(RentBase::class, 'rent_base_id');
    }
}
