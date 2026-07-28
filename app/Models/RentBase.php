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
