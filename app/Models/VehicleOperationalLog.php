<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleOperationalLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicle_operational_logs';

    protected $fillable = [
        'vehicle_id',
        'log_type',
        'assigned_department',
        'assigned_user_id',
        'meter_reading',
        'vehicle_status',
        'remarks',
        'logged_by',
        'logged_at',
    ];

    protected $casts = [
        'logged_at'     => 'datetime',
        'meter_reading' => 'integer',
    ];

    // Relationships
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function logger()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    // Scopes
    public function scopeMeterReadings($query)
    {
        return $query->where('log_type', 'meter_reading');
    }

    public function scopeAssignments($query)
    {
        return $query->where('log_type', 'assignment');
    }

    public function scopeStatusChanges($query)
    {
        return $query->where('log_type', 'status_change');
    }

    public function scopeForVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    // Static helpers
    public static function getCurrentMeterReading($vehicleId)
    {
        return self::where('vehicle_id', $vehicleId)
            ->where('log_type', 'meter_reading')
            ->orderByDesc('logged_at')
            ->value('meter_reading') ?? 0;
    }

    public static function getCurrentStatus($vehicleId)
    {
        return self::where('vehicle_id', $vehicleId)
            ->where('log_type', 'status_change')
            ->orderByDesc('logged_at')
            ->value('vehicle_status') ?? 'active';
    }

    public static function getCurrentAssignment($vehicleId)
    {
        return self::where('vehicle_id', $vehicleId)
            ->where('log_type', 'assignment')
            ->orderByDesc('logged_at')
            ->first();
    }

    public function getLogTypeLabel()
    {
        return match($this->log_type) {
            'assignment'    => 'Assignment',
            'meter_reading' => 'Meter Reading',
            'status_change' => 'Status Change',
            default         => 'Unknown',
        };
    }

    public function getLogTypeBadge()
    {
        return match($this->log_type) {
            'assignment'    => 'info',
            'meter_reading' => 'primary',
            'status_change' => 'warning',
            default         => 'secondary',
        };
    }

    public function getStatusBadge()
    {
        return match($this->vehicle_status) {
            'active'             => 'success',
            'inactive'           => 'secondary',
            'sold'               => 'danger',
            'under_maintenance'  => 'warning',
            default              => 'secondary',
        };
    }
}