<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleService extends Model
{
    use HasFactory;
    protected $table = 'vehicle_services';
    protected $fillable = [
        'vehicle_id',
        'service_date',
        'service_type',
        'service_cost',
        'odometer_reading',
        'notes',
    ];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
