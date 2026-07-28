<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Owner extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'owners';

    protected $fillable = [
        'owner_name',
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

    public function getNameAttribute()
    {
        return $this->attributes['owner_name'] ?? ($this->attributes['name'] ?? '');
    }

    public function floors()
    {
        return $this->hasMany(PropertiesFloor::class, 'owner_id');
    }
}
