<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgreementUtility extends Model
{
    use HasFactory;

    protected $table = 'agreement_utilities';

    protected $fillable = [
        'agreement_id',
        'utility_type_id',
        'amount',
        'disburse_with_rent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'disburse_with_rent' => 'boolean',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class, 'agreement_id');
    }

    public function utilityType()
    {
        return $this->belongsTo(UtilityType::class, 'utility_type_id');
    }
}
