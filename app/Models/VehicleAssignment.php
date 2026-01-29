<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class VehicleAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'ticket_id',
        'start_datetime',
        'end_datetime',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    // Check if assignment is currently active
    public function isActive()
    {
        $now = now();
        return $this->status === 'active' && 
               $this->start_datetime <= $now && 
               $this->end_datetime >= $now;
    }

    // Get time remaining in hours
    public function getTimeRemainingAttribute()
    {
        if (!$this->isActive()) {
            return 0;
        }
        return now()->diffInHours($this->end_datetime, false);
    }

    // Scope for active assignments
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('start_datetime', '<=', now())
                    ->where('end_datetime', '>=', now());
    }

    // Scope for scheduled assignments
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('start_datetime', '>', now());
    }
}
