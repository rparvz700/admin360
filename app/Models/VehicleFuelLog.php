<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleFuelLog extends Model
{
    use HasFactory;
    protected $table = 'vehicle_fuel_logs';
    protected $fillable = [
        'vehicle_id',
        'refuel_date',
        'fuel_quantity_liters',
        'fuel_cost',
        'odometer_reading',
    ];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
