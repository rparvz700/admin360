<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ElectricityMeterNoc extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'electricity_meter_nocs';

    protected $fillable = [
        'meter_id',
        'noc_number',
        'period_start_date',
        'period_end_date',
        'issuing_authority',
        'file_path',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'period_start_date' => 'date',
        'period_end_date'   => 'date',
    ];

    public function meter()
    {
        return $this->belongsTo(ElectricityMeter::class, 'meter_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusLabelAttribute()
    {
        $today = now()->startOfDay();
        if ($today->between($this->period_start_date, $this->period_end_date)) {
            $daysLeft = $today->diffInDays($this->period_end_date, false);
            if ($daysLeft <= 30) {
                return 'Expiring Soon (' . $daysLeft . ' days)';
            }
            return 'Valid';
        } elseif ($today->gt($this->period_end_date)) {
            return 'Expired';
        } else {
            return 'Upcoming';
        }
    }

    public function getStatusBadgeAttribute()
    {
        $today = now()->startOfDay();
        if ($today->between($this->period_start_date, $this->period_end_date)) {
            $daysLeft = $today->diffInDays($this->period_end_date, false);
            if ($daysLeft <= 30) {
                return 'warning-light text-warning';
            }
            return 'success-light text-success';
        } elseif ($today->gt($this->period_end_date)) {
            return 'danger-light text-danger';
        } else {
            return 'info-light text-info';
        }
    }
}
