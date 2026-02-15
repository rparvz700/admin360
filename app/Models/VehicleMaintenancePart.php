<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleMaintenancePart extends Model
{
    use HasFactory;

    protected $table = 'vehicle_maintenance_parts';

    protected $fillable = [
        'vehicle_maintenance_id',
        'vehicle_part_id',
        'vehicle_id',
        'action_type',
        'quantity',
        'vendor_id',
        'part_cost',
        'warranty_period_months',
        'warranty_expiry_date',
        'next_replacement_due_date',
        'next_replacement_due_km',
        'remarks',
    ];

    protected $casts = [
        'part_cost'                  => 'decimal:2',
        'quantity'                   => 'integer',
        'warranty_period_months'     => 'integer',
        'warranty_expiry_date'       => 'date',
        'next_replacement_due_date'  => 'date',
        'next_replacement_due_km'    => 'integer',
    ];

    // Relationships
    public function maintenance()
    {
        return $this->belongsTo(VehicleMaintenance::class, 'vehicle_maintenance_id');
    }

    public function part()
    {
        return $this->belongsTo(VehiclePart::class, 'vehicle_part_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // Helpers
    public function isUnderWarranty()
    {
        return $this->warranty_expiry_date && $this->warranty_expiry_date >= now();
    }

    public function isDueForReplacement()
    {
        if ($this->next_replacement_due_date && $this->next_replacement_due_date <= now()) return true;

        if ($this->next_replacement_due_km) {
            $currentKm = VehicleOperationalLog::getCurrentMeterReading($this->vehicle_id);
            if ($currentKm >= $this->next_replacement_due_km) return true;
        }
        return false;
    }

    public function getActionTypeLabel()
    {
        return match($this->action_type) {
            'replace' => 'Replaced',
            'repair'  => 'Repaired',
            'service' => 'Serviced',
            default   => 'Unknown',
        };
    }

    public function getActionTypeBadge()
    {
        return match($this->action_type) {
            'replace' => 'danger',
            'repair'  => 'warning',
            'service' => 'info',
            default   => 'secondary',
        };
    }

    public function getWarrantyStatus()
    {
        if (!$this->warranty_expiry_date) return ['label' => 'No Warranty', 'badge' => 'secondary'];
        if ($this->isUnderWarranty())      return ['label' => 'Active',     'badge' => 'success'];
        return ['label' => 'Expired', 'badge' => 'danger'];
    }
}