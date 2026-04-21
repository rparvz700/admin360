<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Driver extends Model
{
    use HasFactory;

    protected $table = 'drivers';

    protected $fillable = [
        'hr_id',
        'name',
        'sur_name',
        'joining_date',
        'employment_contract',
        'gender',
        'email',
        'phone',
        'blood_group',
        'marital_status',
        'date_of_birth',
        'image_path',
        'contract_renewed',
        'confirmation_date',
        'contract_end_date',
        'passport_no',
        'designation',
        'department',
        'division',
        'office_location',
        'subcenter',
        'job_location',
        'supervisor_name',
        'supervisor_email',
        'supervisor_hr_id',
        'supervisor_company',
        'bill_reviewer_name',
        'bill_reviewer_email',
        'bill_reviewer_hr_id',
        'bill_reviewer_company',
    ];


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

    public function availability()
    {
        return $this->hasOne(DriverAvailability::class);
    }

    // Helper methods
    public function isAvailable()
    {
        // Check availability record
        if ($this->availability && $this->availability->isUnavailable()) {
            return false;
        }

        // Check if driver is not on assignment
        if ($this->currentAssignment) {
            return false;
        }

        return true;
    }

    public function getCurrentStatus()
    {
        // Check availability record first
        if ($this->availability && $this->availability->isUnavailable()) {
            return [
                'status' => $this->availability->status,
                'label' => ucwords(str_replace('_', ' ', $this->availability->status)),
                'color' => 'danger',
                'details' => $this->availability,
            ];
        }

        // Check assignment
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

    public function getFullNameAttribute()
    {
        return trim($this->name . ' ' . $this->sur_name);
    }
}
