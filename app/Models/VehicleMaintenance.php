<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleMaintenance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicle_maintenances';

    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'status',
        'start_datetime',
        'estimated_end_datetime',
        'actual_end_datetime',
        'service_description',
        'vendor_id',
        'invoice_id',
        'meter_reading_at_service',
        'total_service_cost',
        'next_service_due_date',
        'next_service_due_km',
        'current_service_completed',
        'labor_cost',
        'parts_cost',
        'parts_replaced',
        'performed_by',
        'approved_by',
        'remarks',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'estimated_end_datetime' => 'datetime',
        'actual_end_datetime' => 'datetime',
        'next_service_due_date'       => 'date',
        'total_service_cost'          => 'decimal:2',
        'labor_cost'                  => 'decimal:2',
        'parts_cost'                  => 'decimal:2',
        'parts_replaced'              => 'array',
        'current_service_completed'   => 'boolean',
        'meter_reading_at_service'    => 'integer',
        'next_service_due_km'         => 'integer',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    // Check if maintenance is currently ongoing
    public function isOngoing()
    {
        $now = now();
        return in_array($this->status, ['scheduled', 'in_progress']) && 
               $this->start_datetime <= $now && 
               (!$this->actual_end_datetime || $this->actual_end_datetime >= $now);
    }

    // Get estimated time remaining in hours
    public function getEstimatedTimeRemainingAttribute()
    {
        if (!$this->isOngoing()) {
            return 0;
        }
        return now()->diffInHours($this->estimated_end_datetime, false);
    }


    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function maintenanceParts()
    {
        return $this->hasMany(VehicleMaintenancePart::class, 'vehicle_maintenance_id');
    }

    // Scopes
    public function scopeRoutine($query)     { return $query->where('maintenance_type', 'routine'); }
    public function scopeBreakdown($query)   { return $query->where('maintenance_type', 'breakdown'); }
    public function scopeAccident($query)    { return $query->where('maintenance_type', 'accident'); }
    public function scopeInspection($query)  { return $query->where('maintenance_type', 'inspection'); }
    // Scope for active maintenance
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['scheduled', 'in_progress'])
                    ->where('start_datetime', '<=', now())
                    ->where(function($q) {
                        $q->whereNull('actual_end_datetime')
                          ->orWhere('actual_end_datetime', '>=', now());
                    });
    }
    
    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    public function scopeDueForService($query)
    {
        return $query->where('current_service_completed', false)
            ->where(function ($q) {
                $q->where('next_service_due_date', '<=', now()->toDateString())
                  ->orWhere('next_service_due_km', '<=', function ($sub) {
                      // Subquery placeholder – compare per vehicle in app layer
                  });
            });
    }

    // Helpers
    public function getMaintenanceTypeLabel()
    {
        return match($this->maintenance_type) {
            'routine'    => 'Routine',
            'breakdown'  => 'Breakdown',
            'accident'   => 'Accident',
            'inspection' => 'Inspection',
            default      => 'Unknown',
        };
    }

    public function getMaintenanceTypeBadge()
    {
        return match($this->maintenance_type) {
            'routine'    => 'success',
            'breakdown'  => 'danger',
            'accident'   => 'danger',
            'inspection' => 'info',
            default      => 'secondary',
        };
    }

    public function isDue()
    {
        if (!$this->current_service_completed) return false;
        if ($this->next_service_due_date && $this->next_service_due_date <= now()) return true;

        $currentKm = VehicleOperationalLog::getCurrentMeterReading($this->vehicle_id);
        if ($this->next_service_due_km && $currentKm >= $this->next_service_due_km) return true;

        return false;
    }

    public function getDaysUntilDue()
    {
        if (!$this->next_service_due_date) return null;
        return now()->diffInDays($this->next_service_due_date, false);
    }

    public function getKmUntilDue()
    {
        if (!$this->next_service_due_km) return null;
        $currentKm = VehicleOperationalLog::getCurrentMeterReading($this->vehicle_id);
        return $this->next_service_due_km - $currentKm;
    }
}

