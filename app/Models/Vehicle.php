<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vehicle extends Model
{
    use HasFactory;
    protected $table = 'vehicles';
    protected $fillable = [
        'vehicle_type_id',
        'registration_number',
        'brand',
        'model',
        'manufacture_year',
        'color',
        'seating_capacity',
        'engine_number',
        'chassis_number',
        'use_purpose',
        'use_company',
        'isRented',
        'purchase_price',
        'purchase_date',
        'status',
        'reg_year',
        'engine_cc',
        'vehicle_weight'
    ];
    public function vehicleType()
    {
        return $this->belongsTo(VehicleType::class, 'vehicle_type_id');
    }

    // New relationships
    public function assignments()
    {
        return $this->hasMany(VehicleAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(VehicleAssignment::class)
                    ->where('status', 'active')
                    ->where('start_datetime', '<=', now())
                    ->where('end_datetime', '>=', now())
                    ->latest();
    }

    public function upcomingAssignments()
    {
        return $this->hasMany(VehicleAssignment::class)
                    ->where('status', 'scheduled')
                    ->where('start_datetime', '>', now())
                    ->orderBy('start_datetime');
    }

    public function maintenance()
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function currentMaintenance()
    {
        return $this->hasOne(VehicleMaintenance::class)
                    ->whereIn('status', ['scheduled', 'in_progress'])
                    ->where('start_datetime', '<=', now())
                    ->where(function($q) {
                        $q->whereNull('actual_end_datetime')
                          ->orWhere('actual_end_datetime', '>=', now());
                    })
                    ->latest();
    }

    // Helper methods
    public function isAvailable()
    {
        // Check if vehicle is not in maintenance
        if ($this->currentMaintenance) {
            return false;
        }

        // Check if vehicle is not on assignment
        if ($this->currentAssignment) {
            return false;
        }

        // Check base status
        return $this->status === 'active';
    }

    public function getCurrentStatus()
    {
        if ($this->currentMaintenance) {
            return [
                'status' => 'in_maintenance',
                'label' => 'In Maintenance',
                'color' => 'danger',
                'details' => $this->currentMaintenance,
            ];
        }

        if ($this->currentAssignment) {
            return [
                'status' => 'on_assignment',
                'label' => 'On Assignment',
                'color' => 'warning',
                'details' => $this->currentAssignment,
            ];
        }

        return [
            'status' => 'available',
            'label' => 'Available',
            'color' => 'success',
            'details' => null,
        ];
    }

    public function getOwnershipType()
    {
        return $this->isRented ? 'Rented' : 'Owned';
    }

    // ─── NEW: Maintenance module relationships ───────────
    public function operationalLogs()
    {
        return $this->hasMany(VehicleOperationalLog::class);
    }

    public function maintenances()
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function maintenanceParts()
    {
        return $this->hasMany(VehicleMaintenancePart::class);
    }

    // ─── NEW: Helper methods ─────────────────────────────
    public function getCurrentMeterReading()
    {
        return VehicleOperationalLog::getCurrentMeterReading($this->id);
    }

    public function getCurrentVehicleStatus()
    {
        return VehicleOperationalLog::getCurrentStatus($this->id);
    }

    public function getCurrentAssignment()
    {
        return VehicleOperationalLog::getCurrentAssignment($this->id);
    }

    public function getLatestMaintenance()
    {
        return $this->maintenances()->orderByDesc('service_date')->first();
    }

    public function getTotalMaintenanceCost($startDate = null, $endDate = null)
    {
        $query = $this->maintenances();
        if ($startDate) $query = $query->where('start_datetime', '>=', $startDate);
        if ($endDate)   $query = $query->where('start_datetime', '<=', $endDate);
        return $query->sum('total_service_cost');
    }

    /**
     * Get all parts that are due for replacement on this vehicle.
     */
    public function getPartsDue()
    {
        return $this->maintenanceParts()
            ->with('part')
            ->where(function ($q) {
                $currentKm = $this->getCurrentMeterReading();
                $q->where('next_replacement_due_date', '<=', now()->toDateString())
                  ->orWhere('next_replacement_due_km', '<=', $currentKm);
            })
            ->get();
    }

    /**
     * Check if this vehicle is due for next service.
     */
    public function isServiceDue()
    {
        $latest = $this->getLatestMaintenance();
        if (!$latest) return false;
        return $latest->isDue();
    }

    public function getDisplayName()
    {
        return $this->brand . ' ' . $this->model . ' (' . $this->registration_number . ')';
    }

}
