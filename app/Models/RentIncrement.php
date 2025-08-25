<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RentIncrement extends Model
{
    use HasFactory;

    protected $table = 'rent_increments';

    protected $fillable = [
        'agreement_id',
        'base_rent_id',
        'incremented_amount',
        'increment_start_date',
        'increment_end_date',
        'increment_amount',
        'increment_percentage',
        'increment_frequency',
        'method_description',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }

    public function baseRent()
    {
        return $this->belongsTo(RentBase::class, 'base_rent_id');
    }
}
