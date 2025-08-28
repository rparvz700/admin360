<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleType extends Model
{
    use HasFactory;
    protected $table = 'vehicle_types';
    protected $fillable = [
        'type_name',
    ];
}
