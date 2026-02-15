<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehiclePart extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicle_parts';

    protected $fillable = [
        'part_name',
        'part_code',
        'category',
        'description',
        'typical_lifespan_km',
        'typical_lifespan_months',
        'is_active',
    ];

    protected $casts = [
        'is_active'              => 'boolean',
        'typical_lifespan_km'    => 'integer',
        'typical_lifespan_months'=> 'integer',
    ];

    // Relationships
    public function maintenanceParts()
    {
        return $this->hasMany(VehicleMaintenancePart::class, 'vehicle_part_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Helpers
    public function getCategoryLabel()
    {
        return match($this->category) {
            'engine'        => 'Engine',
            'tyre'          => 'Tyre',
            'battery'       => 'Battery',
            'oil'           => 'Oil / Lubricant',
            'brake'         => 'Brake System',
            'body'          => 'Body / Cover',
            'transmission'  => 'Transmission / Gear',
            'electrical'    => 'Electrical',
            'other'         => 'Other',
            default         => 'Unknown',
        };
    }

    public function getCategoryBadge()
    {
        return match($this->category) {
            'engine'        => 'danger',
            'tyre'          => 'warning',
            'battery'       => 'info',
            'oil'           => 'success',
            'brake'         => 'danger',
            'body'          => 'secondary',
            'transmission'  => 'primary',
            'electrical'    => 'info',
            default         => 'secondary',
        };
    }

    public function getLastReplacementForVehicle($vehicleId)
    {
        return $this->maintenanceParts()
            ->where('vehicle_id', $vehicleId)
            ->orderByDesc('created_at')
            ->first();
    }

    public static function generatePartCode($category)
    {
        $prefix = match($category) {
            'engine'       => 'ENG',
            'tyre'         => 'TYR',
            'battery'      => 'BAT',
            'oil'          => 'OIL',
            'brake'        => 'BRK',
            'body'         => 'BDY',
            'transmission' => 'TRS',
            'electrical'   => 'ELC',
            default        => 'OTH',
        };
        $last = self::where('part_code', 'like', $prefix . '%')->orderBy('id', 'desc')->first();
        $num  = $last ? intval(substr($last->part_code, 4)) + 1 : 1;
        return $prefix . '-' . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}