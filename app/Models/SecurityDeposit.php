<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecurityDeposit extends Model
{
    use HasFactory;

    protected $table = 'security_deposits';

    protected $fillable = [
        'agreement_id',
        'security_deposit_total',
        'security_deposit_absorbable',
        'security_deposit_non_absorbable',
        'absorb_start_date',
        'absorb_end_date',
        'absorb_amount',
        'absorb_amount_percentage',
        'absorb_frequency',
        'method_description',
    ];

    public function agreement()
    {
        return $this->belongsTo(Agreement::class);
    }
}
