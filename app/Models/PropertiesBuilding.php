<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PropertiesBuilding extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'properties_building';

    protected $fillable = [
        'code',
        'site_code',
        'rio_id',
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

    public function rio()
    {
        return $this->belongsTo(Rio::class, 'rio_id');
    }

    public function meters()
    {
        return $this->hasMany(ElectricityMeter::class, 'building_id');
    }

    public function floors()
    {
        return $this->hasMany(PropertiesFloor::class, 'building_id');
    }

    public function getSiteCodeAttribute()
    {
        return $this->attributes['site_code'] ?? $this->code ?? null;
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'upazila', 'upazilla');
    }
}
