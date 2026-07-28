<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ElectricityMeter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'electricity_meters';

    protected $fillable = [
        'meter_number',
        'meter_type',
        'provider_name',
        'building_id',
        'floor_id',
        'vendor_id',
        'consumer_no',
        'sanctioned_load_kw',
        'meter_location_notes',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sanctioned_load_kw' => 'decimal:2',
    ];

    public function building()
    {
        return $this->belongsTo(PropertiesBuilding::class, 'building_id');
    }

    public function floor()
    {
        return $this->belongsTo(PropertiesFloor::class, 'floor_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function bills()
    {
        return $this->hasMany(ElectricityBill::class, 'meter_id');
    }

    public function latestBill()
    {
        return $this->hasOne(ElectricityBill::class, 'meter_id')->latestOfMany();
    }

    public function getMeterTypeLabelAttribute()
    {
        return match($this->meter_type) {
            'postpaid_main' => 'Postpaid Main Meter',
            'postpaid_sub'  => 'Postpaid Sub-Meter',
            'prepaid'       => 'Prepaid Meter',
            default         => ucfirst($this->meter_type),
        };
    }

    public function getMeterTypeBadgeAttribute()
    {
        return match($this->meter_type) {
            'postpaid_main' => 'primary-light text-primary',
            'postpaid_sub'  => 'info-light text-info',
            'prepaid'       => 'warning-light text-warning',
            default         => 'secondary-light text-secondary',
        };
    }
}
