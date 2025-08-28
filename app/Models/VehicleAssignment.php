<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleAssignment extends Model
{
    use HasFactory;
    protected $table = 'vehicle_assignments';
    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'assigned_date',
        'released_date',
        'status',
        'notes',
    ];
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
