<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentBase extends Model
{
    use HasFactory;

    // Relationship: securityDeposits
    public function securityDeposits()
    {
        return $this->hasMany(\App\Models\SecurityDeposit::class, 'agreement_id', 'agreement_id');
    }

    protected $table = 'rent_base';

    protected $fillable = [
        'agreement_id',
        'base_rent',
        'vat',
        'tax',
        'is_at_source',
        'rent_type',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

    // Accessors for agreement start and end date
    public function getAgreementStartDateAttribute()
    {
        return $this->agreement ? $this->agreement->from_date : null;
    }

    public function getAgreementEndDateAttribute()
    {
        return $this->agreement ? $this->agreement->to_date : null;
    }

    // Relationship: increments
    public function increments()
    {
        return $this->hasMany(RentIncrement::class, 'base_rent_id');
    }
}
