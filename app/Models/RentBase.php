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

    protected static function booted(): void
    {
        static::saved(function ($rentBase) {
            if ($rentBase->agreement_id) {
                \App\Models\NpvAgreementSummary::where('agreement_id', $rentBase->agreement_id)->delete();
            }
        });

        static::deleted(function ($rentBase) {
            if ($rentBase->agreement_id) {
                \App\Models\NpvAgreementSummary::where('agreement_id', $rentBase->agreement_id)->delete();
            }
        });
    }

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
     * Get details about the current effective rent for a given billing month (defaults to current month).
     * Calculates which increment tier/period the month belongs to.
     *
     * @param string|null $billingMonth Format: "YYYY-MM" (e.g. "2026-10")
     * @return array
     */
    public function getCurrentRentDetails(?string $billingMonth = null): array
    {
        $billingMonth = $billingMonth ?: now()->format('Y-m');
        $monthDate = \Carbon\Carbon::parse($billingMonth . '-01');
        $monthStart = $monthDate->copy()->startOfMonth();
        $monthEnd   = $monthDate->copy()->endOfMonth();
        $baseRent   = (float) $this->base_rent;

        $increments = $this->relationLoaded('increments')
            ? $this->increments->sortBy('increment_start_date')->values()
            : $this->increments()->orderBy('increment_start_date')->get();

        $runningTotal = $baseRent;
        $activeIncrement = null;
        $activeTierNumber = 0;
        $tierCounter = 0;
        $fallbackRent = null;
        $lastEndedTier = 0;
        $effectiveRent = $baseRent;

        foreach ($increments as $inc) {
            $tierCounter++;
            $incAmt = (float) ($inc->increment_amount ?? 0);
            $runningTotal += $incAmt;
            $incTotal = (!is_null($inc->incremented_amount) && (float) $inc->incremented_amount > 0)
                ? (float) $inc->incremented_amount
                : $runningTotal;

            $startDate = $inc->increment_start_date ? \Carbon\Carbon::parse($inc->increment_start_date)->startOfDay() : null;
            $endDate   = $inc->increment_end_date ? \Carbon\Carbon::parse($inc->increment_end_date)->endOfDay() : null;

            if ($startDate && $monthEnd->gte($startDate)) {
                if (is_null($endDate) || $monthStart->lte($endDate)) {
                    $activeIncrement = $inc;
                    $activeTierNumber = $tierCounter;
                    $effectiveRent = $incTotal;
                } elseif ($monthStart->gt($endDate)) {
                    $fallbackRent = $incTotal;
                    $lastEndedTier = $tierCounter;
                }
            }
        }

        if ($activeIncrement) {
            $currentRent = $effectiveRent;
            $startFormatted = $activeIncrement->increment_start_date ? \Carbon\Carbon::parse($activeIncrement->increment_start_date)->format('d M Y') : 'N/A';
            $endFormatted = $activeIncrement->increment_end_date ? \Carbon\Carbon::parse($activeIncrement->increment_end_date)->format('d M Y') : 'Ongoing';
            $periodLabel = "Increment Tier #{$activeTierNumber} ({$startFormatted} - {$endFormatted})";
            $status = 'incremented';
        } elseif (!is_null($fallbackRent)) {
            $currentRent = $fallbackRent;
            $periodLabel = "Post-Increment (After Tier #{$lastEndedTier})";
            $status = 'post_increment';
        } else {
            $currentRent = $baseRent;
            $periodLabel = "Base Rent Period (Initial)";
            $status = 'base';
        }

        $vat = (float) $this->vat;
        $tax = (float) $this->tax;

        return [
            'current_rent'     => $currentRent,
            'base_rent'        => $baseRent,
            'billing_month'    => $billingMonth,
            'month_label'      => $monthDate->format('F Y'),
            'period_label'     => $periodLabel,
            'status'           => $status,
            'active_increment' => $activeIncrement,
            'is_incremented'   => $currentRent > $baseRent,
            'increment_diff'   => max(0, $currentRent - $baseRent),
            'vat'              => $vat,
            'tax'              => $tax,
            'subtotal'         => $currentRent + $vat + $tax,
        ];
    }

    /**
     * Get the effective rent for a billing month, considering active increments.
     *
     * @param string $billingMonth Format: "YYYY-MM" (e.g. "2026-08")
     * @return array
     */
    public function getEffectiveRentForMonth(string $billingMonth): array
    {
        $details = $this->getCurrentRentDetails($billingMonth);

        return [
            'base_rent'        => $details['base_rent'],
            'increment_amount' => $details['increment_diff'],
            'effective_rent'   => $details['current_rent'],
            'vat'              => $details['vat'],
            'tax'              => $details['tax'],
            'subtotal'         => $details['subtotal'],
        ];
    }

    // Accessors for agreement start and end date
    public function getAgreementStartDateAttribute()
    {
        return $this->agreement ? ($this->agreement->payment_start_date ?? $this->agreement->from_date) : null;
    }

    public function getAgreementEndDateAttribute()
    {
        return $this->agreement ? ($this->agreement->expiry_date ?? $this->agreement->to_date) : null;
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
