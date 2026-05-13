<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VatTax extends Model
{
    use HasFactory;

    protected $table = 'vat_taxes';

    protected $fillable = [
        'type',
        'vat',
        'tax',
        'status',
    ];

    protected $casts = [
        'vat' => 'decimal:2',
        'tax' => 'decimal:2',
        'status' => 'boolean',
    ];
}
