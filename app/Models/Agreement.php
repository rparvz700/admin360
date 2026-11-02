<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agreement extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'agreements';

    protected $fillable = [
        'agreement_ref_no',
        'vendor_id',
        'agreement_date',
        'payment_start_date',
        'expiry_date',
        'status',
        'npv_interest_rate',
        'remarks',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'npv_interest_rate' => 'float',
    ];

    public function getPaymentStartDateAttribute($value)
    {
        return $value ?? ($this->attributes['from_date'] ?? null);
    }

    public function getExpiryDateAttribute($value)
    {
        return $value ?? ($this->attributes['to_date'] ?? null);
    }

    public function getFromDateAttribute($value)
    {
        return $this->attributes['payment_start_date'] ?? ($value ?? null);
    }

    public function getToDateAttribute($value)
    {
        return $this->attributes['expiry_date'] ?? ($value ?? null);
    }

    protected static function booted(): void
    {
        static::saved(function ($agreement) {
            \App\Models\NpvAgreementSummary::where('agreement_id', $agreement->id)->delete();
        });

        static::deleted(function ($agreement) {
            \App\Models\NpvAgreementSummary::where('agreement_id', $agreement->id)->delete();
        });
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function floors()
    {
        return $this->hasMany(PropertiesFloor::class, 'agreement_id');
    }

    public function buildings()
    {
        return $this->hasManyThrough(
            PropertiesBuilding::class,
            PropertiesFloor::class,
            'agreement_id',
            'id',
            'id',
            'building_id'
        );
    }

    public function getBuildingNamesAttribute(): string
    {
        $floors = $this->relationLoaded('floors') ? $this->floors : $this->floors()->with('building')->get();
        return $floors->map(function ($floor) {
            if (!$floor->building) return null;
            $name = $floor->building->site_name;
            $code = $floor->building->code ?? $floor->building->site_code;
            if ($name && $code && $name !== $code) {
                return "{$name} ({$code})";
            }
            return $name ?: $code;
        })->filter()->unique()->implode(', ');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'agreement_id');
    }

    public function rentIncrements()
    {
        return $this->hasMany(RentIncrement::class, 'agreement_id');
    }

    public function rentBases()
    {
        return $this->hasMany(RentBase::class, 'agreement_id');
    }

    public function rentBase()
    {
        return $this->hasOne(RentBase::class, 'agreement_id');
    }

    public function advanceSettlements()
    {
        // return $this->hasMany(advanceSettlements::class, 'agreement_id');
    }

    public function securityDeposits()
    {
        return $this->hasMany(SecurityDeposit::class, 'agreement_id');
    }

    public function utilities()
    {
        return $this->hasMany(AgreementUtility::class, 'agreement_id');
    }

    public function maintenance()
    {
        return $this->hasMany(Maintenance::class, 'agreement_id');
    }

    public function documents()
    {
        return $this->morphMany(GenericDocument::class, 'documentable');
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
