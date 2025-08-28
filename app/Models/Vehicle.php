<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;
    protected $table = 'vehicles';
    protected $fillable = [
        'vehicle_type_id',
        'registration_number',
        'brand',
        'model',
        'manufacture_year',
        'color',
        'seating_capacity',
        'engine_number',
        'chassis_number',
        'use_purpose',
        'use_company',
        'isRented',
        'purchase_price',
        'purchase_date',
        'status',
    ];
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }
}
