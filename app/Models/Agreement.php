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
        'from_date',
        'to_date',
        'status',
        'remarks',
        'created_at',
        'updated_at',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function floors()
    {
        return $this->hasMany(PropertiesFloor::class, 'agreement_id');
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
