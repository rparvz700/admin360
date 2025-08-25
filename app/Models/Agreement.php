<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Agreement extends Model
{
    use HasFactory;

    protected $table = 'agreements';

    protected $fillable = [
        'agreement_ref_no',
        'agreement_date',
        'from_date',
        'to_date',
        'status',
        'remarks',
        'created_at',
        'updated_at',
    ];

    public function floors()
    {
        return $this->hasMany(PropertiesFloor::class, 'agreement_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'agreement_id');
    }

    public function rentIncrements()
    {
        return $this->hasMany(RentIncrement::class, 'agreement_id');
    }

    public function advanceSettlements()
    {
        return $this->hasMany(AdvanceSettlement::class, 'agreement_id');
    }

    public function securityDeposits()
    {
        return $this->hasMany(SecurityDeposit::class, 'agreement_id');
    }

    public function maintenance()
    {
        return $this->hasMany(Maintenance::class, 'agreement_id');
    }
}
