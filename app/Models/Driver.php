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
}
