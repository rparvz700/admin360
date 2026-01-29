<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class DriverAvailability extends Model
{
    use HasFactory;

    protected $table = 'driver_availability';

    protected $fillable = [
        'driver_id',
        'status',
        'unavailable_from',
        'unavailable_until',
        'reason',
    ];

    protected $casts = [
        'unavailable_from' => 'datetime',
        'unavailable_until' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    // Check if driver is currently unavailable
    public function isUnavailable()
    {
        if ($this->status === 'available') {
            return false;
        }

        if ($this->unavailable_from && $this->unavailable_until) {
            $now = now();
            return $now >= $this->unavailable_from && $now <= $this->unavailable_until;
        }

        return in_array($this->status, ['on_leave', 'sick', 'unavailable']);
    }

    // Get time until available in hours
    public function getTimeUntilAvailableAttribute()
    {
        if (!$this->isUnavailable() || !$this->unavailable_until) {
            return 0;
        }
        return now()->diffInHours($this->unavailable_until, false);
    }
}

