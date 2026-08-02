<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentBase extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    // Relationship: securityDeposits
    public function securityDeposits()
    {
        return $this->hasMany(\App\Models\SecurityDeposit::class, 'agreement_id', 'agreement_id');
    }

    public function agreementUtilities()
    {
        return $this->hasMany(\App\Models\AgreementUtility::class, 'agreement_id', 'agreement_id');
    }

    protected $table = 'rent_base';

    protected $fillable = [
        'agreement_id',
        'base_rent',
        'vat',
        'tax',
        'is_at_source',
        'rent_type',
        'invoice_id',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'rent_invoices')
                    ->withPivot('billing_month')
                    ->withTimestamps();
    }

    public function hasInvoiceForMonth(string $billingMonth): bool
    {
        return $this->invoices()
                    ->wherePivot('billing_month', $billingMonth)
                    ->exists();
    }

    /**
     * Get the effective rent for a billing month, considering active increments.
     *
     * @param string $billingMonth Format: "YYYY-MM" (e.g. "2026-08")
     * @return array
     */
    public function getEffectiveRentForMonth(string $billingMonth): array
    {
        $monthStart = \Carbon\Carbon::parse($billingMonth . '-01');
        $monthEnd   = $monthStart->copy()->endOfMonth();
        $baseRent   = (float) $this->base_rent;

        $activeIncrement = $this->increments()
            ->where('increment_start_date', '<=', $monthEnd)
            ->where(function ($q) use ($monthStart) {
                $q->whereNull('increment_end_date')
                  ->orWhere('increment_end_date', '>=', $monthStart);
            })
            ->orderByDesc('increment_start_date')
            ->first();

        if ($activeIncrement && $activeIncrement->incremented_amount) {
            $effectiveRent   = (float) $activeIncrement->incremented_amount;
            $incrementAmount = $effectiveRent - $baseRent;
        } else {
            $lastIncrement = $this->increments()
                ->orderByDesc('increment_start_date')
                ->first();

            if ($lastIncrement && $lastIncrement->increment_end_date && $monthStart->gt(\Carbon\Carbon::parse($lastIncrement->increment_end_date))) {
                $effectiveRent   = (float) ($lastIncrement->incremented_amount ?? ($baseRent + ($lastIncrement->increment_amount ?? 0)));
                $incrementAmount = $effectiveRent - $baseRent;
            } else {
                $effectiveRent   = $baseRent;
                $incrementAmount = 0.0;
            }
        }

        $vat = (float) $this->vat;
        $tax = (float) $this->tax;

        return [
            'base_rent'        => $baseRent,
            'increment_amount' => max(0, $incrementAmount),
            'effective_rent'   => $effectiveRent,
            'vat'              => $vat,
            'tax'              => $tax,
            'subtotal'         => $effectiveRent + $vat + $tax,
        ];
    }

    // Accessors for agreement start and end date
    public function getAgreementStartDateAttribute()
    {
        return $this->agreement ? $this->agreement->from_date : null;
    }

    public function getAgreementEndDateAttribute()
    {
        return $this->agreement ? $this->agreement->to_date : null;
    }

    // Relationship: increments
    public function increments()
    {
        return $this->hasMany(RentIncrement::class, 'base_rent_id');
    }

    public function components()
    {
        return $this->hasMany(RentComponent::class, 'rent_base_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // ->logOnly(['agreement_id', 'building_id'])
            ->logAll()
            ->logExcept(['updated_at'])
            ->logOnlyDirty() 
            ->dontSubmitEmptyLogs(); 
    }
}
