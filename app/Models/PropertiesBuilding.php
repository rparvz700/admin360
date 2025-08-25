<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PropertiesBuilding extends Model
{
    use HasFactory;

    protected $table = 'properties_building';

    protected $fillable = [
        'code',
        'site_name',
        'country',
        'division',
        'district',
        'upazila',
        'area',
        'address',
        'lat',
        'long',
    ];

    public function floors()
    {
        return $this->hasMany(PropertiesFloor::class, 'building_id');
    }
}
