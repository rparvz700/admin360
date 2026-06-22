<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UtilityType extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'utility_types';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function agreementUtilities()
    {
        return $this->hasMany(AgreementUtility::class, 'utility_type_id');
    }
}
