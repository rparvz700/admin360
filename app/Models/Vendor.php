<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'vendor_code',
        'name',
        'vendor_type',
        'contact_person',
        'phone',
        'email',
        'address',
        'services_offered',
        'rating',
        'is_active',
    ];

    protected $casts = [
        'services_offered' => 'array',
        'rating' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function maintenances()
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function maintenanceParts()
    {
        return $this->hasMany(VehicleMaintenancePart::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function agreements()
    {
        return $this->hasMany(Agreement::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('vendor_type', $type);
    }

    // Helpers
    public function getTotalMaintenanceCost($startDate = null, $endDate = null)
    {
        $query = $this->maintenances();
        if ($startDate) $query = $query->where('service_date', '>=', $startDate);
        if ($endDate)   $query = $query->where('service_date', '<=', $endDate);
        return $query->sum('total_service_cost');
    }

    public function getMaintenanceCount($startDate = null, $endDate = null)
    {
        $query = $this->maintenances();
        if ($startDate) $query = $query->where('service_date', '>=', $startDate);
        if ($endDate)   $query = $query->where('service_date', '<=', $endDate);
        return $query->count();
    }

    public function getVendorTypeLabel()
    {
        return match($this->vendor_type) {
            'workshop'       => 'Workshop',
            'parts_supplier' => 'Parts Supplier',
            'both'           => 'Workshop & Parts',
            default          => 'N/A',
        };
    }

    public static function generateVendorCode()
    {
        $last = self::orderBy('id', 'desc')->first();
        $number = $last ? intval(substr($last->vendor_code, 4)) + 1 : 1;
        return 'VEN-' . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}