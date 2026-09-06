<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicle_types';

    protected $fillable = [
        'type_name',
    ];

    /**
     * Get all vehicles belonging to this vehicle type.
     */
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'vehicle_type_id');
    }
}
