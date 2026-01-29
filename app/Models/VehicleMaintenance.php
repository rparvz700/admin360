<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class VehicleMaintenance extends Model
{
    use HasFactory;

    protected $table = 'vehicle_maintenance';

    protected $fillable = [
        'vehicle_id',
        'maintenance_type',
        'status',
        'start_datetime',
        'estimated_end_datetime',
        'actual_end_datetime',
        'description',
        'notes',
        'cost',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'estimated_end_datetime' => 'datetime',
        'actual_end_datetime' => 'datetime',
        'cost' => 'decimal:2',
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
}

