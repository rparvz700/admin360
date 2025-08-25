<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Owner extends Model
{
    use HasFactory;

    protected $table = 'owners';

    protected $fillable = [
        'name',
        'vendor_code',
        'account_title',
        'bank_name',
        'account_no',
        'routing_no',
        'mobile_number',
        'created_at',
        'updated_at',
    ];

    public function floors()
    {
        return $this->hasMany(PropertiesFloor::class, 'owner_id');
    }
}
