<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rio extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'rios';

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'rio_user', 'rio_id', 'user_id');
    }

    public function buildings()
    {
        return $this->hasMany(PropertiesBuilding::class, 'rio_id');
    }

    public function bills()
    {
        return $this->hasMany(ElectricityBill::class, 'rio_id');
    }
}
